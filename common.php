<?php

require_once("./Connect2.0/API/qqConnectAPI.php");
$qc = new QC();
$acs = $qc->qq_callback();
$oid = $qc->get_openid();       //唯一的 openid
$qc = new QC($acs,$oid);
$ret = $qc->get_user_info();

echo "<meta charset='utf-8' />";
$qq=$ret["nickname"];        //qq昵称
$figureurl_qq_2=$ret["figureurl_qq_2"];

//echo "<pre>";
//print_r($ret);
//echo "<pre>";
//echo $oid;
//exit;
if($ret['ret'] == 0){
    $data=base64_encode($qq."#####".$oid."#####".time());
//    echo "data=".$data.".....<br/>";

    $qqLogin_app = $_COOKIE['qqLogin_app'];
//    echo "qqLogin_app=".$qqLogin_app.".....";
    if( $qqLogin_app == "weixin_shop" ){
        header("Location:http://testweixin.dajike.com/QQLogins/receivePhpQQLoginResult?data=".$data);
    }else if( $qqLogin_app == "shop" ){
        header("Location:http://testshop.dajike.com/QQLogins/receivePhpQQLoginResult?data=".$data);
    }else{
        header("Location: index.php?app=member&act=qq_login&data=".$data);
    }


}else{
    echo "<meta charset='utf-8' />";
    echo "获取失败，请开启调试查看原因";
}


?>


