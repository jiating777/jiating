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
class School extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = 'school';
        $this->redirect = '/school';
        $this->searchFields = [
            'name' => [
                'label'     => '学校名称',
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

    public function add(Request $request){
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect=''){
        $redirect = url($this->redirect);
        return parent::addPost2($request,$redirect);
    }


    public function edit(Request $request) {

        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect=''){
        $redirect = url($this->redirect);
        return parent::editPost($request,$redirect);
    }


}