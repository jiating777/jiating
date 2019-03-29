<?php

namespace app\api\controller;

use app\admin\model\Product;
use app\admin\model\Villages;

use think\Request;

/**
 * 一村一品首页
 */
class ProductHomeFrame extends BaseController
{
    /**
     * 首页数据
     */
    public function getProductHomeFrame()
    {
        $param = self::getHttpParam();

        $where = [
            //'townId' => $param->townId
        ];

        // 抢先预订
        $presaleProduct = $this->getPresaleProduct($where);

        // 推荐
        $recommendProduct = $this->getRecommendProduct($where);

        $data = [
            'presaleProduct' => $presaleProduct ? : '',
            'recommendProduct' => $recommendProduct ? : '',
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 搜索
     */
    public function searchProduct()
    {
        $param = self::getHttpParam();

        /*if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }*/
        if (empty($param->keyword) || !trim($param->keyword)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'keyword 不能为空');
        }

        $where = [
            //'townId' => $param->townId,
            'title' => ['like', '%' . $param->keyword . '%'],
            'preEndTime' => [['eq', 0], ['gt', time()], 'or']
        ];

        $model = db('product');
        $field = 'id, title, imgUrl, price, unit, cityId, xianId, townId, villageId, preEndTime, style';
        $result = $model->where($where)->field($field)->limit(0, 100)->order('createDate DESC')->select();
        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关产品');
        }

        $areaDB = db('area');
        foreach ($result as &$item) {
            $unitArr = get_product_unit();
            $item['unit'] = $unitArr[$item['unit']];
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

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
    }

    /**
     * 获取预售产品
     */
    protected function getPresaleProduct($where)
    {
        //$model = new Product();
        $model = db('product');
        $where['style'] = 2;
        $where['preEndTime'] = ['gt', time()];
        $field = 'id, title, imgUrl, price, unit, cityId, xianId, townId, villageId, preEndTime';
        $result = $model->where($where)->field($field)->limit(0, 4)->order('preEndTime ASC')->select();
        if (!$result) {
            return false;
        }

        $areaDB = db('area');
        foreach ($result as &$item) {
            $unitArr = get_product_unit();
            $item['unit'] = $unitArr[$item['unit']];
            $city = $areaDB->where(['id' => $item['cityId']])->value('name');
            $xian = $areaDB->where(['id' => $item['xianId']])->value('name');
            $town = $areaDB->where(['id' => $item['townId']])->value('name');
            $village = Villages::where(['id' => $item['villageId']])->value('name');
            $address = $city . $xian . $town . $village;
            // 产地
            $item['address'] = $address;
            $item['imgUrl'] = $item['imgUrl'].'?imageView2/1/w/220/h/220';
            unset($item['cityId']);
            unset($item['xianId']);
            unset($item['townId']);
            unset($item['villageId']);
        }

        return $result;
    }

    /**
     * 获取推荐产品--按最新添加排序
     */
    protected function getRecommendProduct($where)
    {
        $model = new Product;
        $where['style'] = 1;
        $field = 'id, title, imgUrl, price, unit';
        $result = $model->where($where)->field($field)->limit(0, 4)->order('createDate DESC')->select();
        if (!$result) {
            return false;
        }

        return $result;
    }

}