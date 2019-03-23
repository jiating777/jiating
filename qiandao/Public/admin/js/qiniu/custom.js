window.onload = function () {
    $(".navli:nth-child(3) .nav-toggle i").addClass("icon-screen-desktop");
    $(".navli:nth-child(4) .nav-toggle i").addClass("glyphicon glyphicon-briefcase");
    $(".navli:nth-child(5) .nav-toggle i").addClass("glyphicon glyphicon-user");
    $(".navli:nth-child(6) .nav-toggle i").addClass("glyphicon glyphicon-shopping-cart");
    $(".navli:nth-child(7) .nav-toggle i").addClass("glyphicon glyphicon-record");
    $(".navli:nth-child(8) .nav-toggle i").addClass("icon-layers");
    $(".navli:nth-child(9) .nav-toggle i").addClass("glyphicon glyphicon-cog");
    $(".navli:nth-child(10) .nav-toggle i").addClass("glyphicon glyphicon-envelope");
    $(".navli:nth-child(11) .nav-toggle i").addClass("glyphicon glyphicon-list-alt");

    var relUrl = document.location.pathname;
    console.log(relUrl);
    $(".page-sidebar .nav-link").each(function () {
        $this = $(this);
        var subUrl = $(this).attr("href");
        //console.log(subUrl);
        if ($this[0].href == String(window.location) || relUrl.indexOf(subUrl) >= 0) {
            //$this.addClass("on");
            $this.parents(".sub-menu").prev(".nav-toggle").find(".arrow").addClass("open");
            $this.parents(".sub-menu").show();
            $this.parents(".nav-item").addClass("open");
            $this.parent("li").siblings("li").removeClass("open").removeClass("active");
        }
    });
    /*$(".page-sidebar .open .nav-toggle").css("border-left","solid 4px #5c9acf");
    $(".page-sidebar .open .nav-toggle").css("padding","12px 11px");*/

    $("table").parent("div").addClass("tabContainer");
    console.log("-------hello---begin-----");
    // getMsg();

}
var noteLists = [];//定义数组存储未读公告的nodeId
var orderLists = [];//定义数组存储未读公告的orderId
var afterSaleLists = [];//定义数组储存未读orderId
var isFirstFlag = true;
var isFirstNote = true;
var isFirstAfterSale = true;
//window.setInterval("getMsg()", 10000); // 10秒执行
var msgAudio = document.createElement('audio') // 生成一个audio元素
// msgAudio.src = '/public/static/assets/pages/media/msg.mp3' // 音乐的路径


function msgCheck(arr) {
    var reflag = 0;
    for (var i = 0; i < arr.length; i++) {
        var oneflag = true;
        for (var j = 0; j < orderLists.length; j++) {
            if (arr[i].id == orderLists[j]) {
                oneflag = false;
                break;
            }
        }
        if (oneflag) {
            reflag++;
            orderLists[orderLists.length] = arr[i].id;
        }
    }
    return reflag;
}

function msgCheck2(arr) {
    var reflag = 0;
    for (var i = 0; i < arr.length; i++) {
        var oneflag = true;
        for (var j = 0; j < noteLists.length; j++) {
            if (arr[i].id == noteLists[j]) {
                oneflag = false;
                break;
            }
        }
        if (oneflag) {
            reflag++;
            noteLists[noteLists.length] = arr[i].id;
        }
    }
    return reflag;
}

function msgCheck3(arr) {
    var reflag = 0;
    for (var i = 0; i < arr.length; i++) {
        var oneflag = true;
        for (var j = 0; j < afterSaleLists.length; j++) {
            if (arr[i].id == afterSaleLists[j]) {
                oneflag = false;
                break;
            }
        }
        if (oneflag) {
            reflag++;
            afterSaleLists[afterSaleLists.length] = arr[i].id;
        }
    }
    return reflag;
}

//数组去重
function MsgUnique(oldArray, newArray) {
    var n = oldArray;
    var res = []; //一个新的临时数组
//遍历当前数组 
    for (var i = 0; i < newArray.length; i++) {
//如果当前数组的第i已经保存进了临时数组，那么跳过， 
//否则把当前项push到临时数组里面 
        if (n.indexOf(newArray[i]) == -1)
            res.push(newArray[i]);
    }
    return res;
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

/**
 * AJAX通用处理
 */

var url = "";
var pname = window.location.pathname;
var isExist = pname.indexOf("?");
if (isExist > 0) {
    url = pname.substr(0, isExist).replace('/p/', '/a/');
} else {
    url = pname.replace('/p/', '/a/');
}


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
    getLikeTpe: function (data) {
        if (data == '0') {
            return "无连接"
        } else if (data == '1') {
            return "商品详情"
        } else if (data == '2') {
            return "商品分类"
        } else if (data == '3') {
            return "优惠券列表"
        } else if (data == '4') {
            return "商品搜索"
        } else if (data == '5') {
            return "到店自提"
        } else if (data == '9') {
            return "拼团列表"
        } else if (data == '7') {
            return "文章"
        } else if (data == '8') {
            return "文章分类"
        } else if (data == '6') {
            return "拼团商品"
        } else if (data == '10') {
            return "积分商品列表"
        } else if (data == '11') {
            return "积分商品详情"
        } else if (data == '12') {
            return "分销页面"
        } else if (data == '13') {
            return "门店列表"
        } else if (data == '14') {
            return "门店详情"
        } else if (data == '21') {
            return "分享返现"
        } else if (data == '22') {
            return "会员开通"
        } else if (data == '23') {
            return "门店详情"
        } else if (data == '24') {
            return "我的订单"
        } else if (data == '25') {
            return "我的优惠券"
        } else if (data == '26') {
            return "我的拼团"
        } else if (data == '27') {
            return "账户余额"
        } else if (data == '29') {
            return "首页"
        } else {
            return "无连接"
        }
    }, isRepeat: function (ary) {
        var nary = ary.sort();

        for (var i = 0; i < ary.length; i++) {

            if (nary[i] == nary[i + 1]) {
                return true;
            }
        }
        return false;
    }


};

