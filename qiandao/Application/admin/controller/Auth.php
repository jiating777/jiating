<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use app\admin\model\Admin;

use think\Exception;
use think\Controller;

class Auth extends Controller
{

    /**
     * Login
     */
    public function login(){
        $session_admin = session('ADMIN');
        if(!empty($session_admin->id)){
            $this->redirect('/index');
        }

        return $this->view->fetch();
    }

    /**
     * Ajax Login
     * @return \think\response\Json
     */
    public function doLogin(){
        try {
            if($this->request->isAjax()) {
                $loginName = trim($this->request->param('username'));
                $password = trim($this->request->param('password'));


                if(!$loginName){
                    throw new Exception('登录名不能为空！');
                }
                if(!$password){
                    throw new Exception('密码不能为空！');
                }
                $row = Admin::where(['loginName' => $loginName])->find();  //判断管理员类型
                if(!$row) {
                    throw new Exception('用户名不存在！');
                }
                if(BaseHelper::passwordEncrypt($password) != $row['password']){
                    return json(['code'=>0, 'msg'=>'密码错误！'], 200);
                }

                $code = 1;
                $msg = '登录成功';
                $row['name'] = $row['loginName'];
                session('ADMIN', $row);
                // 日志
                //Common::adminLog('管理员登录');
            }else{
                throw new Exception('请求方式不正确');
            }
        }catch (Exception $e) {
            $code = 0;
            $msg = $e->getMessage();
        }

        $data = array(
            'code'  => $code,
            'msg'   => $msg
        );
        return json($data, 200);
    }

    /**
     * Logout
     */
    public function logout(){
        session('ADMIN', null);

        // 清除浏览器 LocalStorage
        echo "<script>localStorage.clear();</script>";

        $this->redirect('/login');
    }

    /**
     * 检查用户名是否已存在
     */
    public function checkName(){
        $loginName = $this->request->param('loginName');
        $admin = Admin::where(['loginName' => $loginName, 'isDelete' => 2])->field('loginName')->find();

        if($admin){
            return json(['status' => 1]);
        }else{
            return json(['status' => 0]);
        }
    }

}