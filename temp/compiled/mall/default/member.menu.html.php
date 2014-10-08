    <div id="left">
       <h4 class="ti_01">个人主页</h4>
       <div class="lm_01">
    <?php $_from = $this->_var['_member_menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
        <?php if ($this->_var['item']['submenu']): ?>
        <dl class="show_dl_menu">
            <dd class="show_menu" show_id="<?php echo $this->_var['item']['id']; ?>" style="cursor:pointer"><?php echo $this->_var['item']['text']; ?>
                <b></b>
            </dd>
            <dt>
        <?php $_from = $this->_var['item']['submenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'subitem');if (count($_from)):
    foreach ($_from AS $this->_var['subitem']):
?>
            
            <?php if ($this->_var['subitem']['name'] == $this->_var['_curitem']): ?>            
                <div class="item_curr" show_menu_id="<?php echo $this->_var['item']['id']; ?>">
                <a <?php if ($this->_var['subitem']['name'] == 'cj'): ?>href="javascript:;" class="cj_link"<?php elseif ($this->_var['subitem']['name'] == 'daima'): ?>href="<?php echo $this->_var['subitem']['url']; ?>" target="_blank"<?php else: ?>href="<?php echo $this->_var['subitem']['url']; ?>"<?php endif; ?> ><?php echo $this->_var['subitem']['text']; ?></span></a>


            <?php else: ?>
                <div class="item" show_menu_id="<?php echo $this->_var['item']['id']; ?>">
                    <a <?php if ($this->_var['subitem']['name'] == 'cj'): ?>href="javascript:;" class="cj_link"<?php elseif ($this->_var['subitem']['name'] == 'daima'): ?>href="<?php echo $this->_var['subitem']['url']; ?>" target="_blank"<?php else: ?>href="<?php echo $this->_var['subitem']['url']; ?>"<?php endif; ?> ><?php echo $this->_var['subitem']['text']; ?></span></a>
            <?php endif; ?>

            <?php if ($this->_var['subitem']['name'] == 'bank'): ?><img src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/help-round.png" title="银行卡作为大集客与您进行资金结算的唯一账号，请您务必事先绑定银行卡，以方便大集客与您进行资金结算。" /><?php endif; ?>
            </div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        	</dt>
        </dl>
        <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php if ($this->_var['_member_menu']['overview']): ?>
        <div style="padding: 10px;border-top: 1px solid #E3E3E3;">
            <p style="line-height: 20px;">您目前不是卖家，您可以: </p>
            <a href="<?php echo $this->_var['_member_menu']['overview']['url']; ?>" style="background:url('<?php echo $this->res_base . "/" . 'images/member/gousite.png'; ?>') repeat scroll ;display: block;width: 153px;height: 42px;" title="<?php echo $this->_var['_member_menu']['overview']['text']; ?>"></a>
        </div>
        <div class="clear"></div> 
        <?php endif; ?>
           <?php if ($this->_var['member_type'] == '01' && $this->_var['_user']['spreader_type'] != 1 && $this->_var['_user']['shop_name'] == "" && $this->_var['_user']['shop_name'] == null): ?>
           <div style="padding: 10px;border-top: 1px solid #E3E3E3;">
               <p style="line-height: 20px;">您还不是集客小店,您可以: </p>
               <a href="index.php?app=jkxd_portal" style="background:url('<?php echo $this->res_base . "/" . 'images/member/gousite.png'; ?>') repeat scroll 0px -49px ;display: block;width: 153px;height: 42px;" title="立即开通我的集客小店"></a>
           </div>
           <div class="clear"></div>
           <?php endif; ?>
        </div>
    </div>

    <form method="post" name="cj_form" id="cj_form" action="<?php echo $this->_var['TO_PLCHOUJIANG_URL']; ?>">
        <input type="hidden" name="userId" value="<?php echo $this->_var['visitor']['user_id']; ?>">
    </form>
<script>
    $(".cj_link").bind("click",function(){
        $("#cj_form").submit();
    })

    $(".show_menu").bind("click",function(){
        if($(".show_dl_menu dt div[show_menu_id='"+$(this).attr("show_id")+"']").css("display")=="none"){
            $(".show_dl_menu dt div[show_menu_id='"+$(this).attr("show_id")+"']").css("display","block");
            $(".show_dl_menu dd[show_id='"+$(this).attr("show_id")+"'] b").css("background","url('<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/member/user_icoooo.png') no-repeat scroll 0 0 rgba(0, 0, 0, 0)");
        }else{
            $(".show_dl_menu dt div[show_menu_id='"+$(this).attr("show_id")+"']").css("display","none");
            $(".show_dl_menu dd[show_id='"+$(this).attr("show_id")+"'] b").css("background","url('<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/member/user_icoooo2.png') no-repeat scroll 0 0 rgba(0, 0, 0, 0)");
        }
    })
</script>	