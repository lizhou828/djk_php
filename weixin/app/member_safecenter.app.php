<?php
/**
 * Created by JetBrains PhpStorm.
 * User: liz
 * Date: 13-11-25
 * Time: 下午4:46
 * To change this template use File | Settings | File Templates.
 */
/**
 * 微商城用户中心：安全中心（包含设置支付密码、手机绑定、修改密码等）
*/
class Member_safecenterApp extends MallbaseApp{
    function index(){
        $userId = $this -> visitor ->get("user_id");
        if($userId < 1){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }else{
            $user_mod = &m("member");
            $user = $user_mod->get('user_id='.$userId);
            $this->assign("user",$user);
            $this->display("member_safecenter.index.html");
        }

    }

    //设置支付密码
    function setPayPassword(){
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $this->assign("user",$user);
        if(!$userId){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }else{
            if(!$user['phone_mob'] || $user['phone_mob'] == null || $user['phone_mob'] == " "){
                $this->display("member_safecenter.bindIndex.html");
            }else{
                $this->display("member_safecenter.setPayPassword.html");
            }
        }
    }


    function passwordPage(){
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $this->assign("user",$user);
        if(!$userId){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }else{
            $this->display("member_safecenter.changePayPasswd.html");
        }
    }
    function changePayPasswd(){
        $password1 = $_GET['password1'];
        $password2 = $_GET['password2'];
        if( !$password1 || $password1 == " " || !$password2 || $password2 == " " || $password1 != $password2){
            exit;
        }
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $result = $user_mod->edit('user_id='.$userId,array("password2"=>md5($password2)));
        $this->json_result($result,$user['password2']);
    }
    function changePayPasswdSuccess(){
        $flag = $_GET['flag'];//flag==1 表示是再次修改密码
        $this->assign("flag",$flag);
        $this->display("member_safecenter.changePayPasswdSuccess.html");
    }

    function bindIndex(){
        $userId = $this -> visitor ->get("user_id");
        if(!$userId){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }
        $user_mod = &m("member");
        $user = $user_mod->get("user_id=".$userId);
        $this->assign("user",$user);
        $this->display("member_safecenter.bindIndex.html");
    }

    //检查手机验证码
    function checkCode(){
        $user_mod = &m("member");
        $userId = $this -> visitor ->get("user_id");
        if(!$userId){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }

        $init_code = $_SESSION["yanzhengma"];
        $code = $_POST["verifyCode"];
        $phone_mob =$_POST["phone_mob"];


        if(!preg_match("/1[3|4|5|8]([0-9]{9})/",$phone_mob)){
            $this->json_result(0,"非法手机号码!");
        }
        $result = $user_mod->get("phone_mob=".$phone_mob."");
        if($result){
            $this->json_result(0,"该手机号已被使用!");
        }
        if ($code != $init_code) {
            $this->json_result(0,"验证码填写错误!");
        }

        $user_mod = &m("member");
        $user_mod->edit('user_id='.$userId,array("phone_mob"=>$phone_mob,"phone_mob_bind_status" => 1 ) );
        $this->json_result(1,"成功绑定手机号码!");

    }

    function checkPhone(){
        $phone_mob =$_GET["phone_mob"] ? $_GET["phone_mob"] : $_POST["phone_mob"] ;
        $user_mod = &m("member");
        $result = $user_mod->get("phone_mob=".$phone_mob."");
        if(!$result){
           echo 1;
        }
    }

    //绑定手机号码
    function bindMobile(){
        $this->display("member_safecenter.bindMobile.html");
    }
    function bindMobileSuccess(){
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get("user_id=".$userId);
        $this->assign("user",$user);
        $this->display("member_safecenter.bindMobileSuccess.html");
    }

}