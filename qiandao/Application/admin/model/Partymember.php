<?php

namespace app\admin\model;

class Partymember extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'partymember';

    public function getGenderAttr($gender){
        return show_gender($gender);
    }

    public static function _after_insert($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'partymember']);
        }
        //若选择为扶贫干部，关联贫困户表
        if(isset($data['isAid']) && $data['isAid'] == 1) {
            if(isset($data['memberId']) && !empty($data['memberId'])){
                foreach ((array)$data['memberId'] as $item) {
                    \app\admin\model\Povertymember::where('memberId', $item)->update(['aidingId' => $data['id']]);
                }
            }
        }
    }


    public static function _after_update($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'partymember']);
        }

        //若选择为扶贫干部，关联贫困户表
        if(isset($data['isAid']) && $data['isAid'] == 1) {
            $list = \app\admin\model\Povertymember::field('memberId')->where('aidingId',$data['id'])->select();
            foreach ($list as $l) {
                $listIds[] = $l['memberId'];
            }
            $delIds = array_diff($listIds,(array)$data['memberId']);
            \app\admin\model\Povertymember::where('memberId','in',implode(',', (array)$delIds))->update(['aidingId'=>'']);
            if(isset($data['memberId']) && !empty($data['memberId'])){
                foreach ((array)$data['memberId'] as $item) {
                    \app\admin\model\Povertymember::where('memberId', $item)->update(['aidingId' => $data['id']]);
                }
            }
        } else {  //

        }
    }


}