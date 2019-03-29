<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28 0028
 * Time: 下午 8:25
 */
namespace app\api\service;


use app\admin\model\Tenant;
use app\common\BaseHelper;
use app\common\model\Useraccountdetails;
use app\lib\exception\OrderException;
use app\lib\exception\ParameterException;
use app\lib\exception\TokenException;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class UserRecharge{
    protected $uid;
    protected $money;
    protected $productname = '用户充值';
    protected $orderNO;
    public function entrance($uid,$postdata){
        $this->uid = $uid;
        $this->money = $postdata['money'];

        return $this->createRechargeorder();
    }

    private function createRechargeorder(){
        $Useraccountdetails = new Useraccountdetails();
        $orderNO = Order::makeOrderNo();
        $Useraccountdetails->id = BaseHelper::getUUID();
        $Useraccountdetails->userId = $this->uid;
        $Useraccountdetails->orderNO = $orderNO;
        $Useraccountdetails->type = 1;
        $Useraccountdetails->money = $this->money * 100;
        $Useraccountdetails->rechargeStatus = 0;
        $Useraccountdetails->tenantId = Token::getTenantid();
        if($Useraccountdetails->save()){
            return [
                'orderId' => $Useraccountdetails->id,
                'orderNO' => $orderNO,
                'money' => $Useraccountdetails->money,
                'create_time' => $Useraccountdetails->create_time,
                'pass' => true
            ];
        }else{
            return [
                'pass' => false,
            ];
        }


    }

    public function Recharge($RechargeId,$type=1){   //type=1用户充值   type=3开通会员
        if($type==1){
            $this->productname = '用户充值';
        }else{
            $this->productname = '开通会员';
        }
        if(!$RechargeId){
            throw new ParameterException([
                'msg' => '订单id不能为空'
            ]);
        }
        return $this->pay($RechargeId);
    }

    private function pay($RechargeId){
        $this->checkRechargeId($RechargeId);
        $Rechargeorder = Useraccountdetails::get($RechargeId);
        return show(config('status.SUCCESS_STATUS'),'ok',$this->makeWxPreOrder($Rechargeorder->money,$this->productname));
    }

    private function makeWxPreOrder($money,$productname){
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();

        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNO);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($money);
        $wxOrderData->SetBody($productname);
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('miniprogram.pay_back_RechargeUrl'));
        return $this->getPaySignature($wxOrderData);
    }

    private function getPaySignature($wxOrderData){
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }else{

        }
        $this->recordPreOrder($wxOrder);
        return $this->sign($wxOrder);
    }

    private function sign($wxOrder){
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid($this->getProAppid());
        $jsApiPayData->SetTimeStamp((string)time());
        $jsApiPayData->SetNonceStr(BaseHelper::getUUID());
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign();
        $rowValues = $jsApiPayData->GetValues();
        $rowValues['paySign'] = $sign;
        $rowValues['isfree'] = 1;  //付费开通标志
        unset($rowValues['appId']);
        return $rowValues;
    }

    private function getProAppid(){
        $tenantid = Token::getTenantid();
        $tenant = Tenant::get($tenantid);
        return $tenant->appId;
    }
    private function recordPreOrder($wxOrder){
        Useraccountdetails::where('orderNO',$this->orderNO)->update(['prepay_id'=>$wxOrder['prepay_id']]);
    }

    private function checkRechargeId($RechargeId){
        $Rechargeorder = Useraccountdetails::get($RechargeId);
        if(!$Rechargeorder){
            throw new ParameterException([
                'msg' => '该充值订单不存在'
            ]);
        }
        if(!Token::isValidOperate($Rechargeorder->userId)){
            throw new TokenException([
                'msg' => '充值订单与用户不匹配',
                'errorCode' => 10004
            ]);
        }
        if($Rechargeorder->rechargeStatus!=0){
            throw new OrderException([
                'msg' => '该充值订单可能已被支付过了'
            ]);
        }
        $this->orderNO = $Rechargeorder->orderNO;
        return true;
    }
}