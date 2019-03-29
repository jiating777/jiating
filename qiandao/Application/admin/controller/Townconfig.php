<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15 0015
 * Time: 下午 2:37
 */
namespace app\admin\controller;

use think\Request;

class Townconfig extends Base
{

    public function _initialize(){
        parent::_initialize();
    }
    public function config(){
        $townId = session('ADMIN')['townId'];
        $config = '';
        $Townconfig = \app\admin\model\Townconfig::where('townId',$townId)->find();
        if($Townconfig){
            $config = $Townconfig;
        }
        $this->assign('config',$config);
        $this->assign('townId',$townId);
        return $this->fetch();
    }

    public function configPost(Request $request){
        if($request->isPost()){
            $param = $request->param();
            $Townconfig = \app\admin\model\Townconfig::where('townId',$param['townId'])->find();
            if($Townconfig){
                //更新
                $Townconfig->dongtaioff = $param['dongtaioff'];
                $Townconfig->pinglunoff = $param['pinglunoff'];
                $Townconfig->dongtaicheckoff = $param['dongtaicheckoff'];
                $Townconfig->save();
            }else{
                $Townconfig = new \app\admin\model\Townconfig();
                //增加
                $Townconfig->townId = $param['townId'];
                $Townconfig->dongtaioff = $param['dongtaioff'];
                $Townconfig->pinglunoff = $param['pinglunoff'];
                $Townconfig->dongtaicheckoff = $param['dongtaicheckoff'];
                $Townconfig->save();
            }

            return $this->success('修改成功！', 'admin/' . strtolower($this->model) . '/config');
        }
    }
}