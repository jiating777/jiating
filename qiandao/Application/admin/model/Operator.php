<?php

namespace app\admin\model;

class Operator extends Base
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

    public static function getAllOrganization($townId)
    {
        $operator = Operator::alias('a')->join('member m','a.memberId=m.id')->join('organization o','m.organizationId=o.id')->field('a.*,m.job,o.name')->where(['a.townId' => $townId])->order('o.sorting')->select();
        $operatorMap = [];
        foreach ($operator as $o) {
            $operatorMap[$o['id']][] = $o['name'];  //以operator表id为键值,组织名称为值
        }
        return $operatorMap;
    }


}