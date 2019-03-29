<?php 

/**
 * 党建工作-村党员展示
 */

namespace app\admin\controller;

use \think\Request;
use app\common\Common;

use app\admin\model\Area;
use app\admin\model\Villages;

class Partymember extends Base{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'partymember';
        $this->defaultWhere = ['isDelete'=>2]; //default where
        $this->defaultOrder = 'createDate DESC';
    }

    public function index() {
        return parent::index();
    }

    public function add(Request $request){
        $educationList = get_education();
        $this->assign('educationList', $educationList);

        return parent::add($request);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect = ''){
        if(!$request->post('partyTime')){
            $request->post(['partyTime' => date('Y-m-d')]);
        }

        return parent::addPost($request, $redirect);
    }

    /**
     * Edit
     */
    public function edit(Request $request){

        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }
        $this->assign('area', $this->getArea($info));

        $educationList = get_education();
        $this->assign('educationList', $educationList);

        $orgName = \app\admin\model\Organization::where('id',$info['organizationId'])->value('name');   //每个成员属于一个组织
        $info['organizationId'] = $orgName;

        $aid = \app\admin\model\Povertymember::alias('a')->join('member b','a.memberId=b.id')->field('a.*,b.name')->where('aidingId',$info['id'])->select();
        $this->assign('aid',$aid);

        return $this->view->fetch($this->editView, ['info' => $info]);
    }

    /**
     * Edit Post
     */
    public function editPost(Request $request, $redirect = ''){
        return parent::editPost($request, $redirect);
    }

    public function delete(){
        return $this->error('TODO', url('admin/'.$this->model.'/index'));
        $model = model($this->model);
        $request = $this->request;
        $id = $request->param('id');
        $info = $model->find(['id', $id]);
        if(!$info){
            return redirect('admin/'.$this->model.'/index');
        }

        if($info['memberId'] == $this->admin->memberId){
            return $this->error('不能删除当前登录账号', url('admin/'.$this->model.'/index'));
        }

        //查询是否有管理管理员，若是，则不可删除
        $find = Partyorganization::where(['organizationId'=>$id,'isDelete'=>2])->find();
        if($find) {
            return $this->error('此组织下有成员，不能删除', url('admin/index/index'));
        } else {
            $result = $model->where('id', $id)->update(['isDelete' => 1]);
        }

        if($result !== false) {

            $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
            Common::adminLog($request, $logInfo);
        }
        if($result !== false){
            return $this->success('删除成功！', url('admin/index/index'));
        }else{
            return $this->error('删除失败！', url('admin/index/index'));
        }
    }

}