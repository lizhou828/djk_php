/* spec对象 */
function spec(id, spec1, spec2, price, stock)
{
    this.id    = id;
    this.spec1 = spec1;
    this.spec2 = spec2;
    this.price = price;
    this.stock = stock;
}

/* goodsspec对象 */
function goodsspec(specs, specQty, defSpec)
{
    this.specs = specs;
    this.specQty = specQty;
    this.defSpec = defSpec;
    this.spec1 = null;
    this.spec2 = null;
    if (this.specQty >= 1)
    {
        for(var i = 0; i < this.specs.length; i++)
        {
            if (this.specs[i].id == this.defSpec)
            {
                this.spec1 = this.specs[i].spec1;
                if (this.specQty >= 2)
                {
                    this.spec2 = this.specs[i].spec2;
                }
                break;
            }
        }
    }

    // 取得某字段的不重复值，如果有spec1，以此为条件
    this.getDistinctValues = function(field, spec1)
    {
        var values = new Array();
        for (var i = 0; i < this.specs.length; i++)
        {
            var value = this.specs[i][field];
            if (spec1 != '' && spec1 != this.specs[i].spec1) continue;
            if ($.inArray(value, values) < 0)
            {
                values.push(value);
            }
        }
        return (values);
    }

    // 取得选中的spec
    this.getSpec = function()
    {
        for (var i = 0; i < this.specs.length; i++)
        {
            if (this.specQty >= 1 && this.specs[i].spec1 != this.spec1) continue;
            if (this.specQty >= 2 && this.specs[i].spec2 != this.spec2) continue;
            return this.specs[i];
        }
        return null;
    }

    // 初始化
    this.init = function()
    {
        if (this.specQty >= 1)
        {
            var spec1Values = this.getDistinctValues('spec1', '');

            for (var i = 0; i < spec1Values.length; i++)
            {
                if (spec1Values[i] == this.spec1)
                {
                    $(".n1_02 dt:eq(0)").append("<li><a href='javascript:void(0);' onclick='selectSpec(1,this)'>" + spec1Values[i] + "</a></li>");
                }
                else
                {
                    $(".n1_02 dt:eq(0)").append("<li><a href='javascript:void(0);' onclick='selectSpec(1, this)'>" + spec1Values[i] + "</a></li>");
                }
            }
        }
        if (this.specQty >= 2)
        {
            var spec2Values = this.getDistinctValues('spec2', '');
            for (var i = 0; i < spec2Values.length; i++)
            {
                if (spec2Values[i] == this.spec2)
                {
                    $(".n1_02 dt:eq(1)").append("<li><a href='javascript:void(0);' onclick='selectSpec(2, this)'>" + spec2Values[i] + "</a></li>");
                }
                else
                {
                    $(".n1_02 dt:eq(1)").append("<li><a href='javascript:void(0);' onclick='selectSpec(2, this)'>" + spec2Values[i] + "</a></li>");
                }
            }
        }
        var spec = this.getSpec();
        $("[ectype='goods_prompt']").html(spec.spec1 + ' ' + spec.spec2);
    }
}

/* 选中某规格 num=1,2 */
function selectSpec(num, liObj)
{
    goodsspec['spec' + num] = $(liObj).html();

    $(liObj).addClass("sel");
    $(liObj).parents('li').siblings().find('a').removeClass("sel");
    var spec = goodsspec.getSpec();
    var sign = 't';
    $('dt[nctyle="ul_sign"]').each(function(){
        if($(this).find('.sel').html() == null ){
            sign = 'f';
        }
    });
    if (spec == null ) {
        $('[nctype="goods_stock"]').html(0);
        $('[nctype="goods_prompt"]').show().html('<dt>提示：</dt><dd><em style="font-style: normal">所选规格库存量不足，请选择其它规格购买。</em></dd>');
    }
    if (spec != null && sign == 't')
    {

        $('[nctype="goods_price"]').html("&yen;"+number_format(spec.price,2));
        //限时折扣价格
        $('[nctype="goods_stock"]').html(spec.stock);
        if(parseInt(spec.stock) == 0){
            $('[nctype="goods_prompt"]').show().html('<dt>提示：</dt><dd><em style="font-style: normal">所选规格库存量不足，请选择其它规格购买。</em></dd>');
        }else{
            SP_V = '';
            $('dt[nctyle="ul_sign"]').find('li > .sel').each(function(i){
                SP_V += $(this).text()+'，';
            });
            SP_V = SP_V.substr(0,SP_V.length-1);
            $('[nctype="goods_prompt"]').show().html('<dt>提示：</dt><dd><em style="font-style: normal">您选择了：'+SP_V+'</em></dd>');
        }
    }
}
function slideUp_fn()
{
    $('.ware_cen').slideUp('slow');
}
$(function(){
    goodsspec.init();

//    //放大镜效果/
//    if ($(".jqzoom img").attr('jqimg'))
//    {
//        $(".jqzoom").jqueryzoom({ xzoom: 310, yzoom: 310 });
//    }
//
//    // 图片替换效果
//    $('.zoom-desc li').mouseover(function(){
//        $('.zoom-desc li').removeClass();
//        $(this).addClass('ware_pic_hover');
//        $('.zoom-small-image img').attr('src', $(this).children('img:first').attr('src'));
//        $('.zoom-small-image img').attr('jqimg', $(this).attr('smallImage'));
//    });
//
    //点击后移动的距离
    var left_num = -55;

    //整个ul超出显示区域的尺寸
    var li_length = ($('.list_div li').width() + 5) * $('.list_div li').length - 310;
    $('.list_a2').click(function(){
        var posleft_num = $('.list_div ul').position().left;
        if($('.list_div ul').position().left > -li_length){
            $('.list_div ul').css({'left': posleft_num + left_num});
        }
    });

    $('.list_a1').click(function(){
        var posleft_num = $('.list_div ul').position().left;
        if($('.list_div ul').position().left < 0){
            $('.list_div ul').css({'left': posleft_num - left_num});
        }
    });

    // 加入购物车弹出层
    $('.close_btn').click(function(){
        $('.ware_cen').slideUp('slow');
    });
});

//收藏分享处下拉操作
//jQuery.divselect = function(divselectid,inputselectid) {
//    var inputselect = $(inputselectid);
//    $(divselectid).click(function(){
//    var ul = $(divselectid+" ul");
//    if(ul.css("display")=="none"){
//    ul.slideDown("fast");
//    }
//});
//$(document).click(function(){
//    $(divselectid+" ul").hide();
//    });
//};
//    $(function(){
//        $.divselect("#handle-l");
//        $.divselect("#handle-r");
//    });