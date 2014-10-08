<?php
/* 集客小店——会员中心控制器 */
class JkxdApp extends MemberbaseApp
{
    var $_member_mod;
    var $_my_order_mod;
    var $t_app;
    var $order_app;
    var $jifenlog_mod;
    var $yuelog_mod;
    var $_user_id;
    var $user;
    function __construct(){
        $this->JkxdApp();
    }
    function JkxdApp(){
        parent::__construct();
        $this->_member_mod =& m('member');
        $this->_my_order_mod =& m('order');
        $this->jifenlog_mod =& m('jifenlog');
        $this->yuelog_mod =& m('yuelog');
        $this->_user_id= $this->visitor->get('user_id');
        $this->user = $this->_member_mod->get_info($this->visitor->get('user_id'));
        include_once(ROOT_PATH . '/app/tuiguang.app.php');
        include_once(ROOT_PATH . '/app/buyer_order.app.php');
        $this->t_app = new TuiguangApp();
        $this->order_app =new Buyer_orderApp();
        $this->assign('user', $this->_member_mod->get_info($this->visitor->get('user_id')));
        $this->assign("jkxd_site_url",$this->changeSiteUrl());
    }
    /**
     * 生成集客小店专属域名
     * @return string
     * @author liz
     */
    function changeSiteUrl(){
        $url = site_url();
        if($url==null || $url =="") return $url;

        $id = $_GET['id'] ? $_GET['id'] :$_POST['id'];
        if(!$id || $id==null|| $id==""){
            $user = $this->user;
            if($user && $user['user_id'] > 0 && $user['spreader_type'] ==1 && $user['shop_name'] != null && $user['shop_name'] !="" ){
                $id = $user['user_id'];
            }else{
                return $url;
            }
        }

        $paramArray=explode(".",$url);
        if(!$paramArray || count($paramArray) < 3 ) return $url;

        $site_url = $paramArray[0].".$id.".$paramArray[1].".".$paramArray[2];
        return $site_url;
    }




    function index(){
        $this->_curitem('jkxd_index');
//        $this->display("shop_2.html");
        $this->display("jkxd.index.html");
    }

    //我推荐的商家
    function jkxd_store(){
        $this->t_app->tuiguang_store();
    }

    //我推荐的商家订单
    function jkxd_order(){
        $this->order_app->index();
    }

    function get_jine(){
        $conditions=" and yue_log.type=6 and yue_log.yue_type=1 and yue_log.beizhu LIKE '%集客小店%'";
        $buyer_name = $_GET["buyer_name"];
        $seller_name = $_GET["seller_name"];

        if($buyer_name != ""){
            $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =yue_log.order_sn and  ord.buyer_name ='$buyer_name')";
        }

