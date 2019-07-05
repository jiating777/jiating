<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14 0014
 * Time: 下午 7:29
 */
namespace app\common\validate;


class WechatAuth extends BaseValidate{
    protected $rule = [
        'template_id' => 'number',
        'user_version' => 'require',
        'user_desc' => 'require',
        'tag' => 'require',
        'projectType' => 'require|checkprojectType'
    ];
    protected $message = [
        'template_id' => '模板id必须是数字',
        'user_version' => '代码版本号不能为空',
        'user_desc' => '代码描述不能为空',
        'tag' => '小程序的标签不能为空',
        'projectType' => '项目选择不正确'
    ];

    protected function checkprojectType($value){
        if($value == 1 || $value == 2){
            return true;
        }else{
            return false;
        }
    }
}