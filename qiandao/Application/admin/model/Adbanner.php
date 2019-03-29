<?php

namespace app\admin\model;

use app\lib\Qiniu;

use app\admin\model\Image;

class Adbanner extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'adbanner';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = false;


    public static function _after_delete($id){
        if(is_array($id)){
            $list = Image::where('relatedTable', 'Adbanner')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'Adbanner')->where('relatedId', $id)->select();
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
     * 页面位置
     * @param $position
     * @return string
     */
    public function getPositionAttr($position){
        if(!$position){
            return '';
        }
        $positionArr = get_pagesposition();

        return $positionArr[$position];
    }

}