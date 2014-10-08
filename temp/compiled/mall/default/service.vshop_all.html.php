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

        $("#create_time_from").datepicker({dateFormat: 'yy-mm-dd'});
        $("#create_time_to").datepicker({dateFormat: 'yy-mm-dd'});
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


<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <ul class="tab">
            <li class="active">精品集客小店管理</li>
        </ul>

        <div class="wrap">
            <div class="scarch_order">
                <div style="font-size: 16px;margin-bottom: 20px;">
                    <strong>当前精品店个数：<span style="color: red">
                        <?php echo $this->_var['vshopListCount']; ?>
                    </span> 个</strong>
                    <span style="float: right; margin-right: 16px;">
                        <a href="javascript:void(0)" onclick="vshop_bind()">
                            <img src="<?php echo $this->res_base . "/" . 'images/bind_vshop.png'; ?>">
                        </a>
                    </span>
                </div>
                <form method="get" action="">
                    <div style="float:left;margin-top: 20px;width: 775px;">
                        <input name="app" value="service" type="hidden"/>
                        <input name="act" value="vshop_all" type="hidden"/>
                        <input name="orderBy" id="orderBy" value="" type="hidden"/>
                        <input name="ascDesc" id="ascDesc" value="desc" type="hidden"/>

                        <span class="title" style="margin-left:1px">入驻时间:</span>
                        <input class="text_normal width2 " name="create_time_from" id="create_time_from" value="<?php echo $this->_var['create_time_from']; ?>" />
                        &#8211;
                        <input class="text_normal width2" id="create_time_to"  name="create_time_to" value="<?php echo $this->_var['create_time_to']; ?>" />
                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <span class="title">集客小店号:</span>
                        <input class="text_normal" type="text" name="shop_id"
                               style="width: 67px"/>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="title">按销量:</span>
                        <select name="orderSalesAscDesc" style="width: 70px;" onchange="order_by_order(this.value)">
                            <option selected="selected">请选择</option>
                            <option value="desc" >从高到低</option>
                            <option value="asc" >从低到高</option>
                        </select>

                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="title">按总营业额:</span>
                        <select name="orderSalesAscDesc" style="width: 70px;" onchange="order_by_sales(this.value)">
                            <option selected="selected">请选择</option>
                            <option value="desc">从高到低</option>
                            <option value="asc">从低到高</option>
                        </select>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="submit" class="btn" value="搜索" />
                    </div>


                </form>
            </div>
            <div class="public">

                <div class="ble">
                    <table width="766" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="align2" width="220" align="center" bgcolor="#f3f3f3" style="color:#333;" >精品店信息</td>
                            <td height="30" width="120" colspan="3" align="center" bgcolor="#f3f3f3" style="color:#333;">入驻时间</td>
                            <td width="53" align="center" bgcolor="#f3f3f3" style="color:#333;">订单数量</td>
                            <td width="108" align="center" bgcolor="#f3f3f3" style="color:#333;">所属服务中心</td>
                            <td width="87" align="center" bgcolor="#f3f3f3" style="color:#333;">总营业额</td>
                            <td width="96" align="center" bgcolor="#f3f3f3" style="color:#333;" colspan="2">操作</td>
                        </tr>
                        <?php if ($this->_var['vshopList'] != null && count ( $this->_var['vshopList'] ) > 0): ?>
                            <?php $_from = $this->_var['vshopList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'vshop');if (count($_from)):
    foreach ($_from AS $this->_var['vshop']):
?>
                                <tr style="border-top: 1px solid #d3d3d3">
                                    <td align="center" style=" padding-left:8px;">
                                        集客小店号:<?php echo $this->_var['vshop']['user_id']; ?>&nbsp;&nbsp;<br/>
                                        店铺名:<?php echo $this->_var['vshop']['shop_name']; ?>
                                    </td>
                                    <td height="50" colspan="3" align="center"><?php echo $this->_var['vshop']['create_time']; ?></td>
                                    <td height="50" align="center"><?php echo $this->_var['vshop']['order_count']; ?></td>
                                    <td align="center" style="font-weight:bold;"><?php echo $this->_var['vshop']['region_name']; ?></td>
                                    <td align="center" style="color:#999"><?php if ($this->_var['vshop']['sales_count'] && $this->_var['vshop']['sales_count'] > 0): ?><?php echo $this->_var['vshop']['sales_count']; ?><?php else: ?>0<?php endif; ?></td>
                                    <td colspan="2" align="center">
                                        <a href="javascript:void(0)" onclick="vshop_cancel_page('<?php echo $this->_var['vshop']['user_id']; ?>')" > 取消精品店资格</a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        <?php else: ?>
                         <tr>
                             <td class="member_no_records" colspan="7" style="text-align:center">没有相关的结果</td>
                         </tr>
                        <?php endif; ?>
                    </table>
                </div>




                <!--<div class="order_form_page">-->
                    <!--<div class="page">-->
                        
                    <!--</div>-->
                <!--</div>-->



            </div>
            <iframe name="seller_order" style="display:none;"></iframe>
        </div>
    </div>
</div>
<div class="clear"></div>
</div>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js" ></script>

<script type="text/javascript">
    function vshop_bind(){
        var uri =encodeURI("<?php echo $this->_var['site_url']; ?>/index.php?app=service&act=vshop_bind_page");
        var id = 'vshop_bind_page';
        var title = "绑定集客小店";
        var width = '570';
        ajax_form(id, title, uri, width);
    }
    function vshop_cancel_page(_shop_id){
        var _uri = "<?php echo $this->_var['site_url']; ?>/index.php?app=service&act=vshop_cancel_page&shop_id="+_shop_id;
        _uri =encodeURI(_uri);
        var id = 'vshop_cancel_page';
        var title = "取消精品店资格";
        var width = '570';
        ajax_form(id, title, _uri, width);
    }
</script>

<?php echo $this->fetch('footer.html'); ?>
