<!doctype html>
<html>
<head>
    <base href="<?php echo $this->_var['site_url']; ?>/" />
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
    <meta HTTP-EQUIV="expires" CONTENT="0">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7 charset=utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php echo $this->_var['page_seo']; ?>

    <style type="text/css">
        body {
            _behavior: url(<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/csshover.htc);
        }</style>
    <script type="text/javascript">
        //<!CDATA[
        var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
        var IMG_URL = "<?php echo $this->_var['img_url']; ?>";
        var REAL_SITE_URL = "<?php echo $this->_var['real_site_url']; ?>";
        var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
        //]]>
    </script>
    <script type="text/javascript" src="index.php?act=jslang"></script>
    <link href="<?php echo $this->res_base . "/" . 'css/public.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/index.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/djk.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/base.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/dialog.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/layout.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/user.css'; ?>" rel="stylesheet" type="text/css">
    <!--[if IE]>
    <script src="<?php echo $this->res_base . "/" . 'js/html5.js'; ?>"></script>
    <![endif]-->
    <!--[if IE 6]>
    <script src="<?php echo $this->res_base . "/" . 'js/IE6_PNG.js'; ?>"></script>
    <script>
        DD_belatedPNG.fix('.pngFix');
    </script>
    <script>
        // <![CDATA[
if((window.navigator.appName.toUpperCase().indexOf("MICROSOFT")>=0)&&(document.execCommand))
try{
document.execCommand("BackgroundImageCache", false, true);
   }
catch(e){}
// ]]>
</script>
<![endif]-->
    <script>
        COOKIE_PRE = '7210_';_CHARSET = 'utf-8';SITEURL = '<?php echo $this->_var['site_url']; ?>';
    </script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/jquery.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/common.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript">
        $(function () {
            //search
            $("#details").children('ul').children('li').click(function () {
                $(this).parent().children('li').removeClass("current");
                $(this).addClass("current");
                $('#search_act').attr("value", $(this).attr("act"));
            });
            var search_act = $("#details").find("li[class='current']").attr("act");
            $('#search_act').attr("value", search_act);
            $("#keyword").blur();
            var url = SITE_URL+"/index.php?app=default&act=get_vistor";
            $.getJSON(url,{},function(data) {
                if(data['nick_name']!=null){
                    $("#login_name").html(data['nick_name']+"&nbsp;");
                }
                if (data['user_id'] ==0 ) {
                    $("#login").html("[<a href='<?php echo url('app=member&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>'>登录</a>]");
                    $("#register").html("[<a href='<?php echo url('app=member&act=register&ret_url=' . $this->_var['ret_url']. ''); ?>'>注册</a>]");
                } else {
                    $("#login_out").html("[<a href='<?php echo url('app=member&act=logout'); ?>'>退出</a>]");
                }
                if (data['member_type'] =='02' || data['member_type']=='03') {
                    var html="";
                    html+="<li class=\"user-center\">";
                    html+="<div class=\"pm\"><a href=\"index.php?app=service&p=service&tuoguan=1\" target=\"_top\" title=\"进入服务中心\">进入服务中心<i></i></a>";
                    html+="</div>";
                    html+="</li>";
                    $(".quick-menu").html(html);
                } else if (data['member_type'] =='04') {
                    var html="";
                    html+="<li class=\"user-center\">";
                    html+="<div class=\"pm\"><a href=\"index.php?app=service&p=service&tuoguan=1\" target=\"_top\" title=\"进入采购中心\">进入采购中心<i></i></a>";
                    html+="</div>";
                    html+="</li>";
                    $(".quick-menu").html(html);
                }

            })
        });


        $(function(){
            $.getJSON("index.php?app=cart&act=cart_kinds",{},function(data) {
                $("#cart_kinds").html(data);
            });
        });
    </script>
    <script type="text/javascript"  src="<?php echo $this->res_base . "/" . 'js/min.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/accordion.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/tonjay.js'; ?>"  charset="utf-8"></script>
    <script type="text/javascript"  src="<?php echo $this->res_base . "/" . 'js/swfobject_modified.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript"  src="<?php echo $this->res_base . "/" . 'js/mini.js'; ?>" charset="utf-8"></script>
    <!--<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/index.js'; ?>" charset="utf-8"></script>-->
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/poshytip.js'; ?>" charset="utf-8"></script>
    
    <script type="text/javascript"  src="<?php echo $this->res_base . "/" . 'js/double_adv.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jike.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'member.js'; ?>" charset="utf-8"></script>
    <?php echo $this->_var['_head_tags']; ?>

