<html>
<head>
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
    <meta HTTP-EQUIV="expires" CONTENT="0">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7 charset=utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="<?php echo $this->res_base . "/" . 'css/public.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/index.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/djk.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/base.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/dialog.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/layout.css'; ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo $this->res_base . "/" . 'css/user.css'; ?>" rel="stylesheet" type="text/css">

    <script>
        function del(id) {
            if(confirm("你确定把当前商品从精品集客小店上删除?")){
                document.location.href = "/index.php?app=service&act=vshop_recomGoods&_m=del&id=" + id;
            }


        }
    </script>
</head>
<body>
<table class="table_head_line" style="width: 100%">
    <tbody>
    <tr class="gray">
        <th>商品图片</th>
        <th>商品名称</th>
        <th>审核状态</th>
        <th>操作</th>
    </tr>

    <?php $_from = $this->_var['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'rgoods');if (count($_from)):
    foreach ($_from AS $this->_var['rgoods']):
?>
    <tr class="line">
        <td><img src="<?php $imgarr = explode('.',$this->_var['rgoods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['IMG_URL'] .'/'.$imgarr[0] .'_' . '80X80' . '.' .$imgarr[1];
                            else echo $this->_var['IMG_URL'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="50" height="50"></td>
        <td><?php echo sub_str($this->_var['rgoods']['goods_name'],28); ?></td>
        <td>
            <?php if ($this->_var['rgoods']['status'] == 0): ?>
                待审核
            <?php elseif ($this->_var['rgoods']['status'] == 1): ?>
                通过
            <?php elseif ($this->_var['rgoods']['status'] == 2): ?>
                不通过(<?php echo $this->_var['rgoods']['unpass_reason']; ?>)
            <?php endif; ?>
        </td>
        <td>
            <a href="javascript:del(<?php echo $this->_var['rgoods']['id']; ?>);" class="delete">删除</a>
        </td>
    </tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </tbody>
</table>
</body>
</html>