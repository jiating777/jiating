<?php

namespace app\admin\controller;

use app\admin\model\Memberhomeimg;
use app\admin\model\Organization;
use app\admin\model\Usersubmitexamine;
use app\common\BaseHelper;
use app\common\Common;
use app\admin\model\Ethnic;

use app\lib\exception\ParameterException;
use think\Db;
use think\Exception;
use think\Request;
use app\admin\model\Image;
use app\admin\model\Povertymember;

class Member extends Base
{

    protected $isIframe = false;

    public function _initialize()
    {
        $this->exceptAction = ['checkIdentityNumber'];

        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = ['neq', 1];
        $defaultWhere['shenheStatus'] = ['<', 2];  //过滤掉审核失败或审核中状态的数据
        $this->defaultWhere = $defaultWhere;

        $this->searchFields = [
            'name' => [
                'label'     => '姓名',
                'field'     => 'name',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'
            ],
        ];
    }

    public function index(){
        return parent::index();
    }

    /**
     * Add
     */
    public function add(Request $request){
        // 名族
        $ethnics = Ethnic::select();
        $ethnicList = collection($ethnics)->toArray();
        // 文化程度
        $educationList = get_education();
        // 健康状况
        $healthList = get_health();

        $povertyreason = povertyreason();

        $this->assign('povertyreason',$povertyreason);
        $this->assign('ethnicList', $ethnicList);
        $this->assign('educationList', $educationList);
        $this->assign('healthList', $healthList);

        return parent::add($request);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect = ''){
        if(!$request->post('birthday')){
            $request->post(['birthday' => substr($request->post('identityNumber'),6,4).'-'.substr($request->post('identityNumber'),10,2).'-'.substr($request->post('identityNumber'),12,2)]);
        }
        if($request->post('partyTime')){
            $request->post(['partyTime' => strtotime($request->post('partyTime'))]);
        }
        $request->post(['shenheStatus' => 0]);
        return parent::addPost($request, $redirect);
    }

    /**
     * Edit
     */
    public function edit(Request $request){
        // 名族
        $ethnics = Ethnic::select();
        $ethnicList = collection($ethnics)->toArray();
        // 文化程度
        $educationList = get_education();
        // 健康状况
        $healthList = get_health();
        $povertyreason = povertyreason();

        $this->assign('povertyreason',$povertyreason);

        $id = $request->param('id');
        $imagesList = Image::where(['relatedId' => $id, 'tag' => 'imglist'])->select();
        $this->assign('imagesList', $imagesList);
        $this->assign('imgcount', count($imagesList));

        $poverty = Povertymember::alias('a')->join('member b','a.aidingId=b.id')->field('a.*,b.name')->where('memberId',$id)->find();
        // dump($poverty);die;
        $this->assign('poverty',$poverty);

        $this->assign('ethnicList', $ethnicList);
        $this->assign('educationList', $educationList);
        $this->assign('healthList', $healthList);

        return parent::edit($request);
    }

    /**
     * Edit Post
     */
    public function editPost(Request $request, $redirect = ''){
        if(!$request->post('birthday')){
            $request->post(['birthday' => substr($request->post('identityNumber'),6,4).'-'.substr($request->post('identityNumber'),10,2).'-'.substr($request->post('identityNumber'),12,2)]);
        }

        if($request->post('partyTime')){
            $request->post(['partyTime' => strtotime($request->post('partyTime'))]);
        }

        return parent::editPost($request, $redirect);
    }

    /**
     * Delete
     */
    public function delete(){
        $model = model($this->model);
        $request = $this->request;
        $id = $request->param('id');

        $member = $model->where('id',$id)->find();

        //判断是否为管理员，若是，则提示
        $operator = \app\admin\model\Operator::where('memberId',$id)->find();
        if($operator) {
            return $this->error('该村民是管理员，请先去删除管理员');
        }

        if($member['isPoverty'] == 1) {// 是否是贫困户，若是，则删除povertymember表相关数据
            $delpoverty = db('povertymember')->where(['memberId' => $id])->delete();
        }

        $result = $model->where('id', $id)->update(['isDelete' => 1]);

        if($result !== false){
            // Query执行后的操作
            $model->_after_delete($id);

            $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
            Common::adminLog($request, $logInfo);
            return $this->success('删除成功！', 'admin/' . strtolower($request->controller()) . '/index');
        } else {
            return $this->error('删除失败！', 'admin/' . strtolower($this->model) . '/index');
        }
    }

