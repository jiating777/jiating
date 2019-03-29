<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/10 0010
 * Time: 下午 5:55
 */
namespace app\api\service;

use app\admin\model\Area;
use app\admin\model\Orderitem;
use app\admin\model\Product;
use app\common\BaseHelper;
use app\lib\exception\ParameterException;
use think\Db;
use think\Exception;

class SampleOrder{

    protected $userId;
    protected $productId;
    protected $orderId;
    protected $townId;
    protected $cityId;
    protected $xianId;
    protected $count;
    protected $style = 3;  //3表示申领
    protected $villageId = NULL;
    protected $deliverAddress;
    protected $userName;
    protected $userPhone;
    protected $content = NULL;

    public function place($param){
        $this->userId = $param['userId'];
        $this->productId = $param['productId'];
        $this->townId = $param['townId'];
        $this->xianId = $param['xianId'];
        $this->cityId = $param['cityId'];
        $this->count = $param['count'];
        $this->deliverAddress = $param['deliverAddress'];
        $this->userName = $param['userName'];
        $this->userPhone = $param['userPhone'];
        if(isset($param['villageId']) && !empty($param['villageId'])){
            $this->villageId = $param['villageId'];
        }
        if(isset($param['content']) && !empty($param['content'])){
            $this->content = $param['content'];
        }

//        //检测库存
//        $status = $this->checkStock($this->productId,$this->count);
//        if(!$status['pass']){
//            $status['orderId'] = -1;
//            return $status;
//        }

        $productinfo = $this->getProductInfo($this->productId);
        if(!$productinfo['pass']){
            $productinfo['order_id'] = -1;
            return $productinfo;
        }

        //创建订单
        $order = $this->createOrder($productinfo);
        $order['pass'] = true;
        return $order;
    }


    private function createOrder($productinfo){
        Db::startTrans();
        try{
            $orderNO = $this->makeOrderNo();
            $order = new \app\admin\model\Order();
            $order->id = BaseHelper::getUUID();
            $order->orderNO = $orderNO;
            $order->townId = $this->townId;
            $order->cityId = $this->cityId;
            $order->xianId = $this->xianId;
            $order->villageId = $this->villageId;
            $order->userId = $this->userId;
            $order->totalCount = 1;
            $order->totalAmount = 0;
            $order->status = 2;
            $order->content = $this->content;
            $order->deliverAddress = $this->deliverAddress;
            $order->userName = $this->userName;
            $order->userPhone = $this->userPhone;
            $order->style = $this->style;
            $order->save();
            $orderId = $order->id;
            $create_time = $order->createDate;
            $orderitem = new Orderitem();
            $orderitem->id = BaseHelper::getUUID();
            $orderitem->orderId = $orderId;
            $orderitem->userId = $this->userId;
            $orderitem->productId = $this->productId;
            $orderitem->productName = $productinfo['title'];
            $orderitem->price = $productinfo['price'];
            $orderitem->productImg = $productinfo['imgUrl'];
            $orderitem->count = 1;
            $orderitem->oldStatus = 2;
            $orderitem->save();
            Db::commit();
            return [
                'orderNO' => $orderNO,
                'orderId' => $orderId,
                'create_time' => $create_time
            ];

        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }
    }




//    private function checkStock($productId,$count){
//        $status = [
//            'pass' => false,
//            'orderPrice' => 0,
//            'totalCount' => $count,
//            'pStatusArray' => []
//        ];
//        $Product = Product::get($productId);
//        if(!$Product){
//            throw new ParameterException([
//                'msg' => '该商品不存在'
//            ]);
//        }
//        $pStatus = [
//            'title' => '',
//            'price' => 0,
//            'imgUrl' => null,
//        ];
//        $pStatus['title'] = $Product->title;
//        $pStatus['price'] = $Product->price;
//        $pStatus['imgUrl'] = $Product->imgUrl;
//        if($Product->stock > $count){
//            $status['pass'] = true;
//        }
//
//
//        array_push($status['pStatusArray'],$pStatus);
//        return $status;
//
//    }
    //获取商品信息
    private function getProductInfo($productId){
        $status = [
            'pass' => true,
            'title' => '',
            'price' => 0,
            'imgUrl' => ''
        ];
        $Product = Product::get($productId);
        if($Product){
            $status['title'] = $Product->title;
            $status['price'] = $Product->price;
            $status['imgUrl'] = $Product->imgUrl;
        }else{
            $status['pass'] = false;
        }

        return $status;

    }
    /**
     * 生成订单号
     * @return string
     */
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
}