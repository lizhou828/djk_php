<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 *    pos机相关功能主入口
 */
class PosApp extends MallbaseApp
{
    var $_member_mod;
    var $_posbind_mob;
    var $_posapply_mob;
    var $_store_mod;
    var $_posrecord_mob;
    function __construct(){
        $this->PosApp();
    }

    function PosApp(){
        parent::__construct();
        $this->_member_mod =& m('member');
        $this->_posbind_mob =& m('posbind');
        $this->_posapply_mob =& m('posapply');
        $this->_posrecord_mob =& m('posrecord');
        $this->_store_mod =& m('store');
        $state = $this->visitor->get('state');
        $store_mod =& m('store');
        if($store_mod->get("store_id =".$this->visitor->get('user_id')." and state=1 and dropState=1")){
            $this->assign('storeFlg', 1);
        }
    }

     //pos首页
    function index(){
        $this->_config_seo('title', "pos机刷卡" . ' - ' . Lang::get('member_center'));
        $this->display('pos.index.html');
    }

    //pos申请
    function apply(){

        if($_GET["success"]){
            $this->assign('success', 1);
            $this->display('pos.apply.html');
            exit;
        }

        if(!$_POST){
            $this->_config_seo('title', "POS机申请" . ' - ' . Lang::get('member_center'));

            $store=$this->_store_mod->get("s.state=1 and s.seller_type=3 and s.dropState=1 and s.store_id=".$this->visitor->get("user_id"));
            if(!$store){
                $response_html="只有实体店类型的商家才能申请pos机终端！<br><br><a href='index.php?app=apply&step=2&id=2&p=seller'>没有实体店？点击进入商家入驻并且选择商家类型为实体店。</a>";
                $response_html.="<br><br><a href='javascript:history.go(-1)'>返回上一页</a>&nbsp;&nbsp;<a href='index.php?app=pos'>pos机主页</a>&nbsp;&nbsp;<a href='index.php?app=member'>会员中心</a>";
                $this->show_warning($response_html);
                exit;
            }

            $apply = $this->_posapply_mob->get("user_id=".$this->visitor->get('user_id'));
            if($apply){
                $this->assign('data', $apply);
            }
            $this->assign('store', $store);
            $this->display('pos.apply.html');
        }else{
            $seller_name = $_POST["seller_name"];
            $store_name = $_POST["store_name"];
            $mobile_phone_number = $_POST["mobile_phone_number"];
            $telephone_number = $_POST["telephone_number"];

            if($seller_name == null || $seller_name ==""){
                echo "<script type=\"text/javascript\">window.parent.callback_apply(".json_encode(array("flag"=>"error","error_msg"=>"商家名称名称不能为空","error_lbl"=>"seller_name")).");</script>";
                exit;
            }

            if($store_name == null || $store_name ==""){
                echo "<script type=\"text/javascript\">window.parent.callback_apply(".json_encode(array("flag"=>"error","error_msg"=>"店铺名称不能为空","error_lbl"=>"store_name")).");</script>";
                exit;
            }

            if($mobile_phone_number == null || $mobile_phone_number ==""){
                echo "<script type=\"text/javascript\">window.parent.callback_apply(".json_encode(array("flag"=>"error","error_msg"=>"手机号码不能为空","error_lbl"=>"mobile_phone_number")).");</script>";
                exit;
            }
            $data["seller_name"] = $seller_name;
            $data["store_name"] = $store_name;
            $data["mobile_phone_number"] = $mobile_phone_number;
            $data["telephone_number"] = $telephone_number;
            $data["user_id"] = $this->visitor->get('user_id');

            ini_set('date.timezone','Asia/Shanghai');
            $data["create_time"] = date('Y-m-d H:i:s',time());

            $apply = $this->_posapply_mob->get("user_id=".$this->visitor->get('user_id'));
            $apply_id = null;
            if($apply){
                $data["status"]=0;
                $apply_id = $this->_posapply_mob->edit($apply["id"],$data);
            }else{
                $apply_id = $this->_posapply_mob->add($data);
            }
            if($apply_id>0){
                echo "<script type=\"text/javascript\">window.parent.callback_apply(".json_encode(array("flag"=>"ok")).");</script>";
            }else{
                echo "<script type=\"text/javascript\">window.parent.callback_apply(".json_encode(array("flag"=>"error","error_msg"=>"网络繁忙".$this->pr($data),"error_lbl"=>"net_error")).");</script>";
            }

       }
    }

