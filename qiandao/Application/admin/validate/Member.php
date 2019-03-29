<?php

namespace app\admin\validate;

class Member extends Base
{
    protected $rule = [
        'name' => 'require|isNotEmpty',
        'identityNumber' => 'require|isIdentityNumber',
        'mobile' => 'require|isMobile',
        //'__token__' => 'token'
    ];

    protected $message = [
        'name' => '姓名不能为空',
        'identityNumber.require' => '身份证号不能为空',
        'identityNumber.isIdentityNumber' => '身份证号格式不正确',
        'mobile.require' => '手机号码不能为空',
        'mobile.isMobile' => '手机号码格式不正确',
    ];

    protected function isIdentityNumber($value)
    {
        $rule = '/(^\d{15}$)|(^\d{17}([0-9]|X))$/isu';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|6|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}