<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>大集客-移动商城-集客小店</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/public.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>
</head>

<body>
<div id="w_3202" style="height: 600px;">
    <div class="top">商品类别列表<em class="em1"><a href="javascript:history.go(-1)">返回</a></em></div>

    <div class="list_list">
        <ul class="uls">
            <?php $_from = $this->_var['goodsCategoryList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcate');$this->_foreach['gcate'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['gcate']['total'] > 0):
    foreach ($_from AS $this->_var['gcate']):
        $this->_foreach['gcate']['iteration']++;
?>
            <li>
                <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['gcate']['cate_id']; ?>">
                    <strong><?php echo sub_str($this->_var['gcate']['cate_name'],26); ?><i>（<?php echo $this->_var['gcate']['c']; ?>）</i></strong>
                    <span>></span>
                </a>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <?php echo $this->fetch('jkxd_portal.footer.html'); ?>
</div>
</body>
</html>
