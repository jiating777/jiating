
var Member = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();
    var checkNum_url = $('.checkNum_url').val();

    var initTable = function() {
        var table = $('#data-table');

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: datalist_url,
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatar",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                        }
                    },
                    {"data": "name"},
                    {"data": "gender"},
                    {"data": "identityNumber"},
                    {"data": "mobile"},
                    {
                        "data": "isPoverty",
                        "render": function(data, type, row, meta) {
                            return data == 1 ? '贫困户' : '-';
                        }
                    },
                    {
                        "data": "isParty",
                        "render": function(data, type, row, meta) {
                            return data == 1 ? '党员' : '-';
                        }
                    },
                    {
                        "data": "shenheStatus",
                        "render": function(data, type, row, meta) {
                            return data == 1 ? '村民认证' : '后台添加';
                        }
                    },
                    {
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>';
                            if(row.id == ADMININFO.memberId){
                                html += '<button type="button" class="btn btn-danger" disabled="disabled">删除</button>';
                            }else{
                                html += '<button type="button" class="btn btn-danger delete-btn">删除</button>';
                            }

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
                    console.log(data);
                    if(search){
                        var arr = $.parseJSON(search);
                        for(var key in arr){
                            $("#searchForm input[name='"+key+"']").val(arr[key]);
                            $("#searchForm select[name='"+key+"']").val(arr[key]);
                        }
                    }
                },
                "order": [
                    [1, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 2, 4, 5, 6, 7, 8]
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
            var ispoverty = $("input[name='isPoverty']:checked").val();
            if(ispoverty == 1) {
                $('.poverty').removeClass('hide');
            }

            var isparty = $("input[name='isParty']:checked").val();
            if(isparty == 1) {
                $('.party').removeClass('hide');
                var oId = $('.selectO').val();
                getOrganization(oId,'get',true);
            }
            getOrganization(1,'get');
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

    $(".ispoverty label input").click(function(){
        var isPoverty = $(this).val();
        if(isPoverty == 1) {
            console.log($('.town').val());
            console.log($('.village').val());
            if($('.town').val() == 0 && $('.village').val() == 0) {
                layer.msg('请先选择乡镇');
                // return;
            }
            $('.poverty').removeClass('hide');
        } else if(isPoverty == 2) {
            $('.poverty').addClass('hide');
        }
    });

    $(".isparty label input").click(function(){
        var isParty = $(this).val();
        if(isParty == 1) {  //检测是否选择了乡镇
            if($('.town').val() == 0 && $('.village').val() == 0) {
                layer.msg('请先选择乡镇');
                // return;
            }
            $('.party').removeClass('hide');
        } else if(isParty == 2) {
            $('.party').addClass('hide');
        }
    });


    return {
        init: function() {
            initTable();

            this.onEvent();
        },

        onEvent : function(){

            // 批量导出
            $('#export_csv').on('click', function(){
                var url = $(this).data('url');
                var cityId = $('#searchForm').find('select[name=cityId]').val();
                var xianId = $('#searchForm').find('select[name=xianId]').val();
                var townId = $('#searchForm').find('select[name=townId]').val();
                var villageId = $('#searchForm').find('select[name=villageId]').val();
                var name = $('#searchForm').find('input[name=name]').val();

                var $form = '<form id="export_form" action="'+url+'" method="post" style="display: none;">';
                $form += '<input name="cityId" value="'+cityId+'" >';
                $form += '<input name="xianId" value="'+xianId+'" >';
                $form += '<input name="townId" value="'+townId+'" >';
                $form += '<input name="villageId" value="'+villageId+'" >';
                $form += '<input name="name" value="'+name+'" >';
                $form += '</form>';
                if($(document).find('#export_form').length){
                    $(document).find('#export_form').remove();
                }
                $(document.body).append($form);
                // 提交表单，实现下载
                $(document).find('#export_form').submit();
            });

            // 表单提交
            $('.save-btn').on('click', function () {
                //var _form = $('#ajax-form');
                var _this = $(this);
                var form = this.form;
                if(form.id.value.trim()=='') {
                    if(form.villageId.value == '0' && form.townId.value == '0') {
                        layer.msg('请选择乡镇');
                        return;
                    }
                }
                if(form.name.value.trim()=='') {
                    layer.msg('姓名不能为空');
                    form.name.focus();
                    return;
                }
                if(form.identityNumber.value.trim()=='') {
                    layer.msg('身份证号不能为空');
                    form.identityNumber.focus();
                    return;
                }
                if(!checkIdentity(form.identityNumber.value)){
                    layer.msg('身份证号格式不正确');
                    form.identityNumber.focus();
                    return;
                }
                var mobile = $('#mobile').val();

                if(mobile.trim() == ''){
                    layer.msg('手机号不能为空');
                    form.mobile.focus();
                    return;
                }
                var mobile_reg = /^1(3|4|5|6|7|8)[0-9]\d{8}$/;
                if(!mobile_reg.test(mobile)){
                    layer.msg('手机号格式不正确');
                    return;
                }
                var identityNumber = $('#identityNumber').val();
                var ori_identityNumber = $('#identityNumber').attr('data-identityNumber');

                if(identityNumber != ori_identityNumber){
                    var url = checkNum_url;
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {'identityNumber': identityNumber},
                        dataType : 'json',
                        success: function (data) {
                            if(data.status == 1){
                                layer.msg('该身份证号已经存在！');
                                return;
                            }
                        }
                    });
                }

                //若选择为贫困户，则检测相关数据
                if(form.isPoverty.value == 1) {
                    if(form.povertyreason.value ==='0') {
                        layer.msg('请选择致贫原因');
                        return;
                    }
                    var price = form.perincome.value;
                    var price_reg = /^(([0-9]+[\.]?[0-9]+)|[1-9])$/;
                    if(!price_reg.test(price)){
                        layer.msg('价格格式不正确！');
                        form.perincome.focus();
                        return;
                    }
                    var price_reg2 = /^\d+(\.\d{1,2})?$/;
                    if(!price_reg2.test(price)){
                        layer.msg('价格小数点后只有两位！');
                        form.perincome.focus();
                        return;
                    }
                    if(form.familymember.value.trim()=='') {
                        layer.msg("请填写家庭人口数");
                        form.familymember.focus();
                        return;
                    }
                    var num_reg = /^\+?[1-9][0-9.]*$/;
                    if(!num_reg.test(form.housearea.value)){
                        layer.msg('请正确填写住房面积');
                        form.housearea.focus();
                        return;
                    }
                    if(!num_reg.test(form.farmlandarea.value)) {
                        layer.msg("请正确填写耕地面积");
                        form.farmlandarea.focus();
                        return;
                    }
                    if(form.aidingId.value=='') {
                        layer.msg("请选择帮扶人!");
                        return;
                    }
                }

                if(form.isParty.value == 1) {  //若选择为党员，检测相关数据
                    if(form.organizationId.value === '0') {
                        layer.msg('请选择组织');
                        return;
                    }
                    if(form.job.value.trim()=='') {
                        layer.msg('请填写职务名称');
                        form.job.focus();
                        return;
                    }
                    if(form.partyTime.value.trim()=='') {
                        layer.msg('请填写入党时间');
                        return;
                    }
                }

                var _data = $(form).serializeObject();
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
                        window.location.href = data.url; //加载页面数据
                    } else if (data.code === 0 ) {  // 错误
                        _this.prop("disabled", false);
                        layer.msg(data.msg);
                    }
                });
            });
        }
    };

}();

