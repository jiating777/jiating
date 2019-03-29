<?php

/**
 * 帮扶工作表
 */
namespace app\admin\model;

use think\Model;
use app\lib\Qiniu;

class Povertywork extends Base {

    public function member1() {  //关联村民表,贫困户
        return $this->belongsTo('member','povertymemberId');
    }

    public function member2() {  //关联村民表，帮扶干部
        return $this->belongsTo('member','povertypartyId');
    }

    public function image() { //图片关联表
        return $this->hasMany('Image','relatedId');
    }

    public static function _after_delete($id){  //删除图片记录及空间中的图片
        if(is_array($id)){
            $list = Image::where('relatedTable', 'article')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'article')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            $res = $Qiniu->delImg($key);  //删除图片
            Image::where('id',$v['id'])->delete();
        }
    } 
} 