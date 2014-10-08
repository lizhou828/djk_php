<?php

/* 提现 */
class MsmApp extends BackendApp
{
    var  $_msm_mod;
    function __construct(){
        $this->MsmApp();
    }

    function MsmApp(){
        parent::__construct();
        $this->_msm_mod =& m('msm');
    }
    function index(){
            $conditions = "1=1 ";
//            if(isset($_GET["status"]) && $_GET["status"]>=0){
//                $status=$_GET["status"];
//                $conditions.=" and tixian.status=$status";
//            }
//            if($_GET["add_time_from"]){
//                $add_time_from=$_GET["add_time_from"];
//                $conditions.=" and tixian.add_time>='$add_time_from'";
//            }
//            if($_GET["add_time_to"]){
//                $add_time_to=$_GET["add_time_to"];
//                $conditions.=" and tixian.add_time<='$add_time_to'";
//            }

            $page   =   $this->_get_page(20);   //获取分页信息
            $msms=$this->_msm_mod->find(array(
                'conditions' =>$conditions,
                'fields'=> 'this.*',
                'count' => true,
                'order' => 'create_time DESC',
                'limit' => $page['limit'],
            ));
            $page['item_count'] = $this->_msm_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('msms', $msms);
            $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
            $this->display('msm.html');
        }

}


?>
