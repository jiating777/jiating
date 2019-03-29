<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use app\admin\model\Image;
use app\admin\model\Villages;

use think\Request;

// TODO
class Setting extends Base
{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'villages';
        $this->redirect = 'admin/setting/index';
    }

    public function index(){
        $row = model($this->model)->where(['id'=>$this->admin->villageId])->find();
        $this->assign('row',$row);

        //$imagesList = Villageimages::where(['villageId'=>$this->admin->villageId])->select();
        $imagesList = [];
        $this->assign('imagesList', $imagesList);

        return $this->fetch($this->indexView);
    }

    public function editPost(Request $request, $redirect='') {
        $data = $request->param();

        try {
            Villages::where('id',$data['villageId'])->update(['name'=>$data['name']]);
            if (isset($data['imgIds']) && !empty($data['imgIds'])) {   //处理图片
                unset($data['name']);
                $data['createOper'] = $this->admin->id;
                
                //$oldimg = Villageimages::where('villageId',$data['villageId'])->column('imgUrl');
                $oldimg = [];
                $del = array_diff($oldimg,$data['imgUrl']);    //获取需要删除的图片地址
                foreach ((array)$del as $d) {
                    //Villageimages::where('imgUrl',$d)->delete();
                }
                $add = array_diff($data['imgUrl'], $oldimg);
                foreach ((array)$add as $a) {
                    $data['id'] = BaseHelper::getUUID();
                    $data['createDate'] = time();
                    $data['imgUrl'] = $a;
                    //Villageimages::create($data);
                    Image::where('imgUrl', $a)->update(['relatedId' => $data['id'], 'relatedTable' => 'Villageimages']);
                }
            }
            return $this->success('保存成功', 'admin/' . strtolower($this->model) . '/index');
        } catch (Exception $e) {
            return $this->error('网络出错，请重试', 'admin/' . strtolower($this->model) . '/index');
        }
        
    }

}