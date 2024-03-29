<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\User as UserMdl;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 用户
 */
class User extends BaseController {

    public function index()
    {   
        //get 
        $param = $request->param();

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];
        // 排序条件
        $columns = $param['order'][0]['column'];
        $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];
        $list = $model->join('userdetail b','b.userId=user.id')->join('school c','b.schoolId = c.id')->field('user.*,b.userNum,b.educational,c.name as schoolName')->where('type','=',$where['student'])->limit($start, $length)->order($order)->select();
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list); 
    }


    //修改
    public function update($id = 0){
        $data = input('put.');
        $result = UserMdl::save($data);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }

    //  post 新增
    public function save(){
        $data = input('post.');
        $result = UserMdl::save($data);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }



}