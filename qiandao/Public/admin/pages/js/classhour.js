var Classhour = function() {


    // 七牛
    var upload_element = 'upload-image'; // 上传按钮的上级元素ID
    var upload_btn = 'upload-btn'; // 上传按钮的ID
    var field = $('#' + upload_btn).attr('data-field');
    var token_url = $('.token_url').val();
    var record_url = $('.record_url').val();
    var max_file_size = $('#' + upload_btn).attr('data-max_file_size');
    var extensions = $('#' + upload_btn).attr('data-extensions');
    var multi = $('#' + upload_btn).attr('data-multi');

    // 课时
    var classhourDatalist_url = $('.classhourDatalist_url').val();
    var classhourDelete_url = $('.classhourDelete_url').val();
    var save_url = $('.save_url').val();


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
                uploaderReady(data.uptoken, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi);
            }
        });
    };

    var uploaderReady = function (token, url, qiniupercent, uploader, pickfiles, max_file_size, extensions, multi) {
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
                        var nativeFile = file.getNative();
                        var size = nativeFile.size;
                        var type = nativeFile.type;
                        //console.log(nativeFile);
                        if(type.indexOf('mp4') >= 0 || type.indexOf('mp3') >= 0){
                            if(size > 1204 * 1024 * 10){
                                layer.msg('mp4、mp3 格式最大为 10M');
                                up.removeFile(file);
                                return;
                            }
                        } else {
                            if(size > 1024 * 1024){
                                layer.msg('最大为 1M');
                                up.removeFile(file);
                                return;
                            }
                        }

                        layer.load(2, {shade: [0.8,'#000000']}); // 打开loading
                    });
                },
                'BeforeUpload': function (up, file) {
                    // 每个文件上传前，处理相关的事情
                    //layer.load(2, {shade: [0.8,'#000000']}); // 打开loading
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
                                // 删除原文件
                                var img_id = $('#' + upload_btn).find('input[name=imgId]').val();
                                var img_url = $('input[name=fileUrl]').val();
                                delOriginalImg(img_id, img_url);

                                $('input[name=fileUrl]').parent().show();
                                $('input[name=fileUrl]').val(sourceLink);
                                if($('#' + upload_btn).find('input[name=imgId]').val()) {  //已有图片
                                    $('#' + upload_btn).find('input[name=imgId]').val(data.image_id);
                                } else {
                                    $('#' + upload_btn).append('<input type="hidden" name="imgId" value="'+data.image_id+'">');
                                }
                                // imgcount++;
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

    // 课时
    var initClasshourTable = function() {
        var table = $('#classhour-datatable');
        var classId = $('input[name=classId]').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: classhourDatalist_url,
                    data: {'id':classId}
                },
                "autoWidth": false,
                "columns": [
                    {"data": "name"},
                    {"data": "sorting"},
                    {
                        "data": "createDate",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            return data;
                        }
                    },
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<button type="button" class="btn btn-danger delete-btn">删除</button>';

                            return html;
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
                    [1, "asc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 3]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
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
                    var url = classhourDelete_url;
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


    return {
        init: function() {
            initUploader();
            initClasshourTable();

            this.onEvent();
        },

        onEvent: function() {

            // 课时 保存
            $('.classhour_save-btn').on('click', function(){
                var form = this.form;
                var _this = $(this);

                if(form.name.value.trim() == '') {
                    layer.msg('请输入课时名称');
                    form.name.focus();
                    return;
                }
                if(form.name.value.length > 40){
                    layer.msg('课时名称最多为40个字符');
                    form.name.focus();
                    return false;
                }
                if(form.fileUrl.value.trim() == ''){
                    layer.msg('请上传课时文件');
                    return false;
                }

                var _data = $(form).serializeObject();
                _data.fileUrl = form.fileUrl.value;
                _this.prop("disabled", true);

                $.ajax({
                    url : save_url,
                    type : 'post',
                    dataType : 'json',
                    contentType : "application/json; charset=utf-8",
                    data : JSON.stringify(_data),
                }).done(function(data) {
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        window.location.reload();
                    } else if (data.code === 0 ) {
                        _this.prop("disabled", false);
                        layer.msg(data.msg);
                    }
                });
            });
        }

    };

}();

jQuery(document).ready(function() {
    Classhour.init();

});