<?php

namespace app\admin\validate;

class Product extends Base
{
    protected $rule = [
        'title' => 'require|isNotEmpty',
        'price' => 'require|number',
        //'__token__' => 'token'
    ];

    protected $message = [
        'title' => '产品名不能为空',
        'price.require' => '价格不能为空',
        'price.number' => '价格必须为数字',
    ];
}