/**
 * Common
 */
var COMMON = function() {

    // 日历控件初始化
    var init_datepicker = function(obj, languages) {
        var startDate = obj.attr('data-start') || -Infinity;
        var endDate = obj.attr('data-end') || Infinity;
        obj.datepicker({
            language:languages,
            format: 'yyyy-mm-dd',
            startDate: startDate,
            endDate: endDate,
            autoclose: true
        });
    };

    // Icheck
    var initIcheck = function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    };

    // 表单验证
    var initValidation = function () {
        if ($('.validation-form').length) {
            $(".validation-form").validate();
        }
    };

    // 确认提示框
    var confirmDialog = function(){
        $('[data-confirm]').on('click', function(e){
            var _this = $(this);
            var text = $(this).data('confirm') ? $(this).data('confirm') : '是否要执行此操作？';
            var title = $(this).data('title') ? $(this).data('title') : '请确认';
            var confirmBtn = $(this).data('confirmbtn') ? $(this).data('confirmbtn') : '确定';
            var cancelBtn = $(this).data('cancelbtn') ? $(this).data('cancelbtn') : '取消';
            e.preventDefault();
            layer.confirm(
                text, // 内容
                {
                    //icon: 7,
                    // 标题
                    title: title,
                    // 按钮
                    btn: [confirmBtn, cancelBtn]
                },
                function(index){
                    if (_this.is('a') && _this.attr('href') != '' && _this.attr('href') != '#') {
                        window.location.href = _this.attr('href');
                    } else if (_this.is(':submit')) {
                        _this.parents('form').submit();
                    }
                    layer.close(index);
                }
            );
        });
    };

    return {
        init: function() {
            init_datepicker($(".date-picker"), 'cn');
            //initIcheck();
            initValidation();
            confirmDialog();
        }
    };
}();

jQuery(document).ready(function() {
    COMMON.init();

    /* 复选框全选(支持多个，纵横双控全选)。
     * 实例：版块编辑-权限相关（双控），验证机制-验证策略（单控）
     * 说明：
     *  "js-check"的"data-xid"对应其左侧"js-check-all"的"data-checklist"；
     *  "js-check"的"data-yid"对应其上方"js-check-all"的"data-checklist"；
     *  全选框的"data-direction"代表其控制的全选方向(x或y)；
     *  "js-check-wrap"同一块全选操作区域的父标签class，多个调用考虑
     */
    if ($('.js-check-wrap').length) {
        var total_check_all = $('input.js-check-all');

        //遍历所有全选框
        $.each(total_check_all, function () {
            var check_all = $(this),
                check_items;

            //分组各纵横项
            var check_all_direction = check_all.data('direction');
            check_items = $('input.js-check[data-' + check_all_direction + 'id="' + check_all.data('checklist') + '"]').not(":disabled");

            //点击全选框
            check_all.change(function (e) {
                var check_wrap = check_all.parents('.js-check-wrap'); //当前操作区域所有复选框的父标签（重用考虑）
                if ($(this).prop('checked')) {
                    //全选状态
                    check_items.prop('checked', true);
                    check_items.parent().addClass("checked");

                    //所有项都被选中
                    if (check_wrap.find('input.js-check').length === check_wrap.find('input.js-check:checked').length) {
                        check_wrap.find(total_check_all).prop('checked', true);
                        check_wrap.find(total_check_all).parent().addClass("checked");
                    }
                } else {
                    //非全选状态
                    check_items.prop('checked', false);
                    check_items.removeProp('checked');
                    check_items.parent().removeClass("checked");

                    check_wrap.find(total_check_all).removeProp('checked');
                    check_wrap.find(total_check_all).parent().removeClass("checked");

                    //另一方向的全选框取消全选状态
                    var direction_invert = check_all_direction === 'x' ? 'y' : 'x';
                    check_wrap.find($('input.js-check-all[data-direction="' + direction_invert + '"]')).removeProp('checked');
                    check_wrap.find($('input.js-check-all[data-direction="' + direction_invert + '"]')).parent().removeClass("checked");
                }
            });

            //点击非全选时判断是否全部勾选
            check_items.change(function () {
                if ($(this).prop('checked')) {
                    if (check_items.filter(':checked').length === check_items.length) {
                        //已选择和未选择的复选框数相等
                        check_all.prop('checked', true);
                        check_all.parent().addClass("checked");
                    }
                } else {
                    //check_items.prop('checked', false);
                    $(this).prop('checked', false);
                    check_all.removeProp('checked');
                    check_all.parent().removeClass("checked");
                }

            });
        });
    }

});


