<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\User as UserMdl;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 个人中心
 */
class User extends BaseController {

    public function get()
    {
        $param = self::getHttpParam();
        $test = [
            'name' => 'test',
            'stuNum' => '180327000',
            'sex' => '男'
        ];
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $test);
    }

}