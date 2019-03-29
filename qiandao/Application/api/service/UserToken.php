<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 下午 2:28
 */
namespace app\api\service;



use app\admin\model\Tenant;
use app\admin\model\Townprogram;
use app\common\BaseHelper;
use app\admin\model\User as UserModel;
use app\common\service\MiniProgramHelp;
use app\lib\exception\ParameterException;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use app\lib\enum\ScopeEnum;

class UserToken extends Token{
    protected $code;
    protected $prAppid;
    protected $component_appid;
    protected $component_access_token;
    protected $login_url;
    function __construct($code,$prAppid)
    {
        $this->code = $code;
        $this->prAppid = $prAppid;
        $this->component_appid = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/getOpenAppID'),true);
        $this->component_access_token = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/getCompomentAccessToken'),true);
        $this->login_url = sprintf(config('miniprogram.login_url'),$this->prAppid,$this->code,$this->component_appid,$this->component_access_token);
    }

    public function get(){
        $wxResult = json_decode(BaseHelper::curlGet($this->login_url),true);
        if(empty($wxResult)){
            throw new Exception("获取session_key及openID异常，微信内部错误");
        }else{
            $loginFail = array_key_exists('errcode',$wxResult);
            if($loginFail){
                $this->processLoginError($wxResult);
            }else{
                return $this->grantToken($wxResult,$this->prAppid);
            }
        }

    }

    private function grantToken($wxResult,$prAppid){
        // 拿到openid
        // 数据库里看一下，这个openid是不是已经存在
        // 如果存在 则不处理，如果不存在那么新增一条user记录
        // 生成令牌，准备缓存数据，写入缓存
        // 把令牌返回到客户端去
        // key: 令牌
        // value: wxResult，uid, scope
       $openid = $wxResult['openid'];
       $user = UserModel::getByOpenID($openid);
       if($user){
            $uid = $user->id;
       }else{
            $uid = $this->newUser($openid,$prAppid);
       }
       $cachedvalue = $this->prepareCachedValue($wxResult,$uid,$prAppid);
       $token = $this->saveToCache($cachedvalue);
       return ['token' => $token,'openid'=>$openid,'session_key'=>$wxResult['session_key']];



    }

    private function saveToCache($cachedvalue){
        $key = self::generateToken();
        $value = json_encode($cachedvalue);
        $expire_in = config('miniprogram.token_expire_in');
        $request = cache($key,$value,$expire_in);
        if(!$request){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }

    private function prepareCachedValue($wxResult,$uid,$prAppid){
        $tenantId = $this->getTenant($prAppid);
        $cachedvalue = $wxResult;
        $cachedvalue['uid'] = $uid;
        $cachedvalue['tenantid'] = $tenantId;
        $cachedvalue['scope'] = ScopeEnum::User;
        return $cachedvalue;
    }

    private function newUser($openid,$prAppid){

        $townprogramId = $this->getTenant($prAppid);
        $user = UserModel::create([
            'id' => BaseHelper::getUUID22(),
            'openId' => $openid,
            'townprogramId' => $townprogramId,

        ]);
        return $user->id;
    }

    private function getTenant($prAppid){
        $tenantModel = new Townprogram();
        $tenant = $tenantModel->where(['appId'=>$prAppid])->field('id')->find();
        if(!$tenant){
            throw new ParameterException([
                'msg' => '商户没有授权，或服务器内部错误'
            ]);
        }
        return $tenant->id;
    }

    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg' => $wxResult['errmsg'],
            'errorCode' => $wxResult['errcode']
        ]);
    }
}
