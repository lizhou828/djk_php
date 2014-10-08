<?php

/* 提现 */
class TiXianApp extends BackendApp
{
    var $_tixian_mod;
    var $_yuelog_mod;
    var $_member_finance_mod;
    var $_member_mod;
    var $_order_mod;

    function __construct(){
        $this->TiXianApp();
    }

    function TiXianApp(){
        parent::__construct();
        $this->_tixian_mod =& m('tixian');
        $this->_yuelog_mod =& m('yuelog');
        $this->_member_finance_mod =& m('memberfinance');
        $this->_order_mod =& m('order');
        $this->_member_mod =& m('member');
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
                $add_time_from=$_GET["add_time_from"]." 00:00:00";
                $conditions.=" and tixian.add_time>='$add_time_from'";
            }
            if($_GET["add_time_to"]){
                $add_time_to=$_GET["add_time_to"]." 23:59:59";
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

            $page   =   $this->_get_page(20);   //获取分页信息
            $tixians=$this->_tixian_mod->find(array(
                'conditions' =>$conditions,
                'fields'=> 'this.*, (select m.user_name from ecm_member m where m.user_id = tixian.user_id) as m_user_name, (select m.member_type from ecm_member m where m.user_id = tixian.user_id) as member_type, (select m.region_name from ecm_member m where m.user_id = tixian.user_id) as region_name',
                'count' => true,
                'order' => 'add_time DESC',
                'limit' => $page['limit'],
            ));

            $sql = "select sum(tixian.jine) from ecm_tixian tixian where tixian.status !=3 and tixian.status !=4  and ".$conditions;
            $total =  $this->_tixian_mod->getOne($sql);
            $this->assign("total",$total);
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
//                    if(isset($_GET["status"]) && $_GET["status"]>=0){
//                        $status=$_GET["status"];
//                        $conditions.=" and tixian.status=$status";
//                    }
            if($_GET["add_time_from"]){
                $add_time_from=$_GET["add_time_from"]." 00:00:00";
                $conditions.=" and tixian.add_time>='$add_time_from'";
            }
            if($_GET["add_time_to"]){
                $add_time_to=$_GET["add_time_to"]." 23:59:59";
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
                'fields'=>"(select m.user_name from ".DB_PREFIX."member m where m.user_id=tixian.user_id) as name,
                                tixian.user_name,
                                tixian.channel_name,
                                tixian.channel_card,
                                tixian.jine,
                                tixian.status,
                                case tixian.type when '0' then '免税' when '1' then '缴税' end ,
                                tixian.add_time,
                                tixian.update_time",
                'order' => 'add_time DESC',
                'count' => true
            ));

//                    $this->pr($conditions);exit;
            $files=array("用户名","提现人","银行名","银行卡号","提现金额","状态","提现类型", "提现时间","更新时间");
            if(count($tixians)>0){

                $this->exportexcel($tixians,$files,gmtime());
            }else{
                echo "没有找到数据!";
            }
        }

    }



    function edit() {
        $tixian_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $tixian = $this->_tixian_mod->get_info($tixian_id);
        if (!IS_POST)
        {
            $member = $this->_member_mod->get_info($tixian['user_id']);
            $this->assign('tixian',$tixian);
            $this->assign('member',$member);
            $this->display("tixian.form.html");
        }else {
            ini_set('date.timezone','Asia/Shanghai');

            if($_POST['type'] && $_POST['type'] == 3) {
                if($tixian["status"] != 0) {
                    $this->show_message('该提现申请已在处理中不能取消提现！');
                    exit;
                }
//                $this->_tixian_mod->edit($tixian_id, array('status' => 3,'update_time'=>date('Y-m-d H:i:s',time())));
                $params["tixianId"] =$tixian_id;
                $params["userId"] =$tixian['user_id'];
                $params["type"] ="cancel_tixian";
//            $this->pr($params["tixianType"]);exit;

                $Client = new HttpClient(JAVA_LOCATION);
                $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;

                $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;

                $pageContents = HttpClient::quickPost($url, $params);
                //echo $pageContents;
                $data = json_decode($pageContents);

                header("Content-Type:text/html;charset=utf-8");
                if(!$data ||  $data->code != 200 ){
                    echo $pageContents;
                    $this->show_message('取消提现失败',
                        'back_list',        'index.php?app=tixian');
                }else{
                    echo "<script>alert('取消提现成功！');</script>";

                    $this->show_message('取消提现成功',
                        'back_list',        'index.php?app=tixian');
                }
            } elseif($_POST['type'] == 2)  {
                if($tixian["status"] !=1) {
                   $this->show_message('该提现申请不在处理中不能完成打款！');
                   exit;
                }
                $this->_tixian_mod->edit($tixian_id, array('status' => 2,'update_time'=>date('Y-m-d H:i:s',time())));
                $this->show_message('处理成功',
                    'back_list',        'index.php?app=tixian');
            } elseif($_POST['type'] == 1){
                if($tixian["status"]!=0) {
                    $this->show_message('该提现申请已取消不能处理！');
                    exit;
                }
                $this->_tixian_mod->edit($tixian_id, array('status' => 1,'update_time'=>date('Y-m-d H:i:s',time())));
                $this->show_message('该提现申请可以处理啦！',
                    'back_list',        'index.php?app=tixian');
            }

        }
    }

    function detail() {
        $userId = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $ksyueSql = "SELECT yue.type,yue.create_time,yue.beizhu,yue.user_id,yue.id,yue.jine as jine ,m.user_name from ecm_yue_log yue,ecm_member m where yue.user_id =".$userId."  and m.user_id =".$userId." and yue.yue_type = 1 order by create_time asc";
        $ksyueLog = $this->_yuelog_mod->db->getAll($ksyueSql);
        $this->assign('ksyueLog', $ksyueLog);
        $total = $this->_yuelog_mod->db->getOne("SELECT SUM(yue.jine) from ecm_yue_log yue where yue.user_id =".$userId." and yue.yue_type = 1 ");
        $this->assign('total', $total);
        $unyueSql = "SELECT yue.type,yue.create_time,yue.beizhu,yue.user_id,yue.id, yue.jine as jine,m.user_name from ecm_yue_log yue,ecm_member m where yue.user_id =".$userId."  and m.user_id =".$userId." and yue.yue_type = 0 order by create_time asc";
        $unyueLog = $this->_yuelog_mod->db->getAll($unyueSql);
        $untotal = $this->_yuelog_mod->db->getOne("SELECT SUM(yue.jine) from ecm_yue_log yue where yue.user_id =".$userId." and yue.yue_type = 0 ");
        $this->assign('untotal', $untotal);
        $this->assign('unksyueLog', $unyueLog);
        $choujiangSql = "SELECT date(yue.create_time) as create_time, sum(yue.jine) as jine, m.user_name from ecm_yue_log yue,ecm_member m where yue.user_id =".$userId." and m.user_id =".$userId." and (yue.type = 0 or yue.type = 1) group by date(yue.create_time), m.user_name order by yue.create_time asc";
        $choujiangLog = $this->_tixian_mod->db->getAll($choujiangSql);
        $cjtotal = $this->_yuelog_mod->db->getOne("SELECT SUM(yue.jine) from ecm_yue_log yue where yue.user_id =".$userId." and (yue.type = 0 or yue.type=1)");
        $this->assign('cjtotal', $cjtotal);
        $chongzhiSql = "SELECT  yue.type,yue.create_time,yue.beizhu,yue.user_id,yue.id, yue.jine as jine, m.user_name from ecm_yue_log yue,ecm_member m where yue.user_id =".$userId." and m.user_id =".$userId." and yue.type = 7 order by create_time asc";
        $chougzhiLog = $this->_tixian_mod->db->getAll($chongzhiSql);
        $cztotal = $this->_yuelog_mod->db->getOne("SELECT SUM(yue.jine) from ecm_yue_log yue where yue.user_id =".$userId." and yue.type = 7 ");
        $this->assign('cztotal', $cztotal);
        $paySql = "SELECT p.*,m.user_name  from ecm_pay p,ecm_member m where p.user_id =".$userId." and m.user_id =".$userId." order by p.create_time asc";
        $payLog = $this->_tixian_mod->db->getAll($paySql);
        $orderSql = "select o.* ,m.user_name from ecm_order o,ecm_member m where o.status = 40 and (o.seller_id = ".$userId." or o.buyer_id = ".$userId.") and m.user_id =".$userId." order by o.add_time desc";
        $orderLog = $this->_order_mod->db->getAll($orderSql);

        $this->assign('choujiangLog', $choujiangLog);
        $this->assign('chongzhiLog', $chougzhiLog);
        $this->assign('payLog', $payLog);
        $this->assign('orderLog', $orderLog);
        $this->assign('id', $userId);
        $this->display("tixian.detail.html");
    }

    function export() {

        $userId = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $sql ="";
        if ($userId&&$userId>0) {
            $conditions =" and yue.user_id =".$userId." and m.user_id =".$userId;
        }else {
            $conditions = " and yue.user_id = m.user_id";
        }
        if($_GET["type"]=='yue') {
            $sql = "SELECT m.user_name as name,yue.type as type,SUM(yue.jine) as jine,yue.beizhu as beizhu,yue.create_time as createTime from ecm_yue_log yue,ecm_member m where  yue.type <> 0 and yue.type <> 1 and yue.type <> 7 ".$conditions." and yue.yue_type = 1 group by date(yue.create_time),yue.type order by create_time asc";
        }
        if($_GET["type"]=='unkouyue') {
            $sql = "SELECT m.user_name as name,yue.type as type,SUM(yue.jine) as jine,yue.beizhu as beizhu,yue.create_time as createTime from ecm_yue_log yue,ecm_member m where  yue.type <> 0 and yue.type <> 1 and yue.type <> 7 ".$conditions." and yue.yue_type = 0 group by date(yue.create_time),yue.type order by create_time asc";
        }
        $ksyueLog = $this->_yuelog_mod->db->getAll($sql);

        $files=array("会员名","摘要","金额","备注","日期");
        if(count($ksyueLog)>0){
            $this->exportexcel($ksyueLog,$files,gmtime());
        }else{
            echo "没有找到数据!";
        }
    }
}

?>
