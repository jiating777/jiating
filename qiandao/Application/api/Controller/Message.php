<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Messages;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 签到记录
 */
class Message extends BaseController {

    public function index()
    {   
        //get 
        $param = $request->param();
        $model = model('Messages');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];
        // 排序条件
        $columns = $param['order'][0]['column'];
        $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];
        $list = $model->limit($start, $length)->order($order)->select();
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list); 
    }


    //  post 留言
    public function save(){
        $data = input('post.');
        $result = Messages::save($data);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }


    //  delete 删除留言
    public function delete(){
        $data = input('put.')
        $result = Messages::delete($data['id']);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result); 
    }


}