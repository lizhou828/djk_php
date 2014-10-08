<?php echo $this->fetch('member.header.html'); ?>

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
<script type="text/javascript">
    function order_by_order(_value){
        $("#orderBy").val("order_count")
        $("#ascDesc").val(_value);
    }
    function order_by_sales(_value){
        $("#orderBy").val("sales_count")
        $("#ascDesc").val(_value);
    }
</script>

<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>

<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js" ></script>
<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <ul class="tab">
            <li class="active">精品店佣金管理</li>
        </ul>

        <div class="wrap">

            <div class="scarch_order">
                <div style="background:#FFE6C6;border:1px solid #FFCFB3;width: 768px;height: 30px;margin-top:5px;" >
                    <div style="padding-left: 20px; padding-top: 8px;">您从精品集客小店获得的佣金将会展示在这里，您可以按照搜索条件查询您的收益情况。</div>
                </div>
                <form method="get" >
                    <div style="float:left;margin-top: 20px;background:#f3f3f3;border:1px solid #d3d3d3;height: 50px;width: 768px;padding-top: 20px;">
                        <input name="app" value="service" type="hidden"/>
                        <input name="act" value="vshop_yongjin" type="hidden"/>

                        <span class="title" style="margin-left:20px">下单时间:</span>
                        <input class="text_normal width2 " type="text" name="add_time_from" id="add_time_from" value="<?php echo $this->_var['add_time_from']; ?>" />
                        &#8211;
                        <input class="text_normal width2" id="add_time_to" type="text" name="add_time_to" value="<?php echo $this->_var['add_time_to']; ?>" />
                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <span class="title">集客小店号:</span>
                        <select name="shop_id" style="width: 105px;">
                            <?php if ($this->_var['vshopIdList'] && count ( $this->_var['vshopIdList'] ) > 0): ?>
                                <option value="">请选择</option>
                                <?php $_from = $this->_var['vshopIdList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', '_shop_id');if (count($_from)):
    foreach ($_from AS $this->_var['_shop_id']):
