<?php
//pos机
class PosApp extends BackendApp{
    var $_posbindbankcard_mob;
    var $_posapply_mob;
    var $_posrecord_mob;
    function __construct()
    {
        $this->PosApp();
        $this->_posbindbankcard_mob =& m('posbindbankcard');
        $this->_posapply_mob =& m('posapply');
        $this->_posrecord_mob =& m('posrecord');
    }
   function PosApp(){
        parent::BackendApp();
   }

    //pos机申请查询
    function apply(){
        $conditions="1=1 ";

        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(pos_apply.create_time,'%Y-%m-%d')>='$add_time_from'";
        }
        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(pos_apply.create_time,'%Y-%m-%d')<='$add_time_to'";
        }

        if($_GET["seller_name"]){
            $conditions.=" and pos_apply.seller_name='".$_GET["seller_name"]."'";
        }

        if($_GET["store_name"]){
            $conditions.=" and pos_apply.store_name='".$_GET["store_name"]."'";
        }

        $page = $this->_get_page();
        $posapply     = $this->_posapply_mob->find(array(
            'conditions'    => $conditions." order by pos_apply.create_time desc",
            'fields'=> "this.*,(select member.user_name from ecm_member member where member.user_id = pos_apply.user_id) as user_name",
            'limit' => $page['limit'],
            'count' => true,
        ));
        $page['item_count'] = $this->_posapply_mob->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data', $posapply);
        $this->display('pos.apply.html');
    }

    function record(){
        $conditions="1=1 ";

        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(pos_record.xiaofei_time,'%Y-%m-%d')>='$add_time_from'";
        }
        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(pos_record.xiaofei_time,'%Y-%m-%d')<='$add_time_to'";
        }

        if($_GET["card_num"]){
            $conditions.=" and pos_record.bank_card='".$_GET["card_num"]."'";
        }

        if($_GET["card_bank"]){
            $conditions.=" and pos_record.bank_name='".$_GET["card_bank"]."'";
        }

        $page = $this->_get_page();
        $posrecord     = $this->_posrecord_mob->find(array(
            'conditions'    => $conditions." order by pos_record.create_time desc",
            'fields'=> "this.*,(select s.region_name from ecm_store s where s.pos_sn=pos_record.pos_sn limit 0,1) as region_name",
            'limit' => $page['limit'],
            'count' => true,
        ));

        $page['item_count'] = $this->_posrecord_mob->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data', $posrecord);
        $this->display('pos.record.html');
    }

    function shenhe(){
        if(!$_POST){
            $data = $this->_posapply_mob->get_info($_GET["id"]);
            $this->assign('data', $data);
            $this->display('pos.shenhe.html');
        }else{
            $status =  $_POST["status"];
            $id =  $_POST["id"];
            $unpass_reason =  $_POST["unpass_reason"];
            if($id == "" || $status == ""){
                echo "<script>alert('参数错误');</script>";
                exit;
            }
            $data["status"] = $status;
            if($unpass_reason !=""){
                $data["unpass_reason"] = $unpass_reason;
            }

            if($status == 1){
                $data["unpass_reason"] = "";
            }

            $this->_posapply_mob->edit($id,$data);
            $this->pop_warning('ok');
        }

    }

}

?>