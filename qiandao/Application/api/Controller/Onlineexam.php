<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Onlineexam as OnlineexamMdl;
use app\admin\model\Examquestions;
use app\admin\model\Joinexam;

use think\Request;

/**
 * 在线考试
 */
class Onlineexam extends BaseController
{

    /**
     * 获取在线考试列表
     *
     * @return \think\response\Json
     */
    public function getOnlineexamList()
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
        $model = db('onlineexam');
        $fields = 'id, name, startTime, endTime';
        $result = $model->where($where)->limit($start, $length)->field($fields)->select();
        $total = $model->where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到在线考试');
        }

        foreach ($result as &$item) {
            $joinExams = Joinexam::where(['examId' => $item['id']])->count();
            // 参与人数
            $item['joinExams'] = $joinExams;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取在线考试详情
     *
     * @return \think\response\Json
     */
    public function getOnlineexamDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }

        if (empty($param->examId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'examId 不能这空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能这空');
        }

        $examId = $param->examId;
        $userId = $param->userId;
        $where = [
            'townId' => $param->townId,
            'id' => $examId
        ];
        if (isset($param->villageId) && $param->villageId) {
            //$where['villageId'] = $param->villageId;
            $where = [
                'villageId' => $param->villageId
            ];
        }
        $fields = 'id, name, startTime, endTime, passingGrade, explain';
        $model = db('onlineexam');
        $item = $model->where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关在线考试');
        }

        // 总分
        $totalScore = Examquestions::where(['examId' => $examId])->sum('score');
        $item['totalScore'] = $totalScore;
        // 考题数
        $totalExams = Examquestions::where(['examId' => $examId])->count();
        $item['totalExams'] = $totalExams;
        // 是否已参加
        $isJoin = db('joinexam')->where(['examId' => $examId, 'userId' => $userId])->find();
        if($isJoin){
            $isJoin = 1;
        }else{
            $isJoin = 2;
        }
        $item['isJoin'] = $isJoin;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 获取考题
     */
    public function getExamquestion()
    {
        $param = self::getHttpParam();

        if (empty($param->examId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'examId 不能为空');
        }
        if (empty($param->number)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'number 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $examId = $param->examId;
        $where = [
            'examId' => $examId,
            'number' => $param->number
        ];
        $fields = 'id, examId, number, subject, score, type, option';
        $item = Examquestions::where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到该题');
        }

        // 用户答案
        $userAnswer = db('examresults')->where(['userId' => $param->userId, 'questionId' => $item->id])->value('answer');
        $item->userAnswer = $userAnswer ? $userAnswer : '';
        $options = json_decode($item->option, true);
        foreach($options as &$option){
            $option['isChecked'] = 0;
            if($userAnswer){
                if(in_array($option['option_NO'], explode(',', $userAnswer))){
                    $option['isChecked'] = 1;
                }
            }
            // 去除正确答案
            unset($option['option_isCorrect']);
        }
        $item->option = $options;

        if($param->number == 1){
            $examInfo = db('onlineexam')->where(['id' => $examId])->find();
            if($examInfo['startTime'] > time() || time() > $examInfo['endTime']){
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '还没到考试开始时间');
            }
            // 参加考试 开始
            $beginJoin = $this->beginJoinexam(['examId' => $examId, 'userId' => $param->userId]);
            if(!$beginJoin){
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '参加考试失败');
            }
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 提交考题答案
     */
    public function placeExamquestion()
    {
        $param = self::getHttpParam();

        if (empty($param->examId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'examId 不能为空');
        }
        if (empty($param->number)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'number 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->answer)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'answer 不能为空');
        }
        if(!preg_match('/^[A-E,]+$/', $param->answer)){
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'answer 格式不正确');
        }

        $examId = $param->examId;
        $joinexamInfo = $this->getJoinexamInfo(['examId' => $examId, 'userId' => $param->userId]);
        if(!$joinexamInfo){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交考题答案失败');
        }
        $questionInfo = $this->getQuestionInfo(['examId' => $examId, 'number' => $param->number]);
        if(!$questionInfo){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交考题答案失败');
        }

        $examresults = db('examresults');
        if($param->answer == $questionInfo['correctAnswer']){
            $isCorrect = 1;
            $score = $questionInfo['score'];
        }else{
            $isCorrect = 2;
            $score = 0;
        }

        $where = [
            'joinId' => $joinexamInfo['id'],
            'questionId' => $questionInfo['id'],
            'userId' => $param->userId,
        ];
        $info = $examresults->where($where)->find();
        if($info){
            // 更新
            $data = [
                'id' => $info['id'],
                'answer' => $param->answer,
                'isCorrect' => $isCorrect,
                'score' => $score,
            ];
            $result = $examresults->update($data);
        }else{
            // 添加
            $data = [
                'id' => BaseHelper::getUUID(),
                'joinId' => $joinexamInfo['id'],
                'questionId' => $questionInfo['id'],
                'userId' => $param->userId,
                'answer' => $param->answer,
                'isCorrect' => $isCorrect,
                'score' => $score,
            ];
            $result = $examresults->insert($data);
        }

        if($result !== false){
            // 最后一题
            if(isset($param->isLast) && $param->isLast){
                $examInfo = db('onlineexam')->where(['id' => $examId])->find();
                if(time() > $examInfo['endTime']){
                    return show(config('status.ERROR_STATUS'), self::NOT_DATA, '已过考试结束时间');
                }
                $endJoin = $this->endJoinexam(['examId' => $examId, 'userId' => $param->userId]);
                if(!$endJoin){
                    return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交试卷失败');
                }
            }

            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交考题答案失败');
        }
    }

    /**
     * 获取参加考试信息
     */
    protected function getJoinexamInfo($data)
    {
        if(!$data['examId'] || !$data['userId']){
            return false;
        }
        $where = [
            'examId' => $data['examId'],
            'userId' => $data['userId']
        ];
        $info = Joinexam::where($where)->find();
        if (!$info) {
            return false;
        }

        return $info;
    }

    /**
     * 获取考题信息
     */
    protected function getQuestionInfo($data)
    {
        if(!$data['examId'] || !$data['number']){
            return false;
        }
        $where = [
            'examId' => $data['examId'],
            'number' => $data['number']
        ];
        $info = Examquestions::where($where)->find();
        if (!$info) {
            return false;
        }

        return $info;
    }

    /**
     * 参加考试 开始
     */
    protected function beginJoinexam($data)
    {
        if(!$data['examId'] || !$data['userId']){
            return false;
        }
        $where = [
            'examId' => $data['examId'],
            'userId' => $data['userId']
        ];
        $info = Joinexam::where($where)->find();
        if (!$info) {
            $userName = db('user')->where(['id' => $data['userId']])->value('nickName');
            $data = [
                'id' => BaseHelper::getUUID(),
                'createDate' => time(),
                'examId' => $data['examId'],
                'userId' => $data['userId'],
                'userName' => $userName
            ];
            $result = Joinexam::create($data);

            if($result !== false){
                return true;
            }else{
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 参加考试 结束
     */
    protected function endJoinexam($data)
    {
        if(!$data['examId'] || !$data['userId']){
            return false;
        }
        $where = [
            'examId' => $data['examId'],
            'userId' => $data['userId']
        ];
        $info = Joinexam::where($where)->find();
        if ($info) {
            // 获取考试分数
            /*
            $where = [
                'a.examId' => $data['examId'],
                'r.userId' => $data['userId'],
                'r.joinId' => $info->id,
                'r.isCorrect' => 1,
            ];
            $join = [
                ['__EXAMRESULTS__ r', 'a.id = r.questionId'],
            ];
            $score = Examquestions::alias('a')->where($where)->join($join)->sum('a.score');
            */
            $where = [
                'userId' => $data['userId'],
                'joinId' => $info->id,
            ];
            $score = db('examresults')->where($where)->sum('score');

            $data = [
                'updateDate' => time(),
                'examtime' => ceil((time() - strtotime($info->createDate)) % 86400 / 60),
                'score' => $score,
            ];

            $result = Joinexam::update($data, ['id' => $info->id]);

            if($result !== false){
                return true;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 考试结果
     */
    public function getExamresults()
    {
        $param = self::getHttpParam();

        if (empty($param->examId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'examId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $examId = $param->examId;
        $where = [
            'examId' => $examId,
            'userId' => $param->userId
        ];
        $fields = 'id, createDate, updateDate, score';
        $item = Joinexam::where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '出错了');
        }
        $item->examtime = strtotime($item->updateDate) - strtotime($item->createDate);
        // 考试信息
        $onlineexam = OnlineexamMdl::where(['id' => $examId])->field('name,passingGrade')->find();
        $item->examName = $onlineexam->name;
        $item->passingGrade = $onlineexam->passingGrade;
        // 考试结果
        $examresults = db('examresults')->alias('a')->where(['joinId' => $item->id])->join('__EXAMQUESTIONS__ q', 'a.questionId = q.id')->field('q.number, a.isCorrect')->order('q.number ASC')->select();
        $item->examresults = $examresults;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 查看答案
     */
    public function viewExamresults()
    {
        $param = self::getHttpParam();

        if (empty($param->examId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'examId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $examId = $param->examId;
        $joinexamInfo = $this->getJoinexamInfo(['examId' => $examId, 'userId' => $param->userId]);
        $where = [
            'a.joinId' => $joinexamInfo['id'],
            'a.userId' => $param->userId
        ];
        $join = [
            ['__EXAMQUESTIONS__ e', 'a.questionId = e.id'],
        ];
        $field = 'a.answer, e.number, e.subject, e.score, e.type, e.option, e.correctAnswer';
        $results = db('examresults')->alias('a')->where($where)->join($join)->field($field)->order('e.number ASC')->select();

        if(!$results){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '出错了');
        }

        foreach ($results as &$item) {
            $options = json_decode($item['option'], true);
            foreach($options as &$option){
                $option['isChecked'] = 0;
                $userAnswer = $item['answer'];
                if(in_array($option['option_NO'], explode(',', $userAnswer))){
                    $option['isChecked'] = 1;
                }
                // 去除正确答案
                unset($option['option_isCorrect']);
            }
            $item['option'] = $options;
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $results);
    }

}