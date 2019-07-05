<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;
use app\common\Common;

use app\admin\model\Menu AS MenuMdl;

/**
 * Class Menu
 * @package app\admin\controller
 */
class Menu extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = 'menu';
        $this->redirect = '/menu';
        $this->searchFields = [
            'name' => [
                'label'     => '菜单名称',
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
        $menus = MenuMdl::field('id,name,parentId')->where(['status'=>1,'parentId'=>0])->select();
        $parentIds = [];
        foreach ($menus as $k => $v) {
            $parentIds[$v['id']] = $v['name'];
        }
        $this->assign('parentIds',$parentIds);
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect=''){
        $redirect = url($this->redirect);
        return parent::addPost2($request,$redirect);
    }

    public function edit(Request $request) {
        $menus = MenuMdl::field('id,name,parentId')->where(['status'=>1,'parentId'=>0])->select();
        $parentIds = [];
        foreach ($menus as $k => $v) {
            $parentIds[$v['id']] = $v['name'];
        }
        $this->assign('parentIds',$parentIds);
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = ''){
        $param = $this->request->param();
        $model = model($this->model);
        
        $data = $this->request->param();
        $data = [
            'name'=>$data['name'],
            'parentId'=>$data['parentId'],
            'imgPath'=>$data['imgPath'],
            'url'=>$data['url'],
            'target'=>$data['target'],
            'sorting'=>$data['sorting'],
            'status'=>$data['status']
        ];
        // dump($data);die;
        $save = $model->where(['id'=>$param['id']])->update($data);
        if($save) {
            return $this->success('编辑成功！', '/menu');
        }
        return $this->error('编辑失败！',url('/menu'));
        
    }

}