
var Member = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();
    var no_save_url = $('.no_save_url').val();

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
                    {
                        "data": "cardImg",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                        }
                    },
                    {"data": "identityNumber"},
                    {"data": "mobile"},
                    {"data": "city"},
                    {"data": "xian"},
                    {"data": "town"},
                    {"data": "village"},
                    {
                        "data": "shenheStatus",
                        "render": function(data, type, row, meta) {
                            var html = '';
                            if(data == 2){
                                html += '<span class="label label-sm label-warning"> 审核中 </span>';
                            }else if(data == 1){
                                html += '<span class="label label-sm label-success"> 审核通过 </span>';
                            }else {
                                html += '<span class="label label-sm label-danger"> 审核不过 </span>';
                            }

                            return html;
                        }
                    },
                    {
                        "data": "shenheStatus",
                        "render": function(data, type, row, meta) {
                            var html = '';
                            if(data == 2){
                                html += '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn purple">去审核</a>';
                            }
                            html += '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-info">详情</a>';

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
                    [2, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [1, 9]
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

    return {
        init: function() {
            initTable();
            this.onEvent();
        },

        onEvent : function(){
            $('.save-btn').on('click', function () {
                //var _form = $('#ajax-form');
                var _this = $(this);
                var _data = $('#ajax-form').serializeObject();
                var typebutton = $(this).attr('data-typebutton');

                if(typebutton == 1) {

                    _this.prop("disabled", true);
                    $.ajax({
                        url : save_url,
                        type : 'post',
                        dataType : 'json',
                        contentType : "application/json; charset=utf-8",
                        data : JSON.stringify(_data),
                    }).done(function(data) {
                        if (data.code == 1) {
                            layer.msg(data.msg);
                            window.location.href = datalist_url; //加载页面数据
                        } else if (data.code === 0 ) {  // 错误
                            _this.prop("disabled", false);
                            layer.msg(data.msg);
                        }
                    });
                } else {
                    openSelfDialog(_data);
                    function openSelfDialog(_data){

                        layer.prompt({title: '请输入不通过的原因', formType: 2}, function(text, index){
                            console.log(_data);

                            $.ajax({
                                url : no_save_url,
                                type : 'post',
                                dataType : 'json',
                                contentType : "application/json; charset=utf-8",
                                data : JSON.stringify({"id":_data.id,"failMsg":text}),
                            }).done(function(data) {
                                if (data.code == 1) {
                                    layer.msg('保存成功');
                                    window.location.href = datalist_url; //加载页面数据
                                } else if (data.code === 0 ) {  // 错误
                                    _this.prop("disabled", false);
                                    layer.msg(data.msg);
                                }
                            });
                            layer.close(index);
                            layer.msg('ok');
                        });
                    }
                }
            });
        }
    };

}();


$(function() {
    Member.init();
});


