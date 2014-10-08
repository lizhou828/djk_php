<div class="clear"></div>
<div class="h2_banner" style="width:1200px; margin:0px auto; margin-top:10px;"><?php $_from = $this->_var['shouye_footer']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'top');if (count($_from)):
    foreach ($_from AS $this->_var['top']):
?><a href="<?php echo $this->_var['top']['link']; ?>" target="_blank"><img src="<?php echo $this->_var['top']['img']; ?>" width="1200" height="60"></a><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
<div class="clear"></div>
<div id="faq">
    <ul class="s1">
        <h3>
            新手上路      </h3>
        <?php $_from = $this->_var['help']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'hl');if (count($_from)):
    foreach ($_from AS $this->_var['hl']):
?>
        <li><a href="<?php echo url('app=article&act=view&article_id=' . $this->_var['hl']['article_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['hl']['title']; ?>"> <?php echo $this->_var['hl']['title']; ?> </a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
    <ul class="s2">
        <h3>
            店主之家      </h3>
        <?php $_from = $this->_var['home']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'hm');if (count($_from)):
    foreach ($_from AS $this->_var['hm']):
?>
        <li><a href="<?php echo url('app=article&act=view&article_id=' . $this->_var['hm']['article_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['hm']['title']; ?>"> <?php echo $this->_var['hm']['title']; ?> </a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
    <ul class="s3">
        <h3>
            集客小店      </h3>
        <?php $_from = $this->_var['pay']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'pa');if (count($_from)):
    foreach ($_from AS $this->_var['pa']):
?>
        <li><a href="<?php echo url('app=article&act=view&article_id=' . $this->_var['pa']['article_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['pa']['title']; ?>"> <?php echo $this->_var['pa']['title']; ?> </a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
    <ul style="width:190px;">
        <li style="padding-top:35px; padding-left:15px;"><img src="<?php echo $this->res_base . "/" . 'images/logo_di.png'; ?>" width="83" height="75" /></li>
    </ul>
    <ul class="s4">
        <h3>
            售后服务      </h3>
        <?php $_from = $this->_var['service']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sv');if (count($_from)):
    foreach ($_from AS $this->_var['sv']):
?>
        <li><a href="<?php echo url('app=article&act=view&article_id=' . $this->_var['sv']['article_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['sv']['title']; ?>"> <?php echo $this->_var['sv']['title']; ?> </a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
    <ul class="s5">
        <h3>
            购物指南      </h3>
        <?php $_from = $this->_var['center']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ct');if (count($_from)):
    foreach ($_from AS $this->_var['ct']):
?>
        <li><a href="<?php echo url('app=article&act=view&article_id=' . $this->_var['ct']['article_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['ct']['title']; ?>"> <?php echo $this->_var['ct']['title']; ?> </a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
    <ul class="s6">
        <h3>
            关于我们      </h3>
        <?php $_from = $this->_var['about']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ab');if (count($_from)):
    foreach ($_from AS $this->_var['ab']):
?>
        <li><a href="<?php echo url('app=article&act=view&article_id=' . $this->_var['ab']['article_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['ab']['title']; ?>"> <?php echo $this->_var['ab']['title']; ?> </a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    </ul>
    <div class="clear"></div>
</div>
<div id="di"><a href="<?php echo url('app=article&act=view&article_id=28'); ?>" target="_blank">联系我们</a> |<a href="<?php echo url('app=article&act=view&article_id=39'); ?>" target="_blank">人才招聘</a> |<a href="<?php echo url('app=article&act=view&article_id=23'); ?>" target="_blank">商家开店</a> | <a href="<?php echo url('app=article&act=view&article_id=29'); ?>" target="_blank">在线支付</a>
    | 客服热线 <a href="<?php echo url('app=article&act=view&article_id=54'); ?>" target="_blank"> 400-728-1117</a>
    
    <div style='display:none;'><a href='http://www.live800.com'>在线客服</a></div><script language="javascript" src="http://chat10.live800.com/live800/chatClient/textButton.js?jid=6251139253&companyID=385228&configID=190636&codeType=custom"></script><script id='write' language="javascript">function writehtml(){var temptext=text_generate();document.getElementById('live190636').innerHTML=temptext;setTimeout('write.src',9000);}writehtml();</script><div style='display:none;'><a href='http://en.live800.com'>live chat</a></div>
    
    <br /> 上海酷鸟网络科技有限公司  版权所有
    <a href="http://www.miitbeian.gov.cn/" target="_blank">沪ICP备13035566号</a>
    上海市公安局宝山分局备案编号：<a href="http://www.zx110.org" target="_blank">3101130300284792-1</a><br/>
    <img src="<?php echo $this->res_base . "/" . 'images/yewei_zhifubao.jpg'; ?>"/>
        <a href="http://www.sgs.gov.cn/lz/licenseLink.do?method=licenceView&entyId=20131119172754953" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/yewei_gongshang.jpg'; ?>"/></a>
