<?php

/* 提现 */
class TiXianApp extends BackendApp
{
    var $_tixian_mod;

    function __construct(){
        $this->TiXianApp();
    }

    function TiXianApp(){
        parent::__construct();
        $this->_tixian_mod =& m('tixian');;
    }
    function index(){
        //是否导出
        $exportFlag=$_GET["exportFlag"];

        if($exportFlag<1){
                    $page = $this->_get_page();
                    $conditions = "1=1 ";
                    if(!empty($_GET["user_name"]) && $_GET["user_name"]!="") {
                        $user_name=$_GET["user_name"];
                        $conditions.=" and EXISTS(select 1 from ".DB_PREFIX."member m where m.user_id=tixian.user_id and m.user_name like '%$user_name%')";
                    }
                    if(isset($_GET["status"]) && $_GET["status"]>=0){
                        $status=$_GET["status"];
                        $conditions.=" and tixian.status=$status";
                    }
                    if($_GET["add_time_from"]){
                        $add_time_from=$_GET["add_time_from"];
                        $conditions.=" and tixian.add_time>='$add_time_from'";
                    }
                    if($_GET["add_time_to"]){
                        $add_time_to=$_GET["add_time_to"];
                        $conditions.=" and tixian.add_time<='$add_time_to'";
                    }

                    if(isset($_GET["user_type"]) && $_GET["user_type"]>0){
                        $user_type=$_GET["user_type"];
                        if($user_type==1){
                            $conditions.=" and not EXISTS(select 1 from ".DB_PREFIX."store s where s.store_id=tixian.user_id)";
                        }elseif($user_type==2){
                            $conditions.=" and EXISTS(select 1 from ".DB_PREFIX."store s where s.store_id=tixian.user_id)";
                        }elseif($user_type==3){
                            $conditions.=" and EXISTS(select 1 from ".DB_PREFIX."member m where m.user_id=tixian.user_id and member_type='02')";
                        }

                    }

                    $tixians=$this->_tixian_mod->find(array(
                        'conditions' =>$conditions,
                        'fields'=> 'this.*',
                        'count' => true,
                        'order' => 'add_time DESC',
                        'limit' => $page['limit'],
                    ));
                    $page['item_count'] = $this->_tixian_mod->getCount();
                    $this->_format_page($page);
                    $this->assign('page_info', $page);
                    $this->assign('tixians', $tixians);

                    $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                        'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
                    $this->display('tixian.html');
        }else{
                    //$this->pr($_GET);
                    $conditions="1=1 ";
                    if(!empty($_GET["user_name"]) && $_GET["user_name"]!=""){
                        $user_name=$_GET["user_name"];
                        $conditions.=" and EXISTS(select 1 from ".DB_PREFIX."member m where m.user_id=tixian.user_id and m.user_name like '%$user_name%')";
                    }
                    if(isset($_GET["status"]) && $_GET["status"]>=0){
                        $status=$_GET["status"];
                        $conditions.=" and tixian.status=$status";
                    }
                    if($_GET["add_time_from"]){
                        $add_time_from=$_GET["add_time_from"];
                        $conditions.=" and tixian.add_time>='$add_time_from'";
                    }
                    if($_GET["add_time_to"]){
                        $add_time_to=$_GET["add_time_to"];
                        $conditions.=" and tixian.add_time<='$add_time_to'";
                    }
                    if(isset($_GET["user_type"]) && $_GET["user_type"]>0){
                        $user_type=$_GET["user_type"];
                        if($user_type==1){
                            $conditions.=" and not EXISTS(select 1 from ".DB_PREFIX."store s where s.store_id=tixian.user_id)";
                        }elseif($user_type==2){
                            $conditions.=" and EXISTS(select 1 from ".DB_PREFIX."store s where s.store_id=tixian.user_id)";
                        }elseif($user_type==3){
                            $conditions.=" and EXISTS(select 1 from ".DB_PREFIX."member m where m.user_id=tixian.user_id and member_type='02')";
                        }
                    }
                    $tixians=$this->_tixian_mod->find(array(
                        'conditions' =>$conditions,
                        'fields'=> "
                                (select m.user_name from ".DB_PREFIX."member m where m.user_id=tixian.user_id) as name,
                                tixian.user_name,
                                tixian.channel_name,
                                tixian.channel_card,
                                tixian.jine,
                                tixian.status,
                                tixian.add_time,
                                tixian.update_time",
                        'order' => 'add_time DESC',
                        'count' => true
                    ));

                    $this->pr(count($tixians));exit;
                    $files=array("用户名","提现人","银行名","银行卡号","提现金额","状态","提现时间","更新时间");
                    if(count($tixians)>0){
                        $this->exportexcel($tixians,$files,gmtime());
                    }else{
                        echo "没有找到数据!";
                    }
        }

    }

        function exportexcel($data=array(),$title=array(),$filename='report'){
            header("Content-type:application/octet-stream");
            header("Accept-Ranges:bytes");
            header("Content-type:application/vnd.ms-excel");
            header("Content-Disposition:attachment;filename=".$filename.".xls");
            header("Pragma: no-cache");
            header("Expires: 0");
            $html="<table style=\" border:1px solid #66CCFF;background-color:#CDECFA;color:#3300FF\">";
            if (!empty($title)){
                $html.="<tr>";
                foreach ($title as $k => $v) {
                    //$title[$k]=iconv("UTF-8", "GB2312",$v);
                    $html.="<td align=\"left\" width=\"280px\" >".$title[$k]."</td>";
                }
                $html.="</tr>";
            }

            if (!empty($data)){
                foreach($data as $key=>$val){
                    $html.="<tr>";
                    foreach ($val as $ck => $cv) {
                        if($ck!="id"){
                            $html.="<td align=\"left\" width=\"280px\" >".$data[$key][$ck]."</td>";
                        }
                    }
                     $html.="</tr>";
                }
            }
            $html.="</table>";
            echo $html;
        }
}

?>
