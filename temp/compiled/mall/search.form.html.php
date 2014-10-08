<script type="text/javascript">
    function showFocus(obj)
    {
            obj.value="";
    }

    function showBlur(obj)
    {
        if(obj.value=="")
        {
            obj.value="输入商品关键字";
        }
    }

    function search1(){
        var keyWord = $("#keyWord").val();
        if(keyWord == "" || keyWord == "输入商品关键字"){
            alert("请输入商品关键字！");
        }else{
            location.href = "index.php?app=goods&act=goodsList&keyWord=" + keyWord;
        }
    }
</script>

<span class="k_search"><input name="" type="text"
                              <?php if ($this->_var['keyWord'] == ""): ?>value="输入商品关键字"<?php else: ?>value="<?php echo $this->_var['keyWord']; ?>"<?php endif; ?>
                              class="ipu" onblur="showBlur(this)" onfocus="showFocus(this)" id = "keyWord"/></span>
<span class="font1"><input name="" value="" type="button" class="ipu2" onclick="search1()"/></span>