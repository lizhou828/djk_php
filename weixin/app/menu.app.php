<?php
class MenuApp extends MallbaseApp{
    function index (){
        $siteUrl = SITE_URL;
        // $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=aiyGDQlRY5FGS7S7aIFBcP2AyTShENq5PCPDR2jMcnWT-Bx9nLMkRgXSkNXDbxsB_Hj4tCGuiUn1D95A7rnFOFzDHv5t-xm07447AyAKTFQHJJvMWD0gOeB8HujgXUMkm_QZEW0j-b5LKp5kkmWwfg";
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=xc9CFBArBXKiT9DtxbtFRtQQgApCWGFz-CJ9laldYtxjHc3hIxFUBpp6nDHnJw--9JtWkYAF1ePzwU0l4N1-XQfFuDS00c8XjsprDKYfBj-i9fu3Lr6KOUZD34rNfQ0y_viMB8gJ2DMIswh6ItxTRw";
        $data = "{
        \"button\":[
        {
        \"type\":\"click\",
        \"name\":\"本地生活\",
        \"sub_button\":[
        {
        \"type\":\"view\",
        \"name\":\"进入本地生活\",
        \"url\":\"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx7ac999e64dae7632&redirect_uri=$siteUrl/weixin/bdsh&response_type=code&scope=snsapi_base&state=1#wechat_redirect\"
        },
        {
        \"type\":\"click\",
        \"name\":\"查找附近加盟商家\",
        \"key\":\"V1001_01\"
        },
        {
        \"type\":\"view\",
        \"name\":\"POS机刷卡绑定\",
        \"url\":\"$siteUrl/weixin/index.php?app=pos&act=bind\"
        },
        {
        \"type\":\"click\",
        \"name\":\"免费申请POS机\",
        \"key\":\"V1001_01_02\"
        }]
        },
        {
        \"type\":\"click\",
        \"name\":\"移动商城\",
        \"sub_button\":[
        {
        \"type\":\"view\",
        \"name\":\"进入移动商城\",
        \"url\":\"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx7ac999e64dae7632&redirect_uri=$siteUrl/weixin&response_type=code&scope=snsapi_base&state=1#wechat_redirect\"
        },
        {
        \"type\":\"view\",
        \"name\":\"国际馆\",
        \"url\":\"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx7ac999e64dae7632&redirect_uri=$siteUrl/weixin/hgg&response_type=code&scope=snsapi_base&state=1#wechat_redirect\"
        },
        {
        \"type\":\"view\",
        \"name\":\"工厂直供\",
        \"url\":\"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx7ac999e64dae7632&redirect_uri=$siteUrl/weixin/gczg&response_type=code&scope=snsapi_base&state=1#wechat_redirect\"
        },
        {
        \"type\":\"view\",
        \"name\":\"我要抽奖\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=member&act=wycj\"
        },
        {
        \"type\":\"view\",
        \"name\":\"我的专属链接\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=member&act=my_url\"
        }]
        },
        {
        \"type\":\"click\",
        \"name\":\"集客小店\",
        \"sub_button\":[
        {
        \"type\":\"view\",
        \"name\":\"进入我的小店\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=jkxd_portal&act=my_jkxd\"
        },
        {
        \"type\":\"view\",
        \"name\":\"我的收入\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=jkxd_portal&act=shouru\"
        },
        {
        \"type\":\"view\",
        \"name\":\"订单查询\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=jkxd_portal&act=order_all\"
        },
        {
        \"type\":\"view\",
        \"name\":\"商家管理\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=jkxd_portal&act=tuijian_store\"
        },
        {
        \"type\":\"view\",
        \"name\":\"一键推广\",
        \"url\":\"http://www.dajike.com/weixin/index.php?app=jkxd_portal&act=fenxiang\"
        }]
        }]
        }";

        $result =$this ->post($url,$data);
        //var_dump($result);


    }
}

?>