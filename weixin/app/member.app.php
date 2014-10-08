<?php

/**
 *    会员中心
 *    @author    liz
 */
class MemberApp extends MallbaseApp{

    function index(){
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $this->isLogin();

        //类型01:会员,02:服务中心,03服务中心子账号,04采购账号
        if($user["member_type"]=="01"){
           $this->memberCenter();
        }elseif($user["member_type"]=="02"){
            $this->serviceCenter();
        }
    }

    //会员中心业务
    function memberCenter(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);


        //显示账户余额、积分、抽奖券 （如果是有店铺的话，显示余额、营业额）
        $userFinance = $this->getMemberFinance($userId);
        $this->assign("userFinance",$userFinance);

        /*查看是否绑定银行卡*/
        $member_bank = & m('memberbank');
        $bank = $member_bank -> find('user_id = ' . $this->visitor->get('user_id')." and dropState=1");
        $user['bank'] = count($bank);


        $order_mod =& m('order');
        /* 买家提醒：待付款、待确认、待评价订单数 */
        $sql0 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_SUBMITTED . "'";//已付款（针对货到付款而言，他的下一个状态是卖家已发货）
        $sql1 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_PENDING . "'";//待付款
        $sql2 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_SHIPPED . "'";//已发货
        $sql3 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";//交易成功
        $sql4 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_ACCEPTED . "'";//待发货

        $buyer_stat = array(
            'submitted'  => $order_mod->getOne($sql0)+$order_mod->getOne($sql4),
            'pending'  => $order_mod->getOne($sql1),
            'shipped'  => $order_mod->getOne($sql2),
            'finished' => $order_mod->getOne($sql3),
            'accepted' => $order_mod->getOne($sql4),
        );
        $sum = array_sum($buyer_stat);
        $buyer_stat['sum'] = $sum;
        $this->assign('buyer_stat', $buyer_stat);
        $this->assign('TO_CHONGZHI_URL', TO_CHONGZHI_URL);


        /* 卖家提醒：待处理订单和待发货订单 */
        $store_mod = &m("store");
        $store = $store_mod->get("store_id =".$userId." and state=1 and dropState=1");
        if($store){
            $user['store'] = $store;
            $sql5 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_PENDING . "'";//待付款
            $sql6 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_SHIPPED . "'";//已发货
            $sql7 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";//交易成功
            $sql8 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_ACCEPTED . "'";//已付款，等待发货
            $seller_stat = array(
                'pending'  => $order_mod->getOne($sql5),
                'shipped'  => $order_mod->getOne($sql6),
                'finished' => $order_mod->getOne($sql7),
                'accepted' => $order_mod->getOne($sql8),
            );
            $this->assign('seller_stat', $seller_stat);
            $yye =$user_mod->getOne("SELECT SUM(order_amount-jine) FROM ecm_order WHERE STATUS=40 and seller_id =".$user['user_id']);
            $user["yye"]= ($yye!=null) ? $yye:0;
        }

        $user["all_choujiang"]=$user_mod->getOne("select count(1) as cnt from ".DB_PREFIX."choujiangquan where user_id=".$user['user_id']);
        $user["choujiang"]=$user_mod->getOne("select count(1) cnt from ".DB_PREFIX."choujiangquan where count!=100 and  user_id=".$user['user_id']);
        $yuelog_mod =&m("yuelog");
        $user['allShouyi'] = $yuelog_mod->db->getOne("SELECT SUM(jine) FROM ecm_yue_log WHERE user_id  = ".$user['user_id']." AND type =6");
        //是否是签约业务员:
        $memberqianyue_mod =& m('memberqianyue');
        $qianyue = $memberqianyue_mod->get("user_id=".$userId);
        $this->assign("qianyue",$qianyue);

