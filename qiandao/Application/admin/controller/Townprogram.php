<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/6 0006
 * Time: 下午 3:00
 */
namespace app\admin\controller;


use app\common\BaseHelper;
use think\Db;
use think\Exception;
use think\Request;
use app\common\BaseHelper as Helper;

class Townprogram extends Base {

    public function _initialize(){
        parent::_initialize();
        if(session('ADMIN')['name'] != 'admin'){
            return $this->error('权限不够','admin/auth/login');
        }

        $this->searchFields = [
            'operator_loginname' => [
                'label'     => '用户名',
                'field'     => 'operator_loginname',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'

            ],
        ];
    }

    public function index(){
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
            foreach ($list as &$v){
                $v['city'] = getAreaName($v['cityId']);
                $v['xian'] = getAreaName($v['xianId']);
                $v['town'] = getAreaName($v['townId']);
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

        return $this->fetch($this->indexView, [
            'pageSize' => ['url' => fullUrl($request->path())],
            'searchFields' => $this->searchFields,
            'param' => $request->param()
        ]);
    }

    public function addPost(Request $request,$redirect = ''){

        if(request()->isPost()){
            Db::startTrans();
            try{
                $model = model($this->model);
                $data = $request->param();
                if(!$this->checkLoginName($data['operator_loginname'])){
                    return $this->error('商户账号已存在，请重新填写');
                }
                if(!$this->checktownexist($data['cityId'],$data['xianId'],$data['townId'])){
                    return $this->error('此城镇小程序已经登记过');
                }
                $data['id'] = BaseHelper::getUUID();
                $model->save($data);
                $OperatorModel = new \app\admin\model\Operator();
                $OperatorModel->id = BaseHelper::getUUID();
                $OperatorModel->loginName = $data['operator_loginname'];
                $OperatorModel->password = Helper::passwordEncrypt($data['pass_one']);
                $OperatorModel->cityId = $data['cityId'];
                $OperatorModel->xianId = $data['xianId'];
                $OperatorModel->townId = $data['townId'];
                $OperatorModel->townPid = $data['id'];
                $OperatorModel->type = 6;
                $OperatorModel->memberId = 0;
                $OperatorModel->save();
                Db::commit();
                return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
            }catch (Exception $ex){
                Db::rollback();
                return $this->error('添加失败！','admin/' . strtolower($this->model) . '/index');
            }
        }
    }

    public function edit(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }

        $this->assign('area', $this->getArea($info));

        return $this->view->fetch($this->editView, [
            'info' => $info
        ]);
    }

    private function checktownexist($cityId,$xianId,$townId){
        $model = model($this->model);
        $result = $model->where(['cityId'=>$cityId,'xianId'=>$xianId,'townId'=>$townId,'isActive'=>1])->find();
        if(!$result){
            return true;
        }
        return false;
    }

    private function checkLoginName($loginName)
    {
        $model = model('Operator');
        $result = $model->where(['loginName' => $loginName, 'isDelete' => 2])->field('loginName')->find();
        if(!$result){
            return true;
        }
        return false;
    }

    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['operator_loginname']) && $filter['operator_loginname']){
                $where['operator_loginname'] = ['like', '%'.$filter['operator_loginname'].'%'];
            }
        }

        return $where;
    }
}