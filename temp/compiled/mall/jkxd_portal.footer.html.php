<script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/show_share_button.js"></script>
<div class="mandi10">
    <dl>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/my_jkxd/<?php echo $this->_var['member']['user_id']; ?>" style="color: #666666;">
            <dt><img width="32" height="29" src="http://www.dajike.com/weixin/templates/images/ico25.png"></dt>
            <dd style="">我的小店</dd>
        </a>
    </dl>
    <dl>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=shouru" style="color: #666666;">
            <dt><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/tt2.png" width="28" height="27" /></dt>
            <dd>我的收入</dd>
        </a>
    </dl>
    <dl style=" position: relative;">
        <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=cart" style="color: #666666;">
            <em class="ggg"><?php echo $this->_var['cart_goods_kinds']; ?></em>
            <dt><img width="35" height="32" src="http://www.dajike.com/weixin/templates/images/ico27.png"></dt>
            <dd>&#12288;购物车</dd>
        </a>
    </dl>
    <dl>
        <?php if (! $this->_var['user'] || ! $this->_var['user']['user_id'] || $this->_var['user']['user_id'] <= 0): ?>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=fenxiang&id=<?php echo $this->_var['shopOwnerId']; ?>" style="color: #666666;">
        <?php else: ?>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=fenxiang&id=<?php echo $this->_var['user']['user_id']; ?>" style="color: #666666;">
        <?php endif; ?>
            <dt style="padding-left:6px;"><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/tt4.png" width="31" height="26" /></dt>
            <dd>一键分享</dd>
        </a>
    </dl>
    <dl>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=more&id=<?php echo $this->_var['user']['user_id']; ?>" style="color: #666666;">
            <dt style="padding-left:6px;"><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/tt5.png" width="25" height="36" /></dt>
            <dd>更多</dd>
        </a>
    </dl>
</div>
<span style="display: none">
<script type="text/javascript">
    var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000080062'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s22.cnzz.com/z_stat.php%3Fid%3D1000080062' type='text/javascript'%3E%3C/script%3E"));
</script>
</span>


<?php echo $this->fetch('jkxd_portal.jump.html'); ?>