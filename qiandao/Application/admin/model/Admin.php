<?php

namespace app\admin\model;

class Admin extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'operator';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';


    /**
     * 成员属性
     * @return \think\model\relation\HasOne
     */
    public function member()
    {
        return $this->hasOne('Member', 'id', 'memberId');
    }

    /**
     * 职务属性
     * @return \think\model\relation\HasOne
     */
    /*public function organizationjob()
    {
        return $this->hasOne('Organizationjob', 'id', 'jobId')->field('name');
    }*/
    
    /**
     * 组织属性
     * @return \think\model\relation\HasOne
     */
    /*public function organization()
    {
        return $this->hasOne('Organization', 'id', 'organizationId')->field('name');
    }*/

}