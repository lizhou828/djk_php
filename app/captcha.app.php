<?php

/**
 *    验证码
 *
 *    @author    Garbin
 *    @usage    none
 */
class CaptchaApp extends FrontendApp
{
    function index()
    {
        ob_clean();
        $this->_captcha(80, 24);
    }

    /* 检查验证码 */
    function check_captcha()
    {
        $captcha = empty($_GET['captcha']) ? '' : strtolower(trim($_GET['captcha']));
        if (!$captcha)
        {
            echo ecm_json_encode(false);
            return ;
        }
        if (base64_decode($_SESSION['captcha']) != $captcha)
        {
            echo ecm_json_encode(false);
        }
        else
        {
            echo ecm_json_encode(true);
        }
        return ;
    }
    /* 检查验证码 */
    function check_captcha2()
    {
        $captcha = empty($_GET['captcha']) ? '' : strtolower(trim($_GET['captcha']));
        if (!$captcha){
            echo "<script>$('#captcha').val('');js_fail('验证码不能为空');</script>";
            exit;
        }
        echo base64_decode($_SESSION['captcha']) ."---------". $captcha;exit;
//        if (base64_decode($_SESSION['captcha']) != $captcha){
//            echo "<script>$('#captcha').val('');js_fail('验证码错误');</script>";
//            exit;
//        }else{
//            echo "<script>document.getElementById('sava_form').submit();</script>";
//        }

    }
}

?>