<?php

namespace app\admin\model;

use app\lib\Qiniu;

class Knowledge extends Base
{

    public static function _after_insert($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){  //封面图
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledge']);
        }
        if(isset($data['videoId']) && !empty($data['videoId'])){  //视频
            Image::where('id', $data['videoId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledge','tag'=>'video']);
        }

        //多图
        if(isset($data['imgIds']) && !empty($data['imgIds'])){
            foreach ((array)$data['imgIds'] as $item) {
                Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledge', 'tag' => 'imglist']);
            }
        }
    }

    public static function _after_update($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){  //封面图
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledge']);
        }
        if(isset($data['videoId']) && !empty($data['videoId'])){  //视频
            Image::where('id', $data['videoId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledge','tag'=>'video']);
        }
        //多图
        if(isset($data['imgIds']) && !empty($data['imgIds'])){
            foreach ((array)$data['imgIds'] as $item) {
                Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'knowledge', 'tag' => 'imglist']);
            }
        }
    }


    public static function _after_delete($id){  //删除图片记录及空间中的图片
        if(is_array($id)){
            $list = Image::where('relatedTable', 'knowledge')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'knowledge')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            $res = $Qiniu->delImg($key);  //删除图片
            Image::where('id',$v['id'])->delete();
        }
    }

    public function getTypeIdAttr($data){
        if(!$data){
            return '';
        }
        $ethnic = db('Knowledgetype')->where(['id' => $data])->value('name');
        return $ethnic;
    }

}