/*地址三级级联下拉菜单*/
function sheng(province){
//    alert(province.value+"_"+typeof province+"_"+province.options[province.selectedIndex].text);
    if(province.value !='-1' && province.value != null){
        $("#region_id").val(province.value);
        var p = province.options[province.selectedIndex].text;
        $("#p").val(p);
              $("#city").css({"display":"block"});
        $("#city").empty();
        $("#county").empty();
        $("#county").css({"display":"none"});
        $.get("index.php?app=member_address&act=select&type=region&pid="+province.value,function(result){
            var result = eval("("+result+")");
            var data = result.retval;
            if(data.length ==1){
                $("#city").append("<option value='-1' selected='selected'>请选择城市</option>");
                $("#city").append("<option value="+data[0].region_id+">"+data[0].region_name+"</option>");
            }else{
                for(var i =0;i <= data.length;i++){
                    if(i == 0 ){
                        $("#city").append("<option value='-1' selected='selected'>请选择城市</option>");
                    }else{
                        $("#city").append("<option value="+data[i].region_id+">"+data[i].region_name+"</option>");
                    }

                }
            }


        });
    }else{
        $("#p").val('');
        $("#city").css({"display":"none"});
        $("#county").css({"display":"none"});
    }
    $("#s").val('');
    $("#q").val('');
    getRegion();
}
function shi(city){
    if(city.value != '-1'){
        $("#region_id").val(city.value);
        var c = city.options[city.selectedIndex].text;
        $("#s").val(c);
        $("#county").css({"display":"block"});
        $("#county").empty();
        $.get("index.php?app=member_address&act=select&type=region&pid="+city.value,function(result){
            var result = eval("("+result+")");
            var data = result.retval;
            if(data.length ==1){
                $("#county").append("<option value='-1' selected='selected'>请选择区县</option>");
                $("#county").append("<option value="+data[0].region_id+">"+data[0].region_name+"</option>");
            }else{
                for(var i =0;i <= data.length;i++){
                    if(i == 0 ){
                        $("#county").append("<option value='-1' selected='selected'>请选择区县</option>");
                    }else{
                        $("#county").append("<option value="+data[i].region_id+">"+data[i].region_name+"</option>");
                    }
                }
            }

        });

    }else{
        $("#s").val('');
        $("#county").empty();
        $("#county").css({"display":"none"});
    }
    $("#q").val('');
    getRegion();
}
function quxian(county){
    if(county.value != '-1'){
        $("#region_id").val(county.value);
        var q = county.options[county.selectedIndex].text;
        $("#q").val(q);
    }else{
        $("#q").val('');
    }
    getRegion();
}

function getRegion(){
    var p = $("#p").val();
    var s = $("#s").val();
    var q = $("#q").val();
//    alert(p+"_"+typeof p+"_"+ p.length);
    if(p.length ==0 && s.length ==0 && q.length ==0 ){
        $("#region_name").val("");
    }else{
        $("#region_name").val(p+"\t"+s+"\t"+q);
    }

}
