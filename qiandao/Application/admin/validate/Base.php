<?php

namespace app\admin\validate;

use think\Validate;

/**
 * 验证器基类
 * Class Base
 */
class Base extends Validate
{

    /**
     * 验证不为空
     * @param $value
     * @return bool
     */
    protected function isNotEmpty($value){
        if(trim($value)) {
            return true;
        } else {
            return false;
        }
    }

}