<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;

use think\Request;

/**
 * 农事专题
 */
class Knowledgetype extends Base
{
    public function _initialize() {
        parent::_initialize();
        $this->model = 'knowledgetype';
        $this->redirect = 'admin/knowledgetype/index';
        $this->defaultOrder = 'sorting';
        $this->searchFields = [
            'name' => [
                'label'     => '标题',
                'field'     => 'name',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'

            ],
        ];
    }

    public function addPost(Request $request, $redirect = '') {
        $redirect = $this->redirect;
        return parent::addPost($request, $redirect);
    }


    public function editPost(Request $request, $redirect = ''){
        $redirect = $this->redirect;
        return parent::editPost($request, $redirect);
    }

    public function delete() {
        return parent::delete();
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
            if(isset($filter['name']) && $filter['name']){
                $where['name'] = ['like', '%'.$filter['name'].'%'];
            }
        }

        return $where;
    }


}
