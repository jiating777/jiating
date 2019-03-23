<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>签到2019</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/Public/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/Public/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/Public/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/Public/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/Public/plugins/datatables/datatables.min.css" rel="stylesheet">
    <link href="/Public/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet">
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/Public/admin/css/components.min.css" rel="stylesheet" type="text/css" />
    <link href="/Public/admin/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="/Public/admin/css/layout.min.css" rel="stylesheet" type="text/css" />
    <link href="/Public/admin/css/themes/blue.min.css" rel="stylesheet" type="text/css" id="style_color" />
    <link href="/Public/admin/css/custom.css" rel="stylesheet" type="text/css" />
    <!-- END THEME LAYOUT STYLES -->
</head>
<!-- END HEAD -->

<body class="page-header-fixed page-sidebar-closed-hide-logo page-container-bg-solid">
<script src="/Public/admin/js/jquery-3.3.1.min.js"></script>
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="javascript:;">
                <img src="/Public/admin/img/logo.png" alt="logo" class="logo-default" style="margin: 20px 0 0;" />
            </a>
            <div class="menu-toggler sidebar-toggler">
                <!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
        <!-- END RESPONSIVE MENU TOGGLER -->

        <!-- BEGIN PAGE TOP -->
        <!-- BEGIN PAGE TOP -->
<div class="page-top">
    <!-- BEGIN TOP NAVIGATION MENU -->
    <div class="top-menu">
        <ul class="nav navbar-nav pull-right">
            <li class="dropdown dropdown-user">
                <a href="<?php echo U('common/delcache');?>" class="dropdown-toggle">
                    <i class="fa fa-refresh"></i> 清除缓存&nbsp;&nbsp;&nbsp;&nbsp;
                </a>
            </li>
            <!-- BEGIN USER LOGIN DROPDOWN -->
            <li class="dropdown dropdown-user">
                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                    <img class="img-circle" src="/Public/admin/img/avatar.jpg" alt="" />
                    <span class="username username-hide-on-mobile"> <?php echo ($adminInfo["name"]); ?> </span>
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-default">
                    <li>
                        <a href="<?php echo U('admin/admin/profile');?>">
                            <i class="icon-user"></i> 个人中心
                        </a>
                    </li>
                    <li class="divider"> </li>
                    <li>
                        <a href="<?php echo U('admin/auth/logout');?>" onclick="localStorage.clear();">
                            <i class="icon-key"></i> 退出
                        </a>
                    </li>
                </ul>
            </li>
            <!-- END USER LOGIN DROPDOWN -->
        </ul>
    </div>
    <!-- END TOP NAVIGATION MENU -->
</div>
<!-- END PAGE TOP -->
        <!-- END PAGE TOP -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- BEGIN HEADER & CONTENT DIVIDER -->
<div class="clearfix"> </div>
<!-- END HEADER & CONTENT DIVIDER -->
<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!-- BEGIN SIDEBAR -->
    <!-- BEGIN SIDEBAR -->
<div class="page-sidebar-wrapper">
    <div class="page-sidebar navbar-collapse collapse">
        <!-- BEGIN SIDEBAR MENU -->
        <ul class="page-sidebar-menu  page-header-fixed page-sidebar-menu-hover-submenu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
            <?php echo ($sidebar); ?>
        </ul>
        <!-- END SIDEBAR MENU -->
    </div>
</div>
<!-- END SIDEBAR -->
    <!-- END SIDEBAR -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<!-- END FOOTER -->

<div id="common-modal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Title</h4>
            </div>
            <div class="modal-body">
                <p>Some ...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-success confirm-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<div id="modal-content" style="display: none;">
    {block name="modalcontent"}{/block}
</div>

<input type="hidden" class="getregion_url" value="">
<script>
    //全局变量
</script>


