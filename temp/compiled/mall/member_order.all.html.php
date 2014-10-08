<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>订单列表</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/common.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/order-inquiry.css" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['SITE_URL']; ?>/weixin/templates/js/jquery-1.6.2.min.js"></script>
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/hide_share_button.js"></script>
    <script type="text/javascript">
        function deleteOrder(order_id){
            var flag = confirm("删除订单后无法找回，你确定要删除该订单吗?");
            if(flag){
                $.get(
                        "<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=delete",
                        {"order_id":order_id},
                        function(result){
                            var result = eval("("+result+")");
                            alert(result.msg);
                            window.location.reload();
                        })
            }
        }
        $(function(){
            var status= "<?php echo $this->_var['time']; ?>";
            if(status == 'recentThreeMonths' || status == ""){
                $(".cancel1-r").css({"background":"none repeat scroll 0 0 #CCCCCC"});
                $(".cancel1-l").css({"background":"none repeat scroll 0 0 #999999"});
            }else{
                $(".cancel1-1").css({"background":"none repeat scroll 0 0 #CCCCCC"});
                $(".cancel1-r").css({"background":"none repeat scroll 0 0 #999999"});
            }
        });
    </script>
</head>
<body>
<div style="width: 320px;margin: 0px auto">
<div style="padding-top: 50px;">
<div class="address-t" >
    <div class="address-l"><a href="javascript:history.go(-1)">返回</a></div>
    <div class="address-c2" style="padding-left: 55px;">订单列表</div>
    <?php echo $this->fetch('member.index_header.html'); ?>
</div>
<div class="Safety-cen-1">
    <div class="cancel1">
        <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user']['user_id']; ?>&status=<?php echo $this->_var['status']; ?>&time=recentThreeMonths"><div class="cancel1-l">近3个月的订单</div></a>
        <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user']['user_id']; ?>&status=<?php echo $this->_var['status']; ?>&time=threeMonthsAgo"><div class="cancel1-r">3个月前的订单</div></a>
    </div>
    <?php if (! $this->_var['orders'] || count ( $this->_var['orders'] ) == 0): ?>
        <?php if ($this->_var['time'] == 'threeMonthsAgo'): ?>
            <div class="my1">
                <p><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/images/k-n.png" width="93" height="93" alt="哭脸" /></p>
                <p style="text-align: center">没有符合条件的结果!</p>
            </div>
        <?php else: ?>
            <div class="my1">
                <p><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/images/k-n.png" width="93" height="93" alt="哭脸" /></p>
                <p style="text-align: center">您最近还没购买过任何商品，快去购物吧!</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['order']):
?>
            <div class="image-text0">

                <div class="image-text-t">
                    <div class="image-text-t1">
                        <p>订单编号：<?php echo $this->_var['order']['order_sn']; ?></p>
                        <p>下单时间：<?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['add_time']); ?>
                        </p>
                    </div>
                    <div class="image-text-t2">
                        <a href="javascript:void(0)" onclick="deleteOrder('<?php echo $this->_var['order']['order_id']; ?>')">
                            <img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/images/la-ji.jpg" width="23" height="27" />
                        </a>
                    </div>
                </div>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=detail&order_id=<?php echo $this->_var['order']['order_id']; ?>">
                <div class="image-text-c">
                    <?php $_from = $this->_var['order']['order_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
                        <dl>
                            <dt>
                                <?php if (! $this->_var['goods']['goods_image']): ?>
                                    <img src="<?php echo $this->_var['site_url']; ?>/data/system/default_goods_image.gif" width="100" height="100" alt="<?php echo sub_str($this->_var['goods']['goods_name'],30); ?>" />
                                <?php else: ?>
                                    <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods']['goods_image']; ?>" width="100" height="100" alt="<?php echo sub_str($this->_var['goods']['goods_name'],30); ?>" />
                                <?php endif; ?>
                            </dt>
                            <dd>
                                <h3><?php echo $this->_var['goods']['goods_name']; ?></h3>
                                <p><?php echo $this->_var['goods']['specification']; ?></p>
                                <strong>￥<?php echo number_format2($this->_var['goods']['price']); ?>×<?php echo $this->_var['goods']['quantity']; ?></strong>
                            </dd>
                        </dl>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </div>
                </a>
                <div class="image-text-b">
                    <div class="image-text-b1">
                        <p>订单金额：<span><?php echo number_format2($this->_var['order']['order_amount']); ?></span></p>
                        <p>订单状态：<span>
                            <?php if ($this->_var['order']['status'] == 0): ?>
                                已取消
                            <?php elseif ($this->_var['order']['status'] == 10): ?>
                                已付款
                            <?php elseif ($this->_var['order']['status'] == 11): ?>
                                待付款
                            <?php elseif ($this->_var['order']['status'] == 20): ?>
                                待发货
                            <?php elseif ($this->_var['order']['status'] == 30): ?>
                                已发货
                            <?php elseif ($this->_var['order']['status'] == 40): ?>
                                订单完成
                            <?php endif; ?>
                        </span>
                        </p>
                    </div>

                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=detail&order_id=<?php echo $this->_var['order']['order_id']; ?>">
                            <div class="image-text-b2">
                                <?php if ($this->_var['order']['yue'] + $this->_var['order']['koushui_yue'] > 0): ?>
                                    支付余款
                                <?php else: ?>
                                    立即支付
                                <?php endif; ?>
                            </div>
                        </a>

                        <!--<div class="image-text-b2" style="background: none repeat scroll 0 0 #CCCCCC">商家代付</div>-->

                </div>

            </div>
            <div class="blank" style="height:10px;"></div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>


    
        <script type="text/javascript">
            function formerPage(curr_page,page_count){
//                alert(curr_page-1);
                var page = parseInt(curr_page)-1
                if( page> 0 ){
                    window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user_id']; ?>&time=<?php echo $this->_var['time']; ?>&status=<?php echo $this->_var['status']; ?>&page="+page;
                }else{
                    alert("已经到了首页！");
                    return;
                }
            }
            function nextPage(curr_page,page_count){
                var page = parseInt(curr_page)+1;
                if(page > page_count){
                    alert("已经到了最后一页！");
                }else{
                    window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user_id']; ?>&time=<?php echo $this->_var['time']; ?>&status=<?php echo $this->_var['status']; ?>&page="+page;
                }
            }
        </script>
        <div class="ddi">
            <div class="left" style="width: 235px; padding-left: 75px;">
                <a href="javascript:formerPage('<?php echo $this->_var['page']['curr_page']; ?>','<?php echo $this->_var['page']['page_count']; ?>')" class="a1" style="margin-right: 20px;"></a>
                <a style="float: left; height: 26px; line-height: 26px; font-family: tahoma; font-size: 14px; margin-right: 20px;"><?php echo $this->_var['page']['curr_page']; ?>/<?php echo $this->_var['page']['page_count']; ?></a>
                <a href="javascript:nextPage('<?php echo $this->_var['page']['curr_page']; ?>','<?php echo $this->_var['page']['page_count']; ?>')" class="a2"></a>
            </div>
        </div>
    
    <?php endif; ?>

    <div class="00" style="height:50px;"></div>

</div>
</div>
</div>
<?php echo $this->fetch('cnzz.html'); ?>
</body>
</html>
