<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 分类品种
 */
class Producttype extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $this->defaultWhere = [
            // 'villageId' => $this->admin->villageId
            'parentId' => ['neq', 0]
        ];
        $this->searchFields = [
            'parentId' => [
                'label'     => '分类',
                'field'     => 'parentId',
                'type'      => 'select',
                'disabled'  => false,
                'condition' => '=',
                'data'      => Helper::makeOptions(
                    model($this->model),
                    ['parentId' => 0],
                    ['id' => 'asc'],
                    ['0' => '所有']
                )
            ],
            'name' => [
                'label'     => '品名',
                'field'     => 'name',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'

            ],
        ];
    }

    public function index(){

        return parent::index();
    }

    /**
     * Add
     */
    public function add(Request $request){
        // 分类
        $this->assign('categorys', Helper::makeOptions(
            model($this->model),
            ['parentId' => 0],
            ['id' => 'asc'],
            []
        ));

        return parent::add($request);
    }

    /**
     * Add Post
     */
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
                // 单图
                if(isset($data['imgId']) && !empty($data['imgId'])){
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Producttype']);
                }
                // 详情图
                if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){
                    foreach ($data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Producttype']);
                    }
                }

                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('添加成功！', $redirect);
                } else {
                    return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('');
        }
    }

    /**
     * Edit
     */
    public function edit(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }

        // 分类
        $this->assign('categorys', Helper::makeOptions(
            model($this->model),
            ['parentId' => 0],
            ['id' => 'asc'],
            []
        ));

        return $this->view->fetch($this->editView, [
            'info' => $info
        ]);
    }

    /**
     * Edit Post
     */
    public function editPost(Request $request, $redirect = ''){
        $model = model($this->model);

        //save data
        if ($request->isPost()) {
            $data = $request->param();
            // Update data
            $data['updateDate'] = time();
            $data['updateOper'] = $this->admin->id;

            $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

            if($result !== false) {
                // 单图
                if (isset($data['imgId']) && !empty($data['imgId'])) {
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Producttype']);
                }
                // 详情图
                if (isset($data['detailImgIds']) && !empty($data['detailImgIds'])) {
                    foreach ($data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Producttype']);
                    }
                }

                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '更新了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('编辑成功！', $redirect);
                } else {
                    return $this->success('编辑成功！', 'admin/' . strtolower($this->model) . '/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('error !');
        }
    }

    /**
     * 筛选条件
     */
    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['parentId']) && $filter['parentId']){
                $where['parentId'] = $filter['parentId'];
            }

            if(isset($filter['name']) && $filter['name']){
                $where['name'] = ['like', '%'.$filter['name'].'%'];
            }
        }

        return $where;
    }

}