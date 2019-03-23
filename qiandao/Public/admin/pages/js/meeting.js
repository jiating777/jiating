var Meeting = function() {


    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var detail_url = $('.detail_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();


    var init_datepicker = function () {
        if($('#date').length > 0){
            $('#date').datepicker({
                language:"cn",
                format: 'yyyy-mm-dd',
                todayBtn : "linked",
                autoclose : true,
                todayHighlight : true,
                startDate : new Date()
                //endDate : new Date()
            });
        }
        // 开始时间
        if($('#startTime').length > 0){
            $('#startTime').timepicker({
                //defaultTime: false,
                autoclose: true,
                showMeridian: false, // 24小时模式
                //showSeconds: true, // 显示秒字段
                minuteStep: 1, // 指定分钟字段的步骤
                secondStep: 1,
                /*icons: {
                    up: 'glyphicon glyphicon-chevron-up',
                    down: 'glyphicon glyphicon-chevron-down'
                }*/
            }).on('changeTime.timepicker',function(e){
                var time = e.time;
                var endTime = e.time.value;
                //var endTime = (time.hours + 1) + ':' + time.minutes + ':' + time.seconds;
                //$('#endTime').timepicker('setTime', endTime);
            });
        }
        // 结束时间
        if($('#endTime').length > 0){
            $('#endTime').timepicker({
                //defaultTime: false,
                autoclose: true,
                showMeridian: false, // 24小时模式
                //showSeconds: true, // 显示秒字段
                minuteStep: 1, // 指定分钟字段的步骤
                secondStep: 1,
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
                    {"data": "title"},
                    {
                        "data": "startTime",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            return data;
                        }
                    },
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
                        //"width": "20%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>';
                            html += '<button type="button" class="btn btn-danger delete-btn">删除</button>';
                            html += '<a href="'+detail_url+'?id='+row.id+'" type="button" class="btn btn-info">详情</a>';
                            html += '<button type="button" class="btn purple signCode-btn">签到码</button>';

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
                    "targets": [0, 3]
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

        // 签到码
        table.on('click', '.signCode-btn', function(event){
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var signCode = dataArr[0].signCode;

            //var url = window.location.href;
            var host = 'http://' + window.location.host;
            var imgUrl = host+'/public/upload/'+signCode;

            var title = '签到码';
            var confirmBtn = '下载签到码';
            var content = '<img src="'+imgUrl+'" alt="签到二维码">';
            layer.open({
                title: title,
                area: ['500px', '500px'],
                content: content,
                btn: [confirmBtn],
                btnAlign: 'c', //按钮居中
                yes: function(index){
                    download(imgUrl);
                    layer.close(index);
                }
            });
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

    // 文件下载
    var download = function (src) {
        var $a = document.createElement('a');
        $a.setAttribute("href", src);
        $a.setAttribute("download", "");
        var evObj = document.createEvent('MouseEvents');
        evObj.initMouseEvent('click', true, true, window, 0, 0, 0, 0, 0, false, false, true, false, 0, null);
        $a.dispatchEvent(evObj);
    };

    var tableSearch = function(table, params) {

        table.DataTable().search(JSON.stringify(params)).draw();
    };


    return {
        init: function() {
            init_datepicker();
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

                if(form.title.value.trim() == '') {
                    layer.msg('请输入会议主题');
                    form.title.focus();
                    return;
                }
                if(form.title.value.length > 40){
                    layer.msg('会议主题最多为40个字符');
                    form.title.focus();
                    return false;
                }
                if(form.date.value.trim() == ''){
                    layer.msg('请输入会议日期');
                    form.date.focus();
                    return false;
                }
                if(form.startTime.value.trim() == ''){
                    layer.msg('请输入会议开始时间');
                    form.startTime.focus();
                    return false;
                }
                if(form.endTime.value.trim() == ''){
                    layer.msg('请输入会议结束时间');
                    form.endTime.focus();
                    return false;
                }
                var startTime = new Date(Date.parse(form.date.value + ' ' + form.startTime.value));
                var endTime = new Date(Date.parse(form.date.value + ' ' + form.endTime.value));
                if(endTime <= startTime){
                    layer.msg('会议结束时间应大于开始时间');
                    form.endTime.focus();
                    return false;
                }
                if(form.address.value.trim() == '') {
                    layer.msg('请输入会议地点');
                    form.address.focus();
                    return;
                }
                if(form.address.value.length > 60){
                    layer.msg('会议地点最多为60个字符');
                    form.address.focus();
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
                        $('.join-tab').trigger('click');
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
                $('.meeting-tab').trigger('click');
            });
        }

    };

}();

jQuery(document).ready(function() {
    Meeting.init();

});