<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 分类
 */
class Producttypetop extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $this->model = 'Producttype';
    }

    public function index(){
        $request = $this->request;
        $param = $request->param();
        if($request->isAjax()){
            $model = model($this->model);

            // 每页起始条数
            $start = $param['start'];
            // 每页显示条数
            $length = $param['length'];
            // 排序条件
            $columns = $param['order'][0]['column'];
            $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];

            $where = [
                'parentId' => 0
            ];
            $list = $model->where($where)->limit($start, $length)->order($order)->select();
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

        return $this->fetch();
    }

    public function add(Request $request){
        return $this->fetch();
    }

    public function addPost(Request $request, $redirect = ''){
        $model = model($this->model);

        //save data
        if ($request->isPost()) {
            $data = $request->param();
            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();
            $data['createOper'] = $this->admin->id;

            $result = $model->save($data);

            if($result !== false) {
                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('添加成功！', $redirect);
                } else {
                    return $this->success('添加成功！', 'admin/producttypetop/index');
                }
            } else {
                return $this->error($model->getError());
            }
            return $this->success('添加成功！', 'admin/producttypetop/index');
        } else {
            return $this->error('');
        }
    }

    public function edit(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        $info = $model->find($id);
        if(!$info){
            return $this->error('error !', 'admin/producttypetop/index');
        }
        return $this->fetch('', [
            'info' => $info
        ]);
    }

    public function editPost(Request $request, $redirect = ''){
        $model = model($this->model);
        if ($request->isPost()) {
            $data = $request->param();
            // Update data
            $data['updateDate'] = time();
            $data['updateOper'] = $this->admin->id;

            $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

            if($result !== false) {
                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '更新了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('编辑成功！', $redirect);
                } else {
                    return $this->success('编辑成功！', 'admin/producttypetop/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('error !');
        }
    }

}