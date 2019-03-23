<?php

namespace api\controller;


use Think\Controller;
use Think\Request;

class BaseController extends Controller
{

    const MSG_SUCCESS = '请求成功';
    const NOT_DATA = '暂无数据';
    const NOT_PARAM = '缺少参数';


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
            $userInfo = User::alias('a')->where(['a.id' => $where['id']])->join('__MEMBER__ m', 'a.memberId = m.id')->find();

            return $userInfo;
        }

        return $userInfo;
    }

}