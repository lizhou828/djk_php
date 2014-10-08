<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>大集客-移动商城</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/public.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>

    <style type="text/css">
        *{margin:0;padding:0;}
        body{font-size:12px;color:#222;font-family:Verdana,Arial,Helvetica,sans-serif;background:#f0f0f0;}
        .clearfix:after{content: ".";display: block;height: 0;clear: both;visibility: hidden;}
        .clearfix{zoom:1;}
        ul,li{list-style:none;}
        img{border:0;}
        .wrapper{width:310px;margin:0 auto;padding-bottom:50px;}
        h1{height:50px;line-height:50px;font-size:22px;font-weight:normal;font-family:"Microsoft YaHei",SimHei;margin-bottom:20px;}
            /* focus */
        #focus{width:310px;height:212px;overflow:hidden;position:relative;}
        #focus ul{height:212px;position:absolute;}
        #focus ul li{float:left;width:310px;height:212px;overflow:hidden;position:relative;background:#000;}
        #focus ul li div{position:absolute;overflow:hidden;}
        #focus .btnBg{position:absolute;width:310px;height:20px;left:0;bottom:0;}
        #focus .btn{position:absolute;width:290px;height:10px;padding:5px 10px;right:0;bottom:0;text-align:right;}
        #focus .btn span{display:inline-block;_display:inline;_zoom:1;width:5px;height:10px;_font-size:0;margin-left:5px;cursor:pointer;background:#666;}
        #focus .btn span.on{background:#fff;}
        #focus .preNext{width:45px;height:100px;position:absolute;top:55px;background:url(<?php echo $this->_var['site_url']; ?>/weixin/templates/images/sprite.png) no-repeat 0 0;cursor:pointer;}
        #focus .pre{left:0;}
        #focus .next{right:0;background-position:right top;}
    </style>

    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript">
        $(function() {
            var sWidth = $("#focus").width(); //获取焦点图的宽度（显示面积）
            var len = $("#focus ul li").length; //获取焦点图个数
            var index = 0;
            var picTimer;

            //以下代码添加数字按钮和按钮后的半透明条，还有上一页、下一页两个按钮
            var btn = "<div class='btnBg'></div><div class='btn'>";
            for(var i=0; i < len; i++) {
                btn += "<span></span>";
            }
            btn += "";
            $("#focus").append(btn);
            $("#focus .btnBg").css("opacity",0.5);

            //为小按钮添加鼠标滑入事件，以显示相应的内容
            $("#focus .btn span").css("opacity",0.4).mouseover(function() {
                index = $("#focus .btn span").index(this);
                showPics(index);
            }).eq(0).trigger("mouseover");

            //上一页、下一页按钮透明度处理
            $("#focus .preNext").css("opacity",0.2).hover(function() {
                $(this).stop(true,false).animate({"opacity":"0.5"},300);
            },function() {
                $(this).stop(true,false).animate({"opacity":"0.2"},300);
            });

            //上一页按钮
            $("#focus .pre").click(function() {
                index -= 1;
                if(index == -1) {index = len - 1;}
                showPics(index);
            });

            //下一页按钮
            $("#focus .next").click(function() {
                index += 1;
                if(index == len) {index = 0;}
                showPics(index);
            });

            //本例为左右滚动，即所有li元素都是在同一排向左浮动，所以这里需要计算出外围ul元素的宽度
            $("#focus ul").css("width",sWidth * (len));

            //鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
            $("#focus").hover(function() {
                clearInterval(picTimer);
            },function() {
                picTimer = setInterval(function() {
                    showPics(index);
                    index++;
                    if(index == len) {index = 0;}
                },2000); //此4000代表自动播放的间隔，单位：毫秒
            }).trigger("mouseleave");

            //显示图片函数，根据接收的index值显示相应的内容
            function showPics(index) { //普通切换
                var nowLeft = -index*sWidth; //根据index值计算ul元素的left值
                $("#focus ul").stop(true,false).animate({"left":nowLeft},300); //通过animate()调整ul元素滚动到计算出的position
                //$("#focus .btn span").removeClass("on").eq(index).addClass("on"); //为当前的按钮切换到选中的效果
                $("#focus .btn span").stop(true,false).animate({"opacity":"0.4"},300).eq(index).stop(true,false).animate({"opacity":"1"},300); //为当前的按钮切换到选中的效果
            }
        });

    </script>
</head>

<body>
<div id="w_3202">
    <div class="search">
        <span class="font">大集客</span>
        <?php echo $this->fetch('search.form.html'); ?>
    </div>
    <div class="w_banner_pic"> <div class="wrapper">
        <div id="focus">
            <ul>
                <?php $_from = $this->_var['lunbo']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'lb');$this->_foreach['lb'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['lb']['total'] > 0):
    foreach ($_from AS $this->_var['lb']):
        $this->_foreach['lb']['iteration']++;
