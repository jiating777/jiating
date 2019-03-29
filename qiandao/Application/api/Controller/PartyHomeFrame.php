<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Meeting;
use app\admin\model\Microclassroom;
use app\admin\model\Research;
use app\admin\model\Operator;
use app\admin\model\Member;

use think\Request;

/**
 * 智慧党建首页
 */
class PartyHomeFrame extends BaseController
{
    /**
     * 首页数据
     */
    public function getPartyHomeFrame()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }

        $where = [
            'townId' => $param->townId
        ];
        if (isset($param->villageId) && $param->villageId) {
            $where = [
                'villageId' => $param->villageId
            ];
        }

        // 最新通知
        $articleNotice = $this->getArticleNotice($where);

        // 最新要闻
        $articleParty = $this->getArticleParty($where);

        // 最新课程
        $classroom = $this->getClassroom($where);

        // 最新调研
        $research = $this->getResearch($where);

        $data = [
            'articleNotice' => $articleNotice ? : '',
            'articleParty' => $articleParty ? : '',
            'classroom' => $classroom ? : '',
            'research' => $research ? : '',
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 获取最新通知
     */
    protected function getArticleNotice($where)
    {
        $model = new Article;
        $field = 'id, title, createDate';
        $result = $model->where($where)->where(['type' => 'notice'])->field($field)->order('createDate DESC')->find();
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * 获取最新要闻
     */
    protected function getArticleParty($where)
    {
        //$model = new Article;
        $model = db('article');
        $field = 'id, iconUrl, title, createDate, createOper';
        $result = $model->where($where)->where(['type' => 'party'])->field($field)->limit(0, 5)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }
        foreach ($result as &$item) {
            $memberId = Operator::where(['id' => $item['createOper']])->value('memberId');
            if($memberId && $memberId != 0){
                // 组织
                $organization = Member::alias('a')->where(['a.id' => $memberId])->join('__ORGANIZATION__ o', 'a.organizationId = o.id')->value('o.name');
                $item['organization'] = $organization;
            }else{
                $item['organization'] = $this->defaultOrganization;
            }
            unset($item['createOper']);
        }

        return $result;
    }

    /**
     * 获取最新课程
     */
    protected function getClassroom($where)
    {
        $model = new Microclassroom();
        $field = 'id, title, imgUrl';
        $result = $model->where($where)->field($field)->limit(0, 5)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * 获取最新调研
     */
    protected function getResearch($where)
    {
        //$model = new Research();
        $model = db('research');
        $field = 'id, name, imgUrl, endTime';
        $result = $model->where($where)->field($field)->limit(0, 5)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }

        return $result;
    }

}