var Resource = function() {

    var upload_element = 'cover-image'; // 上传按钮的上级元素ID
    var upload_btn = 'upload-cover-btn'; // 上传按钮的ID
    var field = $('#' + upload_btn).attr('data-field');
    var token_url = $('.token_url').val();
    var record_url = $('.record_url').val();
    var max_file_size = $('#' + upload_btn).attr('data-max_file_size');
    var extensions = $('#' + upload_btn).attr('data-extensions');
    var multi = $('#' + upload_btn).attr('data-multi');

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

//'use strict';

$(function() {
    Member.init();
    Resource.init();
 
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
                        console.log('减少到:'+imgcount);
                    }
                } else{
                    layer.msg('删除失败');
                }
            }
        });
    });

});

function checkIdentity(identity){
    var reg = /^[1-9]{1}[0-9]{14}$|^[1-9]{1}[0-9]{16}([0-9]|[xX])$/;
    if(reg.test(identity)){
        return true;
    }else{
        return false;
    }
}

$('.village').on('change',function () {
    getOrganization($(this),'change');
});

function getList(e,type) {
    $('.'+type).html('');
    var name = '';
    if(type == 'xian') {
        name = '所有区县';
        $('.town').html('');
        $('.town').append("<option value='0'>所有乡镇</option>");
        $('.village').html('');
        $('.village').append("<option value='0'>所有村</option>");
    } else if(type=='town') {
        name = '所有乡镇';
        $('.village').html('');
        $('.village').append("<option value='0'>所有村</option>");
    }

    $('.organization').html('');
    $('.organization').append("<option value='0'>选择组织</option>");
    if(e.val() != 0) {
        $.ajax({
            url : areaurl,
            type : 'post',
            dataType : 'json',
            contentType : "application/json; charset=utf-8",
            data : JSON.stringify({'id':e.val()}),
        }).done(function(data) {
            var $item = "<option value='0'>"+name+"</option>";
            for (var i in data){
                $item += "<option  value='"+i+"' '>"+data[i]+"</option>";
            }
            $('.'+type).append($item);
        });
    } else {
        $('.'+type).append("<option value='0'>"+name+"</option>");
    }

}

