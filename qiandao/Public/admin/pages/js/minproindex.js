var Townprogram = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var detail_url = $('.detail_url').val();
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
                "autoWidth": true,
                "columns": [

                    {"data": "user_version"},
                    {"data": "draft_id"},
                    {"data": "source_miniprogram"},
                    {"data": "user_desc"},
                    {"data": "developer"},
                    {"data": "create_time"},
                    {
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>' +
                                '<button type="button" class="btn btn-danger delete-btn">删除</button>' +
                                '<a href="'+detail_url+'?id='+row.id+'" type="button" class="btn btn-info">详情</a>';
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
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
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
            //initTable();

            this.onEvent();
        },

        onEvent : function(){


            $('#form-submit').on('click', function () {
                var form = this.form;
                if(form.projectType.value == 0) {
                    layer.msg('请选择项目');
                    return;
                }
                if(form.template_id.value.trim()=='') {
                    layer.msg('请输入第三方授权模板ID');
                    form.template_id.focus();
                    return;
                }

                if(form.user_version.value.trim()=='') {
                    layer.msg('请输代码版本号');
                    form.user_version.focus();
                    return;
                }
                if(form.user_desc.value.trim()=='') {
                    layer.msg('请输入代码描述');
                    form.user_desc.focus();
                    return;
                }
                if(form.tag.value.trim()=='') {
                    layer.msg('请输入小程序的标签');
                    form.tag.focus();
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
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        window.location.href= data.url; //加载页面数据
                    } else if (data.status === 0 ) {  // 错误
                        $("#form-submit").removeAttr("disabled");
                        layer.msg(data.message);
                    }
                });

            });

            $('.add_experiencer').on('click',function() {
                layer.prompt({title:'请输入体验者微信号'},function(val,index){
                    var url='http://upa.kuman.cn/finance/tus';
                    $.ajax({
                        url : '/admin/MiniProgram/addexperiencer?wechat_id=' + val,
                        type : 'get',
                        dataType : 'json',
                        contentType : "application/json; charset=utf-8",
                        data : {},
                    }).done(function(data) {
                        if (data.status == 1) {
                            layer.msg(data.message);
                            //window.location.href= data.url; //加载页面数据
                        } else if (data.status == 0 ) {  // 错误
                            $("#form-submit").removeAttr("disabled");
                            layer.msg(data.message);
                        }
                    });
                    layer.close(index);
                    //window.parent.location.reload();
                });

            });

            $('.look_qrcode').on('click',function() {
                var qrcodeurl =  $(this).attr('data-qrcode');
                //页面层
                openSelfDialog(qrcodeurl);
                function openSelfDialog(qrcodeurl) {
                    if(!qrcodeurl){
                        layer.tips('暂时获取不到二维码，您还没有授权，或者IP没有添加到开放平台', '.look_qrcode', {
                            tips: [1, '#26344b'],
                            time: 4000
                        });
                    }else {
                        layer.open({
                            type: 3,
                            title:'小程序体验码',
                            skin: 'layui-layer-rim', //加上边框
                            shadeClose: true,
                            area: ['440px', '440px'], //宽高
                            content: '<img src='+ qrcodeurl+'>'
                        });
                    }

                }

            });

            $('.jiebang').on('click',function() {
                layer.open({
                    type: 1
                    ,title: "提示" //不显示标题栏   title : false/标题
                    ,closeBtn: true
                    ,area: '300px;'
                    ,shade: 0.6
                    ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
                    ,resize: false
                    ,btn: ['确定', '取消']
                    ,btnAlign: 'c'
                    ,moveType: 1 //拖拽模式，0或者1
                    ,content: '<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">注意：解绑之前必须先去小程序后台解除第三方绑定！<a href="https://mp.weixin.qq.com/" target="_blank">去小程序后台</a><br/>解绑之后将无法访问小程序，<br/>请谨慎操作！！</div>'
                    ,success: function(layero){
                        var btn = layero.find('.layui-layer-btn');
                        btn.find('.layui-layer-btn0').click(function(){

                            var url = "/admin/MiniProgram/wechatjiebang";
                            $.ajax({
                                type: 'POST',
                                url: url,
                                dataType: 'json',
                                success: function(data){
                                    if(data.status == '1'){
                                        layer.alert(data.message,{icon: 6});
                                        location.reload();
                                    }else {
                                        layer.alert(data.message,{icon: 5});
                                    }

                                },
                                error:function(data) {
                                    console.log(data.msg);
                                },
                            });
                        });
                    }
                });
            });
        }
    };

}();

$(function() {
    Townprogram.init();
    
});
