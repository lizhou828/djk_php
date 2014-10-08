
    
    <div class="login" style="margin: 0px auto;">
        <div class="logo">
            <?php if ($_GET['app'] == 'hgg'): ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/hg_logo.png'; ?>" width="269" height="47" /></a>
            <?php elseif ($_GET['app'] == 'bdsh' || $_GET['act'] == 'store'): ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/bdsh.png'; ?>" width="320" height="55" ></a>
            <?php elseif ($_GET['app'] == 'tese'): ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/tese.png'; ?>" width="320" height="55" ></a>
            <?php elseif ($_GET['app'] == 'gczg'): ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/gczg.png'; ?>" width="320" height="55" ></a>
            <?php elseif ($_GET['app'] == 'market'): ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/chaoshi.png'; ?>" width="289" height="55" ></a>
            <?php elseif ($_GET['app'] == 'lydj'): ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/lydj.png'; ?>" width="289" height="55" ></a>
            <?php else: ?>
            <a href="/"><img src="<?php echo $this->res_base . "/" . 'images/logo.png'; ?>" width="269" height="47" /></a>
            <?php endif; ?>
        </div>
        <?php if ($_GET['app'] == 'bdsh' || $_GET['act'] == 'store'): ?>
        <div class="sh">
            <h2><a href="<?php if ($this->_var['filters']['bdsh_area']): ?><?php echo url('app=bdsh&pd_id=5&bdsh_reg_id=' . $this->_var['filters']['bdsh_reg_id']. ''); ?><?php else: ?><?php echo url('app=bdsh&pd_id=5&bdsh_reg_id=321'); ?><?php endif; ?>" style="color: #333333" id="chengshi">&nbsp;<?php if ($this->_var['filters']['bdsh_area']): ?> <?php echo $this->_var['filters']['bdsh_area']; ?><?php else: ?>上海<?php endif; ?></a></h2>
            <span><a href="<?php echo url('app=bdsh&act=change_city'); ?>">[切换城市]</a></span>
        </div>
        <?php endif; ?>
        <div class="search" id="search">

            <div class="k1">
                <form action=""  method="GET" id="searchForm">
                    <input type="hidden" name="act" id="act" value="index"/>
                    <input type="hidden" name="app" id="app" value="search"/>
                    <input type="hidden" id="search_type" name="search_type" value="">
                <span class="sp1"><input type="text"  class="text" lang="zh-CN"  value="<?php echo $this->_var['ky']['0']; ?>" onblur="searchBlur(this)" onfocus="searchFocus(this)" maxlength="60" x-webkit-speech="" onwebkitspeechchange="foo()" x-webkit-grammar="builtin:search" id="keyword" name="keyword" style="color: rgb(153, 153, 153);" /></span>
                <span class="sp2"><input type="submit" value="找商品" onclick="searchInput();" class="buttom"  <?php if ($this->_var['kw']): ?> value="<?php echo $this->_var['ky']; ?>"<?php endif; ?>/></span>
                <!--<span class="sp3"><input type="submit" value="找商家" onclick="searchInput('store');" class="buttom" /></span>-->
                </form>
            </div>
            <div class="resou">热搜：
                <?php $_from = $this->_var['hot_keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'keyword');$this->_foreach['my_kw'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['my_kw']['total'] > 0):
    foreach ($_from AS $this->_var['keyword']):
        $this->_foreach['my_kw']['iteration']++;
?>
                <a href="<?php echo url('app=search&keyword=' . $this->_var['keyword']. ''); ?>" <?php if (($this->_foreach['my_kw']['iteration'] - 1) == 0): ?> class="cd0281e" <?php elseif (($this->_foreach['my_kw']['iteration'] - 1) == 1): ?>class="c1d5b9f"<?php elseif (($this->_foreach['my_kw']['iteration'] - 1) == 2): ?>class="ceaac44"<?php elseif (($this->_foreach['my_kw']['iteration'] - 1) == 3): ?>class="c92bd49"<?php else: ?>class="c27a196"<?php endif; ?>><?php echo $this->_var['keyword']; ?></a>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        </div>
        <div class="car" style="float: left;"><a href="<?php echo url('app=cart'); ?>"></a><span class="span01"><span><?php echo $this->_var['cart_goods_kinds']; ?></span></span></div>
        <div style=" width:153px; height:38px; background:url('<?php echo $this->res_base . "/" . 'images/pic/kf.jpg'; ?>') no-repeat; float:right; " ></div>

    </div>

