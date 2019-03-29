<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Villages;
use app\admin\model\Member;
use app\admin\model\Image;
use app\admin\model\Product;
use app\admin\model\Povertyproject;
use app\admin\model\Communitydynamics;

use think\Request;

/**
 * 首页
 */
class HomeFrame extends BaseController
{
    /**
     * 首页数据
     */
    public function getHomeFrame()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $where = [
            'townId' => $param->townId
        ];

        // 最新头条
        $articleToutiao = $this->getArticleToutiao();

        // 美丽普济--村子
        $villages = $this->getVillages($where);

        // 农事知识
        $farmings = $this->getFarmings();

        // 本地出品--产品
        $products = $this->getProducts($where);

        // 扶贫项目
        $povertyprojects = $this->getPovertyprojects($where);

        // 最新动态
        $communitydynamics = $this->getCommunitydynamics($where, $param->userId);

        $data = [
            'articleToutiao' => $articleToutiao ? : '',
            'villages' => $villages ? : '',
            'farmings' => $farmings ? : '',
            'products' => $products ? : '',
            'povertyprojects' => $povertyprojects ? : '',
            'communitydynamics' => $communitydynamics ? : '',
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 获取最新头条
     */
    protected function getArticleToutiao($where = [])
    {
        //$model = new Article;
        $model = db('article');
        $field = 'id, title, createDate';
        $result = $model->where($where)->where(['type' => 'toutiao'])->field($field)->order('createDate DESC')->find();
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * 获取村子
     */
    protected function getVillages($where)
    {
        $model = new Villages();
        $field = 'id, name, imgUrl';
        $result = $model->where($where)->field($field)->limit(0, 6)->order('sorting ASC')->select();
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * 获取农事知识
     */
    protected function getFarmings()
    {
        $list = \app\admin\model\Knowledge::order('createDate DESC')->limit(0, 6)->select();
        return $list;
    }

    /**
     * 获取产品
     */
    public function getProducts($where)
    {
        $model = new Product();
        $field = 'id, title, imgUrl, price, unit';
        $result = $model->where($where)->field($field)->limit(0, 3)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * 获取扶贫项目
     */
    public function getPovertyprojects($where)
    {
        $model = new Povertyproject();
        $field = 'id, title, imgUrl';
        $result = $model->where($where)->field($field)->limit(0, 6)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * 获取最新动态
     */
    public function getCommunitydynamics($where, $userId)
    {
        $townId = $where['townId'];
        // 动态是否需要审核
        $config = $this->getTownConfig($townId);
        if(isset($config['dongtaicheckoff']) && $config['dongtaicheckoff'] == 1){
            $where['isPass'] = 1;
        }

        //$model = new Communitydynamics();
        $model = db('communitydynamics');
        $field = 'id, content, createUser as userId, createDate, countComment, countLike';
        $result = $model->where($where)->field($field)->limit(0, 10)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }
        $userDB = db('user');
        //$dynamicscommentDB = db('dynamicscomment');
        $dynamicslikeDB = db('articlelike');
        $userattentionDB = db('userattention');
        foreach ($result as &$item) {
            $user = $userDB->where(['id' => $item['userId']])->field('id,memberId,nickName,avatarUrl')->find();
            $item['userId'] = $user['id'];
            $item['userName'] = $user['nickName'];
            $item['userAvatar'] = $user['avatarUrl'];
            // 处理动态内容
            $item['content'] = urldecode($item['content']);
            // 处理动态内容
            $item['content'] = urldecode($item['content']);
            // 动态图集
            $imagesList = Image::where(['relatedId' => $item['id'], 'tag' => 'imglist'])->field('imgUrl')->order('sorting ASC')->select();
            $item['imagesList'] = $imagesList;
            /*
            // 评论数
            $countComment = $dynamicscommentDB->where(['dynamicsId' => $item['id']])->count();
            $item['countComment'] = $countComment;
            // 点赞数
            $countLike = $dynamicslikeDB->where(['dynamicsId' => $item['id']])->count();
            $item['countLike'] = $countLike;
            */
            // 是否已点赞
            $isLike = $dynamicslikeDB->where(['userId' => $userId, 'articleId' => $item['id']])->find();
            if($isLike){
                $isLike = 1;
            }else{
                $isLike = 2;
            }
            $item['isLike'] = $isLike;
            // 是否已关注
            $isAttention = $userattentionDB->where(['userId' => $userId, 'attentionUserId' => $item['userId']])->find();
            if($isAttention){
                $isAttention = 1;
            }else{
                $isAttention = 2;
            }
            $item['isAttention'] = $isAttention;
            $userJob = $village = '';
            if($user['memberId'] && $user['memberId'] !== 0){
                $memberInfo = Member::where(['id' => $user['memberId']])->field('villageId,job')->find();
                $userJob = $memberInfo['job'];
                // 发布者职务
                $item['userJob'] = $userJob;
                // 发布者所属村子
                $village = Villages::where(['id' => $memberInfo['villageId']])->value('name');
                $item['village'] = $village;
            }
            
        }

        return $result;
    }

}