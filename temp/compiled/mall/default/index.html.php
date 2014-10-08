<?php echo $this->fetch('header.html'); ?>

<DIV style="MARGIN: 0px auto; WIDTH: 1200px; DISPLAY: block" id=js_ads_banner_top>
    <A href="#" target=_blank><IMG src="<?php echo $this->res_base . "/" . 'images/1234567890.jpg'; ?>" width=1200 height=91></A>
</DIV>

<DIV style="MARGIN: 0px auto; WIDTH: 1200px; DISPLAY: none" id=js_ads_banner_top_slide>
    <A href="#" target=_blank><IMG src="<?php echo $this->res_base . "/" . 'images/123456789.jpg'; ?>" width=1200 height=350></A>
</DIV>

<SCRIPT type=text/javascript src="<?php echo $this->res_base . "/" . 'js/jquery.min.js'; ?>"> </SCRIPT>
<SCRIPT type=text/javascript src="<?php echo $this->res_base . "/" . 'js/lrtk.js'; ?>"> </SCRIPT>

<div id="center_w1200">
<?php echo $this->fetch('common_search.html'); ?>
<?php echo $this->fetch('common.html'); ?>
<!--<script src="http://58439.fy.kf.qycn.com/vclient/state.php?webid=58439" language="javascript" type="text/javascript"></script>-->
<script src="<?php echo $this->res_base . "/" . 'js/jquery-1.6.2.min.js'; ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        var ul = $(".lxfscroll ul");
        var li = $(".lxfscroll li");
        var tli = $(".lxfscroll-title li");
        var speed = 0;
        var autospeed = 4000;
        var i=1;
        var index = 0;
        var n = 0;
        /* 标题按钮事件 */
        function lxfscroll() {
            var index = tli.index($(this));
            tli.removeClass("cur");
            $(this).addClass("cur");

            ul.css({"left":"0px"});
            li.css({"left":"0px"});
            li.eq(index).css({"z-index":i});
            li.eq(index).css({"left":"750px"});
            ul.animate({left:"-750px"},speed);
            i++;
        };
        /* 自动轮换 */
        function autoroll() {
            if(n >=7) {
                n = 0;
                i= 1;
            }
            tli.removeClass("cur");
            tli.eq(n).addClass("cur");
            ul.css({"left":"0px"});
            li.css({"left":"0px"});
            li.eq(n).css({"z-index":i});
            li.eq(n).css({"left":"750px"});
            n++;
            i++;
            timer = setTimeout(autoroll, autospeed);
            ul.animate({left:"-750px"},speed);
        };


        /* 鼠标悬停即停止自动轮换 */
        function stoproll() {
            li.hover(function() {
                clearTimeout(timer);
                n = $(this).prevAll().length+1;
            }, function() {
                timer = setTimeout(autoroll, autospeed);
            });
            tli.hover(function() {
                clearTimeout(timer);
                n = $(this).prevAll().length+1;
            }, function() {
                timer = setTimeout(autoroll, autospeed);
            });
        };
        tli.mouseenter(lxfscroll);
        autoroll();
        stoproll();
    });
</script>
<style type="text/css">

    .lxfscroll {
        width:750px;
        margin-left:auto;
        margin-right:auto;
        position: relative;
        height: 347px;
        overflow: hidden;
    }

    .lxfscroll ul li {
        height: 347px;
        width: 750px;
        text-align: center;
        line-height: 347px;
        position: absolute;
        font-size: 40px;
        font-weight: bold;
    }
    .lxfscroll-title{
        width: 200px;
        margin-right: auto;
        margin-left: auto;
        position: absolute;
        top: 310px;
        left: 535px;
        z-index: 500;
    }
    .lxfscroll-title li{
        height: 20px;
        width: 25px;
        float: left;
        line-height: 20px;
        text-align: center;
        cursor: pointer;
        margin-top: 2px;
        /*margin-right: 5px;*/
        border: 1px solid #CCC;
        color: #c3c3c3;
    }
    .cur{
        color: #FFF;
        font-weight: bold; background:#001b34;
    }
    .lxfscroll ul {
        position: absolute;
    }
