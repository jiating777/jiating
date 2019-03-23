
var Village = function() {

    var datalist_url = $('.datalist_url').val();
    var edit_url = $('.edit_url').val();
    var delete_url = $('.delete_url').val();
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
                "autoWidth": false,
                "columns": [
                    {"data": "name"},
                    {
                        "data": "imgUrl",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+data+'" target="_blank"><img src="'+data+'" style="width: 40px;height: 40px;"/></a>';
                        }
                    },
                    {
                        "width": "15%",
                        "data": "null",
                        "render": function(data, type, row, meta) {
                            return '<a href="'+edit_url+'?id='+row.id+'" type="button" class="btn btn-success">编辑</a>';
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
                    "targets": [0, 2]
                }],
                "dom": "<'row'<'.col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r>" +
                "<t>" +
                "<'relative'<'col-md-5'i><'col-md-7'>lp>",
                "fnInitComplete": function() {
                    var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
                }
            });
        } else {
            // 百度地图API功能
            //创建Map实例
            var map = new BMap.Map("map_container");
            //初始化武汉坐标
            var point = new BMap.Point(114.405492,30.502934);
            var offsetY = 200;
            //地图平移缩放控件：默认左上方
            map.addControl(new BMap.NavigationControl());
            //比例尺控件，默认位于地图左下方，显示地图的比例关系
            map.addControl(new BMap.ScaleControl());
            map.centerAndZoom(point,12);
            //添加鼠标滚动缩放
            map.enableScrollWheelZoom(true);
            //设置标注的图标

            //显示地址信息窗口
            var marker = new BMap.Marker(point);
            //把标注添加到地图上
            map.addOverlay(marker);

            //地址解析类
            var gc = new BMap.Geocoder();
            function showLocationInfo(pt, rs){
                var addComp = rs.addressComponents;
                $("#longitude").val(pt.lng);
                $("#latitude").val(pt.lat);
                console.log('经纬度：'+pt.lat+' '+pt.lng);
                $("#add-text1").val(pt.lat+','+pt.lng);
            }

            //删除标注
            function deletePoint(){
                var allOverlay = map.getOverlays();
                for (var i = 0; i < allOverlay.length; i++){
                    map.removeOverlay(allOverlay[i]);
                }
            }

            //根据选择框或输入框获取地址
            var _area={
                //keyword:'',
                _address:$("#address"),
                unChecked:function(val){
                    return (val==''||typeof (val)=='undefined'||val==null||
                        val=='省份'||val=='地级市'||val=='市、县级市')?false:true;
                },
                list:function(){
                    var _joinArea='';
                    if(_area.unChecked(this._address.val())){
                        _joinArea+=' '+this._address.val();
                        //_joinArea=this._address.val();
                    }
                    return _joinArea;
                },
                init:function(){
                    //绑定百度地图
                    var _this=_area.list();
                    //定义local
                    var local = new BMap.LocalSearch(map, {
                        renderOptions: {
                            map: map,
                            panel: "results",//结果容器id
                            autoViewport: true,   //自动结果标注
                            selectFirstResult: true  , //指定到第一个目标
                            enableRouteSearchBox: true,
                            enableRouteInfo:true
                        },
                        pageCapacity: 1
                    });

                    local.setSearchCompleteCallback(function (searchResult) {
                        var poi = searchResult.getPoi(0);
                        //document.getElementById("result_").value = poi.point.lng + "," + poi.point.lat;
                        map.centerAndZoom(poi.point, 12);
                        gc.getLocation(poi.point, function(rs){
                            showLocationInfo(poi.point, rs);
                        });
                    });
                    local.search(_this);
                }
            }

            //全局变量：存储address字符串长度做判断
            var _thisLength='';
            $("#address").keydown(function(){
                _thisLength=$(this).val().length;
            })
            $("#address").keyup(function(){
                var _newLength=$(this).val().length;
                var timer=setTimeout(function(){
                    if(_thisLength<=_newLength){
                        deletePoint();
                        _area.init();
                    }
                    else{
                        clearTimeout(timer);
                    }
                },1000);
            })
            $("#address").blur(function(){
                deletePoint();
                _area.init();
            })
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
            initTable();

            this.onEvent();
        },

        onEvent : function(){
            $('#form-submit').on('click', function () {
                var form = this.form;
                if(form.id.value.trim()=='') {
                    if(form.townId.value == 0) {
                        layer.msg('请选择村子所在地');
                        return;
                    }
                }
                if(form.name.value.trim()=='') {
                    layer.msg('请输入乡村名称');
                    form.name.focus();
                    return;
                }
                var _data = $(form).serializeObject();
                $("#form-submit").attr("disabled","disabled");
                var posturl = $(form).attr('action');
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
    Village.init();
    if($("#address").val() != ""){
        $("#address").blur();
    }

});
