<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/jquery-1.6.2.min.js'; ?>"></script>
<script type="text/javascript">
    $(function(){
        $.get("index.php?app=cart&act=cart_kinds",function(data){
            $("#cart_kinds").html(data);
        });
        var keyword = $("#keyword").val();
        $("#keyword").blur(function(){
            keyword = $("#keyword").val();
            if( !keyword || keyword == null || keyword==" " || keyword.trim()=="" || typeof keyword =="undefined"){
                $("#keyword").val("请输入商品关键字，多个关键字以空格隔开");
            }
        });
        $("#keyword").focus(function(){
            $("#keyword").val("");
        });
        if('<?php echo $this->_var['type']; ?>'=='localHot'){
            $('#localHot').css({"background":"#7c0000"});
            $('#shopIndex').css({"background":"#331900"});
            $('#jingpin').css({"background":"#331900"});
        }else if('<?php echo $this->_var['type']; ?>'=='jingpin'){
            $('#localHot').css({"background":"#331900"});
            $('#shopIndex').css({"background":"#331900"});
            $('#jingpin').css({"background":"#7c0000"});
        }else if('<?php echo $this->_var['type']; ?>'=='search'){
        }else{
            $('#localHot').css({"background":"#331900"});
            $('#shopIndex').css({"background":"#7c0000"});
            $('#jingpin').css({"background":"#331900"});
        }
    });
    function search_in_shop(){
        var keywords =  $("#keyword").val();
        if(keywords==null || keywords =="" || keywords=="请输入商品关键字，多个关键字以空格隔开"){
            alert("请填写商品名称或关键字!");
            return;
        }else{
            var keyword=$("#keyword").val();
            $("#shop").val(keyword);
            $("#search_in_shop").submit();
        }
    }
    function search_in_website(){
        var keywords =  $("#keyword").val();
        if(keywords==null || keywords ==""){
            alert("请填写商品名称或关键字!");
            return;
        }else{
            var keyword=$("#keyword").val();
            $("#website").val(keyword);
            $("#search_in_website").submit();
        }
    }
</script>

<script type="text/javascript">
    function enterPress(e){ //传入 event
        var e = e || window.event;
        if(e.keyCode == 13){
            search_in_shop();
        }
    }
</script>




