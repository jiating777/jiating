<?php

namespace app\admin\model;

use app\lib\Qiniu;

use app\admin\model\Image;

class Communitydynamics extends Base
{

    // 当前模型名称 不带前缀
    protected $name = 'communitydynamics';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';


    public static function _after_delete($id){
        if(is_array($id)){
            $list = Image::where('relatedTable', 'Communitydynamics')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'Communitydynamics')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            // 删除七牛图片
            $res = $Qiniu->delImg($key);

            Image::where('id', $v['id'])->delete();
        }
    }

    // 处理动态内容
    public function getContentAttr($data){
        if(!$data){
            return '';
        }

        return urldecode($data);
    }
}