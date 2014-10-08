
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $this->_var['shopOwner']['shop_name']; ?>的精品集客小店</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/com.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/hone.css" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>

    <script type="text/javascript">
        function apply_jkxd(){
            $.get('<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=apply_jkxd',function(data){
                data = eval("("+data+")");
                if(data.retval == 0){
                    alert(data.msg);
                }else if(data.retval==1){
                    window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=apply_jkxd_page';
                }
            })
        }

        function search_in_shop(){
            var keywords =  $("#keyword").val();
            var re = /^\\S+$/;
            if(keywords==null || keywords =="" || keywords=="搜索商品关键字"){
                alert("请填写商品名称或关键字!");
                return;
            }else if(re.test(keywords)){
                alert("搜索的关键字不能为空!");
                return;
            }else{
                var keyword=$("#keyword").val();

                $("#search_in_shop").submit();
            }
        }
        function shop_info(){
            window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=shop_info&id=<?php echo $this->_var['shopOwner']['user_id']; ?>';
        }
        function all_category(_type){
            window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=vshop_category&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type='+_type;
        }

    </script>

</head>
<body>

<div style=" width:320px; margin:0px auto;padding-top: 50px;">
    <div class="enter-t">
        <div class="enter-t1">
            <a href="<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=jkxd_portal">
                <div style="color: white">首页</div>
            </a>
        </div>
        <div class="enter-t22">精品集客小店</div>
        <a href="javascript:void(0)" onclick="apply_jkxd()"><div class="enter-t33">我也要开小店</div></a>
    </div>
    <div class="clear"></div>
    <div class="enter-bg" <?php if ($this->_var['user'] && $this->_var['user']['user_id'] && $this->_var['user']['user_id'] > 0): ?>style="padding-bottom:55px;"<?php else: ?>style="padding-bottom:0px;"<?php endif; ?>>
        <div>
            <form action="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=vshop_list" method="get" id="search_in_shop">
                <input type="hidden"  name="app" value="jkxd_portal" />
                <input type="hidden"  name="act" value="vshop_list" />
                <input type="hidden"  name="type" value="search" />
                <input type="hidden" name="id" id="id" value="<?php echo $this->_var['shopOwner']['user_id']; ?>"/>
                <div class="sou-text">
                    <input  style="height: 26px;border-style:none;width:200px;" id="keyword" name="keyword" type="text" value="搜索商品关键字"
                            onfocus="if(this.value=='搜索商品关键字'){this.value='';this.style.color='#333'}"
                            onblur="if(this.value==''){this.value='搜索商品关键字';this.style.color='#999'}"/>
                    <input type="button" style="float: right; font-weight: bold;cursor:pointer;background: none repeat scroll 0 0 #D0281E;height: 28px; border: medium none; color: #FFFFFF;"
                           value="搜索" onclick="search_in_shop()"/>

                </div>
            </form>
        </div>
        <div class="my-more" style="cursor: pointer;position: relative" onclick="ShowDIV('DialogDiv','<?php echo $this->_var['shopOwner']['user_id']; ?>','')">
            <dl>
                    <dt><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/img/logo2.jpg" width="73" height="65" alt="头像" /></dt>
                    <dd>
                        <h3>
                            <?php echo $this->_var['shopOwner']['shop_name']; ?><img src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/v2.png" alt="已认证"/>
                        </h3>
                        <p style="color:#666;"><?php echo $this->_var['jkxd_site_url']; ?></p>
                    </dd>
                </dl>
            </dl>
            <div>
                <em style="position: absolute;right: 5px;top:15px;" ><img id="switch_img" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/switch.png" alt="集客小店与精品集客小切换"/></em>
            </div>
        </div>

    
    <!--<div class="topCategory">-->
        <!--<a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=vshop_list&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&topCateId=1268&view=category"><div class="category">礼品、箱包、钟表、珠宝</div></a>-->
        <!--<a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=vshop_list&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&topCateId=1243&view=category"><div class="category">服饰鞋帽</div></a>-->
        <!--<a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=vshop_list&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&topCateId=1258&view=category"><div class="category">运动、健康</div></a>-->
    <!--</div>-->
    




    
    <?php if ($this->_var['bendi_temai'] && $this->_var['bendi_temai']['goodsList']): ?>
        <div class="hot">
            <div style="cursor:pointer" onclick="all_category('localHot')" class="hot-t"><span>共<strong><?php echo $this->_var['bendi_temai']['item_count']; ?></strong>件商品 > </span>本地特卖</div>
            <div class="hot-c">
                    <?php $_from = $this->_var['bendi_temai']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>?vshop=1" title="<?php echo $this->_var['goods']['goods_name']; ?>" >
                            <dl style="margin-bottom: 20px;position: relative">
                                <dt>
                                    <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?> "  width="100" height="100" />
                                </dt>
                                <em style="position: absolute;left: 5px;top:5px;"><img src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/temai.png" /></em>
                                <dd style="height: 32px;overflow: hidden"><?php echo sub_str($this->_var['goods']['goods_name'],28); ?></dd>
                                <p>￥<?php echo $this->_var['goods']['price']; ?></p>
                                <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwnerId']): ?>
                                <p style="font-size: 12px;color: #999999;margin-top: 0px;">奖励￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></p>
                                <?php endif; ?>
                            </dl>
                        </a>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <div class="clear"></div>
            </div>
        </div>
    <?php endif; ?>
    




    
    <div class="hot">
        <div style="cursor:pointer" onclick="all_category()" class="hot-t"><span>共<strong><?php echo $this->_var['page']['item_count']; ?></strong>件商品 > </span>大集客精品</div>
        <div class="hot-c">
            <?php if ($this->_var['page'] && $this->_var['page']['goodsList']): ?>
                <?php $_from = $this->_var['page']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>?vshop=1" title="<?php echo $this->_var['goods']['goods_name']; ?>" >
                        <dl style="margin-bottom: 20px;">
                            <dt>
                                <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?> "  width="100" height="100" />
                            </dt>
                            <dd style="height: 32px;overflow: hidden"><?php echo sub_str($this->_var['goods']['goods_name'],28); ?></dd>
                            <p>￥<?php echo $this->_var['goods']['price']; ?></p>
                            <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwnerId']): ?>
                            <p style="font-size: 12px;color: #999999;margin-top: 0px;">奖励￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></p>
                            <?php endif; ?>
                        </dl>
                    </a>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php else: ?>
                没有商品！
            <?php endif; ?>
            <div class="clear"></div>
        </div>
    </div>
    
