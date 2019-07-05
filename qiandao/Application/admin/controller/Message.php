<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;
use app\common\Common;

use app\admin\model\Messages AS MessageMdl;

/**
 * Class Menu
 * @package app\admin\controller
 */
class Message extends Base
{
	public function _initialize() {
        parent::_initialize();
        $this->model = 'messages';
        $this->redirect = '/message';

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = ['neq', 1];
        $defaultWhere['responderId'] = 0;
        $this->defaultWhere = $defaultWhere;
    }

	public function index()
	{
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

            $list = $model->where($where)->limit($start, $length)->order($order)->select();
            $userDB = db('user');
            foreach ($list as $item) {
                $user = $userDB->where(['id' => $item->userId])->field('name')->find();
                $item->user = $user;
            }
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
        return parent::index();
	}
}
  