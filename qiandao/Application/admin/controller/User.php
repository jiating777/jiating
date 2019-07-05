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
                'label'     => '学号/工号',
                'field'     => 'userNum',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'
            ],
            'type' => [
                'label'=>'用户类型',
                'field'     => 'user.type',
                'type'      => 'select',
                'disabled'  => false,
                'data'=>[
                    '1'=>'全部',
                    'student'=>'学生',
                    'teacher'=>'教师']
            ]
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
            // dump($where);
            if($this->defaultWhere){
                //$model = $model->where($this->defaultWhere);
                $where = array_merge($where, $this->defaultWhere);
            }
            if(isset($where['userNum']) && $where['user.type'] !='1') {
                $list = $model->join('userdetail b','b.userId=user.id')->join('school c','b.schoolId = c.id')->field('user.*,b.userNum,b.educational,c.name as schoolName')->where('type','=',$where['user.type'])->where('userNum','like','%'.$where['userNum'].'%')->limit($start, $length)->order($order)->select();
                $count = $model->join('userdetail b','b.userId=user.id')->where($where)->count();
            } else if(isset($where['user.type']) && $where['user.type'] =='1'){
                $list = $model->join('userdetail b','b.userId=user.id')->join('school c','b.schoolId = c.id')->field('user.*,b.userNum,b.educational,c.name as schoolName')->where('userNum','like','%'.$where['userNum'].'%')->limit($start, $length)->order($order)->select();
                $count = $model->join('userdetail b','b.userId=user.id')->where($where)->count();
            } else {
                $list = $model->join('userdetail b','b.userId=user.id')->join('school c','b.schoolId = c.id')->field('user.*,b.userNum,b.educational,c.name as schoolName')->where($where)->limit($start, $length)->order($order)->select();
                $count = $model->join('userdetail b','b.userId=user.id')->where($where)->count();
            }
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