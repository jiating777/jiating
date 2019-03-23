function changeVillage(e) {  //切换镇，获得村列表
    var village = $('#village').val();
    village = JSON.parse(village);
    if(e.value != 0) {
        var v1 = village[e.value];
        console.log(e.value);
        console.log(village);
        $('.village').html('');
        for(var i in v1) {
            var $item = "<option value='"+i+"'>"+v1[i]+"</option>";
            $('.village').append($item);
        }
    } else {
        $('.village').html('<option value="0">所有村</option>');
    }    
}

//若有筛选条件，初始化筛选条件
var village = $('#village').val();
village = JSON.parse(village);
var pid = $('#parentId').val();   //筛选条件，镇id
if(pid == 0) {
    pid =   $('.town option:selected').val(); 
}
var vid = $('#villageId').val();  //筛选条件，村id
if(pid != 0) {
    var village = village[pid];
    $('.village').html('<option value="0">所有村</option>');
    for(var i in village) {
        var $item = "<option ";
        if(i == vid) {
            $item += 'selected';
        }
        $item += " value='"+i+"'>"+village[i]+"</option>";
        $('.village').append($item);
    }
}


var adminTyp = $('#type').val();
//若登录账号类型为5，初始化村列表
if(adminTyp == 5) {
    var v = village;    
    for(var i in v) {
        var $item = "<option ";
            $item += 'selected';
        $item += " value='"+i+"'>"+v[i]+"</option>";
        $('.village').append($item);
    }
}


