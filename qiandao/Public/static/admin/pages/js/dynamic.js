
var Dynamic = function() {


    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var detail_url = $('.detail_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();
    var checkPass_url = $('.checkPass_url').val();

    var userDatalist_url = $('.userDatalist_url').val();

    var isLoadUserTable = false;


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
                        "data": "user.avatar",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                        }
                    },
                    {"data": "user.name"},
                    {
                        "data": "content",
                        "render": function(data, type, row, meta) {
                            return data.length > 15 ?  data.substr(0,15)+'...' : data;
                        }
                    },
                    {"data": "createDate"},
                    {
                        "data": "isPass",
                        "render": function(data, type, row, meta) {
                            var html = '';
                            if(data == 1){
                                html += '<span class="label label-sm label-success"> 审核通过 </span>';
                            }else if(data == 2){
                                html += '<span class="label label-sm label-danger"> 审核不过 </span>';
                            }else{
                                html += '<span class="label label-sm label-warning"> 未审核 </span>';
                            }

                            return html;
                        }
                    },
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>';
                            html += '<button type="button" class="btn btn-danger delete-btn">删除</button>';
                            html += '<a href="'+detail_url+'?id='+row.id+'" type="button" class="btn btn-info">详情</a>';

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
                    "targets": [0, 1, 5]
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

    var initUserTable = function(params) {
        var table = $('#user-datatable');

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: userDatalist_url,
                    data: params
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatar",
                        "render": function(data, type, row, meta) {
                            return '<img src="'+data+'" style="width: 40px;height: 40px;"/>';
                        }
                    },
                    {"data": "name"},
                    {
                        "data": "gender",
                        "render": function(data, type, row, meta) {
                            if(data == 1){
                                return '男';
                            }else if(data == 2){
                                return '女';
                            }else{
                                return '未知';
                            }
                        }
                    },
                    {"data": "organization"},
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<button type="button" class="btn btn-success select-btn">选择</button>';

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
                "destroy": true, // 重新加载表格内容
                "columnDefs": [{
                    "orderable": false,
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnCreatedRow": function(nRow, aData, iDataIndex){
                    //
                },
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });

            isLoadUserTable = true;
        }

        // 筛选
        $('#doSearch').on('click', function(event){
            var param = $('#searchForm').serializeObject();

            tableSearch(table, param);
        });

        // 选择
        table.on('click', '.select-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var name = dataArr[0].name;

            $('input[name=createUser]').val(id);
            $('.select-user').empty().html(name);
            $('#user-modal').modal('hide');
        });
    };

    var viewUser = function (id = '') {
        var townId = $('.town').val();
        var villageId = $('.village').val();
        if(villageId == 0 && townId == 0) {
            layer.msg('请先选择乡镇');
            return false;
        }
        var isParty = $('input[name=isParty]:checked').val();
        var params = {
            'townId' : townId,
            'villageId' : villageId,
            'isParty' : isParty
        };

        $('#user-modal').modal('show');
        initUserTable(params);
    };


    return {
        init: function() {
            initTable();

            this.onEvent();
        },

        onEvent : function(){

            // 选择发布人
            $('.select-user').on('click', function(){
                viewUser();
            });

            $('.check-type, .city, .xian, .town, .village').on('change', function(){
                $('input[name=createUser]').val('');
                $('.select-user').empty().html('请选择');
            });

            // 表单提交
            $('.save-btn').on('click', function () {
                //var _form = $('#ajax-form');
                var _this = $(this);
                var form = this.form;

                if(form.townId.value == 0) {
                    layer.msg('请选择所在地');
                    return;
                }
                if(form.createUser.value.trim()=='') {
                    layer.msg('请选择发布人');
                    return;
                }
                if(form.content.value.trim()=='') {
                    layer.msg('请输入动态内容');
                    form.content.focus();
                    return;
                }
                if($('#upload-image').find('img.exist-image').length <= 0){
                    layer.msg('请上传动态图');
                    return false;
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

            // 审核
            $('.checkPass-btn').on('click', function () {
                //var _form = $('#ajax-form');
                var _this = $(this);
                var id = $('input[name=id]').val();
                var isPass = $(this).attr('data-value');
                var _data = {'id':id, 'isPass':isPass};

                _this.prop("disabled", true);
                $.ajax({
                    url : checkPass_url,
                    type : 'post',
                    dataType : 'json',
                    contentType : "application/json; charset=utf-8",
                    data : JSON.stringify(_data),
                }).done(function(data) {
                    _this.prop("disabled", false);
                    layer.msg(data.msg);
                    if (data.code == 1) {
                        layer.msg(data.msg);
                        window.location.href = data.url; //加载页面数据
                    }
                });
            });
        }
    };

}();

$(function() {
    Dynamic.init();

});


