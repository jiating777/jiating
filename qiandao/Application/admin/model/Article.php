<?php

/**
 * 文章模型
 */
namespace app\admin\model;

use app\lib\Qiniu;

use app\admin\model\Articletype;

class Article extends Base
{
    public function typeName() {  //关联文章类型表
        return $this->belongsTo('Articletype','typeId');
    }

    public function getTypeIdAttr($data) {
        return Articletype::where('id',$data)->value('name');
    }

    public static function _after_insert($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'article']);
        }
        if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){  //处理详情图
            foreach ((array)$data['detailImgIds'] as $v) {
                Image::where('id', $v)->update(['relatedId' => $data['id'], 'relatedTable' => 'article']);
            }            
        }
    }

    public static function _after_update($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'article']);
        }
        if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){  //处理详情图
            foreach ((array)$data['detailImgIds'] as $v) {
                Image::where('id', $v)->update(['relatedId' => $data['id'], 'relatedTable' => 'article']);
            }            
        }
    }

    public static function _after_delete($id){  //删除图片记录及空间中的图片,删除相关评论和点赞数据
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

        $delCommenet = \app\admin\model\Articlecomment::where('articleId',$id)->delete();
        $delLike = \app\admin\model\Articlelike::where('articleId',$id)->delete();

    }
}   