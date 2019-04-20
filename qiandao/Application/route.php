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
Route::rule('editrole','admin/role/edit');  // 编辑角色

Route::rule('schedule','admin/schedule/index');  // 学校作息时间列表

Route::rule('menu','admin/menu/index');  // 后台菜单管理
Route::rule('user','admin/user/index');  // 后台菜单管理
Route::rule('test','admin/common/test');  // 





// ++++++++++++++++++++ API 接口 ++++++++++++++++++++ //



// ++++++++++++++++++++ API 接口 ++++++++++++++++++++ //


// ++++++ 农事服务 ++++++ //
Route::rule('wx/knowledgetype','api/Knowledge/getType'); // 专题列表
Route::rule('wx/knowledgeList','api/Knowledge/getList'); // 农事知识列表
Route::rule('wx/getKnowledgeHomeFrame','api/Knowledge/getLastList'); // 农事知识最新6条列表
Route::rule('wx/knowledgeDetail','api/Knowledge/getDetail'); // 农事知识详情

// ++++++ 智慧党建 ++++++ //
// 智慧党建首页
Route::rule('wx/getPartyHomeFrame','api/PartyHomeFrame/getPartyHomeFrame');
Route::rule('wx/organizationList','api/Organization/getList');  //组织列表
Route::rule('wx/partyList','api/Organization/partyList');  //组织下的成员列表
Route::rule('wx/partyDetail','api/Member/getPartyDetail');  //组织下的成员详情

// 微课堂
Route::rule('wx/microclassroom/getMicroclassroomType','api/Microclassroom/getMicroclassroomType'); // 分类
Route::rule('wx/microclassroom/getMicroclassroomList','api/Microclassroom/getMicroclassroomList'); // 列表
Route::rule('wx/microclassroom/getMicroclassroomDetail','api/Microclassroom/getMicroclassroomDetail'); // 详情
Route::rule('wx/microclassroom/viewClasshourFile','api/Microclassroom/viewClasshourFile'); // 课程文件浏览
Route::rule('wx/microclassroom/joinClassroom','api/Microclassroom/joinClassroom'); // 加入学习计划
// 在线考试
Route::rule('wx/onlineexam/getOnlineexamList','api/Onlineexam/getOnlineexamList'); // 列表
Route::rule('wx/onlineexam/getOnlineexamDetail','api/Onlineexam/getOnlineexamDetail'); // 详情
Route::rule('wx/onlineexam/getExamquestion','api/Onlineexam/getExamquestion'); // 获取考题
Route::rule('wx/onlineexam/placeExamquestion','api/Onlineexam/placeExamquestion'); // 提交考题答案
Route::rule('wx/onlineexam/getExamresults','api/Onlineexam/getExamresults'); // 考试结果
Route::rule('wx/onlineexam/viewExamresults','api/Onlineexam/viewExamresults'); // 查看答案
// 会议
Route::rule('wx/meeting/getMeetingList','api/Meeting/getMeetingList'); // 列表
Route::rule('wx/meeting/getMeetingDetail','api/Meeting/getMeetingDetail'); // 详情
// 调研
Route::rule('wx/research/getResearchList','api/Research/getResearchList'); // 列表
Route::rule('wx/research/getResearchDetail','api/Research/getResearchDetail'); // 详情
Route::rule('wx/research/placeResearch','api/Research/placeResearch'); // 提交调研
// ++++++ 精准扶贫 ++++++ //




// ++++++++++++ 通用 ++++++++++++ //
// 轮播图
Route::rule('wx/handle/adbanner','api/Common/adbanner');
// 关注
Route::rule('wx/handle/attention','api/Common/attention');
// 取消关注
Route::rule('wx/handle/cancelAttention','api/Common/cancelAttention');
// 发布评论
Route::rule('wx/handle/postComment','api/Common/postComment');
// 收藏
Route::rule('wx/handle/collect','api/Common/collect');
// 取消收藏
Route::rule('wx/handle/cancelCollect','api/Common/cancelCollect');
// 点赞
Route::rule('wx/handle/like','api/Common/like');
// 获取评论列表
Route::rule('wx/handle/commentList','api/Common/commentList');
// 上传单张图片
Route::rule('wx/uploadOneImg','api/Common/uploadOneImg');
// 删除单张图片
Route::rule('wx/delOneImg','api/Common/delOneImg');


//文章相关
Route::rule('wx/article/getArticle','api/Article/getArticle');
Route::rule('wx/article/getArticleType','api/Article/getArticleType');
Route::rule('wx/article/getArticleDetail','api/Article/getArticleDetail');
Route::rule('api/test','api/Article/test');


// ++++++++++++++++++++ 获取openID ++++++++++++++++++++ //
Route::rule('wx/token/user','api/Token/getToken');
Route::rule('wx/getUserinfo','api/UserInfo/getUserInfo');


Route::rule('wx/decodePhone','api/UserInfo/decodePhone');  //解析手机号
Route::rule('wx/wechat/userInfo','api/wechat/userInfo');  //保存用户信息
Route::rule('wx/wechat/getInfo','api/wechat/getInfo');  //获取用户信息

Route::rule('wx/weather/getWeather','api/Weather/getWeather');  //天气