</head>

<body>

<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<div id="page">
    <div id="topNav" class="warp-all">
        <dl class="user-entry">
            <dt><span id="login_name"></span>您好，欢迎来到<span><a href="<?php echo $this->_var['site_url']; ?>" title="首页" alt="首页">大集客网上商城</a></span></dt>
            <dd id="login"></dd>
            <dd id="register"></dd>
            <dd id="login_out"></dd>
        </dl>
        <ul class="quick-menu">
            <?php if ($this->_var['visitor']['member_type'] == '01'): ?>
            <li>
                <?php if ($this->_var['member']['user_id'] > 0 && $this->_var['member']['spreader_type'] == 1 && $this->_var['member']['shop_name'] != null): ?>
                <?php if (strstr ( $this->_var['site_url'] , 'www' )): ?>
                <a href="http://www.<?php echo $this->_var['member']['user_id']; ?>.dajike.com" target="_blank">
                    <?php else: ?>
                    <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=my_jkxd&id=<?php echo $this->_var['member']['user_id']; ?>" target="_blank">
                        <?php endif; ?>
                        <?php else: ?>
                        <a href="index.php?app=jkxd_portal">
                            <?php endif; ?>
                            我的集客小店</a>
            </li>
            <li class="pm" id="fwzx_login">
            </li>
            <li class="pm"><a href="<?php echo url('app=member&act=home'); ?>"  target="_top" title="会员中心">会员中心</a></li>
            <li><a href="<?php echo url('app=apply&step=2&id=2'); ?>" id="shangjiaruizhu" target="_blank">商家入驻</a></li>

            <li><a href="index.php?app=pos">POS机刷卡入口</a></li>
            <li><span></span><a href="javascript:void(0);" onmousedown="www_zzjs_net(this,'http://www.dajike.com','大集客');">收藏大集客</a></li>
            <!--<li class="cart">-->
                <!--<div class="menu"><a href="<?php echo url('app=cart'); ?>"> <span class="menu-hd"><s></s>购物车<strong class="goods_num" id = "cart_kinds"><?php echo $this->_var['cart_goods_kinds']; ?></strong>种商品<i></i></span></a>-->
                    <!--&lt;!&ndash;<div class="menu-bd" id="top_cartlist"><img class="loading"  src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/loading.gif"/>&ndash;&gt;-->
                    <!--&lt;!&ndash;</div>&ndash;&gt;-->
                <!--</div>-->
            <!--</li>-->
            
            <!--<li class="favorite">-->
                <!--<div class="menu"><a class="menu-hd" href="<?php echo url('app=my_favorite'); ?>"  target="_top"  title="我的收藏">我的收藏-->
                    <!--<i></i></a>-->

                    <!--<div class="menu-bd">-->
                        <!--<ul>-->
                            <!--<li><a href="<?php echo url('app=my_favorite&type=goods'); ?>" target="_top" title="收藏的商品">收藏的商品</a></li>-->
                        <!--</ul>-->
                    <!--</div>-->
                <!--</div>-->
            <!--</li>-->
            
            <!--<li class="pm"><a href="<?php echo url('app=message&act=newpm'); ?>" target="_top">站内消息</a></li>-->
            <?php elseif ($this->_var['visitor']['member_type'] == '02' || $this->_var['visitor']['member_type'] == '03'): ?>
            <li class="user-center"><a href="index.php?app=service&p=service&tuoguan=1" target="_top">进入服务中心</a></li>
            <?php elseif ($this->_var['visitor']['member_type'] == '04'): ?>
            <li class="user-center"><a href="index.php?app=service&act=service&p=service&tuoguan=1" target="_top">进入采购中心</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<header id="topHeader">
    <div class="site-logo"><a href="<?php echo $this->_var['site_url']; ?>"><img src="<?php echo $this->res_base . "/" . 'images/logo.png'; ?>" class="pngFix"></a></div>
    <div id="search" class="search" style="display: none">
        <div class="details" id="details">
            <div id="a1" class="form" >
                <form action="<?php echo url('app=search'); ?>" onSubmit="return searchInput();" method="GET">
                    <input type="hidden" name="act" value="index"/>
                    <input type="hidden" name="app" value="search"/>

                    <div class="formstyle" >
                        <input name="keyword" id="keyword" type="text" class="textinput" value=""
                               onFocus="searchFocus(this)" onBlur="searchBlur(this)" maxlength="60" x-webkit-speech
                               lang="zh-CN" onwebkitspeechchange="foo()" x-webkit-grammar="builtin:search"/>
                        <input name="Submit" type="submit" class="search-button" value="">
                    </div>
                </form>
            </div>
            <div class="tag">
                <?php $_from = $this->_var['hot_keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'keyword');if (count($_from)):
    foreach ($_from AS $this->_var['keyword']):
?>
                <a href="<?php echo url('app=search&keyword=' . $this->_var['keyword']. ''); ?>"><?php echo $this->_var['keyword']; ?></a>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
        </div>
    </div>
</header>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/jquery.showLoading.js'; ?>" charset="utf-8"></script>
<link href="<?php echo $this->res_base . "/" . 'css/showLoading.css'; ?>" rel="stylesheet" type="text/css">


<?php if ($this->_var['member'] && $this->_var['member']['member_type'] == '01' && ( $this->_var['member']['spreader_type'] != 1 || $this->_var['member']['shop_name'] == null || $this->_var['member']['shop_name'] == '' )): ?>
    <script>
    (function($){
        $.fn.floatAd = function(options){
            var defaults = {
                imgSrc : "<?php echo $this->res_base . "/" . 'images/shop_ico6.png'; ?>", //漂浮图片路径
                url : "index.php?app=jkxd_portal", //图片点击跳转页
                openStyle : 0, //跳转页打开方式 1为新页面打开  0为当前页打开
                speed : 10 //漂浮速度 单位毫秒
            };
            var options = $.extend(defaults,options);
            var _target = options.openStyle == 1 ?  "target='_blank'" : '' ;
            var html = "<div id='float_ad' style='position:absolute;left:0px;top:0px;z-index:1000000;cleat:both;'>";
            html += "  <a href='index.php?app=jkxd_portal'>" +
                    "<img src='" + options.imgSrc + "' border='0' class='float_ad_img'/>" +
                    "</a> " +
                    "<a href='javascript:;' id='close_float_ad' style=''>X</a>";
            html += "</div>";
            $('body').append(html);
            function init(){
                var x = 0,y = 0
                var xin = true, yin = true
                var step = 1
                var delay = 10
                var obj=$("#float_ad")
                obj.find('img.float_ad_img').load(function(){
                    var float = function(){
                        var L = T = 0;
                        var OW = obj.width();//当前广告的宽
                        var OH = obj.height();//高
                        var DW = $(document).width(); //浏览器窗口的宽
                        var DH =  window.screen.availHeight-50;
                        x = x + step *( xin ? 1 : -1 );
                        if (x < L) {
                            xin = true; x = L
                        }
                        if (x > DW-OW-1){//-1为了ie
                            xin = false; x = DW-OW-1
                        }
                        y = y + step * ( yin ? 1 : -1 );
                        if (y > DH-OH-1) {
                            yin = false; y = DH-OH-1 ;
                        }
                        if (y < T) {
                            yin = true; y = T
                        }
                        var left = x ;
                        var top = y;
                        obj.css({'top':top,'left':left});
                    }
                    var itl = setInterval(float,options.speed);
                    $('#float_ad').mouseover(function(){clearInterval(itl)});
                    $('#float_ad').mouseout(function(){itl=setInterval(float, options.speed)} )
                });
                // 点击关闭
                $('#close_float_ad').live('click',function(){
                    $('#float_ad').hide();
                });
            }
            init();
        }; //floatAd
    })(jQuery);
    $(function(){
        //调用漂浮插件
        $("body").floatAd({
            imgSrc : '<?php echo $this->res_base . "/" . 'images/shop_ico6.png'; ?>',
            url : "index.php?app=jkxd_portal"
        });
    })
</script>
<?php endif; ?>



