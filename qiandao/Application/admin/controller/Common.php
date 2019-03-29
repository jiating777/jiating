<?php

namespace app\admin\controller;

use think\Cache;
use think\Controller;

class Common extends Controller
{

    /**
     * 清除缓存
     */
    public function delCache(){
        // 清文件缓存
        $dirs = [ROOT_PATH.'runtime/'];
        @mkdir('runtime',0777,true);
        foreach($dirs as $dir) {
            @unlink($dir);
        }
        // 清理缓存
        Cache::clear();

        $this->success('清除缓存成功！');
    }
}