</style>

<div class="hot">热卖：
    <?php $_from = $this->_var['hot_category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ct');$this->_foreach['my_cate'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_cate']['total'] > 0):
    foreach ($_from AS $this->_var['ct']):
        $this->_foreach['my_cate']['iteration']++;
?><a href="<?php echo $this->_var['ct']['url']; ?>"
    <?php if (($this->_foreach['my_cate']['iteration'] - 1) == 0): ?>  class="cd0281e"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 1): ?> class="c1d5b9f"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 2): ?>class="c27a196"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 3): ?>class="ceaac44"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 4): ?>class="c666666"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 5): ?>class="c666666"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 6): ?>class="c92bd49"
    <?php elseif (($this->_foreach['my_cate']['iteration'] - 1) == 7): ?>class="c27a196"
    <?php endif; ?> ><?php echo $this->_var['ct']['name']; ?></a><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>

<div class="fl">
    <div class="menu_left">
        <div class="title"><a href="<?php echo url('app=category'); ?>" target="_blank" style="color: #fff">全部商品列表</a></div>
        <div class="con">
            <?php $_from = $this->_var['gcategorys_left']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['gcategory']['iteration']++;
?>
            <?php if (($this->_foreach['gcategory']['iteration'] - 1) < 8): ?>
            <?php if (! empty ( $this->_var['gcategory'] )): ?>
            <dl>
                <dd><a href="<?php echo url('app=search&cate_id=' . $this->_var['gcategory']['id']. ''); ?>"><?php echo sub_str($this->_var['gcategory']['value'],22); ?></a></dd>
                <dt>
                    <span><a href="<?php echo url('app=search&cate_id=' . $this->_var['gcategory']['children']['0']['id']. ''); ?>"><?php echo $this->_var['gcategory']['children']['0']['value']; ?></a></span>
                    <span><a href="<?php echo url('app=search&cate_id=' . $this->_var['gcategory']['children']['1']['id']. ''); ?>"><?php echo $this->_var['gcategory']['children']['1']['value']; ?></a></span>
                    <span><a href="<?php echo url('app=search&cate_id=' . $this->_var['gcategory']['children']['2']['id']. ''); ?>"><?php echo $this->_var['gcategory']['children']['2']['value']; ?></a></span>
                </dt>
                <em>
                    <dl class="ssp_no1">
                        <dd> <?php echo htmlspecialchars($this->_var['gcategory']['value']); ?> </dd>
                        <dt>
                            <?php $_from = $this->_var['gcategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?>
                            <i><a href="<?php echo url('app=search&cate_id=' . $this->_var['child']['id']. ''); ?>"><?php echo htmlspecialchars($this->_var['child']['value']); ?></a></i> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        </dt>
                    </dl>

                </em>
            </dl>
            <span class="menu_line"></span>
            <?php endif; ?>
            <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>
    <div class="banner">
        <div class="ban" id="zy_banner">
           <div class="lxfscroll">
               <ul>
               <?php $_from = $this->_var['huanden']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'hd');if (count($_from)):
    foreach ($_from AS $this->_var['hd']):
