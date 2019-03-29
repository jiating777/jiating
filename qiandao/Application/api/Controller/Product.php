<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Image;
use app\admin\model\Product as ProductMdl;
use app\admin\model\Producttype;
use app\admin\model\Villages;

/**
 * 村特产接口
 */
class Product extends BaseController
{

    /**
     * 镇农产品
     */
    public function getTownProducts()
    {
        $param = self::getHttpParam();

        /*if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }*/

        $where = [
            //'townId' => $param->townId
        ];

        // 获取镇下所有村
        $result = Villages::where($where)->field('id, name, imgUrl, address')->order('sorting ASC')->select();

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关村子');
        }

        $data = [];
        foreach ($result as $key => $item) {
            $field = 'id, title, imgUrl, price, unit, style';
            $products = ProductMdl::where(['villageId' => $item['id']])->field($field)->limit(0, 3)->order('createDate DESC')->select();
            foreach ($products as $k => $v) {
                $products[$k]['imgUrl'] = $v['imgUrl'].'?imageView2/1/w/220/h/220';
            }
            // 村农产品
            /*if(!$products){
               unset($result[$key]);
            }*/
            $item['products'] = $products;
            if($products){
                $data[] = $item;
            }
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 村农产品
     */
    public function getVillageProducts()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->villageId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $where = [
            'villageId' => $param->villageId,
            'style' => 1
        ];

        $result = ProductMdl::where($where)->field('id, title, imgUrl, price, unit, style')->limit($start, $length)->select();
        $total = ProductMdl::where($where)->count();

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关农产品');
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取产品列表
     *
     * @return \think\response\Json
     */
    public function getProductList()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (empty($param->townId)) {
            //return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }
        $where = [
            //'townId' => $param->townId,
            'preEndTime' => [['eq', 0], ['gt', time()], 'or']
        ];
        if (!empty($param->typeId)) {
            $where['typeId'] = $param->typeId;
        }
        if (!empty($param->varietieId)) {
            $where['varietieId'] = $param->varietieId;
        }
        if (!empty($param->cityId)) {
            $where['cityId'] = $param->cityId;
        }
        if (!empty($param->xianId)) {
            $where['xianId'] = $param->xianId;
        }
        if (!empty($param->townId)) {
            $where['townId'] = $param->townId;
        }
        if (!empty($param->villageId)) {
            $where['villageId'] = $param->villageId;
        }
        $model = db('product');
        $fields = 'id, title, imgUrl, price, unit, cityId, xianId, townId, villageId, preEndTime, style';
        // 默认 最新上架
        $order = 'createDate DESC';
        if (!empty($param->recommendId)) {
            $recommendId = $param->recommendId;
            if($recommendId == 1){
                // 价格最低
                $order = 'price ASC';
            }else if($recommendId == 2){
                // 销量最多
                $order = 'totalSales DESC';
            }
        }

        $result = $model->where($where)->field($fields)->limit($start, $length)->order($order)->select();
        $total = ProductMdl::where($where)->count();
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

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取产品详情
     *
     * @return \think\response\Json
     */
    public function getProductDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->productId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'productId 不能这空');
        }

        $productId = $param->productId;
        $where = [
            'id' => $productId
        ];
        $fields = 'title, imgUrl, price, unit, minBuyNum, description, phone, specJson, preStartTime, preEndTime, preDeliverDate, cityId, xianId, townId, villageId';

        $model = db('product');
        $item = $model->where($where)->order('createDate DESC')->field($fields)->find();
        // 图集
        $imagesList = Image::where(['relatedId' => $productId, 'tag' => 'imglist'])->field('imgUrl')->select();
        foreach ($imagesList as $k => $v) {
            $imagesList[$k]['imgUrl'] = $v['imgUrl'].'?imageView2/1/w/750/h/750';
        }
        $item['imagesList'] = $imagesList;
        $unitArr = get_product_unit();
        $item['unit'] = $unitArr[$item['unit']];
        $areaDB = db('area');
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
        // 产品规格
        $item['specJson'] = json_decode($item['specJson'], true);
        $item['imgUrl'] = $item['imgUrl'].'?imageView2/1/w/220/h/220';

        if (empty($item)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关产品');
        } else {
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
        }
    }

    /**
     * 获取预售产品列表
     */
    public function getPresaleProductList()
    {
        $param = self::getHttpParam();

        $start = 0;
        $length = 20;
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $model = db('product');
        $where['style'] = 2;
        $where['preEndTime'] = ['gt', time()];
        $field = 'id, title, imgUrl, price, unit, cityId, xianId, townId, villageId, preStartTime, preEndTime, preDeliverDate';
        $result = $model->where($where)->field($field)->limit($start, $length)->order('preEndTime ASC')->select();
        $total = ProductMdl::where($where)->count();
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

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取预售产品详情
     */
    public function getPresaleProductDetail()
    {
        return $this->getProductDetail();
    }

    /**
     * 获取产品分类
     */
    public function getProductType()
    {
        $param = self::getHttpParam();

        $where = [
            'parentId' => 0
        ];
        if (isset($param->parentId) && $param->parentId) {
            $where = [
                'parentId' => $param->parentId
            ];
        }
        if (isset($param->parentId) && $param->parentId) {
            $where = [
                'parentId' => $param->parentId
            ];
            $data = Producttype::where($where)->field('id, name, imgUrl')->select();
        } else {
            $data = Producttype::where($where)->field('id, name, imgUrl')->select();

            if($data){
               $child = Producttype::where(['parentId' => $data[0]['id']])->field('id, name, imgUrl')->select();
                $data[0]['child'] = $child;
            }
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 获取产品产地
     */
    public function getProductPlace()
    {
        $param = self::getHttpParam();

        $where = [
            'parentId' => 0
        ];
        $model = db('area');
        if (isset($param->parentId) && $param->parentId) {
            $where = [
                'parentId' => $param->parentId
            ];
            $data = $model->where($where)->field('id, name')->select();
        } else {
            $city = $model->where($where)->field('id, name')->select();
            $xian = $model->where(['parentId' => $city[0]['id']])->field('id, name')->find();
            $town = $model->where(['parentId' => $xian['id']])->field('id, name')->find();
            $village = Villages::where(['id' => $town['id']])->field('id, name')->find();
            $data = [
                'city' => $city,
                'xian' => $xian,
                'town' => $town,
                'village' => $village
            ];
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

    /**
     * 获取村
     */
    public function getVillages()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }

        $where = [
            'townId' => $param->townId
        ];

        $result = Villages::where($where)->field('id, name')->select();

        if (!$result) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '没有相关村');
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
    }

    /**
     * 获取产品推荐
     */
    public function getRecommend()
    {
        $data = [
            '0' => [
                'id' => 1,
                'name' => '价格最低'
            ],
            '1' => [
                'id' => 2,
                'name' => '销量最多'
            ],
            '2' => [
                'id' => 3,
                'name' => '最新上架'
            ]
        ];

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $data);
    }

}