     //pos绑定
    function bind(){
        if(!$_POST){
            $this->_config_seo('title', "POS机在线绑定" . ' - ' . Lang::get('member_center'));
            $msm_time = $_SESSION["msm_time"];
            if($msm_time != "" && $msm_time >0 && gmtime() - $msm_time < 120){
                $this->assign('t_time',120-(gmtime() - $msm_time));
            }

            $channel_mod =& m('channel');
            $channels=$channel_mod->find(array(
                "conditions"=>" status =1 and type=4 and channel_code!='ZHIFUBAO' and channel_code!='UPOP' and channel_code !='CAIFUTONG' ",
                'limit' => 100,
                'count' => true
            ));

            $user_id = $this->visitor->get('user_id');
            if ($user_id != null  && $user_id != "") {
                $user = $this->_member_mod->get("user_id='$user_id'");
                $this->assign('user', $user);
            }

            $this->assign('channels', $channels);
            $this->display('pos.bind.html');
        }else{
            $show_type = $_POST["show_type"];
            $user_name = $_POST["user_name"];
            $password = $_POST["password"];

            $bank_name = $_POST["bank_name"];
            $bank_code = $_POST["bank_code"];
            $bank_card = trim($_POST["bank_card"]);
            $real_name = $_POST["real_name"];
            $shenfenzheng = $_POST["shenfenzheng"];
            $m_phone = $_POST["m_phone"];
            $yanzhengma = $_POST["yanzhengma"];



            if($user_name == null || $user_name ==""){
                echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"请输入用户名","error_lbl"=>"user_name")).");</script>";
                exit;
            }

            $yanzhengma = $_POST["yanzhengma"];

