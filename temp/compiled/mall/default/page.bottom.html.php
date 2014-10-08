<?php if ($this->_var['page_info']['page_count'] > 1): ?>
<div class="fenye1">
    <!--<a><?php echo sprintf('共 %s 个项目', $this->_var['page_info']['item_count']); ?></a>-->
    <?php if ($this->_var['page_info']['prev_link']): ?>
    <a class="a3" href="<?php echo $this->_var['page_info']['prev_link']; ?>">上一页<span></span></a>
    <?php else: ?>
    <a class="a1" style="color:#c9c9c9;">上一页<span></span></a>
    <?php endif; ?>
    <?php if ($this->_var['page_info']['first_link']): ?>
    <a href="<?php echo $this->_var['page_info']['first_link']; ?>">1&nbsp;<?php echo $this->_var['page_info']['first_suspen']; ?></a>
    <?php endif; ?>
    <?php $_from = $this->_var['page_info']['page_links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('page', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['page'] => $this->_var['link']):
?>
    <?php if ($this->_var['page_info']['curr_page'] == $this->_var['page']): ?>
    <a  class="dangqian" href="<?php echo $this->_var['link']; ?>"><?php echo $this->_var['page']; ?></a>
    <?php else: ?>
    <a  href="<?php echo $this->_var['link']; ?>"><?php echo $this->_var['page']; ?></a>
    <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    <?php if ($this->_var['page_info']['last_link']): ?>
    <a  href="<?php echo $this->_var['page_info']['last_link']; ?>"><?php echo $this->_var['page_info']['last_suspen']; ?>&nbsp;<?php echo $this->_var['page_info']['page_count']; ?></a>
    <?php endif; ?>
    <a class="nonce"><?php echo $this->_var['page_info']['curr_page']; ?>/<?php echo $this->_var['page_info']['page_count']; ?></a>
    <?php if ($this->_var['page_info']['next_link']): ?>
    <a class="a2" href="<?php echo $this->_var['page_info']['next_link']; ?>">下一页<span></span></a>
    <?php else: ?>
    <a class="a2" style="color:#c9c9c9;">下一页<span></span></a>
    <?php endif; ?>
</div>
<?php endif; ?>