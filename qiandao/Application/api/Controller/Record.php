<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Record as RecordMdl;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 签到记录
 */
class Record extends BaseController {

    public function index()
    {   
        //get 
        $param = $request->param();
        $model = model('Record');

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


    //  post 签到
    public function save(){
        $data = input('post.');
        $result = RecordMdl::save($data);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }


}