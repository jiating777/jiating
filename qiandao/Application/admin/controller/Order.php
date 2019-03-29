<?php

namespace app\admin\controller;

use think\Request;

class Order extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $this->defaultWhere = [
            'style' => 1
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


    /**
     * 筛选条件
     */
    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['cityId']) && $filter['cityId']){
                $where['cityId'] = $filter['cityId'];
            }
            if(isset($filter['xianId']) && $filter['xianId']){
                $where['xianId'] = $filter['xianId'];
            }
            if(isset($filter['townId']) && $filter['townId']){
                $where['townId'] = $filter['townId'];
            }
            if(isset($filter['villageId']) && $filter['villageId']){
                $where['villageId'] = $filter['villageId'];
            }

            if(isset($filter['status']) && $filter['status']){
                $where['status'] = $filter['status'];
            }
            if(isset($filter['orderNO']) && $filter['orderNO']){
                $where['orderNO'] = ['like', '%'.$filter['orderNO'].'%'];
            }
        }

        return $where;
    }

}