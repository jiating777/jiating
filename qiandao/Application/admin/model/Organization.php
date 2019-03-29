<?php

namespace app\admin\model;


class Organization extends Base
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'organization';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = true;

    public function getOrganizationCountAttr($value,$data) {  //获取某一组织下的职务数
        return 3;
        // return Organizationjob::where(['organizationId'=>$data['id'],'isDelete'=>2])->count();
    }
}