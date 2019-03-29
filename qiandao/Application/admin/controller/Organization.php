<?php

namespace app\admin\controller;

use app\common\Common;
use app\common\BaseHelper;

use app\admin\model\Member;

use think\Request;

class Organization extends Base
{

    protected $redirect = '';

    public function _initialize()
    {
        parent::_initialize();

        $this->model = 'organization';
        //$this->indexView = 'index';
        $this->redirect = 'admin/organization/index';

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = ['neq', 1];
        $this->defaultWhere = $defaultWhere;
    }

    public function index(){

        return parent::index();
    }

    /**
     * Add
     */
    public function add(Request $request){

        return parent::add($request);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect = ''){
        $redirect = $this->redirect;

        return parent::addPost($request, $redirect);
    }

    /**
     * Edit
     */
    public function edit(Request $request){

        return parent::edit($request);
    }

    /**
     * Edit Post
     */
    public function editPost(Request $request, $redirect = ''){
        $redirect = $this->redirect;

        return parent::editPost($request, $redirect);
    }

    /**
     * Delete
     */
    public function delete(){
        $model = model($this->model);

        $request = $this->request;
        $id = $request->param('id');
        if($id){
            //删除一条
            $info = $model->find(['id', $id]);
            if(!$info){
                return redirect('admin/'.$this->model.'/index');
            }

            if(isset($this->admin->organizationId) && $id == $this->admin->organizationId){
                return $this->error('当前登录账号属于当前组织，不能删除！', url('admin/organization/index'));
            }

            //查询是否有成员，若有，则不可删除
            // $find = Partyorganization::where(['organizationId'=>$id,'isDelete'=>2])->find();   
            $find = Member::where(['organizationId' => $id, 'isDelete' => 2])->find();
            if($find) {
                return $this->error('此组织下有成员，不能删除', url('admin/organization/index'));
            } else {
                $result = $model->where('id', $id)->update(['isDelete' => 1]);
            }

            if($result !== false) {

                $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);
            }

            if($result !== false){
                return $this->success('删除成功！', url('admin/organization/index'));
            }else{
                return $this->error('删除失败！', url('admin/organization/index'));
            }
        }
    }

}