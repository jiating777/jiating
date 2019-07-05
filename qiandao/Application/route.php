<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;


// ++++++++++++++++++++ 后台 ++++++++++++++++++++ //
Route::rule('login','admin/auth/login');  // 登录
Route::rule('index','admin/dashboard/index');  // 首页
Route::rule('role','admin/role/index');  // 角色管理列表
Route::rule('addrole','admin/role/add');  // 添加角色
Route::rule('editrole/:id','admin/role/edit');  // 编辑角色

Route::rule('schedule','admin/schedule/index');  // 学校作息时间列表
Route::rule('addschedule','admin/schedule/add');  // 学校作息时间列表
Route::rule('editschedule/:id','admin/schedule/edit');  // 学校作息时间列表

Route::rule('menu','admin/menu/index');  // 后台菜单管理
Route::rule('user','admin/user/index');  // 用户管理
Route::rule('message','admin/message/index');  // 留言管理
Route::rule('school','admin/school/index');  // 学校管理
Route::rule('department','admin/department/index');  // 学校管理
Route::rule('test','admin/common/test');  //





// ++++++++++++++++++++ API 接口 ++++++++++++++++++++ //
Route::rule('api/getuser','api/user/get');  //






