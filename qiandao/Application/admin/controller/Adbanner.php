<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 轮播图
 */
class Adbanner extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $this->defaultWhere = [
            // 'villageId' => $this->admin->villageId
        ];
    }

    public function index(){

        return parent::index();
    }

    /**
     * Add
     */
    public function add(Request $request){
        // 页面位置
        $positions = get_pagesposition();
        // 链接类型
        $linkTypes = get_link_type();

        $this->assign('positions', $positions);
        $this->assign('linkTypes', $linkTypes);

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
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Adbanner']);
                }
                // 详情图
                if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){
                    foreach ($data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Adbanner']);
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

        // 页面位置
        $positions = get_pagesposition();
        // 链接类型
        $linkTypes = get_link_type();

        $this->assign('positions', $positions);
        $this->assign('linkTypes', $linkTypes);

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
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Adbanner']);
                }
                // 详情图
                if (isset($data['detailImgIds']) && !empty($data['detailImgIds'])) {
                    foreach ($data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Adbanner']);
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

}