/**
 * 输入为空检查
 * @param name '#id' '.id'  (name模式直接写名称)
 * @param type 类型  0 默认是id或者class方式 1 name='X'模式
 */
function is_empty(name, type){
    if(type == 1){
        if($('input[name="'+name+'"]').val() == ''){
            return true;
        }
    }else{
        if($(name).val() == ''){
            return true;
        }
    }
    return false;
}

/**
 * 打开iframe式的窗口对话框
 * @param url
 * @param title
 * @param options
 */
function open_iframe_layer(url, title, options) {
    var params = {
        type: 2,
        title: title,
        shadeClose: true,
        skin: 'layui-layer-nobg',
        shade: [0.5, '#000000'],
        area: ['90%', '90%'],
        content: url
    };
    params = options ? $.extend(params, options) : params;

    layer.open(params);
}

/**
 * 打开modal
 */
function open_modal(title, content, options) {
    var element = $('#common-modal');
    var params = {
        'width' : '1000px',
        'height' : '500px',
    };
    params = options ? $.extend(params, options) : params;

    var $modal_dialog = element.find('.modal-dialog');
    var modal_title = element.find('.modal-title');
    var modal_body = element.find('.modal-body');
    $modal_dialog.css({'width' : params.width, 'height' : params.height});
    modal_body.css({'height' : params.height, 'overflow-y' : 'auto'});
    modal_title.empty().html(title);
    modal_body.empty().html(content);

    // 获取可视窗口的高度
    var clientHeight = (document.body.clientHeight < document.documentElement.clientHeight) ? document.body.clientHeight: document.documentElement.clientHeight;
    // 得到dialog的高度
    var dialogHeight = $modal_dialog.height();
    // 计算出距离顶部的高度
    var m_top = (clientHeight - dialogHeight - 60) / 2;
    if(m_top < 5){
        m_top = 5;
    }
    // 居中显示
    $modal_dialog.css({'margin': m_top + 'px auto'});

    element.modal('show');
}

// 获取活动剩余天数 小时 分钟
//倒计时js代码精确到时分秒，使用方法：注意 var EndTime= new Date('2013/05/1 10:00:00'); //截止时间 这一句，特别是 '2013/05/1 10:00:00' 这个js日期格式一定要注意，否则在IE6、7下工作计算不正确哦。
//js代码如下：
function get_trime(end_time){
    // var EndTime= new Date('2016/05/1 10:00:00'); //截止时间 前端路上 http://www.51xuediannao.com/qd63/
    var EndTime= new Date(end_time); //截止时间 前端路上 http://www.51xuediannao.com/qd63/
    var NowTime = new Date();
    var t =EndTime.getTime() - NowTime.getTime();
    /*var d=Math.floor(t/1000/60/60/24);
    t-=d*(1000*60*60*24);
    var h=Math.floor(t/1000/60/60);
    t-=h*60*60*1000;
    var m=Math.floor(t/1000/60);
    t-=m*60*1000;
    var s=Math.floor(t/1000);*/

    var d=Math.floor(t/1000/60/60/24);
    var h=Math.floor(t/1000/60/60%24);
    var m=Math.floor(t/1000/60%60);
    var s=Math.floor(t/1000%60);
    if(s >= 0)
        return d + '天' + h + '小时' + m + '分' +s+'秒';
}


$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

