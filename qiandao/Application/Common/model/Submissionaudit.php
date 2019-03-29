<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14 0014
 * Time: 下午 7:03
 */
namespace app\common\model;


class Submissionaudit extends Otherdatabase
{
    // 当前模型名称 不带前缀
    protected $name = 'submissionaudit';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';
}