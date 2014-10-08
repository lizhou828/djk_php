<?php echo $this->fetch('header.html'); ?>



<?php echo $this->fetch('jkxd_portal.header.html'); ?>
<script>
    function turn(id) {
        if (id == "dajike") {
            $("#dajike").show();
            $("#hgg").hide();
            $("#more").attr("href", "/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=djk");
            $("#dajike_tab").css("background", "#666666");
            $("#dajike_tab").css("color", "#fff");
            $("#hgg_tab").css("background", "#fff")
            $("#hgg_tab").css("color", "#000");
            $("#hgg_count").hide();
            $("#dajike_count").show();
        } else {
            $("#dajike").hide();
            $("#hgg").show();
            $("#more").attr("href", "/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=hgg");
            $("#hgg_tab").css("color", "#fff");
            $("#hgg_tab").css("background", "#666666");
            $("#dajike_tab").css("color", "#000");
            $("#dajike_tab").css("background", "#fff")
            $("#hgg_count").show();
            $("#dajike_count").hide();
        }
    }
</script>

<div id="center_w1200">
    
    <?php if ($this->_var['page_info'] && $this->_var['page_info']['goodsList'] > 0 && count ( $this->_var['page_info']['goodsList'] ) > 0): ?>
    <div class="xiaod">
        <div class="top">
            <div class="biti"></div>
            <div class="duo">
                <a>共 <span> <?php echo $this->_var['page_info']['item_count']; ?></span> 件商品</a>
                <a href="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>" style=" padding-left:10px;">更多<img src="<?php echo $this->res_base . "/" . 'images/shop_ico.png'; ?>" width="7" height="7" style="margin-left:5px;" /></a>
            </div>
        </div>
        <div class="bottom">
            <ul>
                <?php $_from = $this->_var['page_info']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                    <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> style="padding-right:0px;margin-right:0px;"<?php endif; ?>>
                        <div class="pic">
                            <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank">
                                <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="220" height="220" alt="<?php echo $this->_var['goods']['goods_name']; ?>" />
                            </a>

                        </div>
                        <div class="text"><a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],60); ?></a></div>
                        <div class="jiage">￥<span><?php echo $this->_var['goods']['price']; ?></span>
                            <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                            <em class="em1">奖励：￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></em>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

            </ul>
        </div>
    </div>
    <?php else: ?>
    <div class="xiaod">
        <div class="top">
            <div class="biti"></div>
            <div class="duo">
                <a>共 <span> <?php echo $this->_var['recommendHotGoodsCount']; ?></span> 件商品</a>
                <a href="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>" style=" padding-left:10px;">更多<img src="<?php echo $this->res_base . "/" . 'images/shop_ico.png'; ?>" width="7" height="7" style="margin-left:5px;" /></a>
            </div>
        </div>
        <div class="bottom">
            <ul>
                <?php $_from = $this->_var['recommendHotGoodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> style="padding-right:0px;margin-right:0px;"<?php endif; ?>>
                <div class="pic">
                    <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank">
                        <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" alt="<?php echo $this->_var['goods']['goods_name']; ?>" width="220" height="220" />
                    </a>
                </div>
                <div class="text"><a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],60); ?></a></div>
                <div class="jiage">￥<span><?php echo $this->_var['goods']['price']; ?></span>
                    <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                         <em class="em1">奖励：￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></em>
                    <?php endif; ?>
                </div>
                </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="clear"></div>
    
    <div class="shangc">
        <div class="ttp">
            <div class="left">
                <a id="dajike_tab" href="javascript:turn('dajike');" class="dq">大集客商城</a>
                <a id="hgg_tab" href="javascript:turn('hgg');">韩国直购</a>
            </div>
            <div class="duo">
                <a>共 <span id="dajike_count"><?php echo $this->_var['recommendGoodsCount']; ?></span>
                    <span id="hgg_count" style="display: none"><?php echo $this->_var['recommendHggGoodsCount']; ?></span>
                    件商品</a>
                <a id="more" href="/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=djk" style=" padding-left:10px;">更多<img src="<?php echo $this->res_base . "/" . 'images/shop_ico.png'; ?>" width="7" height="7" style="margin-left:5px;" /></a>
            </div>
        </div>

        
        <div id="dajike" class="bottom">
            <ul>
                <?php $_from = $this->_var['recommendGoodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'recommendGoods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['recommendGoods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                    <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> style="padding-right:0px;margin-right:0px;"<?php endif; ?>>
                        <div class="pic">
                            <a href="index.php?app=goods&id=<?php echo $this->_var['recommendGoods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['recommendGoods']['goods_name']; ?>">
                                <img src="<?php $imgarr = explode('.',$this->_var['recommendGoods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" alt="<?php echo $this->_var['recommendGoods']['goods_name']; ?>" width="220" height="220" />
                            </a>

                        </div>
                        <div class="text">
                            <a href="index.php?app=goods&id=<?php echo $this->_var['recommendGoods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['recommendGoods']['goods_name']; ?>">
                                <?php echo sub_str($this->_var['recommendGoods']['goods_name'],60); ?>
                            </a>
                        </div>
                        <div class="jiage">￥<span><?php echo $this->_var['recommendGoods']['price']; ?></span>
                            <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                                <em class="em1">奖励：￥<?php echo number_format2($this->_var['recommendGoods']['yongjin']); ?></em>
                            <?php endif; ?>

                        </div>
                    </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

            </ul>
        </div>
        

        
        <div id="hgg" class="bottom" style="display: none">
            <ul>
                <?php $_from = $this->_var['recommendHggGoodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'recommendGoods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['recommendGoods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> style="padding-right:0px;margin-right:0px;"<?php endif; ?>>
                <div class="pic">
                    <a href="index.php?app=goods&id=<?php echo $this->_var['recommendGoods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['recommendGoods']['goods_name']; ?>">
                        <img src="<?php $imgarr = explode('.',$this->_var['recommendGoods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" alt="<?php echo $this->_var['recommendGoods']['goods_name']; ?>" width="220" height="220" />
                    </a>

                </div>
                <div class="text">
                    <a href="index.php?app=goods&id=<?php echo $this->_var['recommendGoods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" title="<?php echo $this->_var['recommendGoods']['goods_name']; ?>">
                        <?php echo sub_str($this->_var['recommendGoods']['goods_name'],60); ?>
                    </a>
                </div>
                <div class="jiage">￥<span><?php echo $this->_var['recommendGoods']['price']; ?></span>
                    <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                        <em class="em1">奖励：￥<?php echo number_format2($this->_var['recommendGoods']['yongjin']); ?></em>
                    <?php endif; ?>
                </div>
                </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
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