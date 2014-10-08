<?php
//结算银行卡
class MemberbankApp extends BackendApp{

   var $memberbank_mod;
    var $member_mod;
    function __construct(){
        $this->MemberbankApp();
    }
   function MemberbankApp(){
        parent::BackendApp();
        $this->memberbank_mod =  & m('memberbank');
        $this->member_mod =  & m('member');
   }
   function index(){
        $page = $this->_get_page();

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

        $conditions=" 1=1 ";

        if($_GET["kahao"]){
            $conditions.=" and member_bank.kahao='".$_GET["kahao"]."'";
        }

       if($_GET["user_name"]){
           $conditions.=" and member_bank.user_name='".$_GET["user_name"]."'";
       }

       if($_GET["user"]){
           $conditions.=" and EXISTS(select 1 from ecm_member m where m.user_id =member_bank.user_id and m.user_name='".$_GET["user"]."' )";
       }

        $memberbank = $this->memberbank_mod->find(array(
                                  "conditions" =>$conditions,
                                  "fields"      =>"this.*",
                                  'limit' => $page['limit'],
                                  'count' => true,
                                  'order' => "$sort $order"
        ));
        $page['item_count'] = $this->memberbank_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info',$page);
        $this->assign('data',$memberbank);
        $this->display('member_bank.index.html');
   }

   function info(){
       if(empty($_GET["id"]) || $_GET["id"] == ""){
           $this->show_warning("没有找到数据");
           exit;
       }
       $memberbank = $this->memberbank_mod->get_info($_GET["id"]);
       $memberbank["userData"]=$this->member_mod->get_info($memberbank["user_id"]);
       $this->assign('data',$memberbank);
       $this->display('member_bank.info.html');
   }


   function set_default(){
       if(empty($_GET["id"]) || $_GET["id"] == "" || empty($_GET["uid"]) || $_GET["uid"] == "" || $_GET["uid"] <= 0){
           $this->show_warning("没有找到数据");
           exit;
       }
       $memberbank = $this->memberbank_mod->db_query("update ecm_member_bank set if_default=0 where user_id=".$_GET["uid"]);
       $memberbank = $this->memberbank_mod->edit($_GET["id"],array("if_default"=>1));
       header("Location: index.php?app=memberbank&act=info&id=".$_GET["id"]);

   }


}

?>