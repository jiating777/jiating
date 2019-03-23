var Project = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var detail_url = $('.detail_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();
    var checkNum_url = $('.checkNum_url').val();

    var isLoadDetailTable = false;  //进度详情

    // 七牛
    var upload_element = 'cover-image'; // 上传按钮的上级元素ID
    var upload_btn = 'upload-cover-btn'; // 上传按钮的ID
    var field = $('#' + upload_btn).attr('data-field');
    var token_url = $('.token_url').val();
    var record_url = $('.record_url').val();
    var max_file_size = $('#' + upload_btn).attr('data-max_file_size');
    var extensions = $('#' + upload_btn).attr('data-extensions');
    var multi = $('#' + upload_btn).attr('data-multi');

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
                    {
                        "data": "type",
                        "render": function(data, type, row, meta) {
                            return data == 1 ? '捐物品' : '捐钱';
                        }
                    },
                    {"data": "name"},
                    {
                        "data": "status",
                        "render": function(data, type, row, meta) {
                            return data == 1 ? '进行中' : '已完成';
                        }
                    },
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
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 6]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                    var city = $('input[name=city]').val();
                    if(city){
                        //$("#city").find("option:contains('"+city+"')").attr("selected", true);
                        get_xian($("#city"));
                    }
                }
            });
        } else {
            var istype = $("input[name='type']").val();
            if(istype == 1) {
                $('.type1').removeClass('hide');
                $('.type2').addClass('hide');
            } else {
                $('.type1').addClass('hide');
                $('.type2').removeClass('hide');
            }
            $(".istype label input").click(function(){
                var type = $(this).val();
                if(type == 1) {
                    $('.type1').removeClass('hide');
                    $('.type2').addClass('hide');
                } else if(type == 2) {
                    $('.type1').addClass('hide');
                    $('.type2').removeClass('hide');
                }
            });

            if($('#note').val() != undefined) {
                $('#text-count').text($('#note').val().length);   //初始化已输入字数

                $('#note').keyup(function() {
                    var len=this.value.length;
                    $('#text-count').text(len);
                });
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
                            if(data.status == 1){
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

    var DetailTable = function() {  //详情进展
        var table = $('#detail_table');
        var projectId = $('input[name=projectId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: detailList_url,
                    data: {
                        'projectId' : projectId
                    }
                },
                "autoWidth": false,
                "columns": [
                    {"data": "donateName"},
                    {
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            if(row.donateNum) {
                                return '捐'+row.donateContent+row.donateNum+'件';
                            } else {
                                return '捐款'+row.donateMoney/100+'元';
                            }
                        }
                    },
                    {
                        "data": "createDate",
                        "render": function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<button type="button" class="btn btn-success btn-edit">编辑</button>' +
                            '<button type="button" class="btn btn-danger delete-btn">删除</button>';
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

        // 编辑
        table.on('click', '.btn-edit', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);
            var data = {'id':id};
            $.ajax({
                url: detailEdit_url,
                type: 'POST',
                data: data,
                dataType : 'json',
                success: function (data) {
                    $('#view-modal').modal('show');
                    $('#view-modal .cover-imag .multi-image').remove();
                    $('#view-modal').find('input[name="oldNum"]').remove();
                    $('#view-modal').find('input[name="oldMoney"]').remove();
                    $('#view-modal').find('input[name="id"]').val(data.id);
                    $('#view-modal').find('input[name="donateName"]').val(data.donateName);
                    $('#view-modal').find('input[name="donateContent"]').val(data.donateContent);
                    $('#view-modal').find('input[name="donateNum"]').val(data.donateNum);
                    $('#project-form').append('<input type="hidden" name="oldNum" value="'+data.donateNum+'">');
                    $('#project-form').append('<input type="hidden" name="oldMoney" value="'+data.donateMoney+'">');
                    $('#view-modal').find('input[name="donateMoney"]').val(data.donateMoney/100);
                    var $item = "";
                    for (var i = data.image.length - 1; i >= 0; i--) {
                        $item += '<span class="multi-image">'+
                                    '<input type="hidden" name="imgIds" value="'+data.image[i]["id"]+'">'+
                                    '<input type="hidden" name="imgUrl" value="'+data.image[i]["imgUrl"]+'">'+
                                    '<img class="exist-image" src="'+data.image[i]["imgUrl"]+'" alt="">'+
                                    '<img class="del" src="/public/static/pages/image/del.png" alt="">'+
                                '</span>';
                    }
                    $('input[name=imgcount]').val(data.image.length);
                    $('#view-modal .cover-imag').prepend($item);
                }
            });
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
                    var url = detailDelete_url;
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

        isLoadDetailTable = true;
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
    

    return {
        init: function() {
            initTable();
            initUploader();
            this.onEvent();
        },

        onEvent : function(){
            $('.detail-tab').on('click', function(){
                if(!isLoadDetailTable){
                    DetailTable();
                }
            });

            $('.addDetail-btn').on('click', function(){
                $('#view-modal').find('input[name="id"]').val('');
                $('#view-modal').find('input[name="donateName"]').val('');
                $('#view-modal').find('input[name="donateContent"]').val('');
                $('#view-modal').find('input[name="donateMoney"]').val('');
                $('#view-modal').find('input[name="donateNum"]').val('');
                $('#view-modal .cover-imag .multi-image').remove();
                $('#view-modal').find('input[name="oldNum"]').remove();
                $('#view-modal').find('input[name="oldMoney"]').remove();
                $('#view-modal').modal('show');
            });

            $('#form-submit').on('click', function () {
                var _this = $(this);
                var form = this.form;
                if(form.id.value.trim()=='') {
                    if(form.villageId.value == '0' && form.townId.value == '0') {
                        layer.msg('请选择乡镇');
                        return;
                    }
                    if(form.memberId.value.trim()=='') {
                        layer.msg('请选择贫困户');
                        return;
                    }
                }                
                if(form.title.value.trim()=='') {
                    layer.msg('请填写项目标题');
                    form.title.focus();
                    return;
                }
                if(form.typeId.value ==0) {
                    layer.msg('请选择分类');
                    return;
                }

                //捐物
                if(form.type.value == 1) {
                    if(form.productName.value =='') {
                        layer.msg('请填写物品名称');
                        form.productName.focus();
                        return;
                    }
                    if(form.productNum.value =='') {
                        layer.msg('请填写物品数量');
                        form.productNum.focus();
                        return;
                    }
                    if(form.receiveName.value =='') {
                        layer.msg('请填写收件人名称');
                        form.receiveName.focus();
                        return;
                    }
                    if(form.receiveAddress.value =='') {
                        layer.msg('请填写收件人详细地址');
                        form.receiveAddress.focus();
                        return;
                    }
                    if(form.zipCode.value =='') {
                        layer.msg('请填写邮政编码');
                        form.zipCode.focus();
                        return;
                    }
                } else {  //捐钱
                    if(form.money.value.trim() == '') {
                        layer.msg('请填写捐钱金额');
                        form.money.focus();
                        return;
                    }
                    var price = form.money.value;
                    var price_reg = /^(([0-9]+[\.]?[0-9]+)|[1-9])$/;
                    if(!price_reg.test(price)){
                        layer.msg('价格格式不正确！');
                        form.money.focus();
                        return;
                    }
                    var price_reg2 = /^\d+(\.\d{1,2})?$/;
                    if(!price_reg2.test(price)){
                        layer.msg('价格小数点后只有两位！');
                        form.money.focus();
                        return;
                    }
                    if(form.accountName.value.trim() == '') {
                        layer.msg('请填写收款账户姓名');
                        form.accountName.focus();
                        return;
                    }
                    if(form.bankName.value.trim() == '') {
                        layer.msg('请填写收款银行');
                        form.bankName.focus();
                        return;
                    }
                    if(form.bankCode.value.trim() == '') {
                        layer.msg('请填写银行卡片');
                        form.bankCode.focus();
                        return;
                    }
                }

                var _data = $(form).serializeObject();
                _data.imgUrl = _data.coverImg;
                _data.money = _data.money * 100;
                _this.prop("disabled", true);
                $.ajax({
                    url : posturl,
                    type : 'post',
                    dataType : 'json',
                    contentType : "application/json; charset=utf-8",
                    data : JSON.stringify(_data),
                }).done(function(data) {
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        window.location.href = data.url; //加载页面数据
                    } else if (data.code === 0 ) {  // 错误
                        _this.prop("disabled", false);
                        layer.msg(data.msg);
                    }
                });
            });

            $('.saveDetail-btn').on('click',function () {
                var _data = $('#project-form').serializeObject();
                if(_data.donateName.trim()=='') {
                    layer.msg('请填写捐助人姓名');
                    return;
                }
                if($('.type').val() == 1) {  //捐物
                    if(_data.donateContent.trim() == '') {
                        layer.msg('请填写捐助物品名称');
                        return;
                    }
                    if(_data.donateNum.trim() == '') {
                        layer.msg('请填写捐助物品数量');
                        return;
                    }
                } else {  //捐钱
                    if(_data.donateMoney.trim() == '') {
                        layer.msg('请填写捐钱金额');
                        return;
                    }
                    var price = _data.donateMoney;
                    var price_reg = /^(([0-9]+[\.]?[0-9]+)|[1-9])$/;
                    if(!price_reg.test(price)){
                        layer.msg('价格格式不正确！');
                        return;
                    }
                    var price_reg2 = /^\d+(\.\d{1,2})?$/;
                    if(!price_reg2.test(price)){
                        layer.msg('价格小数点后只有两位！');
                        return;
                    }
                }
                _data.donateMoney = _data.donateMoney * 100;
                $.ajax({
                    url : detailSave_url,
                    type : 'post',
                    dataType : 'json',
                    contentType : "application/json; charset=utf-8",
                    data : JSON.stringify(_data),
                }).done(function(data) {
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        $('#view-modal').modal('hide');
                        $('#detail_table').DataTable().draw(false);
                    } else if (data.code === 0 ) {  // 错误
                        layer.msg(data.msg);
                    }
                });
            });
        }
    };

}();

$(function() {
    Project.init();
 
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

$('.add-btn').on('click', function(){   //选择贫困户
    var villageId = $('.village').find('option:selected').val();
    if(!villageId || villageId == 0) {
        layer.msg('请先选择乡村');
        return;
    }
    var title = $(this).attr('data-title');
    var content = '<div class="col-md-9"><div class="form-inline"><form id="searchForm" class="relative row"><input type="text" class="form-control" name="title" placeholder="姓名" maxlength="44"><button type="button" id="doQuery" class="btn btn-success" title="筛选"> <i class="fa fa-search"></i> 筛选</button></form></div></div>'+
    '<table class="table" id="memberAll"><thead><tr><th> 姓名 </th><th> 性别 </th><th> 出生年月 </th><th> 身份证号 </th><th> 操作 </th></tr></thead></table>';
    var options = {
        'width' : '1000px',
    };
    open_modal(title, content, options);
    var table = $('#memberAll');

    table.dataTable({
        "processing": true, // 开启服务器模式
        "ordering": false, // 禁止排序
        serverSide: true,
        "ajax": {
            url:povertymember,
            data : {'villageId':villageId},
        },
        "autoWidth": false,
        "columns": [
            {"width": "20%","data": "name"},
            {
                "width": "20%",
                "data": "gender",
                "render": function(data, type, row, meta) {
                    if(data == 1) {
                        return '男';
                    } else {
                        return '女';
                    }
                }
            },
            {"width": "20%","data": "birthday",},
            {"width": "20%","data": "identityNumber"},
            {"width": "30%","data": "null","defaultContent": "<button type=\"button\" class=\"btn btn_select_role \">选取</button>"}
        ],
        "language":{    
            "lengthMenu": "每页显示 _MENU_ 条记录",
            "emptyTable": "暂无数据记录",
            "info": "显示 _START_ 至 _END_ 条数据，共 _TOTAL_ 条记录！",
            "zeroRecords": "没有搜索到匹配记录",
            "infoEmpty": "",
            "infoFiltered": "",
            "processing": "数据加载中,请稍后...",
            "paginate": {
                "first": "首页",
                "previous": "",
                "next": "",
                "last": "末页"
            }
        },
        "pageLength": 5,
        "columnDefs": [{
            "orderable": false,
        }],
        "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
            "<t>" +
            "<'table_b relative'<'col-md-5'i><'col-md-7'>p>",
        "fnCreatedRow": function(nRow, aData, iDataIndex){   //标识已选数据
            var curentId = $("input[name='memberId']").val();
            if(curentId == aData['id']) {
                $(nRow).find(".btn_select_role").attr('style','background:#5cb85c');
            }
        },
        "fnInitComplete": function() {}
    });
    //选中
    table.on('click', '.btn_select_role', function(event) {
        var oData_arr = table.DataTable().rows($(this).parents("tr")).data(); // 操作行对象
        var id=oData_arr[0].id;  //member表ID
        var selecttxt = oData_arr[0].name;  //姓名
        if(oData_arr[0].aidingId == null || oData_arr[0].aidingId.length == 0) {
            layer.msg('此贫困户暂未有帮扶干部，请选择其他');
            return;
        }
        //判断此贫困户是否有帮扶人，若无，重新选择
        $("#povertywork_form").find('input[name="memberId"]').val(id);
        $("#povertywork_form").find('input[name="aidingId"]').val(oData_arr[0].aidingId);
        $("#povertywork_form .add-btn").html(selecttxt);
        $(this).parents(".modal").modal('hide');
    });

    $('#doQuery').click(function(event){  //按名称搜索
        var title =   $("#searchForm input[name='title']").val();
        console.log(title);
        var param={"name":title};
        $('#memberAll').DataTable().search(JSON.stringify(param)).draw();     
    });
});