/**
 * Created by lzw on 2017/09/16 .
 *  七牛上传图片JS
 *  传入token后执行方法
 */

function uploaderReady(token, url, qiniupercent, uploader, pickfiles) {
    var uploader = Qiniu.uploader({
        runtimes: 'html5,flash,html4',
        browse_button: pickfiles,     //上传按钮的ID
        container: uploader,      //上传按钮的上级元素ID
        drop_element: uploader,
        max_file_size: '500kb',         //最大文件限制
        flash_swf_url: '@{/assets/global/qiniu/Moxie.swf}',
        dragdrop: false,
        chunk_size: '4mb',              //分块大小
        //uptoken_url: '',              //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
        uptoken: token,                 //若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
        // save_key: true,              // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
        domain: qiniuConfig.returnUrl(),   //自己的七牛云存储空间域名
        multi_selection: false,         //是否允许同时选择多文件
        get_new_uptoken: false,
        filters: {
            mime_types: [               //文件类型过滤，这里限制为图片类型
                {title: "Image files", extensions: "jpg,jpeg,gif,png"}
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

                $('#btn-uploader [name="input_qiniu_url"]').val(sourceLink);
                $('#btn-uploader [name="img_qiniu_url"]').attr('src', sourceLink);
                $('#btn-uploader [name="imgUrl"]').attr('src', sourceLink);
                $('input[name="img"]').val(sourceLink);
                $('#btn-uploader [ type="button"]').css('background', "url(" + sourceLink + ")");
                $('#btn-uploader [ type="button"]').css('background-size', "100%");


                console.log(sourceLink);
                if (url != null && url != '') {
                    $.ajax({
                        url: url,
                        type: 'put',
                        dataType: 'json',
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify(sourceLink),
                    }).done(function (data) {
                        if (data.status === '1') {
                            alert('上传成功！', function () {
                                /*table.DataTable().ajax.reload(); // 刷新table
                                 $('#modal_new_role').modal('hide');*/
                            });

                        } else if (data.status === '2') {
                            //	$('#modal_new_role .help-block').html(data.msg); // 返回错误信息
                        }
                    });
                }
            },
            'Error': function (up, err, errTip) {
                if ('文件验证失败。请稍后重试。' == errTip) {
                    errTip = "请上传图片格式";
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
}


/**
 * Created by lzw on 2017/11/21 .
 *  七牛上传图片JS
 *  传入token后执行方法  优化
 */
function uploaderQiniuReady(token, qiniupercent, pickfiles) {
    var uploader = Qiniu.uploader({
        runtimes: 'html5,flash,html4',
        // browse_button:  ['pickfiles','pickfiles1','pickfiles2','pickfiles3'],     //上传按钮的ID
        browse_button: pickfiles,     //上传按钮的ID
        /* container: uploader,      //上传按钮的上级元素ID
         drop_element: uploader,*/
        max_file_size: '500kb',         //最大文件限制
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
                {title: "Image files", extensions: "jpg,jpeg,gif,png"}
            ]
        },
        auto_start: true,
        unique_names: true,             //自动生成文件名,如果值为false则保留原文件名上传
        init: {
            'FilesAdded': function (up, files) {

                plupload.each(files, function (file) {
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
                $('#' + pickfiles + ' [name="img_qiniu_url"]').parent().attr('src', sourceLink);
                /* $(' [name="imgUrl"]').attr('src',sourceLink);
                 $('input[name="img"]').val(sourceLink);
                 $(' [ type="button"]').css('background',"url("+sourceLink+")");
                 $(' [ type="button"]').css('background-size',"100%"); */
                console.log(sourceLink);

            },
            'Error': function (up, err, errTip) {
                if ('文件验证失败。请稍后重试。' == errTip) {
                    errTip = "请上传图片格式";
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
}

//新增角色
var request_role_new = function (data, resolve, reject) {
    mdTool.request({
        //   url: '/a/sys/role',
        url: url,
        method: 'PUT',
        data: data,
        success: resolve,
        error: reject
    })
};
//修改角色
var request_role_edit = function (data, resolve, reject) {
    mdTool.request({
        // url: '/a/sys/role',
        url: url,
        method: 'POST',
        data: data,
        success: resolve,
        error: reject
    })
};
//删除角色
var request_role_del = function (data, resolve, reject) {
    mdTool.request({
        // url: '/a/sys/role',
        url: url,
        method: 'DELETE',
        data: data,
        success: resolve,
        error: reject
    })
};
//获取角色
var request_role_get = function (id, resolve, reject) {
    mdTool.request({
        //   url: '/a/sys/role',
        url: url,
        method: 'get',
        data: "id=" + encodeURIComponent(id),
        success: resolve,
        error: reject
    })
};


function timeParse(time) {
    var newDate = new Date();
    var Strs = time.split(":");
    newDate.setHours(Strs[0]);
    newDate.setMinutes(Strs[1]);
    newDate.setSeconds(0);
    return newDate;
}

function stringLines(f) {
    return f.toString().replace(/^[^\/]+\/\*!?\s?/, '').replace(/\*\/[^\/]+$/, '');
}






