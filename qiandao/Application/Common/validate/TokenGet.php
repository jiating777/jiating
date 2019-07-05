<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 上午 11:20
 */
namespace app\common\validate;

class TokenGet extends BaseValidate{
    protected $rule = [
        'code' => 'require|isNotEmpty',
        'prAppid' => 'require|isNotEmpty',
    ];

    protected $message = [
        'code' => '没有code不能获取Token！',
        'prAppid' => '没有小程序appid',
    ];
}