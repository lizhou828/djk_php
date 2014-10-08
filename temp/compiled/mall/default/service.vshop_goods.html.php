<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript"  src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/jquery.ui.js"></script>
<script type="text/javascript"  src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/themes/ui-lightness/jquery.ui.css"  />
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
        <ul class="tab">
            <li class="active">商品推荐</li>
        </ul>

        <div class="wrap">
        <div class="public_select table" style=" padding: 20px 0;">
            <table id="my_goods" class="table" style="text-align: center;" >
                <tr>
                    <th colspan="12">
                        <div class="scarch_order">
                            <form id="my_goods_form" method="get">
                                <input type="hidden" name="app" value="service">
                                <input type="hidden" name="act" value="vshop_goods">
                                商品名
                                <input type="text" class="text_normal" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword']); ?>"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                新增时间
                                <input class="text_normal width2" type="text" name="add_time_from" id="add_time_from" value="<?php echo htmlspecialchars($_GET['add_time_from']); ?>" /> &#8211;
                                <input class="text_normal width2" id="add_time_to" type="text" name="add_time_to" value="<?php echo htmlspecialchars($_GET['add_time_to']); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;托管商家
                                <select name="store_id" class="sgcategory">
                                    <?php if ($this->_var['stores']): ?>
                                        <option value="-1">全部</option>
                                        <?php $_from = $this->_var['stores']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store');if (count($_from)):
    foreach ($_from AS $this->_var['store']):
?>
                                            <option value="<?php echo $this->_var['store']['store_id']; ?>" <?php if ($_GET['store_id'] == $this->_var['store']['store_id']): ?>selected="selected"<?php endif; ?> ><?php echo $this->_var['store']['store_name']; ?></option>
                                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                    <?php else: ?>
                                        <option value="">没有找到商家</option>
                                    <?php endif; ?>
                                </select>



                                <br><br>

                                分类&nbsp;&nbsp;&nbsp;&nbsp;
                                <select name="cate_id" id="cate_id">
                                    <option value="0">请选择...</option>
                                    <?php echo $this->html_options(array('options'=>$this->_var['scategories'],'selected'=>$_GET['cate_id'])); ?>
                                </select>

                                
                                <!--<select name="users" class="sgcategory">-->
                                    <!--<option value="-1">全部</option>-->
                                    <?php if ($this->_var['users']): ?>
                                    <?php $_from = $this->_var['users']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['user']):
