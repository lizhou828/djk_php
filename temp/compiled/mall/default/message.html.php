<?php echo $this->fetch('member.header.html'); ?>

<div class="jieh">
    <ul>

        <li style=" border-right:1px solid #b1cae8;" id='dd'>
        <a href="index.php">进入首页</a>
        </li>
    </ul>
</div>

<div class="content">
    <div class="particular">
        <div class="particular_wrap">
            <p class="<?php if ($this->_var['icon'] == "notice"): ?>success<?php else: ?>defeated<?php endif; ?>">
                <span></span>
                <b style="float: left; width:380px;"><?php echo $this->_var['message']; ?></b>
                <?php if ($this->_var['err_file']): ?>
                <b style="clear: both; float: left; font-size: 15px;">Error File: <strong><?php echo $this->_var['err_file']; ?></strong> at <strong><?php echo $this->_var['err_line']; ?></strong> line.</b>
                <?php endif; ?>
                <?php if ($this->_var['icon'] != "notice"): ?>
                <font style="clear: both; display:block; margin:0 0 0 50px;">
                <?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                      <!--  <a style="color:#aaa;" href="<?php echo $this->_var['item']['href']; ?>">>> <?php echo $this->_var['item']['text']; ?></a><br />  -->
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </font>
                <?php endif; ?>
            </p>
            <a href="javascript:history.go(-1);">返回上一页</a>
        </div>

        <div class="clear"></div>
        <div class="adorn_right1"></div>
        <div class="adorn_right2"></div>
        <div class="adorn_right3"></div>
        <div class="adorn_right4"></div>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
//<!CDATA[
<?php if ($this->_var['redirect']): ?>
window.setTimeout("<?php echo $this->_var['redirect']; ?>", 1000);
<?php endif; ?>
//]]>
</script>
<?php echo $this->fetch('footer.html'); ?>