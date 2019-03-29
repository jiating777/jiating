<?php

namespace app\admin\controller;

use app\admin\model\Orderitem;
use app\lib\exception\ParameterException;
use think\Db;
use think\Exception;
use think\Request;

class Receiveorder extends Order
{

    public function _initialize()
    {
        parent::_initialize();

        $this->model = 'Order';
        $this->defaultWhere = [
            'style' => 3
        ];
    }

    public function index(){
        $request = $this->request;
        if($request->isAjax()){
            $param = $request->param();
            $model = model($this->model);

            // 每页起始条数
            $start = $param['start'];
            // 每页显示条数
            $length = $param['length'];
            // 排序条件
            $columns = $param['order'][0]['column'];
            $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];

            $where = $this->getFilterWhere($request);
            if($this->defaultWhere){
                $where = array_merge($where, $this->defaultWhere);
            }

            $list = $model->where($where)->limit($start, $length)->order($order)->select();
            /*$userDB = db('user');
            foreach ($list as $item) {
                // 购买人信息
                $user = $userDB->where(['id' => $item->userId])->find();
                $item->user = $user;
            }*/
            $count = $model->where($where)->count();

            $result = [
                'status' => '1',
                'draw' => $param['draw'],
                'data' => $list,
                'recordsFiltered' => $count,
                'recordsTotal' => $count,
            ];

            return json($result);
        }

        // 订单状态
        $statusList = get_order_status();
        $this->assign('statusList', $statusList);

        return parent::index();
    }

    public function detail($id){
        $order = \app\admin\model\Order::get($id);
        $order['cityname'] = getAreaName($order->cityId);
        $order['xianname'] = getAreaName($order->xianId);
        $order['townname'] = getAreaName($order->townId);
        $order['villagename'] = '';
        if($order->villageId){
            $order['villagename'] = \app\admin\model\Villages::where('id',$order->villageId)->value('name');
        }
        $Orderitem = Orderitem::where('orderId',$id)->find();
        if($Orderitem){
            $Product = \app\admin\model\Product::get($Orderitem->productId);
            $Orderitem['unitname'] = $Product->unit;
        }
        $this->assign('order',$order);
        $this->assign('orderitem',$Orderitem);
        return $this->fetch();
    }

    /**
     * 发货
     * @param Request $request
     */
    public function delivery(Request $request){
        $param = $request->param();
        if(!$param['id'] || !$param['expressName'] || !$param['expressNo']){
            return $this->error('非法提交');
        }
        Db::startTrans();
        try{
            $Order = \app\admin\model\Order::get($param['id']);
            if($Order->status == '待发货'){
                $Order->status = 3;
                $Order->deliverDate = time();
                $Order->expressName = $param['expressName'];
                $Order->expressNo = $param['expressNo'];
                $Order->save();
                $Orderitem = Orderitem::where(['orderId'=>$param['id'],'oldStatus'=>2])->find();
                if($Orderitem){
                    $Orderitem->oldStatus = 3;
                    $Orderitem->save();
                }
            }else{
                throw new ParameterException([
                    'msg' => '之前已经发货了'
                ]);
            }
            Db::commit();
            return $this->success('提交成功！', 'admin/' . strtolower($request->controller()) . '/index');
        }catch (Exception $ex){
            Db::rollback();
            return $this->error($ex->msg);
        }
    }

    /**
     * 拒绝申领样品
     */
    public function refuse(){
        // TODO

    }

}