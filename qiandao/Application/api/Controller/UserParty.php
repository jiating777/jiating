<?php

namespace app\api\controller;

use app\admin\model\User;
use app\admin\model\Member;
use app\admin\model\Organization;
use app\admin\model\Classhour;
use app\admin\model\Joinexam;
use app\admin\model\Joinmeeting;
use app\admin\model\Joinresearch;

use think\Request;

/**
 * 党员中心
 */
class UserParty extends BaseController
{

    /**
     * 获取我的组织
     *
     * @return \think\response\Json
     */
    public function getMyOrganizations()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
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

        $userId = $param->userId;
        $userInfo = $this->getUserInfo(['id' => $userId]);
        if(!$userInfo->memberId || $userInfo->memberId === 0){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你还不是村民');
        }
        $organizationId = $userInfo->organizationId;
        if(!$organizationId){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你还不是党员');
        }

        $organizationName = Organization::where(['id' => $organizationId])->value('name');
        $where['organizationId'] = $organizationId;
        $result = Member::where($where)->limit($start, $length)->select();
        $total = Member::where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到组织成员');
        }
        $data = [
            'organizationName' => $organizationName,
            'memberList' => $result
        ];

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data, $total);
    }

    /**
     * 获取我的学习计划
     *
     * @return \think\response\Json
     */
    public function getMyClassrooms()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->status) && $param->status != 0) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'status 不能为空');
        }
        if (!in_array($param->status, [0, 1])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'status 格式不正确');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $userId = $param->userId;
        $where = [
            'a.userId' => $userId,
            'a.status' => $param->status
        ];

        $model = db('joinclassroom');
        $fields = 'a.id as joinId, a.status, c.id as classId, c.title, c.imgUrl';
        $result = $model->alias('a')->where($where)->join('__MICROCLASSROOM__ c', 'a.classId = c.id')->limit($start, $length)->field($fields)->select();
        $total = $model->alias('a')->where($where)->join('__MICROCLASSROOM__ c', 'a.classId = c.id')->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到课堂');
        }

        foreach ($result as &$item) {
            $classHours = Classhour::where(['classId' => $item['classId']])->count();
            $item['classHours'] = $classHours;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取我的学习计划详情
     *
     * @return \think\response\Json
     */
    public function getMyClassroomDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->classId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'classId 不能为空');
        }

        $userId = $param->userId;
        $classId = $param->classId;
        $where = [
            'a.userId' => $userId,
            'a.classId' => $classId
        ];

        $model = db('joinclassroom');
        $fields = 'a.id as joinId, a.status, c.id as classId, c.title, c.imgUrl';
        $item = $model->alias('a')->where($where)->join('__MICROCLASSROOM__ c', 'a.classId = c.id')->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关课堂');
        }

        // 课时
        $field = 'id, name, fileUrl';
        $classhour = Classhour::where(['classId' => $classId])->field($field)->order('sorting ASC')->select();
        $classHours = Classhour::where(['classId' => $classId])->count();
        $item['classHours'] = $classHours;
        $classroomresultsDB = db('classroomresults');
        foreach ($classhour as &$value) {
            $status = $classroomresultsDB->where(['joinId' => $item['joinId'], 'classhourId' => $value['id']])->value('status');
            if($status && $status == 1){
                $status = 1;
            }else{
                $status = 0;
            }
            $value->status = $status;
        }
        $item['classhour'] = $classhour;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 取消学习计划
     */
    public function cancelClassroom()
    {
        $param = self::getHttpParam();

        if (empty($param->joinId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'joinId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $joinId = $param->joinId;
        $model = db('joinclassroom');
        $where = [
            'joinId' => $joinId,
            'userId' => $param->userId,
        ];
        $result = $model->where($where)->delete();

        if($result !== false){
            $classroomresultsDB = db('classroomresults');
            $classroomresultsDB->where(['joinId' => $joinId])->delete();

            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '取消学习失败');
        }
    }

    /**
     * 获取我的考试
     *
     * @return \think\response\Json
     */
    public function getMyOnlineexams()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $userId = $param->userId;
        $where = [
            'a.userId' => $userId,
        ];

        $model = db('joinexam');
        $fields = 'a.id as joinId, a.score, e.id as examId, e.name, e.startTime, e.endTime, e.passingGrade';
        $result = $model->alias('a')->where($where)->join('__ONLINEEXAM__ e', 'a.examId = e.id')->limit($start, $length)->field($fields)->select();
        $total = $model->alias('a')->where($where)->join('__ONLINEEXAM__ e', 'a.examId = e.id')->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到考试');
        }

        foreach ($result as &$item) {
            $joinExams = Joinexam::where(['examId' => $item['examId']])->count();
            // 参与人数
            $item['joinExams'] = $joinExams;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取我的会议
     *
     * @return \think\response\Json
     */
    public function getMyMeetings()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $userId = $param->userId;
        $userInfo = $this->getUserInfo(['id' => $userId]);
        if(!$userInfo->memberId || $userInfo->memberId == 0){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你还不是村民');
        }
        $where = [
            'a.memberId' => $userInfo->memberId,
        ];

        $model = db('joinmeeting');
        $fields = 'a.id as joinId, a.isSign, m.id as meetingId,m.title, m.startTime, m.endTime, m.address';
        $result = $model->alias('a')->where($where)->join('__MEETING__ m', 'a.meetingId = m.id')->limit($start, $length)->field($fields)->select();
        $total = $model->alias('a')->where($where)->join('__MEETING__ m', 'a.meetingId = m.id')->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到会议');
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 会议扫码签到
     *
     * @return \think\response\Json
     */
    public function meetingSignIn()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->meetingId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'meetingId 不能为空');
        }

        $userId = $param->userId;
        $userInfo = $this->getUserInfo(['id' => $userId]);
        if(!$userInfo->memberId || $userInfo->memberId == 0){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你还不是村民');
        }
        $where = [
            'meetingId' => $param->meetingId,
            'memberId' => $userInfo->memberId,
        ];
        $model = db('joinmeeting');
        $info = $model->where($where)->find();
        if(!$info){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你还不是会议成员');
        }
        if($info['isSign'] == 1){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你已经签到了');
        }
        $result = $model->where($where)->update(['isSign' => 1]);

        if($result !== false){
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, '签到成功');
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '签到失败');
        }
    }

    /**
     * 获取我参与的调研
     *
     * @return \think\response\Json
     */
    public function getMyResearchs()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $userId = $param->userId;
        $where = [
            'a.userId' => $userId,
        ];

        $model = db('joinresearch');
        $fields = 'a.id as joinId, r.id as researchId, r.name, r.endTime, r.imgUrl';
        $result = $model->alias('a')->where($where)->join('__RESEARCH__ r', 'a.researchId = r.id')->limit($start, $length)->field($fields)->select();
        $total = $model->alias('a')->where($where)->join('__RESEARCH__ r', 'a.researchId = r.id')->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到调研');
        }

        foreach ($result as &$item) {
            // 参与人数
            $joinResearchs = Joinresearch::where(['researchId' => $item['researchId']])->count();
            $item['joinResearchs'] = $joinResearchs;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

}