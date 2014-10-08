<?php
/**
 * Created by JetBrains PhpStorm.
 * User: liz
 * Date: 13-11-25
 * Time: 下午4:53
 * To change this template use File | Settings | File Templates.
 */
/**
 * 微商城-用户中心-结算中心(只针对于服务中心和商家)：包含积分、结算、余额、收益、提现等业务
 */
class Member_accountApp extends MallbaseApp{

    function index(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        $flag = $this->canTiXian($user['user_id']);

        $user['can_tixian'] = $flag==true ? "true" :"false";

        $tichengDetailMod = &m("tichengDetail");
        $jiesuan_type = "";
        //普通会员
        if($user['member_type']=='01'){
            //是否是签约业务员:
            $memberqianyue_mod =& m('memberqianyue');
            $qianyue = $memberqianyue_mod->get("user_id=".$userId);
            if($qianyue){
                $user['qianyue'] = $qianyue;
                $jiesuan_type=0; //金额
            }else{
                $jiesuan_type=1;  //积分
            }

            //是买家还是商家
            $store_mod = &m("store");
            $store = $store_mod->get("store_id =".$userId." and state=1 and dropState=1");
            if($store){
                $user['store'] = $store;
                $yye =$user_mod->getOne("SELECT SUM(jine) FROM ecm_yue_log WHERE user_id=".$user['user_id']." and type=4");
                $user["yye"]= ($yye!=null) ? $yye:0;
            }
        //服务中心
        }elseif($user['member_type'] =='02'){
            $jiesuan_type=0; //金额
        }

        $allShouyi = $tichengDetailMod->db->getOne("SELECT SUM(jine) FROM ecm_yue_log WHERE user_id  =".$userId."  AND type =6");
        $user['allShouyi'] = $allShouyi;
        //显示账户余额、收益、积分、抽奖权 （如果是有店铺的话营业额）
        $userFinance = $this->getMemberFinance($userId);
        $this->assign("userFinance",$userFinance);

        /*查看是否绑定银行卡*/
        $member_bank = & m('memberbank');
        $bank = $member_bank -> find('user_id = ' . $user['user_id']);
        $user['bank'] = count($bank);


        $this->assign("user",$user);
        $this->display("member_account.index.html");
    }


    //营业额
    function yye(){
        $userId = $this->visitor->get("user_id");
        $this->isLogin();
        $memberMod = &m("member");
        $user = $memberMod->get("user_id=".$userId);
        $state = $this->visitor->get('state');
        if($state==0){
            exit("只有商家才能查看营业额log");
        }
        $yyelog_mod =& m('yuelog');//营业额log表废弃， 营业额log要在余额log表里面找,type=4

        $page = $this->_get_page(50);
        $sql = " select y.* ,o.seller_name,o.buyer_name,o.TYPE".
               " from ecm_yue_log y left join ecm_order o on y.order_sn=o.order_sn".
               " where  y.type=4 and  y.user_id =".$user['user_id']." order by y.create_time DESC ";
        $sqlCount = "select count(*) from ecm_yue_log y left join ecm_order o on y.order_sn=o.order_sn".
            " where y.type=4  and y.user_id =".$user['user_id'];

        $sql .="limit ".$page['curr_page'].", ".$page['pageper'];
        $yyelogs = $yyelog_mod->getAll($sql);
        $page['item_count'] = $yyelog_mod->getOne($sqlCount);
        $this->_format_page($page);
        $this->assign('page', $page);
        $this->assign('yyelogs', $yyelogs);
        $this->display('member_account.yye.html');
    }

    //收益
    function shouyi(){
        $this->isLogin();
        $user_id = $this->visitor->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$user_id);
        $this->assign("user",$user);

        $conditions="";

        $type=$_GET["type"];
        if($type!="" && $type=="income"){
            $conditions.=" and  jine > 0 ";
        }else if($type=="pay"){
            $conditions.=" and jine < 0 ";
        }
        $this->assign("type",$type);


        $user_id = $this->visitor->get('user_id');
        $model_order =& m('order');
        $page = $this->_get_page();
        $user_info = $this->visitor->get();

        $yuelog_mod =& m('yuelog');
        $conditions .=" and yue_type=1";//收益账户（扣税账户）

        if($_GET['shop_type']=='jkxd'){
            $conditions .=" and type=6 and beizhu like '%集客小店%'";
            $this->assign("shop_type",$_GET['shop_type']);
        }

