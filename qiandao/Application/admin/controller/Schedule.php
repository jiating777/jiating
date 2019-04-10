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

        return parent::index();
    }

    public function edit(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->alias('a')->join('school b','a.schoolId=b.id')->field('a.*,b.name,b.code')->where('a.id='.$id)->find();

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }
        return  $this->view->fetch($this->editView, ['info' => $info]);
    }

}