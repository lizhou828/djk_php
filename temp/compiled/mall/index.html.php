<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>大集客-移动商城</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/public.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/show_share_button.js"></script>
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>

    <script type="text/javascript">
        $(function(){
            setTimeout(function(){aaa();},1000);
        })

        function aaa(){
            window.location.href="index.php?app=goods&act=index1"
        }
    </script>
</head>



<body>
<div id="bg">
    <div class="w320">
        <div class="logo"><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/logo01.png" width="106" height="102" /></div>
        <div class="font">mandi 大集客   汇集全球名品</div>
        <div class="font1">copyright2013@dajike.com</div>
    </div>
</div>
</body>
</html>