var mdTool = {
    request: function (param) {
        var _this = this;
        $.ajax({
            //url: param.url || '',
            url: url || '',
            type: param.method || 'get',
            dataType: param.type || 'json',
            contentType: "application/json; charset=utf-8",
            data: param.data || '',
            success: function (res) {
                // 状态 status + 数据 data

                // 请求数据正确，调用param.success方法
                if (res.status === "1") {
                    typeof param.success === 'function' && param.success(res.data, res.message);
                }
                // 未登录
                else if (res.status === "3") {
                    window.location.href = "/";
                }
                // 请求数据错误, 调用param.error方法
                else if (res.status === "2") {
                    typeof param.error === 'function' && param.error(res.message);
                }
            },
            error: function (err) {
                // 404 503
                typeof param.error === 'function' && param.error(err.statusText);
            }
        });
    },
    // 表单验证
    validate: function (value, type) {
        // 非空验证
        if (type === 'null') {
            return value.indexOf(' ') > 0;
        }
        // 字段值
        var value = $.trim(value);

        // 非空验证
        if (type === 'require') {
            return !!value;
        }
        //汉字验证
        if (type === 'ecode') {
            return /^[^\u4e00-\u9fa5]{0,}$/.test(value);
        }
        // 手机号验证
        if (type === 'phone') {
            return /^1[0-9]{10}$/.test(value);
        }
        if (type === 'phone1') {
            return /^400-[0-9]{3}-[0-9]{4}|^800-[0-9]{3}-[0-9]{4}|^1(3|4|7|5|8)([0-9]{9})|^0[0-9]{2,3}-[0-9]{8}$/.test(value);
        }
        // 邮箱验证
        if (type === 'email') {
            return /^(\w)+(\.\w+)*@(\w)+((\.\w{2,3}){1,3})$/.test(value);
        }
        //验证非零的正整数
        if (type === 'num') {
            return /^\+?[1-9][0-9]*$/.test(value);
        }
        //验证1-10的正数
        if (type === 'num10') {
            return /^([1-9]|10)\.?\d*$/.test(value);
        }
        //验证正数
        if (type === 'znum') {
            return /^(([0-9]+[\.]?[0-9]+)|[1-9])$/.test(value);
        }
        if (type === 'two') {
            return /^\d+(\.\d{1,2})?$/.test(value);
        }
        if (type === 'three') {
            return /^\d+(\.\d{1,3})?$/.test(value);
        }
    },

    // 表单验证
    showMessage: function (value) {
        $('#modal_new_role .help-block').html(value);
        $('#modal_new_role .help-block').css('color', 'red');
    },
    showMessage1: function (value) {
        $('#user_eidt_error').html(value);
        $('#user_eidt_error').css('color', 'red');
    },
    // 表单验证
    showMsg: function (id, value) {
        $('#' + id).html(value);
        $('#' + id).css('color', 'red');
    },
    showMessageForHelpBlock: function (value) {
        $('.help-block').html(value);
        $('.help-block').css('color', 'red');
    },

    fmtDate: function (obj) {
        var date = new Date(obj);
        var y = date.getFullYear();
        var m = "0" + (date.getMonth() + 1);
        var d = "0" + date.getDate();
        var h = "0" + date.getHours();
        var f = "0" + date.getMinutes();
        return y + "-" + m.substring(m.length - 2, m.length) + "-" + d.substring(d.length - 2, d.length) + " " + h.substring(h.length - 2, h.length) + ":" + f.substring(f.length - 2, f.length);
    },
    sumBytes: function (s) {
        var totalLength = 0;
        var i;
        var charCode;
        for (i = 0; i < s.length; i++) {
            charCode = s.charCodeAt(i);
            if (charCode < 0x007f) {
                totalLength = totalLength + 1;
            } else if ((0x0080 <= charCode) && (charCode <= 0x07ff)) {
                totalLength += 2;
            } else if ((0x0800 <= charCode) && (charCode <= 0xffff)) {
                totalLength += 3;
            }
        }
        return totalLength;
    },

    uploaderReady: function (token, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi) {
        var uploader = Qiniu.uploader({
            runtimes: 'html5,flash,html4',
            browse_button: pickfiles,     //上传按钮的ID
            container: uploader,      //上传按钮的上级元素ID
            drop_element: uploader,
            max_file_size: max_file_size,         //最大文件限制
            flash_swf_url: '@{/assets/global/qiniu/Moxie.swf}',
            dragdrop: false,
            chunk_size: '4mb',              //分块大小
            //uptoken_url: '',              //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
            uptoken: token,                 //若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
            // save_key: true,              // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
            domain: qiniuConfig.returnUrl(),   //自己的七牛云存储空间域名
            multi_selection: multi,         //是否允许同时选择多文件
            filters: {
                mime_types: [               //文件类型过滤，这里限制为图片类型
                    {title: "Image files", extensions: extensions}
                ]
            },
            auto_start: true,
            unique_names: true,             //自动生成文件名,如果值为false则保留原文件名上传
            init: {
                'FilesAdded': function (up, files) {
                    plupload.each(files, function (file) {
                        // 文件添加进队列后，处理相关的事情
                    });
                },
                'BeforeUpload': function (up, file) {
                    // 每个文件上传前，处理相关的事情
                },
                'UploadProgress': function (up, file) {
                    //文件上传时，处理相关的事情

                    /*可能是文件大小
                    var chunk_size = plupload.parseSize(this.getOption('chunk_size'));
                    */

                    //上传进度 class="layui-btn" type="button"
                    // $('#' + qiniupercent).attr('style', 'width:' + file.percent + '%');
                    // $("#" + qiniupercent).css("background-color", "green");

                    //console.log(file.percent + "%");
                },
                'UploadComplete': function () {
                    //do something
                },
                'FileUploaded': function (up, file, info) {
                    //每个文件上传成功后,处理相关的事情
                    //其中 info 是文件上传成功后，服务端返回的json，形式如
                    //{
                    //  "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                    //  "key": "gogopher.jpg"
                    //}
                    var domain = up.getOption('domain');
                    var res = eval('(' + info.response + ')');
                    /* var imageInfo = Qiniu.imageInfo(res.key);//获取图片原始大小

                     //imageInfo.format  图片格式   imageInfo.width  图片宽   imageInfo.height 图片高

                     console.log(imageInfo);
                     */
                    var sourceLink = domain + res.key;//获取上传文件的链接地址

                    $.ajax({   //记录上传的每一张图片
                        url: record_url,
                        type: 'POST',
                        data: {
                            'imgUrl': sourceLink,
                            'controller': 'test'
                        },
                        cache: false,
                        dataType: 'json',
                        success: function (data) {
                            if (data.code === '1') {
                            }
                        }
                    });
                    if ("sgimg_add" == pickfiles) {
                        $('#btn-uploader [name="iconUrl"]').val(sourceLink);
                        $('#btn-uploader [name="img"]').attr('src', sourceLink);
                    } else if ("sgDetail_add" == pickfiles) {
                        var isNext = true;
                        $("img[name='imgUrl']").each(function (i) {
                            if (isNext == true) {
                                if ($(this).attr("src") == null || $(this).attr("src") == "undefined") {
                                    $(this).val(sourceLink);
                                    $(this).attr('src', sourceLink);
                                    isNext = false;
                                    if (i == 4) {
                                        $("#sgDetail_add").prop("disabled", true)
                                    }
                                }
                            }
                        })

                    } else if ("sgimg_add1" == pickfiles) {
                        $('.isVideo video').prop('src', sourceLink);
                    } else {
                        $('#' + pickfiles + ' [name="img_qiniu_url"]').attr('src', sourceLink);
                        var $item = $("<div class='edui-image-item'><div class='edui-image-close'></div></div>").append($("<img src='" + sourceLink + "' class='edui-image-pic edui-image-width img-responsive' />"));
                        try {
                            UM.getEditor('container').focus();
                            UM.getEditor('container').execCommand('inserthtml', "<img src='" + sourceLink + "' class='edui-image-pic edui-image-width img-responsive' style='width:100%;'/>");
                        } catch (err) {
                        }
                    }
                    console.log(sourceLink);

                },
                'Error': function (up, err, errTip) {
                    if ('文件验证失败。请稍后重试。' == errTip) {

                        errTip = "请上传图片格式";
                        if (extensions == "mp4") {
                            errTip = "请上传mp4格式";
                        }
                    }
                    alert(errTip);
                },
            }
        });
        uploader.start();
    },
    uploaderVideoReady: function (token, qiniupercent, pickfiles) {
        var uploader = Qiniu.uploader({
            runtimes: 'html5,flash,html4',
            // browse_button:  ['pickfiles','pickfiles1','pickfiles2','pickfiles3'],     //上传按钮的ID
            browse_button: pickfiles,     //上传按钮的ID
            /* container: uploader,      //上传按钮的上级元素ID
             drop_element: uploader,*/
            max_file_size: '40M',         //最大文件限制
            flash_swf_url: '@{/assets/global/qiniu/Moxie.swf}',
            dragdrop: false,
            chunk_size: '4mb',              //分块大小
            //uptoken_url: '',              //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
            uptoken: token,                 //若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
            // save_key: true,              // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
            domain: qiniuConfig.returnUrl(),   //自己的七牛云存储空间域名
            multi_selection: false,         //是否允许同时选择多文件
            filters: {
                mime_types: [               //文件类型过滤，这里限制为图片类型
                    {title: "Image files", extensions: "mp4"}
                ]
            },
            auto_start: true,
            unique_names: true,             //自动生成文件名,如果值为false则保留原文件名上传
            init: {
                'FilesAdded': function (up, files) {
                    plupload.each(files, function (file) {
                        $('.success_help').html("正在上传，请稍等！");
                        $(".success_help").attr("style", "color:green;");
                        // 文件添加进队列后，处理相关的事情
                        /* let testImgSrc=file.getNative();
                           let temUrl=window.URL.createObjectURL(testImgSrc);
                           let objImg = document.querySelector('.img-responsive');
                           objImg.src = temUrl;
                               console.log(objImg.naturalWidth);
                               console.log(objImg.naturalHeight);

                               var fileItem = files[i].getNative(),
                               url = window.URL || window.webkitURL || window.mozURL;
                         var src = url.createObjectURL(fileItem);*/

                    });
                },
                'BeforeUpload': function (up, file) {
                    // 每个文件上传前，处理相关的事情
                },
                'UploadProgress': function (up, file) {
                    //文件上传时，处理相关的事情

                    /*可能是文件大小
                    var chunk_size = plupload.parseSize(this.getOption('chunk_size'));
                    */

                    //上传进度 class="layui-btn" type="button"
                    $('#' + qiniupercent).attr('style', 'width:' + file.percent + '%');
                    $("#" + qiniupercent).css("background-color", "green");

                    //console.log(file.percent + "%");
                },
                'UploadComplete': function () {
                    //do something
                },
                'FileUploaded': function (up, file, info) {
                    //每个文件上传成功后,处理相关的事情
                    //其中 info 是文件上传成功后，服务端返回的json，形式如
                    //{
                    //  "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                    //  "key": "gogopher.jpg"
                    //}
                    var domain = up.getOption('domain');
                    var res = eval('(' + info.response + ')');
                    var sourceLink = domain + res.key;//获取上传文件的链接地址
                    //$('#'+uploader +' [name="input_qiniu_url"]').val(sourceLink);

                    //    $(' [name="input_qiniu_url"]').val(sourceLink);
                    $('#' + pickfiles + ' [name="img_qiniu_url"]').attr('src', sourceLink);
                    $('.success_help').html("上传成功！");
                    $(".success_help").attr("style", "color:green;");
                    setTimeout(function () {
                        $('.success_help').html("");
                    }, 8000);//5秒后执行该方法
                    if (pickfiles == "pickfilesforarticleRadio") {
                        UM.getEditor('container').focus();
                        //单视频不加Your browser does not support the video tag.有问题
                        UM.getEditor('container').execCommand('inserthtml', "<video controls='controls' preload='auto'  class='edui-image-pic edui-image-width img-responsive' style='width:100%;'  src='" + sourceLink + "'/> Your browser does not support the video tag.</video>");

                        // UM.getEditor('container').execCommand('inserthtml',"<video controls='controls' preload='auto'  class='edui-image-pic edui-image-width img-responsive' style='width:100%;'  src='" + sourceLink + "'/></video>  ");
                    }

                    console.log(sourceLink);

                },
                'Error': function (up, err, errTip) {
                    if ('文件验证失败。请稍后重试。' == errTip) {
                        errTip = "请上传mp4格式";
                    }
                    alert(errTip);
                },
                'Key': function (up, file) {
                    //当save_key和unique_names设为false时，该方法将被调用
                    var key = "";
                    $.ajax({
                        url: '/qiniu-token/get-key/',
                        type: 'GET',
                        async: false,//这里应设置为同步的方式
                        success: function (data) {
                            var ext = Qiniu.getFileExtension(file.name);
                            key = data + '.' + ext;
                        },
                        cache: false
                    });
                    return key;
                }
            }
        });
        uploader.start();
    },
    isRepeat: function (ary) {
        var nary = ary.sort();

        for (var i = 0; i < ary.length; i++) {

            if (nary[i] == nary[i + 1]) {
                return true;
            }
        }
        return false;
    }


};

