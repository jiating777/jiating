<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/28 0028
 * Time: 下午 6:13
 */
namespace app\api\service;

use app\common\DictConstant;
use app\common\model\Groupdetail;
use app\common\model\Groupproduct;
use app\common\model\Ordercommission;
use app\common\model\Orderproductdetail;
use app\common\model\Product;
use app\common\model\Productspecgroups;
use app\common\model\Salerule;
use app\common\model\User;
use app\common\service\TemplateMessage;
use think\Cache;
use think\Db;
use think\Exception;
use app\common\model\Order as OrderModel;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

/**
 * 处理微信支付回调
 * Class WxNotify
 * @package app\api\service
 */
class WxNotify extends \WxPayNotify{

    //<xml>
    //<appid><![CDATA[wx2421b1c4370ec43b]]></appid>
    //<attach><![CDATA[支付测试]]></attach>
    //<bank_type><![CDATA[CFT]]></bank_type>
    //<fee_type><![CDATA[CNY]]></fee_type>
    //<is_subscribe><![CDATA[Y]]></is_subscribe>
    //<mch_id><![CDATA[10000100]]></mch_id>
    //<nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
    //<openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
    //<out_trade_no><![CDATA[1409811653]]></out_trade_no>
    //<result_code><![CDATA[SUCCESS]]></result_code>
    //<return_code><![CDATA[SUCCESS]]></return_code>
    //<sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
    //<sub_mch_id><![CDATA[10000100]]></sub_mch_id>
    //<time_end><![CDATA[20140903131540]]></time_end>
    //<total_fee>1</total_fee>
    //<trade_type><![CDATA[JSAPI]]></trade_type>
    //<transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
    //</xml>
    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'SUCCESS'){
            $orderNo = $data['out_trade_no'];
            $paytime = self::timeFormat($data['time_end']);
            Db::startTrans();
            try
            {
                $order = OrderModel::where('orderNO','=',$orderNo)->lock(true)->find();
                $orderService = new Order();
                if($order->linkType == 0){ //普通订单
                    if($order->status == 1){
                        //检测库存量
                        $stockStatus = $orderService->checkOrderStock($order->id);
                        if($stockStatus['pass']){
                            //写入微信支付单号
                            $transaction_id = $data['transaction_id'];
                            OrderModel::where('orderNO',$orderNo)->update(['transaction_Id'=>$transaction_id,'payDate'=>$paytime]);
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
                }elseif ($order->linkType == 1){ //拼团订单
                    if($order->status == 1){
                        //检测库存量
                        $stockStatus = $orderService->checkOrderStock($order->id);
                        if($stockStatus['pass']){
                            //写入微信支付单号
                            $transaction_id = $data['transaction_id'];
                            OrderModel::where('orderNO',$orderNo)->update(['transaction_Id'=>$transaction_id,'payDate'=>$paytime]);
                            //更新订单状态
                            $this->updateOrderStatus($order->id,$order->type,true);
                            //减库存
                            $this->reduceStock($stockStatus);
                            //改变用户拼团状态
                            $groupproduct = Groupproduct::get($order->spellGroupId);
                            $chentuannum = $groupproduct->groupSuccCount;
                            $timeh = $groupproduct->groupDateStr;
                            $groupdetail = Groupdetail::where('orderId',$order->id)->find();
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
                        }else{
                            $this->updateOrderStatus($order->id,$order->type,false);
                        }

                    }
                }else{
                }
                Db::commit();
                //发送模板消息
                if(Cache::get('ORDERID') != $order->id){
                    TemplateMessage::paysendmessage($order->id);
                    Cache::set('ORDERID',$order->id);
                }

                return true;
            }catch (Exception $ex){
                Db::rollback();
                Log::record($ex);
                return false;
            }
        }else{
            return true;
        }
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
    private function updateOrderStatus($orderID,$ordertype,$success){
        if($success && $ordertype==DictConstant::ORDER_TYPE_ONLINE){
            $status = DictConstant::OrderPendingDelivery;
        }
        if($success && $ordertype==DictConstant::ORDER_TYPE_UNDERLINE){
            $status = DictConstant::Order_Unconsumed;
        }
        if(!$success){
            $status = DictConstant::PAID_BUT_OUT_OF;
        }
        OrderModel::where('id','=',$orderID)->update(['status'=>$status]);
        $Orderproductdetail = new Orderproductdetail();
        $orderpro = $Orderproductdetail->where('orderId',$orderID)->select();
        $list = [];
        foreach ($orderpro as $value){
            array_push($list,['id'=>$value['id'],'oldStatus'=>$status]);
        }
        $Orderproductdetail->saveAll($list);


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

    public static function timeFormat($time){
        $paytime = substr($time,0,4).'-';
        $paytime .= substr($time,4,2).'-';
        $paytime .= substr($time,6,2);
        $paytime .= ' ';
        $paytime .= substr($time,8,2).':';
        $paytime .= substr($time,10,2).':';
        $paytime .= substr($time,12,2);
        $paytime = date('Y-m-d H:i:s',strtotime($paytime));
        return $paytime;
    }

}