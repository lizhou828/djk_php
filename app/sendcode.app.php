<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 13-9-10
 * Time: 下午6:12
 * To change this template use File | Settings | File Templates.
 */

class SendCodeApp extends MallbaseApp{

    /* 构造函数 */
    function __construct() {
        $this->SendCodeApp();
    }

    function SendCodeApp() {
        parent::__construct();
        $this->_member_mod =& m('member');
        include_once(ROOT_PATH . '/includes/MSM.php');                    //短信发送
        include_once(ROOT_PATH . '/includes/email.class.php');          //邮件发送
    }
    function send(){
        $phone_or_tel=$_POST["phone_or_tel"]?$_POST["phone_or_tel"]:$_GET["phone_or_tel"];
        if($phone_or_tel==""){
            echo "不能为空!<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
            exit;
        }
        if($_GET["type"]!="bind_pos"){
            $data=$this->_member_mod->find("(phone_mob='$phone_or_tel' or email='$phone_or_tel')");
            if(count($data)>1){
                echo "手机已经存在，请更换！<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
                exit;
            }
        }
        $old_time = $_SESSION["msm_time"];
        if (!$old_time || $old_time == '') {
            $old_time = gmtime();
            $_SESSION["msm_time"] = $old_time;
        } else if (gmtime() - $old_time < 120) {
            echo "发送失败，请稍后发送!<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
            exit;
        }
        $_SESSION["msm_time"] = gmtime();
        $code= $this->generate_code(6);
        $_SESSION["yanzhengma"]=$code;
        $this->send_msm($phone_or_tel,$code);
        exit;

    }


    //绑定成功后验证手机
    function send2(){
        $phone_or_tel=$_POST["phone_or_tel"]?$_POST["phone_or_tel"]:$_GET["phone_or_tel"];
        if($phone_or_tel==""){
            echo "<br>不能为空!<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
            exit;
        }
        $data=$this->_member_mod->find("(phone_mob='$phone_or_tel')");
        if(count($data)>1){
            echo "<br>手机已经存在，请更换！<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
            exit;
        }else if(count($data)==1){
            $user_id=$this->visitor->get("user_id");
            foreach($data as $k=>$v){
                if($v["user_id"]!=$user_id){
                    echo "<br>手机号已被占用！<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
                    exit;
                }
            }
        }

        $old_time = $_SESSION["msm_time"];
        if (!$old_time || $old_time == '') {
            $old_time = gmtime();
            $_SESSION["msm_time"] = $old_time;
        }else if (gmtime() - $old_time < 120) {
            echo "<br>发送失败，请不要重复发送!<script>window.clearInterval(InterValObj);$(\"#btnSendCode\").removeAttr(\"disabled\");$(\"#btnSendCode\").val(\"重新发送验证码\");</script>";
            exit;
        }
        $_SESSION["msm_time"] = gmtime();
//        if(Conf::get('IF_DUANXING') ==0) {
//            $code =1234;
//        }else {
            $code=$this->generate_code(6);
//        }
        //$code="1234";//
//        $code = $this->generate_code(6);
        $_SESSION["yanzhengma"]=$code;
        $this->send_msm($phone_or_tel,$code);
        exit;
    }

    function bank_sendCode() {
        $phone = $_POST['phone'];
        $code = $_POST['code'];
        if(false && Conf::get('IF_DUANXING') ==0) {
            $code1 =1234;
        }else {
            $code1=$this->generate_code(7);
            $_SESSION['delete_bank_code'] = $code1;
            $content = "您正在进行更改绑定银行账户操作，卡号为" . $code ."。本次操作验证码为" .$code1 ."。";
            $smsclient = new SMSClient();
            $rs = $smsclient->sendSMS($phone,$content);
        }
//        echo $code1;
        if($rs && $rs["errorno"]==0){
            echo json_encode(array("flag"=>"ok"));
            return;
        }else{
            echo json_encode(array("flag"=>"error"));
            return;
        }
    }

