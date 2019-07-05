<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Menu as MenuMdl;

use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 菜单列表
 */
class Menu extends BaseController {

    public function get()
    {
        $param = self::getHttpParam();
        $list = MenuMdl::select();

        $test = [
            'name' => 'test',
            'stuNum' => '180327000',
            'sex' => '男'
        ];
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list);
    }

}