?>
               <li><a href="<?php echo $this->_var['hd']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['hd']['img']; ?>"  width="750" height="347"/></a></li>
               <!--<li><a href="<?php echo url('app=bdsh&pd_id=5'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/bdsh_banner.jpg'; ?>" width="750" height="347" /></a></li>-->
               <!--<li><a href="<?php echo url('app=article&act=view&article_id=55'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/jike_banner.jpg'; ?>" width="750" height="347" /></a></li>-->
               <!--<li><a href="<?php echo url('app=search&search_type=search&keyword=牛轧糖'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/banner01.jpg'; ?>" width="750" height="347" /></a></li>-->
               <!--<li><a href="<?php echo url('app=search&cate_id=1298'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/banner04.jpg'; ?>" width="750" height="347" /></a></li>-->
               <!--&lt;!&ndash;<li><a href="<?php echo url('app=search&cate_id=1248'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/gaogenxie_banner.jpg'; ?>" width="750" height="347" /></a></li>&ndash;&gt;-->
               <!--<li><a href="<?php echo url('app=search&cate_id=1245'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/banner03.jpg'; ?>" width="750" height="347" /></a></li>-->
               <!--<li><a href="<?php echo url('app=search&cate_id=1275'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/yinshi_banner.jpg'; ?>" width="750" height="347" /></a></li>-->
               <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
               <!--<li><a href="<?php echo url('app=search&cate_id=1236'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/fushi_banner.jpg'; ?>"  width="750" height="347"/></a></li>-->

               <!--<li><a href="<?php echo url('app=service&act=register_service'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/banner01.jpg'; ?>" width="750" height="347" /></a></li>-->
               <!--<li><a href="<?php echo url('app=service&act=register_service'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/fwzx.jpg'; ?>" width="750" height="347" /></a></li>-->
           </ul>
           </div>
            <div class="lxfscroll-title">
            <ul>
                <li class="cur">1</li>
                <li>2</li>
                <li>3</li>
                <li>4</li>
                <li>5</li>
                <li>6</li>
                <li>7</li>
            </ul>
        </div>
        </div>
        <div class="pic">
            <ul>
                <?php $_from = $this->_var['shouye_xiaotu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'xiaotu');$this->_foreach['xt'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['xt']['total'] > 0):
    foreach ($_from AS $this->_var['xiaotu']):
        $this->_foreach['xt']['iteration']++;
?>
                <li <?php if ($this->_foreach['xt']['iteration'] == 4): ?> style="margin-right:0px;" <?php endif; ?>><a href="<?php echo $this->_var['xiaotu']['link']; ?>" target="_blank" title="<?php echo $this->_var['xiaotu']['title']; ?>"><img src="<?php echo $this->_var['xiaotu']['img']; ?>" width="180" height="190" /></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
    </div>
    <div class="f_right">
        <div class="fl11"> <?php $_from = $this->_var['shouye_huandeng_right']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'hr');$this->_foreach['sr'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sr']['total'] > 0):
    foreach ($_from AS $this->_var['hr']):
        $this->_foreach['sr']['iteration']++;
?> <?php if ($this->_foreach['sr']['iteration'] == 1): ?><a href="<?php echo $this->_var['hr']['link']; ?>"><img src="<?php echo $this->_var['hr']['img']; ?>" width="220" height="65" /></a><?php endif; ?><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="fl2">
            <h2><span><a href="<?php echo url('app=article&cate_id=2'); ?>" target="_blank">更多>></a></span>商城公告</h2>
            <div class="con" style="overflow: hidden;">
                <ul>
                    <?php $_from = $this->_var['news_ad']['notices']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'article');$this->_foreach['notices'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['notices']['total'] > 0):
    foreach ($_from AS $this->_var['article']):
        $this->_foreach['notices']['iteration']++;
?>
                    <?php if (($this->_foreach['notices']['iteration'] - 1) < 5): ?>
                        <li>
                            <a target="_blank" href="<?php echo url('app=article&act=view&article_id=' . $this->_var['article']['article_id']. ''); ?>" title="<?php echo htmlspecialchars($this->_var['article']['title']); ?>"><?php echo htmlspecialchars($this->_var['article']['title']); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
        <div class="fl3">
            <span style="height: 32px;line-height: 32px;">大集客官方微信</span>
            <div class="wenxin" style="width: 174px;text-align: center;"><img src="<?php echo $this->res_base . "/" . 'images/qrcode_for_gh_1d111f902492_258.jpg'; ?>" width="174" height="174" />微信扫一扫 随时随地购物
            </div>
        </div>
        <div class="fl4"><?php $_from = $this->_var['shouye_huandeng_right']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'hr');$this->_foreach['sr'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sr']['total'] > 0):
    foreach ($_from AS $this->_var['hr']):
        $this->_foreach['sr']['iteration']++;
?> <?php if ($this->_foreach['sr']['iteration'] == 2): ?> <a href="<?php echo $this->_var['hr']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['hr']['img']; ?>" width="220" height="60" /></a><?php endif; ?><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </div>
    </div>
</div>

<div class="h1_banner"><?php $_from = $this->_var['shouye_top']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'top');if (count($_from)):
    foreach ($_from AS $this->_var['top']):