    function getoldtime() {
        $old_time = $_SESSION["msm_time"];
        if (!$old_time || $old_time == '') {
            echo 0;
            exit;
        } else {
            if (gmtime() - $old_time < 120) {
                echo ceil((gmtime() - $old_time));
            } else {
                echo 0;
            }
            exit;
        }
    }

    function checkCode() {
        $init_code = $_SESSION["yanzhengma"];
        $code = $_GET["yanzhengma"] ? $_GET["yanzhengma"] : $_POST["yanzhengma"];
        if ($code != $init_code) {
            echo ecm_json_encode(false);
        } else {
            echo ecm_json_encode(true);
        }
    }

    function check_email(){
        //$this->_member_mod
    }
    function check_mob(){
        //$_GET
        $phone_mob=$_GET["phone_mob"];
        $data=$this->_member_mod->find("phone_mob=$phone_mob");
        if(count($data)>1){
            echo 0;
        }else if(count($data)==0){
            echo 1;
        }else{
            foreach($data as $k=>$v){

            }
        }
    }

    function generate_code($len = 4)
    {
        $chars = '1234567890';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
            $arr[$i] = $chars[$i];
        }
        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }

    /*function test(){
        $code = $this->generate_code(4);
        $smsclient = new SMSClient();
        $rs=$smsclient->sendSMS('15821020398','您好，您本次操作的验证是：'.$code."!本短信发自中国大集客！如果不是本人操作，请忽略");
    }*/
    function send_msm($tel,$code){
        //echo "发送成功！";
        $smsclient = new SMSClient();
        $rs=$smsclient->sendSMS($tel,"您本次操作的验证码为".$code."（大集客客服绝不会索取此验证码，切勿告知他人），如果不是本人操作，请自动忽略。【大集客】");
        if($rs["errorno"]==0){
            $_SESSION['yanzhengma'] = $code;
            echo "验证码发送成功！";
        }else{
            echo "发送失败！";
            $_SESSION["msm_time"]=0;
        }
    }

    //java 调用 php 短信平台 接口
    function msm_api(){
        $tel = $_POST["mob"];
        $content = $_POST["content"];       //内容中包含 验证码

        if($tel == "" || $content == ""){
            echo json_encode(array("code"=>201));      //参数错误
            exit;
        }
        /*if($_SERVER['SERVER_NAME'] != JAVA_LOCATION ){
            echo json_decode(array("code"=>202));     //请求地址错误
            exit;
        }*/
        $smsclient = new SMSClient();
        $rs=$smsclient->sendSMS($tel,"$content【大集客】");

        if($rs["errorno"]==0){
            echo json_encode(array("code"=>200));     //发送成功
            exit;
        }
        echo json_encode(array("code"=>203));     //短信平台错误
    }

    function test(){

        $mailconfig['host'] =       EMAIL_HOST; //主机
        $mailconfig['port'] =       EMAIL_PORT; //端口 一般为25
        $mailconfig['trueemail'] = EMAIL_TRUENAME; //真实的地址
        $mailconfig['username'] = EMAIL_USERNAME; //SMTP认证的帐号
        $mailconfig['password'] = EMAIL_PASSWORD; //改成自己的
        $mailconfig['debug'] = true; //是否显示和服务器会话信息？
        $mailconfig['from'] = EMAIL_FROM; //显示给用户的发件人

        $this->pr($mailconfig);

        set_time_limit(EMAIL_TIME);
        $smtp = new SMTP($mailconfig);
        $smtp->send(array("17706753@qq.com"), "test", "123456");
        echo "ok";
    }
    function  send_email($smail, $title, $content){
        $mailconfig['host'] =       EMAIL_HOST; //主机
        $mailconfig['port'] =       EMAIL_PORT; //端口 一般为25
        $mailconfig['trueemail'] = EMAIL_TRUENAME; //真实的地址
        $mailconfig['username'] = EMAIL_USERNAME; //SMTP认证的帐号
        $mailconfig['password'] = EMAIL_PASSWORD; //改成自己的
        $mailconfig['debug'] = false; //是否显示和服务器会话信息？
        $mailconfig['from'] = EMAIL_FROM; //显示给用户的发件人
        set_time_limit(EMAIL_TIME);
        $smtp = new SMTP($mailconfig);
        $smtp->send(array($smail), $title, $content);
    }

    function active() {
        $email_active_code = $_GET["code"];
        $member_mod  =& m('member');
        $user = $member_mod->get("email_active_code='" . $email_active_code . "' and email_bind_status=0");
        if ($user["user_id"]>0) {
            $member_mod->edit($user["user_id"], array("email_bind_status"=>1,"email_active_code"=>""));
            $this->assign("active_user", $user);
        }
        $this->display("sendcode.active.html");
    }

    //
    function checkCode2() {
        $init_code = $_SESSION["yanzhengma"];
        $code = $_GET["yanzhengma"] ? $_GET["yanzhengma"] : $_POST["yanzhengma"];
        if ($code != $init_code) {
            echo 0;
        } else {
            echo 1;
        }
    }

    //安全邮箱 -- 验证码
    function checkCode3() {
        $init_code = base64_decode($_SESSION['captcha']);
        $code = $_GET["yanzhengma"] ? $_GET["yanzhengma"] : $_POST["yanzhengma"];
        if ($code != $init_code) {
            echo 0;
        } else {
            echo 1;
        }
    }

    //验证用户名
    function  checkUser(){
        $password=$_GET["password"]?$_GET["password"]:$_POST["password"];
        if($password==""){
            echo "<br>密码不能为空！";
            exit;
        }
        $user_id=$this->visitor->get("user_id");
        if($user_id<=0){
            echo "<br>请先登入！";
            exit;
        }
        $userInfo=$this->_member_mod->get($user_id);

        if($userInfo["password"]!=md5($password)) {
            echo 0;
        } else {
            echo 1;
        }
    }

    //会员中心接收设置二级密码 发送短信到已经绑定的手机
    function  send3(){
        $user_id=$this->visitor->get("user_id");
        if($user_id<=0){
            echo "请先登入!";
            exit;
        }
        $user=$this->_member_mod->get_info($user_id);
        $old_time = $_SESSION["msm_time"];
        if (!$old_time || $old_time == '') {
            $old_time = gmtime();
            $_SESSION["msm_time"] = $old_time;
        }else if (gmtime() - $old_time < 120) {
            echo "发送失败，请稍后发送!";
            exit;
        }
        $_SESSION["msm_time"] = gmtime();

        /*$code="1234";//        测试的时候调用
        $_SESSION["yanzhengma"]=$code;
        echo "发送成功";
        exit;*/
        if(false && Conf::get('IF_DUANXING') ==0) {
            $code =1234;
        }else {
            $code=$this->generate_code(6);
        }
        $_SESSION["yanzhengma"]=$code;
        $this->send_msm($user["phone_mob"],$code);
    }

    function order_code()
    {
        $order_id = $_POST['order_id'];
        if($order_id == ""){
            $order_id = $_GET['order_id'];
        }
        if(!$order_id == ""){
            $order_goods_model = & m('ordergoods');
            $order_goods = $order_goods_model -> find(array(
                'conditions' => 'order_id = '. $order_id,
            ));
            if(count($order_goods) > 0){
                if(false && Conf::get('IF_DUANXING') ==0) {
                    $code =1234;
                }else {
                    $code=$this->generate_code(6);
                }
                foreach($order_goods as $goods){
                    if($goods['cellphone'] != 0 && $goods['cellphone'] != ""){
                       $order_goods_model -> edit('rec_id = '. $goods['rec_id'] , 'code = '. $code);
                    }
                }
                $smsclient = new SMSClient();
                $smsclient -> sendSMS($goods['cellphone'],'您好，您的订单号:'.$order_id.'。验证码是：'.$code." 。本短信发自中国大集客！【大集客】");
                echo $order_id."success";
            }else {
                echo $order_id."no_such";
            }

        }
    }
}
?>