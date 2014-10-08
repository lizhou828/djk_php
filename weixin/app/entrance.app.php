<?php

/**
 *    验证码
 *
 *    @author    Garbin
 *    @usage    none
 */
class EntranceApp extends MallbaseApp
{
    function index()
    {
        include ROOT_PATH."/weixin/weixin.php";
        $wechatObj = new WechatCallbackapiTest();

        if (isset($_GET['echostr'])) {
            $wechatObj->valid();
        }else{
            $wechatObj->responseMsg();

        }
    }
    function memberIndex(){
        include ROOT_PATH."/weixin/weixin_member.php";
        $weChatObj = new MemberWeChatCallbackAPI();
        if (isset($_GET['echostr'])) {
            $weChatObj->valid();
        }else{
            $weChatObj->responseMsg();
        }
    }


}

?>