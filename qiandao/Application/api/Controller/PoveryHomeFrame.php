<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Operator;
use app\admin\model\Member;

use think\Request;

/**
 * 精准扶贫首页
 */
class PoveryHomeFrame extends BaseController
{
    /**
     * 首页数据
     */
    public function getPoveryHomeFrame()
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

        // 扶贫政策
        $articlePolicy = $this->getArticlePolicy($where);

        // 扶贫项目
        $povertyprojects = action('api/HomeFrame/getPovertyprojects', ['where' => $where]);

        // 扶贫工作
        $povertyWork = $this->getArticleWork($where);

        $data = [
            'articlePolicy' => $articlePolicy ? : '',
            'povertyprojects' => $povertyprojects ? : '',
            'povertyWork' => $povertyWork ? : '',
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 获取扶贫政策
     */
    protected function getArticlePolicy($where)
    {
        //$model = new Article;
        $model = db('article');
        $field = 'id, iconUrl, title, createDate, createOper';
        $result = $model->where($where)->where(['type' => 'policy'])->field($field)->limit(0, 5)->order('createDate DESC')->select();
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
     * 获取扶贫工作
     */
    protected function getArticleWork($where)
    {
        $list =  \app\admin\model\Povertywork::where($where)->where(['isDelete'=>2])->limit(0, 10)->order('createDate DESC')->select();
        foreach ($list as $k => $v) {
            $list[$k]['member'] = $v->member1;   //贫困户基本信息
            $list[$k]['memberaid'] = $v->member2;  //帮扶人信息
            $list[$k]['image'] = $v->image;
            unset($list[$k]['member1']);
            unset($list[$k]['member2']);
        }

        return $list;
    }

}