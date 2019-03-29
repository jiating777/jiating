<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 下午 6:52
 */

namespace app\api\controller;

use app\admin\model\Townprogram;
use app\api\wxchat\WxBizDataCrypt;


use app\admin\model\User;
use app\admin\model\Member;
use app\common\BaseHelper;

/**
 * Class UserInfo
 * @package app\api\controller
 * 获取用户信息
 * 传入参数openId
 */
class UserInfo extends BaseController
{

    public function getUserInfo($where = '')
    {
        $param = self::getHttpParam();
        if (empty($param->appId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'appId不能为空');
        } else if (empty($param->openId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'openId不能为空');
        } else {
            $appId = $param->appId;
            $openId = $param->openId;
            $user = User::where('openId', $openId)->find();
            $Townprogram = Townprogram::get($user->townprogramId);
            if($Townprogram){
                $user->townId = $Townprogram->townId;
            }else{
                return show(config('status.ERROR_STATUS'), '该小程序可能没有授权', '小程序没有授权');
            }


            if (empty($user)) {
                $townprogramId = Townprogram::where('appId', $appId)->column('id')[0];
                $user = new User;
                $user->id = BaseHelper::getUUID22();
                $user->openId = $openId;
                $user->townprogramId = $townprogramId;
                $user->save();
            }

            $userInfo = parent::getUserInfo(['id' => $user->id]);
            if(isset($userInfo->isParty)){
                $user->isParty = $userInfo->isParty;
            }else{
                $user->isParty = 2;
            }

            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $user);
        }
    }

    /**
     * @return \think\response\Json
     * 解析手机号
     */
    public function decodePhone()
    {
        $param = self::getHttpParam();
        if (empty($param->openId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'openId不能这空');
        } else if (empty($param->appId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'appId不能这空');
        } else if (empty($param->encryptedData)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'encryptedData不能这空');
        } else if (empty($param->iv)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'iv不能这空');
        } else if (empty($param->sessionKey)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'sessionKey不能这空');
        } else {
            $appId = $param->appId;
            $openId = $param->openId;
            $encryptedData = $param->encryptedData;
            $iv = $param->iv;
            $sessionKey = $param->sessionKey;

            $pc = new WxBizDataCrypt($appId, $sessionKey);
            $errCode = $pc->decryptData($encryptedData, $iv, $data);

            if ($errCode == 0) {
                $phone['mobile'] = json_decode($data)->phoneNumber;
                $user = User::where('openId', $openId)->find();
                $member = Member::where('mobile',json_decode($data)->phoneNumber)->find();
                if($member) {  //找到对应手机号
                    $user->isMember = 1;
                    $user->memberId = $member['id'];
                }
                $user->mobile = json_decode($data)->phoneNumber;
                $user->save();
                return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $phone);
            } else {
                return show(config('status.ERROR_STATUS'), $errCode, '手机号解析错误');
            }
        }
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 编辑个人信息
     */
    public function saveUserInfo()
    {
        $param = self::getHttpParam();
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId不能为空');
        } else {
            $user = User::where('id', $param->userId)->find();
            if (!empty($param->nickName)) {
                $user->nickName = $param->nickName;
            }
            if (!empty($param->avatarUrl)) {
                $user->avatarUrl = $param->avatarUrl;
            }
            if (!empty($param->name)) {
                $user->name = $param->name;
            }
            if (!empty($param->gender)) {
                $user->gender = $param->gender;
            }
            if (!empty($param->dateOfBirth)) {
                $user->dateOfBirth = $param->dateOfBirth;
            }
            $user->save();
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $user);
        }
    }

}