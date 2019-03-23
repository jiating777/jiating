var Comment = function() {


    var commentList_url = $('.commentList_url').val();
    var deleteComment_url = $('.deleteComment_url').val();
    var likeList_url = $('.likeList_url').val();

    var isLoadCommentTable = isLoadLikeTable = false;


    /**
     * 评论 table
     */
    var commentTable = function() {
        var table = $('#comment_table');
        var itemId = $('input[name=itemId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: commentList_url,
                    data: {
                        'itemId' : itemId
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
        var itemId = $('input[name=itemId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: likeList_url,
                    data: {
                        'itemId' : itemId
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
        }
    };

}();

$(function() {
    Comment.init();
});
