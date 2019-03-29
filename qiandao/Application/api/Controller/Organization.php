<?php

namespace app\api\controller;


use app\admin\model\Organization AS OrganizationMdl;
use app\admin\model\Member;


class Organization extends BaseController
{
    /**
     * @return \think\response\Json
     * 组织列表
     */
    public function getList()
    {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId不能为空');
        }

        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }
        $where['townId'] = $param->townId;
        if(!empty($param->villageId)) {
            $where['villageId'] = $param->villageId;
        }

        $list = OrganizationMdl::where($where)->order('sorting ASC')->limit($start, $length)->select();
        $total = OrganizationMdl::where($where)->count();

        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到组织信息');
        }
        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);

    }

    public function partyList()
    {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;
        if (empty($param->organizationId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'organizationId不能为空');
        }

        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $list = Member::where(['organizationId'=>$param->organizationId])->limit($start, $length)->select();
        $total = Member::where(['organizationId'=>$param->organizationId])->count();

        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到成员信息');
        }
        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);
    }

}