            if(IF_DUANXING !=0){
                if($yanzhengma=="" || empty($_SESSION["yanzhengma"]) || $_SESSION["yanzhengma"] == "" ){
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"请输入验证码","error_lbl"=>"yanzhengma")).");</script>";
                    exit;
                }

                $init_code = $_SESSION["yanzhengma"];
                if ($yanzhengma != $init_code){
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"验证码错误","error_lbl"=>"yanzhengma")).");</script>";
                    exit;
                }
            }


            $user_id = 0;
            //已经有帐号
            $user_id = $this->visitor->get('user_id');
            $user = $this->_member_mod->get("user_id='$user_id'");
            if($show_type == 1){
                $user = $this->_member_mod->get("user_name='$user_name'");

                if ($user["member_type"] != "01") {
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"服务中心或者采购帐号不能绑定pos消费银行卡","error_lbl"=>"user_name")).");</script>";
                    exit;
                }

                if ($user["im_qq"] == null || $user["im_qq"] == "") {
                    if($user == null || $user["password"] != md5($password)){
                        echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"您的登入帐号密码错误".$user["im_qq"],"error_lbl"=>"user_name")).");</script>";
                        exit;
                    }
                }
                $this->_do_login($user["user_id"]);
                $user_id = $user["user_id"];
            }else{
                //没有帐号
                if (substr(strtoupper($user_name),0,3) == "DJK") {
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"用户名错误【用户名不能已DJK开头】","error_lbl"=>"user_name")).");</script>";
                    exit;
                }
                $user = $this->_member_mod->get("user_name='$user_name' or phone_mob ='$user_name' or email ='$user_name'");
                if($user){
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"用户名已经存在，请重新输入","error_lbl"=>"user_name")).");</script>";
                    exit;
                }
                $confirm_password = $_POST["confirm_password"];
                if($confirm_password == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"确认密码不能为空","error_lbl"=>"confirm_password")).");</script>";
                    exit;
                }
                if($confirm_password != $password){
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"二次密码不一致","error_lbl"=>"confirm_password")).");</script>";
                    exit;
                }
                $user_data["user_name"]   = $user_name;
                $user_data["password"]    = md5($password);
                $user_data["member_type"] = "01";
                $user_data["nick_name"]   = $user_name;
                $user_data["reg_ip"]   = real_ip();
                $user_id = $this->_member_mod->add($user_data);
                $this->_do_login($user_id);
            }

            if($user_id>0){

                if (strlen($bank_card)<14) {
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"银行卡号最少14位","error_lbl"=>"user_name")).");</script>";
                    exit;
                }
                $hiddenbankcard = bank_hidden($bank_card);
                $bank_bind = $this->_posbind_mob->get("bank_card='$hiddenbankcard' and state = 1");
                if ($bank_bind) {
                    echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"银行卡已经存在，请重新输入","error_lbl"=>"user_name")).");</script>";
                    exit;
                }



                $data["user_name"]=$user_name;
                $data["bank_name"]=$bank_name;
                $data["bank_code"]= $_POST["bank_code"];
                $data["bank_card"]= $bank_card;
                $data["full_bank_card"]= $bank_card;
                $data["real_name"]=$real_name;
                $data["shenfenzheng"]=$shenfenzheng;
                $data["m_phone"]=$m_phone;
                $data["state"]=1;
                $data["user_id"]=$user_id;
                ini_set('date.timezone','Asia/Shanghai');
                $data["create_time"]=date('Y-m-d H:i:s',time());
                $id = $this->_posbind_mob->add($data);
                $this->_posbind_mob->commit_transaction();
                echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"ok")).");</script>";
                exit;
            }else{
                echo "<script type=\"text/javascript\">window.parent.callback_bind(".json_encode(array("flag"=>"error","error_msg"=>"找不到指定的用户","error_lbl"=>"user_name")).");</script>";
                exit;
            }

        }

    }

    //消费记录
    function show_list(){
        $this->_config_seo('title', "POS机刷卡交易记录" . ' - ' . Lang::get('member_center'));

        $conditions="1=1 ";

        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(pos_record.xiaofei_time,'%Y-%m-%d')>='$add_time_from'";
        }
        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(pos_record.xiaofei_time,'%Y-%m-%d')<='$add_time_to'";
        }

        $conditions.=" and pos_record.bank_card='".bank_hidden(trim($_GET["card_num"]))."'";

        $page = $this->_get_page();
        /*$posrecord     = $this->_posrecord_mob->find(array(
            'conditions'    => $conditions. " order by pos_record.xiaofei_time desc",
            'fields'=> "this.*,(select s.region_name from ecm_store s where s.pos_sn=pos_record.pos_sn limit 0,1) as region_name",
            'limit' => $page['limit'],
            'count' => true,
        ));*/
		$posrecord     = $this->_posrecord_mob->find(array(
            'conditions'    => $conditions." order by pos_record.xiaofei_time desc",
            'fields'=> "this.*,(select concat(s.region_name,s.address) from ecm_store s where s.pos_sn=pos_record.pos_sn limit 0,1) as region_name",
            'limit' => $page['limit'],
            'count' => true,
        ));

        $page['item_count'] = $this->_posrecord_mob->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data', $posrecord);
        $jine = $this->_posrecord_mob->getOne("select sum(pos_record.jine) from ecm_pos_record pos_record where bank_card='".bank_hidden(trim($_GET["card_num"]))."'");
        $this->assign('sum_jine', $jine);
        $this->display('pos.show_list.html');
    }
}

?>
