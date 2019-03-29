<?php

/**
 * 小程序登录用户表
 */
namespace app\admin\model;

class User extends Base {

    //昵称字符转换
    public function getnickNameAttr($data){
        return urldecode($data);
    }
    public static function getByOpenID($openid){
        $user = self::where('openid','=',$openid)->find();
        return $user;
    }
}