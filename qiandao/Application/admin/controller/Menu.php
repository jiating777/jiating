<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;

use app\admin\model\Menu AS MenuMdl;
use app\admin\model\Villageimages;

/**
 * Class Menu
 * @package app\admin\controller
 */
class Menu extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){
        $request = Request::instance();
        $id = $request->param('id');
        $where = ['parentId'=>$id,'status'=>1];
        if(session('ADMIN')['type'] != 0) {
            $where['id'] = ['in',$this->access];
            if(session('ADMIN')['type'] == 6) {
                $where['id'] = ['not in',config('auth.sys_auth')];
            }
        }

        $list = MenuMdl::where($where)->order('sorting ASC')->select();

        $this->assign('list',$list);
        return $this->fetch('index');
    }

}