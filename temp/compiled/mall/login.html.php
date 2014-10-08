<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>登录,大集客-移动商城</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/public.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/show_share_button.js"></script>

    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>

    <script type="text/javascript">

        function showFocus(obj)
        {
            if(obj.value=="请输入账号")
            {
                obj.value="";
            }
        }

        function showBlur(obj)
        {
            if(obj.value=="")
            {
                obj.value="请输入账号";
            }
        }

        $(function(){
            $("#login").click(function(){
                var user_name = $("#username").val();
                var password = $("#password").val();
                var url1 = $("#url1").val();
                $.post("index.php?app=member&act=login",{user_name:user_name,password:password},function(data){
                    if(data.flag == 'error'){
                        $("#error").show();
                    }
                    if(data.flag == 'ok'){
                        window.location.href = url1;
                    }
                },'json')
            });
        })
    </script>

</head>

<body>
<div id="w_3202" style="min-height:500px;">
    <input type="hidden" value="<?php echo $this->_var['ret_url1']; ?>" id = "url1">
    <div class="top">
        登录
        <em class="em1"><a href="javascript:history.go(-1)">返回</a></em>
        <em class="em2">
            <?php if ($this->_var['ret_url1'] != null && $this->_var['ret_url1'] != '' && stristr ( $this->_var['ret_url1'] , "cart" ) != ""): ?>
                <a href="index.php?app=member&act=register_form&returnUrl=cart"/>注册</a>
            <?php else: ?>
                <a href="javascript:void(0)" onclick="window.location='index.php?app=member&act=register_form&ret_url='+encodeURIComponent('<?php echo $this->_var['ret_url1']; ?>')" >注册</a>
            <?php endif; ?>
        </em>
    </div>
    <div class="wbg">
        <div class="lil1">
            <span class="font_w">账号：</span>
            <span class="input_w"><input name="user_name" type="text" value="请输入账号" class="input_1" id = "username" onblur="showBlur(this)"
                                         onfocus="showFocus(this)" /></span>
        </div>
        <div class="lil1">
            <span class="font_w">密码：</span>
            <span class="input_w"><input name="password" type="password"  class="input_1" id = "password"/></span>
        </div>
        <div class="baoc" style="display:none;" id = "error">您输入的账号或者密码不正确</div>
        <div class="but" id = "login">登录</div>

        <div>

                <a href="index.php?app=jkxd_portal&act=qq_apply">
                    <img width="170" height="32" alt="qq申请" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/qqzh.jpg" complete="complete"/>
                </a>
            <p style="color: #000000">
                还未开通集客小店，<a href="index.php?app=jkxd_portal&act=apply_jkxd_page"><span style="color: red">点击申请</span></a>
            </p>

        </div>
    </div>
</div>
</body>
</html>

