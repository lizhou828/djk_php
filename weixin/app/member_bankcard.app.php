<?php
/**
 * Created by JetBrains PhpStorm.
 * User: liz
 * Date: 13-11-25
 * Time: 下午4:49
 * To change this template use File | Settings | File Templates.
 */
/**
 * 微商城用户中心：银行卡管理
*/
class Member_bankcardApp extends MallbaseApp{

    //绑定银行卡第一步：手机验证
    function mobileValidate(){
        $userId = $this -> visitor ->get("user_id");
        $ret_url = "";
        if($userId < 1){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }
        $member_mod = & m('member');
        $user=$member_mod->get("user_id=".$userId);

        if(!$user || !$user['phone_mob'] || !$user['phone_mob']==''){
            header("Location: ". SITE_URL . "/weixin/index.php?app=app=member_safecenter&act=bindIndex");
        }else{
            $this->assign("user",$user);
            $this->display("member_bankcard.mobileValidate.html");
        }

    }
    //绑定银行卡第二步：检查手机验证码
    function checkCode(){
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
        $code = $_GET["verifyCode"];
        if ($code != $init_code) {
            $this->json_result(-1,"验证码错误");
            exit;
        } else {
            $this->json_result(0,"验证码正确");

        }
    }
    //绑定银行卡第三步：绑定页面
    function bindPage(){
        $userId = $this -> visitor ->get("user_id");
        $ret_url = "";
        if($userId < 1){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }
        $member_mod = & m('member');
        $user=$member_mod->get("user_id=".$userId);
        $this->assign("user",$user);
        //未绑定手机的，去绑定手机
        if(!$user || !$user['phone_mob'] || $user['phone_mob']=='' || $user['phone_mob_bind_status'] == 0){
            header("Location: ". SITE_URL . "/weixin/index.php?app=member_safecenter&act=bindIndex");
        //未设置过支付密码的，去设置支付密码
        }elseif( $user['password2']== null || $user['password2']==""){
            header("Location: ". SITE_URL . "/weixin/index.php?app=member_safecenter&act=setPayPassword");
        }else{
            //支持的银行
            $channelMod = &m("channel");
            $payChannels = $channelMod->find("type=3 and status=1");
            $this->assign("payChannels",$payChannels);
            $this->display("member_bankcard.bindPage.html");
        }

    }


    //绑定银行卡第四步：绑定银行卡
    function bind(){
        $user_id = $this -> visitor ->get("user_id");

        $ret_url = "";
        if($user_id < 1){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }
        $member_bank_model = & m('memberbank');
        $user_name = $_POST['user_name'];
        $bank_code = trim($_POST['bank_code']);
        $bank_name = $_POST['bank_name'];
        $idCard = $_POST['id_card'];
        $kaHao= $_POST['kahao'];


        //该银行卡号已绑定
        $bankCards = $member_bank_model->find("user_id=".$user_id." and kahao='".$kaHao."'");

        $count = count($bankCards);
        if( $count > 0){
            $this->assign("errorMsg","该银行卡已被绑定,请用其它的银行卡！");
            $this->display("member_bankcard.bindFailed.html");
        }else{
            $member_mod = & m('member');
            $user=$member_mod->get("user_id=".$user_id);
            $memberArray=array();
            if(!$user['card_id'] ||$user['card_id']==null || $user['card_id']==""){
                $memberArray['card_id']=$idCard;
            }
            if(!$user['real_name'] ||$user['real_name']==null || $user['real_name']==""){
                $memberArray['real_name']=$user_name;
            }
            if(count($memberArray) > 0 ){
                $member_mod->edit("user_id=".$user['user_id'],$memberArray);
            }
            $user=$member_mod->get("user_id=".$user_id);
            $this->assign("user",$user);
            //是否设置为默认银行卡
            $default="";
            $cards =  $member_bank_model->findAll("user_id=".$user_id);
            if(!$cards){
                $default=1;
            }else{
                $default=0;
            }
            $data = array(
                'user_id' => $user_id,
                'bank_name' => $bank_name,
                'bank_code' => $bank_code,
                'user_name' =>$user['real_name'],
                'kahao' => $kaHao,
                'if_default' => $default,
                'add_time' => date('y-m-d',time()),
            );
            $member_bank_model -> add($data);

            $this->display("member_bankcard.bindSuccess.html");
        }
    }





    //检查是否绑定银行卡
    function check_bind (){
        $userId = $this -> visitor ->get("user_id");
        $ret_url = "";
        if($userId < 1){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }
        $bankCard = $_GET["bank_card"];
        $member_bank = & m('memberbank');
        $bankCards = $member_bank -> find("kahao=".$bankCard);
        $this->json_result(count($bankCards));
    }

    //已绑定的银行卡列表
    function cards(){
        $this->isLogin();
        $user_id = $this -> visitor ->get("user_id");
        $member_mod = & m('member');
        $user=$member_mod->get("user_id=".$user_id);
        $this->assign("user",$user);

        $memberBank_mod = &m("memberbank");
        $bankCards = $memberBank_mod->find('user_id='.$user_id." and dropState=1");
        $this->assign("bankCards",$bankCards);
        $this->assign("cardsCount",count($bankCards));
        $this -> assign('count',count($bankCards) - 1);
        $this->display("member_bankcard.cards.html");
    }

    //银行卡的相关操作需要确认支付密码
    function confirm_payPasswordPage(){
        $this->assign("card_id",$_GET['card_id']);
        $this->display("member_bankcard.confirm_payPasswordPage.html");
    }

    //解除银行卡绑定
    function remove_bankcard(){
        $this->isLogin();
        $user_id = $this -> visitor ->get("user_id");
        $member_mod = & m('member');
        $user=$member_mod->get("user_id=".$user_id);

        $memberBank_mod = &m("memberbank");
        $bankCard = $memberBank_mod->find('id='.$_POST['card_id'].' and user_id='.$user_id." and dropState=1");
        if( $_POST['card_id'] ==null || $_POST['card_id'] == ""){
            echo ecm_json_encode(false);
        }elseif($_POST['pay_password']==null || $_POST['pay_password']=="" || md5($_POST['pay_password']) != $user['password2']){
            echo ecm_json_encode(false);
        }elseif(!$bankCard){
            echo ecm_json_encode(false);
        }else{
            $memberBank_mod->edit("id=".$_POST['card_id'],array("dropState"=>0));
            echo ecm_json_encode(true);
        }


    }

    //每张银行卡信息
    function card_info(){
        $this->isLogin();
        $user_id = $this -> visitor ->get("user_id");
        $member_mod = & m('member');
        $user=$member_mod->get("user_id=".$user_id);
        $id= $_GET['id'];
        $memberBank_mod = &m("memberbank");
        $bankCard = $memberBank_mod->get('id='.$id);
        $this->assign("user",$user);
        $this->assign("bankCard",$bankCard);
        $this->display("member_bankcard.card_info.html");
    }



}