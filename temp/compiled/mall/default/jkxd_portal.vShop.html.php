<?php echo $this->fetch('header.html'); ?>



<?php echo $this->fetch('jkxd_portal.vShop_head.html'); ?>


<div id="center_w1200">
    
    <?php if ($this->_var['bendi_temai'] && $this->_var['bendi_temai']['goodsList'] && count ( $this->_var['bendi_temai']['goodsList'] ) > 0): ?>
        <div class="xiaod">
            <div class="top">
                <div style="float: left; height:40px;width:1030px;margin: 16px 0 0 18px;background: url('<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/shop_pic14.jpg') no-repeat">
                    <span style="color: #27A196;float: right;height:40px;padding-top: 9px;">
                        <?php $_from = $this->_var['bendi_temai']['cates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');if (count($_from)):
    foreach ($_from AS $this->_var['category']):
?>
                            <?php if ($this->_foreach['cate_index']['iteration'] <= 5): ?>
                                <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop_list&type=localHot&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&goodCateId=<?php echo $this->_var['category']['cate_id']; ?>">
                                    <?php echo myeval($this->_var['category']['cate_name'],"substr(value, strpos(value,'	'))"); ?>&nbsp;&nbsp;
                                </a>
                            <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </span>
                </div>


                <div class="duo">
                    <a>共 <span> <?php echo $this->_var['bendi_temai']['item_count']; ?></span> 件商品</a>
                    <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop_list&type=localHot&id=<?php echo $this->_var['shopOwner']['user_id']; ?>" style=" padding-left:10px;">
                        更多<img src="<?php echo $this->res_base . "/" . 'images/shop_ico.png'; ?>" width="7" height="7" style="margin-left:5px;" />
                    </a>
                </div>
            </div>
            <div class="vshop">
                <ul>
                    <?php if ($this->_var['bendi_temai'] && $this->_var['bendi_temai']['goodsList'] && count ( $this->_var['bendi_temai']['goodsList'] ) > 0): ?>
                        <?php $_from = $this->_var['bendi_temai']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                            <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> class="laaa" style="padding-right:0px; margin-right:0px;"<?php endif; ?>>
                                <div class="pic" style="position: relative;">
                                    <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>&vshop=1" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank">
                                        <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="220" height="220" alt="<?php echo $this->_var['goods']['goods_name']; ?>" />
                                    </a>
                                    <em style="position: absolute;left: 5px;top: 10px; "><img src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/ico_18.png" alt="精品集客小店-本地特卖商品"/></em>
                                </div>
                                <div class="text">
                                    <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>&vshop=1" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank">
                                        <?php echo sub_str($this->_var['goods']['goods_name'],60); ?>
                                    </a>
                                </div>
                                <div class="jiage">￥<span><?php echo $this->_var['goods']['price']; ?></span>
                                    <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                                    <em class="em1">奖励：￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></em>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <?php else: ?>
                        <div style="text-align: center;padding-top: 50px;padding-bottom: 50px;">没有相关商品!</div>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="clear"></div>





    
    <div class="shangc">
        <div class="ttp">
            <div class="left">
                <a id="dajike_tab" href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop_list&type=jingpin&id=<?php echo $this->_var['shopOwner']['user_id']; ?>"
                   class="dq" style="background: none repeat scroll 0 0 #B10000;">
                    大集客精品
                </a>
            </div>
            <div class="duo">
                <a>共 <span id="dajike_count"><?php echo $this->_var['djk_jingpin']['item_count']; ?></span>件商品</a>
                <a id="more" href="<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=vshop_list&type=jingpin&id=<?php echo $this->_var['shopOwner']['user_id']; ?>" style=" padding-left:10px;">更多<img src="<?php echo $this->res_base . "/" . 'images/shop_ico.png'; ?>" width="7" height="7" style="margin-left:5px;" /></a>
            </div>
        </div>

        <div id="dajike" class="bottom">
            <ul>
                <?php if ($this->_var['djk_jingpin'] && $this->_var['djk_jingpin']['goodsList'] && count ( $this->_var['djk_jingpin']['goodsList'] ) > 0): ?>
                    <?php $_from = $this->_var['djk_jingpin']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                        <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> style="padding-right:0px;margin-right:0px;"<?php endif; ?>>
                            <div class="pic">
                                <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>&vshop=1" title="<?php echo $this->_var['goods']['goods_name']; ?>">
                                    <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" alt="<?php echo $this->_var['goods']['goods_name']; ?>" width="220" height="220" />
                                </a>

                            </div>
                            <div class="text">
                                <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>&vshop=1" title="<?php echo $this->_var['goods']['goods_name']; ?>">
                                    <?php echo sub_str($this->_var['goods']['goods_name'],60); ?>
                                </a>
                            </div>
                            <div class="jiage">￥<span><?php echo $this->_var['goods']['price']; ?></span>
                                <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                                    <em class="em1">奖励：￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></em>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php else: ?>
                    <div style="text-align: center;padding-top: 50px;padding-bottom: 50px;">没有相关商品!</div>
                <?php endif; ?>


            </ul>
        </div>
    </div>
    



</div>



<?php if ($this->_var['user'] && $this->_var['user']['spreader_type'] == 1 && $this->_var['user']['shop_name'] != null && $this->_var['user']['shop_name'] != ''): ?>
<?php else: ?>
<script>
    (function($){
        $.fn.floatAd = function(options){
            var defaults = {
                imgSrc : "<?php echo $this->res_base . "/" . 'images/shop_ico6.png'; ?>", //漂浮图片路径
                url : "<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=apply_jkxd", //图片点击跳转页
                openStyle : 0, //跳转页打开方式 1为新页面打开  0为当前页打开
                speed : 10 //漂浮速度 单位毫秒
            };
            var options = $.extend(defaults,options);
            var _target = options.openStyle == 1 ?  "target='_blank'" : '' ;
            var html = "<div id='float_ad' style='position:absolute;left:0px;top:0px;z-index:1000000;cleat:both;'>";
            html += "  <a href='javascript:void(0)'" + _target + " onclick=apply_jkxd(" +
                    ")>" +
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
            url : "<?php echo $this->_var['site_url']; ?>/index.php?app=jkxd_portal&act=apply_jkxd"
        });
    })
</script>
<?php endif; ?>



<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/jquery.ui.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js" ></script>
<script type="text/javascript">
    function apply_jkxd(){
        var uri =encodeURI("index.php?app=jkxd_portal&act=apply_jkxd&id=<?php echo $this->_var['shopOwner']['user_id']; ?>");
        var id = 'apply_jkxd_page';
        var title = "申请开通集客小店";
        var width = '755';
        ajax_form(id, title, uri, width);
    }
    function qq_setPassword(){
        var uri =encodeURI("index.php?app=jkxd_portal&act=qq_setPasswordPage&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>");
        var id = 'qq_setPassword';
        var title = "设置集客小店登录密码";
        var width = '780';
        ajax_form(id, title, uri, width);
    }
</script>


<?php if ($this->_var['user'] && ( $this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id'] )): ?>

<?php if ($this->_var['user']['im_qq'] != null && $this->_var['user']['im_qq'] != '' && $this->_var['user']['password'] == ''): ?>
<script type="text/javascript">
    $(function(){
        qq_setPassword();
    });
</script>
<?php endif; ?>



<?php endif; ?>




<?php echo $this->fetch('footer.html'); ?>