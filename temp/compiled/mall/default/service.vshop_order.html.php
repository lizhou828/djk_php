<?php echo $this->fetch('member.header.html'); ?>
<!--<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/jquery-ui.js'; ?>"></script>-->
<!--<style type="text/css" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/themes/ui-lightness/jquery.ui.css"></style>-->

<!--<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>-->
<!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>-->
<!--<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>-->
<script type="text/javascript" charset="utf-8">
    jQuery(function($){
        $.datepicker.regional['zh-CN'] ={
                clearText: '清除', clearStatus: '清除已选日期',
                closeText: '关闭', closeStatus: '不改变当前选择',
                prevText: '<上月', prevStatus: '显示上月',
                nextText: '下月>', nextStatus: '显示下月',
                currentText: '今天', currentStatus: '显示本月',
                monthNames: ['一月','二月','三月','四月','五月','六月',
            '七月','八月','九月','十月','十一月','十二月'],
                monthNamesShort: ['一','二','三','四','五','六',
            '七','八','九','十','十一','十二'],
                monthStatus: '选择月份', yearStatus: '选择年份',
                weekHeader: '周', weekStatus: '年内周次',
                dayNames: ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
                dayNamesShort: ['周日','周一','周二','周三','周四','周五','周六'],
                dayNamesMin: ['日','一','二','三','四','五','六'],
                dayStatus: '设置 DD 为一周起始', dateStatus: '选择 m月 d日, DD',
                dateFormat: 'yy-mm-dd', firstDay: 1,
                initStatus: '请选择日期', isRTL: false
        }
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
        $("#add_time_from").datepicker({dateFormat: 'yy-mm-dd'});
        $("#add_time_to").datepicker({dateFormat: 'yy-mm-dd'});
    });
</script>

<div class="content">
<?php echo $this->fetch('member.menu.html'); ?>
<div id="right">
    <?php echo $this->fetch('member.submenu.html'); ?>
<div class="wrap">
<div class="scarch_order">
    <form method="get">
        <div style="float:left;">

            <span class="title" <?php if ($this->_var['ordersType']): ?>style="margin-left:53px"<?php endif; ?> >&nbsp;&nbsp;订单号:</span>
            <input class="text_normal" type="text" name="order_sn" value="<?php echo htmlspecialchars($this->_var['order_sn']); ?>" />
            <span class="title" style="margin-left:40px">下单时间:</span>
            <input class="text_normal width2 " type="text" name="add_time_from" id="add_time_from" value="<?php echo $this->_var['add_time_from']; ?>" />
            &#8211;
            <input class="text_normal width2" id="add_time_to" type="text" name="add_time_to" value="<?php echo $this->_var['add_time_to']; ?>" />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="title">买家:</span>
            <input class="text_normal" type="text" name="buyer_name" value="<?php echo htmlspecialchars($this->_var['buyer_name']); ?>" />

            <input type="hidden" name="app" value="service" />
            <input type="hidden" name="act" value="vshop_order" />
            <input type="hidden" name="type" value="<?php echo $this->_var['type']; ?>" />
            <br/>
            <br/>
            <span class="title">集客小店号:</span>
            <select name="shop_id" style="width: 105px;">
                <?php if ($this->_var['vshopList'] != null && count ( $this->_var['vshopList'] ) > 0): ?>
                    <option value="">请选择</option>
                    <?php $_from = $this->_var['vshopList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'vshop');if (count($_from)):
    foreach ($_from AS $this->_var['vshop']):
?>
                        <option value="<?php echo $this->_var['vshop']['user_id']; ?>" <?php if ($this->_var['shop_id'] == $this->_var['vshop']['user_id']): ?>selected="selected"<?php endif; ?>><?php echo $this->_var['vshop']['user_id']; ?></option>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php else: ?>
                    <option value="">请选择</option>
                <?php endif; ?>
            </select>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="submit" class="btn" value="搜索" />
        </div>


    </form>
</div>
<div class="public">

    <?php if ($this->_var['orders']): ?>
        <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['order']):
