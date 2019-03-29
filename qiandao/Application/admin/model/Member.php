<?php

namespace app\admin\model;

use app\admin\model\Image;
use app\admin\model\Povertymember;
use app\common\Common;
use app\common\BaseHelper as Helper;

class Member extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'member';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = true;

    public static function _after_insert($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'member']);
        }

        // 家庭环境-多图
        if(isset($data['imgIds']) && !empty($data['imgIds'])){
            foreach ((array)$data['imgIds'] as $item) {
                Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'member', 'tag' => 'imglist']);
            }
        }

        //贫困户信息
        if(isset($data['isPoverty']) && $data['isPoverty'] == 1) {
            $poverty = [
                'id' => Helper::getUUID(),
                'memberId' => $data['id'],
                'povertyreason' => $data['povertyreason'],
                'perincome' => $data['perincome'],
                'familymember' => $data['familymember'],
                'housearea' => $data['housearea'],
                'farmlandarea' => $data['farmlandarea'],
                'isLowInsurance' => $data['isLowInsurance'],
                'isJoinPension' => $data['isJoinPension'],
                'isJoinNewFarmhouse' => $data['isJoinNewFarmhouse'],
                'cityId' => $data['cityId'],
                'xianId' => $data['xianId'],
                'townId' => $data['townId'],
                'villageId' => $data['villageId'],
                'aidingId' => $data['aidingId'],
                'createDate' => time(),
                'createOper' => session('ADMIN')['id']
            ];
            Povertymember::insert($poverty);
        }
    }

    public static function _after_update($data){
        if(isset($data['imgId']) && !empty($data['imgId'])){
            Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'member']);
        }
        // 家庭环境-多图
        if(isset($data['imgIds']) && !empty($data['imgIds'])){
            foreach ((array)$data['imgIds'] as $item) {
                Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'member', 'tag' => 'imglist']);
            }
        }

        //贫困户信息
        if(isset($data['isPoverty']) && $data['isPoverty'] == 1) {
            $poverty = [
                'povertyreason' => $data['povertyreason'],
                'perincome' => $data['perincome'],
                'familymember' => $data['familymember'],
                'housearea' => $data['housearea'],
                'farmlandarea' => $data['farmlandarea'],
                'isLowInsurance' => $data['isLowInsurance'],
                'isJoinPension' => $data['isJoinPension'],
                'isJoinNewFarmhouse' => $data['isJoinNewFarmhouse'],
                'aidingId' => $data['aidingId'],
            ];
            $find = Povertymember::where('memberId',$data['id'])->find();
            if($find) {
                $poverty['updateDate'] = time();
                $poverty['updateOper'] = session('ADMIN')['id'];
                Povertymember::where('id',$find['id'])->update($poverty);
            } else {                
                $poverty['id'] = Helper::getUUID();
                $poverty['memberId'] = $data['id'];
                $poverty['createDate'] = time();
                $poverty['createOper'] = session('ADMIN')['id'];
                $poverty['cityId'] = $data['cityId'];
                $poverty['xianId'] = $data['xianId'];
                $poverty['townId'] = $data['townId'];
                $poverty['villageId'] = $data['villageId'];
                Povertymember::insert($poverty);
            }
        }
    }

    public static function _after_delete($id){
        if(is_array($id)){
            $list = Image::where('relatedTable', 'member')->whereIn('relatedId', $id)->select();
        }else{
            $list = Image::where('relatedTable', 'member')->where('relatedId', $id)->select();
        }
        $Qiniu = new Qiniu();
        foreach ((array)$list as $v) {
            $key = substr(strrchr($v['imgUrl'], '/'), 1);
            $res = $Qiniu->delImg($key);  //删除图片
            Image::where('id',$v['id'])->delete();
        }
    }

    public function getGenderAttr($gender){

        return show_gender($gender);
    }

    public function getEthnicIdAttr($ethnicId){
        if(!$ethnicId){
            return '';
        }

        $ethnic = db('ethnic')->where(['id' => $ethnicId])->value('name');

        return $ethnic;
    }

    public function getEducationAttr($education){
        if(!$education){
            return '';
        }
        $educationArr = get_education();

        return $educationArr[$education];
    }

    public function getHealthAttr($health){
        if(!$health){
            return '';
        }
        $healthArr = get_health();

        return $healthArr[$health];
    }

    /**
     * 民族
     * @return \think\model\relation\HasOne
     */
    /*public function ethnic()
    {
        return $this->hasOne('Ethnic', 'id', 'ethnicId')->field('name');
    }*/

}