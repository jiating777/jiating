<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9 0009
 * Time: 下午 3:16
 */
namespace app\common\service;

use app\common\BaseHelper;
use app\common\model\AppManage;

use app\lib\redis\Redis;
use think\Cache;
use think\Controller;

/**
 * 微信小程序助手类
 * Class MiniProgramHelp
 * @package backend\components
 */
class MiniProgramHelp extends Controller
{

    /**
     * 生成第三方授权二维码链接
     * @return string
     * @author fei <xliang.fei@gmail.com>
     */
    public static function createAuthCode(){
        $pre_auth_code = self::getPreAuthCode();
        if(empty($pre_auth_code)){
            exit("授权失败");
        }
        $AppId = config('miniprogram.MiaoDaoOpenAppID');
        //halt($AppId);
        $redirect_uri = config('miniprogram.domain').'/wx/authcodecallback?b='.session('TENANT_ID');
        return 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=' . $AppId . '&pre_auth_code=' . $pre_auth_code . '&redirect_uri=' . $redirect_uri;
    }

    /**
     * 获取预授权码pre_auth_code
     * @return mixed
     * @author fei <xliang.fei@gmail.com>
     */
    public static function getPreAuthCode(){
        $token = Cache::get('MiaoDaoOpenPreAuthCode');
        if(!empty($token)){
            $token = json_decode($token, true);
            if (!empty($token['expires_time']) && !empty($token['component_access_token']) && time() <= $token['expires_time']) {
                return $token['component_access_token'];
            }
        }
        $component_access_token = self::getCompomentAccessToken();
        if (empty($component_access_token)) {
            return false;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' . $component_access_token;
        $postData['component_appid'] = config('miniprogram.MiaoDaoOpenAppID');
        $result = json_decode(BaseHelper::curlPost($url,json_encode($postData)),true);

        //记录日志
        self::saveMiniProgramLog($url, $postData, $result, '获取预授权码pre_auth_code');

        //令牌
        $data['pre_auth_code'] = $result['pre_auth_code'];
        //过期时间(20分钟)
        $data['expires_time'] = time() + 20 * 60;

        Cache::set('MiaoDaoOpenPreAuthCode',json_encode($data),$data['expires_time']);
        return $data['pre_auth_code'];
    }


    /**
     * 获取第三方平台component_access_token(接口调用凭据)
     * @return mixed
     * @author fei <xliang.fei@gmail.com>
     */
    public static function getCompomentAccessToken(){
        //先从缓存里面读取token，看有没有过期
        $token = Cache::get('MiaoDaoOpenComponentAccessToken');
        if(!empty($token)){
            $token = json_decode($token, true);
            if(!empty($token['expires_time']) && !empty($token['component_access_token']) && time()<$token['expires_time']){
                return $token['component_access_token'];
            }
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $postData['component_appid'] = config('miniprogram.MiaoDaoOpenAppID');
        $postData['component_appsecret'] = config('miniprogram.MiaoDaoOpenAppSecret');
        $postData['component_verify_ticket'] = Redis::getRedisConn()->get('MiaoDaoOpenVerifyTicket');
        $result = json_decode(BaseHelper::curlPost($url,json_encode($postData)),true);
        //记录日志
        self::saveMiniProgramLog($url, $postData, $result, '获取第三方平台component_access_token');

        if (empty($result['component_access_token'])) {
            return false;
        }
        //令牌
        $data['component_access_token'] = $result['component_access_token'];
        //过期时间(1小时50分钟)
        $data['expires_time'] = time() + 60 * 60 + 50 * 60;
        Cache::set('MiaoDaoOpenComponentAccessToken',json_encode($data),$data['expires_time']);

        return $data['component_access_token'];
    }


    /**
     * 使用授权码换取公众号或小程序的接口调用凭据和授权信息
     * @param $auth_code
     * @return mixed
     * @author fei <xliang.fei@gmail.com>
     */
    public static function getApiQueryAuth($auth_code)
    {
        //接口调用凭据
        $component_access_token = self::getCompomentAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $component_access_token;

        $postData['component_appid'] = config('miniprogram.MiaoDaoOpenAppID');
        $postData['authorization_code'] = $auth_code;

        $result = json_decode(BaseHelper::curlPost($url, json_encode($postData)), true);

        //记录日志
        self::saveMiniProgramLog($url, $postData, $result, '使用授权码换取公众号或小程序的接口调用凭据和授权信息');

        return $result;
    }

    /**
     * 获取授权方令牌
     * @param   string $businessId 商家id
     * @param   bool $isRefresh 是否刷新token
     * @return bool|string
     * @author fei <xliang.fei@gmail.com>
     */
    public static function getAuthorizerToken($businessId, $isRefresh = false)
    {
        $businessMiniProgram = self::getMiniProgramInfo($businessId);
        if (empty($businessMiniProgram)) {
            self::saveMiniProgramLog('', '', '', '获取授权方令牌接口 商家小程序信息为空 businessId:' . $businessId);
            return false;
        }

        //未过期
        if (empty($isRefresh) && time() < strtotime($businessMiniProgram->authorizerAccessTokenExpires)) {
            return $businessMiniProgram->authorizerAccessToken;
        }

        //接口调用凭据
        $component_access_token = self::getCompomentAccessToken();

        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $component_access_token;

        $postData['component_appid'] = config('miniprogram.MiaoDaoOpenAppID');
        $postData['authorizer_appid'] = $businessMiniProgram->appId;
        $postData['authorizer_refresh_token'] = $businessMiniProgram->authorizerRefreshToken;

        $result = json_decode(BaseHelper::curlPost($url, json_encode($postData)), true);

        //记录日志
        self::saveMiniProgramLog($url, $postData, $result, '获取授权方令牌 businessId:' . $businessId);

        if (empty($result['authorizer_access_token'])) {
            return false;
        }

        $appmanageInfo = AppManage::where(['projectId' => $businessId])->find();
        $appmanageInfo->authorizerAccessToken = $result['authorizer_access_token'];
        $appmanageInfo->authorizerAccessTokenExpires = date('Y-m-d H:i:s', time() + 55 * 60 * 2);
        $appmanageInfo->authorizerRefreshToken = $result['authorizer_refresh_token'];

        if ($appmanageInfo->save()) {
            return $result['authorizer_access_token'];
        } else {
            return false;
        }
    }

    /**
     * 获取授权方的帐号基本信息
     * @param $businessId
     * @return bool
     * @author fei <xliang.fei@gmail.com>
     */
    public static function getAuthorizerInfo($businessId)
    {
        $businessMiniProgram = self::getMiniProgramInfo($businessId);
        if (empty($businessMiniProgram)) {
            return false;
        }

        //接口调用凭据
        $component_access_token = self::getCompomentAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=' . $component_access_token;

        $postData['component_appid'] = config('miniprogram.MiaoDaoOpenAppID');
        $postData['authorizer_appid'] = $businessMiniProgram->appId;

        $result = json_decode(BaseHelper::curlPost($url, json_encode($postData)), true);

        //记录日志
        self::saveMiniProgramLog($url, $postData, $result, '获取授权方的帐号基本信息 businessId:' . $businessId);

        return $result;
    }

    /**
     * 修改服务器地址(需要先将域名登记到第三方平台的小程序服务器域名中，才可以调用接口进行配置)
     * @param $businessId
     * @param $action
     * @return mixed
     * @author fei <xliang.fei@gmail.com>
     */
    public static function modifyDomain($businessId, $action = 'add')
    {
        $access_token = self::getAuthorizerToken($businessId);           //接口调用凭据
        $url = 'https://api.weixin.qq.com/wxa/modify_domain?access_token=' . $access_token;

        $postData['action'] = $action;
        $postData['requestdomain'] = [config('miniprogram.MiaoDaoOpenRequestdomain')];
        $postData['wsrequestdomain'] = [config('miniprogram.MiaoDaoOpenRequestdomain')];
        $postData['uploaddomain'] = [config('miniprogram.MiaoDaoOpenRequestdomain')];
        $postData['downloaddomain'] = [config('miniprogram.MiaoDaoOpenRequestdomain')];

        $result = json_decode(BaseHelper::curlPost($url, json_encode($postData)), true);

        //记录日志
        self::saveMiniProgramLog($url, $postData, $result, '修改服务器地址 businessId:' . $businessId);

        return $result;
    }

    /**
     * 获取商户信息
     *
     * @param string $businessId 商家id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getMiniProgramInfo($businessId)
    {
        $model = model('Townprogram');
        $info = $model->where(['id' => $businessId])->find();

        $appmanageInfo = AppManage::where(['projectId' => $businessId])->find();
        // 第三方授权获取的令牌
        $info->authorizerAccessToken = $appmanageInfo->authorizerAccessToken;
        // 第三授权获取的token，用于刷新authorizerAccessToken
        $info->authorizerRefreshToken = $appmanageInfo->authorizerRefreshToken;
        // 令牌过期时间
        $info->authorizerAccessTokenExpires = $appmanageInfo->authorizerAccessTokenExpires;
        // 状态
        $info->wxStatus = $appmanageInfo->wxStatus;

        return $info;
    }

    /**
     * 记录请求
     * @param $url  string  请求地址
     * @param $request  array   请求数据
     * @param $response array   返回数据
     * @param $msg  string  备注信息
     * @author fei <xliang.fei@gmail.com>
     */
    public static function saveMiniProgramLog($url, $request, $response, $msg = '')
    {
        file_put_contents(config('path.MiniProgramLog'), '发起时间：' . date('Y-m-d H:i:s') . $msg . ' 请求地址Url:' . $url . ' 请求数据：' . json_encode($request) . ' 返回数据：' . json_encode($response) . PHP_EOL, FILE_APPEND);
    }

}