<?php

namespace app\api\controller;

use app\admin\model\Meeting as MeetingMdl;
use app\admin\model\Joinmeeting;

use think\Request;

/**
 * 会议
 */
class Meeting extends BaseController
{

    /**
     * 获取会议列表
     *
     * @return \think\response\Json
     */
    public function getMeetingList()
    {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $where = [
            'townId' => $param->townId
        ];
        if (isset($param->villageId) && $param->villageId) {
            $where = [
                'villageId' => $param->villageId
            ];
        }
        $model = db('meeting');
        $fields = 'id, title, startTime, endTime, address';
        $result = $model->where($where)->limit($start, $length)->field($fields)->select();
        $total = $model->where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到会议');
        }

        /*foreach ($result as $item){
            //$item->date = date('Y-m-d', strtotime($item->startTime));
            $item->date = date('Y-m-d', strtotime($item->startTime));
            //$item->startTime2 = substr($item->startTime, 11);
            $item->startTime2 = date('H:i', strtotime($item->startTime));
            $item->endTime2 = date('H:i', strtotime($item->endTime));
        }*/

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取会议详情
     *
     * @return \think\response\Json
     */
    public function getMeetingDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }

        if (empty($param->meetingId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'meetingId 不能为空');
        }

        $meetingId = $param->meetingId;
        $where = [
            'townId' => $param->townId,
            'id' => $meetingId
        ];
        if (isset($param->villageId) && $param->villageId && $param->villageId != 'undefined') {
            $where = [
                'villageId' => $param->villageId
            ];
        }
        $fields = 'id, title, startTime, endTime, address, content';

        /*$item = MeetingMdl::where($where)->field($fields)->find();
        $item->date = date('Y-m-d', strtotime($item->startTime));
        $item->startTime2 = date('H:i', strtotime($item->startTime));
        $item->endTime2 = date('H:i', strtotime($item->endTime));*/
        $model = db('meeting');
        $item = $model->where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关会议');
        }

        // 参会人员
        $join = [
            ['__MEMBER__ m', 'a.memberId = m.id'],
        ];
        $field = 'm.avatar, m.name';
        $joinmeeting = Joinmeeting::alias('a')->where(['meetingId' => $meetingId])->join($join)->field($field)->select();
        $item['joinmeeting'] = $joinmeeting;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

}