<?php

namespace app\common;

class Common
{
    /**
     * 后台管理员操作记录
     *
     * @param $request
     * @param string $logInfo
     */
    public static function adminLog($request, $logInfo){
        $data['adminId'] = session('ADMIN')['id'];
        $data['logInfo'] = $logInfo;
        $data['logIp'] = get_IP();
        $data['logUrl'] = $request->path();
        $data['logTime'] = time();

        $model = db('adminlog');
        $model->insert($data);
    }

    /**
     * 获取系统配置
     * @param $name 配置名
     */
    public static function siteConfig($name){
        $model = db('config');

        $config = $model->where(['name' => $name])->find();

        return $config ? $config : [];
    }
}