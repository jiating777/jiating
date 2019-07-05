<?php

namespace app\admin\controller;

use think\Request;
use think\Controller;
use app\common\BaseHelper;

use app\admin\model\School;
use app\admin\model\Record;
use app\admin\model\Course;

class Layer extends Controller
{   
    //所有学校列表--除去已设置作息表的学校
    public function schoolList(){
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数
        $ids = [];
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

    public function schoolList2(){
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $plist = School::field('id,name,code')->limit($start,$length)->select();
        $count = School::count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
        ];
        return json($res);

    }

    public function recordList() {
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $plist = Record::join('user u','record.studentId=u.id')->join('course c','record.classId=c.id')->field('record.*,u.name,c.classname')->where('studentId='.$param['studentId'])->limit($start,$length)->select();
        $count = Record::where('studentId='.$param['studentId'])->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
        ];
        return json($res);
    }

    public function classList()
    {
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $plist = Course::join('user u','course.createrId=u.id')->field('course.*,u.name')->where('createrId='.$param['createId'])->limit($start,$length)->select();
        $count = Course::join('user u','course.createrId=u.id')->field('course.*,u.name')->where('createrId='.$param['createId'])->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
        ];
        return json($res);
    }


}
