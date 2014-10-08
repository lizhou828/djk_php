<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="<?php echo $this->res_base . "/" . 'css/public.css'; ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->res_base . "/" . 'css/index1.css'; ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->res_base . "/" . 'css/jike_shop.css'; ?>" rel="stylesheet" type="text/css" />
    <?php echo $this->_var['page_seo']; ?>

    <style type="text/css">
        body {
            _behavior: url(<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/csshover.htc);
        }</style>
	<LINK href="/favicon.ico" type="image/x-icon" rel=icon>
	<LINK href="/favicon.ico" type="image/x-icon" rel="shortcut icon">
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
    <script type="text/javascript" src="index.php?act=jslang"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/jquery.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/common.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript">
        $(function () {
            $("#keyword").blur();
            var url = "/index.php?app=default&act=get_vistor";
            $.getJSON(url,{},function(data) {
                   if(data['nick_name']!=null){
                       $("#login_name").html(data['nick_name']+"&nbsp;");
                   }
                    if (data['user_id'] ==0 ) {
                        $("#login").html("<a href='<?php echo url('app=member&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>'>[登录]</a>");
                        $("#register").html("&nbsp;<a href='<?php echo url('app=member&act=register&ret_url=' . $this->_var['ret_url']. ''); ?>'>[免费注册]</a>");
                    } else {
                        $("#login_out").html("<a href='<?php echo url('app=member&act=logout'); ?>'>[退出]</a>");

                        //if()shangjiaruizhu
                        $("#shangjiaruizhu").attr("href","index.php?app=my_goods&p=seller");
                    }

                    if (data['member_type'] =='02' || data['member_type']=='03') {
                        $("#quick-menu").html("");
                        var html="";
                        html+="<li style='padding-left: 100px;'>";
                        html+="<a href=\"index.php?app=service&p=service&tuoguan=1\" target=\"_top\" title=\"进入服务中心\">进入服务中心</a>";
                        html+="</li>";
                        $("#quick-menu").html(html);
                    } else if (data['member_type'] =='04') {
                        $("#quick-menu").html("");
                        var html="";
                        html+="<li >";
                        html+="<a href=\"index.php?app=service&p=service&tuoguan=1\" target=\"_top\" title=\"进入采购中心\">进入采购中心</a>";
                        html+="</li>";
                        $("#quick-menu").html(html);
                    }/*else{
                        var html="";
                        html+="<li class=\"pm\">";
                        html+="<a href=\"<?php echo url('app=service&act=register_service'); ?>\" target='_blank'><span style=\"color: #ff3300\">抢注服务中心</span></a><em class=\"em1\"></em>";
                        html+="</li>";
                        $(".quick-menu").html(html+$(".quick-menu").html());
                    }*/

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
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/index.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/public.js'; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/poshytip.js'; ?>" charset="utf-8"></script>
    
    <script type="text/javascript"  src="<?php echo $this->res_base . "/" . 'js/double_adv.js'; ?>"></script>
    <script type="text/javascript">
        //<!CDATA[
        var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
        var IMG_URL = "<?php echo $this->_var['img_url']; ?>";
        var REAL_SITE_URL = "<?php echo $this->_var['real_site_url']; ?>";
        var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
//        document.domain = '<?php echo $this->_var['domain']; ?>';
             //]]>
    </script>
    <?php echo $this->_var['_head_tags']; ?>

</head>
<body>

<div id="top">
<div class="w1200">
    <div class="left"><span class="ico"><a href="index.php?act=ydshop">移动商城</a></span><span id="login_name"></span>您好，欢迎来到<a href="<?php echo $this->_var['site_url']; ?>"><b>大集客网上商城</b></a>！<span id="login"></span><span id="register"></span><span id="login_out"></span> </div>
    <div class="right">
        <ul id="quick-menu">
            <li style="padding-right: 10px;">
                    <?php if ($this->_var['member']['user_id'] > 0 && $this->_var['member']['spreader_type'] == 1 && $this->_var['member']['shop_name'] != null): ?>

                           <a href="<?php echo $this->_var['SHOP_DOMAIN']; ?>/shops/index?id=<?php echo $this->_var['member']['user_id']; ?>" target="_blank">

                    <?php elseif ($this->_var['member']['user_id'] > 0 && ( $this->_var['member']['spreader_type'] != 1 || $this->_var['member']['shop_name'] == null || $this->_var['member']['shop_name'] == '' )): ?>

                             <a href="<?php echo $this->_var['SHOP_DOMAIN']; ?>/?apply=1" target="_blank">

                     <?php else: ?>

                                 <a href="index.php?app=member&act=login">

                    <?php endif; ?>
                    我的集客小店</a>
                   <?php if ($this->_var['vshop'] && $this->_var['vshop']['user_id'] > 0): ?>
                       <em></em>
                       <div class="asa" style="position: absolute; width: 101px; border: 1px solid #ddd; border-top: none; background: #F7F7F7; text-align: center;left: -9px;">
                           <a href="<?php echo $this->_var['SHOP_DOMAIN']; ?>/vshops/index?id=<?php if (! $this->_var['vshop']): ?><?php echo $this->_var['shopOwner']['user_id']; ?><?php else: ?><?php echo $this->_var['vshop']['user_id']; ?><?php endif; ?>" target="_blank">精品集客小店</a>
                       </div>
                   <?php endif; ?>
            </li>
            <li style=" color:#cfcfcf;">|</li>
            <li><a href="<?php echo url('app=member&act=home'); ?>" target="_blank">会员中心</a></li>
            <li style=" color:#cfcfcf;">|</li>
            <li><a href="<?php echo url('app=apply&step=2&id=2'); ?>" id="shangjiaruizhu" target="_blank">商家入驻</a></li>
            <li style=" color:#cfcfcf;">|</li>
            <li><a href="index.php?app=pos">POS机刷卡入口</a></li>
            <li style=" color:#cfcfcf;">|</li>
            <li><span></span><a href="javascript:void(0);" onmousedown="www_zzjs_net(this,'http://www.dajike.com','大集客');">收藏大集客</a></li>
        </ul>
    </div>
</div>
</div>

<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>
<script type="text/javascript">
    function apply_jkxd(){
        var uri =encodeURI("index.php?app=jkxd_portal&act=apply_jkxd");
        var id = 'apply_jkxd_page';
        var title = "申请开通集客小店";
        var width = '755';
        ajax_form(id, title, uri, width);
    }
</script>

