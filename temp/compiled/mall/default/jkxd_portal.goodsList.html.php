<?php echo $this->fetch('header.html'); ?>


<?php echo $this->fetch('jkxd_portal.header.html'); ?>

<script type="text/javascript">
    $(function(){
        var category_id = '<?php echo $this->_var['category_id']; ?>';
        $("#parentCategory a").css({"background":"#fff","color":"#27a196","padding":"5px"});
        $("#parentCategory a span").css({"color":"#999999","font-family":"arial,sans-serif","font-size": "10px", "margin-left": "3px"});

        var node = "#"+category_id+" a"
        $(node).css({"background":"#27a196","color":"#fff"});
        $(node+" span").css({"color":"#fff"});
    });
    function findGoodsByCategory(id){
        $("#category_id").val(id);
        formSubmit("");
    }


    function order_start_time(){
        $("#order_by").val("start_time");
        chooseOrder();
        var category_id = $("#category_id").val()
        formSubmit();


    }

    function order_price(){
        $("#order_by").val("price");
        chooseOrder();
        var category_id = $("#category_id").val()
        formSubmit();

    }
    function order_addTime(){
        $("#order_by").val("add_time");
        chooseOrder();
        var category_id = $("#category_id").val()
        formSubmit();
    }
    function order_default(){
        $("#order_by").val("rg.id");
        chooseOrder();
        var category_id = $("#category_id").val()
        formSubmit();
    }
    function chooseOrder(){
        var desc_asc = $("#desc_asc").val();
        if(desc_asc =="desc"){
            $("#desc_asc").val("asc");
        }else{
            $("#desc_asc").val("desc");
        }
    }
    function formSubmit(url){
        if(url != null && url !="" && typeof url !="undefined"){
            $.get(
                    url,
                    function(data){
                        $("#goods_list").html(data);
                    }
            );
        }else{
            var id  = $("#id").val();
            var category_id = $("#category_id").val();
            var order_by  = $("#order_by").val();
            var desc_asc  = $("#desc_asc").val();
            var type =$("#type").val();
            window.location="index.php?app=jkxd_portal&act=goodsList&type="+type+"&id="+id+"&category_id="+category_id+"&order_by="+order_by+"&desc_asc="+desc_asc;

        }

    }
</script>


<div id="center_w1200">
        <input type="hidden" id="type" value="<?php echo $this->_var['type']; ?>"/>
        <div id="shop_list">
            <form action="" method="post" id="goodsForm">
                <input type="hidden" id="id" name="id" value="<?php echo $this->_var['shopOwner']['user_id']; ?>"/>
                
                <div class="fov1">
                    <div class="txt1">分类:</div>
                    
                    <ul class="inline" id="parentCategory">
                        <li class="item"><a href="index.php?app=jkxd_portal&act=goodsList&id=<?php echo $this->_var['shopOwner']['user_id']; ?>&type=<?php echo $this->_var['type']; ?>&category_id=-1">全部</a></li>
                        <?php $_from = $this->_var['goodsCategoryList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');if (count($_from)):
    foreach ($_from AS $this->_var['category']):
?>
                            <li class="item" >
                                <div id="<?php echo $this->_var['category']['cate_id']; ?>">
                                    <a href="javascript:findGoodsByCategory('<?php echo $this->_var['category']['cate_id']; ?>')"  >
                                        <?php echo myeval($this->_var['category']['cate_name'],"substr(value, strpos(value,'	'))"); ?><span>(<?php echo $this->_var['category']['c']; ?>)</span>
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </li>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </ul>
                    <input type="hidden" id="category_id" name="category_id" value="<?php echo $this->_var['category_id']; ?>"/>
                    <div class="clear"></div>
                </div>

                
                <div class="libiao">
                    <div class="div1">
                        <input type="hidden" id="order_by" name="order_by" <?php if ($this->_var['order_by']): ?> value="<?php echo $this->_var['order_by']; ?>" <?php else: ?> value="start_time"<?php endif; ?>/>
                        <input type="hidden" id="desc_asc" name="desc_asc" <?php if ($this->_var['desc_asc']): ?> value="<?php echo $this->_var['desc_asc']; ?>" <?php else: ?> value="desc"<?php endif; ?>/>
                        <a href="javascript:order_default()" class="c_eeeded">默认排序</a>
                        <a href="javascript:order_price()"><span></span>价格</a>
                        <a href="javascript:order_addTime()"><span></span>最新上架</a>
                    </div>
                </div>
            </form>
        </div>

    
    <div id="goods_list">
        <div class="xiaod">
            <div class="top">
                <?php if ($this->_var['type'] == 'search'): ?>
                    <div class="biti1"></div>
                <?php else: ?>
                    <div class="biti2"></div>
                <?php endif; ?>
                <div class="duo">
                    <a>共 <span> <?php echo $this->_var['page_info']['item_count']; ?></span> 件商品</a>
                </div>
            </div>
            <div class="bottom" style="margin-top:0px;">
                <ul>
                    <?php if ($this->_var['page_info'] && $this->_var['page_info']['goodsList'] && count ( $this->_var['page_info']['goodsList'] ) > 0): ?>
                        <?php $_from = $this->_var['page_info']['goodsList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_index'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_index']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_index']['iteration']++;
?>
                            <li <?php if ($this->_foreach['goods_index']['iteration'] % 5 == 0): ?> style="padding-right:0px;margin-right:0px;"<?php endif; ?>>
                                <div class="pic">
                                    <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" target="_blank">
                                        <img src="<?php $imgarr = explode('.',$this->_var['goods']['default_image']);
                            if(strpos($imgarr[0],'system') == false)
                            echo $this->_var['img_url'] .'/'.$imgarr[0] .'_' . '220X220' . '.' .$imgarr[1];
                            else echo $this->_var['img_url'].'/'.$imgarr[0]. '.' .$imgarr[1]; ?>" width="220" height="220" />
                                    </a>

                                </div>
                                <div class="text">
                                    <a href="index.php?app=goods&id=<?php echo $this->_var['goods']['goods_id']; ?>&shop_id=<?php echo $this->_var['shopOwner']['user_id']; ?>" target="_blank">
                                        <?php echo sub_str($this->_var['goods']['goods_name'],60); ?>
                                    </a>
                                </div>
                                <div class="jiage">￥<span><?php echo $this->_var['goods']['price']; ?></span>
                                    <?php if ($this->_var['user'] && $this->_var['shopOwner'] && $this->_var['user']['user_id'] == $this->_var['shopOwner']['user_id']): ?>
                                        <em class="em1">奖励：￥<?php echo number_format2($this->_var['goods']['yongjin']); ?></em>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <?php else: ?>
                        <li>
                            没有相关数据!
                        </li>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="clear"></div>
        
        <?php echo $this->fetch('page.bottom.html'); ?>

        
    </div>


</div>



<?php echo $this->fetch('footer.html'); ?>

</body>
</html>
