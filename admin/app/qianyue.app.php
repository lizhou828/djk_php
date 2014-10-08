<?php

/* 签约 */
class QianyueApp extends BackendApp
{
    var $memberqianyue_mod;

    function __construct(){
        $this->TiXianApp();
    }

    function TiXianApp(){
        parent::__construct();
        $this->memberqianyue_mod =& m('memberqianyue');
    }
    function index(){
        $page = $this->_get_page();
        $conditions = "1=1 ";
        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and member_qianyue.add_time>='$add_time_from'";
        }
        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and date_format(member_qianyue.add_time,'%Y-%m-%d')<='$add_time_to'";
        }
        if($_GET["user_name"]){
            $conditions.=" and member_qianyue.user_name like '%".$_GET["user_name"]."%'";
        }
        if($_GET["status"] != ""){
            $conditions.=" and member_qianyue.status =".$_GET["status"];
        }
        $qianyue=$this->memberqianyue_mod->find(array(
            'conditions' =>$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'add_time DESC',
            'limit' => $page['limit'],
        ));
        $page['item_count'] = $this->memberqianyue_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data',   $qianyue);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('qianyue.html');
    }

    function view(){
        if(!$_GET["id"]){
            $this->show_warning("请选择签约信息");
            exit;
        }
        $data = $this->memberqianyue_mod->get_info($_GET["id"]);
        $this->assign('data',   $data);
        $this->display('qianyue.view.html');
    }

    function shenhe(){
        if(!$_GET["id"]){
            $this->show_warning("请选择签约信息");
            exit;
        }

        date_default_timezone_set('Asia/Shanghai');
        $update_time = date('Y-m-d H:i:s');

        $rs = $this->memberqianyue_mod->edit($_GET["id"],array("status"=>$_GET["status"],"update_time"=>$update_time));

        $spreader_type = 0;
        if($_GET["status"] == 1){
            $spreader_type =1;
        }
        $user_mod =& m('member');
        $user_mod -> edit($_GET["id"],array("spreader_type"=>$spreader_type));

        $this->memberqianyue_mod->commit_transaction();

        if($rs>0){
            $this->show_message('操作成功',
                'back_list',    "index.php?app=qianyue"
            );
            exit;
        }
        $this->show_warning($this->memberqianyue_mod->get_error());
    }



}

?>
