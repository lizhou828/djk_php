<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $this->_var['title']; ?></title>
    <link href="<?php echo $this->res_base . "/" . 'common.css'; ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->res_base . "/" . 'order-inquiry.css'; ?>" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/hide_share_button.js"></script>
</head>
<body>
<script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function(){
        var pass_status = "<?php echo $this->_var['status']; ?>";

        var order_count = "<?php echo $this->_var['page']['item_count']; ?>"
        var id="#"+pass_status+" li";
        $(".sale-t li").css({"background":"#ccc","color":"#333"});
        if(parseInt(pass_status)==11){
            if(order_count > 0  ){
                $(".sale-t #11 em").css({"display":""});
                $(".sale-t #11 em").html(order_count);
            }else{
                $(".sale-t #11 em").css({"display":"none"});
            }

        }else if(parseInt(pass_status)==10){
            if(order_count > 0  ){
                $(".sale-t #10 em").css({"display":""});
                $(".sale-t #10 em").css({"left":"133px"});
                $(".sale-t #10 em").html(order_count);
            }else{
                $(".sale-t #10 em").css({"display":"none"});
            }

        }else if(parseInt(pass_status)==30){
            if(order_count > 0  ){
                $(".sale-t #30 em").css({"display":""});
                $(".sale-t #30 em").css({"left":"210px"});
                $(".sale-t #30 em").html(order_count);
            }else{
                $(".sale-t #30 em").css({"display":"none"});
            }



        }else if(parseInt(pass_status)==40){
            if(order_count > 0  ){
                $(".sale-t #40 em").css({"display":""});
                $(".sale-t #40 em").css({"left":"287px"});
                $(".sale-t #40 em").html(order_count);
            }else{
                $(".sale-t #40 em").css({"display":"none"});
            }
        }
        $(id).css({"background":"#999","color":"#fff"});
    });

    function order_inquiry(status){
        var id='<?php echo $this->_var['user_id']; ?>';
        var type='<?php echo $this->_var['type']; ?>'
        window.location.href='index.php?app=member_order&act=all&user_id='+id+'&status='+status+'&type='+type;
    }
</script>
<div style="width: 320px;margin: 0px auto">
    <div style="padding-top: 45px;">
        <div class="address-t" >
            <div class="address-l">
                <a href="javascript:history.go(-1)">返回</a>
            </div>
            <div class="address-c2" style="padding-left: 40px;text-align: center">
                <span style="margin-left: auto;margin-right: auto"><?php echo $this->_var['title']; ?></span>
            </div>
            <?php echo $this->fetch('member.index_header.html'); ?>
        </div>
        <div class="Safety-cen-1">
            <div class="sale">
                <div class="sale-t">
                    <ul>
                        <div id="11">
                            <em style="display: none">0</em>
                            <a href="javascript:order_inquiry(11)"><li>待付款</li></a>
                        </div>
                    </ul>
                    <ul>
                        <div id="10">
                            <em style="display: none">0</em>
                            <a href="javascript:order_inquiry(10)"><li >已付款</li></a>
                        </div>
                    </ul>
                    <ul>
                        <div id="30">
                            <em style="display: none">30</em>
                            <a href="javascript:order_inquiry(30)"><li >已发货</li></a>
                        </div>
                    </ul>
                     <ul style="border-right:0px;">
                <div id="40">
                    <em style="display: none">0</em>
                    <a href="javascript:order_inquiry(40)"><li>已完成</li></a>
                </div>
            </ul>
                </div>
            <div class="sale-c">
            <?php if ($this->_var['orders'] && count ( $this->_var['orders'] ) > 0): ?>
                <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');$this->_foreach['_order'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_order']['total'] > 0):
    foreach ($_from AS $this->_var['order']):
        $this->_foreach['_order']['iteration']++;
?>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=detail&order_id=<?php echo $this->_var['order']['order_id']; ?>&type=<?php echo $this->_var['type']; ?>">
                    <dl <?php if (($this->_foreach['_order']['iteration'] == $this->_foreach['_order']['total'])): ?>style="border-bottom:0px;"<?php endif; ?>>
                        <?php if (! $this->_var['order']['order_goods'] || count ( $this->_var['order']['order_goods'] ) == 0): ?>
                            <dt>
                                <img src="<?php echo $this->res_base . "/" . 'images/b-z.jpg'; ?>" width="100" height="100" />
                            </dt>
                        <?php else: ?>
                            <dt>
                                <?php $_from = $this->_var['order']['order_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['o_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['o_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['o_goods']['iteration']++;
?>
                                    <?php if (($this->_foreach['o_goods']['iteration'] - 1) <= 0): ?>
                                        <img src="<?php if (! $this->_var['goods']['goods_image']): ?> <?php echo $this->_var['site_url']; ?>/data/system/default_goods_image.gif <?php else: ?><?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods']['goods_image']; ?><?php endif; ?>" width="100" height="100" />
                                    <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            </dt>
                        <?php endif; ?>
                        <dd>
                            <p>订单编号：<?php echo $this->_var['order']['order_sn']; ?></p>
                            <span>
                                <!--<h3><a href="#">></a></h3>-->
                                <strong>
                                    <?php if ($this->_var['type'] && $this->_var['type'] == 'buyer'): ?>
                                        卖家:<?php echo sub_str($this->_var['order']['seller_name'],30); ?>
                                    <?php elseif ($this->_var['type'] && $this->_var['type'] == 'seller'): ?>
                                        买家:<?php echo sub_str($this->_var['order']['buyer_name'],30); ?>
                                    <?php endif; ?>
                                </strong>
                            </span>
                            <p>
                              下单时间：<?php echo time_format2("Y-m-d H:i:s",$this->_var['order']['add_time']); ?>
                            </p>
                            <p>订单金额：<?php echo number_format2($this->_var['order']['order_amount']); ?></p>
                        </dd>
                    </dl>
                     </a>

                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

                
                <script type="text/javascript">
                    function formerPage(curr_page,page_count){
//                alert(curr_page-1);
                        var page = parseInt(curr_page)-1
                        if( page> 0 ){
                            window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&status=<?php echo $this->_var['status']; ?>&page="+page;
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
                            window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member_order&act=all&user_id=<?php echo $this->_var['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&status=<?php echo $this->_var['status']; ?>&page="+page;
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
                
            <?php else: ?>
               <div style="height:150px;font-size: 14px;padding-top: 100px;padding-bottom: 10px;text-align: center">
                   <span >没有相关订单！</span>
               </div>
            <?php endif; ?>
            </div>
            </div>

        </div>
    </div>
</div>
<?php echo $this->fetch('cnzz.html'); ?>
</body>
</html>
