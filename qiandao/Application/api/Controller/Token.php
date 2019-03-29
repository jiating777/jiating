<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 上午 11:22
 */
namespace app\api\controller;

use app\api\service\UserToken;
use app\common\validate\TokenGet;

class Token{

    /**
     * 获取小程序登录令牌，获取openid
     * @param string $code
     * @param string $prAppid
     * @return \think\response\Json
     */
    public function getToken($code='',$prAppid=''){
        (new TokenGet())->goCheck();
        $ut = new UserToken($code,$prAppid);
        $token = $ut->get();
        if($token){
            return show(config('status.SUCCESS_STATUS'),'成功获取到openid',$token);
        }else{
            return show(config('status.ERROR_STATUS'),'获取openid失败',[]);
        }

    }


}