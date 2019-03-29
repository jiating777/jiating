/**
 * Common
 */
var COMMON = function() {

    // 日历控件初始化
    var init_datepicker = function(obj, languages) {
        var startDate = obj.attr('data-start') || -Infinity;
        var endDate = obj.attr('data-end') || Infinity;
        obj.datepicker({
            language: languages,
            format: 'yyyy-mm-dd',
            startDate: startDate,
            endDate: endDate,
            orientation: 'bottom', // 显示位置为下方
            clearBtn: true,
            autoclose: true
        });
    };

    var  init_daterangepicker = function(obj, languages) {
        var startDate = obj.attr('data-start') || -Infinity;
        var endDate = obj.attr('data-end') || Infinity;
        obj.daterangepicker({
            language: languages,
            format: 'YYYY-MM-DD',
            startDate: startDate,
            endDate: endDate,
            autoclose: true
        });
    };

    // 表单验证
    var initValidation = function () {
        if ($('.validation-form').length) {
            $(".validation-form").validate();
        }
    };

    var handleSelect2 = function() {
        $.fn.select2.defaults.set("theme", "bootstrap");

        var placeholder = "请选择";

        $(".select2").select2({
            allowClear: true,
            placeholder: placeholder,
            width: null
        });
        $(".select2-multiple").select2({
            placeholder: placeholder,
            multiple : true,
            width: null
        });

        $(".select2-allow-clear").select2({
            allowClear: true,
            placeholder: placeholder,
            width: null
        });

        $("button[data-select2-open]").click(function() {
            $("#" + $(this).data("select2-open")).select2("open");
        });

        $(".select2, .select2-multiple, .select2-allow-clear, .js-data-example-ajax").on("select2:open", function() {
            if ($(this).parents("[class*='has-']").length) {
                var classNames = $(this).parents("[class*='has-']")[0].className.split(/\s+/);

                for (var i = 0; i < classNames.length; ++i) {
                    if (classNames[i].match("has-")) {
                        $("body > .select2-container").addClass(classNames[i]);
                    }
                }
            }
        });
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

    var handleAdvancedSearch = function () {
        var cookieName = $('#advancedSearch').data('model') + '_filter';
        if(Cookies.get(cookieName) === '0'){
            $('#advancedSearch').removeClass('opened').nextAll('.table-toolbar').find('.search-form').show();
            $('#advancedSearch .portlet-title').find('.tools').find('a').removeClass('collapse').addClass('expand');
            $('#advancedSearch .portlet-body').hide();

        }else if(Cookies.get(cookieName) === '1'){
            $('#advancedSearch').addClass('opened').nextAll('.table-toolbar').find('.search-form').hide();
            $('#advancedSearch .portlet-title').find('.tools').find('a').removeClass('expand').addClass('collapse');
            $('#advancedSearch .portlet-body').show();
        }

        $('#advancedSearch .portlet-title').click(function(){
            var _this = $(this);
            var isExpand = _this.find('.tools').find('a').hasClass('expand'),
                isCollapse = _this.find('.tools').find('a').hasClass('collapse');

            if(isExpand){
                _this.parent().addClass('opened').nextAll('.table-toolbar').find('.search-form').hide();
                _this.find('.tools').find('a').removeClass('expand').addClass('collapse');
                _this.next('.portlet-body').slideDown();
                Cookies.set(cookieName, '1');

            }else if(isCollapse){
                _this.parent().removeClass('opened').nextAll('.table-toolbar').find('.search-form').show();
                _this.find('.tools').find('a').removeClass('collapse').addClass('expand');
                _this.next('.portlet-body').slideUp();
                Cookies.set(cookieName, '0');
            }
        });
        $('#advancedSearch .portlet-title .tools').click(function(e){
            e.stopPropagation();
            if(Cookies.get(cookieName) === '0'){
                $(this).children('a').addClass('collapse').removeClass('expand');
                $(this).parent('.portlet-title').next('.portlet-body').slideDown();
                $(this).parent().parent().addClass('opened').nextAll('.table-toolbar').find('.search-form').hide();
                Cookies.set(cookieName, '1');
            }else if(Cookies.get(cookieName) === '1'){
                $(this).children('a').removeClass('collapse').addClass('expand');
                $(this).parent('.portlet-title').next('.portlet-body').slideUp();
                $(this).parent().parent().removeClass('opened').nextAll('.table-toolbar').find('.search-form').show();
                Cookies.set(cookieName, '0');
            }else {
                if($(this).parent().next('.portlet-body').is(':visible')){
                    $(this).children('a').addClass('collapse').removeClass('expand');
                    $(this).parent('.portlet-title').next('.portlet-body').slideDown();
                    $(this).parent().parent().addClass('opened').nextAll('.table-toolbar').find('.search-form').hide();
                    Cookies.set(cookieName, '1');
                }else{
                    $(this).children('a').removeClass('collapse').addClass('expand');
                    $(this).parent('.portlet-title').next('.portlet-body').slideUp();
                    $(this).parent().parent().removeClass('opened').nextAll('.table-toolbar').find('.search-form').show();
                    Cookies.set(cookieName, '0');
                }
            }
        });
    };

    return {
        init: function() {
            init_datepicker($(".date-picker"), 'cn');
            //init_daterangepicker($(".daterange-picker"), 'zh-CN');
            //initValidation();
            //handleSelect2();
            confirmDialog();
            handleAdvancedSearch();

            this.onEvent();
        },

        onEvent: function() {
            // 禁止自动完成功能
            $('input').prop('autocomplete', 'off');
        }
    };
}();

jQuery(document).ready(function() {
    COMMON.init();

});

/**
 * 打开modal
 */
function open_modal(title, content, options) {
    var element = $('#common-modal');
    var params = {
        'width' : '1000px',
        // 'height' : '300px'
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
    // $modal_dialog.css({'margin': m_top + 'px auto'});

    element.modal('show');
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
            max_file_size: '2M',         //最大文件限制
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
                    var sourceLink = domain + res.key;//获取上传文件的链接地址
                    console.log(sourceLink);

                    $.ajax({
                        url: record_url,
                        type: 'POST',
                        data: {
                            'imgUrl' : sourceLink,
                            'controller' : 'wxindex',
                            'isVideo' : 1
                        },
                        cache: false,
                        dataType : 'json',
                        success: function (data) {
                            if(data.code === '1') {
                                // pic_table.on('click', '.btn_select_role', function(event) {
                                //     var oData_arr = pic_table.DataTable().rows($(this).parents("tr")).data(); // 操作行对象
                                //     var imgUrl = oData_arr[0]['imgUrl'];
                                //     $(e).find('img').attr('src',imgUrl);
                                //     $(this).parents(".modal").modal('hide');
                                // });
                            }
                        }
                    });

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