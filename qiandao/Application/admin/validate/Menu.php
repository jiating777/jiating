<?php

namespace app\admin\validate;

class Menu extends Base
{
    protected $rule = [
        ['name', 'require|isNotEmpty', '菜单名为必填项|菜单名不能为空'],
        ['url', 'require|isNotEmpty', '链接为必填项|链接不能为空'],
    ];

}