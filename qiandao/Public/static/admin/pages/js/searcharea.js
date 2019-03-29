
var Area = function() {

    // 初始化地址详细
    var initAddress = function() {

    };


    return {
        init: function() {
            get_city();
            //initAddress();

            this.onEvent();
        },

        onEvent : function(){

            // 选择城市
            $('#city').on('change', function () {
                get_xian($(this));
                $('input[name=city]').val($(this).find('option:selected').text());
                $('input[name=xian]').val('');
                $('input[name=town]').val('');
                $('input[name=village]').val('');
            });

            // 选择区/县
            $('#xian').on('change', function () {
                get_town($(this));
                $('input[name=xian]').val($(this).find('option:selected').text());
                $('input[name=town]').val('');
                $('input[name=village]').val('');
            });

            // 选择乡镇
            $('#town').on('change', function () {
                if($(this).attr('data-hasvillage')){
                    get_village($(this));
                }
                $('input[name=town]').val($(this).find('option:selected').text());
                $('input[name=village]').val('');
            });

            // 选择村
            $('#village').on('change', function () {
                $('input[name=village]').val($(this).find('option:selected').text());
            });
        }
    };

}();


$(function() {
    Area.init();

});

var getregion_url = $('.getregion_url').val();
var getvillage_url = $('.getvillage_url').val();

/**
 * 获取城市
 */
function get_city() {
    $('#xian').html('<option value="0">所有区县</option>');
    $('#town').html('<option value="0">所有乡镇</option>');
    $('#village').html('<option value="0">所有村</option>');

    var url = getregion_url + '?level=1&p_id=0';
    var param = {};
    if(ADMININFO.type > 0) {
        param = {'selected':$('.adminCity').val()};
        get_xian($('.adminCity'));
    }
    $.ajax({
        type : "GET",
        url  : url,
        data:param,
        //dataType:"json",
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return false;
        },
        success: function(data) {
            var options = '<option value="0">所有市</option>'+ data;
            $('#city').empty().html(options);

           /* var city = $('input[name=city]').val();
            if(city){
                $("#city").find("option:contains('"+city+"')").attr("selected", true);
                get_xian($("#city"));
            }*/
        }
    });
    return false;
}

/**
 * 获取县
 * @param select对象
 */
function get_xian(obj) {
    var parent_id = $(obj).val();
    if(!parent_id > 0){
        return false;
    }
    $('#town').html('<option value="0">所有乡镇</option>');
    $('#village').html('<option value="0">所有村</option>');

    var url = getregion_url + '?level=2&p_id=' + parent_id;
    var param = {};
    if(ADMININFO.type > 1) {
        param = {'selected':$('.adminXian').val()};
        get_town($(".adminXian"));
    }
    $.ajax({
        type : "GET",
        url  : url,
        data:param,
        //dataType:"json",
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return false;
        },
        success: function(data) {
            var options = '<option value="0">所有区县</option>'+ data;
            $('#xian').empty().html(options);

            var xian = $('input[name=xian]').val();
            if(xian){
                $("#xian").find("option:contains('"+xian+"')").attr("selected", true);
                get_town($("#xian"));
            }
        }
    });
    return false;
}

/**
 * 获取乡镇
 * @param select对象
 */
function get_town(obj) {
    var parent_id = $(obj).val();
    if(!parent_id > 0){
        return;
    }
    $('#village').html('<option value="0">所有村</option>');

    var url = getregion_url + '?level=3&p_id=' + parent_id;
    var param = {};
    if(ADMININFO.type > 2) {
        param = {'selected':$('.adminTown').val()};
        get_village($(".adminTown"));
    }
    $.ajax({
        type : "GET",
        url  : url,
        data:param,
        //dataType:"json",
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return false;
        },
        success: function(data) {
            var options = '<option value="0">所有乡镇</option>'+ data;
            $('#town').empty().html(options);

            var town = $('input[name=town]').val();
            if(town){
                $("#town").find("option:contains('"+town+"')").attr("selected", true);
                get_village($("#town"));
            }
        }
    });
    return false;
}

/**
 * 获取村
 * @param select对象
 */
function get_village(obj) {
    var parent_id = $(obj).val();
    if(!parent_id > 0){
        return;
    }

    var url = getvillage_url + '?townId=' + parent_id;
    var param = {};
    if(ADMININFO.type > 3) {
        param = {'selected':$('.adminVillage').val()};
    }
    $.ajax({
        type : "GET",
        url  : url,
        data:param,
        //dataType:"json",
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return false;
        },
        success: function(data) {
            var options = '<option value="0">所有村</option>'+ data;
            $('#village').empty().html(options);

            var village = $('input[name=village]').val();
            if(village){
                $("#village").find("option:contains('"+village+"')").attr("selected", true);
            }
        }
    });
    return false;
}