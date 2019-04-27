<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use think\Request;
use think\Db;
use app\admin\model\RoleModel;
use app\admin\model\Menu;

class Schedule extends Base
{
    public function _initialize() {
        parent::_initialize();
        $this->model = 'schedule';
        #$this->redirect = '/orle';
        $this->searchFields = [
            'name' => [
                'label'     => '学校名称',
                'field'     => 'schoolname',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'
            ],
        ];
    }

    public function index(){
        $request = $this->request;
        if($request->isAjax()){
            $param = $request->param();
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
                $where = array_merge($where, $this->defaultWhere);
            }
            $list = $model->join('school b','schedule.schoolId=b.id')->field('schedule.*,b.name,b.code')->where($where)->limit($start, $length)->order($order)->select();
            $count = $model->join('school b','schedule.schoolId=b.id')->where($where)->count();

            $result = [
                'status' => '1',
                'draw' => $param['draw'],
                'data' => $list,
                'recordsFiltered' => $count,
                'recordsTotal' => $count,
            ];

            return json($result);
        }

        return parent::index();
    }

    public function addPost(Request $request, $redirect='') {
        $param = $this->request->param();
        $model = model($this->model);
        $schedule = [];
        if(!is_array($param['begin'])) {
            $param['begin'] = array($param['begin']);
        }
        foreach ($param['begin'] as $key => $value) {            
            $tmp = ['start'=>$value,'end'=>$param['end'][$key]];
            $schedule[] = $tmp;
        }
        $data = [
            'id' => Helper::getUUID(),
            'createDate' => time(),
            'schoolId' => $param['schoolId'],
            'total' => $param['total'],
            'schedule' => serialize($schedule)
        ];
        if($model->insert($data)) {
            return $this->success('添加成功！', '/schedule');
        }
        return $this->error('添加失败！',url('/schedule'));
        
    }

    public function edit(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->alias('a')->join('school b','a.schoolId=b.id')->field('a.*,b.name,b.code')->where('a.id="'.$id.'"')->find();
        $info['schedule'] = unserialize($info['schedule']);
        $this->assign('schedule',$info['schedule']);
        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }
        return  $this->view->fetch($this->editView, ['info' => $info]);
    }

    public function editPost(Request $request, $redirect = ''){
        $param = $this->request->param();
        $model = model($this->model);

        foreach ($param['begin'] as $key => $value) {
            $tmp = ['start'=>$value,'end'=>$param['end'][$key]];
            $schedule[] = $tmp;
        }
        $data = [
            'updateDate' => time(),
            'total' => $param['total'],
            'schedule' => serialize($schedule)
        ];
        $save = $model->where(['id'=>$param['id']])->update($data);
        if($save) {
            return $this->success('编辑成功！', '/schedule');
        }
        return $this->error('编辑失败！',url('/schedule'));
        
    }

    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
        }
        if(isset($filter['schoolname']) && $filter['schoolname']) {
            $where['b.name'] = ['like','%'.$filter['schoolname'].'%'];
        }
        return $where;
    }

}