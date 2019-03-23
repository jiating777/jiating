<?php

namespace Home\Controller;

use Think\Controller;


class IndexController extends Controller {

    public function index() {
    	//先判断是否登录，若未登录，调转到login
    	$admin = session('admin');
    	if($admin) {
            $this->assign('adminInfo');
    		$this->display('main');
    	} else {
    		$this->login();
    	}
    }

    public function login() {
    	$this->display(':login');
    }

    public function doLogin() {
    	$name = $_POST['name'];
    	$psd = $_POST['password'];
    	$admin = M('Admin');
    	$row = $admin->where('name="'.$name.'"')->find();
    	if(!$row) {
    		$this->error('用户名未注册请联系管理员','',3);
    	}
    	if($row['password'] == md5($psd)) {
    		session('admin',$row);
    		$this->success('登录成功', 'index');
    	} else {
    		$this->error('密码错误','',3);
    	}
    }

    public function logOut() {
    	session('admin',null);
    	$this->redirect('login');
    }
}