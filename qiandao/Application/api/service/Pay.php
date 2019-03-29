<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 8:12
 */
namespace app\api\service;

use app\admin\model\Tenant;
use app\common\BaseHelper;
use app\common\DictConstant;
use app\common\model\Groupdetail;
use app\common\model\Groupproduct;
use app\common\model\Ordercommission;
use app\common\model\Orderproductdetail;
use app\common\model\Product;
use app\common\model\Productspecgroups;
use app\common\model\Salerule;
use app\common\model\User;
use app\common\model\Useraccountdetails;
use app\common\model\Usercommission;
use app\common\service\TemplateMessage;
use app\lib\exception\OrderException;
use app\lib\exception\PayException;
use app\lib\exception\TokenException;
use think\Db;
use think\Exception;
use app\common\model\Order as OrderModel;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class Pay{

    private $orderID;
    private $orderNO;

    function __construct($orderID)
    {
        if(!$orderID){
            throw new Exception('订单号不能为空');
        }
        $this->orderID = $orderID;
    }

    //余额支付
    public function balancepay($formId){
        $this->checkOrderValid();
        $orderService = new Order();
        $orderStatus = $orderService->checkOrderStock($this->orderID);
        if(!$orderStatus['pass']){
            return $orderStatus;
        }
        return $this->goBalancePay($orderStatus['orderPrice'],$formId);

    }

    private function goBalancePay($orderPrice,$formId){
        //获取用户余额
        //判断订单金额是否小于等于用户余额
        $PayStatus = [
            'Balance' => DictConstant::BALANCE_TRUE,
            'isPay' => true

        ];
        $uid = Token::getCurrentUid();
        $user = User::get($uid);
        if($user->balance < $orderPrice){
            $PayStatus['Balance'] = DictConstant::BALANCE_FALSE;
            $PayStatus['isPay'] = false;
            return $PayStatus;
        }
        Db::startTrans();
        try
        {
            $order = OrderModel::where('id','=',$this->orderID)->lock(true)->find();
            $orderService = new Order();
            if($order->linkType == 0){
                if($order->status == 1){
                    //检测库存量
                    $stockStatus = $orderService->checkOrderStock($order->id);
                    if($stockStatus['pass']){
                        //减余额
                        User::where('id','=',$uid)->setDec('balance',$orderPrice);
                        //更新订单状态
                        $this->updateOrderStatus($order->id,$order->type,true);
                        //减库存
                        $this->reduceStock($stockStatus);
                        //如果存在佣金，则往订单与佣金规则对应表中插入数据
                        if($order->saleRuleId){
                            $this->insertOrderCommiss($order->saleRuleId,$order->id);
                        }
                    }else{
                        $this->updateOrderStatus($order->id,$order->type,false);
                    }
                }
            }elseif ($order->linkType == 1){
                if($order->status == 1){
                    //检测库存量
                    $stockStatus = $orderService->checkOrderStock($order->id);
                    if($stockStatus['pass']){
                        //减余额
                        User::where('id','=',$uid)->setDec('balance',$orderPrice);
                        //更新订单状态
                        $this->updateOrderStatus($order->id,$order->type,true);
                        //减库存
                        $this->reduceStock($stockStatus);
                        //改变用户拼团状态
                        $this->updateUserGroupStatus($order->spellGroupId,$order->id);
                    }else{
                        $this->updateOrderStatus($order->id,$order->type,false);
                    }
                }
            }

            //余额消费需要在（用户账户充值与消费明细表）中插入记录
            $Useraccountdetails = new Useraccountdetails();
            $Useraccountdetails->id = BaseHelper::getUUID();
            $Useraccountdetails->userId = $order->createUser;
            $Useraccountdetails->orderNO = $order->orderNO;
            $Useraccountdetails->type = $Useraccountdetails::TYPE_0;
            $Useraccountdetails->money = $order->total;
            $Useraccountdetails->save();

            Db::commit();
            TemplateMessage::paysendmessage($this->orderID,$formId);
            return $PayStatus;
        }
        catch (Exception $ex){
            Db::rollback();
            Log::record($ex);
            $PayStatus['isPay'] = false;
            throw $ex;
            return $PayStatus;
        }
    }

    private function updateUserGroupStatus($spellGroupId,$orderID){
        $groupproduct = Groupproduct::get($spellGroupId);
        $chentuannum = $groupproduct->groupSuccCount;
        $timeh = $groupproduct->groupDateStr;
        $groupdetail = Groupdetail::where('orderId',$orderID)->find();
        if($groupdetail->userId == $groupdetail->originator){
            $endDate = time()+$timeh*60;
        }else{
            $tuanzhang = Groupdetail::get($groupdetail->unigroupId);
            $endDate = $tuanzhang->endDate;
        }
        $goups = Groupdetail::where('unigroupId',$groupdetail->unigroupId)->lock(true)->select();
        if(count($goups) < $chentuannum){
            $groupdetail->endDate = $endDate;
            $groupdetail->status = 1;
            $groupdetail->isActive = 1;
            $groupdetail->save();
        }elseif (count($goups) == $chentuannum){
            //拼团成功  此团的所有参与者状态都改变
            $allgroups = Groupdetail::where('unigroupId',$groupdetail->unigroupId)->select();
            $list = [];
            foreach ($allgroups as $value){
                array_push($list,['id'=>$value['id'],'status'=>2,'isActive'=>1,'endDate'=>$endDate]);
            }
            (new Groupdetail())->saveAll($list);
        }else{

        }
    }

    private function insertOrderCommiss($saleRuleId,$orderId){
        $sale = Salerule::get($saleRuleId);
        $order = OrderModel::get($orderId);
        $storeId = $order->storeId;
        $proportion = $sale->value1;
        $user = User::get($order->createUser);
        $shangjiId = $user->chiefUserId;
        if($this->isCheckFs($order->createUser,$shangjiId)){
            $ordercommission = new Ordercommission();
            $ordercommission->orderId = $orderId;
            $ordercommission->saleruleId = $saleRuleId;
            $ordercommission->proportion = $proportion;
            $ordercommission->userId = $order->createUser;
            $ordercommission->shangjiId = $shangjiId;
            $ordercommission->total = $order->total;
            $ordercommission->storeId = $storeId;
            $ordercommission->save();
        }
    }
    private function isCheckFs($userid,$shangjiId){
        $user = User::get($userid);
        $shangji = User::get($shangjiId);
        if($user->tenantId == $shangji->tenantId){
            return true;
        }
        return false;

    }
    private function updateOrderStatus($orderID,$ordertype,$success){
        $paytime = date('Y-m-d H:i:s',time());
        if($success && $ordertype==DictConstant::ORDER_TYPE_ONLINE){
            $status = DictConstant::OrderPendingDelivery;
        }
        if($success && $ordertype==DictConstant::ORDER_TYPE_UNDERLINE){
            $status = DictConstant::Order_Unconsumed;
        }
        if(!$success){
            $status = DictConstant::PAID_BUT_OUT_OF;
        }
        OrderModel::where('id','=',$this->orderID)->update(['status'=>$status,'payMethod'=>1,'payDate'=>$paytime]);
        $Orderproductdetail = new Orderproductdetail();
        $orderpro = $Orderproductdetail->where('orderId',$orderID)->select();
        $list = [];
        foreach ($orderpro as $value){
            array_push($list,['id'=>$value['id'],'oldStatus'=>$status]);
        }
        $Orderproductdetail->saveAll($list);

    }

    private function reduceStock($stockStatus){
        foreach ($stockStatus['pStatusArray'] as $singlePSstatus){
            //storeCount
            if(isset($singlePSstatus['specid'])){
                Productspecgroups::where('id','=',$singlePSstatus['specid'])->setDec('storeCount',$singlePSstatus['counts']);
            }else{
                Product::where('id','=',$singlePSstatus['id'])->setDec('storeCount',$singlePSstatus['counts']);
            }
        }
    }

    /**
     * 商户向用户打款
     * @return \think\response\Json
     */
    public function shanghuPay(){
        $this->checkComOrder();
        $comorderModel = new Usercommission();
        $comorder = $comorderModel->where('id',$this->orderID)->find();

        return show(config('status.SUCCESS_STATUS'),'ok',$this->GoshanghuPay($comorder->amount,$comorder->merchantOrderNo));

    }

    private function GoshanghuPay($amount,$OrderNo){
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayTransfers();

        $wxOrderData->SetPartner_trade_no($OrderNo);
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetAmount($amount);
        $wxOrderData->SetDesc('佣金提现');
        $wxOrderData->SetCheck_name('NO_CHECK');

        return $this->DoTransfers($wxOrderData);
    }


    private function DoTransfers($wxOrderData){

//        <xml>
//        <return_code><![CDATA[SUCCESS]]></return_code>
//        <return_msg><![CDATA[]]></return_msg>
//        <mch_appid><![CDATA[wxec38b8ff840bd989]]></mch_appid>
//        <mchid><![CDATA[10013274]]></mchid>
//        <device_info><![CDATA[]]></device_info>
//        <nonce_str><![CDATA[lxuDzMnRjpcXzxLx0q]]></nonce_str>
//        <result_code><![CDATA[SUCCESS]]></result_code>
//        <partner_trade_no><![CDATA[10013574201505191526582441]]></partner_trade_no>
//        <payment_no><![CDATA[1000018301201505190181489473]]></payment_no>
//        <payment_time><![CDATA[2015-05-19 15：26：59]]></payment_time>
//        </xml>

        $wxOrder = \WxPayApi::transfers($wxOrderData);
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('商户向用户打款失败','error');
            if(isset($wxOrder['err_code']) && !empty($wxOrder['err_code'])){
                $usercomModel = new Usercommission();
                $usercom = $usercomModel->where('id',$this->orderID)->find();
                $usercom->status = 3;
                $usercom->causefail = $wxOrder['err_code_des'];
                $usercom->save();
            }
        }else{
            //打款成功后处理逻辑
            $this->transfersSuccess($wxOrder);
        }
        return $wxOrder;
    }

    private function transfersSuccess($wxOrder){
        Db::startTrans();
        try
        {
            $usercomModel = new Usercommission();
            $usercom = $usercomModel->where('merchantOrderNo',$wxOrder['partner_trade_no'])->find();
            $uid = $usercom->userId;
            $feel = $usercom->amount;
            $usercom->status = 2;
            $usercom->tradeDate = $wxOrder['payment_time'];
            $usercom->tradeNo = $wxOrder['payment_no'];
            $usercom->save();

            User::where('id','=',$uid)->setDec('totalIncome',$feel);
            Db::commit();

        }catch (Exception $ex){
            Db::rollback();
            Log::record($ex);
        }




    }

    public function pay(){
        //订单号可能根本就不存在
        //订单号确实是存在的，但是，订单号和当前用户是不匹配的
        //订单有可能已经被支付过
        //发起支付之前再进行库存量检测

        $this->checkOrderValid();
        $orderService = new Order();
        $groupService = new Group();

        $order = \app\common\model\Order::get($this->orderID);
        $Orderproductdetail = Orderproductdetail::where('orderId',$order->id)->find();
        $productName = $Orderproductdetail->productName;
        if($order->linkType == 0){
            $orderStatus = $orderService->checkOrderStock($this->orderID);
        }elseif ($order->linkType == 1){
            $orderStatus = $groupService->checkOrderStock($this->orderID);
        }else{

        }
        if(!$orderStatus['pass']){
            return $orderStatus;
        }
        return show(config('status.SUCCESS_STATUS'),'ok',$this->makeWxPreOrder($orderStatus['orderPrice'],$productName));

    }

    /**
     * 生成微信预订单
     * @param $totalPrice
     * @return array
     */
    private function makeWxPreOrder($totalPrice,$productName){
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNO);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice);
        $wxOrderData->SetBody($productName);
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('miniprogram.pay_back_url'));

        return $this->getPaySignature($wxOrderData);

    }

    /**
     * 请求微信统一下单接口返回给客户端一组支付参数
     * @param $wxOrderData
     * @return \成功时返回，其他抛异常
     */
    private function getPaySignature($wxOrderData){
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }else{

        }
        if($wxOrder['return_code'] == 'FAIL'){
            throw new PayException([
                'msg' => '商户号或者支付秘钥异常'
            ]);
        }
        $this->recordPreOrder($wxOrder);
        return $this->sign($wxOrder);
    }

    /**
     * 生成签名
     * @param $wxOrder
     * @return array
     */
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
        unset($rowValues['appId']);
        return $rowValues;
    }

    private function getProAppid(){
        $tenantid = Token::getTenantid();
        $tenant = Tenant::get($tenantid);
        return $tenant->appId;
    }

    private function recordPreOrder($wxOrder){
        OrderModel::where('id','=',$this->orderID)->update(['prepay_id'=>$wxOrder['prepay_id']]);
    }

    //对订单号进行检测
    private function checkOrderValid(){
        $order = OrderModel::where('id','=',$this->orderID)->find();
        if(!$order){
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->createUser)){
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if($order->status != DictConstant::OrderPendingPayment){
            throw new OrderException([
                'msg' => '该订单已经支付过了',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        $this->orderNO = $order->orderNO;
        return true;

    }

    //对用户佣金提现订单号进行检测
    private function checkComOrder(){
        $comorder = Usercommission::where('id','=',$this->orderID)->find();
        if(!$comorder){
            throw new OrderException([
                'msg' => '提现订单号不存在'
            ]);
        }
        if(!Token::isValidOperate($comorder->userId)){
            throw new TokenException([
                'msg' => '提现订单与用户不匹配',
                'errorCode' => 10007
            ]);
        }
        if($comorder->status != 1){
            throw new OrderException([
                'msg' => '该订单可能已经提现过了'
            ]);
        }
    }
}