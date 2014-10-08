<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        大集客-移动商城-集客小店
    </title>
    <link href="<?php echo $this->_var['site_url']; ?>/weixin/templates/style/public.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script type="text/javascript" src="<?php echo $this->_var['site_url']; ?>/weixin/templates/js/jquery.js" charset="utf-8"></script>

    <style type="text/css">
        .category{
            background: url('<?php echo $this->_var['site_url']; ?>/weixin/templates/images/address-bg.jpg') no-repeat scroll 0 0 rgba(0, 0, 0, 0);
            font-style: normal;
            height: 26px;
            left: 6px;
            line-height: 26px;
            position: absolute;
            text-align: center;
            top: 9px;
            width: 64px;
            margin-left: 240px;
        }
        .category a{
            color: white;
        }
    </style>
    <script type="text/javascript">
        var keyword ='<?php echo $this->_var['keyword']; ?>';
        var type ='<?php echo $this->_var['type']; ?>';
        function orderByPrice (){
            chooseOrder();
            var desc_asc = $("#desc_asc").val();
            if(type=='search' && keyword !=null && keyword !=''){
                window.location="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&keyword="+keyword+"&category_id=<?php echo $this->_var['cateId']; ?>&view=<?php echo $this->_var['view']; ?>&order_by=price&desc_asc="+desc_asc;
            }else{
                window.location="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=<?php echo $this->_var['view']; ?>&order_by=price&desc_asc="+desc_asc;
            }


        }
        function orderByAddTime(){
            chooseOrder();
            var desc_asc = $("#desc_asc").val();
            if(type=='search' && keyword !=null && keyword !=''){
                window.location="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&keyword="+keyword+"&category_id=<?php echo $this->_var['cateId']; ?>&view=<?php echo $this->_var['view']; ?>&order_by=add_time&desc_asc="+desc_asc;
            }else{
                window.location='index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=<?php echo $this->_var['view']; ?>&order_by=add_time&desc_asc='+desc_asc;
            }

        }
        function chooseOrder(){
            var desc_asc = $("#desc_asc").val();
            if(desc_asc =="desc"){
                $("#desc_asc").val("asc");
            }else{
                $("#desc_asc").val("desc");
            }
        }

    </script>
</head>

<body>
<div id="w_3202" style="min-height:500px;">
    <div class="top">
        商品列表
        <?php if ($this->_var['page']['goodsList'] && count ( $this->_var['page']['goodsList'] ) > 0): ?>
        <div class="category">
            <?php if ($this->_var['type'] == 'search' && $this->_var['keyword'] != null && $this->_var['keyword'] != ''): ?>
                <a href="<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&keyword=<?php echo $this->_var['keyword']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=category">类别列表</a>
            <?php else: ?>
                <a href="<?php echo $this->_var['SITE_URL']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=category">类别列表</a>
            <?php endif; ?>

        </div>
        <?php endif; ?>
        <em class="em1"><a href="javascript:history.go(-1)">返回</a></em>
    </div>
    <?php if ($this->_var['page']['goodsList'] && count ( $this->_var['page']['goodsList'] ) > 0): ?>
    <div class="shitu">
        <a href="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>" class="a1">
            列表视图
        </a>
        <a href="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=pic" class="a2">
            图片视图
        </a>
    </div>
    <?php endif; ?>
    <div class="libiao">
        <?php if ($this->_var['page']['goodsList'] && count ( $this->_var['page']['goodsList'] ) > 0): ?>
        <div class="title">
            <input type="hidden" name="desc_asc" id="desc_asc" <?php if (! $this->_var['desc_asc']): ?> value="desc"<?php else: ?>value="<?php echo $this->_var['desc_asc']; ?>"<?php endif; ?>/>
                <a href="javascript:void(0)" onclick="orderByAddTime()" <?php if ($this->_var['order_by'] == 'add_time'): ?> class="a1" <?php else: ?> class="a2"<?php endif; ?>>
                    最新上架
                </a>
                <a href="javascript:void(0)" onclick="orderByPrice()" <?php if ($this->_var['order_by'] == 'price'): ?> class="a1" <?php else: ?> class="a2"<?php endif; ?>>
                    价格
                </a>
        </div>
        <?php endif; ?>
        <div class="llsstt">
            <?php if ($this->_var['page'] && count ( $this->_var['page']['goodsList'] ) > 0): ?>
            <?php $_from = $this->_var['page']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gd');if (count($_from)):
    foreach ($_from AS $this->_var['gd']):
?>
            <a href="<?php echo $this->_var['site_url']; ?>/weixin/goods/<?php echo $this->_var['shopOwner']['user_id']; ?>/<?php echo $this->_var['gd']['goods_id']; ?><?php if ($this->_var['member'] && $this->_var['member']['user_id'] > 0): ?>/<?php echo $this->_var['member']['user_id']; ?><?php endif; ?>">
                <dl>
                    <dd><img src="<?php echo $this->_var['img_url']; ?>/<?php echo $this->_var['gd']['default_image']; ?>" width="100" height="100" /></dd>
                    <dt>
                    <div class="font"><?php echo $this->_var['gd']['goods_name']; ?></div>
                    <div class="jiage"><?php echo price_format($this->_var['gd']['price']); ?></div>
                    </dt>
                </dl>
            </a>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            
            <script type="text/javascript">
                function formerPage(curr_page,page_count){
                    //                alert(curr_page-1);
                    var page = parseInt(curr_page)-1
                    if( page> 0 ){
                        window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=<?php echo $this->_var['view']; ?>&order_by=<?php echo $this->_var['order_by']; ?>&desc_asc=<?php echo $this->_var['desc_asc']; ?>&page="+page;
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
                        window.location ="<?php echo $this->_var['site_url']; ?>/weixin/index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=<?php echo $this->_var['cateId']; ?>&view=<?php echo $this->_var['view']; ?>&order_by=<?php echo $this->_var['order_by']; ?>&desc_asc=<?php echo $this->_var['desc_asc']; ?>&page="+page;
                    }
                }
            </script>
            <div class="Safety-cen-1">
                <div class="ddi">
                    <div class="left" style="width: 235px; padding-left: 75px;">
                        <a href="javascript:formerPage('<?php echo $this->_var['page']['curr_page']; ?>','<?php echo $this->_var['page']['page_count']; ?>')" class="a1" style="margin-right: 20px;"></a>
                        <a style="float: left; height: 26px; line-height: 26px; font-family: tahoma; font-size: 14px; margin-right: 20px;"><?php echo $this->_var['page']['curr_page']; ?>/<?php echo $this->_var['page']['page_count']; ?></a>
                        <a href="javascript:nextPage('<?php echo $this->_var['page']['curr_page']; ?>','<?php echo $this->_var['page']['page_count']; ?>')" class="a2"></a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
                <div style="height: 200px;text-align: center;font-weight: bold;font-size: 14px;margin-top: 100px;">没有相关商品!</div>
            <?php endif; ?>
        </div>
    </div>



    <?php echo $this->fetch('jkxd_portal.footer.html'); ?>
</div>
</body>
</html>
