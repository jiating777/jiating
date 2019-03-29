<?php

namespace app\admin\model;

use app\lib\Qiniu;

use app\admin\model\Image;

class Product extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'product';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = true;


    public static function _after_delete($id){
        if(is_array($id)){
            $list = Image::where('relatedTable', 'Product')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'Product')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            // 删除七牛图片
            $res = $Qiniu->delImg($key);

            Image::where('id', $v['id'])->delete();
        }
    }

    public function getPreStartTimeAttr($date){
        if(!$date){
            return '';
        }

        return date('Y-m-d H:i', $date);
    }

    public function getPreEndTimeAttr($date){
        if(!$date){
            return '';
        }
        return date('Y-m-d H:i', $date);
    }

    public function getPreDeliverDateAttr($date){
        if(!$date){
            return '';
        }

        return date('Y-m-d', $date);
    }

    /**
     * 分类
     * @return \think\model\relation\HasOne
     */
    /*public function category()
    {
        return $this->hasOne('Producttype', 'id', 'typeId')->field('name');
    }*/

    /**
     * 单位
     */
    public function getUnitAttr($unit){
        if(!$unit){
            return '';
        }
        $statusArr = get_product_unit();

        return $statusArr[$unit];
    }

}