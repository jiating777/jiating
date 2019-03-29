<?php

namespace app\api\controller;

use app\admin\model\Image;
use app\admin\model\Member AS MemberMdl;
use app\admin\model\Povertyproject;
use app\lib\exception\ParameterException;
use think\Exception;

/**
 * 村民接口
 */
class Member extends BaseController
{

    /**
     * 获取村民列表
     *
     * @return \think\response\Json
     */
    public function getMemberList()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId不能为空');
        }

        $where = [
            'townId' => $param->townId,
            'villageId' => $param->villageId,
            'isDelete' => 2
        ];
        $fields = 'id, name, avatar, isPoverty';

        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $result = MemberMdl::where($where)->order('createDate DESC')->limit($start, $length)->field($fields)->select();
        $total = MemberMdl::where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到村民');
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取村民详情
     *
     * @return \think\response\Json
     */
    public function getMemberDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId不能为空');
        }
        if (empty($param->memberId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'memberId 不能这空');
        }

        $memberId = $param->memberId;
        $where = [
            'townId' => $param->townId,
            'villageId' => $param->villageId,
            'id' => $memberId
        ];

        $model = new MemberMdl();
        $fields = 'name, avatar, isPoverty, gender, birthday, mobile, ethnicId, address,shenheStatus';
        $item = $model->where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关村民');
        }

        // 家庭环境
        $familyImgs = Image::where(['relatedId' => $memberId, 'relatedTable' => 'member', 'tag' => 'imglist'])->field('imgUrl')->select();
        $item['familyImgs'] = $familyImgs;

        // 扶贫项目
        $povertyprojects = Povertyproject::where(['memberId' => $memberId, 'isDelete' => 2])->field('title, imgUrl')->select();
        $item['povertyprojects'] = $povertyprojects;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    //组织成员详情
    public function getPartyDetail() {
        $param = self::getHttpParam();
        if (empty($param->memberId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'memberId 不能为空');
        }
        $row = MemberMdl::where(['id'=>$param->memberId])->field('name,job,avatar,partyTime,ethnicId,education,mobile,qrcodePath')->find();
        $row['qrcodePath'] = 'http://'.$_SERVER['HTTP_HOST'].$row['qrcodePath'];
        // 小程序背景图
        $row['bgImage'] = 'https://'.$_SERVER['HTTP_HOST'].'/public/static/wechat/image/memberinfo_bg.png';

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $row);
    }

    //我的->我是村民详情
    public function getMyDetails(){
        $param = self::getHttpParam();
        $userId = $param->userId;
        try{
            if(!$userId){
                throw new ParameterException([
                    'msg' => '缺少参数'
                ]);
            }
            $user = \app\admin\model\User::get($userId);
            $Member = \app\admin\model\Member::where('userId',$userId)->find();
            if($Member){
                $Member->partyTime = date('Y-m-d H:i:s', $Member->partyTime);
                $Member->avatar = $Member->avatar.'?imageView2/1/w/110/h/110';
                if($Member->organizationId){
                    $Organization = \app\admin\model\Organization::get($Member->organizationId);
                    if($Organization){
                        $Member['Organizationname'] = $Organization->name;
                    }
                }
                if($Member->villageId){
                    $villagename = getVillageName($Member->villageId);
                    if($villagename){
                        $Member['villagename'] = $villagename;
                    }
                }
                $user['member'] = $Member;
            }
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $user);
        }catch (Exception $ex){
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, $ex->msg);
        }


    }
}