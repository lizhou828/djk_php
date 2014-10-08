
<div class="menu">
    <div class="bod1">
        <div class="bod2">
            <ul>
                <li><a href="/"  <?php if ($this->_var['index'] == '1'): ?> class="dq_b" <?php endif; ?>>首页</a></li>
                <li><a href="<?php echo url('app=market&pd_id=2'); ?>" <?php if ($this->_var['market_category']): ?> class="dq_b"<?php endif; ?> >超市</a></li>
                <li><a class="pngFix" href="<?php echo url('app=tese&act=get_techan_goods&tese_cate_id=1613&pd_id=3'); ?>" target='_blank'>特色大集</a></li>
                <li><a href="<?php echo url('app=bdsh&pd_id=5'); ?>"target='_blank' >本地生活</a></li>
                <li><a href="<?php echo url('app=gczg&pd_id=6'); ?>" target='_blank' >工厂直供</a></li>
                <!--<li class="link"><a href="<?php echo url('app=search&act=groupbuy'); ?>" target='_blank'><span>团购</span></a></li>-->
                <!--<li class="link"><a href="#"><span>优惠券</span></a></li>-->
                <li><a href="<?php echo url('app=jifen'); ?>" <?php if ($_GET['app'] == 'jifen'): ?>class="dq_b"<?php endif; ?>>积分中心</a></li>
                <li><a href="<?php echo url('app=lydj&pd_id=4'); ?>" target="_blank" >旅游大集</a></li>
                <li><a href="<?php echo url('app=hgg&pd_id=10&country_id=2'); ?>" target='_blank'>国际馆</a></li>
                <?php if ($this->_var['member']['user_id'] > 0 && $this->_var['member']['shop_name'] == null): ?>
                    <li><a href="<?php echo $this->_var['SHOP_DOMAIN']; ?>/?apply=1" target="_blank" >集客小店</a></li>
                <?php else: ?>
                <li><a href="<?php echo $this->_var['SHOP_DOMAIN']; ?>" target="_blank" >集客小店</a></li>
                <?php endif; ?>
                <li><a href="<?php echo url('app=service&act=register_service'); ?>" target='_blank'>服务中心</a></li>
            </ul>
            <em><a href="javascript:;" id="cj_link">我要抽奖</a></em>
        </div>
    </div>
</div>
<form method="post" name="cj_form" id="cj_form" action="<?php echo $this->_var['TO_PLCHOUJIANG_URL']; ?>">
<input type="hidden" name="userId" value="<?php echo $this->_var['visitor']['user_id']; ?>">
</form>
<script type="text/javascript">
    $("#cj_link").bind("click",function(){
        $("#cj_form").submit();
    })
</script>