    //居民审核列表
    public function shenhe(){
        $request = $this->request;
        $param = $request->param();
        // Reset filter
        if ($request->param('reset')) {
            return redirect(fullUrl($request->path()));
        }
        if($request->isAjax()){
            $model = model('Member');

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

            $list = $model->where($where)->where('shenheStatus',['=',1],['=',2],['=',3],'OR')->limit($start, $length)->order($order)->select();
            foreach ($list as &$v){
                $v['city'] = getAreaName($v['cityId']);
                $v['xian'] = getAreaName($v['xianId']);
                $v['town'] = getAreaName($v['townId']);
                $v['village'] = getVillageName($v['villageId']);
            }
            $count = $model->where($where)->where('shenheStatus',['=',1],['=',2],['=',3],'OR')->count();
            $result = [
                'status' => '1',
                'draw' => $param['draw'],
                'data' => $list,
                'recordsFiltered' => $count,
                'recordsTotal' => $count,
            ];

            return json($result);
        }

        return $this->fetch('shenhe_list', [
            'pageSize' => ['url' => fullUrl($request->path())],
            'searchFields' => $this->searchFields,
            'param' => $request->param()
        ]);
    }

    /**
     * 居民审核详情
     * @return mixed
     * @throws ParameterException
     */
    public function shenhedetail(){
        $param = Request::instance()->param();
        if(!$param['id']){
            throw new ParameterException([
                'msg' => '非法提交'
            ]);
        }
        $Member = \app\admin\model\Member::get($param['id']);
        $Member['city'] = getAreaName($Member['cityId']);
        $Member['xian'] = getAreaName($Member['xianId']);
        $Member['town'] = getAreaName($Member['townId']);
        $Member['village'] = '';
        if($Member->villageId){
            $Villages = \app\admin\model\Villages::get($Member->villageId);
            $Member['village'] = $Villages->name;
        }
        if($Member->homeImg){
            $homeImg = explode(',',$Member->homeImg);
        }
        $Member['organizationname'] = '';
        if($Member->isParty == 1 && $Member->organizationId){
            $Organization = Organization::get($Member->organizationId);
            if($Organization){
                $Member['organizationname'] = $Organization->name;
            }
        }
        return $this->fetch('shenhedetail',[
            'data' => $Member,
            'homeImg' => isset($homeImg)?$homeImg:''
        ]);
    }

    /**
     * 审核逻辑
     */
    public function shenhePost(Request $request){
        if(request()->isPost()){
            Db::startTrans();
            try{
                $param = $request->param();
                $Member = \app\admin\model\Member::get($param['id']);
                if($Member && $Member->shenheStatus == 2){
                    $Member->shenheStatus = 1;
                    $Member->failMsg = NULL;
                    $Member->save();
                    //图片关联处理
                    Image::whereIn('imgUrl',[$Member->avatar,$Member->cardImg])->update(['relatedId'=>$Member->id,'relatedTable'=>'member']);
                    Image::whereIn('imgUrl',explode(',', $Member->homeImg))->update(['relatedId'=>$Member->id,'relatedTable'=>'member','tag'=>'imglist']);

                    $User = \app\admin\model\User::get($Member->userId);
                    if($User){
                        $User->memberId = $Member->id;
                        $User->save();
                        Db::commit();
                        return $this->success('审核成功！', 'admin/' . strtolower($request->controller()) . '/shenhe');
                    }

                }else{
                    throw new ParameterException([
                        'msg' => '不能重复审核'
                    ]);
                }
            }catch (Exception $ex){
                Db::rollback();
                return $this->error('网络错误');
                
            }
        }
    }

    //审核不通过
    public function noshenhePost(Request $request){
        if(request()->isPost()){
            $param = $request->param();
            $Member = \app\admin\model\Member::get($param['id']);
            if($Member && $Member->shenheStatus == 2){
                $Member->shenheStatus = 3;
                $Member->failMsg = $param['failMsg'];
                $Member->save();
                return $this->success('提交成功！', 'admin/' . strtolower($request->controller()) . '/shenhe');
            }else{
                return $this->error('不能重复审核！', 'admin/' . strtolower($this->model) . '/shenhe');
            }
        }
    }

