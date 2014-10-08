<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>扫码下单</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/Safety.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/common.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/address.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery-1.6.2.min.js"></script>
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/address.js"></script>
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/hide_share_button.js"></script>
    <script type="text/javascript">
        $(function(){
            $("#defaultAddressPic").click(function(){
                var flag = $("#daifu").val();
                if(flag == '1'){
                    $("#daifu").val(0);
                    $("#defaultAddressPic").attr("src","<?php echo $this->_var['site_url']; ?>/weixin/templates/style//images/address/an-n2-1.jpg");
                }else{
                    $("#daifu").val(1);
                    $("#defaultAddressPic").attr("src","<?php echo $this->_var['site_url']; ?>/weixin/templates/style//images/address/an-n2.jpg");
                }
            });
        });

        function setOrder(){
            var id=$("#id").val();
            var user_id="<?php echo $this->_var['member']['user_id']; ?>";
            if( !user_id || user_id==null || user_id==" " || typeof user_id=="undefined"|| user_id <= 0  ){
                window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member&act=login&ret_url='+encodeURIComponent('/weixin/index.php?app=store&act=setOrderPage&id=<?php echo $this->_var['store']['store_id']; ?>');
                return;
            }
            var store_name=$("#store_name").val();
            var goods_name=$("#goods_name").val();
            if(!goods_name.match(/^[\u4E00-\u9FA5|\w|\d]{2,20}$/)){
                alert('请输入商品名(2-20个字)');
                $("#goods_name").css({"border":'1px solid #FF0000'});
                return;
            }else{
                $("#goods_name").css({"border":'1px solid #E5E5E5'});
            }

            var order_count=$("#order_count").val();
            if(order_count=='请输入订单总额'|| order_count=='' || isNaN(order_count) || order_count < 0 || order_count > 1000000){
                alert('请输入合法订单总额(1000000元以内)');
                $("#order_count").val('请输入订单总额');
                $("#order_count").css({"border":'1px solid #FF0000'});
                return;
            }

            var daifu=$("#daifu").val();



            $.ajax({
                url:"<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&act=setOrder",
                type:"POST",
                data:{"id":id,"store_name":store_name,"goods_name":goods_name,"order_count":order_count,"daifu":daifu},
                success:function(result){
                    result = eval("("+result+")");
                    if(result.retval==2){ //买家自己付款
                        $(".address-cen2").disabled=true;
                        if(confirm("生成订单成功！现在去支付吗?")){
                            window.location='<?php echo $this->_var['mobile_pay_url']; ?>/dajike-account/mobile/list.action?orderSn='+result.msg+"&userId=<?php echo $this->_var['member']['user_id']; ?>";
                        }else{
                            alert("您也可以在会员中心支付该订单");
                            window.location.reload();
                        }
                    }else if(result.retval==1){//商家代付
                        $(".address-cen2").disabled=true;
                        alert("生成订单成功！请告知商家代为支付该订单!");

                        window.location.href='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>';
                    }else if(result.retval== -1 ){//未登录
                        window.location= "<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member&act=login&ret_url="+result.msg;
                    }else{
                        alert(result.msg);
                    }
                }
            });

        }
    </script>
    <style type="text/css">
        .address-cen td {
            border-bottom: 0;
            border-left: 0;
        }
    </style>
</head>
<body>
<div style="width: 320px;margin: 0px auto">
    <div style="padding-top: 50px;">
        <div class="address-t">
            <div class="address-l"><a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>">首页</a></div>
            <div class="address-c1">扫码下单</div>
            <div class="address-r">
                <?php if (! $this->_var['member'] || $this->_var['member']['user_id'] <= 0): ?>
                    <a onclick="window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member&act=login&ret_url='+encodeURIComponent('/weixin/index.php?app=store&act=setOrderPage&id=<?php echo $this->_var['store']['store_id']; ?>')"
                       href="javascript:void(0)" style="color:#ffffff">
                        &nbsp;登&nbsp;录
                    </a>
                <?php else: ?>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=goods&act=index1" style="color:#ffffff">移动商城</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="address-cen" >
            <form action="" method="post" id="setOrderForm">
                <table style="font-size: 14px; border:0 ;"border-bottom="0" >
                    <tr>
                        <td width="100px;" align="center" valign="middle">店铺名：</td>
                        <td>
                            <input type="hidden" name="id" id="id" value="<?php echo $this->_var['store']['store_id']; ?>"/>
                            <input class="input-1" style="width: 200px" id="store_name" name="store_name" readonly="readonly" value="<?php echo $this->_var['store']['store_name']; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="100px;" align="center" valign="middle">商品名：</td>
                        <td> <input class="input-1" style="width: 200px" id="goods_name" name="goods_name" value="请输入商品名(2-20个字)"
                                    onblur="if( !this.value.match(/^[\u4E00-\u9FA5|\w|\d]{2,20}$/) ){alert('请输入商品名(2-20个字)');this.value='请输入商品名(2-20个字)';this.style.border='1px solid #FF0000';}else{
                                        this.style.border='1px solid #E5E5E5'
                                }"
                                    onfocus=" if(this.value=='请输入商品名(2-20个字)'){this.value='';this.style.border='1px solid #E5E5E5'}"
                                />
                        </td>
                    </tr>

                    <tr>
                        <td width="100px;" align="center" valign="middle">订单总额：</td>
                        <td> <input class="input-1" style="width: 200px" id="order_count" name="order_count"
                                    onblur="if( isNaN(this.value)|| this.value < 0 ){
                                                this.value='请输入订单总额';this.style.border='1px solid #FF0000';
                                              }else{
                                                this.style.border='1px solid #E5E5E5';
                                              }"
                                    onfocus=" if(this.value=='请输入订单总额'){this.value='';this.style.border='1px solid #E5E5E5'}"
                                /></td>
                    </tr>

                </table>
                <div class="address-cen1" style="display: none">
                    <div class="mo-ren">目前只能由商家代付
                        <input type="hidden" value="1" name="daifu" id="daifu"/>
                        <span>
                                <img src="/weixin/templates/style//images/address/an-n2.jpg"   width="64" height="23" alt="是否商家代付"  />
                        </span>
                    </div>
                </div>
                <div class="address-cen1" style="display: none">
                    <div class="mo-ren" style="height: 60px;" >
                        提示：商家代付由商家在"会员中心"-"代付订单"中处理
                    </div>
                </div>

                <div class="address-cen2" onclick="setOrder()" style="color: #fff;cursor:pointer;font-size:14px;background: none repeat scroll 0 0 #C40000;" >
                    <?php if (! $this->_var['member'] || $this->_var['member']['user_id'] <= 0): ?>
                        您还没有登录，去登陆->
                    <?php else: ?>
                        生成订单
                    <?php endif; ?>

                </div>
            </form>
        </div>
    </div>
</div>

<span style="display: none">
    <script type="text/javascript">
        var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000080062'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s22.cnzz.com/z_stat.php%3Fid%3D1000080062' type='text/javascript'%3E%3C/script%3E"));
    </script>
</span>

</body>
</html>