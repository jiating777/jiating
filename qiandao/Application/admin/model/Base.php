<?php

namespace app\admin\model;

use think\Model;

class Base extends Model
{

    //自动过滤掉不存在的字段
    protected $field = true;

    // 字段验证规则
    //protected $validate = true;

    // 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
    protected $autoWriteTimestamp = true;

    // 创建时间字段
    protected $createTime = 'createDate';

    // 更新时间字段
    protected $updateTime = 'updateDate';

    public function getCreateDateAttr($date){
        if(!$date){
            return '';
        }

        return date('Y-m-d H:i:s', $date);
    }

    public function getUpdateDateAttr($date){
        if(!$date){
            return '';
        }
        return date('Y-m-d H:i:s', $date);
    }

    /**
     * Query执行后的操作
     * @param $data
     */
    public function afterQuery($data){
        //
    }

    public static function _after_insert($data){
        //
    }

    public static function _after_update($data){
        //
    }

    public static function _after_delete($data){
        //
    }

}