<?php echo $this->fetch('member.header.html'); ?>
<script>

    function sava_img(){
        var obj=document.getElementById("portrait").value;
        if(obj!=""){
            document.getElementById("saveImg").submit();
        }else{
            alert("请选择图片");
        }
    }
</script>
<style>
    .p1 span {
        color: red;
        font-size: 14px;
        font-weight: bold;
    }
    .remind dd span {
        color: red;
        font-size: 14px;
        font-weight: bold;
    }
</style>
<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <ul class="tab">
            <li class="<?php if (! $_GET['act']): ?>active<?php else: ?>normal<?php endif; ?>"   ><a href="index.php?app=service&p=service">账户概览</a></li>
        </ul>
        <div class="wrap">
           <div class="public">

                <div class="tsh">
                    <dl>
                        <?php if (! $this->_var['user']['phone_mob'] || $this->_var['user']['phone_mob'] == '' || $this->_var['user']['phone_mob_bind_status'] == 0 || ! $this->_var['user']['email'] || $this->_var['user']['email'] == ''): ?>

                        <dd style="background:url(<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/member/ico_0009.png) no-repeat;"></dd>
                        <dt>

                        <p class="p1">&nbsp;&nbsp;&nbsp;&nbsp;您的账户&nbsp;&nbsp;  <span style="color: red">安全系数 一般</span></p>
                        <?php else: ?>

                        <dd style="background:url(<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/member/ico_0009_1.png) no-repeat;"></dd>
                        <dt>

                        <p class="p1">&nbsp;&nbsp;&nbsp;&nbsp;您的账户&nbsp;&nbsp;  <span style="color: red">安全系数 很高</span></p>
                        <?php endif; ?>
                        </dt>
                        
                         <?php if (! $this->_var['user']['phone_mob'] || $this->_var['user']['phone_mob'] == ''): ?>
                        <p class="p2" style="margin-left: 35px">
                        <span class="field_notice" style="float: right; margin-right: 15px">未设置</span>
                        <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上设置]</a>
                        &nbsp;&nbsp;&nbsp;&nbsp;您还没有绑定安全手机，请尽快设置安全手机！
                        </p>
                        
                        <?php else: ?>
                            <?php if ($this->_var['user']['phone_mob_bind_status'] == 0): ?>
                            <p class="p2">
                            <span class="field_notice" style="float: right; margin-right: 15px">未验证</span>
                            <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上验证]</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;手机号： <?php echo str_hidden($this->_var['user']['phone_mob']); ?> &nbsp;&nbsp;
                            &nbsp;&nbsp;
                            </p>
                            <?php else: ?>
                            <p class="p2" style="margin-top:5px;">  
                            <span class="field_notice" style="float: right; margin-right: 15px">已绑定</span>
                            <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上修改]</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;手机号： <?php echo str_hidden($this->_var['user']['phone_mob']); ?> &nbsp;&nbsp;
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                         <?php if (! $this->_var['user']['email'] || $this->_var['user']['email'] == ''): ?>                        
                        <p class="p2" style="margin-left: 35px">
                        <span class="field_notice" style="float: right; margin-right: 15px">未设置</span>
                        <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上设置]</a>
                        &nbsp;&nbsp;&nbsp;&nbsp;您还没有绑定安全邮箱，请尽快设置安全邮箱！
                        </p>
                        
                        <?php else: ?>
                            <?php if ($this->_var['user']['email_bind_status'] == 0): ?>
                            <p class="p2">
                            <span class="field_notice" style="float: right; margin-right: 15px">未验证</span>
                            <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上验证]</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;邮箱： <?php echo str_hidden($this->_var['user']['email']); ?>  &nbsp;&nbsp;
                            &nbsp;&nbsp;
                            </p>
                            <?php else: ?>
                            <p class="p2">
                            <span class="field_notice" style="float: right; margin-right: 15px">已绑定</span>
                            <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上修改]</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;邮箱：<?php echo str_hidden($this->_var['user']['email']); ?> &nbsp;&nbsp; &nbsp;&nbsp;
                                </p>
                            <?php endif; ?>

                        <?php endif; ?>
                        
                        
                        
                        <?php if (! $this->_var['user']['password2'] || $this->_var['user']['password2'] == ''): ?>
                     	<p class="p2" style="margin-left: 35px">
                        <span class="field_notice" style="float: right; margin-right: 15px">未设置</span>
                        <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上设置]</a>
                        &nbsp;&nbsp;&nbsp;&nbsp;您还没有设置支付密码，请尽快设置支付密码！
                        </p>
                        <?php else: ?>
                        <?php if ($this->_var['user']['email_bind_status'] == 0): ?>
                        <p class="p2" style="margin-left: 35px">
                        <span class="field_notice" style="float: right; margin-right: 15px">未验证</span>
                        <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上验证]</a>
                        &nbsp;&nbsp;&nbsp;&nbsp;支付密码： **********  &nbsp;&nbsp; &nbsp;&nbsp;
                        </p>
                        <?php else: ?>                        
                        <p class="p2" style="margin-left: 35px">
                        <span class="field_notice" style="float: right; margin-right: 15px">已设置</span>
                        <a href="index.php?app=member&act=aqzx&p=userInfo" style="float:right; margin-right:200px; color:blue">[点击马上修改]</a>
                        &nbsp;&nbsp;&nbsp;&nbsp;支付密码： **********  &nbsp;&nbsp; &nbsp;&nbsp;
                        </p>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                    </dl>
                </div>

                <div class="mumber_g">
                    <dl>
                        <dd>
                            <img src="<?php $imgarr = explode('.',$this->_var['user']['portrait']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '130X130' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" style="border:1px solid #a0a0a0;" width="130" height="130" alt="" />
                            <div class="fileInputContainer">
                                <form name="saveImg" id="saveImg" method="post" action="index.php?app=service&act=saveImg" enctype="multipart/form-data">
                                <input id="portrait" class="fileInput" type="file" name="portrait"/>
                                    <br><br><br><br><a href="javascript:sava_img()" id="sava_a" name="sava_a">保存</a>
                                </form>
                            </div>
                            </dd>
                        <dt>

                        <div class="k1" style="height:110px">
                            <div class="lt">
                            	<span class="sp1">服务中心区域：</span>
                                <span class="sp2"> &nbsp;&nbsp;<?php if ($this->_var['user']['user_name'] == 'djkhanguo'): ?>韩国馆<?php else: ?><?php echo $this->_var['user']['region_name']; ?><?php endif; ?></span>
                                
                                <span class="sp1">托管商家营业额：</span>
                                <span class="sp2"><strong style="color:#333" id="tuoguan_yye"><?php echo number_format2($this->_var['tuoguan_yye']); ?></strong> <em>元</em></span>
                                
                                <span class="sp1">本区营业额：</span>
                                <span class="sp2"><strong style="color:#333" id="benqu_yye"><?php echo number_format2($this->_var['all_yye']); ?></strong> <em>元</em></span>

                                <span class="sp1"><img src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/help-round.png" title="包括区域内所有商家的奖励收入和推荐服务中心所获得的奖励"  style="padding-right:6px;vertical-align: middle;"/>我的收益：</span>
                                <span class="sp2"><strong style="color:#333" id="benqu_yye"><?php echo number_format2($this->_var['shouyi']); ?></strong> <em>元</em>&nbsp;&nbsp;
                                    <?php if ($this->_var['show']): ?>
                                    <?php if ($this->_var['shouyi'] > 0): ?>
                                    <a href="javascript:;" ectype="dialog" uri="index.php?app=service&act=tixian&p=koushui" ectype="dialog"
                                       dialog_id="tixian_page" dialog_tixian dialog_width="550" style="color: #285ACB;">【提现】</a>
                                     <?php else: ?>
                                    <a href="javascript:alert('提现金额比如大于0');void(0)"style="color: #285ACB;">【提现】</a>
                                     <?php endif; ?>
                                    <?php endif; ?>
                                </span>
                                </div>
                            <div class="rt">
                           		<span style="display: block; height: 20px;"></span>
                                <span></span>
                            </div>
                        </div>
                        </dt>
                    </dl>
                </div>
                <div class="clear"></div>


                <div class="wrap_bottom"></div>
        </div>
        

        <div class="wrap_line">
            <div class="public_index">
                <div class="information_index">
                    <h3 class="title">服务中心提醒                       
                    </h3>

                    <?php if ($this->_var['flag']): ?>
                    <?php if ($this->_var['user']['member_type'] = 1): ?>
                    <div style="text-align: center;color: #27A196">亲爱的<?php echo $this->_var['user']['user_name']; ?>您好，欢迎您成为大集客认证商家</div>
                    <?php else: ?>
                    <div>亲爱的<?php echo $this->_var['user']['user_name']; ?>您好，欢迎您成为大集客服务中心</div>
                    <?php endif; ?>
                    <br><div style="text-align: center;color: red">！您还未绑定银行卡，请先绑定银行卡，方便大集客与您进行资金结算
                    <input type="button" value="现在绑定" onclick="bindbank();">
                    </div>
                    <?php endif; ?>
                    <div class="remind">



                        <dl>
                            <dt>订单提醒 </dt>
                            <dd>您有 <span class="red"><?php echo $this->_var['seller_stat']['submitted']; ?></span> 个待处理的订单，请尽快到“<a href="index.php?app=service&act=orders&type=submitted&p=service">已提交订单</a>”中处理</dd>
                            <dd>您有 <span class="red"><?php echo $this->_var['seller_stat']['accepted']; ?></span> 个待发货的订单，请尽快到“<a href="index.php?app=service&act=orders&type=accepted&p=service">待发货订单</a>”中处理</dd>
                            <dd>您有 <span class="red"><?php echo $this->_var['seller_stat']['shouhou']; ?></span> 个需要售后处理的订单，请尽快到“<a href="index.php?app=service&act=orders&shouhou=1&type=all_orders">售后处理订单</a>”中处理</dd>
                        </dl>
                        
                        <dl>
                            <dt>咨询提醒</dt>
                            <dd>您有 <span class="red"><?php echo $this->_var['seller_stat']['replied']; ?></span> 商品咨询，请尽快到“<a href="index.php?app=service&act=spzx&p=service">商品咨询</a>”中查看确认</dd>
                        </dl>
                        
                         <!--
                         <dl>
                            <dt>投诉提醒</dt>
                            <dd>您有 <span class="red">0</span> 商品投诉，请尽快到“<a href="index.php?app=service&act=orders&type=accepted">商品投诉中</a>”中查看确认</dd>
                        </dl>
                        -->
                        <a href="index.php?app=service&act=service&p=service&tuoguan=1" title="look_store" target="_blank" class="btn1 pos2"><span class="pic2">查看托管店铺</span></a>
                        <a href="index.php?app=service&act=orders&step=1&p=service" class="btn pos3" title="manage_order"><span class="pic1">查看我的订单</span></a>
                    </div>
                </div>

            </div>
            <div class="wrap_bottom"></div>
        </div>