<!--[if lt IE 9]>
<script src="/Public/admin/js/respond.min.js"></script>
<script src="/Public/admin/js/excanvas.min.js"></script>
<script src="/Public/admin/js/ie8.fix.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="/Public/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/Public/admin/js/js.cookie.min.js" type="text/javascript"></script>
<script src="/Public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="/Public/plugins/datatables/datatables.min.js"></script>
<script src="/Public/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"></script>
<script src="/Public/plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="/Public/plugins/jquery-validation/localization/messages_zh.js"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="/Public/admin/js/app.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="/Public/admin/js/layout.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->

<script src="/Public/plugins/layer/layer.js" type="text/javascript"></script>
<script src="/Public/admin/js/common.js" type="text/javascript"></script>


</body>
</html>

<style>
    .mt-widget-3 .mt-head {
        margin-bottom: 0;
    }
    .mt-head-icon i {
        font-size: 50px;
    }
    .portlet-body .alert {
        margin: 12px -20px -15px;
    }
    .portlet-body .alert-success {
        background-color: #eee;
        border-color: #eee;
        color: #666666b5;
    }
    .pager .disabled {
        border: 0;
    }
    .pager .number {
        font-size: 40px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light portlet-fit ">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-microphone font-dark hide"></i>
                    <span class="caption-subject bold font-dark uppercase"> 菜单 </span>
                    <span class="caption-helper"></span>
                </div>
                <div class="actions">

                </div>
            </div>
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mt-widget-3">
                            <div class="mt-head bg-blue-hoki">
                                <div class="mt-head-icon">
                                    <i class="icon-note"></i>
                                </div>
                                <div class="mt-head-button">
                                    <a class="more" href="">
                                        <button type="button" class="btn btn-circle btn-outline white btn-sm"> 管理员管理 </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mt-widget-3">
                            <div class="mt-head bg-red">
                                <div class="mt-head-icon">
                                    <i class="icon-basket"></i>
                                </div>
                                <div class="mt-head-button">
                                    <a class="more" href="">
                                        <button type="button" class="btn btn-circle btn-outline white btn-sm"> 学生管理 </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mt-widget-3">
                            <div class="mt-head bg-green">
                                <div class="mt-head-icon">
                                    <i class="icon-book-open"></i>
                                </div>
                                <div class="mt-head-button">
                                    <a class="more" href="">
                                        <button type="button" class="btn btn-circle btn-outline white btn-sm"> 教师管理 </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mt-widget-3">
                            <div class="mt-head bg-purple">
                                <div class="mt-head-icon">
                                    <i class="fa fa-rebel"></i>
                                </div>
                                <div class="mt-head-button">
                                    <a class="more" href="">
                                        <button type="button" class="btn btn-circle btn-outline white btn-sm"> 咨询管理 </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <!--<i class="icon-share font-dark"></i>-->
                    <span class="caption-subject font-dark bold uppercase"> 动态发布 </span>
                </div>
                <div class="actions">

                </div>
            </div>
            <div class="portlet-body">
                <div>
                    <ul class="pager">
                        <li class="previous">
                            <span class="disabled">
                                今日新增人数
                            </span>
                        </li>
                        <li class="next">
                            <span class="disabled number"> <?php echo ($countData["todayDynamics ?: 0"]); ?> </span>
                        </li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    累计用户数 <?php echo ($countData["totalDynamics ?: 0"]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <!--<i class="icon-share font-dark"></i>-->
                    <span class="caption-subject font-dark bold uppercase"> 留言 </span>
                </div>
                <div class="actions">

                </div>
            </div>
            <div class="portlet-body">
                <div>
                    <ul class="pager">
                        <li class="previous">
                            <span class="disabled">
                                今日新增留言数
                            </span>
                        </li>
                        <li class="next">
                            <span class="disabled number"> <?php echo ($countData["todayMessages ?: 0"]); ?> </span>
                        </li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    累计留言数 <?php echo ($countData["totalMessages ?: 0"]); ?>
                </div>
            </div>
        </div>
    </div>
</div>