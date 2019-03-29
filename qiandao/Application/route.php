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




// ++++++++++++++++++++ API 接口 ++++++++++++++++++++ //
Route::rule('wx/getUserInfo','api/UserInfo/getUserInfo');
Route::rule('wx/saveUserInfo','api/UserInfo/saveUserInfo');

// ++++++++++++ 首页 Begin ++++++++++++ //
// 首页数据
Route::rule('wx/getHomeFrame','api/HomeFrame/getHomeFrame');

// ++++++ 全部村子 ++++++ //
Route::rule('wx/villages/getVillagesList','api/Villages/getVillagesList');
// 村子详情
Route::rule('wx/villages/getVillagesDetail','api/Villages/getVillagesDetail');
// 村民
Route::rule('wx/member/getMemberList','api/Member/getMemberList'); // 列表
Route::rule('wx/member/getMemberDetail','api/Member/getMemberDetail'); // 详情


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
// 精准扶贫首页
Route::rule('wx/getPoveryHomeFrame','api/PoveryHomeFrame/getPoveryHomeFrame');
// 扶贫项目
Route::rule('wx/povertyproject','api/Poverty/povertyproject');  //扶贫项目列表
Route::rule('wx/povertyprojectType','api/Poverty/povertyprojectType');  //扶贫项目分类列表
Route::rule('wx/povertyprojectDetail','api/Poverty/povertyprojectDetail');  //扶贫项目详情
// 扶贫工作
Route::rule('wx/povertywork','api/Poverty/povertywork');  //帮扶工作列表
// ++++++ 便民服务 ++++++ //
Route::rule('wx/getWorkList','api/article/getWorkList');
// ++++++++++++ 首页 End ++++++++++++ //


// ++++++++++++ 一村一品 Begin ++++++++++++ //
// 首页数据
Route::rule('wx/getProductHomeFrame','api/ProductHomeFrame/getProductHomeFrame');
// 搜索
Route::rule('wx/searchProduct','api/ProductHomeFrame/searchProduct');
// ++++++ 产地直供 ++++++ //
Route::rule('wx/product/getTownProducts','api/Product/getTownProducts'); // 镇农产品
// 产地农产品
Route::rule('wx/product/getVillageProducts','api/Product/getVillageProducts'); // 村农产品
// ++++++ 全部分类 ++++++ //
Route::rule('wx/product/getProductList','api/Product/getProductList'); // 分类产品
Route::rule('wx/product/getProductType','api/Product/getProductType'); // 产品分类
Route::rule('wx/product/getProductPlace','api/Product/getProductPlace'); // 产品产地
Route::rule('wx/product/getVillages','api/Product/getVillages'); // 获取村
Route::rule('wx/product/getRecommend','api/Product/getRecommend'); // 产品推荐
Route::rule('wx/product/searchProduct','api/Product/searchProduct'); // 搜索
// 农产品详情
Route::rule('wx/product/getProductDetail','api/Product/getProductDetail');
// ++++++ 预售 ++++++ //
Route::rule('wx/product/getPresaleProductList','api/Product/getPresaleProductList');
// 预售详情
Route::rule('wx/product/getPresaleProductDetail','api/Product/getPresaleProductDetail');
// ++++++++++++ 一村一品 End ++++++++++++ //


// ++++++++++++ 社区 Begin ++++++++++++ //
// 首页数据
Route::rule('wx/dynamic/getDynamicList','api/Dynamic/getDynamicList');
// 发布动态
Route::rule('wx/dynamic/postDynamic','api/Dynamic/postDynamic');
// 动态详情
Route::rule('wx/dynamic/getDynamicDetail','api/Dynamic/getDynamicDetail');
// 个人动态
Route::rule('wx/dynamic/getUserDynamics','api/Dynamic/getUserDynamics');
// ++++++++++++ 社区 End ++++++++++++ //


// ++++++++++++ 我的 Begin ++++++++++++ //
// 我是村民
// 提交村民审核
Route::rule('wx/userinfo/USEX','api/User/UserSubmitExamine');
Route::rule('wx/userinfo/getMyDetails','api/Member/getMyDetails');
// ++++++ 党员中心 ++++++ //
// 我的组织
Route::rule('wx/userinfo/getMyOrganizations','api/UserParty/getMyOrganizations');
// 学习计划
Route::rule('wx/userinfo/getMyClassrooms','api/UserParty/getMyClassrooms'); // 列表
Route::rule('wx/userinfo/getMyClassroomDetail','api/UserParty/getMyClassroomDetail'); // 详情
Route::rule('wx/userinfo/cancelClassroom','api/UserParty/cancelClassroom'); // 取消学习
// 我的考试
Route::rule('wx/userinfo/getMyOnlineexams','api/UserParty/getMyOnlineexams');
// 我的会议
Route::rule('wx/userinfo/getMyMeetings','api/UserParty/getMyMeetings'); // 列表
Route::rule('wx/userinfo/meetingSignIn','api/UserParty/meetingSignIn'); // 扫码签到
// 参与调研
Route::rule('wx/userinfo/getMyResearchs','api/UserParty/getMyResearchs');
// ++++++ 订单 ++++++ //
Route::rule('wx/..','api/..');
// 订单详情
Route::rule('wx/..','api/..');
// 申领样品
Route::rule('wx/order/placeSampleOrder','api/SampleOrder/placeSampleOrder');
// ++++++ 扶贫 ++++++ //
Route::rule('wx/userinfo/myPoverty','api/User/myPoverty');
// ++++++ 动态 ++++++ //
Route::rule('wx/userinfo/myDynamics','api/User/myDynamics'); // 列表
Route::rule('wx/userinfo/delDynamic','api/User/delDynamic'); // 删除
// ++++++ 关注 ++++++ //
Route::rule('wx/userinfo/myAttention','api/User/myAttention');
// ++++++ 收藏 ++++++ //
Route::rule('wx/userinfo/myCollect','api/User/myCollect');
// ++++++ 留言 ++++++ //
Route::rule('wx/userinfo/postMessage','api/User/postMessage');  //提交留言
// ++++++++++++ 我的 End ++++++++++++ //


// 订单相关
Route::post('createorder','api/Order/placeOrder');
Route::group('api/pay',[
    'pre_order' => ['api/Pay/getPreOrder',['method'=>'post']],
    'notify' => ['api/Pay/receiveNotify',['method'=>'post']],
    'offlineWechat' => ['api/Pay/offlineWecharPay',['method'=>'post']]
]);


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

