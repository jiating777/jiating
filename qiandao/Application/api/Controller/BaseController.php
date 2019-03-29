<?php

namespace app\api\controller;

use app\api\service\Token as TokenService;

use app\admin\model\User;

use think\Controller;
use think\Request;

class BaseController extends Controller
{

    const MSG_SUCCESS = '请求成功';
    const NOT_DATA = '暂无数据';
    const NOT_PARAM = '缺少参数';

    // 默认组织名
    protected $defaultOrganization = '智慧乡镇';


    protected function checkPrimaryScope(){
        TokenService::needPrimaryScope();

    }

    protected function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }

    public function getHttpParam()
    {
        $param = json_encode(Request::instance()->param());
        return json_decode($param);
    }

    /**
     * 获取用户信息
     *
     * @param $where
     * @return mixed
     */
    protected function getUserInfo($where){
        if(!$where['id']){
            return false;
        }
        $userInfo = User::where($where)->find();

        $memberId = $userInfo->memberId;
        if($memberId && $memberId !== 0){
            $userInfo = User::alias('a')->where(['a.id' => $where['id']])->join('__MEMBER__ m', 'a.memberId = m.id')->find();

            return $userInfo;
        }

        return $userInfo;
    }

    /**
     * 根据镇ID 找到上级ID
     *
     * @return mixed
     */
    public function getParentIdsByTownId($townId)
    {
        if(!$townId){
            return [];
        }
        $model = db('area');
        $townParentId = $model->where(['id' => $townId])->value('parentId');
        $xian = $model->where(['id' => $townParentId])->field('id, parentId')->find();
        $cityId = $model->where(['id' => $xian['parentId']])->value('id');

        $data = [
            'cityId' => $cityId,
            'xianId' => $xian['id'],
        ];

        return $data;
    }

    /**
     * 根据镇ID 得到配置
     *
     * @return mixed
     */
    public function getTownConfig($townId)
    {
        if(!$townId){
            return [];
        }
        $model = db('townconfig');
        $config = $model->where(['townId' => $townId])->find();

        return $config;
    }

}