<div id="xiaod_bg">
    <div class="login">
        <div class="logo_shop">

            <div class="fe1">

                <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop&id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['shopOwner']['shop_name']; ?>的精品集客小店">
                    <?php if (strlen ( $this->_var['shopOwner']['shop_name'] ) > 14): ?><?php echo sub_str($this->_var['shopOwner']['shop_name'],10); ?><?php else: ?><?php echo $this->_var['shopOwner']['shop_name']; ?><?php endif; ?><span>的精品集客小店</span>
                </a>
                <img src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/v2.png" title="已认证">
            </div>
            <div class="fe2">
                <div class="xiaoh">集客小店号：<span style="font-family: '微软雅黑', Tahoma, Arial"><?php echo $this->_var['shopOwner']['user_id']; ?></span></div>
                <div class="shouc" onclick=""><a href="javascript:void(0);" onmousedown="www_zzjs_net(this,'<?php echo $this->_var['jkxd_site_url']; ?>','<?php echo $this->_var['shopOwner']['shop_name']; ?>的集客小店');">收藏小店</a></div>
                <div class="fenx">
                    一键分享
                    <em class="fenx2">
                        <div id="promotion_txt">
                            <div id="text1">
                                <span id="zxxTestArea">亲!快来看看我的集客小店，多多捧场哦!顺便悄悄的告诉你，你也可以免费拥有哦!<?php echo $this->_var['jkxd_site_url']; ?></span>
                            </div>
                            <div class="div1">
                                <!--<a href="javascript:copy_clip(document.getElementById('text1').innerText)"><div class="div11">复制内容</div></a>-->
                                <div class="div11" id="forLoadSwf">复制内容</div>
                                <span class="div22">您可以粘贴到QQ、QQ群，或者通过邮件，均可推广！</span>
                            </div>
                        </div>
                    </em>
                </div>
            </div>
        </div>

        
        <script src="<?php echo $this->res_base . "/" . 'js/swfobject.js'; ?>" type="text/javascript"></script>
        <script type="text/javascript">
            var copyCon = document.getElementById("zxxTestArea").innerHTML;
            var flashvars = {
                content: encodeURIComponent(copyCon),
                uri: '<?php echo $this->res_base . "/" . 'images/copy_button.png'; ?>'
            };
            var params = {
                wmode: "transparent",
                allowScriptAccess: "always"
            };
            swfobject.embedSWF("<?php echo $this->res_base . "/" . 'js/clipboard.swf'; ?>", "forLoadSwf", "64", "25", "9.0.0", null, flashvars, params);

            function copySuccess(){

                alert("复制成功！");
            }
        </script>
        


        
        <div class="search">
            <div class="k1">
                <form action="<?php echo url('app=jkxd_portal&act=vshop_list&type=search'); ?>" method="get" id="search_in_shop" >
                     <span class="sp1">
                        <input type="text" id="keyword" name="keyword" lang="zh-CN" value="<?php if ($this->_var['keyword'] && $this->_var['keyword'] != ' '): ?><?php echo $this->_var['keyword']; ?><?php else: ?>请输入商品关键字，多个关键字以空格隔开<?php endif; ?>" class="text" />
                    </span>
                    <input type="hidden"  name="app" value="jkxd_portal" />
                    <input type="hidden"  name="act" value="vshop_list" />
                    <input type="hidden" id="shop" name="keyword" />
                    <input type="hidden" id="shop" name="type" value="search" />
                    <input type="hidden" name="id" id="id" value="<?php echo $this->_var['shopOwner']['user_id']; ?>"/>
                    <span class="sp2_shop">
                        <input type="button" onclick="search_in_shop()" value="搜 索" class="buttom" />
                    </span>
                </form>
            </div>
            <div class="resou_shop">
                热搜：
                <a href="index.php?app=jkxd_portal&act=vshop_list&type=search&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&keyword=韩国">韩国</a>&nbsp;
                <a href="index.php?app=jkxd_portal&act=vshop_list&type=search&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&keyword=钱包">钱包</a>&nbsp;
                <a href="index.php?app=jkxd_portal&act=vshop_list&type=search&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&keyword=ipad">ipad</a>&nbsp;
                <a href="index.php?app=jkxd_portal&act=vshop_list&type=search&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&keyword=三星">三星</a>&nbsp;
            </div>
        </div>
        

        <div class="r0p">
            <div class="car"><a href="index.php?app=cart"></a><span class="span01"><span id="cart_kinds">0</span></span></div>
            <div class="font">
                <a href="index.php?app=jkxd_portal" target="_blank">集客小店是什么？</a>
                <a href="javascript:void(0)" onclick="apply_jkxd()" style=" text-align:left; padding-left:10px; width:106px;background:url('<?php echo $this->res_base . "/" . 'images/shop_ico33.png'; ?>') no-repeat;">我要开集客小店</a>
            </div>
        </div>
    </div>

    
    <script type="text/javascript">
         //   alert( " document.compatMode="+document.compatMode+"  , window.pageYOffset="+window.pageYOffset+" ,navigator.userAgent="+navigator.userAgent);
                var toTopDistance=148;//滚动条距离顶部的距离
                if(/Firefox/gi.test(navigator.userAgent)){
                    toTopDistance = 148;
                }else if(/Chrome/gi.test(navigator.userAgent)){
                    toTopDistance = 280;
                }
                window.onscroll=function(){
                    if( document.body.scrollTop + document.documentElement.scrollTop >= toTopDistance){
                        $(".pindao").css({"position":"fixed","top":"0","width":"100%","z-index":9999});
                    }else{
                        $(".pindao").css({"position":"","top":"","width":"100%"});
                    }
                };
    </script>
    <div class="pindao" style="background: none repeat scroll 0 0 #331900;">
        <div class="wshop_1200">
                    <ul>
                        <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop&id=<?php echo $this->_var['shopOwner']['user_id']; ?>">
                            <li id="shopIndex" <?php if ($this->_var['type'] == 'localHot' || $this->_var['type'] == 'jingpin' || $this->_var['type'] == 'search'): ?> onmouseover="this.style.background='#7c0000'" onmouseout="this.style.background='#331900'" <?php endif; ?>>店铺首页</li>

                        </a>
                        <?php if ($this->_var['temaiGoodsCount'] && $this->_var['temaiGoodsCount'] > 0): ?>
                        <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop_list&type=localHot&id=<?php echo $this->_var['shopOwner']['user_id']; ?>">
                            <li id="localHot" <?php if ($this->_var['type'] != 'localHot'): ?> onmouseover="this.style.background='#7c0000'" onmouseout="this.style.background='#331900'" <?php endif; ?>>本地特卖
                            </li>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop_list&type=jingpin&id=<?php echo $this->_var['shopOwner']['user_id']; ?>">
                            <li id="jingpin" <?php if ($this->_var['type'] != 'jingpin'): ?> onmouseover="this.style.background='#7c0000'" onmouseout="this.style.background='#331900'" <?php endif; ?>>大集客精品
                            </li>
                        </a>
                        <li style="float:right; background:#331900;" >积分收益：
                            <span id="jifen">
                                <?php if ($this->_var['jifen'] && $this->_var['jifen'] > 0): ?>
                                    <?php echo $this->_var['jifen']; ?>
                                <?php else: ?>
                                    0
                                <?php endif; ?>
                            </span>
                        </li>
                        <li style="float:right; background:#331900;">店主收益：￥
                            <?php if ($this->_var['jkxd_shouyi'] && $this->_var['jkxd_shouyi'] > 0): ?>
                                <?php echo number_format2($this->_var['jkxd_shouyi']); ?>
                            <?php else: ?>
                                0
                            <?php endif; ?>
                        </li>
                        <li style="font-family:Tahoma, Geneva, sans-serif; float:right; font-size:12px;">客服热线：400-728-1117</li>
                    </ul>
        </div>
    </div>
    

</div>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/jquery.ui.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js" ></script>
<script type="text/javascript">
    function apply_jkxd(){
        var uri =encodeURI("index.php?app=jkxd_portal&act=apply_jkxd");
        var id = 'apply_jkxd_page';
        var title = "申请开通集客小店";
        var width = '755';
        ajax_form(id, title, uri, width);
    }
</script>