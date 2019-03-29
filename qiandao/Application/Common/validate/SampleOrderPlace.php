<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/10 0010
 * Time: 下午 4:43
 */
namespace app\common\validate;

class SampleOrderPlace extends BaseValidate{
    protected $rule = [
        'productId' => 'require|isNotEmpty',
        'count' => 'require|isNotEmpty',
        'userId' => 'require|isNotEmpty',
        'townId' => 'require|isNotEmpty',
        'deliverAddress' => 'require|isNotEmpty',
        'userName' => 'require|isNotEmpty',
        'userPhone' => 'require|isphone'
    ];

    protected $message = [
        'productId' => '商品Id必填',
        'count' => '购买数量必填',
        'userId' => '缺少用户id',
        'townId' => '缺少镇Id',
        'deliverAddress' => '收货地址错误',
        'userName' => '收货人姓名必填',
        'userPhone' => '收货人联系方式错误'

    ];
}