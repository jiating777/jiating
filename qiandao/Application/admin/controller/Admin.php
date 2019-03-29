<?php

namespace app\admin\controller;

use app\common\BaseHelper;

use think\Request;

class Admin extends Base
{

    /**
     * 个人中心
     */
    public function profile(Request $request){
        $model = model($this->model);

        //save data
        if ($request->isAjax()) {
            $loginName = trim($this->request->param('loginName'));
            $password = trim($this->request->param('password'));
            $newPassword = trim($this->request->param('newpassword'));
            $confirmPassword = trim($this->request->param('confirmpassword'));

            if(!$loginName){
                return json(['code' => 0, 'msg' => '登录名不能为空！']);
            }
            if(!$password){
                return json(['code' => 0, 'msg' => '原始密码不能为空！']);
            }
            if(!$newPassword){
                return json(['code' => 0, 'msg' => '新密码不能为空！']);
            }
            if($newPassword != $confirmPassword){
                return json(['code' => 0, 'msg' => '两次密码不一致！']);
            }

            if($this->admin->password != BaseHelper::passwordEncrypt($password)){
                return json(['code' => 0, 'msg' => '原密码不正确！']);
            }

            // Update data
            $data['loginName'] = $loginName;
            $data['password'] = BaseHelper::passwordEncrypt($newPassword);
            $result = $model->where('id', $this->admin->id)->update($data);

            if ($result !== false) {
                return json(['code' => 1, 'msg' => '保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '保存失败！']);
            }
        }

        return $this->view->fetch();
    }

}