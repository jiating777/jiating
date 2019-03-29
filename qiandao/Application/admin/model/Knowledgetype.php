<?php

namespace app\admin\model;

use app\lib\Qiniu;

class Knowledgetype extends Base
{

    public static function _after_insert($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledgetype']);
        }
    }

    public static function _after_update($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledgetype']);
        }
    }


    public static function _after_delete($id){  //删除图片记录及空间中的图片
        if(is_array($id)){
            $list = Image::where('relatedTable', 'knowledgetype')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'knowledgetype')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            $res = $Qiniu->delImg($key);  //删除图片
            Image::where('id',$v['id'])->delete();
        }
    }

}