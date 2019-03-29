var Product = function() {


    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();

    // 七牛
    var upload_element = 'cover-image'; // 上传按钮的上级元素ID
    var upload_btn = 'upload-cover-btn'; // 上传按钮的ID
    var field = $('#' + upload_btn).attr('data-field');
    var token_url = $('.token_url').val();
    var record_url = $('.record_url').val();
    var max_file_size = $('#' + upload_btn).attr('data-max_file_size');
    var extensions = $('#' + upload_btn).attr('data-extensions');
    var multi = $('#' + upload_btn).attr('data-multi');


    var init_datepicker = function () {
        if($('#preDeliverDate').length > 0){
            $('#preDeliverDate').datepicker({
                language:"cn",
                format: 'yyyy-mm-dd',
                todayBtn : "linked",
                autoclose : true,
                todayHighlight : true,
                startDate : new Date()
            });
        }
        // 开始时间
        if($('#startTime').length > 0){
            var startDate = new Date();
            $('#startTime').datetimepicker({
                language: 'zh-CN',
                format: 'yyyy-mm-dd hh:ii',
                startDate: startDate,
                //endDate: endDate,
                autoclose: true
            }).on('changeDate',function(e){
                var startTime = e.date;
                $('#endTime').datetimepicker('setStartDate', startTime);
            });
        }
        // 结束时间
        if($('#endTime').length > 0){
            var startDate = new Date();
            $('#endTime').datetimepicker({
                language: 'zh-CN',
                format: 'yyyy-mm-dd hh:ii',
                startDate: startDate,
                //endDate: endDate,
                autoclose: true
            }).on('changeDate',function(e){
                var endTime = e.date;
                $('#startTime').datetimepicker('setEndDate', endTime);
            });
        }
    };

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
                        "data": "imgUrl",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                        }
                    },
                    {"data": "title"},
                    {
                        "data": "price",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            return data / 100 + ' 元 /' + row.unit;
                        }
                    },
                    {
                        "data": "minBuyNum",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            return data + ' ' + row.unit;
                        }
                    },
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>';
                            html += '<button type="button" class="btn btn-danger delete-btn">删除</button>';

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
                    [1, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 3, 4]
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

                    var type = $('input[name=type]').val();
                    if(type){
                        $("#type").find("option:contains('"+type+"')").attr("selected", true);
                        get_varietie($("#type"));
                    }
                }
            });
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


    return {
        init: function() {
            init_datepicker();
            initTable();
            initUploader();
            //get_category();

            this.onEvent();
        },

        onEvent: function() {

            if($('#type').length > 0){
                get_category();
            }

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
                        } else{
                            layer.msg('删除失败');
                        }
                    }
                });
            });

            // 添加规格
            $('.add-spec').on('click', function(){
                var html = '';
                html += '<div class="list-group">';
                html += '<div class="row">';
                html += '<div class="col-xs-4">';
                html += '<input type="text" class="form-control spec_format" name="spec_format[]" placeholder="规格名称 如：单个重" value="">';
                html += '</div>';
                html += '<div class="col-xs-4">';
                html += '<input type="text" class="form-control spec_value" name="spec_value[]" placeholder="规格说明 如：4-5两" value="">';
                html += '</div>';
                html += '<div class="col-xs-2">';
                html += '<label class="control-label del-spec" style="color: red; cursor: pointer;">删除</label>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#specJson').append(html);
            });

            // 删除规格
            $(document).find('#specJson').on('click', '.del-spec', function(){
                $(this).parents('.list-group').remove();
            });

            $('#form-submit').on('click', function(){  //提交数据
                var form = this.form;
                if(!form.id.value) {
                    if(form.townId.value == 0) {
                        layer.msg('请选择所在地');
                        return;
                    }
                }

                if(form.title.value.trim() == '') {
                    layer.msg('请输入产品名称');
                    form.title.focus();
                    return;
                }
                if(form.title.value.length > 30){
                    layer.msg('产品名称最多为30个字符');
                    return false;
                }
                if(form.varietieId.value.trim() == '') {
                    layer.msg('请选择品种');
                    return;
                }
                if(form.coverImg.value.trim() == ''){
                    layer.msg('请上传封面图');
                    return false;
                }
                if($('#cover-image').find('img.exist-image').length <= 0){
                    layer.msg('请上传产品图');
                    return false;
                }
                var price = form.price.value;
                if(price.trim() == ''){
                    layer.msg('产品售价不能为空');
                    return false;
                }
                var price_reg = /^(([0-9]+[\.]?[0-9]+)|[1-9])$/;
                if(!price_reg.test(price)){
                    layer.msg('产品售价格式不正确');
                    return false;
                }
                var price_reg2 = /^\d+(\.\d{1,2})?$/;
                if(!price_reg2.test(price)){
                    layer.msg('产品售价小数点后只有两位');
                    return false;
                }
                if(form.minBuyNum.value.trim() == ''){
                    layer.msg('起购数量不能为空');
                    return false;
                }
                var num_reg = /^\+?[1-9][0-9]*$/;
                if(!num_reg.test(form.minBuyNum.value)){
                    layer.msg('数量格式不正确');
                    return false;
                }
                if(form.minBuyNum.value.length > 12){
                    layer.msg('起购数量最大长度为11位');
                    return false;
                }

                if(form.preStartTime.value.trim() == ''){
                    layer.msg('请输入预售期开始时间');
                    form.preStartTime.focus();
                    return false;
                }
                if(form.preEndTime.value.trim() == ''){
                    layer.msg('请输入预售期结束时间');
                    form.preEndTime.focus();
                    return false;
                }
                var startTime = form.preStartTime.value;
                var endTime = form.preEndTime.value;
                if(endTime <= startTime){
                    layer.msg('预售期结束时间应大于开始时间');
                    form.preEndTime.focus();
                    return false;
                }
                if(form.preDeliverDate.value.trim() == ''){
                    layer.msg('请输入预计发货日期');
                    form.preDeliverDate.focus();
                    return false;
                }

                var is_spec_value = true;
                $(document).find('.spec_value').each(function () {
                    var spec_value = $(this).val();
                    var value_reg = /^\+?[1-9][0-9]*$/;

                    /*if(spec_value.trim() == ''){
                        layer.msg('规格数量不能为空');
                        return false;
                    }*/
                    /*if(!value_reg.test(spec_value) || spec_value.length > 12){
                        is_spec_value = false;
                        layer.msg('规格数量需为12位以内的正整数');
                        $(this).focus();
                        return false;
                    }*/
                });
                var _data = $(form).serializeObject();
                $("#form-submit").attr("disabled","disabled");

                $.ajax({
                    url : save_url,
                    type : 'post',
                    //data : JSON.stringify(_data),
                    data : _data,
                    dataType : 'json',
                    //contentType : "application/json; charset=utf-8",
                }).done(function(data) {
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        window.location.href= redirect_url;
                    } else if (data.code === 0 ) {
                        $("#form-submit").removeAttr("disabled");
                        layer.msg(data.msg);
                    }
                });
            });
        }

    };

}();

