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
                    {"data": "name"},
                    {"data": "userNum"},
                    {"data": "type"},
                    {"data": "phone"},
                    {"data": "schoolName"},
                    {"data": "educational"},
                    {
                        "width": "15%",
                        "render": function(data, type, row, meta) {
                            var str = '';
                            if(row.type == 'student') {
                                str += '<button  type="button" class="btn btn-success record-btn">签到记录</button>';
                            } else {
                                str += '<button  type="button" class="btn btn-success class-btn">班课</button>';
                            }
                            str+='<button type="button" class="btn btn-danger delete-btn">重置密码</button>';
                            return str;
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
                "order": [
                    [1, "asc"]
                ],
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

        // 重置密码
        table.on('click', '.delete-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);

            var text = '是否要重置密码吗？';
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
                    layer.msg('重置成功');
                }
            );
        });

        table.on('click', '.record-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);

            var param = {'studentId':id};
            var title = '签到记录';
            var content = '<div class="col-md-9"><div class="form-inline"></div></div>'+
            '<table class="table" id="record"><thead><tr><th> 姓名 </th><th> 签到课程 </th><th> 签到时间 </th></tr></thead></table>';
            var options = {
                'width' : '1000px',
            };
            open_modal(title, content, options);
            var etable = $('#record');
    

            etable.dataTable({
                "processing": true, // 开启服务器模式
                "ordering": false, // 禁止排序
                serverSide: true,
                destroy:false,
                "ajax": {
                    url:listurl,
                    data:param
                },
                "autoWidth": false,
                "columns": [
                    {"width": "20%","data": "name"},
                    {"width": "20%","data": "classname"},
                    {
                        "width": "30%",
                        "data": "null",
                        "render": function(data, type, row, meta){
                            var date = new Date(row.qiandaotime*1000);
                            return date.getFullYear() + '-' + (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-' +date.getDate() + ' '
                            +date.getHours() + ':'+date.getMinutes() + ':'+date.getSeconds();
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
                "fnInitComplete": function() {}
            });
            //确定
            $('.modal-footer .btn-success').click(function(event){
                $(this).parents(".modal").modal('hide');
            });


        });


        table.on('click', '.class-btn', function(event) {
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);

            var param = {'createId':id};
            var title = '班课记录';
            var content = '<div class="col-md-9"><div class="form-inline"></div></div>'+
            '<table class="table" id="class"><thead><tr><th> 名称 </th><th> 上课地点 </th><th> 上课时间 </th></tr></thead></table>';
            var options = {
                'width' : '1000px',
            };
            open_modal(title, content, options);
            var ctable = $('#class');
    

            ctable.dataTable({
                "processing": true, // 开启服务器模式
                "ordering": false, // 禁止排序
                serverSide: true,
                destroy:false,
                "ajax": {
                    url:classlist,
                    data:param
                },
                "autoWidth": false,
                "columns": [
                    {"width": "20%","data": "classname"},
                    {"width": "20%","data": "location"},
                    {"width": "20%","data": "taketime"}
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
                "fnInitComplete": function() {}
            });
            //确定
            $('.modal-footer .btn-success').click(function(event){
                $(this).parents(".modal").modal('hide');
            });
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


