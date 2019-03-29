var ReceiveOrder = function() {


    var datalist_url = $('.datalist_url').val();
    var detail_url = $('.detail_url').val();
    var delete_url = $('.delete_url').val();

    var delivery_url = $('.delivery_url').val();
    var refuse_url = $('.refuse_url').val();


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
                    {"data": "userName"},
                    {"data": "userPhone"},
                    {
                        "data": "deliverAddress",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            return data;
                        }
                    },
                    {"data": "mail"},
                    {"data": "status"},
                    {"data": "createDate"},
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<a href="'+detail_url+'?id='+row.id+'" type="button" class="btn btn-success">详情</a>';
                            //html += '<button type="button" class="btn btn-danger delete-btn">删除</button>';

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
                    [4, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 1, 2, 3, 6]
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
            initTable();

            this.onEvent();
        },

        onEvent: function() {

            $('.save-btn').on('click', function () {
                //var _form = $('#ajax-form');
                var _this = $(this);
                var _data = $('#ajax-form').serializeObject();
                var typebutton = $(this).attr('data-typebutton');

                if(typebutton == 1){
                    //页面层
                    layer.open({
                        type: 1,
                        title: '发货',
                        skin: 'layui-layer-rim', //加上边框
                        area: ['420px', '240px'], //宽高
                        btn: '确定',
                        btnAlign: 'c',
                        content: '<div class="col-xs-12" style="margin-top: 20px;"><select id="expressName" class="form-control layui-layer-input" name="expressName">\n' +
                        '<option value="">物流公司</option>\n' +
                        '<option value="顺丰快递">顺丰快递</option>\n' +
                        '<option value="EMS">EMS</option>\n' +
                        '<option value="申通快递">申通快递</option>\n' +
                        '<option value="圆通快递">圆通快递</option>\n' +
                        '<option value="中通快递">中通快递</option>\n' +
                        '<option value="汇通快递">汇通快递</option>\n' +
                        '<option value="天天快递">天天快递</option>\n' +
                        '<option value="韵达快递">韵达快递</option>\n' +
                        '<option value="德邦物流">德邦物流</option>\n' +
                        '<option value="宅急送快递">宅急送快递</option>\n' +
                        '<option value="中国邮政">中国邮政</option>\n' +
                        '<option value="邮政平邮">邮政平邮</option>\n' +
                        '</select>' +
                        '<span>快递单号：</span><input class="form-control layui-layer-input" name="expressNo" value="">' +
                        '</div>',
                        yes:function() {
                            layer.closeAll();
                            layer.msg('ok');
                            _this.prop("disabled", true);
                            var expressName = $('#expressName option:selected').val();
                            var expressNo = $("input[name='expressNo']").val();
                            $.ajax({
                                url : delivery_url,
                                type : 'post',
                                dataType : 'json',
                                contentType : "application/json; charset=utf-8",
                                data : JSON.stringify({"id":_data.id,"expressName":expressName,"expressNo":expressNo}),
                            }).done(function(data) {
                                if (data.code == 1) {
                                    layer.msg('保存成功');
                                    window.location.href = data.url; //加载页面数据
                                } else if (data.code === 0 ) {  // 错误
                                    _this.prop("disabled", false);
                                    layer.msg(data.msg);
                                }
                            });
                        }
                    });
                }else {
                    openSelfDialog(_data);
                    function openSelfDialog(_data){
                        layer.prompt({title: '请输入不通过的原因', formType: 2}, function(text, index){
                            console.log(_data);
                            $.ajax({
                                url : refuse_url,
                                type : 'post',
                                dataType : 'json',
                                contentType : "application/json; charset=utf-8",
                                data : JSON.stringify({"id":_data.id,"failMsg":text}),
                            }).done(function(data) {
                                if (data.code == 1) {
                                    layer.msg('保存成功');
                                    window.location.href = data.url; //加载页面数据
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

jQuery(document).ready(function() {
    ReceiveOrder.init();

});