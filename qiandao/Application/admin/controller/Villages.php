<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;
use think\Db;
use app\admin\model\Operator;
use app\admin\model\Area;

class Villages extends Base
{
    public function _initialize() {
        parent::_initialize();
        $this->model = 'villages';
        $this->redirect = 'admin/village/index';

        $defaultWhere = $this->getDefaultWhere();
        $this->defaultWhere = $defaultWhere;
    }

    public function index(){
        return parent::index();
    }

    public function add(Request $request){
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect='') {
        return parent::addPost($request, $redirect);
        
    }

    public function edit(Request $request) {
        $model = model($this->model);
        $id = $request->param('id');
        $info = $model->where('id',$id)->find();
        // dump($info);die;
        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }

        $areaMdl = new Area();
        $city = BaseHelper::makeOptions(
            $areaMdl,
            ['level' => 1],
            ['id' => 'asc']
        );
        $this->assign('city',$city);

        $ids = $info['cityId'].','.$info['xianId'].','.$info['townId'];
        $area = Area::where('id','in',$ids)->field('name')->select();
        $str = [];
        foreach ($area as $v) {
            $str[] = $v['name'];
        }
        $this->assign('area',implode('-', $str));
        return $this->view->fetch($this->editView, ['info' => $info]);

    }

    public function editPost(Request $request, $redirect='') {
        return parent::editPost($request, $redirect);        
    }


}