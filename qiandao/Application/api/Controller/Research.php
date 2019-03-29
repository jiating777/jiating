<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Research as ResearchMdl;
use app\admin\model\Researchproject;
use app\admin\model\Joinresearch;

use think\Db;
use think\Exception;
use think\Request;

/**
 * 投票调研
 */
class Research extends BaseController
{

    /**
     * 获取调研列表
     *
     * @return \think\response\Json
     */
    public function getResearchList()
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

        $fields = 'id, name, endTime, imgUrl';
        $model = db('research');
        $result = $model->where($where)->limit($start, $length)->field($fields)->select();
        $total = $model->where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到调研');
        }

        foreach ($result as &$item) {
            // 参与人数
            $joinResearchs = Joinresearch::where(['researchId' => $item['id']])->count();
            $item['joinResearchs'] = $joinResearchs;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取调研详情
     *
     * @return \think\response\Json
     */
    public function getResearchDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }

        if (empty($param->researchId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'researchId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $researchId = $param->researchId;
        $userId = $param->userId;
        $where = [
            'townId' => $param->townId,
            'id' => $researchId
        ];
        if (isset($param->villageId) && $param->villageId) {
            $where = [
                'villageId' => $param->villageId
            ];
        }
        $fields = 'id, name, endTime, preface, epilogue, imgUrl';

        $model = db('research');
        $item = $model->where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关调研');
        }

        // 参与人数
        $joinResearchs = Joinresearch::where(['researchId' => $researchId])->count();
        $item['joinResearchs'] = $joinResearchs;
        // 调研项目
        $field = 'id, title, type, option';
        $researchproject = Researchproject::where(['researchId' => $researchId])->field($field)->order('sorting ASC')->select();
        if($researchproject){
            foreach ($researchproject as $project) {
                $options = json_decode($project->option, true);
                $project->option = $options;
            }
        }
        $item['researchproject'] = $researchproject;
        // 是否已参加
        $isJoin = db('joinresearch')->where(['researchId' => $researchId, 'userId' => $userId])->find();
        if($isJoin){
            $isJoin = 1;
        }else{
            $isJoin = 2;
        }
        $item['isJoin'] = $isJoin;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 提交调研
     */
    public function placeResearch()
    {
        $param = self::getHttpParam();

        if (empty($param->researchId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'researchId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->joinData)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'joinData 不能为空');
        }

        $researchId = $param->researchId;
        $userId = $param->userId;

        $joinresearchInfo = Joinresearch::where(['researchId' => $researchId, 'userId' => $userId])->find();
        if($joinresearchInfo){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '您已提交过，不能再提交');
        }

        $userName = db('user')->where(['id' => $userId])->value('nickName');
        $data = [
            'id' => BaseHelper::getUUID(),
            'createDate' => time(),
            'researchId' => $researchId,
            'userId' => $userId,
            'userName' => $userName
        ];
        $result = Joinresearch::create($data);
        if(!$result){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交失败');
        }

        /*
        @$joinData = json_decode($param->joinData, true);
        if(!$joinData || !is_array($joinData)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '数据格式错误，提交失败');
        }
        */
        $joinData = $param->joinData;

        try {
            Db::startTrans();
            $researchresults = db('researchresults');
            foreach($joinData as $item){
                $item = (Array)$item;
                $resData = [
                    'id' => BaseHelper::getUUID(),
                    'joinId' => $data['id'],
                    'projectId' => $item['projectId'],
                    'userId' => $userId,
                    'answer' => $item['answer']
                ];

                $res = $researchresults->insert($resData);
                if(!$res){
                    Db::rollback();
                    return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交失败，请稍后再试');
                }
            }
            Db::commit();
        } catch (Exception $e) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '提交失败，请稍后再试');
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, '提交成功');
    }

}