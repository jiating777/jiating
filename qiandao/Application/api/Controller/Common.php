<?php

namespace app\api\controller;

use app\admin\model\Townconfig;
use app\common\BaseHelper;
use app\admin\model\Articlecomment;
use app\admin\model\Communitydynamics;
use app\admin\model\Article;
use app\admin\model\Articlelike;
use app\admin\model\Image;

use app\lib\exception\ParameterException;
use app\lib\Qiniu;

use think\Db;
use think\Exception;
use think\Request;

/**
 * 通用
 */
class Common extends BaseController
{

    /**
     * 轮播图
     */
    public function adbanner()
    {
        $param = self::getHttpParam();

        if (empty($param->position)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'position 不能为空');
        }
        if (!in_array($param->position, [1,2,3,4,5,6,7,8])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'position 类型不合法');
        }

        $where = [
            'position' => $param->position,
        ];
        $model = db('adbanner');
        $fields = 'id, imgUrl, type, linkType, content';
        $result = $model->where($where)->field($fields)->limit(0, 10)->order('sorting ASC')->select();

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未找到对应轮播图');
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
    }

    /**
     * 关注
     */
    public function attention()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->attentionUserId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'attentionUserId 不能为空');
        }
        if ($param->userId == $param->attentionUserId) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, '你不能关注你自己');
        }

        $where = [
            'userId' => $param->userId,
            'attentionUserId' => $param->attentionUserId
        ];
        $info = $this->getAttentionInfo($where);
        if($info){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你已经关注了');
        }
        $userId = $param->userId;
        $model = db('userattention');
        $data = [
            'id' => BaseHelper::getUUID(),
            'createDate' => time(),
            'userId' => $userId,
            'attentionUserId' => $param->attentionUserId
        ];
        $result = $model->insert($data);

        if($result !== false){
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '关注失败');
        }
    }

    /**
     * 取消关注
     */
    public function cancelAttention()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->attentionUserId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'attentionUserId 不能为空');
        }
        if ($param->userId == $param->attentionUserId) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, '你不能对你自己取消关注');
        }

        $where = [
            'userId' => $param->userId,
            'attentionUserId' => $param->attentionUserId
        ];
        $model = db('userattention');
        $result = $model->where($where)->delete();

        if($result !== false){
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '取消关注失败');
        }
    }

    /**
     * 获取关注信息
     * 可判断是否已关注
     */
    public function getAttentionInfo($where)
    {
        if(!$where['userId'] || !$where['attentionUserId']){
            return false;
        }

        $model = db('userattention');
        $info = $model->where($where)->find();
        if (!$info) {
            return false;
        }

        return $info;
    }

    /**
     * 发布评论
     */
    public function postComment()
    {
        $param = self::getHttpParam();
        Db::startTrans();
        try{
            if (empty($param->userId)) {
                throw new ParameterException([
                    'msg' => 'userId 不能为空'
                ]);
            }
            $User = \app\admin\model\User::get($param->userId);
            $Townconfig = Townconfig::where('townId',$param->townId)->find();
            if(!$Townconfig || $Townconfig->pinglunoff == 0){
                if(!$User->memberId){
                    throw new ParameterException([
                        'msg' => '不是村民，没有评论权限'
                    ]);
                }
            }

            if (empty($param->articleId)) {
                throw new ParameterException([
                    'msg' => 'articleId 不能为空'
                ]);
            }
            if (empty($param->content) || !trim($param->content)) {
                throw new ParameterException([
                    'msg' => '评论内容不能为空'
                ]);
            }
            if(!$param->commenttype){
                throw new ParameterException([
                    'msg' => '评论类型不能为空'
                ]);
            }
            if(!empty($param->responderId)){
                $check = Articlecomment::get($param->responderId);
                if(!$check){
                    throw new ParameterException([
                        'msg' => '被回复的评论不存在'
                    ]);
                }
            }
            $userId = $param->userId;
            $model = db('articlecomment');
            $userName = db('user')->where(['id' => $userId])->value('nickName');
            $data = [
                'id' => BaseHelper::getUUID(),
                'createDate' => time(),
                'articleId' => $param->articleId,
                'userId' => $userId,
                'responderId' => isset($param->responderId)?$param->responderId:null,
                'content' => $param->content,
                'userName' => $userName,
                'commenttype' => $param->commenttype
            ];
            $result = $model->insert($data);
            if($param->commenttype == 1){
                \app\admin\model\Article::where('id',$param->articleId)->setInc('commentCount',1);
            }else{
                Communitydynamics::where('id',$param->articleId)->setInc('countComment',1);
            }
            Db::commit();
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }catch (Exception $ex){
            Db::rollback();
            return show(config('status.ERROR_STATUS'), $ex->msg, '发布评论失败');
        }
    }

    /**
     * 收藏
     */
    public function collect()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->collectId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'collectId 不能为空');
        }
        if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 不能为空');
        }
        if (!in_array($param->type, [1, 2])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 格式不正确');
        }

        $where = [
            'userId' => $param->userId,
            'collectId' => $param->collectId,
            'type' => $param->type
        ];
        $info = $this->getCollectInfo($where);
        if($info){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '你已经收藏了');
        }
        $userId = $param->userId;
        $model = db('usercollect');
        $data = [
            'id' => BaseHelper::getUUID(),
            'createDate' => time(),
            'userId' => $userId,
            'collectId' => $param->collectId,
            'type' => $param->type
        ];
        $result = $model->insert($data);

        if($result !== false){
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '收藏失败');
        }
    }

    /**
     * 取消收藏
     */
    public function cancelCollect()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->collectId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'collectId 不能为空');
        }
        if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 不能为空');
        }
        if (!in_array($param->type, [1, 2])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 格式不正确');
        }

        $where = [
            'userId' => $param->userId,
            'collectId' => $param->collectId,
            'type' => $param->type
        ];
        $model = db('usercollect');
        $result = $model->where($where)->delete();

        if($result !== false){
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '取消收藏失败');
        }
    }

    /**
     * 获取收藏信息
     * 可判断是否已收藏
     */
    public function getCollectInfo($where)
    {
        if(!$where['userId'] || !$where['collectId'] || !$where['type']){
            return false;
        }

        $model = db('usercollect');
        $info = $model->where($where)->find();
        if (!$info) {
            return false;
        }

        return $info;
    }

    /**
     * 点赞
     */
    public function like()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->articleId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'articleId 不能为空');
        }
        if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 不能为空');
        }
        if (!in_array($param->type, [1, 2])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 格式不正确');
        }

        $msg = false;
        $articleId = $param->articleId;

        $praise_mdl = new Articlelike;

        $filter = array(
            'articleId' => $articleId,
            'userId' => $param->userId
        );

        // 是否已经点赞
        $is_praise = (boolean) $praise_mdl->where($filter)->count();

        // 返回信息
        $return = ['is_praise' => 'true','praises_count' => 0];

        // 是否操作成功
        $result = false;
        if ($is_praise) {  // 已赞的做删除赞动作
            $result = $this->_un_praise($filter, $param->type, $msg);
            if ($result) {
                $return['is_praise'] = 'false';
            }
        } else {    // 没有赞的做点赞动作
            $result = $this->_do_praise($filter, $param->type, $msg);
            if ($result) {
                $return['is_praise'] = 'true';
            }
        }

        // 如果处理成功，返回成功信息
        if ($result) {// 查询最新文章点赞数量
            $praises_count = (int) $praise_mdl->where(['articleId' => $articleId])->count();
            $return['praises_count'] = $praises_count;

            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $return);
        }
        return show(config('status.ERROR_STATUS'), self::NOT_DATA, '点赞失败');
    }

    /**
     * 评论列表
     */
    public function commentList($isApi = true) {
        $param = self::getHttpParam();

        if (empty($param->articleId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'articleId 不能为空');
        }
        $start = 0;
        $length = 20;
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }
        $model = db('articlecomment');
        $list = $model->where('articleId', $param->articleId)->field('id,articleId,responderId,createDate,userId,userName,content')->limit($start,$length)->order('createDate DESC')->select();
        $total = $model->where('articleId', $param->articleId)->count();
        $listMap = [];
        foreach ($list as $l) {
            $listMap[$l['id']] = $l;
        }

        foreach ($list as $k => $v) {
            // 处理评论内容
            $list[$k]['content'] = urldecode($v['content']);

            $user = \app\admin\model\User::get($v['userId']);
            if($user){
                $member = \app\admin\model\Member::get($user->memberId);
                if($member){
                    $list[$k]['avatarimg'] = $member->avatar;
                }else {
                    $list[$k]['avatarimg'] = $user['avatarUrl'];
                }

            }
            if($v['responderId'] != null || !empty($v['responderId'])) {
                $list[$k]['replayName'] = $listMap[$v['responderId']]['userName'];
                $list[$k]['replayComment'] =  urldecode($listMap[$v['responderId']]['content']);
            } else {
                $list[$k]['replayName'] = '';
            }
        }

        if(!$isApi){
            return $list;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);
    }

    /**
     * 点赞
     * 
     * @param array $filter 过滤条件
     * @param string $msg 错误提示语
     * @return boolean 是否操作成功
     */
    private function _do_praise($filter, $type, &$msg) {
        // 当前登录用户
        $userName = db('user')->where(['id' => $filter['userId']])->value('nickName');
        // 点赞
        $praise = array(
            'id' => BaseHelper::getUUID(),
            'createDate' => time(),
            'userId' => $filter['userId'],
            'articleId' => $filter['articleId'],
            'liketype' => $type,
            'userName' => $userName
        );
        Db::startTrans();
        try {
            $reslut = Articlelike::insert($praise);
            if($type == 1) {
                $updateCount = Article::where('id',$filter['articleId'])->setInc('likeCount');
            } else if($type == 2) {
                $updateCount = Communitydynamics::where('id',$filter['articleId'])->setInc('countLike',1);
            }
            $msg = '点赞成功';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $result = false;
        }

        return $reslut;
    }

    /**
     * 取消点赞
     * 
     * @param array $filter 过滤条件
     * @param string $msg 错误提示语
     * @return boolean 是否操作成功
     */
    private function _un_praise($filter, $type, &$msg) {
        Db::startTrans();
        try {
            $reslut = db('articlelike')->where($filter)->delete();
            if($type == 1) {
                $updateCount = Article::where('id',$filter['articleId'])->setDec('likeCount');
            } else if($type == 2) {
                $updateCount = Communitydynamics::where('id',$filter['articleId'])->setDec('countLike',1);
            }            
            $msg = '已取消赞';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $result = false;
        }        
        
        return $reslut;
    }

    /**
     * 上传图片
     */
    public function uploadOneImg() {
        $param = self::getHttpParam();

        /*if (empty($param->imgUrl)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'imgUrl 不能为空');
        }*/
        if (empty($_FILES['images'])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'images 不能为空');
        }

        $Qiniu = new Qiniu();
        $upload = $Qiniu->postDoupload($_FILES['images']);
        if($upload['status'] == 'fail') {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, $upload['message']);
        }
        $data = [
            'id' => BaseHelper::getUUID(),
            'imgUrl' => 'http://img.1miaodao.cn/' . $upload['key'],
            'created' => time(),
            //'relatedTable' => ''
        ];
        $result = Image::insert($data);
        if($result !== false) {
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
        } else {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '上传失败！');
        }
    }

    /**
     * 删除单张图片
     */
    public function delOneImg() {
        $param = self::getHttpParam();

        /*if (empty($param->imgId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'imgId 不能为空');
        }*/
        if (empty($param->imgUrl)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'imgUrl 不能为空');
        }

        //$id = $param->imgId;
        $url = $param->imgUrl;
        $key = substr(strrchr($url, '/'), 1);
        $Qiniu = new Qiniu();
        // 删除七牛图片
        $result = $Qiniu->delImg($key);
        if($result == NULL){
            db('image')->where('imgUrl', $url)->delete();

            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, '删除成功！');
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '删除失败！');
        }
    }

}