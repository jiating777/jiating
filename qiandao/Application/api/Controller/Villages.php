<?php

namespace app\api\controller;

use app\admin\model\Villages as VillagesMdl;
use app\admin\model\Article;
use app\admin\model\Operator;
use app\admin\model\Member;

use think\Request;

/**
 * 村子
 */
class Villages extends BaseController
{

    /**
     * 获取村子列表
     *
     * @return \think\response\Json
     */
    public function getVillagesList()
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

        $model = new VillagesMdl;
        $fields = 'id, name, imgUrl';
        $result = $model->where($where)->limit($start, $length)->field($fields)->select();
        $total = $model->where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到村子');
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取村子详情
     *
     * @return \think\response\Json
     */
    public function getVillagesDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $where = [
            'townId' => $param->townId,
            'villageId' => $param->villageId
        ];

        // 村子详情
        $villageInfo = VillagesMdl::where(['id' => $param->villageId])->field('id, imgUrl, address, addressGeo')->find();

        // 村里事
        $articleVillage = $this->getArticleVillage($where);

        // 本村出品--产品
        $products = action('api/HomeFrame/getProducts', ['where' => $where]);

        // 扶贫项目
        $povertyprojects = action('api/HomeFrame/getPovertyprojects', ['where' => $where]);

        // 最新动态
        $communitydynamics = action('api/HomeFrame/getCommunitydynamics', ['where' => $where, 'userId' => $param->userId]);

        $data = [
            'illageInfo' => $villageInfo,
            'articleVillage' => $articleVillage ? : '',
            'products' => $products ? : '',
            'povertyprojects' => $povertyprojects ? : '',
            'communitydynamics' => $communitydynamics ? : '',
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 获取村里事
     */
    protected function getArticleVillage($where)
    {
        //$model = new Article;
        $model = db('article');
        $field = 'id, iconUrl, typeId, title, createDate, createOper';
        $result = $model->where($where)->where(['type' => 'village'])->field($field)->limit(0, 5)->order('createDate DESC')->select();
        if (!$result) {
            return false;
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

        return $result;
    }
}