        $page = $this->_get_page();
        $yuelog=$yuelog_mod->find(array(
            'conditions' => " user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));


        foreach($yuelog as $k=>$v){
            $yuelog[$k]["order_info"]=$model_order->get(array(
                "conditions"=> "order_sn='".$v["order_sn"]."'",
                "fields"    =>  "this.*,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.seller_id) AS \"t_id_seller\",
                                                       (SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.buyer_id) AS \"t_id_buyer\""
            ));
        }


        $page['item_count'] = $yuelog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('shouyi', $yuelog);



        $userFinance = $this->getMemberFinance($user_id);
        $this->assign('userFinance', $userFinance);

        $flag = $this->canTiXian($user['user_id']);
        $this->assign("flag",$flag);
        $this->display("member_account.shouyi.html");

    }

    //普通会员的收益
    function memberShouyi($user_id){
        $this->isLogin();
        $conditions = $user_id  ? " user_id=".$user_id : "";
        $model_order =& m('order');
        //是否结算成功
        $status=intval($_GET["status"]);
        $conditions.=" and status=".$status;
        $this->assign("status",$status);

        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$user_id ." and status =2 and dropState =1");
        if(!$user || $user['member_type'] != '01'){
            exit("非法用户！");
        }
        $jiesuanType="";
        //是否签约业务员
        $memberqianyue_mod =& m('memberqianyue');
        $qianyue = $memberqianyue_mod->get("user_id=".$user_id);
        $this->assign("qianyue",$qianyue);
        $tichengDetailMod = &m("tichengDetail");

        if($qianyue){
            $jiesuanType=0;
            $user['qianyue'] = $qianyue;
            $allShouyi = $tichengDetailMod->db->getOne("SELECT SUM(jine) FROM ecm_ticheng_detail WHERE user_id  = $user_id AND jiesuan_type = $jiesuanType");
            $successShouyi = $tichengDetailMod->db->getOne("SELECT SUM(jine) FROM ecm_ticheng_detail WHERE user_id  = $user_id AND jiesuan_type = $jiesuanType and status=1");
            $user['allShouyi'] = $allShouyi;
            $user['successShouyi'] = $successShouyi;
        }else{
            $jiesuanType=1;
            $allJifen = $tichengDetailMod->db->getOne("SELECT SUM(jine) FROM ecm_ticheng_detail WHERE user_id  = $user_id AND jiesuan_type =$jiesuanType");
            $user['allJifen'] = $allJifen;
        }
        $conditions.=" and jiesuan_type=".$jiesuanType;

        $page = $this->_get_page(20);
        $tichengDetails=$tichengDetailMod->find(array(
            'conditions' => $conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        //获取相应的订单信息
        $model_order =& m('order');
        foreach($tichengDetails as $k=>$v){
            $tichengDetails[$k]["order_info"]=$model_order->get(array(
                "conditions"=> "order_sn='".$v["order_sn"]."'",
                "fields"    =>  "this.*,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.seller_id) AS \"t_id_seller\",
                                                       (SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.buyer_id) AS \"t_id_buyer\""
            ));
            $sql = "SELECT SUM(jine) FROM ecm_ticheng_detail WHERE user_id  = $user_id and order_sn='".$v["order_sn"]."'  and jiesuan_type=$jiesuanType ";
            //1表示结算成功，0表示未结算
            if($status==1 || $status==0){
                $sql.="and status=".$status;
            }
            $tichengDetails[$k]["order_all_shouyi"]=$model_order->db->getOne($sql);
        }

        $page['item_count'] = $tichengDetailMod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('user', $user);
        $this->assign('tichengDetails', $tichengDetails);
        $this->display("member_account.shouyi.html");
    }




    //服务中心的收益
    function serviceCenterShouyi($user_id){
        $this->isLogin();
        if(empty($user_id)){
            exit("你没有权限查看服务中心的相关信息!");
        }
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId ." and status =2 and dropState =1");
        if(!$user || $user['member_type'] == '01'){
            exit("非法用户！");
        }

        //是否结算成功
        $status=intval($_GET["status"]);
        $conditions=" and status=".$status;
        $this->assign("status",$status);
//
//        //查找服务中心子账户
//        $users=$user_mod->find(array(
//            "conditions"=>"member_type='03' and region_id=".$user["region_id"],
//            "fields"    =>"user_id,user_name"
//        ));
//        $query_conditions="store.fwzx=".$user['user_id'];
//        if(count($users)>0 && $user["member_type"]=="02"){
//            foreach($users as $k=>$v){
//                    $query_conditions.=" or store.fwzx=".$v["user_id"]; //获得子账号下面的商家
//            }
//        }
//
//        $order_mod =& m('order');
//        //托管商家yye
//        $tuoguan_yyy_sql = "SELECT SUM(ord.order_amount-ord.jine) FROM ecm_order ord,ecm_store store where
//                            ord.status=40 and ord.seller_id = store.store_id and store.tuoguan =1 and ($query_conditions)";
//        $tuoguan_shouyi = $order_mod->getOne($tuoguan_yyy_sql);
//        //本区营业额
//        $benqu_yyy_sql = "SELECT SUM(ord.order_amount-ord.jine) FROM ecm_order ord,ecm_store store where
//                             ord.status=40 and ord.seller_id = store.store_id and store.region_id =".$user["region_id"];


        $fwzxId=$user['user_id'];
        $pageNo = ($_GET["pageNo"]?$_GET["pageNo"]:1);
        $params["userId"] = $fwzxId;
        $pageNo  =1;
        if(!empty($_GET["page"]) && $_GET["page"]!=""){
            $pageNo = $_GET["page"];
        }
        $page_size = 20;
        $params["pageSize"] = $page_size;
        $params["pageNo"] = $pageNo;
        $queryType = ($_GET["orderBy"] == "server_jiesuan" ? "server_jiesuan" : "server_shouyi");

        if(!empty($_GET["orderBy"]) && $_GET["orderBy"]!=""){
            $queryType = $_GET["orderBy"];
        }

        $params["type"] = $queryType;
        $Client = new HttpClient(JAVA_LOCATION);
        $url = "http://".JAVA_LOCATION.SERVICE_SHOUYI;
        $pageContents = HttpClient::quickPost($url, $params);
        $rs_data = json_decode($pageContents);
//        var_dump($pageContents);
//        var_dump($rs_data);exit;


        $data = null;

        if(count($rs_data)>0){
            $tmp = $rs_data[0];
            $page = $this->_get_page($page_size);
            $page['item_count'] = $tmp->totalPageNum;
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('total_money', $tmp->totalGain);
            foreach($rs_data as $k=>$v){
                $data[$k] = array("buyerName"=>$v->buyerName,
                    "jiesuanStatus"=>$v->jiesuanStatus,
                    "jine"=>$v->jine,
                    "orderAmount"=>$v->orderAmount,
                    "orderDate"=>$v->orderDate,
                    "orderSn"=>$v->orderSn,
                    "sellerName"=>$v->sellerName,
                    "ticheng"=>$v->ticheng,
                    "yongjin"=>$v->yongjin,
                    "type"=>$v->type,
                    "channelName"=>$v->channelName,
                    "channelCard"=>$v->channelCard,
                    "jiesuanDate"=>$v->jiesuanDate
                );
            }
        }

        $this->assign('shouyi', $data);
        $this->display("member_account.shouyi_service.html");
    }



    //提现明细
    function tixian_detail(){
        $user = $this->visitor->get();
        $this->isLogin();

        $page = $this->_get_page();
        $yueType = $_GET['yue_type'];
        if($yueType){
            $conditions = " and yue_log.yue_type=".$yueType;
        }

        $sql = "select DISTINCT yue_log.*,tixian.* from ecm_yue_log yue_log left join ecm_tixian tixian  on yue_log.user_id = tixian.user_id where yue_log.user_id =".$user['user_id'].$conditions." and yue_log.type=3 order by yue_log.create_time";
//        $this->pr($sql);exit;
        $sqlCount = " select count(*) from ecm_yue_log yue_log left join ecm_tixian tixian  on yue_log.user_id = tixian.user_id where yue_log.user_id =".$user['user_id'].$conditions." and yue_log.type=3";
        $recommendMod = &m("recommend");
        $tixian = $recommendMod->goodsList($sql,$page['curr_page'],$page['pageper']);
        $count = $recommendMod->resultCount($sqlCount);
        $page['item_count']=$count;
//             $this->pr($page);exit;
        $this->_format_page($page);

        $this->assign('page', $page);
        $this->assign('tixianLogs', $tixian);
        $this->display("member_account.tixian_detail.html");
    }

    //余额
    function yue(){
        $user = $this->visitor->get();
        $this->isLogin();
        $yuelog_mod =& m('yuelog');

        $conditions="";
        $type=$_GET["type"];
        if($type!="" && $type=="income"){
            $conditions.=" and  jine > 0 ";
        }else if($type=="pay"){
            $conditions.=" and jine < 0 ";
        }
        $conditions.=" and yue_type=0";

        $this->assign("type",$type);
        $page = $this->_get_page(30);
        $yuelog=$yuelog_mod->find(array(
            'conditions' => " user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        //yue_log:
        //type = -1未知,0抽奖,1平均,2买家退款，3提现，4完成订单打入商家账户,5支付,6收益或奖励
        $page['item_count'] = $yuelog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('yuelogs', $yuelog);

        $flag = $this->canTiXian($user['user_id']);
        $this->assign("flag",$flag);

        $userFinance = $this->getMemberFinance($user['user_id']);
        $this->assign('userFinance', $userFinance);

        $this->display('member_account.yue.html');
    }

//    积分查看
    function jifen(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        //显示账户余额、积分、抽奖券 （如果是有店铺的话，显示余额、营业额）
        $userFinance = $this->getMemberFinance($user['user_id']);
        $this->assign("userFinance",$userFinance);

        $type=$_GET["type"];
        $condition = "";
        if($type =="income"){
            $condition.= " and jifen > 0";
        }elseif($type =="pay"){
            $condition.= " and jifen < 0";
        }

        $jifenlog_mod =& m('jifenlog');
        $page = $this->_get_page(30);
        $jifenlog=$jifenlog_mod->find(array(
            'conditions' => " user_id =".$user['user_id'].$condition,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        $page['item_count'] = $jifenlog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('jifenlog', $jifenlog);
        $this->assign('type',$type);
        $this->display("member_account.jifen.html");
    }


    //结算明细(暂停查看)
    function jiesuan(){
        $user_id = $this->visitor->get("user_id");
        $this->isLogin();
        $state = $this->visitor->get('state');
        if($state==0){
            exit("只有商家才能查看结算明细log");
        }
        $conditions="";
        $endTime = time();
        $startTime = strtotime("-3 months");
        $conditions.=" and DATE_FORMAT(jiesuan.add_time,'%Y-%m-%d %H-%i-%s')>='$startTime'";
        $conditions.=" and DATE_FORMAT(jiesuan.add_time,'%Y-%m-%d %H-%i-%s')<='$endTime'";
        $jiesuan_mod=&m("jiesuan");
        $page = $this->_get_page();
        $jiesuans=$jiesuan_mod->find(array(
            'conditions' => " user_id =".$user_id.$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'add_time DESC',
            'limit' => $page['limit'],
        ));

        $page['item_count'] = $jiesuan_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('jiesuans', $jiesuans);
        $this->display("member_account.jiesuan.html");
    }




    function chooseBankCard(){
        $this->isLogin();
        $user_id = $this -> visitor ->get("user_id");
        $memberBank_mod = &m("memberbank");
        $bankCards = $memberBank_mod->find('user_id='.$user_id." and dropState=1");
        $this->assign("bankCards",$bankCards);
        $this->display("member_account.choose_bankcard.html");
    }

    //设置结算帐号
    function setJiesuanCard(){
        $this->isLogin();
        $user_id = $this -> visitor ->get("user_id");
        $id = $_GET['id'];
        $memberBank_mod = &m("memberbank");
        $member_mod = &m("member");
        $member=$member_mod->get("user_id=".$user_id);

        $memberBank_mod->edit('user_id='.$user_id,array("if_default"=>0));
        $bankCard =$memberBank_mod->get("id=".$id);
        if(!$bankCard){
            $this->json_result(1,"没有这个银行卡！");
            exit;
        }
        $memberBank_mod->edit("id=".$id,array("if_default"=>1));
        $this->json_result(0,"设置结算帐号成功！");

    }


    //页面：提取余额到银行卡
    function tixianPage(){
        $this->isLogin();
        $user_mod = &m("member");
        $userId = $this -> visitor ->get("user_id");
        $user = $user_mod->get('user_id='.$userId);
        $flag = $this->canTiXian($user['user_id']);
        if(!$flag){
            ecm_json_encode("没有提取余额到银行卡的权限");
        }

        $tixianCardId =  $_GET['tixianCardId'];
        if($tixianCardId){
            $tixian_mod = &m("tixian");
            $tixianCard = $tixian_mod->get("id=".$tixianCardId);
            $this->assign("tixianCard",$tixianCard);
        }
        $channel_mod =& m('channel');
        $channels= $channel_mod->find("status=1 and (type=2 or type=3)");

        $this->assign("channels",$channels);

        $this->assign("user",$user);
        $this->assign("type",$_GET['type']);
        $this->display("member_account.tixianPage.html");
    }
    //提取余额到银行卡
    function tixian(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $bank_mod =& m('memberbank');
        $user = $user_mod->get('user_id='.$userId);
        $member_finance = $this->getMemberFinance($user['user_id']);
        $flag = $this->canTiXian($userId);


        header("Content-Type:text/html;charset=utf-8");
        if(!$flag){
            echo "<script>alert('没有提取余额到银行卡的权限！');window.location.href = document.referrer; </script>";
            exit;
        }

        $init_code = $_SESSION["yanzhengma"];
        $code = $_GET["verifyCode"]?$_GET["verifyCode"]:$_POST["verifyCode"];
        if ($code != $init_code) {
            echo "<script>alert('验证码错误！');history.back()</script>";
            exit;
        }

        $password2 = $_POST['password2'];
        if(md5($password2) != $user['password2']){
            echo "<script>alert('支付密码错误！');history.back()</script>";
            exit;
        }

        if($_POST['type']==0){
            if( $_POST['jine'] > $member_finance['unkoushui_yue']){
                echo "<script>alert('没有足够的余额！');history.back()</script>";
                exit;
            }
        }elseif($_POST['type']==1){
            if( $_POST['jine'] > $member_finance['koushui_yue']){
                echo "<script>alert('没有足够的余额！');history.back()</script>";
                exit;
            }
        }else{
            echo "<script>alert('提现链接错误！'); history.back()</script>";
            exit;
        }

        $params["type"] = "tixian";
        $params["userId"] =$user['user_id'];
        $params["userName"] =$_POST["user_name"];
        $params["channelCode"] =$_POST["channel_code"];
        $params["channelName"] =$_POST["channel_name"];
        $params["channelCard"] =$_POST["channel_card"];
        $params["jine"] = $_POST['jine'];
        $params["orderSn"] ="";
        $params["tId"] ="0";
        $params["regionId"] ="0";
        $params["tixianType"] = $_POST['type'];

        $Client = new HttpClient(JAVA_LOCATION);
        $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;

        $pageContents = HttpClient::quickPost($url, $params);
        //echo $pageContents;
        $data = json_decode($pageContents);


        if(!$data ||  $data->code != 200 ){
            echo $pageContents;
            echo "<script>alert('提现申请失败，服务器繁忙，请稍后重试【".$data->code."】！'); history.back()</script>";
            $this->pop_warning('ok','tixian_page');
        }else{
            echo "<script>alert('提现申请成功！');</script>";
            $mybans=$bank_mod->find("user_id=".$user['user_id']." and user_name='".$_POST["user_name"]."' and  bank_code='".$_POST["channel_code"]."' and kahao='".$_POST["channel_card"]."'");

            if(count($mybans)==0){
                $bank_mod->add(array(
                    "user_id"=>$user['user_id'],
                    "user_name"=>$_POST["user_name"],
                    "bank_name"=>$_POST["channel_name"],
                    "bank_code"=>$_POST["channel_code"],
                    "kahao"=>$_POST["channel_card"],
                    "add_time"=>gmtime()
                ));
            }
            $this->display("member_account.tixian_success.html");
        }
    }


    function checkCode(){
        $password2  = $_GET['password2'];
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        if($user['password2'] != md5($password2)){
            $this->json_result(-1,"支付密码错误！");
            exit;
        }

        $init_code = $_SESSION["yanzhengma"];
        $code = $_GET["verifyCode"]?$_GET["verifyCode"]:$_POST["verifyCode"];

        if ($code != $init_code) {
            $this->json_result(-1,"验证码错误！");
            exit;
        } else {
           $this->json_result(0,"验证码正确！");
        }
    }

    //查询历史提现卡
    function historyTixianCards(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $tixian_mod = &m("tixian");
        $tixianCards = $tixian_mod->find("user_id=".$user['user_id']);
        if($tixianCards ){
            $this->assign("tixianCards",$tixianCards);
            $this->assign("countCard",count($tixianCards));
        }
        $this->display("member_account.tixian_historyCards.html");

    }


    //判断是否可以提现(公用的方法)
    function canTiXian($userId){
        $flag = false;
        if($userId == null || $userId=="" || $userId < 0){
            $flag = false;
        }
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        if(!$user){
            $flag = false;
        }elseif($user['member_type']=='01'){
            $flag = true;
        }else{
            $flag = false;
        }
        return $flag;
    }


    /*根据服务中心账户，找到所有托管的商家
     *
     * @return 商家id的数组，若没有，则返回空数组
     */
    function findTrusteeshipStore(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $store_mod = &m("store");
        $user = $user_mod->get('user_id='.$userId);
        if(!$user || $user['member_type'] != '02'){
            echo "您无权查看相关信息!";
            exit;
        }
        //查找服务中心子账户
        $users=$user_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$user["region_id"],
            "fields"    =>"user_id,user_name"
        ));
        $serviceCenters = array($user['user_id']);
        if(count($users)>0){
            foreach($users as $k=>$v){
                array_push($serviceCenters,$v["user_id"]);
            }
        }
        //查找所有托管店铺
        $trusteeship_store = $store_mod->find(array(
            "conditions"=>"tuoguan=1 and fwzx ".db_create_in($serviceCenters),
            "fields"    =>"store_id,fwzx"
        ));
        $storeArray = array();
        if($trusteeship_store && count($trusteeship_store) > 0){
            foreach($trusteeship_store as $k=>$v){
                array_push($storeArray,$v['store_id']);
            }
        }
        return $storeArray;
    }



    //显示全部的商家
    function show_all_store(){
        $conditions=" 1=1 and member.dropState=1 and s.dropState=1 ";
        $user = $this->visitor->get();
        $userInfo=$this->_member_mod->get($user['user_id']);

        if($userInfo["member_type"] != "04" ||  $userInfo["user_name"] !="djk11001" ){
            exit("权限错误!");
        }

        $filter = $this->_get_query_conditions(array(
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'sgrade',
            ),
        ));

        $owner_name = trim($_GET['owner_name']);
        if ($owner_name)
        {
            $filter .= " AND (user_name LIKE '%{$owner_name}%' OR owner_name LIKE '%{$owner_name}%' OR store_name LIKE '%{$owner_name}%') ";
        }
        if($_GET["add_time_from"])
        {
            $add_time_from=strtotime($_GET["add_time_from"]);
            $filter .= " AND add_time>='$add_time_from'";
        }
        if($_GET["add_time_to"])
        {
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            $filter .= " AND add_time<='$add_time_to'";
        }
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])){
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))){
                $sort  = 'sort_order';
                $order = '';
            }
        }
        else{
            $sort  = 'add_time';
            $order = 'desc';
        }

        $this->assign('filter', $filter);
        $conditions .= $filter;
        $page = $this->_get_page();

        $stores = $this->_store_mod->find(array(
            'conditions' => $conditions,
            'join'  => 'belongs_to_user',
            'fields'=> "this.*,
                      member.user_name,
                      (select count(1) from ".DB_PREFIX."goods g  where g.store_id = s.store_id and g.dropState=1)  as goodsCount,
                      (select count(1) from ".DB_PREFIX."order ord  where ord.seller_id = s.store_id) as orderCount,
                      (select user_name from ".DB_PREFIX."member m where  m.user_id=s.fwzx and m.dropState=1) as service_user",
            'limit' => $page['limit'],
            'count' => true,
            'order' => "$sort $order"
        ));
        $sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $this->assign('sgrades', $grades);

        $states = array(
            STORE_APPLYING  => LANG::get('wait_verify'),
            STORE_OPEN      => "开启",
            STORE_CLOSED    => "关闭",
        );
        foreach ($stores as $key => $store){
            $stores[$key]['sgrade'] = $grades[$store['sgrade']];
            $stores[$key]['state'] = $states[$store['state']];
            $certs = empty($store['certification']) ? array() : explode(',', $store['certification']);
            for ($i = 0; $i < count($certs); $i++){
                $certs[$i] = Lang::get($certs[$i]);
            }
            $stores[$key]['certification'] = join('<br />', $certs);
        }
        $this->assign('stores', $stores);
        $page['item_count'] = $this->_store_mod->getCount();
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->_config_seo('title', "商家管理" . ' - ' . Lang::get('member_center'));
        $this->display('service.index.html');
    }


}