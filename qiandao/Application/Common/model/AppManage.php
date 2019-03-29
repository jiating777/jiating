<?php

namespace app\common\model;

use think\Model;

class AppManage extends Otherdatabase
{

    // 当前模型名称 不带前缀
    protected $name = 'app_manage';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    public static function updateStatusByAuditStatus($businessMiniProgramStatus,$AppManage){
        if (isset($businessMiniProgramStatus['errcode']) && $businessMiniProgramStatus['errcode'] == 0) {
            switch ($businessMiniProgramStatus['status']) {
                //审核成功
                case 0:
                    $AppManage->wxStatus = 4;
                    $AppManage->save();

                    //发布代码
                    MiniProgramHelp::releaseCode($AppManage->id);
                    break;
                //审核失败
                case 1:
                    $AppManage->wxStatus = 3;
                    $AppManage->auditMsg = $businessMiniProgramStatus['reason'];
                    $AppManage->save();
                    break;
                //审核中
                default:
                    break;
            }
        }
    }

}