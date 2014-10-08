/** header.php **/
function slideUp_fn()
{
    $('.ncs_cart_popup').slideUp('slow');
}

/** goods.php **/
$(function(){	
	// 商品小图关联大图选中后样式变化
	$('.nc-zoom-gallery').click(function(){
		$('.zoom-desc').find('.nc-zoom-gallery').removeClass('hovered');
		$(this).addClass('hovered');
	});
	// 商品内容部分折叠收起侧边栏控制
	$('#abc').click(function(){
  		$('.layout').toggleClass('expanded');
	});
	// 商品内容介绍Tab样式切换控制
	$('#categorymenu').find("li").click(function(){
		$('#categorymenu').find("li").removeClass('current');
		$(this).addClass('current');
	});
	// 商品详情默认情况下显示全部
	$('#tabGoodsIntro').click(function(){
		$('.bd').css('display','');
		$('.hd').css('display','');	
	});
	// 点击评价隐藏其他以及其标题栏
	$('#tabGoodsRate').click(function(){
		$('.bd').css('display','none');
		$('#ncGoodsRate').css('display','');
		$('.hd').css('display','none');
	});
	// 点击成交隐藏其他以及其标题
	$('#tabGoodsTraded').click(function(){
		$('.bd').css('display','none');
		$('#ncGoodsTraded').css('display','');
		$('.hd').css('display','none');
	});
	// 点击咨询隐藏其他以及其标题
	$('#tabGuestbook').click(function(){
		$('.bd').css('display','none');
		$('#ncGuestbook').css('display','');
		$('.hd').css('display','none');
	});
	//商品排行Tab切换
	$(".ncs-top-tab > li > a").mouseover(function(e) {
		if (e.target == this) {
			var tabs = $(this).parent().parent().children("li");
			var panels = $(this).parent().parent().parent().children(".ncs-top-panel");
			var index = $.inArray(this, $(this).parent().parent().find("a"));
			if (panels.eq(index)[0]) {
				tabs.removeClass("current ").eq(index).addClass("current ");
				panels.addClass("hide").eq(index).removeClass("hide");
			}
		}
	});
	//信用评价动态评分打分人次Tab切换
	$(".ncs-rate-tab > li > a").mouseover(function(e) {
		if (e.target == this) {
			var tabs = $(this).parent().parent().children("li");
			var panels = $(this).parent().parent().parent().children(".ncs-rate-panel");
			var index = $.inArray(this, $(this).parent().parent().find("a"));
			if (panels.eq(index)[0]) {
				tabs.removeClass("current ").eq(index).addClass("current ");
				panels.addClass("hide").eq(index).removeClass("hide");
			}
		}
	});
		
//触及显示缩略图	
	$('.goods-pic > .thumb').hover(
		function(){
			$(this).next().css('display','block');
		},
		function(){
			$(this).next().css('display','none');
		}
	);
	
	/* 商品购买数量增减js */
	// 增加
	$('.a_x2').click(function(){
		num = parseInt($('#quantity').val());
		max = parseInt($('[nctype="goods_stock"]').text());
		if(num < max){
			$('#quantity').val(num+1);
		}
	});
	//减少
	$('.a_x1').click(function(){
		num = parseInt($('#quantity').val());
		if(num > 1){
			$('#quantity').val(num-1);
		}
	});
	
	// 搜索价格不能填写非数字。
	var re = /^[1-9]+[0-9]*(\.\d*)?$|^0(\.\d*)?$/;
	$('input[name="start_price"]').change(function(){
		if(!re.test($(this).val())){
			$(this).val('');
		}
	});
	$('input[name="end_price"]').change(function(){
		if(!re.test($(this).val())){
			$(this).val('');
		}
	});
});

//获得url上面的参数值，根据参数名获取
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

/* add cart */
function add_to_cart(spec_id, quantity)
{
    var url ='/index.php?app=cart&act=add';
    var shop_id = getQueryString("shop_id");    //获得url参数，判断是否集客小店进来的商品
    var vshop =  getQueryString("vshop") ;
    var cart_parameter = {'spec_id':spec_id, 'quantity':quantity};
    if(shop_id!=null && typeof(shop_id)!="undefined" && shop_id!=""){
        if(  vshop == "1" ){//来自精品集客小店
            cart_parameter = {'spec_id':spec_id, 'quantity':quantity,'shop_id':shop_id,'vshop':vshop};
        }else{
            cart_parameter = {'spec_id':spec_id, 'quantity':quantity,'shop_id':shop_id};
        }
    }


    $.getJSON(url, cart_parameter, function(data){
    	if(data != null){
    		if (data.done)
            {
                $('#bold_num').html(data.retval.cart.kinds);
                $('#bold_mly').html(price_format(data.retval.cart.amount));
                $('#show_cart').slideDown('slow');
                setTimeout(slideUp_fn, 5000);
                // 头部加载购物车信息
                load_cart_information1();
            }
            else
            {
                alert(data.msg);
            }
    	}
    });
}

/* 收藏商品 */
function collect_goods(id)
{
    var url = SITE_URL+ '/index.php?app=my_favorite&act=add&type=goods&ajax=1';
    $.getJSON(url, {'item_id':id}, function(data){
        alert(data.msg);
    });
}
/** left.php **/
// 商品分类
function class_list(obj){
	var stc_id=$(obj).attr('span_id');
	var span_class=$(obj).attr('class');
	if(span_class=='ico-block') {
		$("#stc_"+stc_id).show();
		$(obj).html('<em>-</em>');
		$(obj).attr('class','ico-none');
	}else{
		$("#stc_"+stc_id).hide();
		$(obj).html('<em>+</em>');
		$(obj).attr('class','ico-block');
	}
}