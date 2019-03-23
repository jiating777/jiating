var UploadFile = function() {

    var upload_element = 'upload-image'; // 上传按钮的上级元素ID
    var upload_btn = 'upload-btn'; // 上传按钮的ID
    var field = $('#' + upload_btn).attr('data-field');
    var token_url = $('.token_url').val();
    var record_url = $('.record_url').val();
    var max_file_size = $('#' + upload_btn).attr('data-max_file_size');
    var extensions = $('#' + upload_btn).attr('data-extensions');
    var multi = $('#' + upload_btn).attr('data-multi');
    var isAjax = $('#' + upload_btn).attr('data-ajax');

    /**
     * 七牛初始化
     */
    var initUploader = function () {
        var url = "";
        var qiniupercent = "";
        var uploader = upload_element;
        var pickfiles = upload_btn;

        if(extensions == '' || extensions == undefined){
            extensions = 'jpg,jpeg,gif,png';
        }
        if(multi == 'false'){
            multi = false;
            if(max_file_size == '' || max_file_size == undefined){
                max_file_size = '500kb';
            }
        }else{
            multi = true;
            if(max_file_size == '' || max_file_size == undefined){
                max_file_size = '1MB';
            }
        }
        if(isAjax == 'true'){
            isAjax = true;
        }else{
            isAjax = false;
        }

        $.ajax({
            url: token_url,
            type: 'POST',
            data: {},
            cache: false,
            contentType: false,    //不可缺
            processData: false,    //不可缺
            dataType : 'json',
            success: function (data) {
                uploaderReadyForImg(data.uptoken, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi);
            }
        });
    };

    var uploaderReadyForImg = function (token, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi) {
        var uploaderForUditor = Qiniu.uploader({
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
            unique_names :true,             //自动生成文件名,如果值为false则保留原文件名上传
            init: {
                'FilesAdded': function (up, files) {
                    plupload.each(files, function(file) {
                        // 文件添加进队列后，处理相关的事情
                        if(typeof total != 'undefined') {
                            if(imgcount >= total) {
                                layer.msg('最多上传'+total+'张');
                                up.removeFile(file);
                                return;
                            }
                        }
                    });
                },
                'BeforeUpload': function (up, file) {
                    // 每个文件上传前，处理相关的事情
                    layer.load(2, {shade: [0.8,'#000000']}); // 打开loading
                },
                'UploadProgress': function (up, file) {
                    //文件上传时，处理相关的事情

                    //上传进度 class="layui-btn" type="button"

                    //console.log(file.percent + "%");
                },
                'UploadComplete': function () {
                    //do something
                },
                'FileUploaded': function (up, file, info) {
                    //每个文件上传成功后,处理相关的事情,并记录到数据库
                    //其中 info 是文件上传成功后，服务端返回的json，形式如
                    //{
                    //  "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                    //  "key": "gogopher.jpg"
                    //}
                    var domain = up.getOption('domain');
                    var res = eval('(' + info.response + ')');
                    var sourceLink = domain + res.key;//获取上传文件的链接地址
                    //console.log(sourceLink);
                    $.ajax({
                        url: record_url,
                        type: 'POST',
                        data: {
                            'imgUrl': sourceLink
                        },
                        cache: false,
                        dataType: 'json',
                        success: function (data) {
                            layer.closeAll('loading'); // 关闭loading
                            if (data.code === '1') {
                                if(multi) {  // 多图
                                    if(isAjax) {  //ajax提交
                                        var $item = '';
                                        $item += '<span class="multi-image">';
                                        $item += '<input type="hidden" name="imgIds" value="'+data.image_id+'">';
                                        $item += '<input type="hidden" name="'+field+'" value="'+sourceLink+'">';
                                        $item += '<img class="exist-image" src="'+sourceLink+'" alt="" />';
                                        $item += '<img class="del" src="/public/static/pages/image/del.png" alt="">';
                                        $item += '</span>';

                                        $('#' + upload_btn).before($item);
                                    } else {                                    
                                        var $item = '';
                                        $item += '<span class="multi-image">';
                                        $item += '<input type="hidden" name="imgIds" value="'+data.image_id+'">';
                                        $item += '<input type="hidden" name="'+field+'[]" value="'+sourceLink+'">';
                                        $item += '<img class="exist-image" src="'+sourceLink+'" alt="" />';
                                        $item += '<img class="del" src="/public/static/pages/image/del.png" alt="">';
                                        $item += '</span>';

                                        $('#' + upload_btn).before($item);
                                    }
                                } else {
                                    // 单图
                                    var img_id = $('#' + upload_btn).find('input[name=imgId]').val();
                                    //if(img_id){
                                        // 删除原图
                                        var img_url = $('#' + upload_btn).find('img').attr('src');
                                        delOriginalImg(img_id, img_url);
                                    //}

                                    $('#' + upload_btn).find('img').attr('src', sourceLink);
                                    $('#' + upload_btn).find('input').val(sourceLink);
                                    if($('#' + upload_btn).find('input[name=imgId]').val()) {  //已有图片
                                        $('#' + upload_btn).find('input[name=imgId]').val(data.image_id);
                                    } else {
                                        $('#' + upload_btn).append('<input type="hidden" name="imgId" value="'+data.image_id+'">');
                                    }
                                }
                                if(typeof total != 'undefined') {
                                    imgcount++;
                                    console.log('增加到：'+imgcount);
                                }
                            }
                        }
                    });

                },
                'Error': function (up, err, errTip) {
                    layer.closeAll('loading'); // 关闭loading
                    alert(errTip);
                },
            }
        });
    };

    // 删除原图
    var delOriginalImg = function (img_id, img_url) {
        var delimage_url = $('.delimage_url').val();

        $.ajax({
            url: delimage_url,
            type: 'POST',
            data: {
                'imgUrl' : img_url,
                'id' : img_id
            },
            cache: false,
            dataType : 'json',
            success: function (data) {
                //
            }
        });
    };

    return {
        init: function() {
            initUploader();
        }

    };

}();

jQuery(document).ready(function() {
    UploadFile.init();

    /**
     * 删除图片
     * 删除记录数据及七牛空间中的图片
     */
    $(document).find('#upload-image').on('click', '.del', function(){
        var img_id = $(this).parent().find('input').val(); // 图片ID
        var img_url = $(this).parent().find('.exist-image').attr('src'); // 图片地址
        var element = $(this).parent(); // 要删除元素
        var delimage_url = $('.delimage_url').val();

        $.ajax({
            url: delimage_url,
            type: 'POST',
            data: {
                'imgUrl' : img_url,
                'id' : img_id
            },
            cache: false,
            dataType : 'json',
            success: function (data) {
                if(data.code === '1') {
                    element.remove();
                    layer.msg('删除成功');
                    if(typeof total != 'undefined') {
                        imgcount--;
                        console.log('减少到:'+imgcount);
                    }
                } else{
                    layer.msg('删除失败');
                }
            }
        });
    });

});