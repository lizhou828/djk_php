function drop_cart_item(store_id, rec_id){
    var tr = $('#cart_item_' + rec_id);
    var amount_span = $('#cart' + store_id + '_amount');
    var cart_goods_kinds = $('#cart_goods_kinds');
    $.getJSON('index.php?app=cart&act=drop&rec_id=' + rec_id, function(result){
        if(result.done){
            //删除成功
            if(result.retval.cart.quantity == 0){
                window.location.reload();    //刷新
            }
            else{
                tr.remove();        //移除
                var tb = document.getElementById('store_' + store_id);
                var trLength = tb.getElementsByTagName("tr");
                var store = document.getElementById("store_name_" + store_id);
                if(trLength.length <= 1){
                    tb.parentNode.removeChild(tb);
                    store.parentNode.removeChild(store);
                }
//              amount_span.html(price_format(result.retval.amount));
                total_amout();//刷新总费用
                cart_goods_kinds.html(result.retval.cart.kinds);       //刷新商品种类
            }
        }
    });
}
function move_favorite(store_id, rec_id, goods_id){
    var tr = $('#cart_item_' + rec_id);
    $.getJSON('index.php?app=my_favorite&act=add&type=goods&item_id=' + goods_id, function(result){
        //没有做收藏后的处理，比如从购物车移除
        if(result.done){
            //drop_cart_item(store_id, rec_id);
            alert(result.msg);
        }
        else{
            alert(result.msg);
        }

    });
}
function change_quantity(store_id, rec_id, spec_id, input, orig){
    if($("#input_item_"+spec_id).val()<0|| isNaN($("#input_item_"+spec_id).val())) {
        alert("请输入正整数！");
        $("#input_item_"+spec_id).val("1");
        return false;
    }
    var subtotal_span = $('#item' + rec_id + '_subtotal');
    var amount_span = $('#cart' + store_id + '_amount');
    var total = $('#input_'+rec_id+'_subtotal');
    //暂存为局部变量，否则如果用户输入过快有可能造成前后值不一致的问题
    var _v = input.value;
    $.getJSON('index.php?app=cart&act=update&spec_id=' + spec_id + '&quantity=' + _v, function(result){
        if(result.done){
            //更新成功
            $(input).attr('changed', _v);
            subtotal_span.html(price_format(result.retval.subtotal));
            amount_span.html(price_format(result.retval.amount));
            total.val(parseFloat(result.retval.amount));
            total_amout();
        }
        else{
            //更新失败
            alert(result.msg);
            $(input).val($(input).attr('changed'));
        }
    });
}
function decrease_quantity1(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    var price = $('#input_price_' + rec_id).val();
    var totalPrice = $('#item' + rec_id + '_subtotal');
    var a = $('#input_' + rec_id + '_subtotal');
    if(orig > 1){
        item.val(orig - 1);
        var total = ((orig - 1) * price).toFixed(2);
        a.val(total);
        totalPrice.text("￥" + total);
        total_amout();
        item.keyup();
    }
}
function add_quantity1(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    var price = $('#input_price_' + rec_id).val();
    var subTotal = $('#item' + rec_id + '_subtotal');
    var a = $('#input_' + rec_id + '_subtotal');
    item.val(orig + 1);
    var total = ((orig + 1) * price).toFixed(2);
    a.val(total);
    subTotal.text("￥" + total);
    total_amout();
    item.keyup();
}

function decrease_quantity(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    if(orig > 1){
        item.val(orig - 1);
        item.keyup();
    }
}
function add_quantity(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    item.val(orig + 1);
    item.keyup();
}

function total_amout() {
    var amouts =$("input[name=subtotal]");
    var amout=0;
    for(var i=0;i<amouts.length;i++  )
    {
        amout+=parseFloat(amouts[i].value);
    }
    $("#cart_amount").html("￥"+amout.toFixed(2));
}




