<?php

namespace app\api\controller;

use app\admin\model\Townprogram;
use app\common\BaseHelper;

use app\admin\model\User as UserMdl;
use app\admin\model\Article;
use app\admin\model\Villages;
use app\admin\model\Image;
use app\admin\model\Member;
use app\admin\model\Operator;

use app\common\validate\UserSubmitExamine;
use app\lib\exception\ParameterException;

use think\Db;
use think\Exception;

/**
 * 个人中心
 */
class User extends BaseController
{

    /**
     * 扶贫
     *
     * @return \think\response\Json
     */
    public function myPoverty()
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
        $memberId = UserMdl::where(['id'=>$userId])->limit($start, $length)->value('memberId');
        if(empty($memberId) || !$memberId) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '您还未认证为村民');
        }
        $result = \app\admin\model\Povertyproject::where('aidingId',$memberId)->limit($start,$length)->select();
        $total = \app\admin\model\Povertyproject::where('aidingId',$memberId)->count();

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 动态
     *
     * @return \think\response\Json
     */
    public function myDynamics()
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

        $userId = $param->userId;
        $where = [
            'createUser' => $userId,
        ];
        $townId = $param->townId;
        // 动态是否需要审核
        /*$config = $this->getTownConfig($townId);
        if(isset($config['dongtaicheckoff']) && $config['dongtaicheckoff'] == 1){
            $where['isPass'] = 1;
        }*/

        $model = db('communitydynamics');
        $fields = 'id, content, createUser as userId, createDate, countComment, countLike';
        $result = $model->where($where)->field($fields)->limit($start, $length)->order('createDate DESC')->select();
        $total = $model->where($where)->count();

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关动态');
        }

        $userDB = db('user');
        $dynamicslikeDB = db('articlelike');
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
     * 删除动态
     */
    public function delDynamic()
    {
        $param = self::getHttpParam();

        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->dynamicsId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'dynamicId 不能为空');
        }

        $dynamicsId = $param->dynamicsId;

        Db::startTrans();
        $model = db('communitydynamics');
        $where = [
            'id' => $dynamicsId
        ];
        $result = $model->where($where)->delete();
        if($result !== false){
            try {
                $dynamicscommentDB = db('articlecomment');
                $dynamicslikeDB = db('articlelike');
                // 删除动态对应的评论
                $dynamicscommentDB->where(['articleId' => $dynamicsId])->delete();
                // 删除点赞
                $dynamicslikeDB->where(['articleId' => $dynamicsId])->delete();
            } catch (Exception $e) {
                Db::rollback();
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '删除失败，请稍后再试');
            }
            Db::commit();
        }else{
            Db::rollback();
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '删除失败，请稍后再试');
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, '删除成功');
    }

    /**
     * 关注
     *
     * @return \think\response\Json
     */
    public function myAttention()
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

        $model = db('userattention');
        $join = [
            ['__USER__ u', 'a.attentionUserId = u.id'],
        ];
        $fields = 'a.attentionUserId ,u.id as userId, u.nickName, u.avatarUrl';
        $result = $model->alias('a')->where($where)->join($join)->limit($start, $length)->field($fields)->select();
        $total = $model->alias('a')->where($where)->join($join)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到关注');
        }

        foreach ($result as &$item) {
            $countAttentions = $model->where(['attentionUserId' => $item['attentionUserId']])->count();
            // 关注人数
            $item['countAttentions'] = $countAttentions;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 收藏
     *
     * @return \think\response\Json
     */
    public function myCollect()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 不能为空');
        }
        if (!in_array($param->type, [1, 2])) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type 格式不正确');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $userId = $param->userId;
        $type = $param->type;
        if ($type == 1) {
            $where = [
                'a.userId' => $userId,
            ];

            $model = db('usercollect');
            $fields = 'a.id as collectId, t.id as articleId, t.title, t.iconUrl, t.typeId, t.createDate, t.createOper';
            $result = $model->alias('a')->where($where)->join('__ARTICLE__ t', 'a.collectId = t.id')->limit($start, $length)->field($fields)->select();
            $total = $model->alias('a')->where($where)->join('__ARTICLE__ t', 'a.collectId = t.id')->count();

            if (empty($result)) {
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到收藏');
            }

            $articletypeDB = db('articletype');
            foreach ($result as &$item) {
                $articleType = $articletypeDB->where(['id' => $item['typeId']])->value('name');
                // 文章类型
                $item['articleType'] = $articleType;
                $memberId = Operator::where(['id' => $item['createOper']])->value('memberId');
                if($memberId && $memberId != 0){
                    // 组织
                    $organization = Member::alias('a')->where(['a.id' => $memberId])->join('__ORGANIZATION__ o', 'a.organizationId = o.id')->value('o.name');
                    $item['organization'] = $organization;
                }else{
                    $item['organization'] = $this->defaultOrganization;
                }
                unset($item['typeId']);
                unset($item['createOper']);
            }
        } else {
            $where = [
                'a.userId' => $userId,
            ];

            $model = db('usercollect');
            $fields = 'a.id as collectId, p.id as productId, p.title, p.imgUrl, p.createDate, p.price, p.unit, p.cityId, p.xianId, p.townId, p.villageId';
            $result = $model->alias('a')->where($where)->join('__PRODUCT__ p', 'a.collectId = p.id')->limit($start, $length)->field($fields)->select();
            $total = $model->alias('a')->where($where)->join('__PRODUCT__ p', 'a.collectId = p.id')->count();

            if (empty($result)) {
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到收藏');
            }

            $areaDB = db('area');
            foreach ($result as &$item) {
                $city = $areaDB->where(['id' => $item['cityId']])->value('name');
                $xian = $areaDB->where(['id' => $item['xianId']])->value('name');
                $town = $areaDB->where(['id' => $item['townId']])->value('name');
                $village = Villages::where(['id' => $item['villageId']])->value('name');
                $address = $city . $xian . $town . $village;
                // 产地
                $item['address'] = $address;
                unset($item['cityId']);
                unset($item['xianId']);
                unset($item['townId']);
                unset($item['villageId']);
            }
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 发布留言
     */
    public function postMessage()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->content) || !trim($param->content)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, '留言内容不能为空');
        }

        $userId = $param->userId;
        $areaIds = $this->getParentIdsByTownId($param->townId);
        $model = db('messages');
        $data = [
            'id' => BaseHelper::getUUID(),
            'createDate' => time(),
            'cityId' => $areaIds['cityId'],
            'xianId' => $areaIds['xianId'],
            'townId' => $param->townId,
            'userId' => $userId,
            'content' => $param->content,
        ];
        $result = $model->insert($data);

        if($result !== false){
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '发布留言失败');
        }
    }

    /**
     * 提交村民审核
     * @return \think\response\Json
     */
    public function UserSubmitExamine(){
        //参数 : 用户id 镇id 姓名 照片 省份证 民族 文化程度 家庭环境照片 是否是党员  组织  职务 入党时间
        (new UserSubmitExamine())->goCheck();
        $param = self::getHttpParam();
        $userId = $param->userId;
        $townId = $param->townId;
        //获取市id和区id
        try{
            $Townprogram = Townprogram::where('townId',$townId)->find();
            if(!$Townprogram){
                throw new ParameterException([
                    'msg' => '镇不存在'
                ]);
            }
            // $checkIdcard = Member::where(['identityNumber'=>$param->cardNum,'isDelete'=>2])->where('userId','neq',$param->userId)->whereOr('userId',null)->find();
            // if($checkIdcard) {
            //     return show('status.ERROR_STATUS','该身份证号已存在','该身份证号已存在');
            // }
            $Member = Member::where(['userId'=>$param->userId,'isDelete'=>2])->find();
            if($Member){
                if($Member->townId != $townId || $Member->cityId != $Townprogram->cityId || $Member->xianId != $Townprogram->xianId){
                    throw new ParameterException([
                        'msg' => '您已经是其它镇/村的人了！'
                    ]);
                }
                $Member->name = $param->name;
                $Member->userId = $userId;
                $Member->villageId = !empty($param->villageId) ? $param->villageId:0;
                $Member->identityNumber = $param->cardNum;
                $genderNum = substr($Member->identityNumber,-2,1);
                $sBirthday = substr($Member->identityNumber,6,4).'-'.substr($Member->identityNumber,10,2).'-'.substr($Member->identityNumber,12,2);
                $Member->birthday = $sBirthday;
                $Member->gender = ((int)$genderNum)%2 == 0 ? 2 : 1;
                $Member->cardImg = $param->cardImg;
                $Member->avatar = isset($param->userImg)?$param->userImg:null;
                $Member->homeImg = isset($param->homeImg)?$param->homeImg:null;
                if(!$Member->mobile && isset($param->phoneNum) && !empty($param->phoneNum)){
                    $Member->mobile = $param->phoneNum;
                }
                $Member->isParty = $param->isPartymember;
                $Member->organizationId = $param->organization;
                $Member->partyTime = isset($param->partymemberTime)?strtotime($param->partymemberTime) :null;
                $Member->job = isset($param->job)?$param->job:null;
                $Member->shenheStatus = 2;
                try {
                    $Member->save();
                    return show(config('status.SUCCESS_STATUS'),'提交成功','提交成功');
                } catch (Exception $e) {
                    throw new ParameterException([
                        'msg' => '提交失败1'
                    ]);
                }

            }else{
                $Member = new Member();
                $Member->id = BaseHelper::getUUID();
                $Member->name = $param->name;
                $Member->userId = $userId;
                $Member->townId = $townId;
                $Member->cityId = $Townprogram->cityId;
                $Member->xianId = $Townprogram->xianId;
                $Member->villageId = !empty($param->villageId) ? $param->villageId:0;
                $Member->identityNumber = $param->cardNum;
                $genderNum = substr($Member->identityNumber,-2,1);
                $Member->gender = ((int)$genderNum)%2 == 0 ? 2 : 1;
                $sBirthday = substr($Member->identityNumber,6,4).'-'.substr($Member->identityNumber,10,2).'-'.substr($Member->identityNumber,12,2);
                $Member->birthday = $sBirthday;
                $Member->cardImg = $param->cardImg;
                $Member->avatar = isset($param->userImg)?$param->userImg:null;
                $Member->homeImg = isset($param->homeImg)?$param->homeImg:null;
                $Member->mobile = isset($param->phoneNum)?$param->phoneNum:null;
                $Member->isParty = $param->isPartymember;
                $Member->organizationId = isset($param->organization)?$param->organization:null;
                $Member->partyTime = isset($param->partymemberTime)?strtotime($param->partymemberTime):null;
                $Member->job = isset($param->job)?$param->job:null;
                $Member->shenheStatus = 2;
                if($Member->save()){
                    return show(config('status.SUCCESS_STATUS'),'提交成功','提交成功');
                }else{
                    throw new ParameterException([
                        'msg' => '提交失败2'
                    ]);
                }
            }

        }catch (Exception $ex){
            return show('status.ERROR_STATUS','失败','失败');
        }
    }
}