    /**
     * 检查身份证号是否已存在
     */
    public function checkIdentityNumber(){
        if($this->request->isAjax()){
            $identityNumber = $this->request->param('identityNumber');
            $model = model($this->model);

            $result = $model->where(['identityNumber' => $identityNumber, 'isDelete' => 2])->field('identityNumber')->find();

            if($result){
                return json(['status' => 1]);
            }else{
                return json(['status' => 0]);
            }
        }
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
                $where['cityId'] = $filter['cityId'];
            }
            if(isset($filter['xianId']) && $filter['xianId']){
                $where['xianId'] = $filter['xianId'];
            }
            if(isset($filter['townId']) && $filter['townId']){
                $where['townId'] = $filter['townId'];
            }
            if(isset($filter['villageId']) && $filter['villageId']){
                $where['villageId'] = $filter['villageId'];
            }

            if(isset($filter['name']) && $filter['name']){
                $where['name'] = ['like', '%'.$filter['name'].'%'];
            }
        }

        return $where;
    }

    /**
     * 导出数据
     */
    public function export(){
        $param = $this->request->param();

        $where = [
            'isDelete' => 2
        ];
        if(!empty($param['cityId'])){
            $where['cityId'] = $param['cityId'];
        }
        if(!empty($param['xianId'])){
            $where['xianId'] = $param['xianId'];
        }
        if(!empty($param['townId'])){
            $where['townId'] = $param['townId'];
        }
        if(!empty($param['villageId'])){
            $where['villageId'] = $param['villageId'];
        }
        if(!empty($param['name'])){
            $where['name'] = ['like', '%'.$param['name'].'%'];
        }

        $model = model($this->model);
        $data = $model->where($where)->order('createDate', 'asc')->select();
        if(count($data) <= 0){
            $this->error('没有数据！', url('admin/member/index'));
        }
        $areaDB = db('area');
        $villageDB = db('villages');
        $organizationDB = db('organization');
        foreach ($data as $key => $val) {
            // 所属城市
            $val['cityId'] = $areaDB->where(['id' => $val['cityId']])->value('name');
            // 所属县
            $val['xianId'] = $areaDB->where(['id' => $val['xianId']])->value('name');
            // 所属镇
            $val['townId'] = $areaDB->where(['id' => $val['townId']])->value('name');
            // 所属村
            $val['villageId'] = $villageDB->where(['id' => $val['villageId']])->value('name');
            // 是否为贫困户
            switch ($val['isPoverty']) {
                case 1: $val['isPoverty'] = '是'; break;
                case 2: $val['isPoverty'] = '否'; break;
                default : $val['isPoverty'] = '否'; break;
            }
            // 是否为党员
            switch ($val['isParty']) {
                case 1: $val['isParty'] = '是'; break;
                case 2: $val['isParty'] = '否'; break;
                default : $val['isParty'] = '否'; break;
            }
            // 所属组织
            if($val['organizationId']){
                $val['organizationId'] = $organizationDB->where(['id' => $val['organizationId']])->value('name');
            }else{
                $val['organizationId'] = '';
            }
            // 入党时间
            if($val['partyTime']){
                $val['partyTime'] = date('Y-m-d', $val['partyTime']);
            }else{
                $val['partyTime'] = '';
            }
            $val['identityNumber'] = (string) $val['identityNumber'];
        }
        $cellName = array(
            'A' => ['id', '用户编号'],
            'B' => ['cityId', '所属城市'],
            'C' => ['xianId', '所属县'],
            'D' => ['townId', '所属镇'],
            'E' => ['villageId', '所属村'],
            'F' => ['createDate', '创建时间'],
            'G' => ['updateDate', '更新时间'],
            'H' => ['name', '名称'],
            'I' => ['avatar', '头像'],
            'J' => ['gender', '性别'],
            'K' => ['birthday', '生日'],
            'L' => ['identityNumber', '身份证号'],
            'M' => ['mobile', '手机号码'],
            'N' => ['ethnicId', '名族'],
            'O' => ['education', '文化程度'],
            'P' => ['health', '健康状况'],
            'Q' => ['isPoverty', '是否为贫困户'],
            'R' => ['address', '详细地址'],
            'S' => ['isParty', '是否为党员'],
            'T' => ['organizationId', '所属组织'],
            'U' => ['job', '职务名称'],
            'V' => ['partyTime', '入党时间'],
        );

        $filePath = ROOT_PATH . 'public' . DS . 'upload' . DS . 'csv' . DS . 'member' . DS . date('Y-m-d');
        $fileName = $filePath . '/member_' . date('y-m-d-H-i-s');

        $excel = new Excel();
        $excel->exportExcel('村民', $data, $cellName, $filePath, $fileName);

        return redirect('admin/member/index');
    }

}