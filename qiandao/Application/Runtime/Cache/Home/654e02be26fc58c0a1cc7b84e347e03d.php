<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>后台登录</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="Preview page of Metronic Admin Theme #3 for " name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="__STATIC__/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="__STATIC__/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="__STATIC__/admin/login/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="__STATIC__/admin/login/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="__STATIC__/admin/login/css/login.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <!-- END THEME LAYOUT STYLES -->
</head>
<!-- END HEAD -->

<body class=" login">
<!-- BEGIN : LOGIN PAGE 5-1 -->
<div class="user-login-5">
    <div class="row bs-reset">
        <div class="col-md-6 bs-reset mt-login-5-bsfix">
            <div class="login-bg">
                <!--<img class="login-logo" src="" />-->
            </div>
        </div>
        <div class="col-md-6 login-container bs-reset mt-login-5-bsfix">
            <div class="login-content">
                <h1>后台登录</h1>
                <p> 智慧乡镇后台管理系统 </p>
                <form action="<?php echo url('admin/auth/dologin');?>" class="login-form" method="post">
                    <div class="alert alert-danger display-hide">
                        <button class="close" data-close="alert"></button>
                        <span></span>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <input class="form-control form-control-solid placeholder-no-fix form-group" type="text" autocomplete="off" placeholder="用户名" name="username" id="username" />
                        </div>
                        <div class="col-xs-6">
                            <input class="form-control form-control-solid placeholder-no-fix form-group" type="password" autocomplete="off" placeholder="密码" name="password" id="password" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-right">
                            <button class="btn green login-btn" type="button">登录</button>
                        </div>
                    </div>
                </form>
            </div>
            <!--<div class="login-footer">
                <div class="row bs-reset">
                    <div class="col-xs-7 bs-reset">
                        <div class="login-copyright text-right">
                            <p>Copyright &copy; 秒讯科技 2018</p>
                        </div>
                    </div>
                </div>
            </div>-->
        </div>
    </div>
</div>
<!-- END : LOGIN PAGE 5-1 -->
<!-- BEGIN CORE PLUGINS -->
<script src="__STATIC__/admin/login/js/jquery-3.3.1.min.js"></script>
<script src="__STATIC__/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="__STATIC__/admin/login/js/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<script src="__STATIC__/admin/login/js/jquery.backstretch.js" type="text/javascript"></script>
<script src="__STATIC__/plugins/layer/layer.js" type="text/javascript"></script>

<script>
    //全局变量
    var APP = "<?php echo url('/');?>";
</script>
<script src="__STATIC__/admin/login/js/login.js" type="text/javascript"></script>

</body>
</html>