</div>


<div class="pdding"></div>

<?php if ($this->_var['user'] && $this->_var['user']['user_id'] && $this->_var['user']['user_id'] > 0): ?>
    <?php echo $this->fetch('jkxd_portal.footer.html'); ?>
<?php else: ?>
    <?php echo $this->fetch('jkxd_portal.jump.html'); ?>
<?php endif; ?>





<?php if ($this->_var['user'] && ( $this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id'] )): ?>

<?php if ($this->_var['user']['im_qq'] != null && $this->_var['user']['im_qq'] != '' && ( $this->_var['user']['password'] == '' || $this->_var['user']['password'] == null )): ?>
<script type="text/javascript">
    $(function(){
        qq_setPassword();
    });
</script>
<?php endif; ?>

<?php endif; ?>
</div>


<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.plugins/jquery.validate.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/dialog/dialog.js" id="dialog_js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/jquery.ui.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jquery.ui/i18n/zh-CN.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/includes/libraries/javascript/jike.js" ></script>
<script type="text/javascript">
    function qq_setPassword(){
        var uri =encodeURI("index.php?app=jkxd_portal&act=qq_setPasswordPage&id=<?php echo $this->_var['shopOwner']['user_id']; ?>");
        var id = 'qq_setPassword';
        var title = "设置集客小店登录密码";
        var width = '330';
        ajax_form(id, title, uri, width);
    }
</script>


</body>
</html>