function getVillage(e){
    $('.village').html('');
    $('.organization').html('');
    $('.organization').append("<option value='0'>选择组织</option>");
    if(e.val() != 0) {
        $.ajax({
            url : villageurl,
            type : 'post',
            dataType : 'json',
            contentType : "application/json; charset=utf-8",
            data : JSON.stringify({'id':e.val()}),
        }).done(function(data) {
            var $item = "<option value='0'>所有村</option>";
            for (var i in data){
                $item += "<option value='"+i+"' '>"+data[i]+"</option>";
            }
            $('.village').append($item);
            getOrganization(1,'get');
        });
    } else {
        $('.village').append("<option value='0'>所有村</option>");
    }
}

function getOrganization(e,type,sel = false) {
    var townId = $('.town').val() == undefined ? '0' : $('.town').val();
    if(type == 'change') {
        var villageId = e.val();
    } else {
        var villageId = $('.village').val() == undefined ? '0' : $('.village').val();
    }
    console.log(villageId);
    var param = {};
    if(villageId == 0) {
        param = {'townId':townId};
    } else {
        param = {'villageId':villageId};
    }
    $('.organization').html('');
    $.ajax({
        url : organizationUrl,
        type : 'post',
        dataType : 'json',
        contentType : "application/json; charset=utf-8",
        data : JSON.stringify(param),
    }).done(function(data) {
        var $item = "<option value='0'>选择组织</option>";
        for (var i in data){
            $item += "<option  value='"+i+"' ";
            if(i == e) {
                $item += ' selected ';
            }
            $item += " '>"+data[i]+"</option>";
        }
        $('.organization').append($item);
    });
}


$('.add-btn').on('click', function(){    //选择帮扶人
    var townId = $('.town').val();
    var villageId = $('.village').val();
    if(villageId == 0 && townId == 0) {
        layer.msg('请先选择乡村');
        return;
    }
    var param = {};
    if(villageId != 0 ) {
        param = {'villageId':villageId};
    } else if(townId != 0 ) {
        param = {'townId':townId};
    }

    var title = $(this).attr('data-title');
    var content = '<div class="col-md-9"><div class="form-inline"><form id="searchForm" class="relative row"><input type="text" class="form-control" name="title" placeholder="姓名" maxlength="44"><button type="button" id="doQuery" class="btn btn-success" title="筛选"> <i class="fa fa-search"></i> 筛选</button></form></div></div>'+
    '<table class="table" id="memberAll"><thead><tr><th> 姓名 </th><th> 性别 </th><th> 组织 </th><th> 职务 </th><th> 操作 </th></tr></thead></table>';
    var options = {
        'width' : '1000px',
    };
    open_modal(title, content, options);
    var table = $('#memberAll');

    table.dataTable({
        "processing": true, // 开启服务器模式
        "ordering": false, // 禁止排序
        serverSide: true,
        destroy:false,
        "ajax": {
            url:partylist,
            data:param
        },
        "autoWidth": false,
        "columns": [
            {"width": "20%","data": "name"},
            {"width": "20%","data": "gender"},
            {"width": "20%","data": "OrgName"},
            {"width": "20%","data": "job"},
            {
                "width": "20%",
                "data": "null",
                "render": function(data, type, row, meta){
                    return "<button type=\"button\" class=\"btn btn_select_role \">选取<input type=\"radio\" style=\"display:none\" name=\"memberId\" value=\""+row.id+"\" /></button>";
                }
            }
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
        "pageLength": 7,
        "columnDefs": [{
            "orderable": false,
        }],
        "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
            "<t>" +
            "<'table_b relative'<'col-md-5'i><'col-md-7'>p>",
        "fnCreatedRow": function(nRow, aData, iDataIndex){
            if(aData['id'] == $('input[name="aidingId"]').val()) {
                $(nRow).find(".btn_select_role").attr('style','background:#5cb85c');
                $(nRow).find(".btn_select_role").find('input').prop('checked',true);
            }
        },
        "fnInitComplete": function() {}
    });
    //选中
    table.on('click', '.btn_select_role', function(event) {
        var oData_arr = table.DataTable().rows($(this).parents("tr")).data(); // 操作行对象
        var id=oData_arr[0].id;  //member表ID
        var selecttxt = oData_arr[0].name;  //姓名

        $('input[name="aidingId"]').val(id);
        $(".poverty .add-btn").html(selecttxt);
        $(this).parents(".modal").modal('hide');
    });


    $('#doQuery').click(function(event){  //按名称搜索
        var params = {'name':$("#searchForm input[name='title']").val()};
        $('#memberAll').DataTable().search(JSON.stringify(params)).draw();
    });
});