?>
                                    <option value="<?php echo $this->_var['_shop_id']; ?>" <?php if ($this->_var['shop_id'] == $this->_var['_shop_id']): ?>selected="selected"<?php endif; ?>><?php echo $this->_var['_shop_id']; ?></option>
                                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            <?php else: ?>
                                <option value="">请选择</option>
                            <?php endif; ?>
                        </select>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="title">订单编号:</span>
                        <input class="text_normal" type="text" name="order_sn" value="<?php echo $this->_var['order_sn']; ?>" style="width: 150px"/>

                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="submit" class="btn" value="搜索" />
                    </div>


                </form>
            </div>
            <div class="public">
                <!--<div style="margin-bottom: 20px;">总佣金额: <span style="color: orangered">￥<?php echo number_format2($this->_var['all_yongjin']); ?></span></div>-->

                <script type="text/javascript">
                    //   alert( " document.compatMode="+document.compatMode+"  , window.pageYOffset="+window.pageYOffset+" ,navigator.userAgent="+navigator.userAgent);
                    var toTopDistance=330;//滚动条距离顶部的距离
                    if(/Firefox/gi.test(navigator.userAgent)){
                        toTopDistance = 330;
                    }else if(/Chrome/gi.test(navigator.userAgent)){
                        toTopDistance = 580;
                    }
                    window.onscroll=function(){
                        if( document.body.scrollTop + document.documentElement.scrollTop >= toTopDistance){
                            $("#navigator").css({"position":"fixed","top":"0","z-index":9999});
                        }else{
                            $("#navigator").css({"position":"","top":""});
                        }
                    };
                </script>
                <div class="ble" id="navigator" style="background: #E6FBD5">



                    <table width="766" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td  width="95" align="center" bgcolor="#E6FBD5" style="color:#333;" colspan="2">商品</td>
                            <td height="30" width="110"  align="center" bgcolor="#E6FBD5" style="color:#333;">单价</td>
                            <td width="53" align="center" bgcolor="#E6FBD5" style="color:#333;">数量</td>
                            <td width="150" align="center" bgcolor="#E6FBD5" style="color:#333;">订单金额</td>
                            <td width="150" align="center" bgcolor="#E6FBD5" style="color:#333;">我获得的佣金</td>
                        </tr>
                    </table>
                </div>
                <?php if ($this->_var['page_info'] && $this->_var['page_info']['orders'] && count ( $this->_var['page_info']['orders'] ) > 0): ?>
                    <?php $_from = $this->_var['page_info']['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');$this->_foreach['order_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['order_index']['total'] > 0):
    foreach ($_from AS $this->_var['order']):
        $this->_foreach['order_index']['iteration']++;
?>
                        <div class="ble"  >
                            <table width="766" border="0" cellspacing="0" cellpadding="0">
                                <tr style="height: 30px;">
                                    <td colspan="6" bgcolor="#f3f3f3"  >
                                        <span style="margin-left: 20px;">
                                            <a target="_blank" href="<?php echo $this->_var['site_url']; ?>/index.php?app=service&act=view&order_id=<?php echo $this->_var['order']['order_id']; ?>">订单编号:<?php echo $this->_var['order']['order_sn']; ?></a>
                                        </span>
                                        <span style="margin-left: 40px;">下单时间:<?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['add_time']); ?></span>
                                        <span style="margin-left: 40px;">集客小店号:<?php echo $this->_var['order']['shop_id']; ?></span>
                                        <span style="margin-left: 40px;">买家:<?php echo $this->_var['order']['buyer_name']; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <?php if ($this->_var['order']['order_goods'] && count ( $this->_var['order']['order_goods'] ) > 0): ?>
                                        <td  width="466"  align="center"  style="color:#333;border-top:1px solid #dddddd;" colspan="2">
                                            <table width="466" border="0"  style="border-right:1px solid #dddddd;">
                                                <tbody>
                                                    <?php $_from = $this->_var['order']['order_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'good');if (count($_from)):
    foreach ($_from AS $this->_var['good']):
?>
                                                        <tr width="466">
                                                            <td style="float:left;width:100px; height:100px;">
                                                                <img width="100px" height="100px" alt="<?php echo $this->_var['good']['goods_name']; ?>" src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['good']['goods_image']; ?>">
                                                            </td>
                                                            <td style="float:left;width:200px;height:100px;">
                                                                <div style="margin-top: 40px;"><?php echo $this->_var['good']['goods_name']; ?> </div>
                                                            </td>
                                                            <td style="float:left;width:110px;height:100px;">
                                                                <div style="margin-top: 40px;text-align: center"><?php echo price_format($this->_var['good']['price']); ?></div>
                                                            </td>
                                                            <td style="float:left;width: 50px;height:100px;">
                                                                <div style="margin-top: 40px;text-align: center"><?php echo $this->_var['good']['quantity']; ?></div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                                </tbody>
                                            </table>
                                        </td>
                                    <?php else: ?>
                                        <td  width="466"  align="center"  style="color:#333;border-right:1px solid #dddddd;border-top:1px solid #dddddd;" colspan="2">
                                                        没有商品
                                        </td>
                                    <?php endif; ?>

                                    <td width="150" align="center" rowspan="<?php echo ($this->_foreach['goods_index']['iteration'] == $this->_foreach['goods_index']['total']); ?>" style="color:#333;border-right:1px solid #dddddd;border-top:1px solid #dddddd;">
                                        <?php echo price_format($this->_var['order']['order_amount']); ?>
                                        <?php if ($this->_var['order']['shipping_fee'] && $this->_var['order']['shipping_fee'] > 0): ?>
                                            <br/>
                                            <span style="color: #808080">(含运费:<?php echo price_format($this->_var['order']['shipping_fee']); ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td width="150" align="center"  style="color:#333;border-top:1px solid #dddddd;"><?php echo number_format2($this->_var['order']['yong_jin']); ?></td>
                                </tr>
                            </table>
                        </div>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php else: ?>
                    <table>
                        <tr><td class="member_no_records" colspan="7" style="padding-left: 300px;">没有符合条件的商品</td></tr>
                    </table>
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
