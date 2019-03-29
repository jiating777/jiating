var Operator = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();

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
                    {"data": "loginName"},
                    {"data": "name"},
                    {"data": "job"},
                    {"data": "createDate"},
                    {
                        "width": "15%",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>' +
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
                    "targets": [0, 2]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
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


    return {
        init: function() {
            initTable();

            this.onEvent();
        },

        onEvent : function(){
            $('#form-submit').on('click', function () {
                var form = this.form;                //var _formData = $('#ajax-form').serializeObject();
                if(form.townId.value == 0) {
                    layer.msg('请选择管理范围');
                    return;
                }
                if(form.loginName.value.trim()=='') {
                    layer.msg('请输入管理员账号');
                    form.loginName.focus();
                    return;
                }
                if(!form.id.value) {
                    if(form.pass_one.value.trim()=='') {
                        layer.msg('请输入登录密码');
                        form.pass_one.focus();
                        return;
                    }
                    if(form.pass_tow.value.trim()=='') {
                        layer.msg('请确认密码');
                        form.pass_tow.focus();
                        return;
                    }
                    if(form.pass_tow.value != form.pass_one.value) {
                        layer.msg('两次密码不一致');
                        form.pass_tow.focus();
                        return;
                    }
                }
                if(form.memberId.value.trim()=='') {
                    layer.msg('请选择管理人员');
                    return;
                }
                var _data = $(form).serializeObject();
                if(_data.villageId != 0) {
                    _data.level = 4;
                } else if(_data.townId != 0) {
                    _data.level = 3;
                } else if(_data.xianId != 0) {
                    _data.level = 2;
                } else if(_data.cityId != 0){
                    _data.level = 1;
                }
                
                $("#form-submit").attr("disabled","disabled");
                $.ajax({
                    url : posturl,
                    type : 'post',
                    dataType : 'json',
                    contentType : "application/json; charset=utf-8",
                    data : JSON.stringify(_data),
                }).done(function(data) {
                    console.log(data);
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
    Operator.init();

});

$('.add-btn').on('click', function(){    //选择管理员
    var townId = $('input[name="townId"]').val();
    var villageId = $('.village').val();
    if(villageId == 0 && townId == 0) {
        layer.msg('请先选择乡镇');
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
    '<table class="table" id="memberAll"><thead><tr><th> 姓名 </th><th> 性别 </th><th> 职务 </th><th> 操作 </th></tr></thead></table>';
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
            {"width": "20%","data": "job"},
            {
                "width": "30%",
                "data": "null",
                "render": function(data, type, row, meta){
                    return "<button type=\"button\" class=\"btn btn_select_role \">选取<input type=\"radio\" style=\"display:none\" name=\"memberId\" value=\""+row.id+"\" /></button>";
                }
                // "defaultContent": 
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
             $('.aid .memberId').find('input').each(function(i){
                if(aData['id'] == $(this).val()) {
                    $(nRow).find(".btn_select_role").attr('style','background:#5cb85c');
                    $(nRow).find(".btn_select_role").find('input').prop('checked',true);
                }
            });
            var tagId = $("input[name='tagId']").val() + ',';
            if(aData['aidingId'] != null && aData['aidingId'].length > 0 && aData['aidingId'] != $('input[name="id"]').val()) {  //标识已被其他帮扶干部选中的贫困户
                $(nRow).find(".btn_select_role").prop("disabled", true);
            }
        },
        "fnInitComplete": function() {}
    });
    //选中
    table.on('click', '.btn_select_role', function(event) {
        var oData_arr = table.DataTable().rows($(this).parents("tr")).data(); // 操作行对象
        var id=oData_arr[0].id;  //member表ID
        var selecttxt = oData_arr[0].name;  //姓名

        $('input[name="memberId"]').val(id);
        $(".add-btn").html(selecttxt);
        $(this).parents(".modal").modal('hide');
    });


    $('#doQuery').click(function(event){  //按名称搜索
        var params = {'name':$("#searchForm input[name='title']").val()};
        $('#memberAll').DataTable().search(JSON.stringify(params)).draw();
    });
});

$('.city').on('change',function (e) {
    $('input[name="memberId"]').val('');
    $(".add-btn").html('');
});
$('.xian').on('change',function (e) {
    $('input[name="memberId"]').val('');
    $(".add-btn").html('');
});
$('.town').on('change',function (e) {
    $('input[name="memberId"]').val('');
    $(".add-btn").html('');
});
$('.village').on('change',function (e) {
    $('input[name="memberId"]').val('');
    $(".add-btn").html('');
})
