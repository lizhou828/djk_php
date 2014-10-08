
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $this->_var['shopOwner']['shop_name']; ?>的集客小店</title>
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
        function hot(){
            window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&view=category';
        }
        function djk(){
            window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=djk&view=category';
        }
        function hgg(){
            window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=hgg&view=category';
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

    </script>
</head>
<body>

<div style=" width:320px; margin:0px auto;padding-top: 50px;">
    <div class="enter-t">
        <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal"><div class="enter-t1">首页</div></a>
        <div class="enter-t22"><?php echo $this->_var['shopOwner']['shop_name']; ?>的集客小店</div>
        <a href="javascript:void(0)" onclick="apply_jkxd()"><div class="enter-t33">我也要开小店</div></a>
    </div>
    <div class="clear"></div>
    <div class="enter-bg" <?php if ($this->_var['user'] && $this->_var['user']['user_id'] && $this->_var['user']['user_id'] > 0): ?>style="padding-bottom:55px;"<?php else: ?>style="padding-bottom:0px;"<?php endif; ?>>
        <div>
            <form action="<?php echo url('app=jkxd_portal&act=goodsList'); ?>" method="get" id="search_in_shop">
                <input type="hidden"  name="app" value="jkxd_portal" />
                <input type="hidden"  name="act" value="goodsList" />
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
                            <?php echo $this->_var['shopOwner']['shop_name']; ?>
                        </h3>

                    <p style="color:#666;"><?php echo $this->_var['jkxd_site_url']; ?></p>
                </dd>
            </dl>
            <div>
                <em style="position: absolute;right: 5px;top:15px;" ><img id="switch_img" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/images/switch.png" alt="集客小店与精品集客小切换"/></em>
            </div>
        </div>




        
        <div class="hot">
            <?php if ($this->_var['page_info'] && $this->_var['page_info']['goodsList'] > 0 && count ( $this->_var['page_info']['goodsList'] ) > 0): ?>
                <div onclick="hot()" style="cursor:pointer" class="hot-t"><span>共<strong> <?php echo $this->_var['page_info']['item_count']; ?></strong>件商品  ></span>小店热销</div>
                <div class="hot-c">
                    <?php $_from = $this->_var['page_info']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['user'] && $this->_var['user']['user_id'] > 0): ?>/<?php echo $this->_var['user']['user_id']; ?><?php endif; ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>">
                            <dl style="margin-bottom: 20px;">
                                <dt><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></dt>
                                <dd style="height: 32px;overflow: hidden" ><?php echo sub_str($this->_var['goods']['goods_name'],28); ?></dd>
                                <p>￥<?php echo $this->_var['goods']['price']; ?></p>
                                <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwnerId']): ?>
                                    <p style="font-size: 12px;color: #999999;margin-top: 0px;">奖励￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></p>
                                <?php endif; ?>
                            </dl>
                        </a>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <div class="clear"></div>
                </div>
            <?php else: ?>
                <div onclick="hot()" style="cursor:pointer" class="hot-t"><span>共<strong><?php echo $this->_var['recommendHotGoodsCount']; ?></strong>件商品  ></span>小店热销</div>
                <div class="hot-c">
                    <?php $_from = $this->_var['recommendHotGoodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['user'] && $this->_var['user']['user_id'] > 0): ?>/<?php echo $this->_var['user']['user_id']; ?><?php endif; ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" >
                            <dl style="margin-bottom: 20px;">
                                <dt><img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="100" height="100" /></dt>
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
            <?php endif; ?>
        </div>
        


        
        <div class="hot">
            <div onclick="djk()" style="cursor:pointer" class="hot-t"><span>共<strong><?php echo $this->_var['recommendGoodsCount']; ?></strong>件商品  ></span>大集客商城</div>
            <div class="hot-c">
                <?php if ($this->_var['recommendGoodsList']): ?>
                    <?php $_from = $this->_var['recommendGoodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'recommendGoods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['recommendGoods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['recommendGoods']['goods_id']; ?><?php if ($this->_var['user'] && $this->_var['user']['user_id'] > 0): ?>/<?php echo $this->_var['user']['user_id']; ?><?php endif; ?>" title="<?php echo $this->_var['recommendGoods']['goods_name']; ?>">
                        <dl style="margin-bottom: 20px;">
                            <dt>
                                <img src="<?php $imgarr = explode('.',$this->_var['recommendGoods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?> " width="100" height="100" />
                            </dt>
                            <dd style="height: 32px;overflow: hidden"><?php echo sub_str($this->_var['recommendGoods']['goods_name'],20); ?></dd>
                            <p>￥<?php echo $this->_var['recommendGoods']['price']; ?></p>
                            <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwnerId']): ?>
                                 <p style="font-size: 12px;color: #999999;margin-top: 0px;">奖励￥<?php echo number_format2($this->_var['recommendGoods']['yongjin']); ?></p>
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
        


        
        <div class="hot">
            <div onclick="hgg()" style="cursor:pointer" class="hot-t"><span>共<strong><?php echo $this->_var['recommendHggGoodsCount']; ?></strong>件商品  ></span>韩国直购</div>
            <div class="hot-c">
                <?php if ($this->_var['recommendHggGoodsList']): ?>
                    <?php $_from = $this->_var['recommendHggGoodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'recommendGoods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['recommendGoods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                        <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['recommendGoods']['goods_id']; ?><?php if ($this->_var['user'] && $this->_var['user']['user_id'] > 0): ?>/<?php echo $this->_var['user']['user_id']; ?><?php endif; ?>" title="<?php echo $this->_var['recommendGoods']['goods_name']; ?>" >
                        <dl style="margin-bottom: 20px;">
                            <dt>
                                <img src="<?php $imgarr = explode('.',$this->_var['recommendGoods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?> "  width="100" height="100" />
                            </dt>
                            <dd style="height: 32px;overflow: hidden"><?php echo sub_str($this->_var['recommendGoods']['goods_name'],28); ?></dd>
                            <p>￥<?php echo $this->_var['recommendGoods']['price']; ?></p>
                            <?php if ($this->_var['user']['user_id'] == $this->_var['shopOwnerId']): ?>
                                <p style="font-size: 12px;color: #999999;margin-top: 0px;">奖励￥<?php echo number_format2($this->_var['recommendGoods']['yongjin']); ?></p>
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
