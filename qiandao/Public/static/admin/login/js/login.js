var Login = function() {

    var handleLogin = function() {

        $('.login-form input').keypress(function(e) {
            if (e.which == 13) {
                submitForm();
                return false;
            }
        });

        /**
         * 登录表单提交验证
         */
        $('.login-btn').on('click', function(){
            submitForm();
        });
    };

    var submitForm = function () {
        var username = $('#username').val();
        var password = $('#password').val();

        var reg = /^\s*$/g;
        if(reg.test(username)){
            //layer.msg('请输入登录名！');
            tipsShow('请输入登录名！');
            $('#username').focus();
            return false;
        }
        if(reg.test(password)){
            //layer.msg('密码不能为空！');
            tipsShow('密码不能为空！');
            $('#password').focus();
            return false;
        }

        var url = $('.login-form').attr('action');
        var data = {
            'username' : username,
            'password' : password
        };
        console.log(url);
        $.post(url, data, function(data){
            if(data.code == 1){
                // window.location.href = APP + 'index';
                window.location.href = '/index';
            }else{
                //layer.msg(data.msg);
                tipsShow(data.msg);
            }
        });
    };

    var tipsShow = function (message) {
        $('span', $('.alert-danger')).empty().html(message);
        $('.alert-danger', $('.login-form')).show();
    };


    return {
        init: function() {

            handleLogin();

            // init background slide images
            $('.login-bg').backstretch([
                    "../../public/static/admin/login/img/bg1.jpg",
                    "../../public/static/admin/login/img/bg2.jpg",
                ], {
                  fade: 1000,
                  duration: 8000
                }
            );
        }
    };

}();

jQuery(document).ready(function() {
    Login.init();
});