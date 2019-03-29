<?php 

/**
 * 精准扶贫-贫困户
 */

namespace app\admin\controller;

use think\Request;
use app\common\Common;
use app\common\BaseHelper as Helper;

class Povertymember extends Base{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'povertymember';
        // $this->defaultWhere = ['villageId'=>$this->admin->villageId];
        $this->searchFields = [
            'cityId' => [
                'label'     => '',
                'field'     => 'cityId',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => '=',
            ],
            'xianId' => [
                'label'     => '',
                'field'     => 'xianId',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => '=',
            ],
            'townId' => [
                'label'     => '',
                'field'     => 'townId',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => '=',
            ],
            'villageId' => [
                'label'     => '',
                'field'     => 'villageId',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => '=',
            ],
        ];
    }

    public function index() {
        return parent::index();
    }

    public function add(Request $request) {
        $povertyreason = povertyreason();
        $aidingplan = aidingplan();      
        $povertydegree = povertydegree();
        $outpoverty = outpoverty();
        $this->assign('povertyreason',$povertyreason);
        $this->assign('aidingplan',$aidingplan);  
        $this->assign('povertydegree',$povertydegree);
        $this->assign('outpoverty',$outpoverty);
        
        $model = model($this->model);
        $list = $model->where('villageId',$this->admin->villageId)->select();//查询出已是贫困户的memberID
        $ids = [];
        foreach ($list as $v) {
            $ids[] = $v['memberId'];
        }
        $this->assign('tagId',implode(',', $ids));
        return parent::add($request);
    }
    
    public function addPost(Request $request, $redirect='') {
        return parent::addPost($request, $redirect);
    }


    public function edit(Request $request) {
        $povertyreason = povertyreason();
        $aidingplan = aidingplan();      
        $povertydegree = povertydegree();
        $outpoverty = outpoverty();
        $this->assign('povertyreason',$povertyreason);
        $this->assign('aidingplan',$aidingplan);  
        $this->assign('povertydegree',$povertydegree);
        $this->assign('outpoverty',$outpoverty);
        //查询出已是贫困户的memberID
        $model = model($this->model);
        $list = $model->where('villageId',$this->admin->villageId)->select();
        $ids = [];
        foreach ($list as $v) {
            $ids[] = $v['memberId'];
        }
        $this->assign('tagId',implode(',', $ids));
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = ''){
        return parent::editPost($request, $redirect);
    }

}