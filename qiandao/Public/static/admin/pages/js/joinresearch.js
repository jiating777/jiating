var Joinresearch = function() {


    var joinresearchDatalist_url = $('.joinresearchDatalist_url').val();
    var viewJoinresearch_url = $('.viewJoinresearch_url').val();


    // 参加调研
    var initJoinresearchTable = function() {
        var table = $('#joinresearch-datatable');
        var researchId = $('input[name=researchId]').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: joinresearchDatalist_url,
                    data: {'id':researchId}
                },
                "autoWidth": false,
                "columns": [
                    {"data": "userName"},
                    {"data": "createDate"},
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

            viewJoinresearch(id);
        });
    };

    var viewJoinresearch = function (id = '') {
        var url = viewJoinresearch_url;
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
                    $('#view-modal').find('.modal-body').find('.exam-organization').html($('.exam-organization').html());
                    $('#view-modal').modal('show');
                }else{
                    layer.msg(data.msg);
                }
            }
        });
    };


    return {
        init: function() {
            initJoinresearchTable();

            this.onEvent();
        },

        onEvent: function() {
            //
        }

    };

}();

jQuery(document).ready(function() {
    Joinresearch.init();

});