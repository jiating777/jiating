var Knowledgetype = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();

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
                    {"data": "partyName"},
                    {"data": "povertyName"},
                    {
                        "data": "content",
                        "render": function(data, type, row, meta) {
                            var str = data.substr(0,30);
                            return str;
                        }
                    },
                    {"data": "createDate"},
                    {
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
                    [3, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0,1,2, 4]
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
            $('#text-count').text($('#note').val().length);   //初始化已输入字数

            $('#note').keyup(function() {
                var len=this.value.length;
                $('#text-count').text(len);
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
            $('#form-submit').on('click', function(){  //提交数据
                if(imgcount > total) {  //检测图片数量
                    layer.msg("图片最多添加"+total+"张，请删除多余图片");
                    return;
                }
                var form = this.form;
                if(form.povertymemberId.value=='') {
                    layer.msg("请选择贫困户!");
                    return;
                }
                if(form.content.value.trim()=='') {
                    layer.msg("请填写工作描述!");
                    form.content.focus();
                    return;
                }

                var _data = $(form).serializeObject();
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
                    } else {
                        $("#form-submit").removeAttr("disabled");
                    }
                });
            });
        }
    };

}();

$(function() {
    Knowledgetype.init();
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
            var curentId = $("input[name='povertymemberId']").val();
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
        $("#povertywork_form").find('input[name="povertymemberId"]').val(id);
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



