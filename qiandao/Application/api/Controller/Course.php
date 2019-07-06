<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Course as CoursedMdl;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 签到记录
 */
class Course extends BaseController {

    public function index()
    {   
        //get 
        $param = $request->param();
        $model = model('Course');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];
        // 排序条件
        $columns = $param['order'][0]['column'];
        $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];
        $list = $model->alias('a')->join('user b','a.studentId=b.id')field('a.*,b.name')->where('a.classId','=',$param['id'])->limit($start, $length)->order($order)->select();
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list); 
    }


    //  post 添加班课
    public function save(){
        $data = input('post.');
        $result = CoursedMdl::save($data);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }

    //修改班课
    public function update($id = 0){
        $data = input('put.');
        $result = CoursedMdl::where('id',$data['id'])->update($data);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }

    //  delete 删除班课
    public function delete(){
        $data = input('post.')
        $result = CoursedMdl::delete($data['id']);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }


}