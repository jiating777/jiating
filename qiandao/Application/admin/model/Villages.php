<?php

namespace app\admin\model;

use think\Model;

class Villages extends Base
{

    /**
     * 根据条件得到 select options
     */
    /*public static function makeOptions($where, $field = '', $order = [])
    {
        $options = [];
        $results = Villages::where($where)->field($field)->order($order)->select();
        foreach ($results as $result) {
            $options[$result->id] = $result->name;
        }

        return $options;
    }*/
}
