<?php

namespace app\admin\validate;

class Organization extends Base
{
    protected $rule = [
        'name' => 'require|isNotEmpty',
        //'__token__' => 'token'
    ];

    protected $message = [
        'name' => '组织名称不能为空',
    ];
}