jQuery(document).ready(function() {
    Product.init();

});


var getcategory_url = $('.getcategory_url').val();

// 选择分类
$('#type').on('change', function () {
    get_varietie($(this));
    $('input[name=type]').val($(this).find('option:selected').text());
});

// 选择品种
$('#varietie').on('change', function () {
    $('input[name=varietie]').val($(this).find('option:selected').text());
});

/**
 * 获取分类
 */
function get_category() {
    $('#varietie').html('<option value="0">选择品种</option>');

    var url = getcategory_url + '?p_id=0';
    $.ajax({
        type : "GET",
        url  : url,
        //dataType:"json",
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return false;
        },
        success: function(data) {
            var options = '<option value="0">选择分类</option>'+ data;
            $('#type').empty().html(options);

            /*var type = $('input[name=type]').val();
            if(type){
                $("#type").val(type);
                get_varietie($("#type"));
            }*/
        }
    });
    return false;
}

/**
 * 获取品种
 * @param select对象
 */
function get_varietie(obj) {
    var parent_id = $(obj).val();
    if(!parent_id > 0){
        return false;
    }

    var url = getcategory_url + '?p_id=' + parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        //dataType:"json",
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return false;
        },
        success: function(data) {
            var options = '<option value="0">选择品种</option>'+ data;
            $('#varietie').empty().html(options);

            var varietie = $('input[name=varietie]').val();
            if(varietie){
                //$("#varietie").val(varietie);
                $("#varietie").find("option:contains('"+varietie+"')").attr("selected", true);
            }
        }
    });
    return false;
}