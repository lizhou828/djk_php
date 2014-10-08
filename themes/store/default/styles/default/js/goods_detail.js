function reCursiveShowTalkWindow(sender_id, sender_name, receiver_id, receiver_name, receiver_type, shopJsonStr, realDivId, array_id, goodsId, goodsName) {
    try {
        window.document.getElementById('chat_iframe').contentWindow.showTalkWindow(sender_id, sender_name, receiver_id, receiver_name, receiver_type, shopJsonStr, realDivId, array_id, goodsId, goodsName);
    } catch(e) {
        setTimeout(function() {
            reCursiveShowTalkWindow(sender_id, sender_name, receiver_id, receiver_name, receiver_type, shopJsonStr, realDivId, array_id, goodsId, goodsName);
        }, 500);
    }
}
function to_im() {
//    alert(document.domain);
    var url = $.trim($("#chat_url").val());
    var sendId = $.trim($("#sendId").val()); //login userId for originUserId
    var sendName = $.trim($("#sendName").val()); //login userName for djk
    var userId = $.trim($("#shopId").val()); //rsShop 's originUserId
    userId = $.trim(userId);
    var shopName = $.trim($("#shopName").val());
    var shopId = $.trim($("#shopId").val());
    var goodsName = $.trim($("#goodsName").val());
    var goodsId = $.trim($("#goodsId").val());
    var shopJsonStr = shop2json();
//    var shopJsonStr = "";
    var chat_div = $("#d_im_chat");
    var realDivId = "talkWindowDivId_" + userId;
    if (!document.getElementById('chat_iframe')) {
        chat_div.html("");
        chat_div.append("<div id=\"talkWindowDivId\" style='display:none'></div>");
        chat_div.append("<div class='tstart-drop-panel' id='bottom' style='display:none'></div>");
        chat_div.append("<div id='bottom11'></div>");
        $("<iframe id='chat_iframe' name='chat_iframe' frameborder='no' border='0' scrolling='no' src=" + url + ">").appendTo('div#bottom11');
        $("<iframe id='post_iframe' name='post_iframe' style='display:none;' width='0' height='0' src=''>").appendTo('div#bottom11');
        reCursiveShowTalkWindow(sendId, sendName, userId, shopName, 1, shopJsonStr, realDivId, userId, goodsId, goodsName);
    } else if (!document.getElementById(realDivId)) {
        reCursiveShowTalkWindow(sendId, sendName, userId, shopName, 1, shopJsonStr, realDivId, userId, goodsId, goodsName);
    } else if (document.getElementById(realDivId)) {
        if (document.getElementById("talkWindowDivId").style.display == 'none') {
            document.getElementById("talkWindowDivId").style.display = 'block';
        }
        document.getElementById(realDivId).style.display = 'block';
    }
}

function shop2json() {
    var shopName = $.trim($("#shopName").val());
    var shopId = $.trim($("#shopId").val());
    var shopCity = $.trim($("#shopCity").val());
    var shopContacter = $.trim($("#shopContacter").val());
    var shopTel = $.trim($("#shopTel").val());
    var shopMobile = $.trim($("#shopMobile").val());
    var shopAddress = $.trim($("#shopAddress").val());
    var array = new Array();
    array[0] = shopId;
    array[1] = shopName;
    array[2] = shopCity;
    array[3] = shopContacter;
    array[4] = shopTel;
    array[5] = shopMobile;
    array[6] = shopAddress;
    var jsonStr = $.toJSON(array);
    return jsonStr;
}
