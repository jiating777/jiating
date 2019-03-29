<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 下午 5:04
 */

namespace app\api\service;

use app\lib\exception\ForbiddenException;
use think\Request;
use think\Cache;
use think\Exception;
use app\lib\exception\TokenException;
use app\api\service\Token as TokenService;
use app\lib\enum\ScopeEnum;

class Token
{
    public static function generateToken()
    {
        //32个字符组成一组随机字符串
        $randChars = random_string(32);
        //用三组字符串，进行md5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('miniprogram.token_salt');

        return md5($randChars . $timestamp . $salt);
    }

    /**
     * 根据客户端传过来的token信息获取对应的缓存信息
     * @param $key
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    public static function getCurrentTokenVar($key)
    {
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }
    }

    public static function getCurrentUid()
    {
        //token
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }

    public static function getTenantid()
    {
        //token
        $tenantid = self::getCurrentTokenVar('tenantid');
        return $tenantid;
    }

    /**
     * 用户和后台管理员访问的接口
     * @return bool
     * @throws ForbiddenException
     * @throws TokenException
     */
    public static function needPrimaryScope()
    {
        $scope = TokenService::getCurrentTokenVar('scope');
        if ($scope) {
            if ($scope >= ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    /**
     * 只有用户才能访问的接口
     * @return bool
     * @throws ForbiddenException
     * @throws TokenException
     */
    public static function needExclusiveScope()
    {
        $scope = TokenService::getCurrentTokenVar('scope');
        if ($scope) {
            if ($scope == ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    /**
     * 检测记录里的id是否与令牌里的id一致
     * @param $checkedUID
     * @return bool
     * @throws Exception
     */
    public static function isValidOperate($checkedUID){
        if(!$checkedUID){
            throw new Exception('检查UID时必须传入一个被检查的UID');
        }
        $currentOperateUID = self::getCurrentUid();
        if($currentOperateUID == $checkedUID){
            return true;
        }
        return false;
    }


}