        if($seller_name != ""){
            $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =yue_log.order_sn and  ord.seller_name ='$seller_name')";
        }

        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["order_sn"] != ""){
            $order_sn=$_GET["order_sn"];
            $conditions.=" and order_sn='$order_sn'";
        }


        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')<='$add_time_to'";
        }
        $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =yue_log.order_sn and ord.type='shop' and ord.shop_id=".$this->_user_id.")";
        $page = $this->_get_page();
        $yuelog=$this->yuelog_mod->find(array(
            'conditions' => " user_id =".$this->_user_id.$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        foreach($yuelog as $k=>$v){
            $yuelog[$k]["order_info"]=$this->_my_order_mod->get(array(
                "conditions"=> "order_sn='".$v["order_sn"]."'",
                "fields"    =>  "this.*,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.seller_id) AS \"t_id_seller\",
                                     (SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.buyer_id) AS \"t_id_buyer\",
                                     (SELECT COUNT(1) FROM ecm_member m1, ecm_store s1 WHERE s1.`fwzx` >0 and s1.tuoguan=1 AND m1.`user_id`=s1.`fwzx` AND m1.`member_type`='04' and s1.store_id=order_alias.seller_id) as \"if_djk\" "
            ));
        }

        $page['item_count'] = $this->yuelog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('shouyi', $yuelog);
    }

    function get_jifen(){
        $buyer_name = $_GET["buyer_name"];
        $seller_name = $_GET["seller_name"];
        $conditions=" and jifen_log.type in (6,8)";
        if($buyer_name != ""){
            $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =jifen_log.order_sn and  ord.buyer_name ='$buyer_name')";
        }

        if($seller_name != ""){
            $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =jifen_log.order_sn and  ord.seller_name ='$seller_name')";
        }

        if($_GET["order_sn"] != ""){
            $order_sn=$_GET["order_sn"];
            $conditions.=" and order_sn='$order_sn'";
        }

        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(jifen_log.create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(jifen_log.create_time,'%Y-%m-%d')<='$add_time_to'";
        }
        $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =jifen_log.order_sn and ord.shop_id=".$this->_user_id.")";
        $page = $this->_get_page();
        $jifenlog=$this->jifenlog_mod->find(array(
            'conditions' => " user_id =".$this->_user_id.$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        foreach($jifenlog as $k=>$v){
            $jifenlog[$k]["order_info"]=$this->_my_order_mod->get(array(
                "conditions"=> "order_sn='".$v["order_sn"]."'",
                "fields"    =>  "this.*,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.seller_id) AS \"t_id_seller\",
                                     (SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.buyer_id) AS \"t_id_buyer\",
                                     (SELECT COUNT(1) FROM ecm_member m1, ecm_store s1 WHERE s1.`fwzx` >0 and s1.tuoguan=1 AND m1.`user_id`=s1.`fwzx` AND m1.`member_type`='04' and s1.store_id=order_alias.seller_id) as \"if_djk\" "
            ));
        }

        $page['item_count'] = $this->jifenlog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('shouyi', $jifenlog);
    }

    //佣金管理
    function my_yongjing(){

        $page = $this->_get_page();
        $user_info = $this->visitor->get();

        if(!$_GET["orderBy"] || $_GET["orderBy"] == "jine"){
            $this->get_jine();         //获得金额收入
        }else{
            $this->get_jifen();        //获得积分收入
        }

        $this->assign('jifen_shouyi', $this->jifenlog_mod->getOne("SELECT SUM(jifen_log.jifen) FROM ecm_jifen_log jifen_log WHERE jifen_log.user_id  = ".$this->_user_id." AND jifen_log.TYPE in (6,8)
        and EXISTS (select 1 from ecm_order ord where ord.order_sn =jifen_log.order_sn and ord.shop_id=".$this->_user_id.")"));

        $this->assign('yue_shouyi', $this->yuelog_mod->getOne("SELECT SUM(yue_log.jine) FROM ecm_yue_log yue_log WHERE yue_log.user_id  = ".$this->_user_id." and yue_type=1  AND yue_log.TYPE =6 and yue_log.beizhu LIKE '%集客小店%'"));

        $this->_config_seo('title', "我的收益" . ' - ' . Lang::get('member_center'));
        $this->_curmenu('shouyi');
        $this->display('jkxd.my_yongjing.html');
    }

    //一键分享
    function yjtg(){
        $url = site_url();

        if($url==null || $url =="") return $url;

        $id =$this->_user_id;

        if(!$id || $id==null|| $id==""){
            return $url;
        }

        $paramArray=explode(".",$url);

        if(!$paramArray || count($paramArray) < 3 ) return $url;

        $jkxd_site_url = $paramArray[0].".$id.".$paramArray[1].".".$paramArray[2];

        $this->assign("jkxd_site_url",$jkxd_site_url);
        $this->display("jkxd.yjtg.html");
    }

    //修改集客小店名称
    function sava_shop_name(){
        $user_id = $this->visitor->get('user_id');
        $shop_name = $_POST["shop_name"];
        if($user_id>0 || $shop_name!=""){
            $this->_member_mod->edit($user_id,array("shop_name"=>$shop_name));
            echo "【修改成功！】";
        } else {
            echo "【系统繁忙，请稍后重试！】";
        }
    }

}

?>