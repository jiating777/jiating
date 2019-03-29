var Joinmeeting = function() {


    // 参会人员
    var joinmeetingDatalist_url = $('.joinmeetingDatalist_url').val();
    var joinmeetingDelete_url = $('.joinmeetingDelete_url').val();
    var joinmeetingAdd_url = $('.joinmeetingAdd_url').val();

    var memberDatalist_url = $('.memberDatalist_url').val();

    var isLoadJoinmeetingTable = isLoadMemberTable = false;


    var initJoinmeetingTable = function() {
        var table = $('#joinmeeting-datatable');
        var meetingId = $('.meetingId').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: joinmeetingDatalist_url,
                    data: {'id':meetingId}
                },
                "autoWidth": false,
                "columns": [
                    {"data": "name"},
                    {"data": "organizationName"},
                    {"data": "job"},
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<button type="button" class="btn btn-danger delete-btn">删除</button>';

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
                "fnCreatedRow": function(nRow, aData, iDataIndex){
                    var memberIds = $('.memberIds').val();
                    memberIds = memberIds + ',' + aData['memberId'];
                    $('.memberIds').val(memberIds);
                },
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });

            isLoadJoinmeetingTable = true;
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
                    var url = joinmeetingDelete_url;
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

    var initMemberTable = function() {
        var table = $('#member-datatable');
        var townId = $('.townId').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: memberDatalist_url,
                    data: {'townId':townId}
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
                    {"data": "job"},
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            //var html = '<button type="button" class="btn btn-success select-btn">选择</button>';
                            var html = '';
                            html += '<div class="md-checkbox has-success">';
                            html += '<input type="checkbox" id="check-member'+row.id+'" class="md-check check-item" name="id[]" value="'+row.id+'">';
                            html += '<label for="check-member'+row.id+'">';
                            html += '<span class="inc"></span>';
                            html += '<span class="check"></span>';
                            html += '<span class="box"></span>';
                            html += '选择 ';
                            html += '</label>';
                            html += '</div>';

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
                //"destroy": true, // 不重新加载表格内容
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
                "fnCreatedRow": function(nRow, aData, iDataIndex){
                    var memberIds = $('.memberIds').val();
                    if(memberIds.indexOf(aData['id']) >= 0) {
                        $(nRow).find('input[type=checkbox]').prop('checked', true);
                        $(nRow).find('input[type=checkbox]').prop('disabled', true);
                    }
                },
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });

            isLoadMemberTable = true;
        }

        // 选择
        /*table.on('click', '.select-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;
            var _this = $(this);

            var _data = {'id':id};
            _data.meetingId = $('.meetingId').val();
            _this.prop("disabled", true);

            $.ajax({
                url: joinmeetingAdd_url,
                type: 'POST',
                data: _data,
                dataType : 'json',
                success: function (data) {
                    _this.prop("disabled", false);
                    if (data.code == 1) {
                        layer.msg('保存成功');
                        $('#view-modal').modal('hide');

                        var table = $('#joinmeeting-datatable');
                        table.DataTable().ajax.reload();
                    } else if (data.code === 0 ) {  // 错误
                        layer.msg(data.msg);
                    }
                }
            });
        });*/
    };

    var viewMember = function (id = '') {
        $('#view-modal').modal('show');
        if(!isLoadMemberTable){
            initMemberTable();
        }
        /*var table = $('#member-datatable');
        table.DataTable().ajax.reload();*/
    };

    /**
     * 签到 table
     */
    var signMemberTable = function() {
        var table = $('#signMember-datatable');
        var meetingId = $('input[name=meetingId]').val();

        if(table){
            table.dataTable({
                "processing": true,
                "ordering": false, // 禁止排序
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: joinmeetingDatalist_url,
                    data: {
                        'id' : meetingId,
                        'isSign' : 1,
                    }
                },
                "autoWidth": false,
                "columns": [
                    {
                        "data": "avatar",
                        "render": function(data, type, row, meta) {
                            var html = '<div style="float: left;"><img src="'+data+'" class="img-circle" style="width: 70px;height: 40px;"/></div>';
                            html += '<div style="float: left;">';
                            html += '<div class="text-left">'+row.name+'</div>';
                            html += '<div class="text-left">'+row.job+'</div>';
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
    };


    return {
        init: function() {
            signMemberTable();

            this.onEvent();
        },

        onEvent: function() {

            $('.join-tab').on('click', function(){
                var meetingId = $('.meetingId').val();
                if(meetingId && !isLoadJoinmeetingTable){
                    initJoinmeetingTable();
                }
            });

            // 添加参会人员
            $('.addMember-btn').on('click', function(){
                var meetingId = $('.meetingId').val();
                if(!meetingId){
                    layer.msg('请先保存会议信息！');
                    return false;
                }
                viewMember();
            });

            // 确定添加
            $(document).find('.saveJoinmember-btn').on('click', function(){
                var form = document.getElementById("joinmeeting-form");
                var _this = $(this);

                if($(form).find('.check-item').filter(':checked').length <= 0){
                    layer.msg('请至少选择一条数据');
                    return false;
                }
                var ids = '';
                $(form).find('.check-item').not(':disabled').filter(':checked').each(function () {
                    ids += ',' + $(this).val();
                });

                //var _data = $(form).find('.check-item:checked').serializeObject();
                var _data = {};
                _data.ids = ids.substr(1);
                _data.meetingId = $('.meetingId').val();
                _this.prop("disabled", true);

                $.ajax({
                    url: joinmeetingAdd_url,
                    type: 'POST',
                    data : _data,
                    //data : JSON.stringify(_data),
                    dataType : 'json',
                    //contentType : "application/json; charset=utf-8",
                    success: function (data) {
                        _this.prop("disabled", false);
                        if (data.code == 1) {
                            layer.msg('保存成功');
                            $('#view-modal').modal('hide');

                            var table = $('#joinmeeting-datatable');
                            table.DataTable().ajax.reload();

                            $('#member-datatable').DataTable().ajax.reload();
                        } else if (data.code === 0 ) {  // 错误
                            layer.msg(data.msg);
                        }
                    }
                });
                return false;
            });
        }

    };

}();

jQuery(document).ready(function() {
    Joinmeeting.init();

});