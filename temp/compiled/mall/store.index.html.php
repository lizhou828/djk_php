<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $this->_var['store']['store_name']; ?>的店铺</title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/com.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/hone.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/public.css" rel="stylesheet" type="text/css" />
    <meta id="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1,user-scalable=no" name="viewport">
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>
    <script type="text/javascript">
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
    </script>
</head>
<body>

<div style=" width:320px; margin:0px auto;padding-top: 50px;">
    <div class="enter-t">
        <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>"><div class="enter-t1">首页</div></a>
        <div class="enter-t22"><?php echo sub_str($this->_var['store']['store_name'],16); ?></div>

            <div class="enter-t33">
                <?php if (! $this->_var['member'] || $this->_var['member']['user_id'] <= 0): ?>
                    <a onclick="window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=member&act=login&ret_url='+encodeURIComponent('/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>')"
                       href="javascript:void(0)" style="color:#ffffff">
                        登&nbsp;录
                    </a>
                <?php else: ?>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=goods&act=index1" style="color:#ffffff">移动商城</a>
                <?php endif; ?>
            </div>
            </div>
    <div class="clear"></div>
    <div class="enter-bg" <?php if ($this->_var['user'] && $this->_var['user']['user_id'] && $this->_var['user']['user_id'] > 0): ?>style="padding-bottom:55px;"<?php else: ?>style="padding-bottom:0px;"<?php endif; ?>>
    <div>
        <form action="<?php echo url('app=store&act=search'); ?>" method="get" id="search_in_shop">
            <input type="hidden"  name="app" value="store" />
            <input type="hidden"  name="act" value="search" />
            <input type="hidden" name="id" id="id" value="<?php echo $this->_var['store']['store_id']; ?>"/>
            <div class="sou-text">
                <input  style="height: 26px;border-style:none;width:200px;" id="keyword" name="keyword" type="text" value="<?php if ($this->_var['keyword'] == null): ?>搜索商品关键字<?php else: ?><?php echo $this->_var['keyword']; ?><?php endif; ?>"
                        onfocus="if(this.value=='搜索商品关键字'){this.value='';this.style.color='#333'}"
                        onblur="if(this.value==''){this.value='搜索商品关键字';this.style.color='#999'}"/>
                <input type="button" style="float: right; font-weight: bold;cursor:pointer;background: none repeat scroll 0 0 #D0281E;height: 28px; border: medium none; color: #FFFFFF;"
                       value="搜索" onclick="search_in_shop()"/>

            </div>
        </form>
    </div>

    <div>
        <div class="my-more" style="height: 100px;" onclick="javascript:window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>'">
            <dl>
                <dt style="height: 100px;">
                    <img width="73" height="65" style="margin-top: 16px;" alt="头像" src="http://www.dajike.com/weixin/templates/style/img/logo2.jpg"></dt>
                <dd>
                    <h3><?php echo $this->_var['store']['store_name']; ?>的店铺</h3>
                    <div style="color:#666;margin-top: 15px;font-size: 14px"><?php echo $this->_var['store']['address']; ?></div>
                </dd>
            </dl>
        </div>
    </div>

    
    <div class="hot">
        <div onclick="" style="cursor:pointer" class="hot-t">
            <span>
                共<strong><?php if ($this->_var['page'] && count ( $this->_var['page']['goodsList'] ) > 0): ?><?php echo $this->_var['page']['item_count']; ?><?php else: ?>0<?php endif; ?></strong>件商品
            </span>商品列表
        </div>
        <div class="hot-c">
            <?php if ($this->_var['page'] && count ( $this->_var['page']['goodsList'] ) > 0): ?>
                <?php $_from = $this->_var['page']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                    <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/0/<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['user'] && $this->_var['user']['user_id'] > 0): ?>/<?php echo $this->_var['user']['user_id']; ?><?php endif; ?>" title="<?php echo $this->_var['goods']['goods_name']; ?>" >
                        <dl style="margin-bottom: 20px;">
                            <dt>
                                <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '100X100' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?> "  width="100" height="100" />
                            </dt>
                            <dd style="height: 32px;overflow: hidden"><?php echo sub_str($this->_var['goods']['goods_name'],28); ?></dd>
                            <p>￥<?php echo $this->_var['goods']['price']; ?></p>
                        </dl>
                    </a>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php else: ?>
                <div style="text-align: center;padding-bottom: 80px;padding-top: 80px;font-size: 16px;">
                    该商家没有上传商品！
                </div>
            <?php endif; ?>
            <div class="clear"></div>
        </div>
    </div>
    

    <?php if (! $this->_var['page'] && count ( $this->_var['page']['goodsList'] ) <= 0): ?>
        
        <script type="text/javascript">
            function formerPage(curr_page,page_count){
                //                alert(curr_page-1);
                var page = parseInt(curr_page)-1
                if( page> 0 ){
                    window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>&page="+page;
                }else{
                    alert("已经到了首页！");
                    return;
                }
            }
            function nextPage(curr_page,page_count){
                var page = parseInt(curr_page)+1;
                if(page > page_count){
                    alert("已经到了最后一页！");
                }else{
                    window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>&page="+page;
                }
            }
        </script>
        <div id="w_3202" style="padding-bottom: 5px;padding-top: 5px;">
            <div class="ddi">
                <div class="left" style="width: 235px; padding-left: 75px;">
                    <a href="javascript:formerPage('<?php echo $this->_var['page']['curr_page']; ?>','<?php echo $this->_var['page']['page_count']; ?>')" class="a1" style="margin-right: 20px;"></a>
                    <a style="float: left; height: 26px; line-height: 26px; font-family: tahoma; font-size: 14px; margin-right: 20px;"><?php echo $this->_var['page']['curr_page']; ?>/<?php echo $this->_var['page']['page_count']; ?></a>
                    <a href="javascript:nextPage('<?php echo $this->_var['page']['curr_page']; ?>','<?php echo $this->_var['page']['page_count']; ?>')" class="a2"></a>
                </div>
            </div>
        </div>
        
    <?php endif; ?>



</div>


<div class="pdding"></div>
<div style="margin-top:50px;">
    <div class="mmore22" style="padding-top:4px;font-weight:bolder;height:30px;z-index:999;margin:0px auto;position:fixed !important;bottom:0 !important;cursor: pointer;width: 320px;"
         onclick="javascript:window.location='<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=store&act=setOrderPage&id=<?php echo $this->_var['store']['store_id']; ?>'">
        <没有找到想要的商品？自己下单></没有找到想要的商品？自己下单>
    </div>
</div>
</div>


<span style="display: none">
    <script type="text/javascript">
        var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000080062'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s22.cnzz.com/z_stat.php%3Fid%3D1000080062' type='text/javascript'%3E%3C/script%3E"));
    </script>
</span>

</body>
</html>
