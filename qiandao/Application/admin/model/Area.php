<?php

namespace app\admin\model;

use think\Model;

class Area extends Base
{

    /**
     * 根据条件得到 select options
     */
    public static function makeOptions($where, $field = '', $order = [])
    {
        $options = [];
        $results = Area::where($where)->field($field)->order($order)->select();
        foreach ($results as $result) {
            $options[$result->id] = $result->name;
        }

        return $options;
    }
}