?>
                                    <!--<option value="<?php echo $this->_var['user']['user_id']; ?>" &lt;!&ndash;<?php if ($_GET['users'] == $this->_var['user']['user_id']): ?>&ndash;&gt;selected="selected"&lt;!&ndash;<?php endif; ?>&ndash;&gt; ><?php echo $this->_var['user']['user_name']; ?></option>-->
                                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                    <?php endif; ?>
                                <!--</select>  &nbsp;&nbsp;&nbsp;&nbsp;-->
                                <input type="submit" class="btn" value="搜索" />
                            </form>
                        </div>
                    </th>
                </tr>
                <tr class="line_bold"><th colspan="13">&nbsp;</th></tr>
                <tr class="line_bold">
                    <th class="width1"><input type="checkbox" id="all" class="checkall"/></th>
                    <th colspan="6">
                        <span class="all"><label for="all">全选</label></span>
                        <a href="javascript:void(0);" class="edit" ectype="batchbutton" uri="index.php?app=service&amp;act=goods_batch_edit&ret_page=<?php echo $this->_var['page_info']['curr_page']; ?>&p=service" name="id">编辑</a>
                        <a href="javascript:void(0);" class="delete" uri="index.php?app=service&act=drop" name="id" presubmit="confirm('您确定要删除所选店铺的所有信息（包括商品、订单等）吗？')" ectype="batchbutton">删除</a>

                    </th>
                    <th colspan="6">&nbsp;</th>
                </tr>

                <tr class="gray"  ectype="table_header">
                    <th width="25"></th>
                    <th width="40"  style="padding-left: 5px;">缩略图</th>
                    <th width="150" colspan="2" coltype="editable" column="goods_name" checker="check_required" inputwidth="90%" title="排序"  class="cursor_pointer" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span ectype="order_by">商品名称</span></th>
                    <th width="70" column="cate_id" title="排序"  class="cursor_pointer"><span ectype="order_by">商品分类</span></th>
                    <th width="55" coltype="editable" column="brand" checker="check_required" inputwidth="55px" title="排序"  class="cursor_pointer"><span ectype="order_by">状态</span></th>
                    <th width="50" class="cursor_pointer" coltype="editable" column="price" checker="check_number" inputwidth="50px" title="排序"><span ectype="order_by">价格</span></th>
                    <th width="50" class="cursor_pointer" coltype="editable" column="stock" checker="check_pint" inputwidth="50px" title="排序"><span ectype="order_by">库存</span></th>
                    <th width="60" coltype="switchable" column="recommended" onclass="right_ico" offclass="wrong_ico" title="排序"  class="cursor_pointer">
                        &nbsp;&nbsp;&nbsp;<span ectype="order_by">店铺名</span></th>
                    <th width="30" coltype="switchable" column="if_show" onclass="right_ico" offclass="wrong_ico" title="排序"  class="cursor_pointer">佣金比例</th>
                    <th width="100">
                        &nbsp;&nbsp;&nbsp;操作
                    </th>

                </tr>
            <?php if ($this->_var['page_info'] && $this->_var['page_info']['goodsList'] && count ( $this->_var['page_info']['goodsList'] ) > 0): ?>
                <?php $_from = $this->_var['page_info']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['_goods_f'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_goods_f']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['_goods_f']['iteration']++;
?>
                    <tr class="line<?php if (($this->_foreach['_goods_f']['iteration'] == $this->_foreach['_goods_f']['total'])): ?> last_line<?php endif; ?>" ectype="table_item" idvalue="<?php echo $this->_var['goods']['goods_id']; ?>">
                        <td width="25" class="align2"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['goods']['goods_id']; ?>"/></td>
                        <td width="40" class="align2"><?php if ($this->_var['goods']['pd_id'] == 5): ?> <a href="<?php echo url('app=bdsh&act=goods_detail&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><?php else: ?><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><?php endif; ?> <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods']['default_image']; ?>" width="50" height="50"  /></a></td>
                        <td width="150" align="align2" colspan="2">
                            <p class="ware_text" style="width: 220px"><span class="color2" ectype="editobj"><?php echo sub_str($this->_var['goods']['goods_name'],60); ?></span></p>
                        </td>
                        <td width="65"><span class="color2"><?php echo nl2br($this->_var['goods']['cate_name']); ?></span></td>
                        <td width="50" class="align2"><span class="color2" >
                                        <?php if ($this->_var['goods']['dropState'] == 1): ?>正常
                                        <?php elseif ($this->_var['goods']['dropState'] == 2): ?>待审核
                                        <?php elseif ($this->_var['goods']['dropState'] == 3): ?>审核不通过
                                        <?php elseif ($this->_var['goods']['dropState'] == 4): ?>
                                        <label style="color: red;cursor: pointer" title="韩国馆一审通过，请耐心等待最终审核">一审通过</label>
                                        <?php endif; ?>
                                    </span></td>
                        <td width="50" class="align2"><?php if ($this->_var['goods']['spec_qty']): ?><span class="cursor_pointer"><?php else: ?><span class="color2" ectype="editobj"><?php endif; ?><?php echo $this->_var['goods']['price']; ?></span></td>
                        <td width="50" class="align2">
                            <?php if ($this->_var['goods']['spec_qty']): ?><span class="cursor_pointer">
                            <?php else: ?><span class="color2" ectype="editobj">
                            <?php endif; ?>
                            <?php echo $this->_var['goods']['stock']; ?></span>
                        </td>
                        <td width="50" class="align2"><span style="margin:0px 5px;"><span class="color2"><?php echo $this->_var['goods']['store_name']; ?></span></td>
                        <td width="30" class="align2">
                            <span style="margin:0px 5px;" ectype="editobj"><?php echo $this->_var['goods']['yongjinbili']; ?>%</span>
                        </td>
                        <td width="100">
                            <div style="float: left"><a href="<?php echo url('app=service&act=edit&id=' . $this->_var['goods']['goods_id']. '&p=service&pageNo=' . $this->_var['pageNo']. ''); ?>" class="edit">编辑</a>
                                <a href="javascript:drop_confirm('您确定要删除所选店铺的所有信息（包括商品、订单等）吗？', 'index.php?app=service&amp;act=drop&id=<?php echo $this->_var['goods']['goods_id']; ?>');" class="delete">删除</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
             <?php else: ?>
                <tr>
                    <td class="align2 member_no_records padding6" colspan="10"><?php echo $this->_var['lang'][$_GET['act']]; ?>没有符合条件的商品</td>
                </tr>
            <?php endif; ?>
            <?php if ($this->_var['page_info']['goodsList']): ?>
                <tr class="line_bold line_bold_bottom">
                    <td colspan="13"> </td>
                </tr>
                <tr>
                    <th><input type="checkbox" id="all2" class="checkall"/></th>
                    <th class="align1" colspan="3">
                        <span class="all"><label for="all">全选</label></span>
                    </th>
                    <th colspan="8"><?php echo $this->fetch('member.page.bottom.html'); ?></th>
                </tr>
            <?php endif; ?>
            </table>

            <br/>
            <form id="recommend_form" style="margin-left: 0px;" target="recommend_iframe" action="index.php?app=service&act=vshop_recomGoods" method="post">
                推荐到：
                <select name="position">
                    <option value="">请选择推荐位置</option>
                    <option value="1">精品集客小店——本地特卖</option>
                </select>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" style="width:50px;height: 30px;" onclick="toRecommend();" value="确 定">
                <input id="goods_ids" name="goods_ids" type="hidden" value="">
                <script>
                    function toRecommend() {
                        var goods_ids = "";
                        $(".checkitem").each(function(index){
                            if ($(this).attr("checked")) {
                                goods_ids += $(this).attr("value") + ",";
                            }
                        });
                        $("#goods_ids").val(goods_ids);
                        $("#recommend_form").action = $("recommend_iframe").attr("src");
                        $("#recommend_form").submit();
                        $(".checkitem").each(function(index){
                            $(this).attr("checked", false);
                        });
                        alert("您推荐的商品正在审核中...");

                    }
                </script>
            </form>
            <br/>
            <iframe id="recommend_iframe" name="recommend_iframe" width="780" height="400" src="index.php?app=service&act=vshop_recomGoods">
            </iframe>
        </div>
        <div class="wrap_bottom"></div>
        </div>
    </div>
</div>
<div class="clear"></div>
</div>
<?php echo $this->fetch('footer.html'); ?>
