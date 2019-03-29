<?php

namespace app\admin\controller;

use think\Controller;

/**
 * 地区
 */
class Region extends Controller
{

    protected $model = '';

    public function _initialize()
    {
        $this->model = db('area');
    }

    /**
     * 获取地区
     */
    public function getRegion(){
        $params = $this->request->param();
        $parentId = $params['p_id'];
        $selected = isset($params['selected']) ? $params['selected'] : 0;
        $level = $params['level'];
        $data = $this->model->where("parentId = {$parentId} and level = {$level}")->select();
        $html = '';
        if($data){
            foreach($data as $item){
                if($item['id'] == $selected){
                    $html .= "<option value='{$item['id']}' selected>{$item['name']}</option>";
                } else {
                    $html .= "<option value='{$item['id']}'>{$item['name']}</option>";
                }
            }
        }

        return json($html);
    }

    /**
     * 获取村
     */
    public function getVillage(){
        $params = $this->request->param();
        $parentId = $params['townId'];
        $selected = isset($params['selected']) ? $params['selected'] : 0;
        $data = db('villages')->where("townId = {$parentId}")->select();
        $html = '';
        if($data){
            foreach($data as $item){
                if($item['id'] === $selected){
                    $html .= "<option value='{$item['id']}' selected >{$item['name']}</option>";
                } else {
                    $html .= "<option value='{$item['id']}'>{$item['name']}</option>";
                }
            }
        }

        return json($html);
    }

}