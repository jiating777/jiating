<?php

namespace app\api\controller;


use app\admin\model\User;

use think\Controller;
use think\Request;

class BaseController extends Controller
{

    const MSG_SUCCESS = '请求成功';
    const NOT_DATA = '暂无数据';
    const NOT_PARAM = '缺少参数';


    protected function checkPrimaryScope(){
        TokenService::needPrimaryScope();

    }

    protected function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }

    public function getHttpParam()
    {
        $param = json_encode(Request::instance()->param());
        return json_decode($param);
    }

    /**
     * 获取用户信息
     *
     * @param $where
     * @return mixed
     */
    protected function getUserInfo($where){
        if(!$where['id']){
            return false;
        }
        $userInfo = User::where($where)->find();

        $memberId = $userInfo->memberId;
        if($memberId && $memberId !== 0){
            $userInfo = User::alias('a')->where(['a.id' => $where['id']])->join('userdetail b', 'a.id = b.useId')->find();

            return $userInfo;
        }

        return $userInfo;
    }



}