<a href="http://www.police.sh.cn/shga/gweb/email/110/" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/yewei_110.jpg'; ?>"/></a>
<a href="http://www.zx110.org/" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/yewei_zhengxin.jpg'; ?>"/></a> 
<a href="https://ss.knet.cn/verifyseal.dll?sn=e13112211010043577zqnz000000&amp;ct=df&amp;a=1&amp;pa=0.557971702672331" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/yewei_kexinren.jpg'; ?>"/></a>
<a href="https://online.unionpay.com/static/page/530.html" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/yinlian.jpg'; ?>"/></a>

<span style="display:none">
  <script src="http://kxlogo.knet.cn/seallogo.dll?sn=e13112211010043577zqnz000000&size=3"></script>
</span>

</div>
<span style="display: none">
<script type="text/javascript">
  var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000080062'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s22.cnzz.com/z_stat.php%3Fid%3D1000080062' type='text/javascript'%3E%3C/script%3E"));
</script>
</span>

<style>
    #bottom111 {
        -moz-border-bottom-colors: none;
        -moz-border-left-colors: none;
        -moz-border-right-colors: none;
        -moz-border-top-colors: none;
        background: none;
        border-color: -moz-use-text-color #ECECEC;
        border-image: none;
        border-left: 1px solid #ECECEC;
        border-right: 1px solid #ECECEC;
        border-style: none solid;
        border-width: medium 1px;
        bottom: 0;
        display: block;
        height: 28px;
        line-height: 28px;
        position: fixed;
        right: -131px;
        top: 190px;
        width: 200px;
        z-index: 2000;
    }
</style>
<div id="bottom111" style="display: none">
    <img onclick="open_kf()" src="<?php echo $this->_var['site_url']; ?>/themes/mall/default/styles/default/images/ico/kf.png" style=" cursor: pointer">
</div>


<div id="d_im_chat">
    <div id="talkWindowDivId" style="display:none"></div>
    <div class="tstart-drop-panel" style="display:none" id="bottom"></div>
    <div id="bottom11" style="display:none"></div>
</div>

<script>
    //判断首页
    var url_this = (window.location).toString();
    var tmp = url_this.split(SITEURL)[1];
    if(tmp == "" || tmp == "/" || tmp == "/index.php" || typeof(tmp) =="undefined"){
        $("#bottom111").css("display","");
    }

    <?php if ($this->_var['visitor']['user_id'] > 0): ?>
    $.post("index.php?app=default&act=get_chat","",function(rs){
            $("#bottom11").css("display","");
            $("#bottom11").html(rs);
    })
    <?php endif; ?>

    function open_kf(){
        <?php if ($this->_var['visitor']['user_id'] == 0): ?>
            window.location.href = '<?php echo $this->_var['site_url']; ?>/index.php?app=member&act=login&ret_url=';
        <?php endif; ?>
        <?php if ($this->_var['visitor']['user_id'] > 0): ?>
        if (document.getElementById("chatObj")) {
            show_kf();
        } else {
            $.post("index.php?app=default&act=get_chat&init_chat="+new Date(),"",function(rs){
                $("#bottom11").css("display","");
                $("#bottom11").html(rs);
                show_kf();
            })
        }
        <?php endif; ?>
    }
    function show_kf() {
        try {
            var chatSWF = document.getElementById("chatObj");
            var obj = new Object();
            obj.userid = "5594";
            obj.username = "djkkf001";
            obj.nickname = "在线客服01";
            obj.newMessageNum = "0";
            obj.isKf ="false";
            obj.messages = "";
            obj.online = "false";
            chatSWF.addOtherContector(obj);
        } catch (e) {
            setTimeout(show_kf, 1000);
        }
    }
 </script>
</div>
</body>
</html>