?><a href="<?php echo $this->_var['top']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['top']['img']; ?>" width="1200" height="100"></a><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>

<div class="fl1" style="border-top:2px solid #27a196">
    <div class="f1_left">
        <div class="top"> <?php $_from = $this->_var['layer_1']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay1']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay1']['iteration']++;
?><?php if ($this->_foreach['lay1']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['hgg_category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('hgg', 'categoryfacher');if (count($_from)):
    foreach ($_from AS $this->_var['hgg'] => $this->_var['categoryfacher']):
?>
                <li><a href="<?php echo $this->_var['categoryfacher']; ?>" target="_blank"><?php echo $this->_var['hgg']; ?></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_1']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay1']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay1']['iteration']++;
?><?php if ($this->_foreach['lay1']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>"  target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module1']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods1']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods1']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods1']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_1_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay1']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay1']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
              <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>">查看更多品牌</a></li>
        </ul>
    </div>
</div>


<div class="fl1">
    <div class="f1_left">
        <div class="top"> <?php $_from = $this->_var['layer_2']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay2'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay2']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay2']['iteration']++;
?><?php if ($this->_foreach['lay2']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module2']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>
                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_2']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay2'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay2']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay2']['iteration']++;
?><?php if ($this->_foreach['lay2']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module2']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods1']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods1']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods2']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
                <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
                <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
                <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_2_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay2'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay2']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay2']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>

<div class="fl1" style="border-top:2px solid #1d5b9f">
    <div class="f1_left">
        <div class="top"><?php $_from = $this->_var['layer_3']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay3'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay3']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay3']['iteration']++;
?><?php if ($this->_foreach['lay3']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module3']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>

                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_3']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay3'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay3']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay3']['iteration']++;
?><?php if ($this->_foreach['lay3']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module3']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods3'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods3']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods3']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods3']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_3_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay3'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay3']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay3']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>

<div class="fl1" style="border-top:2px solid #eaac44">
    <div class="f1_left">
        <div class="top"><?php $_from = $this->_var['layer_4']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay4'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay4']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay4']['iteration']++;
?><?php if ($this->_foreach['lay4']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module4']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>
                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_4']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay4'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay4']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay4']['iteration']++;
?><?php if ($this->_foreach['lay4']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module4']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods4'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods4']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods4']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods4']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_4_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay4'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay4']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay4']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>

<div class="fl1" style="border-top:2px solid #92bd49">
    <div class="f1_left">
        <div class="top"><?php $_from = $this->_var['layer_5']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay5'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay5']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay5']['iteration']++;
?><?php if ($this->_foreach['lay5']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module5']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>
                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_5']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay5'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay5']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay5']['iteration']++;
?><?php if ($this->_foreach['lay5']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module5']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods3'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods3']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods3']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods3']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_5_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay5'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay5']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay5']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>


<div class="fl1" style="border-top:2px solid #27a196">
    <div class="f1_left">
        <div class="top"><?php $_from = $this->_var['layer_6']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay6'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay6']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay6']['iteration']++;
?><?php if ($this->_foreach['lay6']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" title="<?php echo $this->_var['layer']['des']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module6']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>
                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_6']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay6'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay6']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay6']['iteration']++;
