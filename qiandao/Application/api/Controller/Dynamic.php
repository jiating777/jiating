<?php

namespace app\api\controller;

use app\common\BaseHelper;
use app\lib\Qiniu;
use app\lib\traits\Image as imageTrait;

use app\admin\model\Townconfig;
use app\admin\model\Villages;
use app\admin\model\Member;
use app\admin\model\Image;
use app\admin\model\Communitydynamics;

use think\Db;

/**
 * 动态接口
 */
class Dynamic extends BaseController
{

    use imageTrait;

    /**
     * 首页数据
     */
    public function getDynamicList()
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
        /*if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 不能为空');
        }
        if (!in_array($param->type, [1, 2, 3])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 格式不正确');
        }*/
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $userId = $param->userId;
        $where = [
            'townId' => $param->townId,
        ];
        $townId = $param->townId;
        // 动态是否需要审核
        $config = $this->getTownConfig($townId);
        if(isset($config['dongtaicheckoff']) && $config['dongtaicheckoff'] == 1){
            $where['isPass'] = 1;
        }
        $order = 'createDate DESC';
        if(isset($param->villageId) && $param->villageId) {
            $where['villageId'] = $param->villageId;
            $model = db('communitydynamics');
            $fields = 'id, content, createUser as userId, createDate, countComment, countLike';
            $result = $model->where($where)->field($fields)->limit($start, $length)->order($order)->select();
            $total = $model->where($where)->count();
        } else {
            if ($param->type == 3) {
                // 我关注的用户
                $userattentionDB = db('userattention');
                $attentionUserIds = $userattentionDB->where(['userId' => $userId])->field('attentionUserId')->select();
                $attentionUserIds = array_column($attentionUserIds, 'attentionUserId');
                $where['createUser'] = ['in', $attentionUserIds];
                $model = db('communitydynamics');
                $fields = 'id, content, createUser as userId, createDate, countComment, countLike';
                $result = $model->where($where)->field($fields)->limit($start, $length)->order($order)->select();
                $total = $model->where($where)->count();

                /*$model = db('userattention');
                $join = [
                    ['__USER__ u', 'a.attentionUserId = u.id'],
                ];
                $fields = 'a.attentionUserId ,u.id as userId, u.nickName, u.avatarUrl';
                $result = $model->alias('a')->where($where)->join($join)->limit($start, $length)->field($fields)->select();
                $total = $model->alias('a')->where($where)->join($join)->count();*/
            } else {
                if($param->type == 2){
                    $order = 'countLike DESC';
                }

                $model = db('communitydynamics');
                $fields = 'id, content, createUser as userId, createDate, countComment, countLike';
                $result = $model->where($where)->field($fields)->limit($start, $length)->order($order)->select();
                $total = $model->where($where)->count();
            }
        }

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关动态');
        }

        $userDB = db('user');
        $dynamicslikeDB = db('articlelike');
        $userattentionDB = db('userattention');
        foreach ($result as &$item) {
            $user = $userDB->where(['id' => $item['userId']])->field('id,memberId,nickName,avatarUrl')->find();
            $item['userId'] = $user['id'];
            $item['userName'] = $user['nickName'];
            $item['userAvatar'] = $user['avatarUrl'];
            // 处理动态内容
            $item['content'] = urldecode($item['content']);
            // 动态图集
            $imagesList = Image::where(['relatedId' => $item['id'], 'tag' => 'imglist'])->field('imgUrl')->order('sorting ASC')->select();
            $item['imagesList'] = $imagesList;
            // 是否已点赞
            $isLike = $dynamicslikeDB->where(['userId' => $userId, 'articleId' => $item['id']])->find();
            if($isLike){
                $isLike = 1;
            }else{
                $isLike = 2;
            }
            $item['isLike'] = $isLike;
            if(isset($param->type) && $param->type == 3){
                $item['isAttention'] = 1;
            }else{
                // 是否已关注
                $isAttention = $userattentionDB->where(['userId' => $userId, 'attentionUserId' => $item['userId']])->find();
                if($isAttention){
                    $isAttention = 1;
                }else{
                    $isAttention = 2;
                }
                $item['isAttention'] = $isAttention;
            }
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

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 发布动态
     */
    public function postDynamic()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        $Townconfig = Townconfig::where('townId',$param->townId)->find();
        if(!$Townconfig || $Townconfig->dongtaioff == 0){
            $User = \app\admin\model\User::get($param->userId);
            if(!$User->memberId){
                return show(config('status.ERROR_STATUS'), "您不是村民，没有权限发动态", '您不是村民，没有权限发动态');
            }

        }
        if (empty($param->content) || !trim($param->content)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, '动态内容不能为空');
        }
        /*if (empty($param->imgIds) || !trim($param->imgIds)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'imgIds 不能为空');
        }*/

        $villageId = 0;
        if(isset($param->villageId) && $param->villageId){
            $villageId = $param->villageId;
        }

        Db::startTrans();
        $userId = $param->userId;
        $areaIds = $this->getParentIdsByTownId($param->townId);
        $model = db('communitydynamics');
        $data = [
            'id' => BaseHelper::getUUID(),
            'createDate' => time(),
            'cityId' => $areaIds['cityId'],
            'xianId' => $areaIds['xianId'],
            'townId' => $param->townId,
            'villageId' => $villageId,
            'createUser' => $userId,
            'content' => $param->content,
        ];

        $result = $model->insert($data);

        if($result !== false){
            $msg = '';
            // 图片
            //$res = $this->_upload_images($data['id'], $msg);
            $res = true;
            if(isset($param->imgIds) && $param->imgIds) {
                $imgIds = (array) $param->imgIds;
                //$res = Image::where(['id' => ['in', $imgIds]])->update(['relatedId' => $data['id'], 'controller' => 'Communitydynamics', 'relatedTable' => 'Communitydynamics', 'tag' => 'imglist']);
                foreach($imgIds as $key => $imgId){
                    $res = Image::where(['id' => $imgId])->update(['relatedId' => $data['id'], 'controller' => 'Communitydynamics', 'relatedTable' => 'Communitydynamics', 'tag' => 'imglist', 'sorting' => $key]);
                }
            }
            if(!$res){
                Db::rollback();
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, $msg);
            }
            Db::commit();
        }else{
            Db::rollback();
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '发布动态失败');
        }

        // 动态是否需要审核
        $isCheck = 0;
        $config = $this->getTownConfig($param->townId);
        if(isset($config['dongtaicheckoff']) && $config['dongtaicheckoff'] == 1){
            $isCheck = 1;
        }
        $data = [
            'result' => $result,
            'isCheck' => $isCheck
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 上传图片
     */
    private function _upload_images($id, &$msg) {
        // 如果没有图片直接返回
        if (!$_FILES['images']) {
            return true;
        }

        // 将 $_FILES 的多个文件类型格式化
        $files = $this->_format_files($_FILES['images']);
        // 检查图片是否合法
        if (!$this->_check_images($files, $msg)) {
            return false;
        }

        $Qiniu = new Qiniu();
        foreach ($files as $file) {
            // 上传图片
            $result = $Qiniu->postDoupload($file);
            if($result['status'] == 'fail') {
                $msg = $result['message'];
                return false;
            }
            $data = [
                'id' => BaseHelper::getUUID(),
                'imgUrl' => 'http://img.1miaodao.cn/' . $result['key'],
                'controller' => 'Communitydynamics',
                'created' => time(),
                'relatedId' => $id,
                'relatedTable' => 'Communitydynamics',
                'tag' => 'imglist'
            ];
            Image::insert($data);
        }

        return true;
    }

    /**
     * 动态详情
     */
    public function getDynamicDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->dynamicsId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'dynamicId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $userId = $param->userId;
        $where = [
            'id' => $param->dynamicsId
        ];
        $model = db('communitydynamics');
        $field = 'id, content, createUser as userId, createDate, countRead, countComment, countLike';
        $item = $model->where($where)->field($field)->find();
        if (!$item) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关动态');
        }

        $userDB = db('user');
        $dynamicslikeDB = db('articlelike');
        $userattentionDB = db('userattention');
        $user = $userDB->where(['id' => $item['userId']])->field('id,memberId,nickName,avatarUrl')->find();
        $item['userId'] = $user['id'];
        $item['userName'] = $user['nickName'];
        $item['userAvatar'] = $user['avatarUrl'];
        // 处理动态内容
        $item['content'] = urldecode($item['content']);
        // 动态图集
        $imagesList = Image::where(['relatedId' => $item['id'], 'tag' => 'imglist'])->field('imgUrl')->order('sorting ASC')->select();
        $item['imagesList'] = $imagesList;
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
        // 评论列表
        $item['commentList'] = action('api/common/commentList', ['isApi' => false]);
        $userJob = $village = '';
        if($user['memberId'] && $user['memberId'] !== 0){
            $memberInfo = Member::where(['id' => $user['memberId']])->field('villageId,job')->find();
            $userJob = $memberInfo['job'];
        }
        // 发布者职务
        $item['userJob'] = $userJob;
        // 发布者所属村子
        $village = Villages::where(['id' => $memberInfo['villageId']])->value('name');
        $item['village'] = $village;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 个人动态
     */
    public function getUserDynamics()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->postUserId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'postUserId 不能为空');
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

        $postUserId = $param->postUserId;
        $userId = $param->userId;
        $where = [
            'createUser' => $postUserId,
        ];
        $townId = $param->townId;
        // 动态是否需要审核
        $config = $this->getTownConfig($townId);
        if(isset($config['dongtaicheckoff']) && $config['dongtaicheckoff'] == 1){
            $where['isPass'] = 1;
        }

        $userDB = db('user');
        $userattentionDB = db('userattention');
        $userInfo =  $userDB->where(['id' => $postUserId])->field('id,memberId,nickName,avatarUrl')->find();
        // 关注人数
        $countAttention = $userattentionDB->where(['attentionUserId' => $postUserId])->count();
        $userInfo['countAttention'] = $countAttention;
        // 是否已关注
        $isAttention = $userattentionDB->where(['userId' => $userId, 'attentionUserId' => $postUserId])->find();
        if($isAttention){
            $isAttention = 1;
        }else{
            $isAttention = 2;
        }
        $userInfo['isAttention'] = $isAttention;

        $model = db('communitydynamics');
        $fields = 'id, content, createUser as userId, createDate, countComment, countLike';
        $result = $model->where($where)->field($fields)->limit($start, $length)->order('createDate DESC')->select();
        $total = $model->where($where)->count();

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关动态');
        }

        $dynamicslikeDB = db('articlelike');
        foreach ($result as &$item) {
            $item['userId'] = $userInfo['id'];
            $item['userName'] = $userInfo['nickName'];
            $item['userAvatar'] = $userInfo['avatarUrl'];
            // 处理动态内容
            $item['content'] = urldecode($item['content']);
            // 动态图集
            $imagesList = Image::where(['relatedId' => $item['id'], 'tag' => 'imglist'])->field('imgUrl')->order('sorting ASC')->select();
            $item['imagesList'] = $imagesList;
            // 是否已点赞
            $isLike = $dynamicslikeDB->where(['userId' => $userId, 'articleId' => $item['id']])->find();
            if($isLike){
                $isLike = 1;
            }else{
                $isLike = 2;
            }
            $item['isLike'] = $isLike;
            $userJob = $village = '';
            if($userInfo['memberId'] && $userInfo['memberId'] !== 0){
                $memberInfo = Member::where(['id' => $userInfo['memberId']])->field('villageId,job')->find();
                $userJob = $memberInfo['job'];
            }
            // 发布者职务
            $item['userJob'] = $userJob;
            // 发布者所属村子
            $village = Villages::where(['id' => $memberInfo['villageId']])->value('name');
            $item['village'] = $village;
        }

        $data = [
            'userInfo' => $userInfo,
            'dynamics' => $result,
            'total' => $total
        ];

        //return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

}