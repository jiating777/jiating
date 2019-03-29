<?php

/**
 * 帮扶工作表
 */
namespace app\admin\model;

use think\Model;
use app\lib\Qiniu;

class Povertyproject extends Base {

    public function image() { //图片关联表
        return $this->hasMany('Image','relatedId');
    }

    public function member() {
        return $this->hasOne('Member', 'id', 'memberId');
    }

    public function memberaid() {
        return $this->hasOne('Member', 'id', 'aidingId');
    }

    public static function _after_insert($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'povertyproject']);
        }

        //多图
        if(isset($data['imgIds']) && !empty($data['imgIds'])){
            foreach ((array)$data['imgIds'] as $item) {
                Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'povertyproject', 'tag' => 'imglist']);
            }
        }
    }

    public static function _after_update($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'povertyproject']);
        }
        //多图
        if(isset($data['imgIds']) && !empty($data['imgIds'])){
            foreach ((array)$data['imgIds'] as $item) {
                Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'povertyproject', 'tag' => 'imglist']);
            }
        }
    }

    public static function _after_delete($id){  //删除图片记录及空间中的图片
        if(is_array($id)){
            $list = Image::where('relatedTable', 'povertyproject')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'povertyproject')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            $res = $Qiniu->delImg($key);  //删除图片
            Image::where('id',$v['id'])->delete();
        }
    } 
} 