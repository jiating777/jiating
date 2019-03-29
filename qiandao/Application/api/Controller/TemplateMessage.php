<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23 0023
 * Time: 下午 6:48
 */

namespace app\api\controller;

class TemplateMessage extends BaseController{
    public function gettemplatelist(){
        return \app\common\service\TemplateMessage::paysendmessage('a05660033fd04170738141f95ea29a50');
    }

}