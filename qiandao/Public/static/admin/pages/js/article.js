var Article = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var detail_url = $('.detail_url').val();
    var redirect_url = $('.redirect_url').val();

    var commentList_url = $('.commentList_url').val();
    var deleteComment_url = $('.deleteComment_url').val();
    var likeList_url = $('.likeList_url').val();
    var isLoadCommentTable = isLoadLikeTable = false;


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
                    {
                        "data": "iconUrl",
                        "render": function(data, type, row, meta) {
                            if(row.type == 'work') {
                                return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                            } else {
                                return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 60px;height: 40px;"/></a>';
                            }
                        }
                    },
                    {"data": "title"},
                    {"data": "typeId"},
                    {"data": "createDate"},
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
                "order": [
                    [3, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 4]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnCreatedRow": function(nRow, aData, iDataIndex){   //若文章类型为notice或toutiao，则不显示图片列
                    if(aData.type == 'notice' || aData.type == 'toutiao') {
                       $(nRow).find('td').eq(0).attr('style','display: none');
                       $(nRow).find('td').eq(2).attr('style','display: none');
                    }
                },
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

    /**
     * 评论 table
     */
    var commentTable = function() {
        var table = $('#comment_table');
        var articleId = $('input[name=articleId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: commentList_url,
                    data: {
                        'articleId' : articleId
                    }
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatarUrl",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>' + ' ' + row.nickName;
                        }
                    },
                    {"data": "content"},
                    {
                        "data": "createDate",
                        "render": function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        "width": "10%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<button type="button" class="btn btn-danger delete-btn">删除</button>';
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
                    //
                },
                "columnDefs": [{
                    "orderable": false,
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });
        }

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
                    var url = deleteComment_url;
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

        isLoadCommentTable = true;
    };

    /**
     * 点赞 table
     */
    var likeTable = function() {
        var table = $('#like_table');
        var articleId = $('input[name=articleId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: likeList_url,
                    data: {
                        'articleId' : articleId
                    }
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatarUrl",
                        "render": function(data, type, row, meta) {
                            var html = '<div style="float: left;"><a href="'+data+'" target="_blank"><img src="'+data+'" class="img-circle" style="width: 70px;height: 70px;"/></a></div>';
                            html += '<div style="float: left;">';
                                html += '<p class="text-left">'+row.nickName+'</p>';
                                html += '<p>'+row.createDate+'</p>';
                            html += '</div>';

                            return html;
                        }
                    },
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
                    //
                },
                "columnDefs": [{
                    "orderable": false,
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                },
                "fnDrawCallback": function ( oSettings ) {
                    $(oSettings.nTHead).hide();
                }
            });
        }

        isLoadLikeTable = true;
    };


    return {
        init: function() {
            initTable();

            this.onEvent();
        },

        onEvent : function(){

            // 评论
            $('.comment-tab').on('click', function(){
                if(!isLoadCommentTable){
                    commentTable();
                }
            });

            // 点赞
            $('.like-tab').on('click', function(){
                if(!isLoadLikeTable){
                    likeTable();
                }
            });

            $('#form-submit').on('click', function () {
                var form = this.form;
                if(form.cityId.value == 0 && form.type.value != 'toutiao') {
                    layer.msg('请选择阅读范围');
                    return;
                }
                if(form.title.value.trim()=='') {
                    layer.msg('请输入标题');
                    form.title.focus();
                    return;
                }
                if(form.typeId.value==='0') {
                    layer.msg("请选择分类!");
                    return;
                }
                if(form.type.value != 'notice' && form.type.value != 'toutiao') {
                    if(form.iconUrl.value=='') {
                        layer.msg("请上传图片!");
                        return;
                    }
                }
                if(form.detail.value.trim()=='') {
                    layer.msg("请输入详情内容题!");
                    form.detail.focus();
                    return;
                }
                var lenth = mdTool.sumBytes(form.detail.value);
                // 500KB
                if (lenth > 1024 * 500) {
                    layer.msg('您输入的详情已超过最大范围,请修改后保存');
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
                } else {
                    _data.level = 0;
                }
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
    Article.init();
});

