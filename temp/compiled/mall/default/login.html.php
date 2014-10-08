<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>大集客-登录</title>
<?php echo $this->_var['page_seo']; ?>
<link href="<?php echo $this->res_base . "/" . 'css/public.css'; ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->res_base . "/" . 'css/login.css'; ?>" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/jquery.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/common.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/poshytip.js'; ?>" charset="utf-8"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/jquery.ui.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/themes/ui-lightness/jquery.ui.css"  />

<script type="text/javascript">
    //注册表单提示
    $('.text').poshytip({
        className: 'tip-yellowsimple',
        showOn: 'focus',
        alignTo: 'target',
        alignX: 'center',
        alignY: 'top',
        offsetX: 0,
        offsetY: 5,
        allowTipHover: false
    });
    $(document).ready(function(){
        $('#login_form').validate({
            errorPlacement: function(error, element){
                var error_td = element.parent('div');
                error_td.find('label').hide();
                error_td.append(error);
            },
            success       : function(label){
                label.remove();
            },
            onkeyup : false,
            rules : {
                user_name : {
                    required : true,
                    userNameValidate : [],
                    remote   : {
                        url :'index.php?app=member&act=check_user_or_phone_mob&type=2&ajax=1',
                        type:'get',
                        data:{
                            user_name : function(){
                                return $('#user_name').val();
                            }
                        },
                        beforeSend : function(){
                            var _checking = $('#checking_user');
                            _checking.prev('.field_notice').hide();
                            _checking.next('label').hide();
                            $(_checking).show();
                        },
                        complete :function(){
                            $('#checking_user').hide();
                        }
                    }
                },
                password : {
                    required : true,
                    cookieValidate : [],
                    remote   : {
                        url :'index.php?app=member&act=check_pwd&ajax=1',
                        type:'post',
                        data:{
                            user_name : function(){
                                return $('#user_name').val();
                            },
                            password : function(){
                                return $('#password').val();
                            }
                        },
                        beforeSend : function(){
                            var _checking = $('#checking_user');
                            _checking.prev('.field_notice').hide();
                            _checking.next('label').hide();
                            $(_checking).show();
                        },
                        complete :function(){
                            $('#checking_user').hide();
                        }
                    }
                },
                captcha : {
                    required : true,
                    remote   : {
                        url : 'index.php?app=captcha&act=check_captcha',
                        type: 'get',
                        data:{
                            captcha : function(){
                                return $('#captcha1').val();
                            }
                        }
                    }
                }
            },
            messages : {
                user_name : {
                    required : '帐号不能为空',
                    equalTo  : '帐号不能为空',
                    remote    : '用户名/邮箱/手机号/集客小店号不存在'
                },
                password  : {
                    required : '密码不能为空',
                    remote    : '用户名或密码错误'
                },
                captcha : {
                    required : '请输入右侧图片中的文字',
                    remote   : '验证码错误'
                }
            }
        });
    });

    jQuery.validator.addMethod("userNameValidate", function(value, element) {
        if($.trim(value) == '' || value == '用户名/邮箱/手机号/集客小店号'){
            return false;
        }
        return true;
    }, "请输入用户名");

    jQuery.validator.addMethod("cookieValidate", function(value, element) {
        if(!(document.cookie || navigator.cookieEnabled)){
            return false;
        }
        return true;
    }, "无法登入,cookie已被禁用！<a href='http://www.dajike.com/index.php?app=article&act=view&article_id=49' target='_blank'>如何开启？</a>");


    function showFocus(obj)
    {
        if(obj.value=="用户名/邮箱/手机号/集客小店号")
        {
            obj.style.color='#000';
            obj.value="";
        }
    }

    function showBlur(obj)
    {
        if(obj.value=="")
        {
            obj.style.color = '#ccc';
            obj.value="用户名/邮箱/手机号/集客小店号";
        }
    }



</script>

</head>

<body style="background:#f2f2f2;">
<div id="login_1000">
   <a href="<?php echo $this->_var['site_url']; ?>" title="进入首页" style="cursor: pointer"><div class="logo1"></div></a>
   <div class="logo2">还没有账号立即去 <a href="index.php?app=member&act=register">注册</a></div>
   <div class="logo3">
      <div class="left_pic"><img src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/member/login_pic.png" width="474" height="305" /></div>
      <div class="right_txt">
      
      	<?php if ($this->_var['postData']['errorMsg'] == 'auth_failed_byStatus'): ?>
        <center><font color="red">用户名或密码错误！</font><br><br></center>
        <?php endif; ?>
        
         <form name="login_form" id="login_form" method="post">
         <div class="go1" style="margin-top:25px;">
            <span>邮箱/手机/用户名/集客小店号</span>
            <div class="item_ifo">
            
              <input name="user_name" id="user_name" type="text" class="text_in" autocomplete="off" onblur="showBlur(this)"
                               onfocus="showFocus(this)" <?php if (! $this->_var['login_name']): ?> style="color: #ccc"<?php endif; ?> value="<?php if ($this->_var['login_name']): ?><?php echo $this->_var['login_name']; ?><?php else: ?>用户名/邮箱/手机号/集客小店号<?php endif; ?>"  />
              <div class="ico1"></div>
              <label>您输入的账户名不存在，请核对后重新输入</label>
            </div>
         </div>
         <div class="go1">
            <span>密码</span>
            <div class="item_ifo">
              <input name="password" id="password" type="password" class="text_in" />
              <div class="ico2"></div>
              <label>您输入的账户名不存在，请核对后重新输入</label>
            </div>
         </div>
         <div class="go2">
           <div class="wangji"><a href="<?php echo url('app=find_password'); ?>">忘记密码？</a></div>
           <input name="sava_user" autocomplete="off" type="checkbox" checked="check"  id="sava_user" />
           <label>记住用户名</label>
         </div>
         <div class="go3"><input name="" type="submit" value="登 录" class="anniu" /></div>
         </form>		
         
         
         <div class="go4"><label style="line-height: 30px;">使用其他登录方式：&nbsp;&nbsp;</label><span><a href="javascript:location='<?php echo $this->_var['site_url']; ?>/Connect2.0/example/oauth/index.php'"><img src="<?php echo $this->res_base . "/" . 'images/register.jpg'; ?>" alt=""></a></span></div>
      </div>
   </div>
</div>
<div id="login_1000di">上海酷鸟网络科技有限公司 版权所有 沪ICP备13035566号 </div>
</body>
</html>
