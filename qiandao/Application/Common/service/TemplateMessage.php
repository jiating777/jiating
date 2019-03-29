<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23 0023
 * Time: 下午 6:08
 */
namespace app\common\service;


use app\common\BaseHelper;
use app\common\model\Order;
use app\common\model\Orderproductdetail;
use app\common\model\User;
use think\Controller;

class TemplateMessage extends Controller {
    //获取小程序模板库标题列表
    public static function paysendmessage($orderID,$formId=''){
        $order = Order::get($orderID);
        $component_access_token = MiniProgramHelp::getAuthorizerToken($order->tenantId);
        if (empty($component_access_token)) {
            return false;
        }
        if($formId==''){
            $formId = $order->prepay_id;
        }else{
            $formId = $formId;
        }
        $orderOrderproductdetail = Orderproductdetail::where('orderId',$order->id)->find();
        $user = User::get($order->createUser);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $component_access_token;
        $total = ($order->total)/100;
        $postData['touser'] = $user->openId;
        $postData['template_id'] = 'Iiyh71ygdDFy4v4tJrV4KjvejBa0B6G_6SlkYOKlSV4';
        $postData['page'] = 'pages/index/index';
        $postData['form_id'] = $formId;
        $postData['data'] = [
            'keyword1' => ['value'=>$order->orderNO],
            'keyword2' => ['value'=>$total.'元'],
            'keyword3' => ['value'=>$order->create_time],
            'keyword4' => ['value'=>$orderOrderproductdetail->productName]
        ];
        $postData['emphasis_keyword'] = 'keyword1.DATA';
        json_decode(BaseHelper::curlPost($url,json_encode($postData)),true);

    }

    public static function refundsendmessage($orderID,$group_hours,$group_nums){
        $order = Order::get($orderID);
        $component_access_token = MiniProgramHelp::getAuthorizerToken($order->tenantId);
        if (empty($component_access_token)) {
            return false;
        }

        $formId = $order->formId;
        $orderOrderproductdetail = Orderproductdetail::where('orderId',$order->id)->find();
        $user = User::get($order->createUser);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $component_access_token;
        $total = ($order->total)/100;
        $postData['touser'] = $user->openId;
        $postData['template_id'] = 'iOWITscRQdwp_RdAYPof0IYeo9EG9_ZEkD6WVXakLEw';
        $postData['form_id'] = $formId;
        $postData['data'] = [
            'keyword1' => ['value'=>$orderOrderproductdetail->productName],
            'keyword2' => ['value'=>$total.'元'],
            'keyword3' => ['value'=>$group_hours.'小时内还没凑满'.$group_nums.'人参团'],
            'keyword4' => ['value'=>$order->create_time],
            'keyword5' => ['value'=>'点击查看详情']
        ];

        json_decode(BaseHelper::curlPost($url,json_encode($postData)),true);


    }

}