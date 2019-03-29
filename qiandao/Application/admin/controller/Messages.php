<?php 

namespace app\admin\controller;

use think\Request;

/**
 * 留言
 */
class Messages extends Base
{

    public function _initialize() {
        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = ['neq', 1];
        $defaultWhere['responderId'] = 0;
        $this->defaultWhere = $defaultWhere;
    }

    public function index() {
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
                $user = $userDB->where(['id' => $item->userId])->field('nickName as name, avatarUrl as avatar')->find();
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

    //回复村留言
    public function addPost(Request $request, $redirect = '') {
        return parent::addPost($request,$redirect);
    }

    //删除留言或单条回复
    public function delete() {
        $request = $this->request;
        $id = $request->param('id');

        $model = model($this->model);
        $del = $model->where(['id'=>$id])->whereOr(['responderId'=>$id])->update(['isDelete'=>1]);
        if($del) {
            return $this->success('删除成功！', 'admin/' . strtolower($this->model) . '/index');
        } else {
            return $this->error('删除失败！', 'admin/' . strtolower($this->model) . '/index');
        }
    }

}