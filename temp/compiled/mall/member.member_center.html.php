<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>我的大集客</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/common.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/order-inquiry.css" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/hide_share_button.js"></script>
    <script type="text/javascript">
        function bindPage(){
            var password2 ='<?php echo $this->_var['user']['password2']; ?>';
            var phone_mob ='<?php echo $this->_var['user']['phone_mob']; ?>';
            var phone_mob_bind_status ='<?php echo $this->_var['user']['phone_mob_bind_status']; ?>';
            if(!phone_mob_bind_status || phone_mob_bind_status == 0 || !phone_mob || phone_mob==null || phone_mob==""){
                alert("您还没有绑定手机号!");
                window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_safecenter&act=bindIndex';
            }else if(!password2 || password2==null || password2==""){
                alert("您还没有设置支付密码!");
                window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_safecenter&act=setPayPassword';
            }else{
                window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_bankcard&act=bindPage';
            }



        }
    </script>
</head>
<body>
<div style="width: 320px;margin: 0px auto;">
    <div style="padding-top: 50px;">

<div id="w_3202">
    <div class="address-t">
        <div class="address-l"><a href="javascript:history.go(-1)">返回</a></div>
        <div class="address-c2" style="padding-left: 60px;">我的大集客</div>
    </div>
<div class="Safety-cen11">
    <div class="mandi1">
        <div class="mandi1-1">
            <?php if ($this->_var['user']['portrait']): ?>
                <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['user']['portrait']; ?>" width="73" height="72" />
            <?php else: ?>
                <img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/tou-x.jpg" width="73" height="72" />
            <?php endif; ?>

        </div>
        <div class="mandi1-2">
            <p><?php if (strlen ( $this->_var['user']['user_name'] ) == 32 && strlen ( $this->_var['user']['im_qq'] ) == 32): ?><?php echo $this->_var['user']['nick_name']; ?><?php else: ?><?php echo $this->_var['user']['user_name']; ?><?php endif; ?></p>
            <?php if ($this->_var['user']['member_type'] == 01): ?>
                <div class="mandi1-2-c">
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_account&act=yue">
                    <dl style="width: 80px;">
                        <dt>账户余额</dt>
                        <dd>￥<?php echo number_format2($this->_var['userFinance']['unkoushui_yue']); ?></dd>
                    </dl>
                </a>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_account&act=shouyi">
                    <dl style="width: 80px;">
                        <dt>收益</dt>
                        <dd>￥<?php echo number_format2($this->_var['userFinance']['koushui_yue']); ?></dd>
                    </dl>
                </a>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_account&act=jifen">
                    <dl style="width: 40px;">
                        <dt>积分</dt>
                        <dd><?php echo $this->_var['userFinance']['jifen']; ?></dd>
                    </dl>
                </a>
                <!--<dl>-->
                    <!--<dt>抽奖权</dt>-->
                    <!--<dd><?php echo $this->_var['user']['choujiang']; ?></dd>-->
                <!--</dl>-->
            </div>
            <?php elseif ($this->_var['user']['member_type'] == 02): ?>
                <div class="mandi1-2-c">
                    
                    <dl>
                        <dt>账户余额</dt>
                        <dd>￥<?php echo number_format2($this->_var['userFinance']['unkoushui_yue']); ?></dd>
                    </dl>
                    <dl style="margin-left:20px;">
                        <dt style="width:101px;">托管商家营业额</dt>
                        <dd>￥<?php echo number_format2($this->_var['tuoguan_yye']); ?></dd>
                    </dl>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <?php if ($this->_var['user']['member_type'] == 02): ?>
        <div class="mandi2-2">
            <p><?php echo $this->_var['seller_stat']['accepted']; ?></p>
            <ul>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&status=20">
                    <li> <div style="margin-left: 46px;">已付款订单<s>（查看托管商家已付款订单）</s></div></li>
                </a>
            </ul>
        </div>
        <div class="mandi3">
            <p style="left: 270px;"><?php echo $this->_var['seller_stat']['all']; ?></p>
            <ul>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&status=all_orders">
                    <li><div style="margin-left: 46px;">全部订单<s>（查看托管商家全部订单）</s><span></span></div></li>
                </a>
            </ul>
        </div>
    <?php elseif ($this->_var['user']['member_type'] == 01): ?>
        <?php if (! $this->_var['user']['store']): ?>
            <div class="mandi2">
                <p><?php echo $this->_var['buyer_stat']['pending']; ?></p>
                <ul>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&status=11">
                        <li><div style="margin-left: 44px;">待付款订单<span>></span></div></li>
                    </a>
                </ul>
            </div>
            <div class="mandi3">
                <p><?php echo $this->_var['buyer_stat']['submitted']; ?></p>
                <ul>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&status=10">
                        <li><div style="margin-left: 46px;">已付款订单<span>></span></div></li>
                    </a>
                </ul>
            </div>
            <div class="mandi3">
                <p><?php echo $this->_var['buyer_stat']['shipped']; ?></p>
                <ul>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&status=30">
                        <li><div style="margin-left: 46px;">已发货订单<span>></span></div></li>
                    </a>
                </ul>
            </div>
            <div class="mandi3">
                <ul>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&status=all_orders">
                        <li><div style="margin-left: 46px;">全部订单<span>></span></div></li>
                    </a>
                </ul>
            </div>
        <?php else: ?>
            <div class="mandi2">
                <ul>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&type=buyer&status=11">
                        <li><div style="margin-left: 44px;">买到的商品<span>></span></div></li>
                    </a>
                </ul>
            </div>
            <div class="mandi3">
                <ul>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&type=seller&status=11">
                        <li><div style="margin-left: 46px;">卖出的商品<span>></span></div></li>
                    </a>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>



    <div class="mandi4">
        <ul>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_account">
                <li><div style="margin-left: 46px;">结算中心<span>></span></div></li>
            </a>
        </ul>
    </div>

    <div class="mandi6-1">
        <?php if ($this->_var['user']['bank'] > 0): ?>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_bankcard&act=cards">
            <dl>
                <dt></dt>
                <dd>我的银行卡<span>></span>
                    <p>共<?php echo $this->_var['user']['bank']; ?>张银行卡</p>
                </dd>
            </dl>
            </a>
        <?php else: ?>
            <a href="javascript:void(0)" onclick="bindPage()">
                <dl>
                    <dt></dt>
                    <dd>我的银行卡<span>></span>
                        <p>未绑定银行卡</p>
                    </dd>
                </dl>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($this->_var['user']['member_type'] == 01): ?>
    <div class="mandi6">
        <ul>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_address&act=all">
                <li><div style="margin-left: 43px;">地址管理<span>></span></div></li>
            </a>
        </ul>
    </div>
    <?php endif; ?>
    <div class="mandi7">
        <ul>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_safecenter&act=bindIndex">
            <li>
                <div style="margin-left: 39px;">手机号码
                <span style="color:#666;">
                    <?php if (! $this->_var['user']['phone_mob'] || $this->_var['user']['phone_mob_bind_status'] == 0): ?>
                           未绑定
                    <?php else: ?>
                        <?php echo $this->_var['user']['phone_mob']; ?>
                    <?php endif; ?>
                </span>
                </div>
            </li>
            </a>
        </ul>
    </div>
    <div class="mandi8">
        <ul>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_safecenter&act=index">
                <li><div style="margin-left: 46px;">安全中心<span>></span></div></li>
            </a>
        </ul>
    </div>
    <div class="mandi9"><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=default&act=logout"><div>退出登录</div></a></div>
</div>
    <?php echo $this->fetch('member.index_footer.html'); ?>

</div>
    </div>
</div>
</body>
</html>
