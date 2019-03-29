<?php

namespace app\admin\model;

use app\admin\model\Image;

class Meeting extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'meeting';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = false;


    /**
     * 开始时间
     */
    public function getStartTimeAttr($date){
        if(!$date){
            return '';
        }

        return date('Y-m-d H:i:s', $date);
    }

    /**
     * 结束时间
     */
    public function getEndTimeAttr($date){
        if(!$date){
            return '';
        }
        return date('Y-m-d H:i:s', $date);
    }

}