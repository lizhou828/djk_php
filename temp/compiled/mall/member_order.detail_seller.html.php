<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>订单详细</title>
    <link href="<?php echo $this->res_base . "/" . 'common.css'; ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->res_base . "/" . 'order-inquiry.css'; ?>" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/hide_share_button.js"></script>

</head>
<body>

<div style="width: 320px;margin: 0px auto">
    <div >

        <div class="ord-t">
            <div class="ord-l"><a href="javascript:history.go(-1)">返回</a></div>
            <div class="ord-c2">订单详情</div>
            <div class="ord-r1">
                  

                  <?php if ($this->_var['type'] && $this->_var['type'] == 'seller' && $this->_var['order']['status'] && ( $this->_var['order']['status'] == 20 || $this->_var['order']['status'] == 10 )): ?>
                    <a href="<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=member_order&act=sendGoodsPage&type=<?php echo $this->_var['type']; ?>&order_id=<?php echo $this->_var['order']['order_id']; ?>">
                        发货
                    </a>
                  <?php else: ?>
                    <a href="<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user']['user_id']; ?>&type=seller&status=11">订单列表</a>
                  <?php endif; ?>
            </div>
        </div>

        <div class="Safety-cen-1" style="padding-top:55px;">
            <div class="cancel">
                订单状态：<strong><?php echo call_user_func("order_status",$this->_var['order']['status']); ?></strong>
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
                <dl <?php if (($this->_foreach['_goods']['iteration'] == $this->_foreach['_goods']['total'])): ?> style="margin:0px;"<?php endif; ?>>
                    <dt>
                        <img src="<?php if (! $this->_var['good']['goods_image']): ?><?php echo $this->_var['site_url']; ?>/data/system/default_goods_image.gif <?php else: ?><?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['good']['goods_image']; ?><?php endif; ?>" width="100" height="100" alt="<?php echo sub_str($this->_var['good']['goods_name'],30); ?>"/>
                    </dt>
                    <dd>
                        <h3><?php echo $this->_var['good']['goods_name']; ?></h3>
                        <p><?php echo $this->_var['good']['specification']; ?></p>
                        <strong>￥<?php echo number_format2($this->_var['good']['price']); ?>×<?php echo $this->_var['good']['quantity']; ?></strong>
                    </dd>
                </dl>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
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
                </div>
                <p><strong>订单金额</strong><span>￥<?php echo number_format2($this->_var['order']['order_amount']); ?></span></p>
            </div>
            <div class="list2">
                <div class="list2-t">
                    订单信息
                </div>
                <div class="list2-c">
                    <table width="307" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="75" height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>订单编号</p></td>
                            <td width="13" height="30"><p>&nbsp;</p></td>
                            <td><?php echo $this->_var['order']['order_sn']; ?></td>
                        </tr>

                        
                        <tr>
                            <td height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>下单时间</p></td>
                            <td height="30" ><p>&nbsp;</p></td>
                            <td ><?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['add_time']); ?></td>
                        </tr>

                        

                        <?php if ($this->_var['order']['status'] == 40 && $this->_var['order']['pay_time']): ?>
                        <tr>
                            <td width="75" height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>付款时间</p></td>
                            <td width="13" height="30"><p>&nbsp;</p></td>
                            <td><?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['pay_time']); ?></td>
                        </tr>
                        <?php endif; ?>

                        
                        <?php if ($this->_var['order']['status'] == 30 && $this->_var['order']['ship_time']): ?>
                        <tr>
                            <td width="75" height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>付款时间</p></td>
                            <td width="13" height="30"><p>&nbsp;</p></td>
                            <td><?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['pay_time']); ?></td>
                        </tr>
                        <tr>
                            <td width="75" height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>物流公司</p></td>
                            <td width="13" height="30"><p>&nbsp;</p></td>
                            <td><?php echo $this->_var['kuaidigongsi']; ?></td>
                        </tr>
                        <tr>
                            <td width="75" height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>物流单号</p></td>
                            <td width="13" height="30"><p>&nbsp;</p></td>
                            <td><?php echo $this->_var['invoice_no']; ?></td>
                        </tr>
                        <tr>
                            <td width="75" height="30" align="center" style="border-right:1px solid #e5e5e5;"><p>发货时间</p></td>
                            <td width="13" height="30"><p>&nbsp;</p></td>
                            <td><?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['ship_time']); ?></td>
                        </tr>

                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>




    </div>
</div>
<?php echo $this->fetch('cnzz.html'); ?>
</body>
</html>
