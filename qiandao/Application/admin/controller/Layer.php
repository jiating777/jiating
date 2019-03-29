<?php

namespace app\admin\controller;

use think\Request;
use think\Controller;
use app\common\BaseHelper;

use app\admin\model\Member;
use app\admin\model\Povertymember;
use app\admin\model\Povertyparty;
use app\admin\model\Area;
use app\admin\model\Villages;

class Layer extends Controller
{   
    //所有村民
    public function memberList(){
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $where = ['isDelete'=>2];
        $search = json_decode($param['search']['value'],true);
        if(isset($search['name']) && $search['name']){  //搜索
            $where['name'] = ['like','%'.$search['name'].'%'];
        }
        if(isset($param['villageId']) && $param['villageId']) {
            $where['villageId'] = $param['villageId'];
        }

        $plist = Member::field('id,name,avatar,gender,birthday,identityNumber,mobile')->where($where)->limit($start,$length)->select();
        $count = Member::where($where)->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
            'test' => $param
        ];
        return json($res);

    }

    //帮扶人列表-去除贫困户-服务器端分页
    public function memberAidList() {

        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $where = ['villageId'=>session('ADMIN')['villageId'],'isDelete'=>2];
        $search = json_decode($param['search']['value'],true);
        if($search['name']){  //搜索
            $where['name'] = ['like','%'.$search['name'].'%'];
        }

        $poverty = Povertymember::where('villageId',session('ADMIN')['villageId'])->select();
        $povertyIds = [];
        foreach ($poverty as $v) {
            $povertyIds[] = $v['memberId'];
        }
        $where['id'] = ['not in',implode(',', $povertyIds)];

        $plist = Member::field('id,name,avatar,gender,birthday,identityNumber,mobile')->where($where)->limit($start,$length)->select();
        $count = Member::where($where)->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count
        ];
        return json($res);

    }


    //帮扶人列表-去除贫困户-客户端分页
    public function memberAidList2() {

        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $where = ['isDelete'=>2];

        $poverty = Povertymember::where('villageId',session('ADMIN')['villageId'])->select();
        $povertyIds = [];
        foreach ($poverty as $v) {
            $povertyIds[] = $v['memberId'];
        }
        $where['id'] = ['not in',implode(',', $povertyIds)];

        $plist = Member::field('*')->where($where)->select();
        $count = Member::where($where)->count();
        $res = [
            'status' => '1',
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count
        ];
        return json($res);

    }

    //所有贫困户-服务器分页
    public function povertymember() {
        $request = Request::instance();
        $param = $request->param();

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $where = [];
        $search = json_decode($param['search']['value'],true);
        if($search['name']){  //搜索
            $where['name'] = ['like','%'.$search['name'].'%'];
        }

        if(isset($param['villageId']) && $param['villageId']) {
            $where['a.villageId'] = $param['villageId'];
        }

        $plist = Povertymember::alias('a')->join('member b','a.memberId=b.id')->field('b.id,b.name,b.gender,b.birthday,b.identityNumber,a.aidingId')->where($where)->where('b.isDelete',2)->limit($start,$length)->select();
        $count = Povertymember::where(['villageId'=>session('ADMIN')['villageId']])->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count
        ];
        return json($res);
    }

    //所有贫困户-客户端分页
    public function povertymember2() {
        $request = Request::instance();
        $param = $request->param();

        $where = [];
        if(isset($param['villageId']) && $param['villageId']) {
            $where['a.villageId'] = $param['villageId'];
        }
        if(isset($param['townId']) && $param['townId']) {
            $where['a.townId'] = $param['townId'];
        }
        if(isset($param['xianId']) && $param['xianId']) {
            $where['a.xianId'] = $param['xianId'];
        }
        if(isset($param['cityId']) && $param['cityId']) {
            $where['a.cityId'] = $param['cityId'];
        }
        $plist = Member::alias('a')->join('povertymember b','a.id=b.memberId')->field('a.id,a.name,a.gender,a.birthday,a.identityNumber,b.aidingId')->where($where)->where('a.isPoverty',1)->select();
        // $plist = Povertymember::alias('a')->join('member b','a.memberId=b.id')->field('b.id,b.name,b.gender,b.birthday,b.identityNumber,a.aidingId')->where($where)->where('b.isDelete',2)->select();
        $count = Povertymember::where(['villageId'=>session('ADMIN')['villageId']])->count();
        $res = [
            'status' => '1',
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
            'test' => $param
        ];
        return json($res);
    }

    //所有党员
    public function partyList() {
        $request = Request::instance();
        $param = $request->param();

        $start = $param['start'];  //每页起始条数
        $length = $param['length'];  //每页条数

        $where = ['isParty'=>1,'a.isDelete'=>2];
        $search = json_decode($param['search']['value'],true);
        if($search['name']){  //搜索
            $where['a.name'] = ['like','%'.$search['name'].'%'];
        }
        if(isset($param['villageId']) && $param['villageId']) {
            $where['a.villageId'] = $param['villageId'];
        }
        if(isset($param['townId']) && $param['townId']) {
            $where['a.townId'] = $param['townId'];
        }

        $plist = Member::alias('a')->join('organization b','a.organizationId=b.id')->field('a.id,a.name,a.gender,a.job,b.name OrgName')->where($where)->limit($start,$length)->select();
        $count = Member::alias('a')->where($where)->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count
        ];
        return json($res);
    }

    /**
     * 根据条件获取村民
     */
    public function getMembers() {
        $request = Request::instance();
        $param = $request->param();
        // return $param;

        $start = $param['start'];  // 每页起始条数
        $length = $param['length'];  // 每页条数

        $where = [
            // 'villageId' => session('ADMIN')['villageId'],
            'isDelete' => 2
        ];
        $search = json_decode($param['search']['value'],true);
        if(is_array($search) && count($search) > 0){
            foreach ($search as $key => $value) {
                if(is_array($value)){
                    $where[$key] = [$value[1], $value[0]];
                }else{
                    $where[$key] = ['=', $value];
                }
            }
        }

        $plist = Member::field('id,name,avatar,gender,birthday,identityNumber,mobile')->where($where)->limit($start, $length)->select();
        $count = Member::where($where)->count();
        $res = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $plist,
            'recordsFiltered' => $count,
            'recordsTotal' => $count
        ];

        return json($res);
    }

    public function getCounty() {   //获取县、镇数据
        $request = Request::instance();
        $param = $request->param();
        $pid = $param['id'];

        $areaMdl = new Area();
        // $list = Area::where(['parentId'=>$pid,'level'=>$level])->select();
        $list = BaseHelper::makeOptions(
            $areaMdl,
            ['parentId' => $pid],
            ['id' => 'asc']
        );
        return json($list);
    }

    public function getVillage() {  //根据镇id获取村列表
        $request = Request::instance();
        $param = $request->param();
        $pid = $param['id'];

        $villageMdl = new Villages();
        $list = BaseHelper::makeOptions(
            $villageMdl,
            ['townId' => $pid],
            ['id' => 'asc']
        );
        return json($list);
    }

    public function getOrganization() {
        $request = Request::instance();
        $param = $request->param();
        $where = [];
        if(isset($param['villageId']) && $param['villageId']) {
            $where['villageId'] = $param['villageId'];
        }
        if(isset($param['townId']) && $param['townId']) {
            $where['townId'] = $param['townId'];
        }
        if(isset($param['xianId']) && $param['xianId']) {
            $where['xianId'] = $param['xianId'];
        }
        if(isset($param['cityId']) && $param['cityId']) {
            $where['cityId'] = $param['cityId'];
        }
        $list = \app\admin\model\Organization::where(['isDelete'=>2])->where($where)->select();
        $return = [];
        foreach ($list as $v) {
            $return[$v['id']] = $v['name'];
        }
        return json($return);
    }

}
