<?php

namespace app\api\controller;

use app\admin\model\Povertymember;
use app\admin\model\Povertyparty;
use app\admin\model\Povertywork;
use app\admin\model\Ethnic;
use app\admin\model\Operator;


class Poverty extends BaseController {
    /**
     * 贫困户各类人数查询
     * @DateTime 2018-06-11
     * @return
     */
    public function outpovertyCount() {
        $param = self::getHttpParam();
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId不能为空');
        }
        $poverty1 = povertymember::where(['outpoverty'=>1,'villageId'=>$param->villageId])->count();   //未脱贫
        $poverty2 = povertymember::where(['outpoverty'=>2,'villageId'=>$param->villageId])->count();   //已脱贫
        $poverty3 = povertymember::where(['outpoverty'=>3,'villageId'=>$param->villageId])->count();   //返贫
        $data = [
            'poverty' => $poverty1,
            'outPoverty' => $poverty2,
            'againPoverty' => $poverty3,
        ];
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    //贫困户列表
    public function povertymember() {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId不能为空');
        }
        $where = ['villageId'=>$param->villageId];
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }
        if(!empty($param->memberId)) {  //查询某一帮扶干部下的贫困户
            $where['aidingId'] = $param->memberId;
        }

        if(!empty($param->status)) {  //1：未脱贫 2：已脱贫 3：返贫
            $where['outpoverty'] = $param->status;
        }

        $ethnic = Ethnic::select();  //民族
        $ethnicMap = [];
        foreach ($ethnic as $e) {
            $ethnicMap[$e['id']] = $e['name'];
        }

        $operatorMap = Operator::getAllOperatorJob($param->villageId);

        $list =  Povertymember::where($where)->limit($start, $length)->select();
        $total = Povertymember::where($where)->count();

        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到贫苦户信息');
        }
        foreach ($list as $k => $v) {
            $list[$k]['member'] = $v->member;   //贫苦户基本信息
            $list[$k]['member']['ethnicId'] = $ethnicMap[$list[$k]['member']['ethnicId']];
            $list[$k]['memberaid'] = $v->memberaid;  //帮扶人信息
            $list[$k]['memberaid']['job'] = isset($operatorMap[$list[$k]['memberaid']['id']]) ? $operatorMap[$list[$k]['memberaid']['id']][0] : '';

        }
        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);
    }

    //贫苦户详情
    public function povertymemberDetail() {
        $param = self::getHttpParam();
        if(empty($param->id)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'id不能为空');
        }
        $row = Povertymember::where('id',$param->id)->find();
        if(empty($row)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到贫苦户信息');
        }
        $row['aidingPlan'] = viewAidingplan($row['aidingPlan']);
        $row['member'] = $row->member;
        $row['memberaid'] = $row->memberaid;
        $jobRow = operator::alias('a')->join('operatorjob c','a.id=c.operatorId')->join('organizationjob d','c.jobId=d.id')->field('a.*,d.name')->where(['a.memberId' => $row['memberaid']['id']])->order('d.sorting,createDate DESC')->find();
        $ethnic = Ethnic::get($row['member']['ethnicId']);
        $row['member']['ethnicId'] = $ethnic['name'];
        $row['memberaid']['job'] = $jobRow ? $jobRow['name'] : '';
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $row);
    }

    //扶贫干部列表
    public function partyList() {
        $param = self::getHttpParam();
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId不能为空');
        }
        $list =  Povertyparty::where(['villageId'=>$param->villageId])->select();
        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到贫苦户信息');
        }
        $operatorMap = Operator::getAllOperatorJob($param->villageId);
        foreach ($list as $k => $v) {
            $list[$k]['member'] = $v->member;
            $list[$k]['member']['job'] = isset($operatorMap[$v['memberId']]) ? $operatorMap[$v['memberId']][0] : '';
        }
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list);
    }

    //帮扶工作列表
    public function povertywork() {
        $param = self::getHttpParam();
        $start = 0;
        $length = 10;
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId不能为空');
        }

        $where = ['townId'=>$param->townId,'isDelete'=>2];
        if(!empty($param->villageId)) {
            $where['villageId'] = $param->villageId;
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }
        if(!empty($param->povertypartyId)) {  //查询某一帮扶干部下的帮扶工作
            $where['povertypartyId'] = $param->povertypartyId;
        }
        
        $list =  Povertywork::where($where)->limit($start, $length)->order('createDate DESC')->select();
        $total = Povertywork::where($where)->count();

        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到帮扶工作信息');
        }
        foreach ($list as $k => $v) {
            $list[$k]['member'] = $v->member1;   //贫困户基本信息
            $list[$k]['memberaid'] = $v->member2;  //帮扶人信息
            $list[$k]['image'] = $v->image;
            unset($list[$k]['member1']);
            unset($list[$k]['member2']);
        }
        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);
    }

    //扶贫项目分类列表
    public function povertyprojectType(){
        $list = \app\admin\model\Povertyprojecttype::order('sorting')->select();
        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到分类信息');
        }
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list);
    }

    //扶贫项目列表
    public function povertyproject() {
        $param = self::getHttpParam();
        $start = 0;
        $length = 10;
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId不能为空');
        }
        $where = ['townId'=>$param->townId,'isDelete'=>2];
        if(!empty($param->villageId)) {
            $where['villageId'] = $param->villageId;
        }
        if(!empty($param->type) && $param->type != 0) {
            $where['typeId'] = $param->type;
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $list = \app\admin\model\Povertyproject::where($where)->field('id,imgUrl,title,status')->limit($start, $length)->order('createDate DESC')->select();
        $total = \app\admin\model\Povertyproject::where($where)->count();
        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到帮扶项目信息');
        }
        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);
    }

    //扶贫项目详情
    public function povertyprojectDetail() {
        $param = self::getHttpParam();
        $id = $param->id;  //扶贫项目ID
        $where = ['a.id'=>$id,'a.isDelete'=>2];
        $row = \app\admin\model\Povertyproject::alias('a')->join('member m','a.memberId=m.id')->field('a.*,m.name povertyName')->where($where)->find()->toArray();
        if(empty($row) || !$row) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到详情');
        }
        $poverty = Povertymember::alias('a')->join('member m','a.aidingId=m.id')->join('organization o','m.organizationId=o.id')->where(['memberId'=>$row['memberId']])->field('m.name partyName,m.job,o.name orgName')->find();
        $detail = \app\admin\model\Povertyprojectdetail::where(['projectId'=>$id,'isDelete'=>2])->select();
        foreach ($detail as $k => $v) {
            $detail[$k]['content'] = $row['type'] == 1 ? '捐助'.$v['donateContent'].$v['donateNum'].'件' : '捐助金额'.$v['donateMoney']/100 . '元' ;
        }
        $row['partyName'] = $poverty['partyName'];
        $row['job'] = $poverty['orgName'].$poverty['job'];
        $row['detail'] = $detail;
        $row['image'] = \app\admin\model\Image::where(['relatedId' => $id, 'tag' => 'imglist'])->select();

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $row);

        

    }



}