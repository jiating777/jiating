<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 微课堂
 */
class Microclassroom extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $this->defaultWhere = $defaultWhere;

        $this->searchFields = [
            'title' => [
                'label'     => '课程标题',
                'field'     => 'title',
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
            /*if($this->defaultOrder){
                $model = $model->order($this->defaultOrder);
            }*/

            $list = $model->where($where)->limit($start, $length)->order($order)->select();
            $classhourDB = db('classhour');
            foreach ($list as $item) {
                //$list->category = show_microclass_category($item->categoryId);
                $classHours = $classhourDB->where(['classId' => $item->id])->count();
                $item->classHours = $classHours;
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

    /**
     * Add
     */
    public function add(Request $request){
        // 分类
        $categorys = get_microclass_category();
        $this->assign('categorys', $categorys);

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
            $data['imgUrl'] = $data['coverImg'];

            $result = $model->save($data);

            if($result !== false) {
                // 单图
                if(isset($data['imgId']) && !empty($data['imgId'])){
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Microclassroom']);
                }
                // 详情图
                if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){
                    foreach ($data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Microclassroom']);
                    }
                }

                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);

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
        $categorys = get_microclass_category();
        $this->assign('categorys', $categorys);

        $this->assign('area', $this->getArea($info));

        // 图集
        $imagesList = Image::where(['relatedId' => $info->id, 'tag' => 'imglist'])->where('imgUrl', 'neq', $info->imgUrl)->select();
        $this->assign('imagesList', $imagesList);

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
            $data['imgUrl'] = $data['coverImg'];

            $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

            if($result !== false) {
                // 单图
                if (isset($data['imgId']) && !empty($data['imgId'])) {
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Microclassroom']);
                }
                // 详情图
                if (isset($data['detailImgIds']) && !empty($data['detailImgIds'])) {
                    foreach ($data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Microclassroom']);
                    }
                }

                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '更新了一条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);

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

            if(isset($filter['title']) && $filter['title']){
                $where['title'] = ['like', '%'.$filter['title'].'%'];
            }
        }

        return $where;
    }

    /**
     * 课时
     */
    public function classhour(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }

        $request = $this->request;
        if($request->isAjax()){
            $param = $request->param();
            $model = model('Classhour');

            // 每页起始条数
            $start = $param['start'];
            // 每页显示条数
            $length = $param['length'];
            // 排序条件
            $columns = $param['order'][0]['column'];
            $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];

            $where = [
                'classId' => $id
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

        $this->assign('title', $info->title);
        $this->assign('classId', $info->id);

        return $this->view->fetch('classhour');
    }

    /**
     * 添加课时
     */
    public function addClasshour(Request $request){
        $model = model('Classhour');

        //save data
        if ($request->isPost()) {
            $data = $request->param();
            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();
            $data['createOper'] = $this->admin->id;

            if($data['fileUrl']){
                @$data['fileType'] = pathinfo($data['fileUrl'], PATHINFO_EXTENSION);
            }

            $result = $model->save($data);

            if($result !== false) {
                // 单图
                if(isset($data['imgId']) && !empty($data['imgId'])){
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Classhour']);
                }

                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条课时数据。';
                common::adminLog($request, $logInfo);

                return $this->success('添加成功！', url('admin/microclassroom/classhour', ['id' => $data['classId']]));
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('');
        }
    }

    /**
     * 删除课时
     */
    public function delClasshour(){
        $model = model('Classhour');

        $request = $this->request;
        $id = $request->param('id');

        // 删除一条
        $info = $model->find(['id', $id]);
        if(!$info){
            return redirect('admin/'.$this->model.'/index');
        }
        $result = $model->where('id', $id)->delete();

        if($result !== false){
            // Query执行后的操作
            $model->_after_delete($id);

            $logInfo = $this->admin->name . '删除了1条课时数据。';
            common::adminLog($request, $logInfo);
        }

        if($result !== false){
            return $this->success('删除成功！', url('admin/microclassroom/classhour', ['id' => $info->classId]));
        }else{
            return $this->error('删除失败！', url('admin/microclassroom/classhour', ['id' => $info->classId]));
        }
    }

}