        $this->assign('user', $user);
        $this -> display("member.member_center.html");
    }

    //服务中心业务
    function serviceCenter(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        //显示账户余额、积分、抽奖券 （如果是有店铺的话，显示余额、营业额）
        $userFinance = $this->getMemberFinance($userId);
        $this->assign("userFinance",$userFinance);

        /*查看是否绑定银行卡*/
        $member_bank = & m('memberbank');
        $bank = $member_bank -> find('user_id = ' . $user['user_id']);
        $user['bank'] = count($bank);

        //查找服务中心子账户
        $users=$user_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$user["region_id"],
            "fields"    =>"user_id,user_name"
        ));
        $query_conditions="store.fwzx=".$user['user_id'];
        if(count($users)>0 && $user["member_type"]=="02"){
            foreach($users as $k=>$v){
                if($query_conditions == ""){
                    $query_conditions.="store.fwzx=".$v["user_id"]; //获得子账号下面的商家
                }else{
                    $query_conditions.=" or store.fwzx=".$v["user_id"]; //获得子账号下面的商家
                }
            }
        }

        $order_mod =& m('order');
        //托管商家yye
        $tuoguan_yyy_sql = "SELECT SUM(ord.order_amount-ord.jine) FROM ecm_order ord,ecm_store store where
                            ord.status=40 and ord.seller_id = store.store_id and store.tuoguan =1 and ($query_conditions)";
        $tuoguan_shouyi = $order_mod->getOne($tuoguan_yyy_sql);
        //本区营业额
        $benqu_yyy_sql = "SELECT SUM(ord.order_amount-ord.jine) FROM ecm_order ord,ecm_store store where
                             ord.status=40 and ord.seller_id = store.store_id and store.region_id =".$user["region_id"];
        if($user["user_name"] == "djkhanguo"){
            $benqu_yyy_sql = "SELECT SUM(ord.order_amount-ord.jine) FROM ecm_order ord where ord.status=40 and ord.guojiguan=2";
        }
        $benqu_yyy = $order_mod->getOne($benqu_yyy_sql);
        $this->assign('tuoguan_yye', $tuoguan_shouyi);
        $this->assign('benqu_yye', $benqu_yyy);

        /* 卖家提醒：待处理订单和待发货订单 */
        $goodsqa_mod = & m('goodsqa');
        $query="";
        if($user["region_id"]!=""){
            $query="AND region_id=".$user["region_id"];
        }
        $sql7 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']."
              AND tuoguan=1 AND dropState=1 $query) AND status = '" . ORDER_SUBMITTED . "'";
        $sql8 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']."
              AND tuoguan=1 AND dropState=1 $query) AND status = '" . ORDER_ACCEPTED . "'";
        $sql88 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']."
              AND tuoguan=1 AND dropState=1 $query)";

        $sql9 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE store_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']."
              AND tuoguan=1 AND dropState=1 $query) AND reply_content ='' ";

        $sql10 = "SELECT COUNT(1) FROM ".DB_PREFIX."order_shouhou WHERE
               (STATUS=0 OR STATUS=1) AND
               seller_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']." AND tuoguan=1 AND dropState=1 $query)";
        $sql11 = "SELECT COUNT(1) FROM ".DB_PREFIX."goods WHERE
               dropState=1 AND
               store_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']." AND tuoguan=1 AND dropState=1 $query)";
        $seller_stat = array(
            'submitted' => $order_mod->getOne($sql7),
            'accepted'  => $order_mod->getOne($sql8),
            'all'  => $order_mod->getOne($sql88),
            'replied'   => $goodsqa_mod->getOne($sql9),
            'shouhou'   => $goodsqa_mod->getOne($sql10),
            'goods'     => $goodsqa_mod->getOne($sql11),
        );
        $this->assign('seller_stat', $seller_stat);
        $this->assign('TO_CHONGZHI_URL', TO_CHONGZHI_URL);


        $this->assign('user', $user);
        $this -> display("member.member_center.html");
    }


     function register(){
         $userName = trim($_POST['username']);
         $password = trim($_POST['password']);
         $flag = $_POST['flag'];
         $returnUrl= $_POST['returnUrl'];
         $t_id = $_POST['t_id'];
         if($t_id == ""){
             $t_id = ecm_getcookie("u_id");
         }
         $member_model = & m('member');
         if($flag == "false"){
             echo json_encode(array("flag"=>"error"));
             return;
         }
         if($userName == "" || $password == "" || $userName == "请输入账号"){
             echo json_encode(array("flag"=>"null"));
             return;
         }
         if (substr(strtoupper($userName),0,3) == "DJK") {
             echo json_encode(array("flag"=>"dajike"));
             return;
         }
         $userLength = (strlen($userName) + mb_strlen($userName,'UTF8'))/2;
         $passLength = (strlen($password) + mb_strlen($password,'UTF8'))/2;
         if($passLength < 6 || $passLength > 20){
             echo json_encode(array("flag"=>"length"));
             return;
         }
         $password = md5($password);
         $info = $member_model->get("(user_name = '$userName' or email = '$userName' or phone_mob = '$userName') and dropState = 1");
         if($info['user_id'] > 0 ){
             echo json_encode(array("flag"=>"no"));
             return;
         }else{
             $member_mod  = & m('member');
             if($t_id != ""){
                 $dateInfo = $member_mod->get_info($t_id);
                 if (!empty($dateInfo) && $dateInfo["member_type"] == "01") {
                     $t_id = $dateInfo["user_id"];
                 }else{
                     $t_id = "";
                 }
             }
             $this->visitor->logout();
             $data = array('user_name' => $userName, 'nick_name' => $userName, 'password' => $password, 'member_type' => '01', 'dropState' => 1,'t_id' => $t_id,'reg_time' => gmtime());
             $user_id = $member_model->add($data);


             ecm_setcookie("u_id","",time() + 86400);

             $params = null;
             $params["type"] = "regist";
             $params["userId"] = $user_id;
             $params["tId"] = ($t_id>0) ? $t_id : 0;
             $params["orderId"] ="";
             $params["userName"] ="";
             $params["channelCode"] ="";
             $params["channelName"] ="";
             $params["channelCard"] ="";
             $params["jine"] ="0";
             $params["regionId"] ="0";
             $Client = new HttpClient(JAVA_LOCATION);
             $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
             $pageContents = HttpClient::quickPost($url, $params);


             $this->_do_login($user_id);
             //从集客小店过来的
             if($returnUrl && $returnUrl=="register_for_jkxd"){
                 echo json_encode(array("flag"=>"jkxd"));
             //从购物车中过来的
             }elseif($returnUrl=="cart"){
                 echo json_encode(array("flag"=>"cart"));
             }else{
                 echo json_encode(array("flag"=>"ok"));
             }

             return;
         }
     }

    function fast_register(){
        $userName = trim($_POST['username']);
        $flag = $_POST['flag'];
        $member_model = & m('member');
        if($userName == ""){
            echo json_encode(array("flag"=>"null"));
            return;
        }
        if($flag == "false"){
            echo json_encode(array("flag"=>"error"));
            return;
        }
        $info = $member_model->get("(user_name = '$userName' or email = '$userName' or phone_mob = '$userName') and dropState = 1");
        if($info['user_id'] > 0){
            echo json_encode(array("flag"=>"no"));
            return;
        }else{
            $smsclient = new SMSClient();
            $code = $this -> generate_code(6);
            $_SESSION['mobile_register_code'] = $code;
            $content = "您正在进行手机快速注册操作，密码为：" . $code;
            $rs = $smsclient -> sendSMS($userName,$content);
            echo json_encode(array("flag"=>"ok" , "user_name"=>$userName));
            return;
        }
    }

    function get_password(){
        $userName = trim($_POST['username']);
        $smsclient = new SMSClient();
        $code = $this -> generate_code(6);
        $_SESSION['mobile_register_code'] = $code;
        $content = "您正在进行手机快速注册操作，密码为：" . $code;
        $rs = $smsclient -> sendSMS($userName,$content);
        echo json_encode(array("flag"=>"ok"));
        return;
    }

    function mobile_register(){
        $userName = trim($_POST['username']);
        $password = trim($_POST['password']);
        $code = $_SESSION['mobile_register_code'];
        if($password == ""){
            echo json_encode(array("flag"=>"null"));
            return;
        }
        if($password != $code){
            echo json_encode(array("flag"=>"error"));
            return;
        }
        $member_model = & m('member');
        $this->visitor->logout();
        $data = array('user_name' => $userName, 'password' => md5($password), 'member_type' => 1, 'dropState' => 1);
        $user_id = $member_model->add($data);
        $this->_do_login($user_id);
        echo json_encode(array("flag"=>"ok"));
        return;
    }

    function about(){
        $this -> display("about.html");
    }

    function updatePw_yanzheng(){
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
        $this -> display("modifypassword_yanzheng.html");
    }

    function register_form(){
        $t_id = $_GET['t_id'];
        if (!$t_id) $t_id = ecm_getcookie("t_id");
        if (!$t_id || $t_id == "" || $t_id == null) $t_id = ecm_getcookie("u_id");
        $this -> assign("t_id",$t_id);
        $member_model = & m('member');
        if($t_id && $t_id > 0){
            $t_member = $member_model->get("user_id=".$t_id);
            $this -> assign("t_member",$t_member);
        }
        $this -> assign("ret_url",$_GET['ret_url']);
        $this -> assign("returnUrl",$_GET['returnUrl']);
        $this -> assign("referer",$_SERVER['HTTP_REFERER']);
        $this -> display("register.html");
    }

    function my_url() {
        $u_id = $_GET['u_id'];
        if ($u_id) {
            $this -> assign("userId",$u_id);
            $member_model = & m('member');
            $member = $member_model->get("user_id=".$u_id);
            $this -> assign("c_member",$member);
            $this -> display("member.my_url.html");
        } else {
            $userId = $this -> visitor ->get("user_id");
            $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
            $ret_url = urlencode($ret_url);
            if (!$userId || !($userId > 0)) header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
            else {
                if (!$_GET['u_id']) header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=my_url&u_id=" . $userId);
            }
        }
    }

    //我要抽奖
    function wycj(){
        $this->isLogin();
        header("Location: ". SITE_URL . "/dajike-account/mobile_cj.jsp");
    }



}
?>
