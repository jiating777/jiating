<?php

namespace app\admin\model;

use app\lib\Qiniu;

use app\admin\model\Image;

class Producttype extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'producttype';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';


    public static function _after_delete($id){
        if(is_array($id)){
            $list = Image::where('relatedTable', 'Producttype')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'Producttype')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            // 删除七牛图片
            $res = $Qiniu->delImg($key);

            Image::where('id', $v['id'])->delete();
        }
    }

    public function getParentIdAttr($parentId){
        if(!$parentId){
            return '';
        }

        $name = $this->where(['id' => $parentId])->value('name');

        return $name;
    }

}