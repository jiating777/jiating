<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9 0009
 * Time: 下午 3:26
 */

return [
    //商户id测试使用，后期通过程序获取
    'bussid' => 'Hjkshsh5622fK5245s34sd',
    'domain' => 'https://zhen.1miaodao.cn',
    'wx_domain' => 'https://wxt.1miaodao.cn',

    /****************************** 秒到第三放开发平台配置 *****************************/
    //秒到第三放开发平台appId
    'MiaoDaoOpenAppID' => 'wxde3f1b9460d878a1',
    //秒到第三放开发平台AppSecret
    'MiaoDaoOpenAppSecret' => 'ee6821bde1f35078a75ef64a95a70dbf',
    //秒到第三放开发平台公众号消息校验Token
    'MiaoDaoOpenToken' => '8a23ad382d1d47dk9484d470a404367b',
    //秒到第三放开发平台公众号消息加解密Key
    'MiaoDaoOpenEncodingAesKey' => 'aFUtnhUk4eHaXJlB47Vtjo1CiSIfocQi29rNz3ImMcY',
    //request合法域名
    'MiaoDaoOpenRequestdomain' => 'https://zhen.1miaodao.cn',
    //单个商户提交小程序审核次数
    'SubmissionNums' => 3,

    /*************************用户登录授权*************************************/
    'token_salt' => 'sJ89fj524sd56rf',
    'token_expire_in' => 7200,
    //第三方平台授权登录
    'login_url' => "https://api.weixin.qq.com/sns/component/jscode2session?appid=%s&js_code=%s&grant_type=authorization_code&component_appid=%s&component_access_token=%s",
    //微信支付回调地址
    'pay_back_url' => 'https://wechat-auth.1miaodao.cn/api/pay/notify',
    //微信支付用户充值回调
    'pay_back_RechargeUrl' => 'https://wechat-auth.1miaodao.cn/user/Rechargenotify'

];