?>
            <div class="dp">买家用户：<a href="javascript:;"><?php echo htmlspecialchars($this->_var['order']['buyer_name']); ?></a>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;收货人姓名：<?php echo htmlspecialchars($this->_var['order']['consignee']); ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;收货人电话：<?php if ($this->_var['order']['phone_mob'] != ''): ?><?php echo $this->_var['order']['phone_mob']; ?><?php else: ?><?php echo $this->_var['order']['phone_tel']; ?><?php endif; ?>
                <?php if ($this->_var['order']['status'] == ORDER_PENDING && $this->_var['order']['yue_jine'] > 0 && $this->_var['order']['zhifu'] > 0): ?>
                <span style="color:red">&nbsp;(余额已支付<?php echo number_format2($this->_var['order']['zhifu']); ?>元，还剩<?php echo $this->_var['order']['yue_jine']; ?>元待支付！)</span>
                <?php endif; ?>
            </div>
            <div class="ble">
                <table width="766" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="align2" align="center" bgcolor="#f3f3f3" style="color:#333;" >订单号</td>
                        <td height="30" width="220" colspan="3" align="center" bgcolor="#f3f3f3" style="color:#333;">订单商品</td>
                        <td width="93" align="center" bgcolor="#f3f3f3" style="color:#333;">订单金额</td>
                        <td width="108" align="center" bgcolor="#f3f3f3" style="color:#333;">时间</td>
                        <td width="87" align="center" bgcolor="#f3f3f3" style="color:#333;">订单状态</td>
                        <!--<td width="96" align="center" bgcolor="#f3f3f3" style="color:#333;">操作</td>-->
                    </tr>
                    <tr>

                        <td align="center" style=" padding-left:8px;">
                            <a href="<?php echo url('app=' . $_GET['app']. '&act=view&order_id=' . $this->_var['order']['order_id']. ''); ?>" target="_blank" style="font-weight:bold; color:#666">
                                <?php echo $this->_var['order']['order_sn']; ?><?php if ($this->_var['order']['extension'] == 'groupbuy'): ?><span class="color8">[团购咨询]</span><?php endif; ?>
                            </a>
                        </td>
                        <td height="80" colspan="2">
                            <ul class="sp">
                                <?php $_from = $this->_var['order']['order_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['o_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['o_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['o_goods']['iteration']++;
?>
                                    <?php if (($this->_foreach['o_goods']['iteration'] - 1) <= 3): ?>
                                        <li>
                                            <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>" target="_blank">
                                                <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods']['goods_image']; ?>" style="border: 1px solid #ddd" alt="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" width="60px" height="60px"/>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            </ul>
                        </td>
                        <td height="50">&nbsp;</td>
                        <td align="center" style="font-weight:bold;"><?php echo price_format($this->_var['order']['order_amount']); ?></td>
                        <td align="center" style="color:#999"><?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['add_time']); ?></td>
                        <td align="center" style="font-weight:bold; color:#333"><?php echo call_user_func("order_status",$this->_var['order']['status']); ?><?php if ($this->_var['order']['evaluation_status']): ?>,&nbsp;已评价<?php endif; ?></td>

                    </tr>
                </table>
            </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    <?php else: ?>
        <center>
            <table>
                <tr><td class="member_no_records" colspan="7" style="text-align:center">没有符合条件的商品</td></tr>
            </table>
        </center>
    <?php endif; ?>


    <div class="order_form_page">
        <div class="page">
            <?php echo $this->fetch('member.page.bottom.html'); ?>
        </div>
    </div>



</div>
<iframe name="seller_order" style="display:none;"></iframe>

</div>
</div>
</div>
<div class="clear"></div>
</div>
<?php echo $this->fetch('footer.html'); ?>
