var Researchproject = function() {


    // 调研项目
    var projectDatalist_url = $('.projectDatalist_url').val();
    var projectDelete_url = $('.projectDelete_url').val();
    var viewProject_url = $('.viewProject_url').val();
    var saveProject_url = $('.saveProject_url').val();
    var checkProjectSorting_url = $('.checkProjectSorting_url').val();

    var isLoadProjectTable = false;


    var initProjectTable = function() {
        var table = $('#project-datatable');
        var researchId = $('.researchId').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: projectDatalist_url,
                    data: {'id':researchId}
                },
                "autoWidth": false,
                "columns": [
                    {"data": "sorting"},
                    {"data": "title"},
                    {
                        "data": "type",
                        "render": function(data, type, row, meta) {
                            if(!data){
                                return '';
                            }
                            switch(data){
                                case '1' :
                                    data = '单选';
                                    break;
                                case '2' :
                                    data = '多选';
                                    break;
                                default : break;
                            }
                            return data;
                        }
                    },
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            var html = '<button type="button" class="btn btn-success edit-btn">编辑</button>';
                            html += '<button type="button" class="btn btn-danger delete-btn">删除</button>';

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
                "order": [
                    [0, "asc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [1, 2, 3]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });

            isLoadProjectTable = true;
        }

        // 编辑
        table.on('click', '.edit-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;

            viewProject(id);
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
                    var url = projectDelete_url;
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

    var viewProject = function (id = '') {
        var url = viewProject_url;
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
                    handleType();

                    var options = $('.options').val();
                    if(options){
                        options = $.parseJSON(options);
                        var length = options.length;
                        if(length){
                            var type = $('input[name=type]:checked').val();
                            var obj = '';
                            switch(type){
                                case '1' :
                                    obj = $('.type-select');
                                    break;
                                case '2' :
                                    obj = $('.type-multiselect');
                                    break;
                                default : break;
                            }
                            var _html = '';
                            for (var i = 0; i < length; i++){
                                var option = options[i];
                                console.log(option);
                                var isCorrect_class = '';
                                if(option.option_isCorrect == 1){
                                    isCorrect_class = 'is-correct';
                                }
                                _html += '<div class="row">';
                                _html += '<div class="col-md-2">';
                                    _html += '<label class="control-label"></label><input type="text" class="form-control option_NO" name="option_NO'+type+'[]" value="'+option.option_NO+'" readonly>';
                                _html += '</div>';
                                _html += '<div class="col-md-4">';
                                    _html += '<label class="control-label"></label><input type="text" class="form-control option_value" name="option_value'+type+'[]" value="'+option.option_value+'">';
                                _html += '</div>';

                                if(i >= 2){
                                    _html += '<div class="col-md-1">';
                                        _html += '<label class="control-label"></label>';
                                        _html += '<button type="button" class="btn btn-danger remove-btn"><i class="fa fa-close"></i></button>';
                                    _html += '</div>';
                                }
                                _html += '</div>';
                            }

                            obj.find('.exam-options').empty().html(_html);
                        }
                    }

                    $('#view-modal').modal('show');
                }else{
                    layer.msg(data.msg);
                }
            }
        });
    };

    // 考题类型
    var handleType = function() {
        var type = $('input[name=type]:checked').val();
        switch(type){
            case '1' :
                // 单选
                $('.type-select').show();
                $('.type-multiselect').hide();
                break;
            case '2' :
                // 多选
                $('.type-select').hide();
                $('.type-multiselect').show();
                break;
            default : break;
        }
    };

    // 考题答案
    var handleOptions = function(obj) {
        obj.find('.row').each(function (i) {
            var option_NO = '';
            switch(i){
                case 0 :
                    option_NO = 'A';
                    break;
                case 1 :
                    option_NO = 'B';
                    break;
                case 2 :
                    option_NO = 'C';
                    break;
                case 3 :
                    option_NO = 'D';
                    break;
                case 4 :
                    option_NO = 'E';
                    break;
                case 5 :
                    option_NO = 'F';
                    break;
                default : break;
            }

            $(this).find('.option_NO').val(option_NO);
        });
    };

    // 表单提交
    var submitForm = function() {
        var form = document.getElementById("project-form");
        var _this = $(this);

        if(form.title.value.trim() == '') {
            layer.msg('请输入调研标题');
            form.title.focus();
            return;
        }
        if(form.title.value.length > 80){
            layer.msg('调研标题最多为80个字符');
            form.title.focus();
            return false;
        }
        if(form.sorting.value.trim() == ''){
            layer.msg('请输入排序');
            form.sorting.focus();
            return false;
        }

        var type = form.type.value;
        var _data = $(form).serializeObject();
        var obj = '';
        if(type == 1) {
            // 单选
            obj = $('.type-select');
            //_data.option_NO = form.option_NO1.value;
            //_data.option_value = form.option_value1.value;
            //_data.option_isCorrect = form.option_isCorrect1.value;
        } else if(type == 2) {
            // 多选
            obj = $('.type-multiselect');
        }
        var isOptions = true;
        obj.find('.row').each(function (i) {
            var option_value_elm = $(this).find('.option_value');
            var option_value = option_value_elm.val();
            if(option_value.trim() == ''){
                layer.msg('请输入选项内容');
                option_value_elm.focus();
                isOptions = false;
                return false;
            }
            if(option_value.length > 40){
                layer.msg('选项内容最多为40个字符');
                option_value_elm.focus();
                isOptions = false;
                return false;
            }
        });
        if(!isOptions){
            return false;
        }
        _data.researchId = $('.researchId').val();
        _this.prop("disabled", true);

        $.ajax({
            url : saveProject_url,
            type : 'post',
            //data : JSON.stringify(_data),
            data : _data,
            dataType : 'json',
            //contentType : "application/json; charset=utf-8",
        }).done(function(data) {
            _this.prop("disabled", false);
            if (data.code == 1) {
                layer.msg('保存成功');
                $('#view-modal').modal('hide');

                var table = $('#project-datatable');
                table.DataTable().ajax.reload();
            } else if (data.code === 0 ) {  // 错误
                layer.msg(data.msg);
            }
        });
    };


    return {
        init: function() {

            this.onEvent();
        },

        onEvent: function() {

            $('.project-tab').on('click', function(){
                var researchId = $('.researchId').val();
                if(researchId && !isLoadProjectTable){
                    initProjectTable();
                }
            });

            // 添加项目
            $('.addProject-btn').on('click', function(){
                var researchId = $('.researchId').val();
                if(!researchId){
                    layer.msg('请先保存基本信息！');
                    return false;
                }
                viewProject();
            });

            // 切换项目类型
            $(document).on('click', '.check-type', function(){
                handleType();
            });

            // 添加答案
            $(document).on('click', '.addOption-btn', function(){
                var option_length = $(this).parent().find('.exam-options .row').length;
                if(option_length > 5){
                    layer.msg('最多只能添加6个选项！');
                    return false;
                }
                var option_NO = '';
                switch(option_length){
                    case 2 :
                        option_NO = 'C';
                        break;
                    case 3 :
                        option_NO = 'D';
                        break;
                    case 4 :
                        option_NO = 'E';
                        break;
                    case 5 :
                        option_NO = 'F';
                        break;
                    default : break;
                }
                var type = $('input[name=type]:checked').val();

                var options = '<div class="row">';
                options += '<div class="col-md-2">';
                    options += '<label class="control-label"></label><input type="text" class="form-control option_NO" name="option_NO'+type+'[]" value="'+option_NO+'" readonly>';
                options += '</div>';
                options += '<div class="col-md-4">';
                    options += '<label class="control-label"></label><input type="text" class="form-control option_value" name="option_value'+type+'[]" value="">';
                options += '</div>';
                options += '<div class="col-md-1">';
                    options += '<label class="control-label"></label>';
                    options += '<button type="button" class="btn btn-danger remove-btn"><i class="fa fa-close"></i></button>';
                options += '</div>';
                options += '</div>';

                $(this).parent().find('.exam-options').append(options);
            });
            // 移除选项
            $(document).on('click', '.remove-btn', function(){
                var obj = $(this).parents('.exam-options');
                $(this).parent().parent().remove();

                handleOptions(obj);
            });

            // 保存
            $(document).find('.saveProject-btn').on('click', function(){
                var form = document.getElementById("project-form");
                var _this = $(this);

                if(form.title.value.trim() == '') {
                    layer.msg('请输入调研标题');
                    form.title.focus();
                    return;
                }
                if(form.title.value.length > 80){
                    layer.msg('调研标题最多为80个字符');
                    form.title.focus();
                    return false;
                }
                if(form.sorting.value.trim() == ''){
                    layer.msg('请输入排序');
                    form.sorting.focus();
                    return false;
                }
                var sorting = form.sorting.value;
                var sorting_reg = /^(([0-9]+[\.]?[0-9]+)|[1-9])$/;
                if(!sorting_reg.test(sorting)){
                    layer.msg('排序格式不正确');
                    form.sorting.focus();
                    return false;
                }

                // 检查排序
                var sorting = $('#sorting').val();
                var ori_sorting = $('#sorting').data('sorting');
                var researchId = $('.researchId').val();
                if(sorting != ori_sorting){
                    $.ajax({
                        url: checkProjectSorting_url,
                        type: 'POST',
                        data: {'researchId':researchId, 'sorting':sorting},
                        dataType : 'json',
                        success: function (data) {
                            if(data.status == 1){
                                layer.msg('该排序已经存在！');
                                $('#sorting').focus();
                                return false;
                            }else{
                                submitForm();
                            }
                        }
                    });
                    return false;
                }
                submitForm();
            });
        }

    };

}();

jQuery(document).ready(function() {
    Researchproject.init();

});