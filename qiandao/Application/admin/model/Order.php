<?php

namespace app\admin\model;

class Order extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'order';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';


    public function getStatusAttr($status){
        if(!$status){
            return '';
        }

        return show_order_status($status);
    }

}