var Knowledge = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var detail_url = $('.detail_url').val();
    var redirect_url = $('.redirect_url').val();
    var posturl = $('.save_url').val();

    var commentList_url = $('.commentList_url').val();
    var deleteComment_url = $('.deleteComment_url').val();
    var likeList_url = $('.likeList_url').val();
    var isLoadCommentTable = isLoadLikeTable = false;


    var token_url = $('.token_url').val();
    var record_url = $('.record_url').val();
    // 七牛-多图
    var upload_element = 'cover-image'; // 上传按钮的上级元素ID
    var upload_btn = 'upload-cover-btn'; // 上传按钮的ID
    var field = $('#' + upload_btn).attr('data-field');
    
    var max_file_size = $('#' + upload_btn).attr('data-max_file_size');
    var extensions = $('#' + upload_btn).attr('data-extensions');
    var multi = $('#' + upload_btn).attr('data-multi');

    // 七牛-视频
    var video_element = 'upload-video'; // 上传按钮的上级元素ID
    var video_btn = 'upload-video-btn'; // 上传按钮的ID


    var initTable = function() {
        var table = $('#data-table');

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: datalist_url,
                },
                "autoWidth": true,
                "columns": [
                    {
                        "data": "imgUrl",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 60px;height: 40px;"/></a>';
                        }
                    },
                    {"data": "title"},
                    {"data": "typeId"},
                    {
                        "data": "contentType",
                        "render": function(data, type, row, meta) {
                            return data == 1 ? '图片' : '视频';
                        }
                    },
                    {"data": "sorting"},
                    {"data": "createDate"},
                    {
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>' +
                                '<button type="button" class="btn btn-danger delete-btn">删除</button>' +
                                '<a href="'+detail_url+'?id='+row.id+'" type="button" class="btn btn-info">详情</a>';
                        }
                    }
                ],
                "language": {
                    url: '/public/static/admin/pages/datatable_cn.json'
                },
                "lengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
                "pageLength": 10,
                "scrollX":"",
                "destroy": false,
                "stateSave": true,
                "stateSaveParams": function (settings, data) {
                    var search = data.search.search;
                    if(search){
                        var arr = $.parseJSON(search);
                        for(var key in arr){
                            $("#searchForm input[name='"+key+"']").val(arr[key]);
                            $("#searchForm select[name='"+key+"']").val(arr[key]);
                        }
                    }
                },
                "order": [
                    [5, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 4]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnCreatedRow": function(nRow, aData, iDataIndex){   //若文章类型为notice或toutiao，则不显示图片列
                    if(aData.type == 'notice' || aData.type == 'toutiao') {
                       $(nRow).find('td').eq(0).attr('style','display: none');
                       $(nRow).find('td').eq(2).attr('style','display: none');
                    }
                },
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });
        } else {
            var contenttype = $("input[name='contentType']:checked").val();
            if(contenttype == 1) {  //图片
                $('.image').removeClass('hide');
                $('.video').addClass('hide');
            } else {  //视频
                $('.video').removeClass('hide');
                $('.image').addClass('hide');
            }
        }

        // 筛选
        $('#doSearch').on('click', function(event){
            var param = $('#searchForm').serializeObject();

            tableSearch(table, param);
        });

        // 删除
        table.on('click', '.delete-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);

            var text = '是否要删除这条数据？';
            var title = '请确认';
            var confirmBtn = '确定';
            var cancelBtn = '取消';
            event.preventDefault();

            layer.confirm(
                text,
                {
                    title: title,
                    btn: [confirmBtn, cancelBtn]
                },
                function(index){
                    layer.close(index);
                    var url = delete_url;
                    var data = {'id':id};
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        dataType : 'json',
                        success: function (data) {
                            layer.msg(data.msg);
                            if(data.code == 1){
                                _this.parents('tr').remove();
                            }
                        }
                    });
                }
            );
        });
    };

    var tableSearch = function(table, params) {
        table.DataTable().search(JSON.stringify(params)).draw();
    };

    $(".contenttype label input").click(function(){
        var contenttype = $(this).val();
        if(contenttype == 1) {  //图片
            $('.image').removeClass('hide');
            $('.video').addClass('hide');
        } else if(contenttype == 2) {   //视频
            $('.image').addClass('hide');
            $('.video').removeClass('hide');
        }
    });

    /**
     * 评论 table
     */
    var commentTable = function() {
        var table = $('#comment_table');
        var articleId = $('input[name=articleId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: commentList_url,
                    data: {
                        'articleId' : articleId
                    }
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatarUrl",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>' + ' ' + row.nickName;
                        }
                    },
                    {"data": "content"},
                    {
                        "data": "createDate",
                        "render": function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        "width": "10%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<button type="button" class="btn btn-danger delete-btn">删除</button>';
                        }
                    }
                ],
                "language": {
                    url: '/public/static/admin/pages/datatable_cn.json'
                },
                "lengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
                "pageLength": 10,
                "scrollX":"",
                "destroy": false,
                "stateSave": false,
                "stateSaveParams": function (settings, data) {
                    //
                },
                "columnDefs": [{
                    "orderable": false,
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });
        }

        // 删除
        table.on('click', '.delete-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);

            var text = '是否要删除这条数据？';
            var title = '请确认';
            var confirmBtn = '确定';
            var cancelBtn = '取消';
            event.preventDefault();

            layer.confirm(
                text,
                {
                    title: title,
                    btn: [confirmBtn, cancelBtn]
                },
                function(index){
                    layer.close(index);
                    var url = deleteComment_url;
                    var data = {'id':id};
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        dataType : 'json',
                        success: function (data) {
                            layer.msg(data.msg);
                            if(data.code == 1){
                                _this.parents('tr').remove();
                            }
                        }
                    });
                }
            );
        });

        isLoadCommentTable = true;
    };

    /**
     * 点赞 table
     */
    var likeTable = function() {
        var table = $('#like_table');
        var articleId = $('input[name=articleId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: likeList_url,
                    data: {
                        'articleId' : articleId
                    }
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatarUrl",
                        "render": function(data, type, row, meta) {
                            var html = '<div style="float: left;"><a href="'+data+'" target="_blank"><img src="'+data+'" class="img-circle" style="width: 70px;height: 70px;"/></a></div>';
                            html += '<div style="float: left;">';
                                html += '<p class="text-left">'+row.nickName+'</p>';
                                html += '<p>'+row.createDate+'</p>';
                            html += '</div>';

                            return html;
                        }
                    },
                ],
                "language": {
                    url: '/public/static/admin/pages/datatable_cn.json'
                },
                "lengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
                "pageLength": 10,
                "scrollX":"",
                "destroy": false,
                "stateSave": false,
                "stateSaveParams": function (settings, data) {
                    //
                },
                "columnDefs": [{
                    "orderable": false,
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                },
                "fnDrawCallback": function ( oSettings ) {
                    $(oSettings.nTHead).hide();
                }
            });
        }

        isLoadLikeTable = true;
    };

    /**
     * 七牛初始化
     */
    var initUploader = function () {
        if($('#' + upload_element).length <= 0){
            return ;
        }

        var url = "";
        var qiniupercent = "";
        var uploader = upload_element;
        var pickfiles = upload_btn;
        if(max_file_size == '' || max_file_size == undefined){
            max_file_size = '1MB';
        }
        if(extensions == '' || extensions == undefined){
            extensions = 'jpg,jpeg,gif,png';
        }
        if(multi == 'false'){
            multi = false;
        }else{
            multi = true;
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
                        if(total) {
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
                            //console.log(data);
                            layer.closeAll('loading'); // 关闭loading
                            if (data.code === '1') {
                                if(multi) {
                                    // 多图
                                    var $item = '';
                                    $item += '<span class="multi-image">';
                                    $item += '<input type="hidden" name="imgIds" value="'+data.image_id+'">';
                                    $item += '<input type="hidden" name="'+field+'" value="'+sourceLink+'">';
                                    $item += '<img class="exist-image" src="'+sourceLink+'" alt="" />';
                                    $item += '<img class="del" src="/public/static/pages/image/del.png" alt="">';
                                    $item += '</span>';

                                    $('#' + upload_btn).before($item);
                                } else {
                                    // 单图
                                    // 删除原图
                                    var img_id = $('#' + upload_btn).find('input[name=imgId]').val();
                                    var img_url = $('#' + upload_btn).find('img').attr('src');
                                    delOriginalImg(img_id, img_url);

                                    $('#' + upload_btn).find('img').attr('src', sourceLink);
                                    $('#' + upload_btn).find('input').val(sourceLink);
                                    $('#' + upload_btn).append('<input type="hidden" name="imgId" value="'+data.image_id+'">');
                                }
                                if(total) {
                                    imgcount++;
                                    console.log('增加：'+imgcount);
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
            }
        });
    };

    var initUploaderVideo = function () {
        if($('#' + video_element).length <= 0){
            return ;
        }

        var url = "";
        var qiniupercent = "";
        var uploader = video_element;
        var pickfiles = video_btn;
        var max_file_size = '10MB';
        var extensions = 'mp4';
        var multi = false;

        $.ajax({
            url: token_url,
            type: 'POST',
            data: {},
            cache: false,
            contentType: false,    //不可缺
            processData: false,    //不可缺
            dataType : 'json',
            success: function (data) {
                uploaderReadyForVideo(data.uptoken, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi);
            }
        });
    };

    var uploaderReadyForVideo = function (token, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi) {
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
                                // 删除原图
                                var img_id = $('#' + video_btn).find('input[name=videoId]').val();
                                var img_url = $('#' + video_btn).find('video').attr('src');
                                delOriginalImg(img_id, img_url);

                                $('#' + video_btn).find('video').attr('src', sourceLink);
                                $('#' + video_btn).find('input').val(sourceLink);
                                $('#' + video_btn).append('<input type="hidden" name="videoId" value="'+data.image_id+'">');
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


    return {
        init: function() {
            initTable();
            initUploader();
            initUploaderVideo();

            this.onEvent();
        },

        onEvent : function(){

            // 评论
            $('.comment-tab').on('click', function(){
                if(!isLoadCommentTable){
                    commentTable();
                }
            });

            // 点赞
            $('.like-tab').on('click', function(){
                if(!isLoadLikeTable){
                    likeTable();
                }
            });

            $('#form-submit').on('click', function () {
                var form = this.form;
                if(form.title.value.trim()=='') {
                    layer.msg('请输入标题');
                    form.title.focus();
                    return;
                }
                if(form.typeId.value==='0') {
                    layer.msg("请选择专题");
                    return;
                }
                if(form.coverImg.value=='') {
                    layer.msg("请上传封面图!");
                    return;
                }
                if(form.content.value.trim()=='') {
                    layer.msg("请输入知识概要");
                    form.content.focus();
                    return;
                }
                var _data = $(form).serializeObject();
                _data.imgUrl = _data.coverImg;
                $("#form-submit").attr("disabled","disabled");
                $.ajax({
                    url : posturl,
                    type : 'post',
                    dataType : 'json',
                    contentType : "application/json; charset=utf-8",
                    data : JSON.stringify(_data),
                }).done(function(data) {
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        window.location.href= data.url; //加载页面数据
                    } else if (data.code === 0 ) {  // 错误
                        $("#form-submit").removeAttr("disabled");
                        layer.msg(data.msg);
                    }
                });

            });
        }
    };

}();

$(function() {
    Knowledge.init();
    $(document).find('#cover-image').on('click', '.del', function(){
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
                    if(total) {
                        imgcount--;
                        console.log('减少：'+imgcount);
                    }
                } else{
                    layer.msg('删除失败');
                }
            }
        });
    });
});
