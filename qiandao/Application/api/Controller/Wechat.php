<?php 


namespace app\api\controller;

use app\api\service\Wechat AS WechatService;
use think\Db;
use app\common\BaseHelper;

use app\admin\model\User;
use app\admin\model\Villages;


class Wechat extends BaseController {

	public function create() {  //获得3rd_session
		$request = $this->request;
    	$param = array('code'=>$request->param('code'));  //登录时获取的 code
    	$session_3rd = $request->param('session_3rd') ? : '';

    	$wechatService = new WechatService();
    	$result = $wechatService->create($param);

    	return json($result);

	}

	public function get_open_id() {
		$request = $this->request;
        $appId = $request->param('appId');

        $village = Villages::where('appId',$appId)->find();
        if(!$village) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '获取村信息失败');
        }

        $wechatService = new WechatService();
        $param = array('code'=>$request->param('code'),'appId'=>$request->param('appId'),'appSecret'=>$village['appSecret']);  //登录时获取的 code
        $result = $wechatService->get_open_id($param);
        if( !$result || !isset($result['openid'])) {  //获取失败，直接返回
            return show(config('status.NOT_DATA'), self::NOT_DATA, $result);
        }

        $user = User::where('openId',$result['openid'])->find();
        if(!$user) {
            $id = BaseHelper::getUUID22();
            User::create([
                'id' => $id,
                'openId' => $result['openid'],
                'villageId' => $village['id'],
            ]);
            $userId = $id;
        } else {
            $userId = $user['id'];
        }
        $result['villageId'] = $village['id'];
        $result['imgUrl'] = $village['imgUrl'];
        $result['phone'] = $village['phone'];
        $result['name'] = $village['name'];
        $result['addressGeo'] = $village['addressGeo'];
        $result['userId'] = $userId;

    	return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);

	}

    //保存用户的个人信息-留言需要
    public function userInfo() {
        $param = self::getHttpParam();
        if (empty($param->openId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'openId不能为空');
        }
        $data = [
            'nickName' => $param->nickName,
            'avatarUrl' => $param->avatarUrl
        ];
        $save = User::where('openId',$param->openId)->update($data);
        if($save) {
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $save);
        }
        return show(config('status.ERROR_STATUS'), self::NOT_DATA, '保存失败');
    }

    //获取用户信息，用于判断是否需要授权
    public function getInfo() {
        $param = self::getHttpParam();
        if (empty($param->openId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'openId不能为空');
        }
        $user = User::where('openId',$param->openId)->find();
        if($user) {
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $user);
        }
        return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关信息');

    }




}