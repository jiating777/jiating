var Research = function() {


    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var detail_url = $('.detail_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();


    var init_datetimepicker = function () {
        if($('#endTime').length > 0){
            var startDate = new Date();
            $('#endTime').datetimepicker({
                language: 'zh-CN',
                format: 'yyyy-mm-dd hh:ii',
                startDate: startDate,
                //endDate: endDate,
                autoclose: true
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
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 60px;height: 40px;"/></a>'
                                + ' ' + row.name;
                        }
                    },
                    /*{"data": "name"},*/
                    {
                        "data": "endTime",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            return data;
                        }
                    },
                    {
                        //"width": "15%",
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
                    [1, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 2]
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


    return {
        init: function() {
            init_datetimepicker();
            initTable();

            this.onEvent();
        },

        onEvent: function() {

            // 表单提交
            $('#form-submit').on('click', function(){
                var form = this.form;
                var _this = $(this);
                if(!form.id.value) {
                    if(form.townId.value == 0) {
                        layer.msg('请选择所在地');
                        return;
                    }
                }

                if(form.name.value.trim() == '') {
                    layer.msg('请输入调研名称');
                    form.name.focus();
                    return;
                }
                if(form.name.value.length > 40){
                    layer.msg('调研名称最多为40个字符');
                    form.name.focus();
                    return false;
                }
                if(form.endTime.value.trim() == ''){
                    layer.msg('请输入截止时间');
                    form.endTime.focus();
                    return false;
                }
                if(form.imgUrl.value.trim() == ''){
                    layer.msg('请上传封面图');
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
                    _this.prop("disabled", false);
                    if (data.code == 1) {
                        layer.msg('保存成功');

                        $('input[name=id]').val(data.id);
                        $('.project-tab').trigger('click');
                    } else if (data.code === 0 ) {
                        layer.msg(data.msg);
                    }
                });
            });

            // 第二步保存
            $('.secondstep-btn').on('click', function(){
                window.location.href = redirect_url;
            });
            // 返回第一步
            $('.back-firststep').on('click', function(){
                $('.research-tab').trigger('click');
            });
        }

    };

}();

jQuery(document).ready(function() {
    Research.init();

});