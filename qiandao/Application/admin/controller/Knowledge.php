<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Knowledgetype;

use think\Request;

/**
 * 农事专题
 */
class Knowledge extends Base
{
    public function _initialize() {
        parent::_initialize();
        $this->model = 'knowledge';
        $this->redirect = 'admin/knowledge/index';
        $this->defaultOrder = 'sorting';
        $type = array_column(Knowledgetype::select(),'name','id');
        $this->searchFields = [
            'typeId' => [
                'label'     => '',
                'field'     => 'typeId',
                'type'      => 'select',
                'disabled'  => false,
                'data' => array_merge(['0'=>'所有专题'],$type)
            ],
            'contentType' => [
                'label'     => '',
                'field'     => 'contentType',
                'type'      => 'select',
                'disabled'  => false,
                'data' => [
                    '0' => '所有类型',
                    '1' => '图片',
                    '2' => '视频',
                ]
            ],
            'title' => [
                'label'     => '标题',
                'field'     => 'title',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'

            ],
        ];
    }

    public function add(Request $request) {
        $typeList = Knowledgetype::select();
        $this->assign('typeList',$typeList);
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect = '') {
        $redirect = $this->redirect;
        return parent::addPost($request, $redirect);
    }

    public function edit(Request $request) {
        $typeList = Knowledgetype::select();
        $imagesList = Image::where(['relatedId' => $request->param('id'), 'tag' => 'imglist'])->select();
        $this->assign('imgList',$imagesList);
        $this->assign('imgcount',count($imagesList));
        $this->assign('typeList',$typeList);
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = ''){
        $redirect = $this->redirect;
        return parent::editPost($request, $redirect);
    }

    public function delete() {
        return parent::delete();
    }

    public function detail(Request $request) {
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', url($this->redirect));
        }
        $info->organization = $this->getOrganization($info->createOper);

        return $this->view->fetch('detail', [
            'info' => $info
        ]);
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
            if(isset($filter['contentType']) && $filter['contentType']){
                $where['contentType'] = $filter['contentType'];
            }
            if(isset($filter['title']) && $filter['title']){
                $where['title'] = ['like', '%'.$filter['title'].'%'];
            }
        }

        return $where;
    }


}
