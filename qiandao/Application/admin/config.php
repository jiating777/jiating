<?php

return [

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'admin',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Dashboard',
    // 默认操作名
    'default_action'         => 'index',

    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '__STATIC__'    => '/public/static',
    ],

    // 默认跳转页面对应的模板文件
    //'dispatch_success_tmpl'  => APP_PATH . 'admin' . DS . 'view' . DS . 'success_jump.html',
    //'dispatch_error_tmpl'    => APP_PATH . 'admin' . DS . 'view' . DS . 'error_jump.html',

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // 过期时间
        'expire'         => 60 * 60 * 24, // 单位：秒
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => 'ses',
        // SESSION 前缀
        'prefix'         => 'mxkj',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],


];
