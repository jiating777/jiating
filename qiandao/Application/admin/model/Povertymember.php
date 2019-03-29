<?php

/**
 * 贫困户
 */
namespace app\admin\model;

use think\Model;

class Povertymember extends Base {

    public function member() {  //关联村民表
        return $this->belongsTo('Member','memberId');
    }

    public function memberaid() {  //关联村民表
        return $this->belongsTo('Member','aidingId');
    }

    public function getPovertydegreeAttr($data){
        return viewPovertydegree($data);
    }

    public function getPovertyreasonAttr($data){
        return viewPovertyreason($data);
    }

    public function getOutpovertyAttr($data){
        return viewOutpoverty($data);
    }

} 