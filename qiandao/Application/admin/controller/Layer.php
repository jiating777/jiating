<?php

namespace app\admin\controller;

use think\Request;
use think\Controller;
use app\common\BaseHelper;

use app\admin\model\School;
use app\admin\model\Area;

class Layer extends Controller
{   
    //所有学校列表--服务器端分页
    public function schoolList(){
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $where = [];
        $search = json_decode($param['search']['value'],true);
        if(isset($search['name']) && $search['name']){  //搜索
            $where['name'] = ['like','%'.$search['name'].'%'];
        }

        $plist = School::field('id,name,code')->where($where)->limit($start,$length)->select();
        $count = School::where($where)->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
            'test' => $where
        ];
        return json($res);

    }


}
