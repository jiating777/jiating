<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;
use app\common\Common;

use app\admin\model\School AS SchoolMdl;

/**
 * Class Menu
 * @package app\admin\controller
 */
class Department extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = 'department';
        $this->redirect = '/department';
        $this->searchFields = [
            'name' => [
                'label'     => '院系名称',
                'field'     => 'name',
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

            $list = $model->alias('d')->join('school s','d.schoolId = s.id')->field('d.*,s.name as school')->where($where)->limit($start, $length)->order($order)->select();
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

    public function add(Request $request){
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect=''){
        $redirect = url($this->redirect);
        return parent::addPost2($request,$redirect);
    }


    public function edit(Request $request) {

        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->alias('a')->join('school b','a.schoolId=b.id')->field('a.*,b.name as school')->where('a.id="'.$id.'"')->find();
        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }
        return  $this->view->fetch($this->editView, ['info' => $info]);
    }

    public function editPost(Request $request, $redirect=''){
        $redirect = url($this->redirect);
        return parent::editPost($request,$redirect);
    }


}