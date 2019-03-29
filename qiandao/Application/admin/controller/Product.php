<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

class Product extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['style'] = 1;
        $this->defaultWhere = $defaultWhere;
    }

    public function index(){

        return parent::index();
    }

    /**
     * Add
     */
    public function add(Request $request){
        // 单位
        $unitList = get_product_unit();
        $this->assign('unitList', $unitList);

        return parent::add($request);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect = ''){
        $model = model($this->model);

        //save data
        if ($request->isAjax()) {
            $data = $request->param();
            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();
            $data['createOper'] = $this->admin->id;
            // 价格转为分
            if($data['price'] && is_numeric($data['price'])){
                $data['price'] = $data['price'] * 100;
            }
            $specJson = [];
            if(isset($data['spec_format']) && $data['spec_format']){
                foreach ($data['spec_format'] as $key => $val) {
                    if(!trim($val)){
                        continue;
                    }
                    $specJson[] = [
                        'spec_format' => $val,
                        'spec_value' => $data['spec_value'][$key]
                    ];
                }
            }
            $data['imgUrl'] = $data['coverImg'];
            $data['specJson'] = json_encode($specJson);
            // 预售产品
            if(isset($data['preStartTime'])){
                $data['preStartTime'] = strtotime($data['preStartTime']);
                $data['preEndTime'] = strtotime($data['preEndTime']);
                $data['preDeliverDate'] = strtotime($data['preDeliverDate']);
            }

            $result = $model->save($data);

            if($result !== false) {
                // 单图
                if(isset($data['imgId']) && !empty($data['imgId'])){
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Product']);
                }
                // 多图
                if(isset($data['imgIds']) && !empty($data['imgIds'])){
                    foreach ((array)$data['imgIds'] as $item) {
                        Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'Product', 'tag' => 'imglist']);
                    }
                }
                // 详情图
                if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){
                    foreach ((array)$data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Product']);
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

        // 单位
        $unitList = get_product_unit();
        $this->assign('unitList', $unitList);

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
        if ($request->isAjax()) {
            $data = $request->param();
            // Update data
            $data['updateDate'] = time();
            $data['updateOper'] = $this->admin->id;
            // 价格转为分
            if($data['price'] && is_numeric($data['price'])){
                $data['price'] = $data['price'] * 100;
            }
            $specJson = [];
            if(isset($data['spec_format']) && $data['spec_format']){
                foreach ($data['spec_format'] as $key => $val) {
                    if(!trim($val)){
                        continue;
                    }
                    $specJson[] = [
                        'spec_format' => $val,
                        'spec_value' => $data['spec_value'][$key]
                    ];
                }
            }
            $data['imgUrl'] = $data['coverImg'];
            $data['specJson'] = json_encode($specJson);
            // 预售产品
            if(isset($data['preStartTime'])){
                $data['preStartTime'] = strtotime($data['preStartTime']);
                $data['preEndTime'] = strtotime($data['preEndTime']);
                $data['preDeliverDate'] = strtotime($data['preDeliverDate']);
            }

            $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

            if($result !== false) {
                // 单图
                if (isset($data['imgId']) && !empty($data['imgId'])) {
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Product']);
                }
                // 多图
                if (isset($data['imgIds']) && !empty($data['imgIds'])) {
                    foreach ((array)$data['imgIds'] as $item) {
                        Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'Product', 'tag' => 'imglist']);
                    }
                }
                // 详情图
                if (isset($data['detailImgIds']) && !empty($data['detailImgIds'])) {
                    foreach ((array)$data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Product']);
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

            if(isset($filter['typeId']) && $filter['typeId']){
                $where['typeId'] = $filter['typeId'];
            }
            if(isset($filter['varietieId']) && $filter['varietieId']){
                $where['varietieId'] = $filter['varietieId'];
            }
            if(isset($filter['title']) && $filter['title']){
                $where['title'] = ['like', '%'.$filter['title'].'%'];
            }
        }

        return $where;
    }

    /**
     * 获取 分类/品种
     */
    public function getCategory(){
        $params = $this->request->param();
        $parentId = $params['p_id'];
        $model = model('Producttype');

        $data = $model->where(['parentId' => $parentId])->field('id, name')->select();
        $html = '';
        if($data){
            foreach($data as $item){
                $html .= "<option value='{$item['id']}'>{$item['name']}</option>";
            }
        }

        return json($html);
    }

}