<?php if ($this->_var['applying']): ?>
        <div class="wrap_line">
            <div class="public_index">
                <div class="information_index">
                    <h3 class="title">apply_remind</h3>
                    <div class="remind">
                        <dl>
                            <dt>apply_state</dt>
                            <dd><?php echo sprintf('store_applying', $this->_var['user']['sgrade']); ?></dd>
                        </dl>
                        <a href="index.php?app=apply&step=2&id=<?php echo $this->_var['user']['sgrade']; ?>" title="edit_store_info" class="btn1 pos2"><span class="pic2">edit_store_info</span></a>
                    </div>
                </div>

            </div>
            <div class="wrap_bottom"></div>
        </div>
<?php endif; ?>
        <div class="clear"></div>
        <div class="adorn_right1"></div>
        <div class="adorn_right2"></div>
        <div class="adorn_right3"></div>
        <div class="adorn_right4"></div>
    </div>
    <div class="clear"></div>
</div>

<form id="chouzhiform1" name="chouzhiform1" method="post" action="<?php echo $this->_var['TO_CHONGZHI_URL']; ?>" target="_blank">
    <input type="hidden" name="userId" value="<?php echo $this->_var['visitor']['user_id']; ?>"/>
</form>

<script>
    $("#chouzhi_btn").bind("click",function(){
        //window.open("","do_chouzhi");
        document.getElementById("chouzhiform1").submit();
    })

    function bindbank(){
        var uri =encodeURI("index.php?app=member&act=bankform");
        var id = 'member_bank_bind';
        var title = "绑定银行卡";
        var width = '700';
        ajax_form(id, title, uri, width);
    }
	
	/*window.onload = function(){
		//k1
        //$('.k1').showLoading({'addClass': 'loading-indicator-bars'});
        $.post("index.php?app=service&act=get_service_api","",function(data){
           var rs =  data;
           if(rs.code!=200){
               alert("服务器繁忙，获取用户信息失败");
           } else{
			   $("#benqu_yye").html(rs.regionYye);
			   $("#tuoguan_yye").html(rs.storeYye);
           }
		   //$('.k1').hideLoading();
        },"json")
	}*/
	
</script>
<iframe name="do_tixian" style="display:none;"></iframe>
<?php echo $this->fetch('footer.html'); ?>
