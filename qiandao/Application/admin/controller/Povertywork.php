<?php 

/**
 * 精准扶贫-帮扶工作
 */

namespace app\admin\controller;

use app\admin\model\Image;
use \think\Request;
use app\lib\Qiniu;
use app\common\Common;
use app\common\BaseHelper as Helper;

use app\admin\model\Member;
use app\admin\model\Povertymember;
use app\admin\model\Area;

class Povertywork extends Base{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'povertywork';

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = ['neq', 1];
        $this->defaultWhere = $defaultWhere;
    }

    public function index() {
        $request = $this->request;
        $param = $request->param();
        // Reset filter
        if ($request->param('reset')) {
            return redirect(fullUrl($request->path()));
        }
        if($request->isAjax()){
            $model = model($this->model);

            // 每页起始条数
            $start = $param['start'];
            // 每页显示条数
            $length = $param['length'];
            // 排序条件
            $columns = $param['order'][0]['column'];
            $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];

            $where = $this->getFilterWhere($request);
            if($this->defaultWhere){
                //$model = $model->where($this->defaultWhere);
                $where = array_merge($this->defaultWhere, $where);
            }
            /*if($this->defaultOrder){
                $model = $model->order($this->defaultOrder);
            }*/

            $list = $model->where($where)->limit($start, $length)->order($order)->select();
            foreach ($list as $k => $v) {
                $list[$k]['povertyName'] = Member::where('id',$v['povertymemberId'])->value('name');
                $list[$k]['partyName'] = Member::where('id',$v['povertypartyId'])->value('name');
            }
            $count = $model->where($where)->count();
            $result = [
                'status' => '1',
                'draw' => $param['draw'],
                'data' => $list,
                'recordsFiltered' => $count,
                'recordsTotal' => $count,
            ];
            return json($result);

        }

        return $this->view->fetch($this->indexView, [
            'pageSize' => ['url' => fullUrl($request->path())],
            'searchFields' => $this->searchFields,
            'param' => $request->param()
        ]);
    }

    public function add(Request $request) {
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect = ''){
        $data = $request->param();
        $povertymemberId = $data['povertymemberId'];
        // return $data;
        $data['id'] = Helper::getUUID();
        if(isset($data['imgIds'])) {
            if(!is_array($data['imgIds'])) {  //关联图片
                Image::where('id',$data['imgIds'])->update(['relatedId'=>$data['id'],'relatedTable'=>$this->model]);
            } else {
                foreach ($data['imgIds'] as $v) {
                    Image::where('id',$v)->update(['relatedId'=>$data['id'],'relatedTable'=>$this->model]);
                }
            }
        }
        $member = Member::where('id',$povertymemberId)->field('cityId,xianId,townId,villageId')->find();
        $povertypartyId = Povertymember::where('memberId',$povertymemberId)->value('aidingId');
        $data['cityId'] = $member['cityId'];
        $data['xianId'] = $member['xianId'];
        $data['townId'] = $member['townId'];
        $data['villageId'] = $member['villageId'];
        $data['povertypartyId'] = $povertypartyId;
        $data['createDate'] = time();
        $data['updateDate'] = time();
        $data['createOper'] = $this->admin->id;
        $data['updateOper'] = $this->admin->id;
        $model = model($this->model);
        $result = $model->create($data);
        
        if ($result) {
            return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
        } else {
            return $this->error('出错，请重试');
        }
    }

    public function edit(Request $request) {
        $this->assign('imgList',[]);
        $this->assign('imgcount',0);
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = '') {
        $data = $request->param();
        $id = $data['id'];
        if(isset($data['imgIds'])) {
            $imgList = Image::where(['relatedId'=>$id])->select();
            $imgId = [];
            foreach ((array)$imgList as $v) {  //原有图片ID组
                $imgId[] = $v['id'];
            }
            $addImg = array_diff((array)$data['imgIds'],$imgId);   //需要添加的图片ID组
            foreach ((array)$addImg as $i) {
                Image::where('id',$i)->update(['relatedId'=>$id,'relatedTable'=>$this->model]);
            }
            // if(isset($data['imgIds']))
            unset($data['imgIds']);
            unset($data['imgUrl']);
        }

        $data['updateDate'] = time();
        $data['updateOper'] = $this->admin->id;
        $model = model($this->model);
        $result = $model->where('id', $id)->update($data);
        if ($result) {
            return $this->success('编辑成功！', 'admin/' . strtolower($this->model) . '/index');
        } else {
            return $this->error('出错，请重试');
        }
    }

    public function delete() {
        $model = model($this->model);

        $request = $this->request;
        $id = $request->param('id');

        $info = $model->find(['id', $id]);
        if(!$info){
            return redirect('admin/'.$this->model.'/index');
        }
        $result = $model->where('id', $id)->update(['isDelete' => 1]);
        if($result !== false) {
            $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
            Common::adminLog($request, $logInfo);
            return $this->success('删除成功！', url('admin/' . strtolower($this->model) . '/index'));
        }
        return $this->error('删除失败！', url('admin/' . strtolower($this->model) . '/index'));
    }

}