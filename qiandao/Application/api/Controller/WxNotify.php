<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21 0021
 * Time: 16:42
 */

namespace app\api\controller;
use think\Controller;
use think\Loader;
use think\Log;
Loader::import('example.WxPay',EXTEND_PATH,'.NativePay.php');
Loader::import('platformwxpay.WxPay',EXTEND_PATH,'.Notify.php');
class WxNotify extends Controller
{

    //微信回调地址
    public function index(){
        Log::info("123");

     $wxpaynotify = new \WxPayNotify();
     $wxpaynotify->Handle();





    }

}