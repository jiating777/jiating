var Examquestions = function() {


    // 考题
    var examDatalist_url = $('.examDatalist_url').val();
    var examDelete_url = $('.examDelete_url').val();
    var viewExam_url = $('.viewExam_url').val();
    var saveExam_url = $('.saveExam_url').val();
    var checkExamNumber_url = $('.checkExamNumber_url').val();

    var isLoadExamTable = false;
    var isCheckExamNumber = false;


    var initExamTable = function() {
        var table = $('#exam-datatable');
        var examId = $('.examId').val();

        if(table.length > 0){
            table.dataTable({
                "processing": true,
                serverSide: true,// 开启服务器模式
                "ajax": {
                    url: examDatalist_url,
                    data: {'id':examId}
                },
                "autoWidth": false,
                "columns": [
                    {"data": "number"},
                    {"data": "subject"},
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
                                case '3' :
                                    data = '判断';
                                    break;
                                default : break;
                            }
                            return data;
                        }
                    },
                    {"data": "score"},
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
                    "targets": [1, 2, 4]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    //var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });

            isLoadExamTable = true;
        }

        // 编辑
        table.on('click', '.edit-btn', function(event) {
            // 操作行对象
            var dataArr = table.DataTable().rows($(this).parents("tr")).data();
            var id = dataArr[0].id;

            viewExam(id);
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
                    var url = examDelete_url;
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

    var viewExam = function (id = '') {
        var url = viewExam_url;
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
                    //console.log(options);
                    if(options){
                        options = $.parseJSON(options);
                        console.log(options);
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
                                case '3' :
                                    obj = $('.type-judge');
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
                                _html += '<div class="col-md-3">';
                                    _html += '<label class="control-label"></label>';
                                 _html += '<button type="button" class="btn btn-default '+isCorrect_class+' set-correct"><input type="hidden" class="form-control option_isCorrect" name="option_isCorrect'+type+'[]" value="'+option.option_isCorrect+'">正确答案</button>';
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
                $('.type-judge').hide();
                break;
            case '2' :
                // 多选
                $('.type-select').hide();
                $('.type-multiselect').show();
                $('.type-judge').hide();
                break;
            case '3' :
                // 判断
                $('.type-select').hide();
                $('.type-multiselect').hide();
                $('.type-judge').show();
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
    var submitForm = function(btnObj) {
        var form = document.getElementById("exam-form");
        var _this = btnObj;

        if(form.number.value.trim() == ''){
            layer.msg('请输入题号');
            form.number.focus();
            return false;
        }
        if(form.number.value > 200){
            layer.msg('题号最多为200题');
            form.number.focus();
            return false;
        }
        if(form.subject.value.trim() == '') {
            layer.msg('请输入考题题目');
            form.subject.focus();
            return;
        }
        if(form.subject.value.length > 80){
            layer.msg('考题题目最多为80个字符');
            form.subject.focus();
            return false;
        }
        if(form.score.value.trim() == ''){
            layer.msg('请输入考题分数');
            form.score.focus();
            return false;
        }
        if(form.score.value > 100){
            layer.msg('考题分数最高为100分');
            form.score.focus();
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
        } else {
            // 判断
            obj = $('.type-judge');
        }
        var isOptions = true;
        obj.find('.row').each(function (i) {
            var option_value_elm = $(this).find('.option_value');
            var option_value = option_value_elm.val();
            if(option_value.trim() == ''){
                layer.msg('请输入答案');
                option_value_elm.focus();
                isOptions = false;
                return false;
            }
            if(option_value.length > 40){
                layer.msg('答案最多为40个字符');
                option_value_elm.focus();
                isOptions = false;
                return false;
            }
        });
        if(!isOptions){
            return false;
        }
        _data.examId = $('.examId').val();
        _this.prop("disabled", true);

        $.ajax({
            url : saveExam_url,
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

                var table = $('#exam-datatable');
                table.DataTable().ajax.reload();
            } else if (data.code === 0 ) {  // 错误
                layer.msg(data.msg);
            }
        });
    };


    return {
        init: function() {
            //initExamTable();

            this.onEvent();
        },

        onEvent: function() {

            $('.exam-tab').on('click', function(){
                var examId = $('.examId').val();
                if(examId && !isLoadExamTable){
                    initExamTable();
                }
            });

            // 添加考题
            $('.addExam-btn').on('click', function(){
                var examId = $('.examId').val();
                if(!examId){
                    layer.msg('请先保存考试内容！');
                    return false;
                }
                viewExam();
            });

            // 切换考题类型
            $(document).on('click', '.check-type', function(){
                handleType();
            });

            // 添加答案
            $(document).on('click', '.addOption-btn', function(){
                var option_length = $(this).parent().find('.exam-options .row').length;
                if(option_length > 5){
                    layer.msg('最多只能添加6个答案！');
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
                options += '<div class="col-md-3">';
                    options += '<label class="control-label"></label>';
                    options += '<button type="button" class="btn btn-default set-correct"><input type="hidden" class="form-control option_isCorrect" name="option_isCorrect'+type+'[]" value="2">正确答案</button>';
                options += '</div>';
                options += '<div class="col-md-1">';
                    options += '<label class="control-label"></label>';
                    options += '<button type="button" class="btn btn-danger remove-btn"><i class="fa fa-close"></i></button>';
                options += '</div>';
                options += '</div>';

                $(this).parent().find('.exam-options').append(options);
            });
            // 移除答案
            $(document).on('click', '.remove-btn', function(){
                var obj = $(this).parents('.exam-options');
                if($(this).parent().parent().find('.is-correct').length > 0){
                    layer.msg('不能删除正确答案！');
                    return false;
                }
                $(this).parent().parent().remove();

                handleOptions(obj);
            });
            // 设为正确答案
            $(document).on('click', '.set-correct', function(){
                var obj = $(this).parents('.exam-options');
                var isCorrect = $(this).hasClass('is-correct');
                var type = $('input[name=type]:checked').val();
                if(type != 2) {
                    /*obj.find('.set-correct').removeClass('is-correct');
                    obj.find('.option_isCorrect').val(2);
                    obj.find('.set-correct').prop('disabled', false);

                    if(!isCorrect){
                        $(this).addClass('is-correct');
                        $(this).find('.option_isCorrect').val(1);
                        $(this).prop('disabled', true);
                    }else{
                        $(this).removeClass('is-correct');
                        obj.find('.option_isCorrect').val(2);
                    }*/
                    if(isCorrect){
                        // 至少要保留一个正确答案
                        return false;
                    }else{
                        obj.find('.set-correct').removeClass('is-correct');
                        obj.find('.option_isCorrect').val(2);

                        $(this).addClass('is-correct');
                        $(this).find('.option_isCorrect').val(1);
                    }
                } else {
                    if(!isCorrect){
                        $(this).addClass('is-correct');
                        $(this).find('.option_isCorrect').val(1);
                    }else{
                        if(obj.find('.is-correct').length <= 1){
                            // 至少要保留一个正确答案
                            return false;
                        }
                        $(this).removeClass('is-correct');
                        $(this).find('.option_isCorrect').val(2);
                    }
                }
            });

            // 检查题号
            $(document).on('blur', '#examNumber', function(){
                //checkExamNumber();
            });
            // 保存
            $(document).find('.saveExam-btn').on('click', function(){
                var form = document.getElementById("exam-form");
                var _this = $(this);

                if(form.number.value.trim() == ''){
                    layer.msg('请输入题号');
                    form.number.focus();
                    return false;
                }
                var number = form.number.value;
                var number_reg = /^(([0-9]+[\.]?[0-9]+)|[1-9])$/;
                if(!number_reg.test(number)){
                    layer.msg('题号格式不正确');
                    form.number.focus();
                    return false;
                }
                if(form.number.value > 200){
                    layer.msg('题号最多为200题');
                    form.number.focus();
                    return false;
                }

                // 检查题号
                var number = $('#examNumber').val();
                var ori_number = $('#examNumber').data('number');
                var examId = $('.examId').val();
                if(number != ori_number){
                    $.ajax({
                        url: checkExamNumber_url,
                        type: 'POST',
                        data: {'examId':examId, 'number':number},
                        dataType : 'json',
                        success: function (data) {
                            if(data.status == 1){
                                layer.msg('该题号已经存在！');
                                $('#examNumber').focus();
                                return false;
                            }else{
                                submitForm(_this);
                            }
                        }
                    });
                    return false;
                }
                submitForm(_this);
            });
        }

    };

}();

jQuery(document).ready(function() {
    Examquestions.init();

});