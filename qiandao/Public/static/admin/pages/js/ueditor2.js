// window.UMEDITOR_CONFIG.toolbar = [
//     'source | fontfamily fontsize | justifyleft justifycenter justifyright | bold italic underline strikethrough  forecolor | inserttable | insertorderedlist insertunorderedlist | image'
// ];

UE.registerUI('image', function(name) {
    var me = this;
    var $btn = new UE.ui.Button({
        name: 'simpleupload',
        cssRules: 'background-position: -380px 0;',
        onclick: function() {
            document.getElementById("pickfiles1").click();
        },
        title: '上传图片',
    });
    return $btn;
});
UE.registerUI('video', function(name) {
    var me = this;
    var $btn = new UE.ui.Button({
        name: 'insertvideo',
        cssRules: 'background-position: -320px -20px;',
        onclick: function() {
            document.getElementById("pickfilesforarticleRadio").click();
        },
        title: '上传视频',
    });
    return $btn;
});
var um = UE.getEditor('container');
$(document).ready(function() {
    var pro_value = $("#pro_txt").html();
    um.ready(function() { //编辑器初始化完成再赋值  
        um.setHeight(400);
        um.setContent(pro_value); //赋值给UEditor  
    });
});
var token_url = $('.token_url').val();
var record_url = $('.record_url').val();

function getTokenMessageForUditor() {
    var url="";
    var qiniupercent="";
    var uploader="btn-uploader1";
    var pickfiles="pickfiles1";
    $.ajax({
        url: token_url,
        type: 'POST',
        data: {},
        cache: false,
        contentType: false,    //不可缺
        processData: false,    //不可缺
        dataType : 'json',
        success: function (data) {
            var obj = data;
            uploaderReadyForUditor(obj.uptoken, url, qiniupercent, uploader, pickfiles);
        }
    });
};

function uploaderReadyForUditor(token, url, qiniupercent, uploader, pickfiles) {
    var uploaderForUditor = Qiniu.uploader({
        runtimes: 'html5,flash,html4',
        browse_button: pickfiles,     //上传按钮的ID
        container: uploader,      //上传按钮的上级元素ID
        drop_element: uploader,
        max_file_size: '1mb',         //最大文件限制
        flash_swf_url: '@{/assets/global/qiniu/Moxie.swf}',
        dragdrop: true,
        get_new_uptoken: false,
        chunk_size: '4mb',              //分块大小
        //uptoken_url: '',              //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
        uptoken: token,                 //若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
        // save_key: true,              // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
        domain: qiniuConfig.returnUrl(),   //自己的七牛云存储空间域名
        multi_selection: true,         //是否允许同时选择多文件
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

                $.ajax({   //记录上传的每一张图片
                    url: record_url,
                    type: 'POST',
                    data: {
                        'imgUrl': sourceLink,
                        'controller': 'article'
                    },
                    cache: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.code === '1') {
                            var $item = $('<input type="hidden" name="detailImgIds[]" value="'+data.image_id+'">');
                            $('.form-actions').before($item);
                        }
                    }
                });

                var $item = $("<div class='edui-image-item'><div class='edui-image-close'></div></div>").append($("<img src='" + sourceLink + "' class='edui-image-pic edui-image-width img-responsive'  style='width:100%;' />"));

                UE.getEditor('container').focus();
                UE.getEditor('container').execCommand('inserthtml', "<img src='" + sourceLink + "' class='edui-image-pic edui-image-width img-responsive' style='width:100%;' />");

            },
            'Error': function (up, err, errTip) {
                alert(errTip);
            },
        }
    });
    uploaderForUditor.start();
}

function getVideoTokenMessageForUditor() {
    var url="";
    var qiniupercent="";
    var uploader="btn-uploader1";
    var pickfiles="pickfilesforarticleRadio";
    $.ajax({
        url: token_url,
        type: 'POST',
        data: {},
        cache: false,
        contentType: false,    //不可缺
        processData: false,    //不可缺
        dataType : 'json',
        success: function (data) {
            var obj = data;
            uploaderVideoReadyForUditor(obj.uptoken,qiniupercent,pickfiles);
          }
    });
};

function uploaderVideoReadyForUditor(token,qiniupercent, pickfiles) {
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

                $.ajax({   //记录上传的每一张图片
                    url: record_url,
                    type: 'POST',
                    data: {
                        'imgUrl': sourceLink,
                        'controller': 'article'
                    },
                    cache: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.code === '1') {
                            var $item = $('<input type="hidden" name="detailImgIds[]" value="'+data.image_id+'">');
                            $('.form-actions').before($item);
                        }
                    }
                });
                
                $('#' + pickfiles + ' [name="img_qiniu_url"]').attr('src', sourceLink);
                $('.success_help').html("上传成功！");
                $(".success_help").attr("style", "color:green;");
                setTimeout(function () {
                    $('.success_help').html("");
                }, 8000);//5秒后执行该方法
                if (pickfiles == "pickfilesforarticleRadio") {
                    UE.getEditor('container').focus();
                    //单视频不加Your browser does not support the video tag.有问题
                    UE.getEditor('container').execCommand('inserthtml', "<video controls='controls' preload='auto'  class='edui-image-pic edui-image-width img-responsive' style='width:100%;'  src='" + sourceLink + "'/> Your browser does not support the video tag.</video>");
                }

            },
            'Error': function (up, err, errTip) {
                if ('文件验证失败。请稍后重试。' == errTip) {
                    errTip = "请上传mp4格式";
                }
                alert(errTip);
            },
        }
    });
    uploader.start();
}

$(function() {
    getTokenMessageForUditor();
    getVideoTokenMessageForUditor();
});