?><?php if ($this->_foreach['lay6']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" title="<?php echo $this->_var['layer']['des']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module6']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods6'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods6']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods6']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods6']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_6_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay6'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay6']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay6']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>


<div class="fl1" style="border-top:2px solid #d0281e">
    <div class="f1_left">
        <div class="top"><?php $_from = $this->_var['layer_7']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay7'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay7']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay7']['iteration']++;
?><?php if ($this->_foreach['lay7']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" title="<?php echo $this->_var['layer']['des']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module7']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>
                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_7']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay7'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay7']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay7']['iteration']++;
?><?php if ($this->_foreach['lay7']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" title="<?php echo $this->_var['layer']['des']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module7']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods7'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods7']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods7']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods7']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_7_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay7'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay7']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay7']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>


<div class="fl1" style="border-top:2px solid #1d5b9f">
    <div class="f1_left">
        <div class="top"><?php $_from = $this->_var['layer_8']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay8'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay8']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay8']['iteration']++;
?><?php if ($this->_foreach['lay8']['iteration'] == 1): ?><a href="<?php echo $this->_var['layer']['link']; ?>" title="<?php echo $this->_var['layer']['des']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="108" /></a><?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="cen">
            <ul>
                <?php $_from = $this->_var['goods_module8']['gcategorys_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categoryfacher');$this->_foreach['my_father'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_father']['total'] > 0):
    foreach ($_from AS $this->_var['categoryfacher']):
        $this->_foreach['my_father']['iteration']++;
?>
                <?php if ($this->_foreach['my_father']['iteration'] < 2): ?>
                <?php $_from = $this->_var['categoryfacher']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'facher');$this->_foreach['fa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fa']['total'] > 0):
    foreach ($_from AS $this->_var['facher']):
        $this->_foreach['fa']['iteration']++;
?>
                <?php $_from = $this->_var['facher']['childcate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['child']['iteration']++;
?>
                <?php if (($this->_foreach['child']['iteration'] - 1) < 8): ?>
                <li><a href="<?php echo url('app=search&cate_id=' . $this->_var['item']['cate_id']. ''); ?>"><?php echo $this->_var['item']['cate_name']; ?></a></li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
        <div class="bottom"><?php $_from = $this->_var['layer_8']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay8'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay8']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay8']['iteration']++;
?><?php if ($this->_foreach['lay8']['iteration'] == 2): ?> <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>"><img src="<?php echo $this->_var['layer']['img']; ?>" width="210" height="135" /></a> <?php endif; ?> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
    </div>
    <div class="f1_center">
        <ul>
            <?php $_from = $this->_var['goods_module8']['img_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods3'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods3']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods3']['iteration']++;
?>
            <li <?php if (($this->_foreach['goods3']['iteration'] - 1) > 4): ?> style="border-bottom:none"<?php endif; ?>>
            <div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></a></div>
            <div class="text"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" target="_blank"><?php echo sub_str($this->_var['goods']['goods_name'],35); ?></a></div>
            <div class="jiage"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="f1_right">
        <ul>
            <?php $_from = $this->_var['shouye_layer_8_brand']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'layer');$this->_foreach['lay8'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lay8']['total'] > 0):
    foreach ($_from AS $this->_var['layer']):
        $this->_foreach['lay8']['iteration']++;
?>
            <li><a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['layer']['img']; ?>" width="98" height="35" /></a>
                <a href="<?php echo $this->_var['layer']['link']; ?>" target="_blank" title="<?php echo $this->_var['layer']['des']; ?>">&nbsp;<?php echo sub_str($this->_var['layer']['des'],15); ?></a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li class="more"><a href="<?php echo url('app=brand'); ?>" target="_blank">查看更多品牌</a></li>
        </ul>
    </div>
</div>


<?php echo $this->fetch('footer.html'); ?>