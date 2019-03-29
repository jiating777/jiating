<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18 0018
 * Time: 下午 2:25
 */
namespace app\common;

class DictConstant{
    /****************************返回错误 状态************************/
    //错误
    const ERROR_STATUS = 0;
    //成功
    const SUCCESS_STATUS = 1;

    /****************************订单状态 order************************/
    // 订单状态 待付款
    const OrderPendingPayment = 1;
    // 订单状态 已经支付，待发货
    const OrderPendingDelivery = 2;
    // 订单状态 已经支付，已经发货,待收货
    const OrderAlreadyShipped = 3;
    //订单状态 已完成
    const OrderCompleted = 4;
    //线下订单，未消费
    const Order_Unconsumed = 8;
    //线下订单，已消费
    const Order_Already_consumed = 7;
    //订单状态，售后中
    const Order_Reefund = 6;
    //拒绝售后退款
    const Order_Refuse = 11;
    //同意售后退款
    const Order_Agree = 10;
    //已支付，但库存不足
    const PAID_BUT_OUT_OF = 12;

    //支付方式，余额支付
    const PAP_TYPE_BALANCE = 1;
    //余额充足
    const BALANCE_TRUE = 1;
    //余额不足
    const BALANCE_FALSE = 0;

    //订单类型，线上订单
    const ORDER_TYPE_ONLINE = 0;
    //订单类型，线下订单
    const ORDER_TYPE_UNDERLINE = 1;




    /****************************订单商品状态 goods************************/
    // 订单商品状态 完成
    const ORDER_GOODS_STATUS_COMPLETE = 10;
    // 订单商品状态 删除(改餐时被取消)
    const ORDER_GOODS_STATUS_DELETE = 0;

    /****************************商家统计 businessCount************************/
    // 统计类型 会员
    const BUSINESS_COUNT_TYPE_MEMBER = 1;
    // 统计类型 订单
    const BUSINESS_COUNT_TYPE_ORDER = 2;
    // 统计类型 收入
    const BUSINESS_COUNT_TYPE_INCOME = 3;

    /***************************返回数据类型***************************/
    const FORMAT_RAW = 'raw';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_XML = 'xml';
}