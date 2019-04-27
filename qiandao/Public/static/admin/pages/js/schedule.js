var Schedule = function() {

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
                    {"data": "name"},
                    {"data": "code"},
                    {"data": "total"},
                    {"data": "createDate"},
                    {
                        "render": function(data, type, row, meta) {
                            return '<a href="'+edit_url+'/'+row.id+'" type="button" class="btn btn-success">编辑</a>' +
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
                    "targets": [2,4]
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
                if(form.schoolId.value.trim()=='') {
                    layer.msg('请选择学校');
                    return;
                }
                if(!form.id.value) {
                }
                
                var _data = $(form).serializeObject();
                console.log(_data);
                
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
    Schedule.init();

});

$('.add-btn').on('click', function(){    //选择学校

    var param = {};

    var title = $(this).attr('data-title');
    var content = '<div class="col-md-9"><div class="form-inline"><form id="searchForm" class="relative row"><input type="text" class="form-control" name="title" placeholder="名称" maxlength="20"><button type="button" id="doQuery" class="btn btn-success" title="筛选"> <i class="fa fa-search"></i> 筛选</button></form></div></div>'+
    '<table class="table" id="schoolAll"><thead><tr><th> 名称 </th><th> 代码 </th><th> 操作 </th></tr></thead></table>';
    var options = {
        'width' : '1000px',
    };
    open_modal(title, content, options);
    var table = $('#schoolAll');
    

    table.dataTable({
        "processing": true, // 开启服务器模式
        "ordering": false, // 禁止排序
        serverSide: true,
        destroy:false,
        "ajax": {
            url:schoollist,
            data:param
        },
        "autoWidth": false,
        "columns": [
            {"width": "20%","data": "name"},
            {"width": "20%","data": "code"},
            {
                "width": "30%",
                "data": "null",
                "render": function(data, type, row, meta){
                    return "<button type=\"button\" class=\"btn btn_select_role \">选取<input type=\"checkbox\" style=\"display:none\" name=\"shoolId\" value=\""+row.id+"\" /></button>";
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
        var id=oData_arr[0].id;  //表ID
        var selecttxt = oData_arr[0].name;  //名称


        $("#shceduleForm").find('input[name="schoolId"]').val(id);
        $("#shceduleForm .add-btn").html(selecttxt);
        $(this).parents(".modal").modal('hide');
    });

    //确定
    $('.modal-footer .btn-success').click(function(event){
        var _data = table.$("input").serializeObject();
        console.log(_data.memberId);
        if(_data.memberId == undefined) {
            layer.msg('您还未选择任何数据');
        }else {
            $(this).parents(".modal").modal('hide');
        }
    });

    $('#doQuery').click(function(event){  //按名称搜索
        console.log('query');
        var title =   $("#searchForm input[name='title']").val();
        var param={"name":title};
        $('#schoolAll').DataTable().search(JSON.stringify(param)).draw(); 
        // $('#schoolAll').DataTable().columns(0).search(title,true,false).draw();//对第二列进行模糊非智能搜索 
    });
});

$('.create_total').on('click',function() {  //设置作息时间
    var total = $("#total").val();
    if (!total) {
        layer.msg('请输入总节数');
        return
    }
    if(total > 15) {
        layer.msg('节数超过最大限制');
        return
    }
    var str = "";
    for (var i = 2; i <= total; i++) {
        str += '<label class="control-label col-md-3" style="margin-top: 5px"></label>'+
        '<div class="col-md-2" style="margin-top: 10px">第'+i+'节</div><div class="col-md-3" style="margin-top: 5px">'+
        '<input type="time" class="form-control" placeholder="开始时间" data-required="1" name="begin" value=""></div><div class="col-md-1">-</div>'+
        '<div class="col-md-3" style="margin-top: 5px"><input type="time" class="form-control" placeholder="结束时间" data-required="1" name="end" value=""></div>';
    }
    $('.schedule .form-group').append(str);
    $('.schedule').show();
    $(this).attr("disabled",true);
});

$('.add_line').on('click',function(){
    var num = parseInt($("#total").val())+1;
    $("#total").val(num);
    $("#total2").val(num);
    var str = '<div><label class="control-label col-md-3" style="margin-top: 5px"></label>'+
        '<div class="col-md-1" style="margin-top: 10px">第'+num+'节</div><div class="col-md-3" style="margin-top: 5px">'+
        '<input type="time" class="form-control" placeholder="开始时间" data-required="1" name="begin" value=""></div><div class="col-md-1">-</div>'+
        '<div class="col-md-3" style="margin-top: 5px"><input type="time" class="form-control" placeholder="结束时间" data-required="1" name="end" value=""></div>'+
        '<div class="col-md-1" style="margin-top: 5px"><button class="btn red delete" type="button" onclick="del_line(this)">删除</button></div></div>';

    $('.schedule .form-group').append(str);
});

function del_line(data) {
    console.log('delline');
    $(data).parent().parent().remove();
    var num = parseInt($("#total").val())-1;
    $("#total").val(num);
    $("#total2").val(num);
}




