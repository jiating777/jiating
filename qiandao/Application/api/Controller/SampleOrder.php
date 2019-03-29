<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/10 0010
 * Time: 下午 4:39
 */
namespace app\api\controller;

use app\common\validate\SampleOrderPlace;
use app\api\service\SampleOrder as SampleOrderService;
use think\Request;

class SampleOrder extends BaseController {

    public function placeSampleOrder(Request $request){
        (new SampleOrderPlace())->goCheck();
        $param = $request->param();
        $area = $this->getParentIdsByTownId($param['townId']);
        $param['cityId'] = $area['cityId'];
        $param['xianId'] = $area['xianId'];

        // 是否已申领样品
        $info = db('orderitem')->where(['userId' => $param['userId'], 'productId' => $param['productId']])->find();
        if($info){
            return show(config('status.ERROR_STATUS'), '你已申领过，不能再次申领', '你已申领过，不能再次申领');
        }

        $SampleOrderService = new SampleOrderService();
        $status = $SampleOrderService->place($param);

        if(!$status['pass']){
            return show(config('status.ERROR_STATUS'), '申领样品失败，库存不足', $status);
        }else{
            return show(config('status.SUCCESS_STATUS'), '申领样品成功', $status);
        }

    }
}