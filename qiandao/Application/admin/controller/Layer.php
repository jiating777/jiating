<?php

namespace app\admin\controller;

use think\Request;
use think\Controller;
use app\common\BaseHelper;

use app\admin\model\School;

class Layer extends Controller
{   
    //所有学校列表--除去已设置作息表的学校
    public function schoolList(){
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $sList = model('schedule')->field('schoolId')->select();
        foreach ($sList as $v) {
            $ids[] = $v['schoolId'];
        }
        $where = [];
        $search = json_decode($param['search']['value'],true);
        if(isset($search['name']) && $search['name']){  //搜索
            $where['name'] = ['like','%'.$search['name'].'%'];
        }

        $plist = School::field('id,name,code')->where('id not in ('.implode(',', $ids).')')->where($where)->limit($start,$length)->select();
        $count = School::where($where)->where('id not in ('.implode(',', $ids).')')->count();
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
