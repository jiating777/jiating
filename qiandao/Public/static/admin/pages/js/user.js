var Operator = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    console.log(datalist_url);

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
                    {"data": "id"},
                    {"data": "name"},
                    {"data": "url"},
                    {"data": "sorting"},
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
                    "targets": [0, 2,4]
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
                if(form.name.value.trim()=='') {
                    layer.msg('请输入菜单名称');
                    form.name.focus();
                    return;
                }
                if(!form.id.value) {
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
                    }
                });

            });
        }
    };

}();


$(function() {
    Operator.init();

});


