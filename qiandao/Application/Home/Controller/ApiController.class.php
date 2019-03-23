<?php

namespace Home\Controller;

use Think\Controller\RestController;

class ApiController extends RestController{
	public function rest() {
		$data = array('name'=>'admin','password'=>'123456');
		// echo $_GET['id'];
		// print_r($_GET);
		return $this->response($data,'json');
	}
}