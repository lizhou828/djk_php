<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class MemberApp extends MemberbaseApp
{
    var $_feed_enabled = false;
    var $_posbindbankcard_mob;
    var $_posapply_mob;
    var $_posrecord_mob;
    var $user_id;
    var $user;
    function __construct()
    {
        include_once(ROOT_PATH . '/includes/email.class.php');          //邮件发送
        include_once(ROOT_PATH . '/includes/MSM.php');                    //短信发送
        $this->MemberApp();
    }
    function MemberApp()
    {
        parent::__construct();
        $ms =& ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();

        $this->_posbindbankcard_mob =& m('posbind');
        $this->_posapply_mob =& m('posapply');
        $this->_posrecord_mob =& m('posrecord');

        $this->assign('feed_enabled', $this->_feed_enabled);

        $this->_member_mod =& m('member');
        $state = $this->visitor->get('state');
        $this->user = $this->_member_mod->get("user_id=".$this->visitor->get('user_id'));
        $this->assign('user', $this->user);
        $store_mod =& m('store');
        if($store_mod->get("store_id =".$this->visitor->get('user_id')." and state=1 and dropState=1")){
            $this->assign('storeFlg', 1);
        }

    }
    function index()
    {
        /* 清除新短消息缓存 */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));

        $user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        $user['portrait'] = portrait($user['user_id'], $info['portrait'], 'middle');

        $user['phone_mob'] = $info['phone_mob'];
        $user['email']     = $info['email'];
        $user['phone_mob_bind_status']     = $info['phone_mob_bind_status'];
        $user['email_bind_status']     = $info['email_bind_status'];
        $user['password2']     = $info['password2'];
        $user['spreader_type']     = $info['spreader_type'];

        $user_api = json_decode($this->user_api("","",""));
        if( $user_api->code == 200) {
            $user["koushui_yue"]=$user_api->koushui_yue;
            $user["unkoushui_yue"]=$user_api->unkoushui_yue;
            $user["xiaofei_jine"]=$user_api->xiaofei_jine;
            $user["jifen"]=$user_api->jifen;
        }


        $store_mod =& m('store');

        $state = $this->visitor->get('state');
        if($store_mod->get("store_id =".$this->visitor->get('user_id')." and state=1 and dropState=1")){
            //判断如果有店铺，显示 营业额
            $yye =$user_mod->getOne("SELECT SUM(order_amount) FROM ecm_order WHERE STATUS=40 and seller_id =".$user['user_id']);
            $user["yye"]= ($yye!=null) ? $yye:0;
        }

        if($user["member_type"]=="01"){
            $user["all_choujiang"]=$user_mod->getOne("select count(1) as cnt from ".DB_PREFIX."choujiangquan where user_id=".$user['user_id']);
            $user["choujiang"]=$user_mod->getOne("select count(1) cnt from ".DB_PREFIX."choujiangquan where  status = 1 AND count < 100  and  user_id=".$user['user_id']);
        }



        $t_cxount = $user_mod->getOne("select count(1) as cnt from ".DB_PREFIX."member where t_id = ".$user['user_id']);
        if($t_cxount >0 ){
            $ourders_store = $user_mod->getOne("SELECT count(1) FROM ecm_order orders WHERE orders.status =40 AND EXISTS (SELECT 1 FROM ecm_member member
                                           WHERE member.`user_id` = orders.seller_id AND member.dropState = 1  AND member.t_id=".$user['user_id'].")");
            $ourders_member =  $user_mod->getOne("SELECT count(1) FROM ecm_order orders WHERE orders.status =40 AND EXISTS (SELECT 1 FROM ecm_member member
                                    WHERE member.user_id = orders.buyer_id AND member.dropState = 1  AND member.t_id=".$user['user_id'].")");
            $this->assign('ourders_store', $ourders_store);
            $this->assign('ourders_member', $ourders_member);

        }else{
            $this->assign('tuiguang_flag', "false");
        }
        //$this->pr($user);
        $this->assign('user', $user);
        $this->assign('_user', $user);


        $this->assign('member_type', $info["member_type"]);

        /* 店铺信用和好评率 */
        if ($user['has_store']){
            $store_mod =& m('store');
            $store = $store_mod->get_info($user['has_store']);
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);
            $this->assign('store', $store);
            //获取托管商家所在的服务中心信息
            if( $store['tuoguan'] == 1 ){
                $store_service = $user_mod->get("user_id= ".$store['fwzx']." and status=2 and dropState=1 and ( member_type='02' or member_type='03' or member_type='04')");
                $this->assign('store_service',$store_service );
            }

            $this->assign('store_closed', STORE_CLOSED);
        }
        $goodsqa_mod = & m('goodsqa');
        $groupbuy_mod = & m('groupbuy');
        /* 买家提醒：待付款、待确认、待评价订单数 */
        $order_mod =& m('order');
        $sql1 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_PENDING . "'";
        $sql2 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_SHIPPED . "'";
        $sql3 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";
        $sql4 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE user_id = '{$user['user_id']}' AND reply_content !='' AND if_new = '1' ";
        $sql5 = "SELECT COUNT(*) FROM " . DB_PREFIX ."groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " .GROUP_CANCELED;
        $sql6 = "SELECT COUNT(*) FROM " . DB_PREFIX ."groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " .GROUP_FINISHED;
        $buyer_stat = array(
            'pending'  => $order_mod->getOne($sql1),
            'shipped'  => $order_mod->getOne($sql2),
            'finished' => $order_mod->getOne($sql3),
            'my_question' => $goodsqa_mod->getOne($sql4),
            'groupbuy_canceled' => $groupbuy_mod->getOne($sql5),
            'groupbuy_finished' => $groupbuy_mod->getOne($sql6),
        );
        $sum = array_sum($buyer_stat);
        $buyer_stat['sum'] = $sum;
        $this->assign('buyer_stat', $buyer_stat);

        /* 卖家提醒：待处理订单和待发货订单 */
        if ($user['has_store'])
        {

            $sql7 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_SUBMITTED . "'";
            $sql8 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_ACCEPTED . "'";
            $sql9 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE store_id = '{$user['user_id']}' AND reply_content ='' ";
            $sql10 = "SELECT COUNT(*) FROM {$groupbuy_mod->table} WHERE store_id='{$user['user_id']}' AND state = " .GROUP_END;
            $seller_stat = array(
                'submitted' => $order_mod->getOne($sql7),
                'accepted'  => $order_mod->getOne($sql8),
                'replied'   => $goodsqa_mod->getOne($sql9),
                'groupbuy_end'   => $goodsqa_mod->getOne($sql10),
            );

            $this->assign('seller_stat', $seller_stat);
        }
        /* 卖家提醒： 店铺等级、有效期、商品数、空间 */
        if ($user['has_store'])
        {
            $store_mod =& m('store');
            $store = $store_mod->get_info($user['has_store']);

            $grade_mod = & m('sgrade');
            $grade = $grade_mod->get_info($store['sgrade']);

            $goods_mod = &m('goods');
            $goods_num = $goods_mod->get_count_of_store($user['has_store']);
            $uploadedfile_mod = &m('uploadedfile');
            $space_num = $uploadedfile_mod->get_file_size($user['has_store']);
            $sgrade = array(
                'grade_name' => $grade['grade_name'],
                'add_time' => empty($store['end_time']) ? 0 : sprintf('%.2f', ($store['end_time'] - gmtime())/86400),
                'goods' => array(
                    'used' => $goods_num,
                    'total' => $grade['goods_limit']),
                'space' => array(
                    'used' => sprintf("%.2f", floatval($space_num)/(1024 * 1024)),
                    'total' => $grade['space_limit']),
            );
            $sql_tixian = "SELECT COUNT(0) FROM ecm_member WHERE member_type = '04' AND user_id = '{$store['fwzx']}'";
            if($store_mod->getOne($sql_tixian) ) {
                $storeIds = Conf::get("tuoguan_store_ids");
                if($storeIds && in_array($store['store_id'],$storeIds)) {
                    // ignore
                } else {
                    $this->assign('noTixian',"true");
                }
            }
            $this->assign('sgrade', $sgrade);

        }

        /* 待审核提醒 */
        if ($user['state'] != '' && $user['state'] == STORE_APPLYING)
        {
            $this->assign('applying', 1);
        }
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
            LANG::get('overview'));


        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css,res:jqtreetable.css',
        ));


        $this->assign('TO_CHONGZHI_URL', TO_CHONGZHI_URL);
        $this->assign('TO_PLCHOUJIANG_URL', TO_CHOUJIANG_URL);


        /*查看是否绑定银行卡*/
        $member_bank = & m('memberbank');
        $bank = $member_bank -> find('user_id = ' . $this->visitor->get('user_id'));
        $flag = true;
        if(count($bank) > 0){
            $flag = false;
        }
        $this->assign('flag', $flag);

        /* 当前用户中心菜单 */
        $this->_curitem('overview');

        $this->_config_seo('title', "账户概览" . ' - ' . Lang::get('member_center'));

        $this->display('member.index.html');
    }

    function first(){
        $this->display('member.first.html');
    }
    //余额log
    function showYueLog(){

        $user = $this->visitor->get();
        $yuelog_mod =& m('yuelog');

        /* 当前用户中心菜单 */
        $this->_curitem('overview');

        $conditions = "";
        $sumconditions = "";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')>='$add_time_from'";
            $sumconditions .=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')>='$add_time_from'";
        }
        $type=$_GET["type"];
        if($type!="" && $type>=0){
            $conditions.=" and type=$type";
            $sumconditions.=" and type=$type";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')<='$add_time_to'";
            $sumconditions .=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $page = $this->_get_page();
        $yuelog=$yuelog_mod->find(array(
            'conditions' => " user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        if ($yuelog) {
            foreach ($yuelog as $key=>$y) {
                if(strpos($y['beizhu'],'_')) {
                    $yuelog[$key]['beizhu'] =substr($y['beizhu'],0,strpos($y['beizhu'],'_'));
                }
            }
        }
        $sumjine =$yuelog_mod->getOne("SELECT SUM(yue_log.jine) FROM ecm_yue_log yue_log WHERE 1=1 $sumconditions and yue_log.user_id =".$user['user_id']);
        $this->assign('sumjine', $sumjine ? abs($sumjine) : 0);

        $page['item_count'] = $yuelog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('yuelog', $yuelog);
        $this->display('member.showLog.html');
    }

    //营业额log
    function showYyeLog(){

        $user = $this->visitor->get();
        $state = $this->visitor->get('state');
        if($state==0){
            exit("只有商家才能查看营业额log");
        }

        $yyelog_mod =& m('yyelog');


        $this->_curitem('overview');

        $conditions="";
        if($_GET["add_time_from"])
        {
            //$add_time_from=$_GET["add_time_from"];
            $add_time_from=gmstr2time($_GET["add_time_from"]);
            $conditions.=" and yye_log.create_time>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            //$add_time_to=$_GET["add_time_to"];
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            // $conditions.=" and date_format(goodsOrder1.add_time,'%Y-%m-%d')<='$add_time_to'";
            $conditions.=" and yye_log.create_time<='$add_time_to'";
        }

        $page = $this->_get_page();
        $yyelog=$yyelog_mod->find(array(
            'conditions' => " user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        $page['item_count'] = $yyelog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('yyelog', $yyelog);

        //$this->pr($yyelog);exit;

        $this->display('member.showLog.html');
    }

    //积分log
    function showJifenLog(){

        $user = $this->visitor->get();

        $jifenlog_mod =& m('jifenlog');

        /* 当前用户中心菜单 */
        $this->_curitem('overview');


        $conditions="";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(jifen_log.create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(jifen_log.create_time,'%Y-%m-%d')<='$add_time_to'";
        }

        $page = $this->_get_page();

        $sql = "SELECT jifen_log.* FROM ecm_jifen_log jifen_log WHERE user_id =".$user['user_id'].$conditions." order by DATE(create_time) desc ";
        $sqlCount = "SELECT count(*) from ecm_jifen_log jifen_log WHERE user_id =".$user['user_id'].$conditions;
        
        $recommendMod = &m("recommend");
        $recommendGoodsList = $recommendMod->goodsList($sql,$page['curr_page'],$page['pageper']);
        $count = $recommendMod->resultCount($sqlCount);
        $page['item_count']=$count;
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('jifenlog', $recommendGoodsList);
        $this->display('member.showLog.html');
    }

    //抽奖权log
    function showChoujiangquanLog(){

        $user = $this->visitor->get();

        $choujiangquan_mod =& m('choujiangquan');

        /* 当前用户中心菜单 */
        $this->_curitem('overview');

        $page = $this->_get_page();

        $conditions="";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(choujiangquan.create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(choujiangquan.create_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $choujiangquan=$choujiangquan_mod->find(array(
            'conditions' => "user_id =".$user['user_id'].$conditions." status",
            'fields'=> 'this.*,(100-this.count) as count',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        $page['item_count'] = $choujiangquan_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('choujiangquan', $choujiangquan);
        $this->display('member.showLog.html');
    }

    //抽奖log
    function showChoujiangLog()
    {
        $user = $this->visitor->get();

        $choujiang_mod =& m('choujiang');

        /* 当前用户中心菜单 */
        $this->_curitem('overview');

        $page = $this->_get_page();

        $conditions="";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(choujiang_detail .create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(choujiang_detail .create_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $choujiang=$choujiang_mod->find(array(
            'conditions' => "user_id =".$user['user_id'].$conditions,
            'fields'=> "this.*",
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        $sumjine =$choujiang_mod->getOne("SELECT SUM(cj.jine) FROM ecm_choujiang_detail cj WHERE 1=1 $conditions and cj.user_id =".$user['user_id']." and cj.pool_type =0");
        $this->assign('sumjine', $sumjine ? abs($sumjine) : 0);

        $sumjifen = $choujiang_mod->getOne("SELECT SUM(cj.jine) FROM ecm_choujiang_detail cj WHERE 1=1 $conditions and cj.user_id =".$user['user_id']." and (cj.pool_type =1 or cj.pool_type= 2)");
        $this->assign('sumjifen', $sumjifen ? abs($sumjifen) : 0);


        if(count($choujiang)>0)
        {
            foreach($choujiang as $k=>$v){

                if($v["jibie"]<=0){continue;}

                if($v["jibie"]>10){
                    $choujiang[$k]["desc"]="抽中了小奖池的".($v["jibie"]-10)."等奖,获得".(ceil($v["jine"]*40))."积分！";
                }else{
                    $choujiang[$k]["desc"]="抽中了大奖池的".($v["jibie"])."等奖,获得".$v["jine"]."奖金！";
                }

            }
        }
        //$this->pr($choujiang);
        $page['item_count'] = $choujiang_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('choujiang', $choujiang);

        $this->display('member.showLog.html');
    }


    //充值log
    function showChongzhiLog(){

        $user = $this->visitor->get();
        $paylog_mod =& m('paylog');

        $this->_curitem("overview");

        $conditions="";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(pay_log.create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(pay_log.create_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $page = $this->_get_page();
        $paylog=$paylog_mod->find(array(
            'conditions' => "type=2 and user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        $page['item_count'] = $paylog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('paylog', $paylog);
        $this->display('member.showLog.html');
    }


    //提现log
    function showTiXianLog(){

        $user = $this->visitor->get();
        $tixian_mod =& m('tixian');

        $this->_curitem("overview");

        $conditions="";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(tixian.add_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(tixian.add_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $page = $this->_get_page();


        $sql = "select DISTINCT yue_log.*,tixian.* from ecm_yue_log yue_log left join ecm_tixian tixian  on yue_log.user_id = tixian.user_id where yue_log.user_id =".$user['user_id'].$conditions." and yue_log.type=3 group by tixian.id order by yue_log.create_time desc";
//        $this->pr($sql);exit;
        $sqlCount = " select count(*) from ecm_yue_log yue_log left join ecm_tixian tixian  on yue_log.user_id = tixian.user_id where yue_log.user_id =".$user['user_id'].$conditions." and yue_log.type=3  group by tixian.id";
        $recommendMod = &m("recommend");
        $tixian = $recommendMod->goodsList($sql,$page['curr_page'],$page['pageper']);
        $count = $recommendMod->resultCount($sqlCount);
        $page['item_count']=$count;
//             $this->pr($page);exit;
        $this->_format_page(
            $page);
        $this->assign('page_info', $page);
        $this->assign('tixianLog', $tixian);
        $this->display('member.showLog.html');
    }

    /**
     *    注册一个新用户
     *
     *    @author    Garbin
     *    @return    void
     */
    function register() {
        $member_mod  =& m('member');
        $msm_time = $_SESSION["msm_time"];
        if($msm_time != "" && $msm_time >0 && gmtime() - $msm_time < 120){
            $this->assign('t_time',120-(gmtime() - $msm_time));
        }

        if ($this->visitor->has_login) {
            /*$this->show_warning('has_login');
            return;*/
            $this->_init_visitor();
        }
        if (!IS_POST) {
            //显示链接来源
            $t_id = $_GET['t_id'];
            if (!$t_id || $t_id == null || $t_id == '') {
                $t_id = $_SESSION['t_id'];
            }
            if (!$t_id || $t_id == null || $t_id == '') {
                $t_id = ecm_getcookie("t_id");
            }
            if($t_id!=null && $t_id!=""){
                if(!is_numeric($t_id)){
                    $t_id =base64_decode($t_id);
                }
                $t_user=$member_mod->get_info($t_id);
                if (!empty($t_user) && $t_user["member_type"] == "01") {
                    $t_id = $t_user['user_id'];
                    $this->assign('t_user',$t_user);
                } else {
                    $t_id = null;
                }
            }
            //判断是否为手机客户端来的
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
            $is_mobile = false;
            foreach ($mobile_agents as $device) {
                if (stristr($user_agent, $device)) {
                    $is_mobile = true;
                    break;
                }
            }
            if($is_mobile){
                header("Location:http://weixin1.dajike.com/members/register?t_id=" . $t_id);
            }
            if (!empty($_GET['ret_url'])) {
                $ret_url = trim($_GET['ret_url']);
            } else {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                } else {
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_register'));
            $this->_config_seo('title', Lang::get('user_register') . ' - ' . Conf::get('user_center'));

            if (Conf::get('captcha_status.register')) {
                $this->assign('captcha', 1);
            }
            if(FAIL_TIME=="FAIL_TIME" || FAIL_TIME==""){
                $this->assign('FAIL_TIME',61);
            } else {
                $this->assign('FAIL_TIME',FAIL_TIME);
            }

            /* 导入jQuery的表单验证插件 */
            $this->import_resource('jquery.plugins/jquery.validate.js');

            $this->display('member.register.html');

        }
        else {
            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];
            $phone_mob = null;
            $email = null;
            $email_active_code = null;

            //做注册限制，每个ip最大注册为50个
            $reg_ip   = real_ip();
            $sql="SELECT COUNT(1) FROM ".DB_PREFIX."member WHERE reg_ip = '$reg_ip' and FROM_UNIXTIME(reg_time, '%Y%m%d' ) = DATE_FORMAT(NOW(),'%Y%m%d')";
            $regCount = $this->_member_mod->getOne($sql);

            //最大注册为可配置
            $REG_MAX=(REG_MAX<=0 || REG_MAX=="" || REG_MAX == "REG_MAX")?50:REG_MAX;
            if($regCount > $REG_MAX){
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="user_reg_max";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }

            //用户已经存在
            if (substr(strtoupper($user_name),0,3) == "DJK") {
                $this -> show_warning('register_username_error');
                //header("Location: index.php?app=member&act=register&msg=register_username_exists");
                return;
            }
            //请接受服务条款
            if (!$_POST['agree']) {
                //$this->show_warning('agree_first');
                //header("Location: index.php?app=member&act=register&msg=agree_first");
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="agree_first";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }
            if (Conf::get('captcha_status.register') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
                //$this->show_warning('captcha_failed');
                //header("Location: index.php?app=member&act=register&msg=captcha_failed");
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="captcha_failed";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }
            //两次输入的密码不一致
            if ($password != $_POST['password_confirm']) {
                // $this->show_warning('inconsistent_password');
                //header("Location: index.php?app=member&act=register&msg=inconsistent_password");
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="inconsistent_password";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }
            $passlen = strlen($password);
            $user_name_len = strlen($user_name);
            //用户名长度只能在3-20位字符之间
            if ($user_name_len < 3 || $user_name_len > 25) {
                //$this->show_warning('user_name_length_error');
                //header("Location: index.php?app=member&act=register&msg=user_name_length_error");
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="user_name_length_error";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }
            //密码长度只能在6-20位字符之间
            if ($passlen < 6 || $passlen > 20) {
                //$this->show_warning('password_length_error');
                //header("Location: index.php?app=member&act=register&msg=password_length_error");
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="password_length_error";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }

            //手机注册时候，短信验证码是否正确
            if (is_mobile($user_name) && IF_DUANXING !=0) {
                /*if (!$_POST["yanzhengma"] || $_POST["yanzhengma"] != $_SESSION["yanzhengma"]) {
                    //header("Location: index.php?app=member&act=register&msg=yanzhengmaerror");
                    $this->import_resource('jquery.plugins/jquery.validate.js');
                    $_POST["errorMsg"]="yanzhengmaerror";
                    $this->assign('postData', $_POST);
                    $this->display('member.register.html');
                    return;
                }*/
                $phone_mob = $user_name;
            }
            //用户已经存在
            $data=$this->_member_mod->find("(user_name='". $user_name. "' or phone_mob='".$user_name."' or email='".$user_name."')");
            if(count($data)>0){
                //header("Location: index.php?app=member&act=register&msg=mob_or_email_find");
                $this->import_resource('jquery.plugins/jquery.validate.js');
                $_POST["errorMsg"]="mob_or_email_find";
                $this->assign('postData', $_POST);
                $this->display('member.register.html');
                return;
            }

            //邮箱注册，生成邮箱绑定激活码
            if (is_email($user_name)) {
                $email = $user_name;
                $email_active_code = base64_encode(gmtime() . $email);
            }

            $t_id = $_POST['t_id'];
            if (!$t_id || $t_id == null || $t_id == "") {
                $t_id = $_SESSION['t_id'];
            }
            if (!$t_id || $t_id == null || $t_id == "") {
                $t_id = ecm_getcookie("t_id");
            }

            if ($t_id && $t_id != null && $t_id != "") {
                //$t_id = iconv("gb2312","UTF-8",base64_decode(trim(( ($session_t_id!="") ? $session_t_id : $cookie_t_id) )));
                if(!is_numeric($t_id)){
                    $t_id =base64_decode($t_id);
                }
                $dateInfo = $member_mod->get_info($t_id);
                if (!empty($dateInfo) && $dateInfo["member_type"] == "01") {
                    $t_id = $dateInfo["user_id"];
                }else{
                    $t_id = null;
                }
            }

            $ms =& ms(); //连接用户中心

            $local_data['user_name']       = $user_name;
            $local_data['password']        = md5($password);
            $local_data['nick_name']       = $user_name;
            $local_data['reg_ip']          =real_ip();
            if ($t_id != null && $t_id != "") {
                //$local_data['jifen']=5;         //推广连接进来的送5积分
                $local_data['t_id'] = $t_id;
            }
            $local_data['reg_time']         = gmtime();
            $local_data['member_type']     = "01";
            if ($phone_mob != null) {
                $local_data['phone_mob'] = $phone_mob;
                $local_data['phone_mob_bind_status'] = 1;
            }
            if ($email != null) {
                $local_data['email'] = $email;
                $local_data['email_active_code'] = $email_active_code;
                $local_data['email_bind_status'] = 0;
            }
            $user_id = $member_mod->add($local_data);
            if (!$user_id)
            {
                $this->show_warning($ms->user->get_error());
                return;
            }

            /*if ($t_id != null) {
                ini_set('date.timezone','Asia/Shanghai');
                $data=array("user_id"=>$user_id,
                    "jifen"=>5,
                    "beizhu"=>"您通过推广连接注册,您获得5积分！",
                    "create_time"=>date('Y-m-d H:i:s',time()),
                    "type"=>3
                );
                $jifenlog_mod =& m('jifenlog');
                $jifenlog_mod->add($data);              //记录积分log
            }*/

            $member_mod->commit_transaction();
            //登录
            $this->_do_login($user_id);
            /* 同步登陆外部系统 */
            $synlogin = $ms->user->synlogin($user_id);

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
            //$data =json_decode($pageContents);           这里忽略反馈结果


            //发送绑定邮件
            if ($email != null) {
                $content = file_get_contents(ROOT_PATH . "/themes/mall/default/regist_email_content.html");
                $content = str_replace("%s1", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
                $content = str_replace("%s2", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
                include_once(ROOT_PATH . '/app/sendcode.app.php');
                $sender = new SendCodeApp();
                $sender -> send_email($email, "欢迎绑定邮箱到大集客", $content);
            }

            if ($phone_mob != null) {
                $smsclient = new SMSClient();
                $content = "恭喜您成功注册为大集客会员，会员号".$phone_mob."，您在大集客平台的每一笔消费都有机会参加100次的抽奖活动，期待你的参予！【大集客】";
                $smsclient->sendSMS($phone_mob, $content);
            }

            $this->_hook('after_register', array('user_id' => $user_id));

            header("Location: index.php?app=member&act=register_success");
        }
    }

    function register_success() {
        $user = $this->visitor->info;
        if ($user['user_id'] > 0) {
            $member_mod  =& m('member');
            $user = $member_mod->get($user['user_id']);

        }
        if (is_email($user['email'])) {
            $arr = explode("@", $user['email']);
            $this->assign("email_domain", $arr[1]);
        }
        $this->assign("mobile", is_mobile($user['phone_mob']));
        $this->assign("user", $user);
        $this->display("member.register_sucess.html");
    }

    function updatenick() {
        $user = $this->visitor->info;
        if ($user['user_id'] > 0) {
            $member_mod  =& m('member');
            $user = $member_mod->get($user['user_id']);
            $nick_name = $_POST["nick_name"];
            $list = $member_mod->find("nick_name='" . $nick_name. "'");
            if (count($list) > 0) {
                echo 'E';
                exit;
            }
            $user['nick_name'] = $nick_name;
            $member_mod->edit($user['user_id'], array('nick_name'=>$nick_name));
            echo 'Y';
        }
    }

    function updateemail() {
        //$email = $_POST["email"];
        $email = $_GET["email"] ? $_GET["email"] : $_POST["email"] ;
        if (!is_email($email)) {
            echo 'N';
            exit;
        }

        $visitor=$this->visitor;

        $user=$visitor->info;

        //$this->pr($user);

        if ($user['user_id'] > 0) {
            $member_mod  =& m('member');
            $list = $member_mod->find("email='" .$email."'");
            if (count($list) > 0) {
                echo 'E';
                exit;
            }
            $list = $member_mod->find("user_id=" . $user['user_id']);
            if (count($list) >0) {
                foreach($list as $k=>$user)
                    if ($user['email'] == null) {
                        $user['email'] = $email;
                        $email_active_code=base64_encode(gmtime() . $email);
                        $member_mod->edit($user["user_id"], array('email'=>$email,'email_active_code'=>$email_active_code,'email_bind_status'=>0));
                        $this->sendbingdemail($user['email'], $email_active_code);
                        echo 'Y';
                    }
            }
        }
    }

    function home() {
        $user_info=  $this->visitor->get();
        if($user_info['member_type']=='01') {
            header("Location:index.php?app=member&p=userInfo");
        }
        if ($user_info['member_type']=='02'||$user_info['member_type']=='03'||$user_info['member_type']=='04') {
            header("Location:index.php?app=service&p=service&tuoguan=1");
        }
    }


    function resend() {
        $user = $this->visitor->info;
        if ($user['user_id'] > 0) {
            $member_mod  =& m('member');
            $user = $member_mod->get(array(
                'conditions' => "1=1 AND user_id =".$user['user_id'],
                'fields' => 'email_active_code,user_name,user_id,email,phone_mob',));
            $this->sendbingdemail($user['email'], $user['email_active_code']);
            echo '发送成功';
        } else {
            echo '发送失败';
        }
    }

    function sendbingdemail($email, $email_active_code) {
        //发送绑定邮件
        if ($email != null) {
            $content = file_get_contents(ROOT_PATH . "/themes/mall/default/regist_email_content.html");
            $content = str_replace("%s1", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
            $content = str_replace("%s2", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
            include_once(ROOT_PATH . '/app/sendcode.app.php');
            $sender = new SendCodeApp();
            $sender -> send_email($email, "欢迎注册大集客", $content);
        }
    }


    /**
     *    检查用户是否存在
     *
     *    @author    Garbin
     *    @return    void
     */
    function check_user()
    {
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name)
        {
            echo ecm_json_encode(false);
            return;
        }
        $ms =& ms();
        if(!$_GET["now"]){
            echo ecm_json_encode($ms->user->check_username($user_name));
            exit;
        }
        echo ecm_json_encode(!($ms->user->check_username($user_name)));

    }

    /**
     *    检查邮箱是否存在
     *
     *    @author    Garbin
     *    @return    void
     */
    function check_user_or_phone_mob()
    {
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name)
        {
            echo ecm_json_encode(false);
            return;
        }

        $flg =true;
        $model_member =& m('member');

        $cnt = count(explode("@",$user_name));
        $sql = "user_name='$user_name' or email='$user_name' or phone_mob='$user_name' or user_id='$user_name' ";
        if($cnt>1){
            $sql = "user_name='$user_name' or email='$user_name' or phone_mob='$user_name'";
        }
        $info = $model_member->get($sql);
        if (!empty($info))
        {
            $this->_error('phone_mob_exists');
            $flg = false;
        }

        if($_GET["type"] || $_GET["user_type"] == "2"){
            echo ecm_json_encode(!$flg);
        }else{
            echo ecm_json_encode($flg);
        }

    }

    function check_pwd(){
        $user_name=$_POST["user_name"];
        $password=$_POST["password"];
        if($user_name == "" || $password == "" ){
            echo ecm_json_encode(false);
            exit;
        }
        $password=md5($password);
        $model_member =& m('member');
        $info = $model_member->get("(user_name='$user_name' or email='$user_name' or phone_mob='$user_name' or user_id='$user_name') and password='$password' and dropState=1");
        if($info["user_id"]>0){
            echo ecm_json_encode(true);
        }else{
            echo ecm_json_encode(false);
        }
    }

    /**
     *    检查邮箱是否存在
     *
     *    @author    Garbin
     *    @return    void
     */
    function check_phone_mob()
    {
        $phone_mob = empty($_GET['phone_mob']) ? null : trim($_GET['phone_mob']);
        if (!$phone_mob)
        {
            echo ecm_json_encode(false);
            return;
        }

        $flg =true;
        $model_member =& m('member');
        $info = $model_member->get("phone_mob='$phone_mob'");
        if (!empty($info))
        {
            $this->_error('phone_mob_exists');
            $flg = false;
        }
        echo ecm_json_encode($flg);
    }

    /*检查验证码是否正确*/
    function check_code()
    {
        $code = empty($_GET['code']) ? null : trim($_GET['code']);
        $se_code = $_SESSION['brzccode'];
        if (!$code)
        {
            echo ecm_json_encode(false);
            return;
        }
        $flg = false;
        if ($se_code == $code)
        {
            $flg = true;
        }
        echo ecm_json_encode($flg);
    }
    /*    检查用户是否存在或者用户已经开了商铺
    *
    *    @author    Garbin
    *    @return    void
    */
    function check_user2()
    {

        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name)
        {
            echo 0;
            return;
        }

        $member_mod  =& m('member');
        $user_info= $member_mod->find(array(
            'conditions' => " (SELECT COUNT(1) FROM ".DB_PREFIX."store WHERE store_id=member.`user_id`)<1 and member.user_name='$user_name' or member.email='$user_name' or member.phone_mob='$user_name')",
            'fields'     => 'user_id',
        ));

        echo count($user_info);

    }

    /**
     *    检查用户是否存在---  邀请码
     *
     *    @author    Garbin
     *    @return    void
     */
    function check_invi_code()
    {
        $invi_code = empty($_GET['invi_code']) ? null : trim($_GET['invi_code']);
        if (!$invi_code)
        {
            echo ecm_json_encode(false);

            return;
        }
        $ms =& ms();
        //验证邀请码和验证用户相反
        echo ecm_json_encode(($ms->user->check_username($invi_code)==null)? true:false);

    }

    /**
     *    修改基本信息
     *
     *    @author    Hyber
     *    @usage    none
     */
    function profile(){
        import('image.func');
        $visitor=$this->visitor->info;

        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                LANG::get('basic_information'));

            if($_GET["act"]=="yinhangka"){
                /* 当前用户中心菜单 */
                $this->_curitem('yinhangka');
            }else{
                /* 当前用户中心菜单 */
                $this->_curitem('my_profile');
            }


            /* 当前所处子菜单 */
            $this->_curmenu('basic_information');

            $ms =& ms();    //连接用户系统
            $edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //获取头像设置方式

            $model_user =& m('member');
            $profile    = $model_user->get_info(intval($user_id));
            $profile['portrait'] = portrait($profile['user_id'], $profile['portrait'], 'middle');

            if(!empty($profile["kahao"]) && $profile["kahao"]!=""){
                $bank_mod =& m('memberbank');
                $my_bank=$bank_mod->get(array(
                    "conditions"=>"user_id=$user_id and bank_code='".$profile["kahao"]."'"
                ));
                if($my_bank["bank_name"]!="" && $my_bank["bank_kahao"]!=""){
                    $profile["kahao"]=$my_bank["bank_name"]."              ".$this->half_replace($my_bank["bank_kahao"]);
                }else{
                    $profile["kahao"]="";
                }

            }else{
                $profile["kahao"]="";
            }


            $this->assign('profile',$profile);
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->assign('edit_avatar', $edit_avatar);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_profile'));
            $this->display('member.profile.html');
        }
        else{

            $model_user =& m('member');
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
                'birthday'  => $_POST['birthday'],
                'im_msn'    => $_POST['im_msn'],
                'im_qq'     => $_POST['im_qq'],
                'kahao'     => $_POST['kahao'],
                'nick_name'     => $_POST['nick_name'],
                'card_id'     => $_POST['card_id'],
            );
            if (!empty($_FILES['portrait']) && $_FILES['portrait']["size"]>0){
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false){
                    return;
                }
                if(file_exists(DIS_PATH . '/' . $portrait)){
                    /*生成压缩图*/
                    $urls = explode('.',$portrait);
                    $path = $urls[0] . '_' . '130X130' . '.' . $urls[1];
                    /* 指定保存位置的根目录 */

                    if(!file_exists(DIS_PATH .'/' . $path)){
                        make_thumb(ROOT_PATH . '/' . $portrait, ROOT_PATH .'/' . $path, 130, 130, 90);
                    }
                } else {
                    $urls = explode('.',$portrait);
                    $path = $urls[0] . '_' . '130X130' . '.' . $urls[1];

                    make_thumb(ROOT_PATH.'/'.$portrait, ROOT_PATH .'/' . $path, 130, 130, 90);
                }
                $data['portrait'] = $portrait;
            }
            $model_user->edit($user_id , $data);
            if($visitor["member_type"]=="02" && $_POST['kahao']!=""){
                $kahao=$_POST['kahao'];
                $serviceInfo_model =& m('serviceInfo');
                $serviceInfo_model->db_query("update ".DB_PREFIX."service_info set kahao='$kahao' where service_id=$user_id and type=1");
            }
            //$this->show_message('edit_profile_successed');
            if($_POST["show_alert"]!=1){
                echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'><script>alert('修改成功！');location='index.php?app=member&act=profile&p=userInfo'</script>";
            }else{
                header("Location: index.php?app=member&act=profile&p=userInfo");
            }

        }
    }
    /**
     *    修改密码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function password(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                LANG::get('edit_password'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_password');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
            $this->display('member.password.html');
        }
        else
        {
            /* 两次密码输入必须相同 */
            $orig_password      = $_POST['orig_password'];
            $new_password       = $_POST['new_password'];
            $confirm_password   = $_POST['confirm_password'];
            if ($new_password != $confirm_password)
            {
                $this->show_warning('twice_pass_not_match');

                return;
            }
            if (!$new_password)
            {
                $this->show_warning('no_new_pass');

                return;
            }
            $passlen = strlen($new_password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            /* 修改密码 */
            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'password'  => $new_password
            ));
            if (!$result)
            {
                /* 修改不成功，显示原因 */
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->show_message('edit_password_successed');
        }
    }
    /**
     *    修改电子邮箱
     *
     *    @author    Hyber
     *    @usage    none
     */
    function email(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                LANG::get('edit_email'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_email');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_email'));
            $this->display('member.email.html');
        }
        else
        {
            $orig_password  = $_POST['orig_password'];
            $email          = isset($_POST['email']) ? trim($_POST['email']) : '';
            if (!$email)
            {
                $this->show_warning('email_required');

                return;
            }
            if (!is_email($email))
            {
                $this->show_warning('email_error');

                return;
            }

            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'email' => $email
            ));
            if (!$result)
            {
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->show_message('edit_email_successed');
        }
    }

    /**
     * Feed设置
     *
     * @author Garbin
     * @param
     * @return void
     **/
    function feed_settings()
    {
        if (!$this->_feed_enabled)
        {
            $this->show_warning('feed_disabled');
            return;
        }
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                LANG::get('feed_settings'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('feed_settings');
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('feed_settings'));

            $user_feed_config = $this->visitor->get('feed_config');
            $default_feed_config = Conf::get('default_feed_config');
            $feed_config = !$user_feed_config ? $default_feed_config : unserialize($user_feed_config);

            $buyer_feed_items = array(
                'store_created' => Lang::get('feed_store_created.name'),
                'order_created' => Lang::get('feed_order_created.name'),
                'goods_collected' => Lang::get('feed_goods_collected.name'),
                'store_collected' => Lang::get('feed_store_collected.name'),
                'goods_evaluated' => Lang::get('feed_goods_evaluated.name'),
                'groupbuy_joined' => Lang::get('feed_groupbuy_joined.name')
            );
            $seller_feed_items = array(
                'goods_created' => Lang::get('feed_goods_created.name'),
                'groupbuy_created' => Lang::get('feed_groupbuy_created.name'),
            );
            $feed_items = $buyer_feed_items;
            if ($this->visitor->get('manage_store'))
            {
                $feed_items = array_merge($feed_items, $seller_feed_items);
            }
            $this->assign('feed_items', $feed_items);
            $this->assign('feed_config', $feed_config);
            $this->display('member.feed_settings.html');
        }
        else
        {
            $feed_settings = serialize($_POST['feed_config']);
            $m_member = &m('member');
            $m_member->edit($this->visitor->get('user_id'), array(
                'feed_config' => $feed_settings,
            ));
            $this->show_message('feed_settings_successfully');
        }
    }

    /**
     *    三级菜单
     *
     *    @author    Hyber
     *    @return    void
     */
    function _get_member_submenu()
    {
        $submenus =  array(
            array(
                'name'  => 'basic_information',
                'url'   => 'index.php?app=member&amp;act=profile',
            ),
            /*array(
                'name'  => 'edit_password',
                'url'   => 'index.php?app=member&amp;act=password',
            ),
            array(
                'name'  => 'edit_email',
                'url'   => 'index.php?app=member&amp;act=email',
            ),*/
        );
        if ($this->_feed_enabled)
        {
            $submenus[] = array(
                'name'  => 'feed_settings',
                'url'   => 'index.php?app=member&amp;act=feed_settings',
            );
        }

        return $submenus;
    }

    /**
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);

        return $uploader->save(Conf::get('up_dir').'/' . gmtime()."_".$user_id);
    }



    function to_tuiguang() {
        $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
            LANG::get('tuiguang'));
        /* 当前用户中心菜单 */
        $this->_curitem('tuiguang');
        /* 当前所处子菜单 */
        $this->_curmenu('edit_email');
        $this->import_resource(array(
            'script' => 'jquery.plugins/jquery.validate.js',
        ));
        $this->display("member.to_tuiguang.html");
    }

    function kf(){
        $user=$this->visitor->info;
        $this->assign('user' , $user);
        $this->assign('sendToFlexURL', TO_CHOUJIANG_URL);
        $this->display("member.kf.html");
    }

    //抽奖
    function cj()
    {
        $this->_config_seo('title', '在线抽奖');
        $user=$this->visitor->info;
        $user_id = $user['user_id'];

        $ref="I0MDcxM0FDQzVBRjg5OQ=EY145ODlGNDg296N0RCDU233Q".$user_id."ME21NDQxO907DUx";
        if($user["member_type"]!="01"){
            $ref.="&UwMDZGRUU3NEY1OEY4Ng=QTBCOTIzODIwRENDNTA5QQ";
        }else{
            $ref.="&UwMDZGRUU3NEY1OEY4Ng=OTVENTY1RUY2NkU3REZGOQ";
        }

        $siteURL=TO_CHOUJIANG_URL;
        $siteURL=substr($siteURL,7);
        $tmp=explode("/",$siteURL);
        $ref.="&url=".$tmp[0];
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
            LANG::get('tuiguang'));
        /* 当前用户中心菜单 */
        $this->_curitem('cj');
        /* 当前所处子菜单 */
        //$this->_curmenu('cj');
        $this->import_resource(array(
            'script' => 'jquery.plugins/jquery.validate.js',
        ));

        $this->assign('ret_url', rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

        $this->assign('ref', $ref);

        if($user_id!="")
        {
            $user_mod =& m('member');
            $user = $user_mod->get_info($user_id);


            //今天已经抽奖次数
            $user["choujiangCount1"]=$user_mod->getOne("SELECT COUNT(1)  as count1 FROM ".DB_PREFIX."choujiang_detail WHERE user_id=$user_id and choujiang_date=DATE_FORMAT(NOW(),'%Y-%m-%d')");

            //剩余总的抽奖次数
            $count2=$user_mod->getOne("SELECT SUM(100-COUNT) as count2 FROM ".DB_PREFIX."choujiangquan WHERE user_id=$user_id");

            //可用抽奖权数量
            $count3=$user_mod->getOne("SELECT count(0) FROM ".DB_PREFIX."choujiangquan WHERE user_id=$user_id" . " and count < 100 ");

            $user["choujiangCount2"]=($count2=="")?0:$count2;

            $this->assign('count3', $count3);

            $this->assign('tuiguangURL', "/reg/".$user_id.".html");


            $user_api = json_decode($this->user_api("","",""));
            $user["koushui_yue"]=$user_api->koushui_yue;
            $user["jifen"]=$user_api->jifen;
            $this->assign('user', $user);
        }


        $choujiang_mod =& m('choujiang');
        $xiaojiang_tmp     = $choujiang_mod->find  (
            array(
                "conditions"=>"choujiang_detail.jibie>=10 order by choujiang_detail.choujiang_date desc LIMIT 0,10",
                "fields"    =>"this.*,(select u.user_name from ecm_member u where u.user_id=choujiang_detail.user_id) as user_name,
                                                           date_format(choujiang_detail.choujiang_date,'%Y-%m-%d') as choujiang_date,
                                                           (jine*40) as \"jine\""
            ));
        $dajiang_tmp       = $choujiang_mod->find(
            array(
                "conditions"=>"choujiang_detail.jibie>0 AND choujiang_detail.jibie<10 and choujiang_detail.jibie>0 order by choujiang_detail.choujiang_date desc LIMIT 0,6",
                "fields"    =>"this.*,
                                                       (select u.user_name from ecm_member u where u.user_id=choujiang_detail.user_id) as user_name,
                                                       date_format(choujiang_detail.choujiang_date,'%Y-%m-%d') as choujiang_date"
            ));


        $xiaojiang=$xiaojiang_tmp;
        $dajiang=$dajiang_tmp;

        foreach($xiaojiang_tmp as $k=>$v){
            $xiaojiang[$k]["user_name"]=$this->half_replace($v["user_name"]);
            $xiaojiang[$k]["jibie"]=($v["jibie"]-10);
        }

        foreach($dajiang as $k1=>$v1){
            $dajiang[$k1]["user_name"]=$this->half_replace($v1["user_name"]);
        }

        //$this->pr($dajiang);exit;
        $this->assign('xiaojiang' , $xiaojiang);
        $this->assign('dajiang'   , $dajiang);

        $this->assign('sendToFlexURL', TO_CHOUJIANG_URL);

        $jiangpin2_mod =& m('jiangpin2');
        $list=$jiangpin2_mod->find(array(
            "conditions"=>" jibie > 10 and choujiang_date=date_format(now(),'%Y-%m-%d') order by jibie limit 0,5" ,
            "fields"    =>"this.*,(jine*40) as \"jifen\""
        ));
        $this->assign('jiangpin2' , $list);
        $this->assign('TO_CHOUJIANG_URL2' , TO_CHOUJIANG_URL2);

        $this->display("member.cj.html");
    }

    //替换字符串中间的部分
    function half_replace($str){
        if(count(explode("@",$str))>1){
            $val=explode("@",$str);
            if(strlen($val[0])<=3){
                return $val[0]."******@".$val[1];
            }else{
                $v=substr($val[0],0,3);
                return $v."******@".$val[1];
            }
        }
        $c = strlen($str)/2;
        return preg_replace('|(?<=.{'.(ceil($c/2)).'})(.{'.floor($c).'}).*?|',str_pad('',floor($c),'*'),$str,1);
    }

    function qiangzhu()
    {

        $user_id = $this->visitor->get('user_id');

        $this->_curitem('qiangzhu');

        $shenqing_model=& m("serviceShenqing");

        $page = $this->_get_page();

        $conditions=" 1=1 and member_id=$user_id ";

        if($_GET["add_time_from"])
        {
            $add_time_from=strtotime($_GET["add_time_from"]);
            $conditions .= " AND add_time>='$add_time_from'";
        }
        if($_GET["add_time_to"])
        {
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            $conditions .= " AND add_time<='$add_time_to'";
        }

        if($_GET["region_id"]>0)
        {
            $region_id=$_GET["region_id"];
            $conditions .= " AND EXISTS (select 1 from ".DB_PREFIX."member m where m.region_id = $region_id and m.member_type='02' and m.user_id=service_shenqing.service_id and m.dropState=1 ) ";
        }

        if($_GET["status"]!='')
        {
            $status=$_GET["status"];
            $conditions .= " AND type=$status ";
        }

        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'sort_order';
                $order = '';
            }
        }
        else
        {
            $sort  = 'id';
            $order = 'desc';
        }

        $shenqings=$shenqing_model->find(array(
            'conditions' => $conditions,
            'limit' => $page['limit'],
            'fields'=> "this.*,
                            (select region_name from ".DB_PREFIX."member where user_id=serviceShenqing.service_id) as regionName,
                            (select service_jibie from ".DB_PREFIX."service_info where service_id=serviceShenqing.service_id and type=1 and service_jibie!='' limit 0,1) as jibie,
                            (".QIANGZHU_FAIL_TIME."-(".gmtime()."-add_time)) as q_time,
                            (add_time+".QIANGZHU_FAIL_TIME.") as fail_time
                            ",
            'count' => true,
            'order' => "$sort $order"
        ));
        if(count($shenqings)>0){

            foreach($shenqings as $k=>$v){
                $shenqings[$k]["flag"] = 0;          // 默认0未过期
                if($v["type"]==0 &&  $v["q_time"]<=0 && $v["paymentState"]==0){             //先判断为未审核，并且已经超过10小时
                    $shenqings[$k]["flag"] = 1;      //已经过期，可以再次抢注
                }
            }
        }
        /*
        $j=0;
        if(count($shenqings)>0){
            foreach($shenqings as $k=>$v){
                if($v["type"]==0 &&  $v["q_time"]<=0 && $v["paymentState"]==0){             //先判断为未审核，并且已经超过10小时

                        $this->_member_mod->edit($v["service_id"],array("status"=>0));
                        $this->_member_mod->db_query("update ".DB_PREFIX."service_shenqing set type=-1,update_time=".gmtime()." where type=0 and service_id=".$v["service_id"]);
                        $j++;
                }
            }
        }
        if($j>0)
        {
            //这里只重复1次，在页面展示的页面也会做1个判断，if 服务中心状态=1 并且 申请时候超时了10小时 ，ajax 还原回去服务中心状态
            $shenqings=$shenqing_model->find(array(
                'conditions' => $conditions,
                'limit' => $page['limit'],
                'fields'=> "this.*,
                            (select region_name from ".DB_PREFIX."member where user_id=serviceShenqing.service_id) as regionName,
                            (select service_jibie from ".DB_PREFIX."service_info where service_id=serviceShenqing.service_id and type=1 and service_jibie!='' limit 0,1) as jibie,
                            (".QIANGZHU_FAIL_TIME."-(".gmtime()."-add_time)) as q_time,
                            (add_time+".QIANGZHU_FAIL_TIME.") as fail_time
                            ",
                'count' => true,
                'order' => "$sort $order"
            ));
        }*/


        $page['item_count'] =$shenqing_model->getCount();

        $this->_format_page($page);

        $this->assign('page_info', $page);

        if(count($shenqings)>0){
            foreach($shenqings as $k=>$v){
                $service_desc=$v["service_desc"];
                $tmp1=explode("#",$service_desc);
                $tmp2=explode("-",$tmp1[1]);
                $shenqings[$k]["year"]=$tmp2[0];
            }
        }

        //$this->pr($shenqings);
        $this->assign('shenqings', $shenqings);

        $region_mod =& m('region');
        $this->assign('regions', $region_mod->get_options(0));
        $this->assign('TO_QIANGZHU_URL', TO_QIANGZHU_URL);


        $this->_config_seo('title', "我的抢注" . ' - ' . Lang::get('user_center'));

        $this->display("my_qiangzhu.html");
    }
    function yinhangka(){
        //$this->profile();

        $bank_mod =& m('memberbank');
        $userInfo=$this->visitor->info;

        if(!$_POST){
            $page = $this->_get_page();
            $my_banks=$bank_mod->find(array(
                "conditions"=>"user_id=".$userInfo["user_id"],
                'fields'=> "this.*,
                        (select kahao from ".DB_PREFIX."member where user_id=".$userInfo["user_id"].") as default_bank",
                'limit' => $page['limit'],
                'count' => true
            ));
            $page['item_count'] = $bank_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);



            if(count($my_banks)>0){
                foreach($my_banks as $k => $v){
                    $my_banks[$k]["shos_kahao"]=$this->half_replace($v["bank_kahao"]);
                }
            }


            $this->assign('my_banks', $my_banks);

            $this->_curitem('yinhangka');
            $this->display("my_bank.html");
        }else{

            $bank_code=$_POST["bank_code"];
            $bank_kahao=$_POST["my_bank"];
            $default_bank=$_POST["default_bank"];

            $password2=$_POST["password2"];
            $user_name=$_POST["user_name"];

            if($bank_code == "" || $userInfo["user_id"] == "" || $bank_kahao == "" || $password2 == "" || $user_name == ""){
                echo "请选择银行,卡号,姓名,二级密码！";      //不能为空
                exit;
            }

            $user_mod =& m('member');
            $info = $user_mod->get_info($userInfo['user_id']);
            if($info["password2"]=="" || $info["password2"]==null){
                echo "您没有设置二级密码，请先设置！";      //已经存在
                exit;
            }

            if($info["password2"] != md5($password2)){
                echo "二级密码错误，请重新输入！";      //已经存在
                exit;
            }


            $data= $bank_mod->find("user_id='".$userInfo["user_id"]."' and bank_code='$bank_code'");
            if(count($data)<=0){
                echo "操作错误，不存在该银行的卡号！";      //不存在
                exit;
            }

            if($default_bank!="" && $userInfo["user_id"]>0){
                $bank_mod->db_query("update ".DB_PREFIX."member set kahao='$default_bank' where user_id=".$userInfo["user_id"]);
            }

            $bank_mod->db_query("update ".DB_PREFIX."member_bank set bank_kahao='$bank_kahao',user_name='$user_name' where user_id='".$userInfo["user_id"]."' and bank_code='$bank_code'");
            echo "修改成功！";
        }

    }

    function add_yinhangka(){
        $channel_mod =& m('channel');
        $bank_mod =& m('memberbank');
        $userInfo=$this->visitor->info;

        if(!$_POST){
            $page = $this->_get_page();
            $channels=$channel_mod->find(array(
                "conditions"=>"(type=2 or type=3)",
                'fields'=> "this.*,
                                       (select count(1) from ".DB_PREFIX."member_bank b where b.bank_code=channel.channel_code and b.user_id='".$userInfo["user_id"]."') as isbank",
                'limit' => $page['limit'],
                'count' => true
            ));

//            $this->pr($channels);

            $page['item_count'] = $channel_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('channels', $channels);

            $this->_curitem('yinhangka');
            $this->display("add_bank.html");
        }else{
            $bank_code=$_POST["bank_code"];
            $bank_name=$_POST["bank_name"];
            $bank_kahao=$_POST["my_bank"];
            $password2=$_POST["password2"];

            $user_name=$_POST["user_name"];

            if($bank_code == "" || $bank_name == "" || $bank_kahao == "" || $password2 =="" || $user_name ==""){
                echo "请选择银行并且输入卡号和姓名和二级密码！";      //不能为空
                exit;
            }

            $user_mod =& m('member');
            $info = $user_mod->get_info($userInfo['user_id']);

            if($info["password2"]=="" || $info["password2"]==null){
                echo "您没有设置二级密码，请先设置！";      //已经存在
                exit;
            }

            if($info["password2"] != md5($password2)){
                echo "二级密码错误，请重新输入！";      //已经存在
                exit;
            }

            $bank_mod =& m('memberbank');
            $data= $bank_mod->find("user_id='".$userInfo["user_id"]."' and bank_code='$bank_code'");
            if(count($data)>0){
                echo "该银行已经存在对应的卡号，如需更换卡号请到我的银行卡中更换！";      //已经存在
                exit;
            }

            $id=$bank_mod->add(array(
                "user_id"=>$userInfo["user_id"],
                "user_name"=>$user_name,
                "bank_name"=>$bank_name,
                "bank_code"=>$bank_code,
                "bank_kahao"=>$bank_kahao,
                "add_time"=>gmtime()
            ));

            if($id>0){
                echo "新增成功！";
            }else{
                echo "新增失败！";
            }
        }

    }

    function password2()
    {
        if(!$_POST)
        {
            $user = $this->visitor->get();
            $user_mod =& m('member');
            $info = $user_mod->get_info($user['user_id']);
            $flg="add";
            if($info["password2"])
            {
                $flg="edit";
            }
            $info["phone_mob2"]=$this->half_replace($info["phone_mob"]);
            /* 当前用户中心菜单 */
            $this->_curitem('password2');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_password');


            $this->assign('flg', $flg);
            //$this->display('member.password.html');
            $this->assign('info', $info);

            if(FAIL_TIME=="FAIL_TIME" || FAIL_TIME==""){
                $this->assign('FAIL_TIME',61);
            }else{
                $this->assign('FAIL_TIME',FAIL_TIME);
            }

            $this->display('service.password.html');
        }else{
//            $this->pr($_POST);
//            exit;
            /* 两次密码输入必须相同 */
            $orig_password      = $_POST['orig_password'];
            $new_password       = $_POST['new_password'];
            $confirm_password   = $_POST['confirm_password'];
            $verifyCode   = $_POST['verifyCode'];
            $phone_mob   = $_POST['phone_mob'];

            if($orig_password=="" ||  $new_password=="" || $confirm_password=="" ||$verifyCode=="" || $phone_mob==""){
                $this->show_warning('手机手机号码，短信验证码，二级密码不能为空！');
                return;
            }

            @session_start();
            $init_code = $_SESSION["yanzhengma"];
            if($init_code==""){
                $this->show_warning('验证码不存在！');
                return;
            }
            if($init_code!=$verifyCode){
                $this->show_warning('验证码错误！');
                return;
            }

            if ($new_password != $confirm_password)
            {
                $this->show_warning('twice_pass_not_match2');

                return;
            }
            if (!$new_password)
            {
                $this->show_warning('no_new_pass2');

                return;
            }
            $passlen = strlen($new_password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error2');
                return;
            }

            /* 修改密码 */
            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'password2'  => $new_password
            ));
            if (!$result)
            {

                /* 修改不成功，显示原因 */
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->_member_mod->edit($this->visitor->get('user_id'),array("phone_mob"=>"$phone_mob"));
            if($_POST["flg"]="edit"){
                $this->show_message('edit_password_successed2');
            }else{
                $this->show_message('add_password_successed2');
            }

        }
    }

    function tixian(){


        $user = $this->visitor->get();
        $tixian_mod =& m('tixian');
        $bank_mod =& m('memberbank');

        $tixian_log=$tixian_mod->find(array(
            "conditions"=>"user_id=".$user["user_id"]." and add_date=DATE_FORMAT(NOW(),'%Y-%m-%d')"
        ));


        if(count($tixian_log)>0){
            $this->assign('flag', 1);
            echo "<script>js_fail('一天只能提现一次，您今天已提现！');</script>";
        }

        $msm_time = $_SESSION["msm_time"];
        if($msm_time != "" && $msm_time >0 && gmtime() - $msm_time < 120){
            $this->assign('t_time',120-(gmtime() - $msm_time));
        }

        $user_id=$this->visitor->get("user_id");
        $userInfo=$this->_member_mod->get($user_id);

        $my_banks=$bank_mod->find(array(
            "conditions"=>"user_id=".$user["user_id"]." and dropState=1  limit 0,10"
        ));
        $this->assign('my_banks', $my_banks);

        if($userInfo["phone_mob"]>0){
            $userInfo["show_tel"]=$this->half_replace($userInfo["phone_mob"]);
        }


        $channel_mod =& m('channel');
        $channels=$channel_mod->find(array(
            "conditions"=>"(type=2 or type=3) and status=1",
            'limit' => 50,
            'count' => true
        ));

        $this->assign('channels', $channels);

        if(FAIL_TIME=="FAIL_TIME" || FAIL_TIME==""){
            $this->assign('FAIL_TIME',61);
        } else {
            $this->assign('FAIL_TIME',FAIL_TIME);
        }


        $user_api = json_decode($this->user_api("","",""));

//        $userInfo["yue"]=$user_api['yue'];
        if($_GET['p'] == 'koushui') {
            $userInfo["koushui_yue"]=$user_api->koushui_yue;
        } else{
            $userInfo["unkoushui_yue"]=$user_api->unkoushui_yue;
        }
        $this->assign('userInfo', $userInfo);
        if(!$_POST){

            $this->import_resource(array(
                'script' => array(
                    array(
                        'path' => 'dialog/dialog.js',
                        'attr' => 'id="dialog_js"',
                    ),
                    array(
                        'path' => 'jquery.ui/jquery.ui.js',
                        'attr' => '',
                    ),
                    array(
                        'path' => 'jquery.plugins/jquery.validate.js',
                        'attr' => '',
                    ),
                ),
                'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css,res:jqtreetable.css',
            ));
            $this->display('service.tixian.html');
        }else{

            if( empty($_POST['channel_name']) || $_POST['channel_name']=="" ||
                empty($_POST['channel_card'])       || $_POST['channel_card']=="" ||
                empty($_POST['user_name'])   || $_POST['user_name']=="" ||
                empty($_POST['jine']) || $_POST['jine']=="" ||
                empty($_POST['password2']) || $_POST['password2']==""
//                empty($_POST['yanzhengma']) || $_POST['yanzhengma']==""
                || empty($_POST['shenfenzheng']) || $_POST['shenfenzheng']==""
            ) {
                $this->pop_warning(Lang::get('tixian_data_null'));
                exit;
            }
//            // 验证码
//            if ($_SESSION['yanzhengma'] != $_POST['yanzhengma'])
//            {
//                $this->pop_warning(Lang::get('captcha_error'));
//                exit;
//            }

//            $store_mod =& m('store');
//
//            $store = $store_mod->get($user_id);
//            if ($store && $store['tuoguan'] == 1 ) {
//                echo "<script>js_fail('托管商家不能提现！');</script>";
//            }
            //二级密码
            $user=$this->_member_mod->get_info($user["user_id"]);
            if($user["password2"]=="" ||  $user["password2"]==null){
                $this->pop_warning(Lang::get('password2_null'));
                exit;
            }

            if($user["password2"]!=md5($_POST["password2"])){
                $this->pop_warning(Lang::get('password2_error'));
                exit;
            }

            //余额
            /*$tixian_jine=$_POST["jine"];
            if($user["yue"]<$tixian_jine){
                $this->pop_warning(Lang::get('tixian_jine_error'));
                exit;
            }

            $channel_names=explode("##",$_POST['channel_name']);
            $channel_code=$channel_names[0];

            $user_id=$user["user_id"];
            $channel_mod =& m('channel');
            $data2= $channel_mod->get("channel_code='$channel_code' and (type=2 or type=3) and status=1");


            if($data2["channel_id"]==null || $data2["channel_id"]<=0){
                $this->pop_warning(Lang::get('channel_error'));
                exit;
            }


            date_default_timezone_set('Asia/Shanghai');
            $sava_data=array(
                "user_id"=>$user["user_id"],
                "user_name"=>$_POST["user_name"],
                "channel_id"=>$data2["channel_id"],
                "channel_name"=>$data2["channel_name"],
                "channel_card"=>$_POST["channel_card"],
                "jine"=>$tixian_jine,
                "status"=>0,
                "add_time"=>date('Y-m-d H:i:s'),
                "add_date"=>date('Y-m-d')
            );
            $id=$tixian_mod->add($sava_data);
            if($id>0){

                $this->_member_mod->db_query("update ".DB_PREFIX."member set yue=yue-$tixian_jine where user_id=$user_id");  //修改余额

                $user=$this->_member_mod->get_info($user_id);
                $data=array("user_id"=>$user_id,
                    "jine"=>(0-$tixian_jine),
                    "beizhu"=>"申请提现：历史余额为".($user["yue"]+$tixian_jine).",最新余额为".$user["yue"].",本次余额变动金额为".$tixian_jine,
                    "create_time"=>date('Y-m-d H:i:s',time()),
                    "type"=>3
                );
                $model_yuelog =& m('yuelog');
                $model_yuelog->add($data);              //记录余额log

            }

            $mybans=$bank_mod->find("user_id=$user_id and user_name='".$_POST["user_name"]."' and bank_name='".$data2["channel_name"]."' and bank_code='".$data2["channel_code"]."'
                        and bank_kahao='".$_POST["channel_card"]."'");

            if(count($mybans)==0){
                $bank_mod->add(array(
                                      "user_id"=>$user_id,
                                      "user_name"=>$_POST["user_name"],
                                      "bank_name"=>$data2["channel_name"],
                                      "bank_code"=>$data2["channel_code"],
                                      "bank_kahao"=>$_POST["channel_card"],
                                      "add_time"=>gmtime()
                ));
            }*/

            $tixian_jine=$_POST["jine"];

            $channel_names=explode("##",$_POST['channel_name']);
            $channel_code=$channel_names[0];

            $channel_mod =& m('channel');
            $data2= $channel_mod->get("channel_code='$channel_code' and (type=2 or type=3) and status=1");


            if($data2["channel_id"]==null || $data2["channel_id"]<=0){
                $this->pop_warning(Lang::get('channel_error'));
                exit;
            }

            $params["type"] = "tixian";
            $params["userId"] = $this->visitor->get('user_id');

            $user = $this->visitor->get();

            $params["userName"] =$_POST["user_name"];
            $params["channelCode"] =$data2["channel_code"];
            $params["channelName"] =$data2["channel_name"];
            $params["channelCard"] =$_POST["channel_card"];
            $params["jine"] =$tixian_jine;

            $params["orderSn"] ="";
            $params["tId"] ="0";
            $params["regionId"] ="0";
            $params["shenfenzheng"] = $_POST["shenfenzheng"];

            $params["tixianType"] =$_POST["tixianType"];
            $params["kaihuhang"] =$_POST["kaihuhang"];
//            $this->pr($params["tixianType"]);exit;

            $Client = new HttpClient(JAVA_LOCATION);
            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;

            $pageContents = HttpClient::quickPost($url, $params);
            //echo $pageContents;
            $data = json_decode($pageContents);

            header("Content-Type:text/html;charset=utf-8");
            if(!$data ||  $data->code != 200 ){
                echo $pageContents;
                echo "<script>alert('提现申请失败，服务器繁忙，请稍后重试【".$data->code."】！');</script>";
                $this->pop_warning('ok','tixian_page');
            }else{
                echo "<script>alert('提现申请成功！');</script>";
                $mybans=$bank_mod->find("user_id=$user_id and user_name='".$_POST["user_name"]."' and  bank_code='".$data2["channel_code"]."' and kahao='".$_POST["channel_card"]."'");

                if(count($mybans)==0){
                    $bank_mod->add(array(
                        "user_id"=>$this->visitor->get('user_id'),
                        "user_name"=>$_POST["user_name"],
                        "bank_name"=>$data2["channel_name"],
                        "bank_code"=>$data2["channel_code"],
                        "kahao"=>$_POST["channel_card"],
                        "add_time"=>date('Y-m-d H:i:s',time())
                    ));
                }
                $this->pop_warning('ok','tixian_page');
            }

        }

    }

    /*
    function show_bank(){
        $channel_name=$_GET["channel_name"];
        $channel_mod =& m('channel');
        $channels=$channel_mod->find(array(
            "conditions"=>"(type=2 or type=3) and channel_name like '%$channel_name%'",
            'fields'=> "this.*",
            'limit' => 2,
            'count' => true
        ));

    }*/


    //安全中心
    function aqzx(){
        if(!$_POST){
            $this->_curitem('aqzx');
            $user_id=$this->visitor->get('user_id');
            $user=$this->_member_mod->get_info($user_id);

            if(!empty($user["phone_mob"]) && $user["phone_mob"]>0){
                $user["show_tel"]=$this->half_replace($user["phone_mob"]);
            }
            if(!empty($user["email"]) && $user["email"]!=""){
                $user["show_email"]=$this->half_replace($user["email"]);
            }

            $this->assign('user',$user);

            $this->_config_seo('title', "安全中心" . ' - ' . Lang::get('user_center'));

            $this->display('service.aqzx.html');
        }
    }

    function bank(){
        /*查看是否绑定银行卡*/
        $this->_curitem('bank');
        $member_bank = & m('memberbank');
        $bank = $member_bank -> find(' dropState=1 and user_id = ' . $this->visitor->get('user_id'));
        $flag = true;
        if(count($bank) > 0){
            $flag = false;
        }
        $user_id=$this->visitor->get('user_id');
        $user=$this->_member_mod->get_info($user_id);
        $this->assign('user',$user);
        $this->assign('flag', $flag);
        $this->assign('bank',$bank);

        $this->_config_seo('title', "银行卡管理" . ' - ' . Lang::get('user_center'));

        $this -> display('member.bank.html');
    }

    //安全中心
    function aqzx_manager(){
        $this->_curitem('aqzx');
        $user_id=$this->visitor->get('user_id');
        if($user_id<=0){
            $this->display('service.manager_step1.html');
            echo "<script>js_fail(\"请先登入\");</script>";
            exit;
        }
        $user=$this->_member_mod->get_info($user_id);

        if(FAIL_TIME=="FAIL_TIME" || FAIL_TIME==""){
            $this->assign('FAIL_TIME',61);
        } else {
            $this->assign('FAIL_TIME',FAIL_TIME);
        }
        if($user["email"]!=""){
            $user["show_email"]=$this->half_replace($user["email"]);
        }
        if($user["phone_mob"]!=""){
            $user["show_phone_mob"]=$this->half_replace($user["phone_mob"]);
        }
        $user_id=$this->visitor->get('user_id');

        $msm_time = $_SESSION["msm_time"];
        if($msm_time != "" && $msm_time >0 && gmtime() - $msm_time < 120){
            $this->assign('t_time',120-(gmtime() - $msm_time));
        }
        if(!$_POST && $user['im_qq']=="" && $user['im_qq']==null){
            $this->assign('user',$user);
            $this->display('service.manager_step1.html');
        }else{

            if($user['im_qq']!="" && $user['im_qq']!=null) {
                $type=$_GET["type"];
                $step="2";
            } else {
                $type=$_POST["type"];
                $step=$_POST["step"];
                $this->assign('post_data',$_POST);
            }

            $this->assign('user',$user);
            //$this->pr($_POST);
            //第一步验证密码
            //$this->pr($_POST);
            if($step==1){
                $password=$_POST["password"];
                $_POST["step"]="1";

                if($user["password"]!=md5($password)){
                    $this->display('service.manager_step1.html');
                    echo "<script>$('#password_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">密码错误</label>')</script>";
                    exit;
                }
                $captcha=$_POST["captcha"];

                $session_captcha = base64_decode($_SESSION["captcha"]);
                if($session_captcha == "" || $captcha == "" ){
                    $this->display('service.manager_step1.html');
                    $_POST["step"]="1";
                    echo "<script>$('#captcha_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">验证码不能为空</label>')</script>";
                    exit;
                }

                if($captcha != $session_captcha){
                    $this->display('service.manager_step1.html');
                    $_POST["step"]="1";
                    echo "<script>$('#captcha_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">验证码错误</label>')</script>";
                    exit;
                }

                $_POST["step"]="2";
                $this->display("service.manager_$type.html");
                exit;
            }else if($step==2){
                if($type=="changerpassword"){
                    $new_password=$_POST["new_password"];
                    $confirm_password=$_POST["confirm_password"];
                    if($new_password!=$confirm_password){
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$('#confirm_password_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">二次密码不一致</label>')</script>";
                        exit;
                    }

                    $id=$this->_member_mod->edit($user_id,array("password"=>md5($new_password)));

                    if($id>0){
                        $this->display('member.changer_sucess.html');
                    }else{
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$('#new_password_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">修改失败，旧密码和新密码不能相同!</label>')</script>";
                        exit;
                    }
                }elseif($type=="changeremail"){
                    include_once(ROOT_PATH . '/app/sendcode.app.php');
                    /*if($user['im_qq']!="" && $user['im_qq']!=null) {
                        if  ($_POST["new_email"]) {
                            $email=$_POST["new_email"];      //发送到旧的邮箱完成绑定
                            $email_active_code = base64_encode(gmtime() . $email);
                            $update_email=$email;
                        } else {
                            $_POST["step"]="2";
                            $this->display("service.manager_$type.html");
                        }
                    } else {*/
                    $email=$_POST["email"];
                    $new_email=$_POST["new_email"];
                    $email_active_code="";
                    $email_active_code = base64_encode(gmtime() . $email);
                    $update_email=$email;
                    if ($new_email != null) {

                        $list=$this->_member_mod->find("email='$new_email' and user_id!=$user_id");
                        if(count($list)>0){
                            $this->display("service.manager_$type.html");
                            $_POST["step"]="2";
                            echo "<script>$('#new_email_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">设置邮箱失败，邮箱已被占用！</label>')</script>";
                            exit;
                        }

                        $email=$user["email"];      //发送到旧的邮箱完成绑定
                        $email_active_code = base64_encode(gmtime() . $new_email);
                        $update_email=$new_email;
                    }else{
                        $list=$this->_member_mod->find("email='$email' and user_id!=$user_id");
                        if(count($list)>0){
                            $this->display("service.manager_$type.html");
                            $_POST["step"]="2";
                            echo "<script>$('#email_lbl').html('<label class=\"error\" for=\"captcha\" generated=\"true\">设置邮箱失败，邮箱已被占用！</label>')</script>";
                            exit;
                        }
                    }

                    // }
                    //发送绑定邮件

                    $content = file_get_contents(ROOT_PATH . "/themes/mall/default/regist_email_content.html");
                    $content = str_replace("%s1", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
                    $content = str_replace("%s2", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
                    $sender = new SendCodeApp();
                    $sender -> send_email($email, "欢迎绑定邮箱到大集客", $content);

                    $id = $this->_member_mod->edit($user_id,array("email"=>$update_email,"email_bind_status"=>0,"email_active_code"=>$email_active_code));

                    $arr = explode("@", $email);
                    $this->assign("email_domain", $arr[1]);
                    $this->display("member.changer_sucess.html");

                }elseif($type=="changerphonemob"){

                    /*if($user['im_qq']!="" && $user['im_qq']!=null) {
                        if  ($_POST["phone_mob"]) {
                            $phone_mob=$_POST["phone_mob"];
                            $mob_type = "phone_mob";
                        } else {
                            $_POST["step"]="2";
                            $this->display("service.manager_$type.html");
                        }
                    } else {*/
                    $new_phone_mob=$_POST["new_phone_mob"];
                    $phone_mob=$_POST["phone_mob"];

                    $mob_type = "phone_mob";

                    if($new_phone_mob!=""){
                        $phone_mob=$new_phone_mob;
                        $mob_type = "new_phone_mob";
                    }
                    // }
                    $list=$this->_member_mod->find("(phone_mob='$phone_mob' or user_name='$phone_mob') and user_id!=$user_id");
                    if(count($list)>0){
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$(\"#{$mob_type}_lbl\").html('<label class=\"error\" for=\"captcha\" generated=\"true\">设置手机号码失败，手机号码已被占用！</label>')</script>";
                        exit;
                    }
                    $id=$this->_member_mod->edit($user_id,array("phone_mob"=>$phone_mob,"phone_mob_bind_status"=>1));
                    if($id>0){
                        $_SESSION["yanzhengma"]="";
                        $_SESSION["msm_time"]=0;
                        $this->display("member.changer_sucess.html");
                    }else{
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$(\"#{$mob_type}_lbl\").html('<label class=\"error\" for=\"captcha\" generated=\"true\">设置手机号码失败！</label>')</script>";
                        exit;
                    }
                }elseif($type=="changerpassword2"){
                    $yanzhengma=$_POST["yanzhengma"];
                    $password2=$_POST["password2"];
                    $confirm_password2=$_POST["confirm_password2"];
                    $init_code = $_SESSION["yanzhengma"];
                    if($yanzhengma == "" || $init_code == "" ){
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$(\"#yanzhengma_lbl\").html('<label class=\"error\" for=\"captcha\" generated=\"true\">验证码不能为空！</label>')</script>";
                        exit;
                    }
                    if($yanzhengma != $init_code){
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$(\"#yanzhengma_lbl\").html('<label class=\"error\" for=\"captcha\" generated=\"true\">验证码错误！</label>')</script>";
                        exit;
                    }
                    if($password2!=$confirm_password2){
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$(\"#confirm_password2_lbl\").html('<label class=\"error\" for=\"captcha\" generated=\"true\">二级密码和确认密码不相同！</label>')</script>";
                        exit;
                    }

                    $id=$this->_member_mod->edit($user_id,array("password2"=>md5($password2)));
                    if($id>0){
                        $_SESSION["yanzhengma"]="";
                        $_SESSION["msm_time"]=0;
                        $this->display("member.changer_sucess.html");
                    }else{
                        $this->display("service.manager_$type.html");
                        $_POST["step"]="2";
                        echo "<script>$(\"#password2_lbl\").html('<label class=\"error\" for=\"captcha\" generated=\"true\">新支付密码不能和旧支付密码相同！</label>')</script>";
                        exit;
                    }

                }
            }
        }



    }

    //密保问题绑定
    function aqzx_question(){
        $this->_curitem('aqzx');
        $user_id=$this->visitor->get('user_id');
        $this->assign('questions',Conf::get('questions'));
        $user=$this->_member_mod->get_info($user_id);
        $type = $_GET['type'];
        $questions = array();
        if(!IS_POST) {
            if($user['question']) {
                $qu = ecm_json_decode($user['question'],'1');
                foreach ($qu[$user_id]['name'] as $key=>$value) {
                    $qu[$user_id]['name'][$key]=urldecode($value);
                }
                $this->assign('qu',$qu);
            }
            $this->assign('user',$user);
            $this->display("member.aqzx_question_form.html");
        } else {
            $question = $_POST['question'];
            $answer = $_POST['answer'];
            if ($question) {
                foreach ($question as $key=>$q) {
                    $questions[$user_id]['name'][$key]=urlencode($q);
                    $questions[$user_id]['answer'][$key]= urlencode($answer[$key]);
                }
            } else  {
                $this->show_message('您没有设置密保问题','','index.php?app=member&act=aqzx&p=userInfo');
                exit;
            }

            $json_questions = ecm_json_encode($questions);

            $this->_member_mod->edit($user_id,array("question"=>$json_questions));
            if($type=='bank') {
                $this->show_message('设置成功','','index.php?app=member&act=aqzx&p=userInfo');
            } else {
                $this->show_message('修改成功','','index.php?app=member&act=aqzx&p=userInfo');
            }

        }
    }
    //
    function  aqzx_mob_bind(){

        $user_id=$this->visitor->get('user_id');
        $_GET["type"]="changerphonemob";
        $this->_curitem('aqzx');
        if($_POST){
            $type=$_POST["type"];
            $post_yanzhengma=$_POST["yanzhengma"];
            $session_yanzhengma=$_SESSION["yanzhengma"];
            if($type == "changerphonemob" && $post_yanzhengma==$session_yanzhengma){

                $id=$this->_member_mod->edit($user_id,array("phone_mob_bind_status"=>1));
                if($id>0){
                    $_SESSION["yanzhengma"]="";
                    $this->display("member.changer_sucess.html");
                }else{
                    $this->display('service.manager.html');
                    $_POST["step"]="2";
                    echo "<script>js_fail(\"验证手机手机号码失败！\");</script>";
                    exit;
                }
            }
        }
    }

    function generate_code($len = 4)
    {
        $chars = '123457acefhkmprtvwxy';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
            $arr[$i] = $chars[$i];
        }
        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }

    function qq_login(){
        if (!$_GET['red']) {
            $red_url = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "&red=1";
            //header("Location: " . $red_url);
            echo "<script>document.location.href='".$red_url."';</script>";
            exit;
        }
        $data=$_GET["data"];

        if($data==""){
            exit("qq号码错误！");
        }
        $data=base64_decode(str_replace(" ","+",$data));
        $data=explode("#####",$data);

        $qq       = $data[0];
        $openid = $data[1];
        echo "<meta charset='utf-8' />";
        if($qq=="" || $openid == ""){
            exit("qq号码错误！");
        }
        $user_name=  $openid;
        $user= $this->_member_mod->get("im_qq='$openid'");
        $user_id=$user["user_id"];

        $spreader_type=null;
        $shop_name=null;
        $cookie = ecm_getcookie('qq_apply_jike_shop');
        $mobile_qq_cookie = ecm_getcookie('mobile_qq_apply');

        if($cookie == 1 || $mobile_qq_cookie==1){
            $spreader_type=1;
            $shop_name=$qq;
        }

        $t_id = $_POST['t_id'];
        if (!$t_id || $t_id == null || $t_id == "") {
            $t_id = $_SESSION['t_id'];
        }
        if (!$t_id || $t_id == null || $t_id == "") {
            $t_id = ecm_getcookie("t_id");
        }
        if ($t_id && $t_id != null && $t_id != "") {
            if(!is_numeric($t_id)){
                $t_id =base64_decode($t_id);
            }
            $dateInfo = $this->_member_mod->get_info($t_id);
            if (!empty($dateInfo) && $dateInfo["member_type"] == "01") {
                $t_id = $dateInfo["user_id"];
            }else{
                $t_id = null;
            }
        }

        //不存在该用户
        if($user_id<=0){
            $data = array(
                "user_name" => $user_name,
                //"password " => md5(time().$user_name),
                "password " =>"",       //QQ注册的密码设置为字符串空！
                "reg_time" =>gmtime(),
                "member_type"=>"01",
                "im_qq"       =>$openid,
                "nick_name"  =>$qq,
                "shop_name"  =>$shop_name,
                "spreader_type"=>$spreader_type,
                "t_id"=>$t_id
            );
            //生成大集客会员
            $user_id=$this->_member_mod->add($data);
            if($user_id>0){
                $this->_member_mod->commit_transaction();
                $params = null;
                $params["type"] = "regist";
                $params["userId"] = $user_id;
                $params["tId"] = ($t_id != '') ? $t_id : '';
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
                //cookie==1表示申请集客小店
                if($cookie == 1){
                    $this->_member_mod->edit($user_id,array('shop_name'=>$shop_name,"spreader_type"=>1));
                    ecm_setcookie("qq_apply_jike_shop", 0, time() + 999999);
                    if(strstr(site_url(),'test')){
                        header("Location: {$site_url}/index.php?app=jkxd_portal&act=my_jkxd&id=$user_id&type=apply_success");
                    }else{
                        header("Location: http://www.".$user_id.".dajike.com?type=apply_success");
                    }
                    //注册成功页面
                //mobile_qq_cookie==1表示手机版申请集客小店
                }elseif($mobile_qq_cookie ==1){
                    //生成集客小店
                    $this->_member_mod->edit($user_id,array('shop_name'=>$shop_name,"spreader_type"=>1));
                    ecm_setcookie("mobile_qq_apply", 0, time() + 999999);
                    if(strstr(site_url(),'test')){
                        header("Location: {$site_url}/weixin/index.php?app=jkxd_portal&act=my_jkxd&id=$user_id&type=apply_success");
                    }else{
                        header("Location: http://www.".$user_id.".dajike.com?mobile=1&type=apply_success");
                    }
                    //注册成功页面
                }else{
                    header("Location: index.php?app=member&act=register_success");
                }

            }

            // QQ 第 n 次登录( n > 1 )
        }else{
            $this->_do_login($user_id);

            //cookie==1表示申请集客小店
            if($cookie == 1){
                //申请集客小店
                if($user && ($user['spreader_type'] !=1 || $user['shop_name'] == null || $user['shop_name'] == "")){
                     $this->_member_mod->edit($user_id,array('shop_name'=>$shop_name,"spreader_type"=>1));
                }
                ecm_setcookie("qq_apply_jike_shop", 0, time() + 999999);
                if(strstr(site_url(),'test')){
                    header("Location: {$site_url}/index.php?app=jkxd_portal&act=my_jkxd&id=$user_id&type=apply_success");
                }else{
                    header("Location: http://www.".$user_id.".dajike.com?type=apply_success");
                }
            //mobile_qq_cookie==1表示手机版申请集客小店
            }elseif($mobile_qq_cookie ==1){
                //判断是否申请过集客小店
                if($user && ($user['spreader_type'] !=1 || $user['shop_name'] == null || $user['shop_name'] == "")){
                    $this->_member_mod->edit($user_id,array('shop_name'=>$shop_name,"spreader_type"=>1));
                }
                ecm_setcookie("mobile_qq_apply", 0, time() + 999999);
                if(strstr(site_url(),'test')){
                    header("Location: {$site_url}/weixin/index.php?app=jkxd_portal&act=my_jkxd&id=$user_id&type=apply_success");
                }else{
                    header("Location: http://www.".$user_id.".dajike.com?mobile=1&type=apply_success");
                }
                //注册成功页面
            }else{
                header("Location: index.php");
            }
        }
    }

    function check_password(){
        $password = $_GET["password"];
        if (!$password)
        {
            echo ecm_json_encode(false);
            return ;
        }
        $user = $this->visitor->get();
        $password = md5($password);
        if($this->_member_mod->get("user_id=".$user["user_id"]." and password = '$password' ")){
            echo ecm_json_encode(true);
            return ;
        }else{
            echo ecm_json_encode(false);
            return ;
        }
    }

    function check_region(){
        $region_id = $_GET["region_id"];
        if (!$region_id){
            echo ecm_json_encode(false);
            return ;
        }
        $region_mod =& m('region');
        if( count($region_mod->get_options($region_id)) ==0 ){
            echo ecm_json_encode(true);
            return ;
        }else{
            echo ecm_json_encode(false);
            return ;
        }
    }

    function save_mob(){
        $phone_mob = $_POST["phone_mob"] ? $_POST["phone_mob"]:$_GET["phone_mob"];
        $yanzhengma = $_POST["yanzhengma"] ? $_POST["yanzhengma"]:$_GET["yanzhengma"];
        $init_code = $_SESSION["yanzhengma"];
        $user_id=$this->visitor->get('user_id');
        //uid不能为空
        if($user_id == "" || $user_id <=0){

            echo ecm_json_encode(false);
            return ;
        }
        //手机号码，验证码不能为空，session验证码不能为空
        if($phone_mob == "" || $yanzhengma == "" || $init_code == ""){

            echo ecm_json_encode(false);
            return ;
        }
        //验证码必须相等
        if($init_code != $yanzhengma){

            echo ecm_json_encode(false);
            return ;
        }
        //手机号码必须未使用
        if($this->_member_mod->get("phone_mob='$phone_mob'")){
            //$this->pr($_POST);
            echo ecm_json_encode(false);
            return ;
        }

        if($this->_member_mod->edit($user_id,array("phone_mob"=>$phone_mob,"phone_mob_bind_status"=>1))){
            echo ecm_json_encode(true);
            return ;
        }

        echo ecm_json_encode(false);
        return ;

    }

    function bankform(){
        $channel_mod =& m('channel');
        $channels=$channel_mod->find(array(
            "conditions"=>"(type=2 or type=3)",
            'fields'=> "this.*"
        ));
        $user_id = $this->visitor->get('user_id');
        $member_model = & m('member');
        $member = $member_model -> get($user_id);
        if($member['password2'] == ''){
            $flag = '1';
            $this -> assign('flag',$flag);
        }
        $this -> assign('channels',$channels);
        $order_type     =&  ot('normal');
        $this->assign('regions',$order_type->get_regions());
        $this -> display('member.bank.form.html');
    }

    function bindbank(){
        header('Content-type: text/json');
        $user_id = $this->visitor->get('user_id');
        $member_bank_model = & m('memberbank');
        $channel_id = $_POST['channel_id'];
        $region_id = $_POST['region_id'];
        $region_name = $_POST['region_name'];
        $user_name = $_POST['user_name'];
        $bank_code = $_POST['bank_code'];
        $default = $_POST['default'];
        $kaihuhang = $_POST['kaihuhang'];
        $channel_model = & m('channel');
        $channel = $channel_model -> get($channel_id);
        if($channel == ''){
            echo json_encode(array("flag"=>"no"));;
            exit;
        }
        if($user_id == ''|| $user_id < 0){
            echo json_encode(array("flag"=>"login"));
            exit;
        }
        $count = count($member_bank_model -> find("kahao = " . $bank_code));
        if($count > 0){
            echo json_encode(array("flag"=>"error"));;
            exit;
        }
        if($channel["channel_code"] == "ZHIFUBAO"){
            if($channel_id == '' || $user_name == '' || $bank_code == '' || $channel_id == ''){
                echo json_encode(array("flag"=>"null"));
                exit;;
            }
        }else{
            if($channel_id == '' || $region_id == '' || $region_name == '' || $user_name == '' || $bank_code == '' || $channel_id == ''){
                echo json_encode(array("flag"=>"null"));
                exit;;
            }
        }

        if($default == 'true'){
            $member_bank_model -> edit("user_id = " . $user_id , "if_default = " . 0);
            $data = array(
                'user_id' => $user_id,
                'bank_name' => $channel['channel_name'],
                'bank_code' => $channel['channel_code'],
                'region_id' =>  $region_id,
                'region_name' => $region_name,
                'user_name' => $user_name,
                'kahao' => $bank_code,
                'kaihuhang' => $kaihuhang,
                'if_default' => 1,
                'add_time' => date('y-m-d',time()),
            );
        }else{
            $data = array(
                'user_id' => $user_id,
                'bank_name' => $channel['channel_name'],
                'bank_code' => $channel['channel_code'],
                'region_id' =>  $region_id,
                'region_name' => $region_name,
                'user_name' => $user_name,
                'kahao' => $bank_code,
                'kaihuhang' => $kaihuhang,
                'if_default' => 0,
                'add_time' => date('Y-m-d',time()),
            );
        }
        $member_bank_model -> add($data);
        echo json_encode(array("flag"=>"ok"));
        return;

    }

    function deletebank(){
        $password2 = $_POST['code'];
        $kahao = $_POST['kahao'];
        $user_id = $this->visitor->get('user_id');
        $member_model = & m('member');
        $member = $member_model -> get($user_id);
        if  ($member['password2']!= md5($password2)) {
            echo json_encode(array("flag"=>"支付密码不正确！"));
            return;
        } else{
            $member_bank_model = & m('memberbank');
            $user_id = $this->visitor->get('user_id');
            $sql = " update ecm_member_bank set dropState =0 where user_id = ". $user_id . " and kahao = '".$kahao ."'";
            $member_bank_model -> db_query($sql);
            echo json_encode(array("flag"=>"ok"));
            return;
        }

    }

    function setdefault(){
        $code = $_POST['kahao'];
        $member_bank_model = & m('memberbank');
        $user_id = $this->visitor->get('user_id');
        $member_bank_model -> edit("user_id = " . $user_id , "if_default = " . 0);
        $member_bank_model -> edit("user_id = " . $user_id . " and kahao = ".$code , "if_default = " . 1);
        echo json_encode(array("flag"=>"ok"));
        return;
    }

    function verification_phone(){
        $code = $_GET['code'];
        if($code == ''){
            $code = $_POST['code'];
        }
        $user_id = $this->visitor->get('user_id');
        $member_model = & m('member');
        $member = $member_model -> get($user_id);
        if($member['phone_mob'] == '' || $member['phone_mob_bind_status'] != 1){
            $flag = '1';
            $this -> assign("flag",$flag);
        }
        $this -> assign("code",$code);
        $this -> assign("user",$member);
        $this -> display("member.verificationphone.bank.html");
    }

    function send_msm($tel,$code){
        //echo "发送成功！";
        $smsclient = new SMSClient();
        $rs=$smsclient->sendSMS($tel,"您本次操作的验证码为".$code."（大集客客服绝不会索取此验证码，切勿告知他人），如果不是本人操作，请自动忽略。【大集客】");

        if($rs["errorno"]==0){
            $_SESSION['yanzhengma'] = $code;
            echo "验证码发送成功！";
        }else{
            echo "发送失败！";
            $_SESSION["msm_time"]=0;
        }
    }

//    function send_code(){
//
//    }

    function verification_phone_delete(){
        $code = $_GET['code'];
        if($code == ''){
            $code = $_POST['code'];
        }
        $user_id = $this->visitor->get('user_id');
        $member_model = & m('member');
        $member = $member_model -> get($user_id);
        if($member['phone_mob'] == '' || $member['phone_mob_bind_status'] != 1){
            $flag = '1';
            $this -> assign("flag",$flag);
        }
        $this -> assign("code",$code);
        $this -> assign("user",$member);
        $this -> display("member.verificationphone.deletebank.html");
    }

    function yzcode(){


        $password2 = $_POST['code'];
        $user_id = $this->visitor->get('user_id');
        $member_model = & m('member');
        $member = $member_model -> get($user_id);
//        $bank_code = $_SESSION['delete_bank_code'];
        if  ($member['password2']!= md5($password2)) {
            echo json_encode(array("flag"=>"支付密码不正确！"));
            return;
        } else{
            echo json_encode(array("flag"=>"ok"));
            return;
        }
    }

    function update(){
        $code = $_GET['code'];
        if($code == ''){
            $code = $_POST['code'];
        }
        $user_id = $this->visitor->get('user_id');
        $channel_model = & m('channel');
        $member_bank_model = & m('memberbank');
        $member_bank = $member_bank_model -> get("user_id = " . $user_id . " and kahao = " . $code);
        $channel = $channel_model -> get("channel_code = '" . $member_bank['bank_code'] . "'");
        $this -> assign("bank",$member_bank);
        $this -> assign("channel1",$channel);
        $channels = $channel_model->find(array(
            "conditions"=>"(type=2 or type=3)",
            'fields'=> "this.*"
        ));
        $this -> assign('channels',$channels);
        $order_type     =&  ot('normal');
        $this->assign('regions',$order_type->get_regions());
        $this -> display("member.update.bank.form.html");
    }

    function updatebank(){
        header('Content-type: text/json');
        $user_id = $this->visitor->get('user_id');
        $member_bank_model = & m('memberbank');
        $id = $_POST['id'];
        $channel_id = $_POST['channel_id'];
        $region_id = $_POST['region_id'];
        $region_name = $_POST['region_name'];
        $user_name = $_POST['user_name'];
        $bank_code = $_POST['bank_code'];
        $default = $_POST['default'];
        $kaihuhang = $_POST['kaihuhang'];
        $channel_model = & m('channel');
        $channel = $channel_model -> get($channel_id);
        if($channel == ''){
            echo json_encode(array("flag"=>"no"));;
            return;
        }
        if($user_id == ''|| $user_id < 0){
            echo json_encode(array("flag"=>"login"));
            return;
        }
        $bank = $member_bank_model -> find("kahao = " . $bank_code);
        if(count($bank) > 0){
            foreach($bank as $bk){
                if($bk['id'] != $id){
                    echo json_encode(array("flag"=>"error"));;
                    return;
                }
            }
        }
        if($channel_id == '' || $region_id == '' || $region_name == '' || $user_name == '' || $bank_code == '' || $channel_id == ''){
            echo json_encode(array("flag"=>"null"));
            return;
        }else{
            if($default == 'true'){
                $member_bank_model -> edit("user_id = " . $user_id , "if_default = " . 0);
                $data = array(
                    'user_id' => $user_id,
                    'bank_name' => $channel['channel_name'],
                    'bank_code' => $channel['channel_code'],
                    'region_id' =>  $region_id,
                    'region_name' => $region_name,
                    'user_name' => $user_name,
                    'kahao' => $bank_code,
                    'kaihuhang' => $kaihuhang,
                    'if_default' => 1,
                    'update_time' => date('y-m-d',time()),
                    'dropState'=>1,
                );
            }else{
                $data = array(
                    'user_id' => $user_id,
                    'bank_name' => $channel['channel_name'],
                    'bank_code' => $channel['channel_code'],
                    'region_id' =>  $region_id,
                    'region_name' => $region_name,
                    'user_name' => $user_name,
                    'kahao' => $bank_code,
                    'kaihuhang' => $kaihuhang,
                    'if_default' => 0,
                    'update_time' => date('Y-m-d',time()),
                    'dropState'=>1,
                );
            }
            $member_bank_model -> edit("user_id = " .$user_id ." and kahao=".$bank_code,$data);
            echo json_encode(array("flag"=>"ok"));
            return;
        }
    }

    function get_user_api(){
        echo $this->user_api('','','');
    }
    function deleteImg(){
        /*$data = $_POST["data"];
        if($data!=""){
            $this->deleteFileInfo($data);
            $this->deleteFileInfo($data);
        }*/
    }

    //pos机刷卡查询
    function pos_view(){
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
            if (strlen($_GET["card_num"])<14) {
                $this->show_warning("银行卡号最少14位");
                exit;
            }
            $conditions.=" and pos_record.bank_card='".bank_hidden($_GET["card_num"])."'";
        }

        $page = $this->_get_page();
        $posrecord     = $this->_posrecord_mob->find(array(
            'conditions'    => $conditions." and  EXISTS (SELECT 1 FROM ecm_pos_bind bind_bankcard WHERE bind_bankcard.`user_id` =".$this->visitor->get('user_id')." AND  bind_bankcard.`bank_card` = pos_record.bank_card and bind_bankcard.state = 1) order by pos_record.xiaofei_time desc",
            'fields'=> "this.*,(select concat(s.region_name, s.address) from ecm_store s where s.pos_sn=pos_record.pos_sn limit 0,1) as region_name, (select bind.bank_name from ecm_pos_bind bind where bind.bank_card = pos_record.bank_card and bind.state=1) as bind_bank_name",
            'limit' => $page['limit'],
            'count' => true,
        ));
        $page['item_count'] = $this->_posrecord_mob->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data', $posrecord);
        $this->_curitem("overview");
        $this->display('member.pos_view.html');
    }

    //pos机刷卡绑定查询
    function pos_bind(){

        $conditions="1=1 ";

        if($_GET["bank_card_number"]){
            $conditions.=" and pos_bind.bank_card='".bank_hidden(trim($_GET["bank_card_number"]))."'";
        }

        $page = $this->_get_page();
        $posbindbankcard     = $this->_posbindbankcard_mob->find(array(
            'conditions'    => $conditions." and user_id=".$this->visitor->get('user_id'),
            'fields'=> "this.*",
            'limit' => $page['limit'],
            'count' => true,
        ));
        $page['item_count'] = $this->_posbindbankcard_mob->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data', $posbindbankcard);
        $this->_curitem("overview");
        $this->display('member.pos_bind.html');
    }

    function pos_bind_delete(){
        $id  = $_GET["id"];
        $val  = $_GET["val"];
        if($id=="" || $val == ""){
            $this->show_warning("请选择卡号");
        }
        $rs = $this->_posbindbankcard_mob->db_query("update ecm_pos_bind set state=$val where id=$id and user_id=".$this->visitor->get('user_id'));
        header("Location: index.php?app=member&act=pos_bind&p=userInfo");
    }

}

?>
