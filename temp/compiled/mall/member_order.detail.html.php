<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>订单详情</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/common.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/order-inquiry.css" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['SITE_URL']; ?>/weixin/templates/js/jquery-1.6.2.min.js"></script>
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/hide_share_button.js"></script>
    <script type="text/javascript">
        $(function (){
            $(".ann2").click(function(){
//                window.location.href= "<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=payPage&orderSn=<?php echo $this->_var['order']['order_sn']; ?>";
                window.location.href="<?php echo $this->_var['mobile_pay_url']; ?>/dajike-account/mobile/list.action?orderSn=<?php echo $this->_var['order']['order_sn']; ?>&userId=<?php echo $this->_var['member']['user_id']; ?>"
            });
        });
        function confirmReceive(){
            $.ajax({
                type:"post",
                url:'<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=member_order&act=confirmReceive',
                data:{"order_id":'<?php echo $this->_var['order']['order_id']; ?>'},
                success:function(data){
                    data=eval("("+data+")");
                    alert(data.msg);
                    if(data.retval =="1"){
                        window.location.reload();
                    }
                },
                beforeSend:function(){
                    $("#confirmReceive").html("<img src='<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/waiting.gif'>");
                }
            });
        }
    </script>
</head>
<body>
<div style="width: 320px;margin: 0px auto">
    <div style="padding-top: 50px;">
<div class="address-t">
    <div class="address-l"><a href="javascript:history.go(-1)">返回</a></div>
    <div class="address-c2" style="padding-left: 55px;">订单详情</div>
    <?php echo $this->fetch('member.index_header.html'); ?>
</div>
<div class="Safety-cen-1">
    <div class="cancel">
        <p>
            <span>
                <?php if ($this->_var['order']['status'] == 0): ?>
                    <span style="color: #1D5B9F">订单已取消</span>
                <?php elseif ($this->_var['order']['status'] == 30): ?>
                    <span id="confirmReceive"><a href="javascript:void(0)" onclick="confirmReceive()">确认收货</a></span>
                <?php elseif ($this->_var['order']['status'] == 11 || $this->_var['order']['status'] == 10 || $this->_var['order']['status'] == 20): ?>
                    <a href="<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=member_order&act=confirmCancel&order_id=<?php echo $this->_var['order']['order_id']; ?>">取消订单</a>
                <?php elseif ($this->_var['order']['status'] == 40): ?>
                    <span style="color: #1D5B9F">已完成</span>
                <?php endif; ?>

            </span>订单编号：<?php echo $this->_var['order']['order_sn']; ?>
        </p>
        <p>下单时间：<?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['add_time']); ?></p>
    </div>
    <div class="shou-h">
        <p>收货人：<?php echo $this->_var['order_extm']['consignee']; ?></p>
        <p>电话：<?php echo $this->_var['order_extm']['phone_mob']; ?></p>
        <p style="border:0px;line-height:20px;margin-top: 3px;">收货地址：<?php echo $this->_var['order_extm']['region_name']; ?><?php echo $this->_var['order_extm']['address']; ?></p>
    </div>
    <div class="image-text">
        <?php $_from = $this->_var['goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'good');$this->_foreach['_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_goods']['total'] > 0):
    foreach ($_from AS $this->_var['good']):
        $this->_foreach['_goods']['iteration']++;
?>
        <dl <?php if (($this->_foreach['_goods']['iteration'] == $this->_foreach['_goods']['total']) && count ( $this->_var['goods'] ) > 1): ?> style="margin:0px;"<?php endif; ?>>
            <dt>
                <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['good']['goods_image']; ?>" width="100" height="100" alt="<?php echo sub_str($this->_var['good']['goods_name'],30); ?>"/>
            </dt>
            <dd>
                <h3><?php echo $this->_var['good']['goods_name']; ?></h3>
                <p><?php echo $this->_var['good']['specification']; ?></p>
                <strong>￥<?php echo number_format2($this->_var['good']['price']); ?>×<?php echo $this->_var['good']['quantity']; ?></strong>
            </dd>
        </dl>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    </div>
    <!--<div class="Payment">支付方式<span><a href="#">支付宝</a></span></div>-->
    <div class="list">
        <div class="list1">
            <dl>
                <dt>商品总额</dt>
                <dd>￥<?php echo number_format2($this->_var['order']['goods_amount']); ?></dd>
            </dl>
            <dl>
                <dt>运费</dt>
                <dd>￥<?php echo $this->_var['order_extm']['shipping_fee']; ?></dd>
            </dl>
            <!--<dl>-->
                <!--<dt>积分抵扣</dt>-->
                <!--<dd>￥<?php echo $this->_var['order']['jifen']; ?></dd>-->
            <!--</dl>-->
        </div>
        <p><strong>订单金额</strong><span>￥<?php echo number_format2($this->_var['order']['order_amount']); ?></span></p>
        <p style="border:0px;">订单状态<span> <?php echo call_user_func("order_status",$this->_var['order']['status']); ?></span></p>
    </div>
    <?php if ($this->_var['user']['member_type'] == 01 && $this->_var['order']['status'] == 11 && $this->_var['order']['is_daifu'] == 0): ?>
        <div class="ann2">
            <a href="javascript:void(0)">
                <?php if ($this->_var['order']['yue'] + $this->_var['order']['koushui_yue'] > 0): ?>
                    支付余款
                <?php else: ?>
                    立即支付
                <?php endif; ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($this->_var['order']['status'] && $this->_var['order']['status'] == 30): ?>
        <div class="list" style="padding: 8px 15px 0 10px;width: 282px;" >
            <div style="border-bottom: 1px solid #E5E5E5; margin-top: 10px;">
                <span style="height: 30px;line-height: 20px;margin-top: 10px;width:75px;">
                    <strong>发货时间</strong>
                </span>
                <span style="height: 30px;line-height: 20px;margin-top: 10px;width:75px;">
                    <div style="float: right"><?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['ship_time']); ?></div>
                </span>
            </div>

            <?php if ($this->_var['wuliugongsi'] && $this->_var['wuliugongsi'] != '' && $this->_var['wuliudanhao'] && $this->_var['wuliudanhao'] != ''): ?>
                <div style="border-bottom: 1px solid #E5E5E5; margin-top: 10px;">
                    <span style="height: 30px;line-height: 20px;margin-top: 10px;width:75px;">
                        <strong>物流公司</strong>
                    </span>
                    <span style="height: 30px;line-height: 20px;margin-top: 10px;width:75px;">
                        <div style="float: right"><?php echo $this->_var['wuliugongsi']; ?></div>
                    </span>
                </div>
                <div style="border-bottom: 1px solid #E5E5E5; margin-top: 10px;">
                    <span style="height: 30px;line-height: 20px;margin-top: 10px;width:75px;">
                        <strong>物流单号</strong>
                    </span>
                    <span style="height: 30px;line-height: 20px;margin-top: 10px;width:75px;">
                        <div style="float: right"><?php echo $this->_var['wuliudanhao']; ?></div>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="00" style="height:50px;"></div>
</div>
    </div>
</div>
<?php echo $this->fetch('cnzz.html'); ?>
</body>
</html>
