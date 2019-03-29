<?php

namespace app\admin\model;

use app\lib\Qiniu;

use app\admin\model\Image;

class Research extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'research';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = false;


    public static function _after_delete($id){
        if(is_array($id)){
            $list = Image::where('relatedTable', 'Research')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'Research')->where('relatedId', $id)->select();
        }

        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            // 删除七牛图片
            $res = $Qiniu->delImg($key);

            Image::where('id', $v['id'])->delete();
        }
    }

    /**
     * 截止时间
     */
    public function getEndTimeAttr($date){
        if(!$date){
            return '';
        }
        return date('Y-m-d H:i:s', $date);
    }

}