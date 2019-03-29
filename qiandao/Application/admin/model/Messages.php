<?php

/**
 * 留言表
 */
namespace app\admin\model;

use think\Model;

class Messages extends Model {

    protected $options = ['isDelete'=>2];


    public function getCreateDateAttr($date){
        if(!$date){
            return '';
        }

        return date('Y-m-d H:i:s', $date);
    }

    public function image() { //图片关联表
        return $this->hasMany('Image','relatedId');
    }

    public function replay() {  //关联本表，获取村委回复
        return $this->hasMany('Messages','responderId')->where('isDelete',2);
    }

    public function member() {  //关联村民表
        return $this->belongsTo('member','userId');;
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