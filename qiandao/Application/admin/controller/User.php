<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;
use app\common\Common;

use app\admin\model\User AS UserMdl;

/**
 * Class Menu
 * @package app\admin\controller
 */
class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = 'user';
        $this->redirect = '/user';
        $this->searchFields = [
            'name' => [
                'label'     => '用户名',
                'field'     => 'name',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'
            ],
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
                //$model = $model->where($this->defaultWhere);
                $where = array_merge($where, $this->defaultWhere);
            }
            $list = $model->join('userdetail b','b.userId=user.id')->field('user.*,b.userNum,b.educational')->where($where)->limit($start, $length)->order($order)->select();
            $count = $model->join('userdetail b','b.userId=user.id')->where($where)->count();

            $result = [
                'status' => '1',
                'draw' => $param['draw'],
                'data' => $list,
                'recordsFiltered' => $count,
                'recordsTotal' => $count,
            ];

            return json($result);
        }

        return parent::index();
    }



}