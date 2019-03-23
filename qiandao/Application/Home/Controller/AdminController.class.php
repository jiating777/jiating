<?php

namespace Home\Controller;

use Think\Controller;


class AdminController extends Controller {

    public function index() {
    	//先判断是否登录，若未登录，调转到login
    	$admin = session('admin');
    	if($admin) {
            $this->assign('adminInfo');
    		$this->display();
    	} else {
    		$this->login();
    	}
    }


}