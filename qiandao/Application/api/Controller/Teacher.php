<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\User as UserMdl;
use app\admin\model\Record;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 教师
 */
class Teacher extends BaseController {

    public function index()
    {   
        //get 
        $param = $request->param();
        $model = model('user');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];
        // 排序条件
        $columns = $param['order'][0]['column'];
        $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];
        $list = $model->join('userdetail b','b.userId=user.id')->join('school c','b.schoolId = c.id')->field('user.*,b.userNum,b.educational,c.name as schoolName')->where('type','=',$where['teacher'])->limit($start, $length)->order($order)->select();
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list); 
    }

    //  post 发起签到
    public function save(){
        $data = input('post.');
        $result = ['openQian'=>true,'courseId'=>$data['id']];
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
    }

    //  post 结束签到
    public function update(){
        $data = input('put.');
        $result = ['openQian'=>false,'courseId'=>$data['id']];
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
    }


}