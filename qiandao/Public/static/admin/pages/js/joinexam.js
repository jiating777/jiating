var Joinexam = function() {


    var joinexamDatalist_url = $('.joinexamDatalist_url').val();
    var viewJoinexam_url = $('.viewJoinexam_url').val();
    //var viewExamresults_url = $('.viewExamresults_url').val();


    // 参加考试
    var initJoinexamTable = function() {
        var table = $('#joinexam-datatable');
        var examId = $('input[name=examId]').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: joinexamDatalist_url,
                    data: {'id':examId}
                },
                "autoWidth": false,
                "columns": [
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            //console.log(meta.row);

                            return '第 ' + (meta.row + 1) + ' 名';
                        }
                    },
                    {"data": "userName"},
                    {"data": "examtime"},
                    {"data": "score"},
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<button type="button" class="btn btn-info view-btn">查看</button>';

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
                    //
                },
                "columnDefs": [{
                    "orderable": false,
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });
        }

        // 编辑
        table.on('click', '.view-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;

            viewJoinexam(id);
        });
    };

    var viewJoinexam = function (id = '') {
        var url = viewJoinexam_url;
        var data = {'id': id};
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    var _html = data.data;

                    $('#view-modal').find('.modal-body').empty().html(_html);
                    $('#view-modal').find('.modal-body').find('.exam-title').html($('.exam-title').html());
                    $('#view-modal').modal('show');
                }else{
                    layer.msg(data.msg);
                }
            }
        });
    };


    return {
        init: function() {
            initJoinexamTable();

            this.onEvent();
        },

        onEvent: function() {
            //
        }

    };

}();

jQuery(document).ready(function() {
    Joinexam.init();

});