?>
                    <?php if (($this->_foreach['lb']['iteration'] - 1) < 5): ?>
                        <li>
                            <a href="<?php echo $this->_var['lb']['link']; ?>">
                                <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['lb']['img']; ?>" alt="<?php echo $this->_var['lb']; ?>" />
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
    </div></div>
    <div class="shortcut">
            <dl class="usl">
                <dd ><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_safecenter&act=bindIndex">
                    <img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/ico21.png" width="42" height="42" /></a></dd>
                <dt><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_safecenter&act=bindIndex">绑定手机</a></dt>
            </dl>
            <dl class="usl">
                <dd ><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=my_jkxd">
                    <img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/ico22.png" width="42" height="42" /></a></dd>
                <dt><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=my_jkxd">集客小店</a></dt>
            </dl>
            <dl class="usl">
                <dd ><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user']['user_id']; ?>&status=all_orders">
                    <img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/ico23.png" width="42" height="42" /></a></dd>
                <dt><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all">订单查询</a></dt>
            </dl>
            <dl class="usl">
                <dd ><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_account&act=shouyi">
                    <img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/ico24.png" width="42" height="42" /></a></dd>
                <dt><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_account&act=shouyi">查询收益</a></dt>
            </dl>
    </div>
    <div class="shop">
        <div class="left_pic"><a href="<?php echo $this->_var['brand1']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
            <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['brand1']['img']; ?>" width="193" height="264" />
        </a></div>
        <div class="right_sp">
            <?php $_from = $this->_var['tuijian']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tj');$this->_foreach['tj'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tj']['total'] > 0):
    foreach ($_from AS $this->_var['tj']):
        $this->_foreach['tj']['iteration']++;
?>
            <?php if (($this->_foreach['tj']['iteration'] - 1) == 1): ?>
                <dl class="dlis" style="margin-top: 10px">
            <?php else: ?>
                <dl class="dlis">
            <?php endif; ?>
                    <dd>
                            <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/0/<?php echo $this->_var['tj']['goods_id']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                            <img src="<?php echo $this->_var['img_url']; ?>/<?php echo $this->_var['tj']['default_image']; ?>" width="100" height="100" /></a></dd>
                    <dt><?php echo price_format($this->_var['tj']['price']); ?></dt>
                </dl>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>

    <div class="luyisi" style="height:150px;">
        <div class="titl" style=" background:#27a196;"><a href="index.php?app=category&act=hgg" style="width: 298px;height: 29px;display: block"><strong>进口商品</strong><span class="ssp">真正时尚韩式先锋 ></span></a></div>
        <div class="chp">
            <ul>
                <?php $_from = $this->_var['jksp']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'jks');if (count($_from)):
    foreach ($_from AS $this->_var['jks']):
