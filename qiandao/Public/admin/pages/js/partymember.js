var Party = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
    var redirect_url = $('.redirect_url').val();
    var save_url = $('.save_url').val();

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
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                        }
                    },
                    {"data": "name"},
                    {"data": "gender"},
                    {"data": "mobile"},
                    {"data": "partyTime"},
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>' +
                                '<button type="button" class="btn btn-danger delete-btn">删除</button>';
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
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 5]
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

    $(".isaid label input").click(function(){
        var isaid = $(this).val();
        console.log(isaid);
        if(isaid == 1) {
            $('.aid').removeClass('hide');
        } else if(isaid == 2) {
            $('.aid').addClass('hide');
        }
    });


    return {
        init: function() {
            initTable();

            this.onEvent();
        },

        onEvent : function(){
            $('#form-submit').on('click', function () {
                var form = this.form;
                if(!form.id.value) {
                    if(form.cityId.value == 0) {
                        layer.msg('请选择乡村');
                        return;
                    }
                    if(form.organizationId.value == 0) {
                        layer.msg('请选择组织');
                        return;
                    }
                }
                if(form.name.value.trim()=='') {
                    layer.msg('请填写姓名');
                    form.name.focus();
                    return;
                }
                if(form.imgUrl.value=='') {
                    layer.msg("请上传图片!");
                    // return;
                }
                if(form.mobile.value.trim()=='') {
                    layer.msg('请填写姓名');
                    form.mobile.focus();
                    return;
                }
                if(form.job.value.trim()=='') {
                    layer.msg('请填写职务名称');
                    form.job.focus();
                    return;
                }
                var _data = $(form).serializeObject();
                if(_data.villageId != 0) {
                    _data.level = 4;
                } else if(_data.townId != 0) {
                    _data.level = 3;
                } else if(_data.xianId != 0) {
                    _data.level = 2;
                } else {
                    _data.level = 1;
                }
                $("#form-submit").attr("disabled","disabled");
                $.ajax({
                    url : save_url,
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
    Party.init();
    var isaid = $("input[name='isAid']:checked").val();
    if(isaid == 1) {
        $('.aid').removeClass('hide');
    }
});

function getList(e,type) {
    $('.'+type).html('');
    var name = '';
    if(type == 'xian') {
        name = '所有区县';
        $('.town').html('');
        $('.town').append("<option value='0'>所有乡镇</option>");
        $('.village').html('');
        $('.village').append("<option value='0'>所有村</option>");

    } else if(type=='town') {
        name = '所有乡镇';
        $('.village').html('');
        $('.village').append("<option value='0'>所有村</option>");
    }
    if(e.value != 0) {
        $.ajax({
            url : areaturl,
            type : 'post',
            dataType : 'json',
            contentType : "application/json; charset=utf-8",
            data : JSON.stringify({'id':e.value}),
        }).done(function(data) {
            var $item = "<option value='0'>"+name+"</option>";
            for (let i in data){
                $item += "<option  value='"+i+"' '>"+data[i]+"</option>";
            }
            $('.'+type).append($item);
            getOrganization();
        });
    } else {
        $('.'+type).append("<option value='0'>"+name+"</option>");
    }

}

function getVillage(e){
    $('.village').html('');
    if(e.value != 0) {
        $.ajax({
            url : villageurl,
            type : 'post',
            dataType : 'json',
            contentType : "application/json; charset=utf-8",
            data : JSON.stringify({'id':e.value}),
        }).done(function(data) {
            var $item = "<option value='0'>所有村</option>";
            for (let i in data){
                $item += "<option value='"+i+"' '>"+data[i]+"</option>";
            }
            $('.village').append($item);
            getOrganization();           
        });
    } else {
        $('.village').append("<option value='0'>所有村</option>");
    }
}

function getOrganization() {
    var cityId = $('.city').find("option:selected").val();
    var xianId = $('.xian').find("option:selected").val() == undefined ? '0' : $('.xian').find("option:selected").val();
    var townId = $('.town').find("option:selected").val() == undefined ? '0' : $('.town').find("option:selected").val();
    var villageId = $('.village').find("option:selected").val() == undefined ? '0' : $('.village').find("option:selected").val();
    $('.organization').html('');
    $.ajax({
        url : organizationUrl,
        type : 'post',
        dataType : 'json',
        contentType : "application/json; charset=utf-8",
        data : JSON.stringify({'cityId':cityId,'xianId':xianId,'townId':townId,'villageId':villageId}),
    }).done(function(data) {
        var $item = "<option value='0'>选择组织</option>";
        for (let i in data){
            $item += "<option  value='"+i+"' '>"+data[i]+"</option>";
        }
        $('.organization').append($item);
    });
}

$('.add-btn').on('click', function(){    //添加贫困户
    var cityId = $('.city').val();
    var xianId = $('.xian').val();
    var townId = $('.town').val();
    var villageId = $('.village').val();
    var level = 1;
    console.log(cityId);
    if(cityId == 0) {
        layer.msg('请先选择乡村');
        return;
    }
    if(villageId != 0 ) {
        level = 4;
    } else if(townId != 0 ) {
        level = 3;
    } else if(xianId != 0 ) {
        level = 2;
    }else {
        level = 1;
    }
    var param = {};
    switch(level) {
        case 1:{
            param = {'cityId':cityId};
            break;
        }
        case 2:{
            param = {'xianId':xianId};
            break;
        }
        case 3:{
            param = {'townId':townId};
            break;
        }
        case 4:{
            param = {'villageId':villageId};
            break;
        }
    }

    var title = $(this).attr('data-title');
    var content = '<div class="col-md-9"><div class="form-inline"><form id="searchForm" class="relative row"><input type="text" class="form-control" name="title" placeholder="姓名" maxlength="44"><button type="button" id="doQuery" class="btn btn-success" title="筛选"> <i class="fa fa-search"></i> 筛选</button></form></div></div>'+
    '<table class="table" id="memberAll"><thead><tr><th> 姓名 </th><th> 性别 </th><th> 出生年月 </th><th> 身份证号 </th><th> 操作 </th></tr></thead></table>';
    var options = {
        'width' : '1000px',
    };
    open_modal(title, content, options);
    var table = $('#memberAll');
    

    table.dataTable({
        "processing": true, // 开启服务器模式
        "ordering": false, // 禁止排序
        serverSide: false,
        destroy:false,
        "ajax": {
            url:povertylist,
            data:param
        },
        "autoWidth": false,
        "columns": [
            {"width": "20%","data": "name"},
            {"width": "20%","data": "gender"},
            {"width": "20%","data": "birthday",},
            {"width": "20%","data": "identityNumber"},
            {
                "width": "30%",
                "data": "null",
                "render": function(data, type, row, meta){
                    return "<button type=\"button\" class=\"btn btn_select_role \">选取<input type=\"checkbox\" style=\"display:none\" name=\"memberId\" value=\""+row.id+"\" /></button>";
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
        "fnCreatedRow": function(nRow, aData, iDataIndex){
             $('.aid .memberId').find('input').each(function(i){
                if(aData['id'] == $(this).val()) {
                    $(nRow).find(".btn_select_role").attr('style','background:#5cb85c');
                    $(nRow).find(".btn_select_role").find('input').prop('checked',true);
                }
            });
            var tagId = $("input[name='tagId']").val() + ',';
            if(aData['aidingId'] != null && aData['aidingId'].length > 0 && aData['aidingId'] != $('input[name="id"]').val()) {  //标识已被其他帮扶干部选中的贫困户
                $(nRow).find(".btn_select_role").prop("disabled", true);
            }
        },
        "fnInitComplete": function() {}
    });
    //选中
    table.on('click', '.btn_select_role', function(event) {
        var oData_arr = table.DataTable().rows($(this).parents("tr")).data(); // 操作行对象
        var id=oData_arr[0].id;  //member表ID

        if($(this).find("input[name='memberId']").is(':checked')) {  //取消选中
            $(this).attr('style','background-color:buttonface');
            $(this).find("input[name='memberId']").prop('checked',false);
            $('.'+id).remove();
        } else {  //选中,添加
            $(this).attr('style','background-color:#5cb85c');
            $(this).find("input[name='memberId']").prop('checked',true);
            var $item = '<input type="hidden" class="'+id+'" name="memberId" value="'+id+'">';
            $('.aid .memberId').append($item);
            $('.aid .memberId button').append("<span style='margin-right:5px;' class='"+id+"'>"+oData_arr[0].name+"</span>");
        }
    });

    //确定
    $('.modal-footer .btn-success').click(function(event){
        var _data = table.$("input").serializeObject();
        console.log(_data.memberId);
        if(_data.memberId == undefined) {
            layer.msg('您还未选择任何数据');
        }else {
            $(this).parents(".modal").modal('hide');
        }
    });

    $('#doQuery').click(function(event){  //按名称搜索
        console.log('query');
        var title =   $("#searchForm input[name='title']").val();
        var villageId = $('#searchForm .village').find('option:selected').val();
        $('#memberAll').DataTable().columns(0).search(title,true,false).columns(5).search(villageId,true,false).draw();//对第二列进行模糊非智能搜索 
    });
});
