<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use think\Request;
use think\Db;
use app\admin\model\RoleModel;
use app\admin\model\Menu;

class Role extends Base
{
    public function _initialize() {
        parent::_initialize();
        $this->model = 'role';
        $this->redirect = '/orle';
        $this->searchFields = [
            'name' => [
                'label'     => '角色名称',
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
        $menus = Menu::field('id,name,parentId')->where(['status'=>1,'parentId'=>0])->select();
        $menuMap = [];
        foreach ($menus as $k => $v) {
            $menuMap[$v['parentId']][] = $v->toArray();
        }
        // dump($menuMap);die;
        $this->assign('menu',$menuMap);
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect='') {
        $param = $this->request->param();
        $model = model('Role');
        $menuIds = (array)$param['menuId'];
        foreach ($menuIds as $v) {
            $find = Menu::where('id',$v)->find();
            if($find['parentId'] != 0) {
                $menuIds[] = $find['parentId'];
            }
        }
        $data = [
            'id' => Helper::getUUID(),
            'createDate' => time(),
            'name' => $param['name'],
            'typeName' => $param['typeName'],
            'appPageId' => implode(",", $menuIds)
        ];
        if($model->insert($data)) {
            return $this->success('添加成功！', '/role');
        }
        return $this->error('添加失败！',url('/role'));
        
    }

    public function edit(Request $request) {
        $menus = Menu::field('id,name,parentId')->where(['status'=>1,'parentId'=>0])->select();
        $menuMap = [];
        foreach ($menus as $k => $v) {
            $menuMap[$v['parentId']][] = $v->toArray();
        }
        $this->assign('menu',$menuMap);
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = '') {
        $model = model('Role');
        $param = $this->request->param();
        $menuIds = (array)$param['menuId'];
        foreach ($menuIds as $v) {
            $find = Menu::where('id',$v)->find();
            if($find['parentId'] != 0) {
                $menuIds[] = $find['parentId'];
            }
        }
        $data = [
            'name' => $param['name'],
            'appPageId' => implode(',', array_unique($menuIds)),
            'typeName' => $param['typeName'],
        ];
        $save = $model->where(['id'=>$param['id']])->update($data);
        if($save) {
            return $this->success('保存成功！', '/role');
        } else{
            return $this->error('保存失败！',url('/role'));
        }
    }

    public function delete()
    {
        $model = model($this->model);

        $request = $this->request;
        $id = $request->param('id');

        $info = $model->find(['id', $id]);
        if(!$info){
            return redirect('/role');
        }
        // 删除前判断其类型下是否有用户，若有，不允许删除
        $result = $model->where('id', $id)->delete();
        if($result !== false) {

            $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
            Common::adminLog($request, $logInfo);
            return $this->success('删除成功！', url('/role'));
        }
        return $this->error('删除失败！', url('/role'));
    }


}