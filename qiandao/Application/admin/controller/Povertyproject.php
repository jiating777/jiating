<?php 

/**
 * 精准扶贫-扶贫项目
 */

namespace app\admin\controller;

use app\admin\model\Image;
use \think\Request;
use think\Db;
use app\common\Common;
use app\common\BaseHelper as Helper;

use app\admin\model\Member;

class Povertyproject extends Base{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'povertyproject';

        /*$defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = ['neq', 1];
        $this->defaultWhere = $defaultWhere;*/
        switch ($this->admin->type) {
            case 0:
                $this->defaultWhere = ['a.isDelete' => ['neq', 1]];
                break;
            case 1:
                $this->defaultWhere = ['a.cityId'=>$this->admin->cityId,'a.isDelete' => ['neq', 1]];
                break;
            case 2:
                $this->defaultWhere = ['a.xianId'=>$this->admin->xianId,'a.isDelete' => ['neq', 1]];
                break;
            case 3:
            case 6:
                $this->defaultWhere = ['a.townId'=>$this->admin->townId,'a.isDelete' => ['neq', 1]];
                break;
            case 4:
                $this->defaultWhere = ['a.villageId'=>$this->admin->villageId,'a.isDelete' => ['neq', 1]];
                break;
            
            default:
                $this->defaultWhere = ['a.isDelete' => ['neq', 1]];
                break;
        }
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
                $where = array_merge($this->defaultWhere, $where);
            }

            $list = $model->alias('a')->field('a.*,b.name')->join('member b','a.memberId=b.id')->where($where)->limit($start, $length)->order($order)->select();
            $count = $model->alias('a')->where($where)->count();

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

    public function add(Request $request){
        $typeList = \app\admin\model\Povertyprojecttype::order('sorting')->select();
        $this->assign('typeList',$typeList);
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect = ''){
        return parent::addPost($request, $redirect);
    }

    public function edit(Request $request){
        $typeList = \app\admin\model\Povertyprojecttype::order('sorting')->select();
        $imagesList = Image::where(['relatedId' => $request->param('id'), 'tag' => 'imglist'])->select();
        $this->assign('typeList',$typeList);
        $this->assign('imgList',$imagesList);
        $this->assign('imgcount',count($imagesList));
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = ''){
        return parent::editPost($request, $redirect);
    }

    public function detail(Request $request) {
        $model = model($this->model);
        // dump($model);die;
        $id = $request->param('id');
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', url($this->redirect));
        }
        $info->organization = $this->getOrganization($info->createOper);
        $info['image'] = Image::where(['relatedId'=>$id,'tag'=>'imglist'])->select();
        return $this->view->fetch('detail', [
            'info' => $info
        ]);
    }

    /**
     * 筛选条件
     */
    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['cityId']) && $filter['cityId']){
                $where['a.cityId'] = $filter['cityId'];
            }
            if(isset($filter['xianId']) && $filter['xianId']){
                $where['a.xianId'] = $filter['xianId'];
            }
            if(isset($filter['townId']) && $filter['townId']){
                $where['a.townId'] = $filter['townId'];
            }
            if(isset($filter['villageId']) && $filter['villageId']){
                $where['a.villageId'] = $filter['villageId'];
            }
        }

        return $where;
    }

    //进展详情列表
    public function detailList() {
        $request = $this->request;
        $param = $request->param();
        $model = model('povertyprojectdetail');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        if(empty($param['projectId'])){
            $result = [
                'status' => '0',
                'draw' => $param['draw'],
                'data' => [],
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
            ];
            return json($result);
        }
        $where = [
            'isDelete' => 2,
            'projectId' => $param['projectId']
        ];

        $list = $model->where($where)->limit($start, $length)->select();
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

    //编辑进展
    public function detailEdit(Request $request) {
        $model = model('povertyprojectdetail');
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);
        $info['image'] = Image::where(['relatedId'=>$id])->select();
        return json($info);
    }

    //进展保存
    public function detailSave(Request $request) {
        $model = model('povertyprojectdetail');
        $data = $request->post();
        Db::startTrans();
        try {
            if(empty($data['id']) || !isset($data['id']) ) {  //添加操作
                $data['createDate'] = time();
                $data['createOper'] = $this->admin->id;
                $data['id'] = Helper::getUUID();
                if(isset($data['imgIds']) && !empty($data['imgIds'])){
                    foreach ((array)$data['imgIds'] as $item) {
                        Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'povertyprojectdetail', 'tag' => 'imglist']);
                    }
                }
                unset($data['imgIds']);
                unset($data['imgUrl']);
                $result = $model->insert($data);
                $row = model($this->model)->where('id',$data['projectId'])->find();
                $total = $row['type'] == 1 ? $row['productNum'] : $row['money'];
                $number = $row['type'] == 1 ? $data['donateNum'] : $data['donateMoney'];
                $update = ['totalDonate'=>($row['totalDonate']+$number)];
                if(($row['totalDonate']+$number) >= $total ) {
                    $update['status'] = 2;
                }
            } else {  //编辑操作
                if(isset($data['imgIds']) && !empty($data['imgIds'])){
                    foreach ((array)$data['imgIds'] as $item) {
                        Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'povertyprojectdetail', 'tag' => 'imglist']);
                    }
                }
                unset($data['imgIds']);
                unset($data['imgUrl']);
                $data['updateDate'] = time();
                $data['updateOper'] = $this->admin->id;
                $row = model($this->model)->where('id',$data['projectId'])->find();
                $total = $row['type'] == 1 ? $row['productNum'] : $row['money'];
                $number = $row['type'] == 1 ? $data['donateNum'] : $data['donateMoney'];
                $oldNumber = $row['type'] == 1 ? $data['oldNum'] : $data['oldMoney'];
                $number = $number - $oldNumber;
                $update['totalDonate'] = $row['totalDonate']+$number;
                $update['status'] = ($row['totalDonate']+$number) >= $total ? 2 : 1;
                unset($data['oldNum']);
                unset($data['oldMoney']);

                $result = $model->where('id',$data['id'])->update($data);
            }
            
            Db::commit();
            model($this->model)->where('id',$data['projectId'])->update($update);
            return $this->success('保存成功');
        } catch (Exception $e) {
            Db::rollback();
            return $this->error('网络出错');
        }
    }

    //删除进展
    public function deleteDetail() {
        $model = model('povertyprojectdetail');

        $request = $this->request;
        $id = $request->param('id');

        $info = $model->find(['id', $id]);
        if(!$info){
            return redirect('admin/'.$this->model.'/detail');
        }
        try{
            $row = model($this->model)->where('id',$info['projectId'])->find();
            $total = $row['type'] == 1 ? $row['productNum'] : $row['money'];
            $number = $row['type'] == 1 ? $info['donamteNum'] : $info['donateMoney'];
            $update = ['totalDonate'=>($row['totalDonate']-$number)];
            if(($row['totalDonate']-$number) < $total ) {
                $update['status'] = 1;
            }
            model($this->model)->where('id',$info['projectId'])->update($update);
            $result = $model->where('id', $id)->update(['isDelete' => 1]);
            $logInfo = $this->admin->name . '删除了1条povertyprojectdetail数据。';
            Common::adminLog($request, $logInfo);
            return $this->success('删除成功！', url('admin/' . strtolower($this->model) . '/detail'));
        } catch (Exception $e) {
            return $this->error('网络出错', url('admin/' . strtolower($this->model) . '/detail'));
        }
        
    }

    //删除扶贫项目
    public function delete() {  //TODO 删除关联进度
        return $this->error('TODO', url('admin/' . strtolower($this->model) . '/index'));
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