?>
                    <li style="margin-right:3px;line-height:22px; text-align:center;">
                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/0/<?php echo $this->_var['jks']['goods_id']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                            <img src="<?php echo $this->_var['img_url']; ?>/<?php echo $this->_var['jks']['default_image']; ?>" width="100" height="100" />￥<?php echo $this->_var['jks']['price']; ?></a>
                    </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
    </div>

    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#d0281e;"><a href="index.php?app=category&act=index1&cate_id=1276" style="width: 298px;height: 29px;display: block"><strong>家用电器</strong><span class="ssp">让家的感觉更好 ></span></a></div>
        <div class="chad">
            <div class="left_font">
                <ul>
                    <?php $_from = $this->_var['jydqs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'jyd');$this->_foreach['jyd'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['jyd']['total'] > 0):
    foreach ($_from AS $this->_var['jyd']):
        $this->_foreach['jyd']['iteration']++;
?>
                        <?php if (($this->_foreach['jyd']['iteration'] - 1) < 6): ?>
                             <li><a href="<?php echo $this->_var['jyd']['link']; ?>"><?php echo $this->_var['jyd']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <div class="right_p">
                <a href="<?php echo $this->_var['jydqBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['jydqBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
        </div>
    </div>
    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#1d5b9f;"><a href="index.php?app=category&act=index1&cate_id=1250" style="width: 298px;height: 29px;display: block"><strong>美容化妆</strong><span class="ssp">秋冬美白 亮出透析肌肤 ></span></a></div>
        <div class="chad">
            <div class="right_p" style="float:left;">
                <a href="<?php echo $this->_var['mrhzBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['mrhzBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
            <div class="left_font" style="float:right;">
                <ul>
                    <?php $_from = $this->_var['mrhzs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'mrh');$this->_foreach['mrh'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['mrh']['total'] > 0):
    foreach ($_from AS $this->_var['mrh']):
        $this->_foreach['mrh']['iteration']++;
?>
                        <?php if (($this->_foreach['mrh']['iteration'] - 1) < 6): ?>
                            <li><a href="<?php echo $this->_var['mrh']['link']; ?>"><?php echo $this->_var['mrh']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#eaac44;"><a href="index.php?app=category&act=index1&cate_id=1225" style="width: 298px;height: 29px;display: block"><strong>电脑数码</strong><span class="ssp">靠神马hold住那帮淫 ></span></a></div>
        <div class="chad">
            <div class="left_font">
                <ul>
                    <?php $_from = $this->_var['dnsms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'dns');$this->_foreach['dns'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['dns']['total'] > 0):
    foreach ($_from AS $this->_var['dns']):
        $this->_foreach['dns']['iteration']++;
?>
                        <?php if (($this->_foreach['dns']['iteration'] - 1) < 6): ?>
                         <li><a href="<?php echo $this->_var['dns']['link']; ?>"><?php echo $this->_var['dns']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <div class="right_p">
                <a href="<?php echo $this->_var['dnsmBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['dnsmBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
        </div>
    </div>
    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#92bd49;"><a href="index.php?app=category&act=index1&cate_id=1243" style="width: 298px;height: 29px;display: block"><strong>服饰鞋帽</strong><span class="ssp">总有你喜欢的一款 ></span></a></div>
        <div class="chad">
            <div class="right_p" style="float:left;">
                <a href="<?php echo $this->_var['fsxmBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['fsxmBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
            <div class="left_font" style="float:right;">
                <ul>
                    <?php $_from = $this->_var['fsxms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'fsx');$this->_foreach['fsx'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fsx']['total'] > 0):
    foreach ($_from AS $this->_var['fsx']):
        $this->_foreach['fsx']['iteration']++;
?>
                        <?php if (($this->_foreach['fsx']['iteration'] - 1) < 6): ?>
                            <li><a href="<?php echo $this->_var['fsx']['link']; ?>"><?php echo $this->_var['fsx']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#27a196;"><a href="index.php?app=category&act=index1&cate_id=1284" style="width: 298px;height: 29px;display: block"><strong>吃货天堂</strong><span class="ssp">挡不住的诱惑 越吃越开心 ></span></a></div>
        <div class="chad">
            <div class="left_font">
                <ul>
                    <?php $_from = $this->_var['chtts']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cht');$this->_foreach['cht'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cht']['total'] > 0):
    foreach ($_from AS $this->_var['cht']):
        $this->_foreach['cht']['iteration']++;
?>
                        <?php if (($this->_foreach['cht']['iteration'] - 1) < 6): ?>
                            <li><a href="<?php echo $this->_var['cht']['link']; ?>"><?php echo $this->_var['cht']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <div class="right_p">
                <a href="<?php echo $this->_var['chttBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['chttBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
        </div>
    </div>
    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#d0281e;"><a href="index.php?app=category&act=index1&cate_id=1234" style="width: 298px;height: 29px;display: block"><strong>居家生活</strong><span class="ssp">全场低价大放送 ></span></a></div>
        <div class="chad">
            <div class="right_p" style="float:left;">
                <a href="<?php echo $this->_var['jjshBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['jjshBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
            <div class="left_font" style="float:right;">
                <ul>
                    <?php $_from = $this->_var['jjshs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'jjs');$this->_foreach['jjs'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['jjs']['total'] > 0):
    foreach ($_from AS $this->_var['jjs']):
        $this->_foreach['jjs']['iteration']++;
?>
                        <?php if (($this->_foreach['jjs']['iteration'] - 1) < 6): ?>
                            <li><a href="<?php echo $this->_var['jjs']['link']; ?>"><?php echo $this->_var['jjs']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="luyisi" style="height:150px;">
        <div class="titl" style="background:#1d5b9f;"><a href="index.php?app=category&act=index1&cate_id=1294" style="width: 298px;height: 29px;display: block"><strong>妈咪宝贝</strong><span class="ssp">世上只有妈咪好 ></span></a></div>
        <div class="chad">
            <div class="left_font">
                <ul>
                    <?php $_from = $this->_var['mmbbs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'mmb');$this->_foreach['mmb'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['mmb']['total'] > 0):
    foreach ($_from AS $this->_var['mmb']):
        $this->_foreach['mmb']['iteration']++;
?>
                        <?php if (($this->_foreach['mmb']['iteration'] - 1) < 6): ?>
                            <li><a href="<?php echo $this->_var['mmb']['link']; ?>"><?php echo $this->_var['mmb']['title']; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <div class="right_p">
                <a href="<?php echo $this->_var['mmbbBrand']['link']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['mmbbBrand']['img']; ?>" width="167" height="107" />
                </a>
            </div>
        </div>
    </div>



    <div class="float5">上海酷鸟网络科技有限公司<br />版权所有 沪ICP备13035566号</div>
    <?php echo $this->fetch('member.index_footer.html'); ?>
</div>
</body>
</html>
