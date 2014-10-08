<?php

/**address
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class ServiceApp extends MemberbaseApp
{
    var $_feed_enabled = false;
    var $_uploadedfile_mod;
    function __construct()
    {
        $this->ServiceApp();

        include_once(ROOT_PATH . '/app/my_goods.app.php');
        include_once(ROOT_PATH . '/app/seller_order.app.php');
        include_once(ROOT_PATH . '/app/my_shipping.app.php');
        include_once(ROOT_PATH . '/app/seller_groupbuy.app.php');
        include_once(ROOT_PATH . '/app/gselector.app.php');
        include_once(ROOT_PATH . '/app/my_qa.app.php');
        include_once(ROOT_PATH . '/app/goods_toushu.app.php');
        include_once(ROOT_PATH . '/app/ordershouhou.app.php');
        include_once(ROOT_PATH . '/includes/email.class.php');          //邮件发送
        include_once(ROOT_PATH . '/includes/MSM.php');                    //短信发送
        include_once(ROOT_PATH . '/app/make_image.app.php');
        $this->assign('init_jibie', Conf::get('jibie'));
        $this->_member_mod =& m('member');
        $this->_store_mod =& m('store');

        $this->_goods_mod =& bm('goods', array('_store_id' => $this->_store_id));
		$this->_curitem(empty($_GET["act"])?'service':$_GET["act"]);
        $this->_uploadedfile_mod = &m('uploadedfile');

		$dataInfo=$this->visitor->info;

        $count = count(explode("djkhanguo",$dataInfo["user_name"]));
        if( $count>1 ){
            $this->assign('hanguo_flag', 1);
        }

		if((empty($dataInfo["member_type"]) || ($dataInfo["member_type"]!="02" && $dataInfo["member_type"]!="03" && $dataInfo["member_type"]!="04"))
            && $_GET["act"]!="register_service"
            && $_GET["act"]!="serachService"
            && ($_GET["act"]!="queryByqiangzhu" && $_GET["act"]!="querybyqiangzhu")
            && $_GET["act"]!="initservice")
		{
            echo "<script>location='index.php';</script>";
		}else{
            $this->assign("service",$dataInfo);
        }
    }
    function ServiceApp()
    {
        parent::__construct();
        $this->assign('appName', $_GET["app"]);
        $this->_store_mod =& m('store');
        $this->_goods_mod =& m('goods');
        /* 当前位置 */
        $this->_curlocal(LANG::get('service_title'),    url('app=service'),
            LANG::get('service_title'));
    }

    function index()
    {
        /* 清除新短消息缓存 */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));

        $user = $this->visitor->get();
        $order_mod =& m('order');



        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);

        $info['portrait'] = portrait($info['user_id'], $info['portrait'], 'middle');

        if($user["member_type"]!="02"){
            header("Location: index.php?app=service&act=service&p=service&tuoguan=1");
        }

        $users=$user_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$info["region_id"],
            "fields"    =>"user_id,user_name"
        ));

        $order_mod =& m('order');
        $query_conditions="store.fwzx=".$user['user_id'];
        if(count($users)>0 && $info["member_type"]=="02"){
            foreach($users as $k=>$v){
                if($query_conditions == ""){
                    $query_conditions.="store.fwzx=".$v["user_id"]; //获得子账号下面的商家
                }else{
                    $query_conditions.=" or store.fwzx=".$v["user_id"]; //获得子账号下面的商家
                }

            }
        }
        //服务中心托管商家yye
        $tuoguan_yyy_sql = "SELECT SUM(ord.order_amount) FROM ecm_order ord,ecm_store store where
                            ord.status=40 and ord.seller_id = store.store_id and store.tuoguan =1 and ($query_conditions)";
        $tuoguan_shouyi = $order_mod->getOne($tuoguan_yyy_sql);
        //本区营业额
        $benqu_yyy_sql = "SELECT SUM(ord.order_amount) FROM ecm_order ord,ecm_store store where
                             ord.status=40 and ord.seller_id = store.store_id and store.region_id =".$user["region_id"];
        if($user["user_name"] == "djkhanguo"){
            $benqu_yyy_sql = "SELECT SUM(ord.order_amount) FROM ecm_order ord where ord.status=40 and ord.guojiguan=2";
        }

        //$memberfinance_mod = & m('memberfinance');
        $user_api = json_decode($this->user_api("","",""));
//        $user_api = $memberfinance_mod->get($user['user_id']);
        $benqu_yyy = $order_mod->getOne($benqu_yyy_sql);
        $this->assign('tuoguan_yye', $tuoguan_shouyi);
        $this->assign('shouyi', $user_api->koushui_yue);
        $this->assign('all_yye', $benqu_yyy);



        if(!empty($info["phone_mob"]) && $info["phone_mob"]>0){
            $info["show_tel"]=$this->half_replace($info["phone_mob"]);
        }
        if(!empty($info["email"]) && $info["email"]!=""){
            $info["show_email"]=$this->half_replace($info["email"]);
        }

        $this->assign('user', $info);
        $query="";
        if($info["region_id"]!="")
        {
            $query="AND region_id=".$info["region_id"];
        }
        $this->assign('member_type', $info["member_type"]);

        $groupbuy_mod = & m('groupbuy');

        $goodsqa_mod = & m('goodsqa');
        /* 卖家提醒：待处理订单和待发货订单 */

        $sql7 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']."
              AND tuoguan=1 AND dropState=1 $query) AND status = '" . ORDER_SUBMITTED . "'";

        $sql8 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id in (select store_id from ".DB_PREFIX."store where fwzx=".$user['user_id']."
              AND tuoguan=1 AND dropState=1 $query) AND status = '" . ORDER_ACCEPTED . "'";

        //echo $sql7;

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
                'replied'   => $goodsqa_mod->getOne($sql9),
                'shouhou'   => $goodsqa_mod->getOne($sql10),
                'goods'     => $goodsqa_mod->getOne($sql11),
            );

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

        $this->assign('seller_stat', $seller_stat);
        $this->assign('TO_CHONGZHI_URL', TO_CHONGZHI_URL);

        /*查看是否绑定银行卡*/
        $member_bank = & m('memberbank');
        $bank = $member_bank -> find('user_id = ' . $this->visitor->get('user_id'));
        $flag = true;
        if(count($bank) > 0){
            $flag = false;
        }
        $this->assign('flag', $flag);


        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
            LANG::get('overview'));

        $this->_curitem("index");
       // echo base64_decode($_SESSION['captcha']);
        $this->_config_seo('title', "帐号概览"."--".Lang::get('member_center'));
	   // $this->service();

        $today = date("Y-m-d H:i:s");
        $firstday = date('Y-m-01 H:i:s', strtotime($today));
        $lastday = date('Y-m-d H:i:s', strtotime("$firstday +1 month -10 day"));
        $newDate =  strtotime($today);
        $day1 = strtotime($lastday);
        if($newDate > $day1) {
            $this->assign("show","true");
        }
        $this->display('service.html');
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

    function service(){

        $conditions=" 1=1 and member.dropState=1 and s.dropState=1 ";
		$user = $this->visitor->get();

		$userInfo=$this->_member_mod->get($user['user_id']);

        if($userInfo["member_type"] == "04" && $userInfo["user_name"] =="djk11001" ){
            $this->show_all_store();
            exit;
        }

        if($userInfo["member_type"] == "04" && $userInfo["user_name"] =="djk11002" ){
            $this->my_goods();
            exit;
        }

        if($userInfo["region_type"]=="02"){
            $this->assign('member_type', 2);
        }
		//只获取该服务中心下的
		if(!empty($userInfo['region_id'])){
			$conditions.=" and s.region_id ='".$userInfo['region_id']."'";
		}

		if ($_GET["tuoguan"]!=""){
            $tuoguan=$_GET["tuoguan"];

            if ($tuoguan == 1) {           //获得托管的商家和提交的商家
                     $users=$this->_member_mod->find(array(
                                            "conditions"=>"member_type='03'and dropState=1  and region_id=".$user['region_id'],
                                            "fields"    =>"user_id,user_name"
                    ));
                    $this->assign('users', $users);
                    $query_conditions="";
                    if(count($users)>0){
                        foreach($users as $k=>$v){
                            $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                        }
                    }
                    $conditions.=" and (fwzx='".$user['user_id']."' $query_conditions)";
                    $conditions.="and tuoguan=1";
            }elseif ($tuoguan == 2) {
                    $conditions.=" and (tuoguan=0 or tuoguan is null) ";
            }elseif ($tuoguan == 3) {
                $conditions.=" and s.seller_type = 3 ";
            }

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

        $users="";
        if($_GET["users"]!="" && $_GET["users"]!=-1){
            $conditions.=" and s.fwzx=".$_GET["users"];
        }
        $stores = $this->_store_mod->find(array(
            'conditions' => $conditions,
            'join'  => 'belongs_to_user',
            'fields'=> "this.*,
                      member.user_name,member.im_qq,member.nick_name,
                      (select count(1) from ".DB_PREFIX."goods g  where g.store_id = s.store_id and g.dropState<>0)  as goodsCount,
                      (select count(1) from ".DB_PREFIX."order ord  where ord.seller_id = s.store_id) as orderCount,
                      (select user_name from ".DB_PREFIX."member m where  m.user_id=s.fwzx and m.dropState=1) as service_user,
                      (select count(1) from ".DB_PREFIX."member m where  m.t_id=s.store_id and m.dropState=1) as tuserCount,
                      (select SUM(order_amount) from ".DB_PREFIX."order ord where  ord.seller_id=s.store_id and ord.status=40) as jine",
            'limit' => $page['limit'],
            'count' => true,
            'order' => "$sort $order"
        ));
        //$this->print_r($stores);
        $sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $this->assign('sgrades', $grades);

        $states = array(
            STORE_APPLYING  => LANG::get('wait_verify'),
            STORE_OPEN      => "开启",
            STORE_CLOSED    => "关闭",
            STORE_UNPASS    => "审核不通过"
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

        /*$region_id=$serviceInfo["region_id"];
        $zizhanghus=$this->_member_mod->find(array(
            "conditions"=>"member.region_id=$region_id and member.member_type='03' and member.dropState=1 ",
            'fields'=> "this.*"
        ));
        $this->assign('zizhanghus', $zizhanghus); //子账号*/

        $this->assign('filtered', $filter? 1 : 0); //是否有查询条件

        $this->assign('tuoguanFlag',empty($_GET["tuoguan"])?0:$_GET["tuoguan"]); //是否有查询条件
        $this->assign('page_info', $page);
        $this->_config_seo('title', "商家管理" . ' - ' . Lang::get('member_center'));
        $this->display('service.index.html');


    }
    //服务中心子账号
    function zizhanghu(){

        $serviceInfo=$this->visitor->info;
        $region_id=$serviceInfo["region_id"];

        if($serviceInfo["member_type"]!="02"){
            $this->show_warning('member_type_error');
            exit;
        }
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
                    array(
                        'path' => 'mlselection.js',
                        'attr' => '',
                    ),
                ),
                'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css,res:jqtreetable.css',
            ));
            $conditions="";

            if($_GET["user_name"]){
                $conditions.="and user_name like '%".$_GET["user_name"]."%'";
            }

            if($_GET["add_time_from"])
            {
                $add_time_from=gmstr2time($_GET["add_time_from"]);
                $conditions.=" and reg_time >='$add_time_from' ";
            }
            if($_GET["add_time_to"])
            {
                $add_time_to=gmstr2time_end($_GET["add_time_to"]);
                $conditions.=" and reg_time <= '$add_time_to' ";
            }

            $page = $this->_get_page(10);
            /*$zizhanghus=$this->_member_mod->find(array(
                                     "conditions"=>"member.region_id=$region_id and member.member_type='03' and member.dropState=1 ".$conditions,
                                     'fields'=> "this.*,
                                           (select count(1) from ".DB_PREFIX."store s where s.fwzx=member.user_id and s.dropState=1 and tuoguan=1) as store_cont,
                                           (select count(1) from ".DB_PREFIX."goods g where g.dropState=1 and EXISTS(select 1 from ".DB_PREFIX."store s1 where s1.store_id=g.store_id
                                            and s1.dropState=1 and s1.fwzx=member.user_id)) as goods_count,
                                           (select count(1) from ".DB_PREFIX."order o where  EXISTS(select 1 from ".DB_PREFIX."store s1 where s1.store_id=o.seller_id and
                                           s1.dropState=1 and s1.fwzx=member.user_id)) as order_count",
                                     'limit' => $page['limit'],
                                     'count' => true
            ));*/
            $zizhanghus=$this->_member_mod->find(array(
                "conditions"=>"member.region_id=$region_id and member.member_type='03' and member.dropState=1 ".$conditions,
                'fields'=> "this.*,(select count(1) from ".DB_PREFIX."store s where s.fwzx=member.user_id and s.dropState=1 and tuoguan=1) as store_cont",
                'limit' => $page['limit'],
                'count' => true
            ));
            $page['item_count'] = $this->_member_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            //$this->pr($zizhanghus);
            $this->assign('zizhanghus', $zizhanghus);

            $this->_config_seo('title', "子帐号管理" . ' - ' . Lang::get('member_center'));

            $this->display('service.zizhanghu.html');
        }else{

        }
    }

    function add_zizhanghu(){

        $serviceInfo=$this->visitor->info;
        $region_id=$serviceInfo["region_id"];
        if(!$_POST){
            $user_id=$_GET["user_id"];
            if($user_id!="" && $user_id!=0){
                $user=$this->_member_mod->get(array("conditions"=>"user_id=$user_id and region_id='$region_id' order by user_id desc"));
                if($user["user_id"]>0){
                    $this->assign('zjh_member', $user);
                }
            }
            $this->display('service.zizhanghu.add.html');
        }else{

            $user_name=$_POST["service_name"].$_POST["user_name"];
            $data=array(
                        "status"    =>$_POST["status"]
            );

            if(!isset($_POST["user_id"]) || $_POST["user_id"]==0){

                $user=$this->_member_mod->get(array("conditions"=>"user_name='$user_name'"));

                if($user["user_id"]>0){
                    $this->pop_warning(Lang::get('user_name_not_null'));
                    exit;
                }
                $data["user_name"]=$user_name;
                $data["password"]=md5($_POST["password"]);
                $data["member_type"]="03";
                $data["region_id"]=$serviceInfo["region_id"];
                $data["region_name"]=$serviceInfo["region_name"];
                $data["reg_time"]=gmtime();
                $data["nick_name"]=$user_name;
                $user_id = $this->_member_mod->add($data);


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

            }else{
                if($_POST["password"]!=""){
                    $data["password"]=md5($_POST["password"]);
                }
                $this->_member_mod->edit($_POST["user_id"],$data);
            }
            $this->pop_warning('ok', 'add_zizhanghu');
        }
    }

    function service_view(){
        if(!$_GET["id"]){
            $this->show_warning('not_store');
            exit;
        }
        $sid=$_GET["id"];
        $store_info=$this->_store_mod->get(array(
            'conditions' =>" s.store_id=$sid ",
            'fields'      =>" this.*,
                                   (SUBSTRING_INDEX(s.ticheng * 100,'.',1)) as ticheng2,
                                   (select user_name from ".DB_PREFIX."member where user_id=s.store_id)        as user_name,
                                   (select cate_id from ".DB_PREFIX."category_store where store_id=s.store_id) as cate_id
                                   ",
        ));


        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];

        $this->_curitem('service');

        $sgrade_mod =& m('sgrade');
        $this->assign('scategories', $this->_get_scategory_options());

        $jibies=Conf::get('jibie');
        $tichengs=Conf::get('ticheng');

        $List=array();

        if(count($jibies)>0 && count($tichengs)>0)
        {
            foreach($jibies as $k=>$v)
            {
                $tmp=$tichengs[$k];
                $tmp2=array();
                foreach($tmp as $k1=>$v1)
                {
                    //托管商家过滤掉15%以下的提成
                    if($_GET["tuoguan"]==1){
                        if($v1*100>=15)
                        {
                            $tmp2=array_merge($tmp2,array($v1*100));
                        }
                    }else{
                        if($v1*100>=10)
                        {
                            $tmp2=array_merge($tmp2,array($v1*100));
                        }
                    }

                }
                if(count($tmp2)>0)
                {
                    $List[$k]=$tmp2;
                }

            }
        }

        /* 导入jQuery的表单验证插件 */
        $this->import_resource(array(
            'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
        ));

        $this->assign('init_tichengs',$List);

        $this->assign('viewType',1);

        $this->assign('store_info', $store_info);

        $this->display('store.view.html');
    }

    function service_edit(){
        if(!$_POST){
                  if(!$_GET["id"]){
                      $this->show_warning('not_store');
                      exit;
                  }
                  $sid=$_GET["id"];
                  $store_info=$this->_store_mod->get(array(
                      'conditions' =>" s.store_id=$sid ",
                      'fields'      =>" this.*,
                                   (SUBSTRING_INDEX(s.ticheng * 100,'.',1)) as ticheng2,
                                   (select user_name from ".DB_PREFIX."member where user_id=s.store_id)        as user_name,
                                   (select cate_id from ".DB_PREFIX."category_store where store_id=s.store_id) as cate_id
                                   ",
                  ));


                  $fwzx_into = $this->visitor->get();
                  $fwzxId=$fwzx_into['user_id'];
                  $this->assign('user',$fwzx_into);
                  $this->_curitem('service');

                  $sgrade_mod =& m('sgrade');
                  $this->assign('scategories', $this->_get_scategory_options());
                  $this->assign('scategories_ticheng', $this->_get_scategory_min_ticheng());


                /*$this->assign('editor_upload', $this->_build_upload(array(
                    'obj' => 'EDITOR_SWFU',
                    'belong' => BELONG_STORE,
                    'item_id' => $fwzxId,
                    'button_text' => Lang::get('bat_upload'),
                    'button_id' => 'editor_upload_button',
                    'progress_id' => 'editor_upload_progress',
                    'upload_url' => 'index.php?app=swfupload',
                    'if_multirow' => 1,
                )));
                $this->assign('build_editor', $this->_build_editor(array(
                    'name' => 'description',
                    'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
                )));*/
                    $init_tichengs2[1]=array();
                    for($i=5;$i<=85;$i++){
                        $init_tichengs2[1][$i]=$i;
                    }

                    $this->assign('init_tichengs2',$init_tichengs2);

                  /* 导入jQuery的表单验证插件 */
                  $this->import_resource(array(
                    'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
                  ));

                  $this->assign('init_tichengs',$List);

                  $this->assign('viewType',1);

                  $region_mod =& m('region');
                  $this->assign('regions', $region_mod->get_options(0));

                  $this->assign('store_info', $store_info);

                  $storeuploadedfile_mod =& m('storeuploadedfile');
                  $storeuploadedfile = $storeuploadedfile_mod->find("store_id=$sid and dropState=1");
                  if($storeuploadedfile && count($storeuploadedfile)>0){
                      //$this->pr($storeuploadedfile);
                      $this->assign('storeuploadedfile', $storeuploadedfile);
                  }
                 $this->assign('ECM_ID', $_COOKIE['ECM_ID']);
                 $this->assign('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);

                  $this->display('store.form1.html');
        }else{
                //header('Content-type: text/json');
                $fwzx_into = $this->visitor->get();
                $fwzxId=$fwzx_into['user_id'];
                $sid=$_POST["store_id"];

                $query_conditions = "";
                if($fwzx_into["member_type"]=="02"){
                    $user_ids=$this->_member_mod->find(array(
                        "conditions"=>"member_type='03' and region_id=".$fwzx_into["region_id"],
                        "fields"    =>"user_id"
                    ));
                    if(count($user_ids)>0){
                        foreach($user_ids as $k=>$v){
                            $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                        }
                    }

                }


                $store_info=$this->_store_mod->get(array(
                    'conditions' =>" store_id=$sid and (fwzx=$fwzxId $query_conditions) and dropState=1",
                ));

                //$tichengs=explode("##",$_POST["ticheng"]);

                $ticheng=$_POST["ticheng"];

                if($store_info["store_id"]!=""){
                    $data=array("owner_name"=>$_POST["owner_name"],
                                "owner_card"=>$_POST["owner_card"],
                                "address"=>$_POST["address"],
                                "zipcode"=>$_POST["zipcode"],
                                "tel"=>$_POST["tel"],
                                "region_id" =>$_POST["region_id"],
                                "region_name" =>$_POST["region_name"],
                                //'jibie'        =>$tichengs[0],
                                'ticheng'        =>$ticheng/100,
                                'yyzz'           => $_POST["yyzz"],
                                'description2' => $_POST["description2"],
                                'seller_type'  => $_POST["seller_type"]
                                );
                    if($fwzx_into["member_type"] == "02" || $fwzx_into["member_type"] == "03"){
                        $data["state"]=0;
                    }

                    //$this->pr($data);exit;
                    $this->_store_mod->edit($sid,$data);


                    $cate_id = intval($_POST['cate_id']);
                    $this->_store_mod->unlinkRelation('has_scategory', $sid);
                    if ($cate_id > 0)
                    {
                        $this->_store_mod->createRelation('has_scategory', $sid, $cate_id);
                    }

                    $cate_id=$_POST["cate_id"];

                    $this->_store_mod->db_query("update ".DB_PREFIX."category_store set cate_id=$cate_id where store_id=$sid");

                    $store_uploaded_file = $_POST["store_uploaded_file"];
                    $storeuploadedfile_mod =& m('storeuploadedfile');
                    if($store_uploaded_file && count($store_uploaded_file)>0){
                        for($i=0;$i<count($store_uploaded_file);$i++){
                            $file_date["store_id"] = $sid;
                            $file_date["file_path"] = $store_uploaded_file[$i];
                            $file_date["dropState"] = 1;
                            $file_date["add_time"] = gmtime();
                            $file_date["if_default"] = 0;
                            $make_app = new Make_imageApp();
                            $make_app->_upload_store_image($file_date["file_path"]);
                            $storeuploadedfile_mod->add($file_date);
                        }
                    }

                    if($_POST["if_default"] !=""){
                        $storeuploadedfile_mod->db_query("update ".DB_PREFIX."store_image set if_default=0 where store_id=$sid");
                        $storeuploadedfile_mod->db_query("update ".DB_PREFIX."store_image set if_default=1 where store_id=$sid and file_path='".$_POST["if_default"]."'");
                    }

                    $this->_store_mod->commit_transaction();

                    if($_SESSION["files_store"] && $_SESSION["files_store"]!=""){
                        $_SESSION["files_store"] = "";
                    }

                    //$this->show_message('操作成功');

                    //echo json_encode(array("flag"=>"ok"));
                    echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"ok")).");</script>";
                    exit;
                }else{
                    //echo json_encode(array("flag"=>"error","error_msg"=>"没有找到商家资料","error_lbl"=>"store_name"));
                    echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"没有找到商家资料","error_lbl"=>"store_name")).");</script>";
                    exit;
                    //$this->show_warning('没有找到商家资料');
                }
        }
    }
    //修改店铺设置
    function store_edit(){
        $user = $this->visitor->get();
        if($user["member_type"] != "04" ||  $user["user_name"] !="djk11001" ){
            $this->show_warning('权限错误!');
            exit;
        }
        $id = $_GET["id"];
        if( $id == null || $id == "" || $id <=0 ){
            $this->show_warning('请选择店铺!');
            exit;
        }
        include_once(ROOT_PATH . '/app/my_store.app.php');
        $storeApp =new My_storeApp();
        $storeApp->index();
    }
    function store_set_image(){
        $user = $this->visitor->get();
        if($user["member_type"] != "04" ||  $user["user_name"] !="djk11001" ){
            $this->show_warning('权限错误!');
            exit;
        }
        $id = $_GET["id"];
        if( $id == null || $id == "" || $id <=0 ){
            $this->show_warning('请选择店铺!');
            exit;
        }
        include_once(ROOT_PATH . '/app/my_store.app.php');
        $storeApp =new My_storeApp();
        $storeApp->set_image();
    }
    function colse_store()
    {
	   if($_GET["id"])
	   {
	   		  $store_id=$_GET["id"];

	   		  $fwzx_into = $this->visitor->get();
	   		  $fwzxId=$fwzx_into['user_id'];
	          $stores = $this->_store_mod->find(array(
	            'conditions' => " store_id=$store_id and s.dropState=1 and fwzx='$fwzxId'",
	          ));
	          if(count($stores)>0)
	          {
				$this->_store_mod->edit($store_id, array("state"=>2));
	          }else
	          {
	          	$this->show_message('操作失败,只能关闭本服务中心下的商铺',
                            'index', 'index.php?app=service');
                exit;
	          }
			  $this->show_message('操作成功',
                            'index', 'index.php?app=service');

	   }
    }
	function my_goods()
	{
		$goodsApp=new My_goodsApp();
		$goodsApp->index();
	}



    //采购审核 普通 商品
    function goods_shenhe() {
        $goods_id = $_GET['id'];
        $type = $_GET['type'];
        $goods_mode = & m('goods');
        if($goods_mode->get($goods_id)) {
            if ($type == '1') {
                $sql = "update ecm_goods set dropState = 1 where goods_id =".$goods_id;
            } else if($type == '2'){
                $sql = "update ecm_goods set dropState = 3 where goods_id =".$goods_id;
            }
            $num = $goods_mode->db->query($sql);
            if ($num >0 ){
                echo "1";
            } else {
                echo "0";
            }
        } else {
            echo "3";
        }
    }

    //采购或韩国馆审核 韩国馆的商品
    function goods_hanguo_shenhe() {
        $goods_id = $_GET['id'];

        $userInfo=$this->visitor->info;
        $hanguo_flag = 0;
        $count = count(explode("djkhanguo",$userInfo["user_name"]));
        $hanguo_flag = ($count>1) ? 1 : 0;

        $type = $_GET['type'];
        $goods_mode = & m('goods');
        if($goods_mode->get($goods_id)) {
            if   ($type == '1') {
                if($userInfo["member_type"] == "04"){
                    $sql = "update ecm_goods set dropState = 1 where goods_id =".$goods_id;
                    $goods_mode->db->query($sql);
                    echo "1";
                }else{
                    if($hanguo_flag == 1){
                        $sql = "update ecm_goods set dropState = 4 where goods_id =".$goods_id;
                        $goods_mode->db->query($sql);
                        echo "1";
                    }
                }

            } else {
                $sql = "update ecm_goods set dropState = 3 where goods_id =".$goods_id;
                $goods_mode->db->query($sql);
                echo "0";
            }
        } else {
            echo "3";
        }
    }

    //批量审核
    function goods_batch_shenhe(){
        $goods_mode = & m('goods');
        $ids = explode(',',$_GET['ids']);
        $str_ids = "";
        $type = $_GET['type'];
        foreach ($ids as $val) {
            if($val!= null && $val!=""){
                if($str_ids == "") {
                    $str_ids = $val;
                } else {
                    $str_ids.=",".$val;
                }
            }

        }

        if ($str_ids !="") {
            if ($type=='1') {

                $userInfo=$this->visitor->info;
                $hanguo_flag = 0;
                $count = count(explode("djkhanguo",$userInfo["user_name"]));
                $hanguo_flag = ($count>1) ? 1 : 0;
                $sql = "update ecm_goods set dropState = 1 where goods_id in (".$str_ids.")";
                //韩国馆自己审核的商品进入2审
                if($hanguo_flag ==1){
                    $sql = "update ecm_goods set dropState = 4 where if_ruzhu=1 and country_id=2 and goods_id in (".$str_ids.")";
                }

            } else {
                $sql = "update ecm_goods set dropState = 3 where goods_id in (".$str_ids.")";
            }

            $num =  $goods_mode->db->query($sql);
            if($num>0){
                echo "1";
            } else {
                echo "0";
            }
        } else {
            echo "3";
        }

    }

    //全部采购商品管理
    function all_goods(){
        $goodsApp=new My_goodsApp();
        $goodsApp->all_goods();
    }

    function brand_list(){
        $goodsApp=new My_goodsApp();
        $goodsApp->brand_list();
    }
    function goods_batch_edit(){
        $goodsApp=new My_goodsApp();
        $goodsApp->batch_edit();
    }

	function add()
    {
        /*
        $validata=$this->_checkService();
        if(!$validata["region_name"])
        {
            $this->show_message('操作异常',
                'index', 'index.php');
        }*/
        //$this->print_r($_POST);exit;
		$goodsApp=new My_goodsApp();
		$this->assign('addType', 1);
        $this->assign('formTitle',"新增托管商品");
		$fwzx_into = $this->visitor->get();
	   	$fwzxId=$fwzx_into['user_id'];

        $userInfo=$this->_member_mod->get($fwzxId);

        $conditions="";

        if($userInfo["member_type"] == "04" && $userInfo["user_name"] =="djk11002" ){
            $stores = $this->_store_mod->find(array(
                'conditions' => "s.dropState=1 and state=1".$conditions,
                'join'  => 'belongs_to_user',
                'order' => "sort_order",
                'fields'=> 'this.*,member.user_name'
            ));
            if(count($stores)>0){
                $shippingArray=null;
                $model_shipping =& m('shipping');
                foreach($stores as $k=>$v){
                    $shippings=$model_shipping->find(array(
                        'conditions'    => 'store_id = '.$k,
                        'fields'=> 'this.shipping_id,this.shipping_name'
                    ));
                    foreach($shippings as $k2=>$v2){
                        $shippingArray[$k][$k2]=array("shipping_id"=>$v2["shipping_id"],"shipping_name"=>$v2["shipping_name"]);
                    }
                }
                $this->assign('shippingArray', $shippingArray);
            }
            $this->assign('stores', $stores);
        }else{
            //只获取该服务中心下的
            if(!empty($userInfo['region_id'])){
                $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
            }
            //获得子账号
            $user_ids=$this->_member_mod->find(array(
                "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
                "fields"    =>"user_id"
            ));
            $query_conditions="";
            if(count($user_ids)>0 && $userInfo["member_type"]=="02"){
                foreach($user_ids as $k=>$v){
                    $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                }

            }

            $stores = $this->_store_mod->find(array(
                'conditions' => " (fwzx=$fwzxId $query_conditions) and tuoguan=1 and state=1 and s.dropState=1 ".$conditions,
                'join'  => 'belongs_to_user',
                'order' => "sort_order",
                'fields'=> 'this.*,member.user_name'
            ));

            if(count($stores)>0) {
                $shippingArray=null;
                $model_shipping =& m('shipping');
                foreach($stores as $k=>$v){
                    $shippings=$model_shipping->find(array(
                        'conditions'    => 'store_id = '.$k,
                        'fields'=> 'this.shipping_id,this.shipping_name'
                    ));
                    foreach($shippings as $k2=>$v2){
                        $shippingArray[$k][$k2]=array("shipping_id"=>$v2["shipping_id"],"shipping_name"=>$v2["shipping_name"]);
                    }
                }
                //$this->print_r($shippingArray);
                $this->assign('shippingArray', $shippingArray);
            }
            $this->assign('stores', $stores);
        }
		$goodsApp->add();
    }

    function bdsh_goods_add(){
        $goodsApp=new My_goodsApp();
        return $goodsApp->bdsh_goods_add();
    }
    function bdsh_goods_edit(){
        $goodsApp=new My_goodsApp();
        return $goodsApp->bdsh_goods_edit();
    }
    function goods_view(){
        $this->edit();
    }

    function edit(){
        /*$validata=$this->_checkService();
        if(!$validata["region_name"])
        {
            $this->show_message('操作异常',
                'index', 'index.php');
        }*/

		$goodsApp=new My_goodsApp();
		$this->assign('addType', 1);
        $this->assign('formTitle',"修改托管商品");
		$fwzx_into = $this->visitor->get();
	   	$fwzxId=$fwzx_into['user_id'];

        $userInfo=$this->_member_mod->get($fwzxId);

        $conditions="";

        //只获取该服务中心下的
        if(!empty($userInfo['region_id'])){
            $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
        }
        $user = $this->visitor->get();
        $userInfo=$this->_member_mod->get($user['user_id']);

        if($userInfo["member_type"] == "04" && $userInfo["user_name"] =="djk11002" ){
            $stores = $this->_store_mod->find(array(
                'conditions' => "s.dropState=1 and state=1".$conditions,
                'join'  => 'belongs_to_user',
                'order' => "sort_order",
                'fields'=> 'this.*,member.user_name'
            ));
            if(count($stores)>0){
                $shippingArray=null;
                $model_shipping =& m('shipping');
                foreach($stores as $k=>$v){
                    $shippings=$model_shipping->find(array(
                        'conditions'    => 'store_id = '.$k,
                        'fields'=> 'this.shipping_id,this.shipping_name'
                    ));
                    foreach($shippings as $k2=>$v2){
                        $shippingArray[$k][$k2]=array("shipping_id"=>$v2["shipping_id"],"shipping_name"=>$v2["shipping_name"]);
                    }
                }
                $this->assign('shippingArray', $shippingArray);
            }
            $this->assign('stores', $stores);
        }else{
                $user_ids=$this->_member_mod->find(array(
                    "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
                    "fields"    =>"user_id"
                ));
                $query_conditions="";
                if(count($user_ids)>0){
                    foreach($user_ids as $k=>$v){
                        $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                    }
                }
                $stores = $this->_store_mod->find(array(
                    'conditions' => " (fwzx=$fwzxId $query_conditions) and tuoguan=1 and s.dropState=1 and state=1".$conditions,
                    'join'  => 'belongs_to_user',
                    'order' => "sort_order",
                    'fields'=> 'this.*,member.user_name'
                ));
                if(count($stores)>0)
                {
                    $shippingArray=null;
                    $model_shipping =& m('shipping');
                    foreach($stores as $k=>$v){
                        $shippings=$model_shipping->find(array(
                            'conditions'    => 'store_id = '.$k,
                            'fields'=> 'this.shipping_id,this.shipping_name'
                        ));
                        foreach($shippings as $k2=>$v2){
                            $shippingArray[$k][$k2]=array("shipping_id"=>$v2["shipping_id"],"shipping_name"=>$v2["shipping_name"]);
                        }
                    }
                    //$this->print_r($shippingArray);
                    $this->assign('shippingArray', $shippingArray);
                }
                $this->assign('stores', $stores);
        }
        $goodsApp->edit();

    }

    function edit_byDjk()
    {
        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];
        $user_name=$fwzx_into["user_name"];
        if($user_name!="djk"){
            exit("您没有权限执行该操作");
        }
        if(!$_GET["id"] || $_GET["id"]<=0){
            exit("操作错误");
        }

        $goodsApp=new My_goodsApp();
        $this->assign('addType', 1);
        $this->assign('formTitle',"修改托管商品");


        $userInfo=$this->_member_mod->get($fwzxId);

        $conditions="";

        //只获取该服务中心下的
        if(!empty($userInfo['region_id']))
        {
            $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
        }

        $user_ids=$this->_member_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
            "fields"    =>"user_id"
        ));
        $query_conditions="";
        if(count($user_ids)>0){
            foreach($user_ids as $k=>$v){
                $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
            }

        }
        $stores = $this->_store_mod->find(array(
            'conditions' => "s.store_id=(select store_id from ".DB_PREFIX."goods where goods_id=".$_GET["id"].")",
            'join'  => 'belongs_to_user',
            'order' => "sort_order",
            'fields'=> 'this.*,member.user_name'
        ));

        if(count($stores)>0)
        {
            $shippingArray=null;
            $model_shipping =& m('shipping');
            foreach($stores as $k=>$v)
            {
                $shippings=$model_shipping->find(array(
                    'conditions'    => 'store_id = '.$k,
                    'fields'=> 'this.shipping_id,this.shipping_name'
                ));
                foreach($shippings as $k2=>$v2)
                {
                    $shippingArray[$k][$k2]=array("shipping_id"=>$v2["shipping_id"],"shipping_name"=>$v2["shipping_name"]);
                }
            }
            $this->assign('shippingArray', $shippingArray);
        }


        $this->assign('stores', $stores);

        $goodsApp->edit();
    }

    function shouyi()
    {

        $page = $this->_get_page();
        $yue_log_mode=& m('yue_log');
        $order_mode=& m('order');



        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];

        $conditions="";

        $conditions2="and ticheng.user_id=$fwzxId and 1=1";

        if(!empty($_GET["order_sn"]) && $_GET["order_sn"]!="")
        {
            $order_id=$_GET["order_sn"];
            $conditions.=" and odr.order_sn='$order_id'";
        }
        if(isset($_GET["status"]) && $_GET["status"]!="")
        {
            $status=$_GET["status"];
            $conditions.=" and odr.account_status=$status";
        }

        if($_GET["add_time_from"])
        {
            //$add_time_from=$_GET["add_time_from"];
            $add_time_from=gmstr2time($_GET["add_time_from"]);
            $conditions.=" and odr.add_time>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            //$add_time_to=$_GET["add_time_to"];
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            // $conditions.=" and date_format(goodsOrder1.add_time,'%Y-%m-%d')<='$add_time_to'";
            $conditions.=" and odr.add_time<='$add_time_to'";
        }
        if($_GET["buyer_name"])
        {
            $conditions.=" and odr.buyer_name like '%".$_GET["buyer_name"]."%'";
        }

        if($_GET["seller_name"])
        {
            $conditions.=" and odr.seller_name like '%".$_GET["seller_name"]."%'";
        }


        $user_ids=$this->_member_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$fwzx_into["region_id"],
            "fields"    =>"user_id"
        ));
        $query_conditions="";
        $query_conditions2="";
        if(count($user_ids)>0){
            foreach($user_ids as $k=>$v){
                $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                $query_conditions2.=" or user_id=".$v["user_id"]; //获得子账号下面的商家
            }

        }


        $pg=$page['limit'];

        $data=null;
        if($_GET["orderBy"]==null || $_GET["orderBy"]=="" || $_GET["orderBy"]=="order"){           //按订单为粒度
            $sql = "SELECT odr.order_id,odr.seller_name,odr.buyer_name,yl.type,odr.jine,odr.order_amount,odr.add_time FROM {$yue_log_mode->table} as yl,{$order_mode->table} as odr WHERE odr.order_sn=yl.order_sn AND yl.type = 6 AND yl.user_id =".$fwzxId.$conditions." order by odr.order_id desc  LIMIT $pg";
//            $sql="SELECT odr.`order_id`,odr.`order_sn`,odr.`buyer_name`,odr.`seller_name`,ticheng.`jine`,odr.order_amount as \"order_amount\",ticheng.ticheng*100 as \"ticheng\",odr.ticheng*100 as \"zhekou\",odr.`add_time`,ticheng.`status`
//            ,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=odr.seller_id) AS \"t_id_seller\"
//            ,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=odr.buyer_id) AS \"t_id_buyer\"
//                                                                                    FROM  ".DB_PREFIX."order odr,".DB_PREFIX."ticheng_detail ticheng
//                                                                                    WHERE odr.order_id=ticheng.`order_id`
//                                                                                    AND   (ticheng.`type`=3 OR ticheng.`type`=4)
//                                                                                    AND   (ticheng.`user_id`='$fwzxId' $conditions2) $conditions order by order_id desc  LIMIT $pg";

            $data = $yue_log_mode->getAll($sql);
            $CountSql="SELECT count(1) FROM {$yue_log_mode->table} as yl,{$order_mode->table} as odr WHERE odr.order_sn=yl.order_sn AND yl.type = 6 AND yl.user_id =".$fwzxId.$conditions." order by  odr.order_id desc";
            //echo $CountSql;
            $yue_log_mode->_updateLastQueryCount($CountSql);
        }else{                                                                                         //按商家为粒度查询
            $sql="SELECT odr.`seller_name`,SUM(ticheng.`jine`) AS \"jine\"  FROM ".DB_PREFIX."order odr,".DB_PREFIX."ticheng_detail ticheng
                                                                                    WHERE odr.order_id=ticheng.`order_id`
                                                                                    AND (ticheng.`type`=3 OR ticheng.`type`=4)
                                                                                    AND (ticheng.`user_id`='$fwzxId' $conditions2) $conditions
                                                                                    GROUP BY odr.`seller_name`
                                                                                    ORDER BY odr.`seller_name` DESC LIMIT $pg";

            $data = $yue_log_mode->getAll($sql);
            $CountSql="SELECT COUNT(1) COUNT FROM (    SELECT 1
                                                    FROM ".DB_PREFIX."order odr,".DB_PREFIX."ticheng_detail ticheng
                                                    WHERE odr.order_id=ticheng.`order_id`
                                                    AND (ticheng.`type`=3 OR ticheng.`type`=4)
                                                    AND (ticheng.`user_id`='$fwzxId' $conditions2) $conditions
                                                    GROUP BY odr.`seller_name`
                                                    )AS tmp";
            $yue_log_mode->_updateLastQueryCount($CountSql);
        }

        $total_money=0.00;
        if(count($data)>0){
            $total_money=$this->_member_mod->getOne("SELECT SUM(ticheng.jine) FROM ".DB_PREFIX."ticheng_detail ticheng WHERE
                                                 (ticheng.`type`=3 OR ticheng.`type`=4) AND (ticheng.`user_id`='$fwzxId' $conditions2)");
        }

        $order_Model=& m('order');

        $this->assign('total_money', $total_money);

        $page['item_count'] = $yue_log_mode->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('shouyi', $data);
        $this->assign('viewType', "service");
        $this->assign('queryInfo', array("order_id"=>$_GET["order_id"],"status"=>$_GET["status"]));

//        $fwzx_into = $this->visitor->get();
//        $fwzxId=$fwzx_into['user_id'];
//
//        $pageNo = ($_GET["pageNo"]?$_GET["pageNo"]:1);
//        $this->_config_seo('title', "我的收益" . ' - ' . Lang::get('member_center'));
//        //$params["userId"] = $this->visitor->get('user_id');
//        $params["userId"] = $fwzxId;
//        $pageNo  =1;
//        if(!empty($_GET["page"]) && $_GET["page"]!=""){
//            $pageNo = $_GET["page"];
//        }
//        $page_size = 20;
//        $params["pageSize"] = $page_size;
//        $params["pageNo"] = $pageNo;
//        $queryType = ($_GET["orderBy"] == "server_jiesuan" ? "server_jiesuan" : "server_shouyi");
//
//        if(!empty($_GET["orderBy"]) && $_GET["orderBy"]!=""){
//            $queryType = $_GET["orderBy"];
//        }
//
//        $params["type"] = $queryType;
//
//        if(!empty($_GET["order_sn"]) && $_GET["order_sn"]!=""){
//            $params["orderSn"] = $_GET["order_sn"];
//        }
//
//        if($_GET["add_time_from"]){
//            $add_time_from=$_GET["add_time_from"];
//            $params["orderBeginDate"] = $add_time_from;
//        }
//
//        if($_GET["add_time_to"])
//        {
//            $add_time_to=$_GET["add_time_to"];
//            $params["orderEndDate"] = $add_time_to;
//        }
//        if($_GET["buyer_name"]){
//            $params["buyerName"] = $_GET["buyer_name"];
//        }
//
//        if($_GET["seller_name"]){
//            $params["sellerName"] = $_GET["seller_name"];
//        }
//
//
//        $sql = "select * from "
//
//
//
//        $Client = new HttpClient(JAVA_LOCATION);
//        $url = "http://".JAVA_LOCATION.SERVICE_SHOUYI;
//        $pageContents = HttpClient::quickPost($url, $params);
//        $rs_data = json_decode($pageContents);
//        //echo $pageContents;
//
//        //$this->pr($rs_data);
//        //$this->pr($pageContents);
//
//        $data = null;
//
//        if(count($rs_data)>0){
//            $tmp = $rs_data[0];
//            $page = $this->_get_page($page_size);
//            $page['item_count'] = $tmp->totalPageNum;
//            $this->_format_page($page);
//            $this->assign('page_info', $page);
//            $this->assign('total_money', $tmp->totalGain);
//            foreach($rs_data as $k=>$v){
//                $data[$k] = array("buyerName"=>$v->buyerName,
//                                   "jiesuanStatus"=>$v->jiesuanStatus,
//                                    "jine"=>$v->jine,
//                                    "orderAmount"=>$v->orderAmount,
//                                    "orderDate"=>$v->orderDate,
//                                    "orderSn"=>$v->orderSn,
//                                    "sellerName"=>$v->sellerName,
//                                    "ticheng"=>$v->ticheng,
//                                    "yongjin"=>$v->yongjin,
//                                    "type"=>$v->type,
//                                    "channelName"=>$v->channelName,
//                                    "channelCard"=>$v->channelCard,
//                                    "jiesuanDate"=>$v->jiesuanDate
//                );
//            }
//        }
//        $this->assign('shouyi', $data);
        $this->display('service.shouyi.html');

    }
	function drop()
    {
		$goodsApp=new My_goodsApp();
		$goodsApp->drop();
    }
    function batch_edit()
    {
		$goodsApp=new My_goodsApp();
		$goodsApp->batch_edit();
    }

    //设置服务中心新增商家的 推广人
    function set_tuiguang($t_id){
        if($this->visitor->get("member_type")!="04"){
            $user_name = $this->visitor->get("user_name");
            //如果是韩国馆的帐号
            if (substr(strtoupper($user_name),0,9) == "DJKHANGUO") {
                $hanguo_data = $this->_member_mod->get("member_type='02' and dropState=1 and status=2 and user_name='djkhanguo'");
                if(!empty($hanguo_data)){
                    $this->_member_mod->edit($t_id,array("t_id"=>$hanguo_data["bind_user_id"]));
                }
            }else{
                //普通服务中心
                $user_id = $this->_member_mod->getOne("SELECT m1.bind_user_id AS 'user_id' FROM ecm_member m1 WHERE m1.`member_type`='02'
AND m1.`dropState`=1 AND m1.`status`=2 AND m1.`region_id` = (SELECT m2.region_id FROM ecm_member m2 WHERE m2.`user_id`='".$this->visitor->get("user_id")."' AND m2.`region_id`!=0)");
                if($user_id){
                    $this->_member_mod->edit($t_id,array("t_id"=>$user_id));
                }
            }
        }
    }

	function uploadStore(){
            if($_SESSION["files_store"] && $_SESSION["files_store"]!=""){
                $files_store = explode("###",$_SESSION["files_store"]);
                $this->assign('files_store', $files_store);
            }
            $this->assign("_curitem", "service");
            $channel_mod =& m('channel');
			$user = $this->visitor->get();
			$userInfo=$this->_member_mod->get($user['user_id']);
			if(!$_POST){
                $channels=$channel_mod->find(array(
                    "conditions"=>"(type=2 or type=3)",
                    'fields'=> "this.*"
                ));
                $this->assign('channels', $channels);
	            $this->assign('user', $userInfo);
				 $this->assign('jibiekey', empty($_GET["type"])?"01":$_GET["type"]);

	            $this->assign('store', array('state' => STORE_OPEN, 'recommended' => 0, 'sort_order' => 65535, 'end_time' => 0));

	            $sgrade_mod =& m('sgrade');
	            $this->assign('sgrades', $sgrade_mod->get_options());

	            $this->assign('states', array(
	                STORE_OPEN   => Lang::get('open'),
	                STORE_CLOSED => Lang::get('close'),
	            ));

	            $this->assign('recommended_options', array(
	                '1' => Lang::get('yes'),
	                '0' => Lang::get('no'),
	            ));

	            $this->assign('scategories', $this->_get_scategory_options());
                $this->assign('scategories_ticheng', $this->_get_scategory_min_ticheng());

                $init_tichengs2[1]=array();
                for($i=5;$i<=85;$i++){
                    $init_tichengs2[1][$i]=$i;
                }

                $this->assign('init_tichengs2',$init_tichengs2);

	            $region_mod =& m('region');
	            $this->assign('regions', $region_mod->get_options(0));

	            $this->assign('init_ticheng', Conf::get('ticheng'));

	            /* 导入jQuery的表单验证插件 */
	            $this->import_resource(array(
	                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
	            ));

                $region_mod =& m('region');
                $this->assign('regions', $region_mod->get_options(0));

                if($_GET["tuoguan"]){
                    $this->assign('tuoguan', 1);
                }

                $this->assign('ECM_ID', $_COOKIE['ECM_ID']);
                $this->assign('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);

	            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
                if($_GET["tuoguan"] == 1){
                    $this->display('store.form1.html');
                }else{
                    $this->display('store.form.html');
                }

			}else
			{
                    //$this->pr($_POST);exit;
                    //header('Content-type: text/json');
                    $store_mod  =& m('store');
                    $store_name = $_POST['store_name'];
                    $owner_name = $_POST['owner_name'];
                    $region_id = $_POST['region_id'];
                    $address = $_POST['address'];
                    $zipcode = $_POST['zipcode'];
                    $tel = $_POST['tel'];
                    $seller_type = $_POST["seller_type"];
                    if($store_name == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入店铺名","error_lbl"=>"store_name")).");</script>";
                        exit;
                    }
                    if (!$store_mod->unique($store_name, 0)){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"店铺名已经存在","error_lbl"=>"store_name")).");</script>";
                        exit;
                    }
                    if($owner_name == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入店主名","error_lbl"=>"owner_name")).");</script>";
                        exit;
                    }
                    if($region_id == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id")).");</script>";
                        exit;
                    }
                    if($address == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入详细地址","error_lbl"=>"address")).");</script>";
                        exit;
                    }
                    if($tel == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择联系电话","error_lbl"=>"tel")).");</script>";
                        exit;
                    }
                    if($seller_type == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择联商家类型","error_lbl"=>"seller_type")).");</script>";
                        exit;
                    }
                    if($_POST["tuoguan"] && $_POST["channel_id"]!=""){
                        $channel_id = $_POST["channel_id"];
                        $channel_user_name = $_POST["channel_user_name"];
                        $channel_kahao = $_POST["channel_kahao"];
                        $channel_name2 = $_POST["channel_name2"];
                        $channel_region_id = $_POST["channel_region_id"];
                        $channel_region_name = $_POST["channel_region_name"];
                        $channel_mod =& m('channel');

                        if($channel_id == ""){
                            echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择开户银行","error_lbl"=>"channel_id")).");</script>";
                            exit;
                        }
                        $channel = $channel_mod->get("(type=2 or type=3) and channel_id = $channel_id");
                        if( !$channel ){
                            echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择开户银行","error_lbl"=>"channel_id")).");</script>";
                            exit;
                        }

                        if($channel["channel_code"] == "alipay"){
                            if($channel_user_name == ""){
                                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入支付宝帐号真实姓名","error_lbl"=>"channel_user_name")).");</script>";
                                exit;
                            }
                            if($channel_kahao == ""){
                                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入支付宝帐号","error_lbl"=>"channel_kahao")).");</script>";
                                exit;
                            }
                        }else{
                            if($channel_user_name == ""){
                                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入银行开户名","error_lbl"=>"channel_user_name")).");</script>";
                                exit;
                            }
                            if($channel_kahao == ""){
                                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入卡号","error_lbl"=>"channel_kahao")).");</script>";
                                exit;
                            }
                            if($channel_name2 == ""){
                                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择开户行支行","error_lbl"=>"channel_name2")).");</script>";
                                exit;
                            }
                        }

                    }
                    $cate_id = intval($_POST['cate_id']);
                    if($cate_id<=0){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择店铺分类","error_lbl"=>"cate_id")).");</script>";
                        exit;
                    }
                    if(!$_POST["ticheng"] || $_POST["ticheng"]=="" || $_POST["ticheng"]==0){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择比例","error_lbl"=>"ticheng")).");</script>";
                        exit;
                    }

                    $region_id=$_POST["region_id"];
                    $region_mod =& m('region');
                    $ticheng=0;
                    $shangjialeixing=$_POST["seller_type"];       //商家类型

                    if($shangjialeixing ==""){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择商家类型","error_lbl"=>"seller_type")).");</script>";
                        exit;
                    }

                    $shangjialeixing+=1;
                    $scategory_mod =& m('scategory');
                    $scategories = $scategory_mod->get_info($cate_id);
                    if($shangjialeixing == 1 ){
                        $shangjialeixing="changshang_ticheng";
                    }
                    elseif($shangjialeixing == 2 ){
                        $shangjialeixing="dailishang_ticheng";
                    }
                    elseif($shangjialeixing == 3 ){
                        $shangjialeixing="lingshoushang_ticheng";
                    }
                    elseif($shangjialeixing == 4 ){
                        $ticheng=$_POST["ticheng"];
                        $shangjialeixing="shitidian_ticheng";
                    }
                    if($shangjialeixing == 4 && $ticheng/100 < $scategories[$shangjialeixing]){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"您选择的比例不能小于店铺的最小比例","error_lbl"=>"ticheng")).");</script>";
                        exit;
                    }
                    $data=$region_mod->get_options($region_id);
                    if(count($data)>0){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"选择地区错误，必须选择到最后一级!","error_lbl"=>"ticheng")).");</script>";
                        return;
                    }
                    $cate_id=$_POST["cate_id"];
                    if($cate_id==0){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，必须选择商家分类!","error_lbl"=>"cate_id")).");</script>";
                        return;
                    }
                    $store_id="";
                    $member_mod  =& m('member');

                    //如果选择了已有用户
                    if($_POST["user_type"] == "2" ){
                        $user = $member_mod->get("user_name='".$_POST["user_name_new"]."'");
                        if(empty($user)){
                            echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"用户名不存在","error_lbl"=>"userName")).");</script>";
                            exit;
                        }
                        $store_id  = $user["user_id"];
                    }else{
                        //新增页面
                        $userNew = array(
                            "user_name"=> $_POST["user_name_new"],
                            "member_type"=>"01",
                            "region_name"=>$_POST["region_name"],
                            "region_id"=>$_POST["region_id"],
                            "nick_name"    => $_POST["user_name_new"],
                            'reg_time'  => gmtime(),
                            'nick_name' => $_POST["user_name_new"]
                        );
                        $userNew["password"] = md5($_POST["password"]);

                        $store_id = $member_mod->add($userNew);

                        $this->set_tuiguang($store_id);     //设置提交的商家的推荐人
                    }

					//是否托管
					$flag=empty($_POST["tuoguan"])?0:$_POST["tuoguan"];
                    //$store_id = $this->visitor->get('user_id');


                    $data = array(
                        'store_id'     => $store_id,
                        'store_name'   => $_POST['store_name'],
                        'owner_name'   => $_POST['owner_name'],
                        'owner_card'   => $_POST['owner_card'],
                        'region_id'    => $_POST['region_id'],
                        'region_name'  => $_POST['region_name'],
                        'address'      => $_POST['address'],
                        'zipcode'      => $_POST['zipcode'],
                        'tel'          => $_POST['tel'],
                        'sgrade'       => 2,
                        //'apply_remark' => $_POST['apply_remark'],
                        'state'        => 0,
                       // 'jibie'        =>$tichengs[0],
                        'ticheng'        =>$ticheng/100,
                        'add_time'     => gmtime(),
                        'fwzx'         =>$this->visitor->get('user_id'),
						'tuoguan'      =>$flag,
                        'yyzz'         => $_POST['yyzz'],
                        'seller_type' => $seller_type,
                        'description2' => $_POST["description2"]
                        //'yaoqingma'     =>empty($_SESSION["invi_code"])?Conf::get('yaoqingma'):base64_decode(trim($_SESSION["invi_code"])),
                    );

                    $image = $this->_upload_image($store_id);
                    if ($this->has_error()){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>$this->get_error(),"owner_name_lbl"=>"cate_id")).");</script>";
                        return;
                    }
                    // 判断是否已经申请过
                    $store_info=$this->_store_mod->get_info($store_id);
                    if (!empty($store_info)){

                        $user_type = $store_id;
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"该用户店铺已经存在","error_lbl"=>"userName")).");</script>";
                        exit;
                        //$store_mod->edit($store_id, array_merge($data, $image));
                    }
                    else{
					    $store_mod->add(array_merge($data, $image));
                    }

                    if ($store_mod->has_error()){
                        echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>$store_mod->get_error(),"error_lbl"=>"store_name")).");</script>";
                        return;
                    }
                    $store_mod->unlinkRelation('has_scategory', $store_id);
                    if ($cate_id > 0){
                        $store_mod->createRelation('has_scategory', $store_id, $cate_id);
                        if($_POST["tuoguan"]  &&  $_POST["channel_id"]!=""){
                            $channel_id = $_POST["channel_id"];
                            $channel_user_name = $_POST["channel_user_name"];
                            $channel_kahao = $_POST["channel_kahao"];
                            $channel_name2 = $_POST["channel_name2"];
                            $channel_region_id = $_POST["channel_region_id"];
                            $channel_region_name = $_POST["channel_region_name"];

                            date_default_timezone_set('Asia/Shanghai');
                            $bank_mod =& m('memberbank');
                            $data=array(
                                "user_id"    => $store_id,
                                "bank_name" => $channel["channel_name"],
                                "bank_code" => $channel["channel_code"],
                                "kaihuhang" => $channel_name2,
                                "kahao"     => $channel_kahao,
                                "user_name" => $channel_user_name,
                                "region_id" => $channel_region_id,
                                "region_name" => $channel_region_name,
                                "if_default" => 1,
                                "add_time"   => date('Y-m-d H:i:s')
                            );
                            $bank_mod ->add($data);
                        }
                    }

                    $store_uploaded_file = $_POST["store_uploaded_file"];
                    if($store_uploaded_file && count($store_uploaded_file)>0){
                        $storeuploadedfile_mod =& m('storeuploadedfile');
                       for($i=0;$i<count($store_uploaded_file);$i++){
                            $file_date["store_id"] = $store_id;
                            $file_date["file_path"] = $store_uploaded_file[$i];
                            $file_date["dropState"] = 1;

                            $file_date["add_time"] = gmtime();
                            $if_default = 0;
                            if($_POST["if_default"] == $store_uploaded_file[$i]){
                                $if_default =1;
                            }
                             $file_date["if_default"] = $if_default;
                             $make_app = new Make_imageApp();
                             $make_app->_upload_store_image($file_date["file_path"]);
                             $storeuploadedfile_mod->add($file_date);

                       }
                    }
                    $store_mod->commit_transaction();
                    if($_SESSION["files_store"] && $_SESSION["files_store"]!=""){
                        $_SESSION["files_store"] = "";
                    }
                    $params = null;
                    $params["type"] = "regist";
                    $params["userId"] = $store_id;
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

                    $this->send_feed('store_created', array(
                        'user_id'   => $this->visitor->get('user_id'),
                        'user_name'   => $this->visitor->get('user_name'),
                        'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
                        'seller_name'   => $data['store_name'],
                    ));
                    if($_SESSION["files_store"] && $_SESSION["files_store"]!=""){
                        $_SESSION["files_store"] = "";
                    }
                    $this->_hook('after_opening', array('user_id' => $store_id));
                    echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"ok")).");</script>";
			}

	}


    /* 上传图片 */
    function _upload_image2($store_id,$file_name)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $file = $_FILES[$file_name];
        if ($file['error'] == UPLOAD_ERR_OK){
            if (empty($file)){
                return "";
            }
            $uploader->addFile($file);
            if (!$uploader->file_info()){
                $this->_error($uploader->get_error());
                return "";
            }
            $uploader->root_dir(ROOT_PATH);
            $up_dir=Conf::get('up_dir');
            $dirname   = $up_dir. '/application';
            list($usec, $sec) = explode(" ", microtime());
            $name=((float)$usec + (float)$sec);
            $tmp=explode(".",$name);
            $time_s=$tmp[1];
            if($time_s == ""){
                $time_s = "00";
            }elseif(strlen($time_s) == 1){
                $time_s = $time_s."0";
            }
            date_default_timezone_set('Asia/Shanghai');
            $sn = date('ymdHis').$time_s;
            $filename  = 'store_' . $store_id.$sn;
            return  $uploader->save($dirname, $filename);
        }
    }

    function uploadStore2()
    {
        $this->uploadStore();
    }

	/* 取得店铺分类 */
    function _get_scategory_options()
    {
        $mod =& m('scategory');
        $scategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');

        return $tree->getOptions();
    }

    /* 取得分类最小提成 */
    function _get_scategory_min_ticheng()
    {
        $mod =& m('scategory');
        $scategories = $mod->find(array
            (
                "conditions"=> " dropState=1 order by cate_id desc",
                'fields' => 'cate_id,changshang_ticheng,dailishang_ticheng,lingshoushang_ticheng,shitidian_ticheng',
            )
        );
        return $scategories;
    }

    /* 上传图片 */
    function _upload_image($store_id)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $data = array();
        for ($i = 1; $i <= 3; $i++)
        {
            $file = $_FILES['image_' . $i];
            if ($file['error'] == UPLOAD_ERR_OK)
            {
                if (empty($file))
                {
                    continue;
                }
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $this->_error($uploader->get_error());
                    return false;
                }

                $uploader->root_dir(ROOT_PATH);
                $up_dir=Conf::get('up_dir');
                $dirname   = $up_dir. '/application';
                $filename  = 'store_' . $store_id.gmtime() . '_' . $i;
                $data['image_' . $i] = $uploader->save($dirname, $filename);
            }
        }
        return $data;
    }


	 /**
     *    生成订单号
     *
     *    @author    Garbin
     *    @return    string
     */
    function _gen_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('order');
        $orders = $model_order->find('order_sn=' . $order_sn);
        if (empty($orders))
        {
            /* 否则就使用这个订单号 */
            return $order_sn;
        }

        /* 如果有重复的，则重新生成 */
        return $this->_gen_order_sn();
    }

    //开单
    function kaidan()
    {
		 	/* 取得店铺商品分类 */
			if(!$_POST)
			{
				$goodsApp=new My_goodsApp();
		        $this->display('service_kaidan.html');
			}else
			{

					if(!$_POST["savaOrder"])
					{
						if(empty($_POST["goodsid"]))
						{
							return;
						}

						$goods_ids=$_POST["goodsid"];

						$goodsMol =& m('goods');

						$goodsList=$goodsMol->find(array
						(
						'conditions' => " 1=1 and goods_id in ($goods_ids)",
						));

					   /* 导入jQuery的表单验证插件 */
				       $this->import_resource(array(
				                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
				       ));
					   $this->assign('goodsList', $goodsList);



					   $this->display('service_kaidan_step2.html');
					}else
					{

//                        $this->pr($_POST);
//                        exit;
                        $order_sn=$this->_gen_order_sn();

                        /* 默认都是待付款 */
                        $order_status = ORDER_PENDING;

                        /* 买家信息 */

                        $user_name=$_POST["user_name"];
                        $dataInfo=$this->_member_mod->get(array(
                            'conditions' => "1=1 AND user_name ='$user_name'",
                            'fields' => 'user_name,user_id,email',));

                        $user_id     =  $dataInfo['user_id'];
                        $user_name   =  $dataInfo['user_name'];
                        $user_email   =  $dataInfo['email'];
                        //获得商品id list
                        $goodsList=$_POST["goods_id"];

                        //获得商品价格 list
                        //$priceList=$_POST["price"];

                        //获得商品 数量 list
                        $quantityList=$_POST["quantity"];

                        //运费
                        $yunfei=empty($_POST["yunfei"])?0:$_POST["yunfei"];

                        //获得卖家的提成比例
                        /*$visitor     =& env('visitor');
                        $visitor_id     =  $visitor->get('user_id');
                        $visitor_name   =  $visitor->get('user_name');*/
                        $model_store =& m('store');
                        $sellerInfo    = $model_store->get_info(intval($_POST["store_id"]));

                        //提成比例
                        $ticheng=$sellerInfo["ticheng"];
                        //提成金额
                        //$jine=$_POST['allPrice']*$ticheng;

                        $jine=0;
                        //$price=$_POST["price"];
                        $quantity=$_POST["quantity"];
                        for($i=0;$i<count($goodsList);$i++)
                        {
                            //防止注入，商品价格从数据库获得
                            $goods_t=$this->_goods_mod->get_info($goodsList[$i]);
                            //$this->print_r($goods_t);exit;
                            $jine+=$goods_t['price']*$quantity[$i];
                        }
                        /* 返回基本信息 */

                        $order_model =& m('order');
                        $order_id    = $order_model->add(array(
                            'order_sn'      =>  $this->_gen_order_sn(),
                            'type'          =>  "material",
                            'extension'     =>  "normal",
                            'seller_id'     =>  $_POST["store_id"],
                            'seller_name'   =>  $sellerInfo["owner_name"],
                            'buyer_id'      =>  $user_id,
                            'buyer_name'    =>  addslashes($user_name),
                            'buyer_email'   =>  $user_email,
                            'status'        =>  30,             //服务中心上传的订单统一为已发货，需要买家自己去确认
                            'add_time'      =>  gmtime(),
                            'goods_amount'  =>  $_POST['allPrice'],
                            'discount'      =>  isset($goods_info['discount']) ? $goods_info['discount'] : 0,
                            'anonymous'     =>  intval($post['anonymous']),
                            'postscript'    =>  '服务中心线下交易',
                            'ticheng'       =>  $ticheng,
                            'jine'          =>  $jine,
                            'payment_id'    =>  '3',             //统一设置支付方式为线下支付
                            'payment_name'  =>  '服务中心-线下支付',
                            'pay_message'  =>  '服务中心已经上传订单，待用户确认',
                            'payment_code'     =>  'xianxia',
                            // 'order_amount' =>  floatval($_POST['allPrice'])+floatval($yunfei)
                            'order_amount' =>  $jine
                        ));

                        $address_id=$_POST["address_id"];
                        $model_address =& m('address');
                        $addressInfo=null;
                        if($address_id>0){
                            $addressInfo=$model_address->get_info($address_id);
                        }else{
                            $addressInfo=array("consignee"=>user_name,"address"=>$_POST["new_address"]);
                        }

                        $consignee_info= array(
                            'consignee'     =>  $addressInfo["consignee"],
                            'region_id'     =>  $addressInfo['region_id'],
                            'region_name'   =>  $addressInfo['region_name'],
                            'address'       =>  $addressInfo['address'],
                            'zipcode'       =>  $addressInfo['zipcode'],
                            'phone_tel'     =>  $addressInfo['phone_tel'],
                            'phone_mob'     =>  $addressInfo['phone_mob'],
                            'shipping_id'   =>  $addressInfo['shipping_id'],
                            'shipping_fee'  =>  0,
                        );
                        /* 插入收货人信息 */
                        $consignee_info['order_id'] = $order_id;
                        $order_extm_model =& m('orderextm');
                        $order_extm_model->add($consignee_info);

                        /* 记录订单操作日志 */
                        $order_log =& m('orderlog');
                        $order_log->add(array(
                            'order_id'  => $order_id,
                            'operator'  => addslashes($this->visitor->get('user_name')),
                            'order_status' => order_status(ORDER_SUBMITTED),
                            'changed_status' => order_status(ORDER_SHIPPED),
                            'remark'    => "服务中心开单，自动变成已发货状态",
                            'log_time'  => gmtime(),
                        ));

                        /* 插入商品信息 */
                        $goods_items = array();

                        foreach ($goodsList as $key => $value)
                        {

                            $goods_tmp=$this->_goods_mod->get_info($value);
                            $image="";
                            if(count($goods_tmp["_images"])>0)
                            {
                                $image=$goods_tmp["_images"][0];
                            }

                            $goods_items[] = array(
                                'order_id'      =>  $order_id,
                                'goods_id'      =>  $value,
                                'goods_name'    =>  $goods_tmp['goods_name'],
                                'spec_id'       =>  $goods_tmp['default_spec'],
                                'specification' =>  $goods_tmp['specification'],
                                'price'         =>  $goods_tmp['price'],
                                'quantity'      =>  $quantityList[$key],
                                'goods_image'   =>  $image['thumbnail'],
                            );


                        }
                        $order_goods_model =& m('ordergoods');
                        $order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入
                        /* 减去商品库存 */
                        $order_model->change_stock('-', $order_id);

                        $this->show_message('开单成功',
                            'index', 'index.php?app=service');

			}

			}

    }



    function query_goods()
    {
		/* 取得店铺商品分类 */
        $this->my_goods();
    }
    function orders()
    {
        $Seller_orderApp=new Seller_orderApp();
        $this->assign('ordersType', '1');
        $user_name =  $this->visitor->get("user_name");
        $count = count(explode("djkhanguo", $user_name));
        if($count == 1 ){
            $this->assign('showBenxiaqu', '1');
        }

        $Seller_orderApp->index();
    }

    function orders_pos(){
        $page = $this->_get_page();
        $model_order =& m('order');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
        $conditions = "1=1 and type='pos'";

        $conditions .= $this->_get_query_conditions(array(
            array(      //按买家名称搜索
                'field' => 'buyer_name',
                'equal' => 'LIKE',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
        ));

        /* 查找订单 */
        $v=$this->visitor->get();
        $user = $this->visitor->get();
        $visitorId=$user['user_id'];

        $member_mod =& m('member');
        $service_info=$member_mod->get_info($visitorId);

        $query="";
        if(!empty($service_info['region_id']))
        {
            $query.=" and s.region_id='".$service_info['region_id']."'";
        }

        if($_GET["store_id"]!="-1" && $_GET["store_id"]!="")
        {
            $query.=" and s.store_id='".$_GET["store_id"]."'";
        }
        $service_region_id = $user["region_id"]?$user["region_id"]:-1;
        $conditions .= " and EXISTS (select 1 from ".DB_PREFIX."store s where  s.dropState=1 and s.region_id=$service_region_id $query and order_alias.seller_id = s.store_id  )";
        $data=array(
            'conditions'    => $conditions,
            'count'         => true,
            'fields'        => "this.*,(order_amount-yue-koushui_yue) as yue_jine, (yue+koushui_yue) as zhifu ",
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        );

        $orders = $model_order->findAll($data);
        $orderextm_mod =& m('orderextm');
        foreach ($orders as $key1 => $order){
            $orderextm = $orderextm_mod->get($key1);
            if($orderextm["order_id"] != "" && $orderextm["order_id"] >0 ){
                $orders[$key1]["consignee"] = $orderextm["consignee"];
                $orders[$key1]["phone_mob"] = $orderextm["phone_mob"];
                $orders[$key1]["phone_tel"] = $orderextm["phone_tel"];
            }
            if ($order['order_goods']) {
                foreach ($order['order_goods'] as $key2 => $goods) {
                    empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
                }
            }
        }

        $store_mod =& m('store');
        $stores = $store_mod->find(array(
            'conditions' => " s.region_id=$service_region_id  and s.dropState=1 and state=1",
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name'
        ));

        $this->assign('stores', $stores);


        $page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('types', array('all' => Lang::get('all_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);

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
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));

        $this->display('service.order_pos.html');
    }

    //本辖区订单
    function benqu_orders() {
        $Seller_orderApp=new Seller_orderApp();
        $this->assign('ordersType', '1');
        $user_name =  $this->visitor->get("user_name");
        $count = count(explode("djkhanguo", $user_name));
        if($count == 1 ){
            $this->assign('showBenxiaqu', '1');
        }
        $Seller_orderApp->index();
    }
    function order_goods(){
        $Seller_orderApp=new Seller_orderApp();
        $this->assign('ordersType', '1');
        $Seller_orderApp->order_goods();
    }

    function shouhou(){
        $Seller_orderApp=new Seller_orderApp();
        $this->assign('ordersType', '1');
        $Seller_orderApp->shouhou();
    }

    function cancel_order()
    {
        $Seller_orderApp=new Seller_orderApp();
        $Seller_orderApp->cancel_order();
    }

    function tuangou(){
        $seller_groupbuyApp=new Seller_groupbuyApp();

        $this->assign('tuangouType', '1');

        $user = $this->visitor->get();
        $fwzxId=$user['user_id'];



        $userInfo=$this->_member_mod->get($fwzxId);
        $conditions="";
        //只获取该服务中心下的
        if(!empty($userInfo['region_id']))
        {
            $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
        }


        $user_ids=$this->_member_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
            "fields"    =>"user_id"
        ));
        $query_conditions="";
        if(count($user_ids)>0 && $userInfo["member_type"]=="02"){
            foreach($user_ids as $k=>$v){
                $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
            }

        }

        $store_mod =& m('store');
        $stores = $store_mod->find(array(
            'conditions' => " (fwzx=$fwzxId $query_conditions) and tuoguan=1 and s.dropState=1 and state=1".$conditions,
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name'
        ));
        $this->assign('stores', $stores);

        $seller_groupbuyApp->index();
    }

    function gselector(){
        $gselectorApp=new GselectorApp();

        $this->assign('tuangouType', '1');

        $gselectorApp->store();
    }
    function tuangou_log(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->log();
    }

    function tuangou_desc(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->desc();
    }

    function tuangou_finished(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->finished();
    }

    function tuangou_export_ubbcode(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->export_ubbcode();
    }

    function tuangou_drop(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->drop();
    }
    function tuangou_cancel(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->cancel();
    }

    function tuangou_start(){
        $seller_groupbuyApp=new Seller_groupbuyApp();
        $seller_groupbuyApp->start();
    }

    function tuangou_edit(){
        $seller_groupbuyApp=new Seller_groupbuyApp();

        $this->assign('tuangouType', '1');

        $seller_groupbuyApp->edit();
    }

    function tuangou_add(){
        $seller_groupbuyApp=new Seller_groupbuyApp();

        $this->assign('tuangouType', '1');

        $user = $this->visitor->get();
        $fwzxId=$user['user_id'];



        $userInfo=$this->_member_mod->get($fwzxId);
        $conditions="";
        //只获取该服务中心下的
        if(!empty($userInfo['region_id']))
        {
            $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
        }

        $user_ids=$this->_member_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
            "fields"    =>"user_id"
        ));
        $query_conditions="";
        if(count($user_ids)>0 && $userInfo["member_type"]=="02"){
            foreach($user_ids as $k=>$v){
                $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
            }

        }

        $store_mod =& m('store');
        $stores = $store_mod->find(array(
            'conditions' => " (fwzx=$fwzxId $query_conditions) and tuoguan=1 and s.dropState=1 and state=1".$conditions,
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name'
        ));
        $this->assign('stores', $stores);

        $seller_groupbuyApp->add();
    }

    function adjust_fee()
    {
        $Seller_orderApp=new Seller_orderApp();
        $Seller_orderApp->adjust_fee();
    }
    function  shipped()
    {
        $Seller_orderApp=new Seller_orderApp();
        $Seller_orderApp->shipped();
    }
    function received_pay()
    {
        $Seller_orderApp=new Seller_orderApp();
        $Seller_orderApp->received_pay();
    }
    function view()
    {
        $Seller_orderApp=new Seller_orderApp();
        $this->assign('ordersType', '1');
        $Seller_orderApp->view();
    }
    function confirm_order()
    {
        $Seller_orderApp=new Seller_orderApp();
        $Seller_orderApp->confirm_order();
    }

    //服务中心资料
    function service_info()
    {
        $serviceInfo_model =& m('serviceInfo');

        $visitor     =& env('visitor');
        $visitor_id     =  $visitor->get('user_id');

        $user_mod =& m('member');
        $info = $user_mod->get_info($visitor_id);

        if($info["region_name"]!="")
        {
            $this->assign('region_name',$info["region_name"]);
        }
        if(!$_POST)
        {

            $serviceInfo=$serviceInfo_model->get("service_id=$visitor_id and type=1 limit 0,1");

            if($serviceInfo["id"]>0){
                $this->assign('serviceInfo',$serviceInfo);
            }

            $this->_config_seo('title', "我的资料" . ' - ' . Lang::get('member_center'));

            $this->display('service.profile.html');

        }else
        {
            $id=$_POST["id"];
            $service_id=$_POST["service_id"];
            $data=array(
                'service_id'=>$service_id,
                'service_user'=>$_POST["service_name"],
                'service_gender'=>$_POST["service_gender"],
                'service_age'=>$_POST["service_age"],
                'service_tel'=>$_POST["service_tel"],
                'service_tel2'=>$_POST["service_tel2"],
                'service_email'=>$_POST["service_email"],
                'service_address'=>$_POST["service_address"],
                'beizhu'=>$_POST["beizhu"],
                'service_zhengjian'=>$_POST["service_zhengjian"],
             );
            $serviceInfo=null;
            if($id=="")
            {
                $list=$serviceInfo_model->find("service_id=$service_id and type=1");
                if(count($list)==0)
                {
                    $newId=$serviceInfo_model->add($data);
                    $serviceInfo=$serviceInfo_model->get_info($newId);
                }
            }else
            {
               $serviceInfo_model->edit($id,$data);
               $serviceInfo=$serviceInfo_model->get_info($id);

            }
            // exit;
            $this->assign('serviceInfo',$serviceInfo);
            $this->display('service.profile.html');
        }

    }
    function peisong()
    {
        $peisong_mod=new My_shippingApp();
        $peisong_mod->index();
    }
    function yunfei()
    {
        $peisong_mod=new My_shippingApp();
        $peisong_mod->yunfei();
    }

    function add_shipping()
    {
        $peisong_mod=new My_shippingApp();
        $peisong_mod->add();
    }
    function edit_shipping()
    {
        $peisong_mod=new My_shippingApp();
        $peisong_mod->edit();
    }
    function delete_shipping()
    {
        $peisong_mod=new My_shippingApp();
        $peisong_mod->drop();
    }

    function import_taobao()
    {
        $goodsApp=new My_goodsApp();
        $goodsApp->import_taobao();
    }

    //还原服务中心
    function initservice()
    {
        /*$serviceId=$_POST["service_id"];
        if($serviceId!="")
        {
            $count=$this->_member_mod->getOne("select count(1) from ".DB_PREFIX."service_shenqing where service_id=$serviceId and type=0
                                        AND paymentState=0");
            if($count>0){
                $this->_member_mod->edit($serviceId,array("status"=>0));
                $this->_member_mod->db_query("update ".DB_PREFIX."service_shenqing set type=-1,update_time=".gmtime()." where type=0 and paymentState=0 and service_id=$serviceId");
            }
        }*/

    }


    //前台抢注服务中心
    function register_service()
    {
        $this->_config_seo('title', '抢注服务中心 - 大集客网上商城');
        $this->display('service.register.html');
    }
    function queryService($queryName)
    {
        //   目前定为抢注后10小时之内可以提交营业执照        60*60*10= 36000       10小时
        $service = $this->_member_mod->get(array(
            "conditions"=> "member_type='02' and status in (0,1,2) and dropState=1 and region_name like '%$queryName%' limit 0,1"
        ));
        $shenqing_model=& m("serviceShenqing");
        if($service["user_id"] >0 ){
            $service_id=$service["user_id"];
            $sq=$shenqing_model->get(array(
                                 "conditions" => "type =0 and service_id = $service_id order by add_time desc limit 0,1",
                                 "fields"     => ''.QIANGZHU_FAIL_TIME.'-('.gmtime().'-add_time) as q_time,
                                           paymentState,yingyezhizhaoImg,shenfenzhengImg ',
            ));
            $service["flag"] =0;       //是否可以抢注，默认0不能抢注，1可以抢注
            if($sq["id"]>0){
                $service["q_time"]=$sq["q_time"];
                $service["paymentState"]=$sq["paymentState"];
                $service["yingyezhizhaoImg"]=$sq["yingyezhizhaoImg"];
                $service["shenfenzhengImg"]=$sq["shenfenzhengImg"];
                $service["shenqing_id"]=$sq["id"];

                if($service["status"] !=2 && $service["paymentState"]==0 && $service["q_time"] <=0 ){
                    $service["flag"] = 1;
                }
            }elseif($service["status"] !=2){
                $service["flag"] = 1;
            }
        }
        //$this->pr($service);exit;
        return $service;
    }

    //检查服务中心状态,检查申请状态
    function checkService($serviceId,$user_id)
    {
           $shenqing_model=& m("serviceShenqing");
           $serviceInfo= $this->_member_mod->get_info($serviceId);
            if(count($serviceInfo)<1 || $serviceInfo["member_type"]!="02" || $serviceInfo["dropState"]!=1)
            {
                $this->show_warning('service_empty');   //服务中心不存在
                exit;
            }
            $count = $this->_member_mod->getOne("SELECT COUNT(1) FROM ecm_service_shenqing WHERE service_id = $serviceId AND
                ((".gmtime()."-add_time > 36000 AND paymentState = 1 and type = 0) OR ".gmtime()."-add_time < 36000)");
            if($serviceInfo["status"]==0 && $count > 0)
            {

                    $conditions="";
                    $shenqing = $shenqing_model->get(array(
                            "conditions"=> "service_id=$serviceId and member_id=$user_id and (type=0 or type=1 ) order by id desc",
                            'fields' => 'id,paymentState,yingyezhizhaoImg,shenfenzhengImg,service_zhengjian,type',
                     ));

                    if(!$shenqing)     //没有找到结果，说明不是登入人预定的
                    {
                        $this->show_warning('service_status_1');
                        exit;
                    }

                    if($shenqing["paymentState"]==0){
                        $this->show_warning('service_status_msg');
                        exit;
                    }

                    //  第2次提交资料 未审核的时候 ，没有提交营业执照，身份证等资料
                    if(empty($shenqing["yingyezhizhaoImg"]))
                    {
                        if($_FILES)
                        {

                            $data=$this->uploadImg($serviceId);
                            if(count($data)>0 && $serviceId!="")
                            {
                                //$shenfenzhengImg=$data["shenfenzhengImg"];
                                $yingyezhizhaoImg=$data["yingyezhizhaoImg"];
                                //$service_zhengjian=$_POST["service_zhengjian"];

                                $shenqing_model->db_query("update ".DB_PREFIX."service_shenqing set yingyezhizhaoImg='$yingyezhizhaoImg'
                                                           where service_id=$serviceId and type in (0,1) and member_id = $user_id ");
                                if($shenqing["type"]==1){
                                    $shenqing_model->db_query("update ".DB_PREFIX."service_info set yingyezhizhaoImg='$yingyezhizhaoImg'
                                                           where service_id=$serviceId and type=1 ");
                                }
                                $shenqing_model->commit_transaction();
                                $this->show_message('uploadServiceInfoOk',
                                    'back_list', "index.php?app=member&act=qiangzhu&p=userInfo");
                                exit;
                            }

                        }else{
                            $this->assign('serviceid',$_GET["serviceid"]);
                            $this->assign('uploadImg',1);
                            $this->display('service.register2.html');
                            exit;
                        }

                    }else{
                        $this->show_warning('service_status_msg3');
                        exit;
                    }



            }else if($serviceInfo["status"]==2)         //服务中心已经被抢注成功
            {

                $shenqing = $shenqing_model->get(array(
                    "conditions"=> "service_id=$serviceId and member_id=$user_id and type=1 order by id desc",
                    'fields' => 'id,paymentState,yingyezhizhaoImg,shenfenzhengImg,service_zhengjian,type',
                ));

                if($shenqing["id"]<1)     //没有找到结果，说明不是登入人预定的
                {
                    $this->show_warning('service_status_2');
                    exit;
                }

                //  第2次提交资料 未审核的时候 ，没有提交营业执照，身份证等资料
                if(empty($shenqing["yingyezhizhaoImg"]))
                {
                    if($_FILES)
                    {
                        $data=$this->uploadImg($serviceId);

                        if(count($data)>0 && $serviceId!="")
                        {
                            //$shenfenzhengImg=$data["shenfenzhengImg"];
                            $yingyezhizhaoImg=$data["yingyezhizhaoImg"];
                            //$service_zhengjian=$_POST["service_zhengjian"];

                            $shenqing_model->db_query("update ".DB_PREFIX."service_shenqing set yingyezhizhaoImg='$yingyezhizhaoImg'
                                                           where service_id=$serviceId and type in (0,1) and member_id = $user_id ");
                            if($shenqing["type"]==1){
                                $shenqing_model->db_query("update ".DB_PREFIX."service_info set yingyezhizhaoImg='$yingyezhizhaoImg'
                                                           where service_id=$serviceId and type=1 ");
                            }
                            $shenqing_model->commit_transaction();
                            $this->show_message('uploadServiceInfoOk',
                                'back_list', "index.php?app=member&act=qiangzhu&p=userInfo");
                            exit;
                        }

                    }else{
                        $this->assign('serviceid',$_GET["serviceid"]);
                        $this->assign('uploadImg',1);
                        $this->display('service.register2.html');
                    }

                }else{
                    $this->show_warning('service_status_msg3');
                    exit;
                }


            }
    }

    /* 上传营业执照图片 */
    function uploadImg($service_id)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $data = array();
        $yingyezhizhaoImg = $_FILES['yingyezhizhaoImg'];
        $shenfenzhengImg = $_FILES['shenfenzhengImg'];

        if ($yingyezhizhaoImg !="" && $yingyezhizhaoImg['error'] == UPLOAD_ERR_OK){
                if (empty($yingyezhizhaoImg)){
                    exit;
                }
                $uploader->addFile($yingyezhizhaoImg);
                if (!$uploader->file_info()){
                    $this->_error($uploader->get_error());
                    return false;
                }
                $uploader->root_dir(ROOT_PATH);
                $dirname   = Conf::get('up_dir'). '/application';
                $filename  = 'yingyezhizhaoImg' . $service_id . '_' . gmtime();
                $data["yingyezhizhaoImg"] = $uploader->save($dirname, $filename);
        }

        if ($shenfenzhengImg !="" && $shenfenzhengImg['error'] == UPLOAD_ERR_OK){
            if (empty($shenfenzhengImg)){
                exit;
            }
            $uploader->addFile($shenfenzhengImg);
            if (!$uploader->file_info()){
                $this->_error($uploader->get_error());
                return false;
            }
            $uploader->root_dir(ROOT_PATH);
            $dirname   = Conf::get('up_dir'). '/application';
            $filename  = 'shenfenzhengImg' . $service_id . '_' . gmtime();
            $data["shenfenzhengImg"] = $uploader->save($dirname, $filename);
        }
        return $data;
    }

    //根据条件查询服务中心
    function queryByqiangzhu()
    {
        $this->_config_seo('title', '抢注服务中心 - 大集客网上商城');
        if(!$_POST)
        {
            if(!$_GET["serviceid"])
            {
                $serachName=$_GET["serachName"];
                /*$tmp=explode("-",$serachName);
                $count=count($tmp);
                $queryName=$tmp[$count-1];*/
                $serachName=str_replace("-","\t",$serachName);
                $list=$this->queryService($serachName);
                $list=array(1=>$list);
               //修改自动扫描java定时任务清除无效的申请为手动触发，当前台用户搜索该区域的时候触发， if 服务中心状态=1 并且 申请时候超时了10小时 ，还原回去服务中心状态
               /*$j=0;
               if(count($list)>0){
                    foreach($list as $k=>$v){
                       if($v["status"]==1 && $v["q_time"]<=0 && $v["paymentState"]==0){
                               $this->_member_mod->edit($v["user_id"],array("status"=>0));
                               $this->_member_mod->db_query("update ".DB_PREFIX."service_shenqing set type=-1,update_time=".gmtime()." where type=0 and service_id=".$v["user_id"]);
                               $j++;
                       }
                    }
                }
                if($j>0)
                {
                    //这里只重复1次，在页面展示的页面也会做1个判断，if 服务中心状态=1 并且 申请时候超时了10小时 ，ajax 还原回去服务中心状态
                    $list=$this->queryService($queryName);
                }*/
                //$this->pr($list);
                $this->assign('list',$list);
                $this->display('service.queryByqiangzhu.html');
            }else
            {
                /* 只有登录的用户才可访问 */
                if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user','check_invi_code','check_user2')))
                {
                    if (!IS_AJAX)
                    {
                        header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                        return;
                    }
                    else
                    {
                        $this->json_error('login_please');
                        return;
                    }
                }

                $user = $this->visitor->get();
                $serviceId=$_GET["serviceid"];

                $this->checkService($serviceId,$user["user_id"]);

                //获得最近的申请记录
                $shenqing_model=& m("serviceShenqing");
                $shenqings=$shenqing_model->find(array("conditions"=>"member_id=".$user["user_id"]." order by id desc limit 0,3"));

                $this->assign('shenqings',$shenqings);


                $serviceInfo_model =& m('serviceInfo');
                $service_info=$serviceInfo_model->get("service_id=$serviceId and type=1");
                if($service_info["id"]<=0 || $service_info["service_jibie"]==""){
                    exit("没有找到对应的服务中心");
                }else{
                    $jibieKey=$service_info["service_jibie"];
                    /*$jibies=Conf::get('fwzx_jibie_detail');
                    $jibie= $jibies[strtoupper($jibieKey)];
					$jibies=null;
					if(count($jibie)>0){
						foreach($jibie as $k=>$v){
							$val=explode("-",$v);
							$jibies[$k]=array("service_desc"=>$v,"time"=>$val[0],"jine"=>$val[1]);
						}
					}
                    $this->assign('jibie',$jibies);
                    $this->assign('Key',$jibieKey);*/
                    $jibies   = Conf::get('fwzx_jibie');
                    $nianfeis = Conf::get('fwzx_nianfei');

                    $jibie= $jibies[strtoupper($jibieKey)];
                    $nianfei= $nianfeis[strtoupper($jibieKey)];

                    $model_user =& m('member');
                    $service = $model_user->get("user_id=".$serviceId);
                    //$this->pr($service);
                    $this->assign('region_name',$service["region_name"]);
                    $this->assign('jibie',$jibie);
                    $this->assign('nianfei',$nianfei);
                }

                $this->assign('serviceid',$_GET["serviceid"]);
                $this->display('service.register2.html');
            }


        }else
        {
            $user = $this->visitor->get();
            $serviceid=$_POST["serviceid"];

            $this->checkService($_POST["serviceid"],$user["user_id"]);

            $tmp=explode("#",$_POST["service_desc"]);
            $tmp2=explode("-",$tmp[1]);

            if(!$_POST["service_user"]   ||  $_POST["service_user"]=="" ||
               !$_POST["service_tel"]    ||   $_POST["service_tel"] =="" ||
			  // !$_POST["service_tel2"]    ||   $_POST["service_tel2"] =="" ||
               !$_POST["serviceid"]       ||  $_POST["serviceid"]   =="" ||
              /* !$_POST["service_desc"]   ||  $_POST["service_desc"]=="" ||*/
               !$_POST["service_zhengjian"] || $_POST["service_zhengjian"] =="" ||
			   //!$_POST["service_email"] || $_POST["service_email"] =="" ||
               !$user["user_id"]
            ){
                $this->show_warning('参数错误');
                exit;
            }

            $data=array(
                "service_user"=>$_POST["service_user"],
                "service_tel"=>$_POST["service_tel"],
				"service_tel2"=>$_POST["service_tel2"],
                "type"=>0,
                "paymentState"=>0,
                "add_time"=>gmtime(),
                "service_id"=>$_POST["serviceid"],
                "member_id"=>$user["user_id"],
                //"service_desc"=>$_POST["service_desc"],
                "jine"=>1012,                                      // 1012  是 1千元 订金+12元网银转账费
                "service_zhengjian"=>$_POST["service_zhengjian"],
				"service_email"=>$_POST["service_email"],
            );

            $shenqing_model=& m("serviceShenqing");
            $serviceInfo_model =& m('serviceInfo');
            $service_info=$serviceInfo_model->get("service_id=".$_POST["serviceid"]." and type=1");
            if($service_info["id"]<=0 || $service_info["service_jibie"]==""){
                exit("没有找到对应的服务中心");
            }else{
                $jibieKey = $service_info["service_jibie"];

                $jibies   = Conf::get('fwzx_jibie');
                $nianfeis = Conf::get('fwzx_nianfei');

                $jibie= $jibies[strtoupper($jibieKey)];
                $nianfei= $nianfeis[strtoupper($jibieKey)];

                $data["service_desc"] = $jibie."#".$nianfei;
            }

            //写入申请
            $id=$shenqing_model->add($data);
            //保存图片
            $data=$this->uploadImg($serviceid);

            if(count($data)>0 && $serviceid!="")
            {
                $shenfenzhengImg=$data["shenfenzhengImg"];
                $yingyezhizhaoImg=$data["yingyezhizhaoImg"];
                $sql="update ".DB_PREFIX."service_shenqing set shenfenzhengImg='$shenfenzhengImg'";
                if($yingyezhizhaoImg!=""){
                    $sql.=",yingyezhizhaoImg='$yingyezhizhaoImg'";
                }
                $sql.=" where id=$id";
                //修改抢注申请照片资料
                $shenqing_model->db_query($sql);
            }
            /*//修改服务中心状态为锁定
            $this->_member_mod->edit($_POST["serviceid"],array("status"=>1));
            $member_model = & m('member');*/
            $member = $this->_member_mod -> get($_POST["serviceid"]);
            $code = $_POST["service_user"] . '已抢注服务中心。地区为:' . $member['region_name'] . '。联系电话：' .$_POST["service_tel"]
            . '。固定电话:' .$_POST["service_tel2"];
            $smsclient = new SMSClient();
            $rs=$smsclient->sendSMS(MSM_PHONE_NUMBER,$code);
			/*echo "抢注成功";
			exit;*/
            $shenqing_model=& m("serviceShenqing");
            $shenqing=$shenqing_model->get_info($id);
            $_POST="";
            if($shenqing["id"]>0){
                $desc=$shenqing["service_desc"];
                $tmp1=explode("#",$desc);
                /*$tmp2=explode("-",$tmp1[1]);
                $shenqing["year"]=$tmp2[0];
                $shenqing["jine2"]=$tmp2[1];*/
                $this->assign('jibie',$tmp1[0]);
                $this->assign('nianfei',$tmp1[1]);

                $this->assign('shenqing',$shenqing);
                $this->assign('success',1);
                $this->assign('TO_QIANGZHU_URL', TO_QIANGZHU_URL);
                $this->display('service.register_success.html');
            }else{
                $this->assign('error',1);
                $this->display('service.register_success.html');
            }
            /*
            $this->show_message('qiangzhuServiceInfoOk',
                'back_list', "index.php?app=member&act=qiangzhu&p=userInfo");*/
        }


    }


    /*function test(){
        $shenqing_model=& m("serviceShenqing");
        $shenqing=$shenqing_model->get_info(133);
        if($shenqing["id"]>0){
            $desc=$shenqing["service_desc"];
            $tmp1=explode("#",$desc);
            $tmp2=explode("-",$tmp1[1]);
            $shenqing["year"]=$tmp2[0];
            $shenqing["jine2"]=$tmp2[1];
            $this->assign('shenqing',$shenqing);
            //$this->assign('success',1);
            $this->display('service.register_success.html');
        }

    }*/

    //查询服务中心状态
    function serachService()
    {
        header('Content-type: text/json');
        $serachName=$_POST["serachName"];
        $tmp=explode("-",$serachName);
        $count=count($tmp);
        $queryName=$tmp[$count-1];

        $service_info=$this->queryService($queryName);
        $returnData=null;
        if(count($service_info)>0)
        {
          foreach($service_info as $k=>$v)
          {
              $returnData= array("state"=>1,"userId"=>$service_info[$k]["user_id"],"jibie"=>$service_info[$k]["jibie"]);
          }
        }else
        {
            $returnData= array("state"=>0);
        }
        echo json_encode($returnData);

    }

    function zxkf(){
        $this->_curitem("zxkf");

        $user = $this->visitor->get();
        $serviceInfo_model =& m('serviceInfo');

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
                    array(
                        'path' => 'mlselection.js',
                        'attr' => '',
                    ),
                ),
                'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css,res:jqtreetable.css',
            ));

            $serviceInfo=$serviceInfo_model->get(array(
                                    "conditions"=>"service_id=".$user["user_id"]." and type=1",
            ));

            if($serviceInfo["QQKF"]!=""){
                $this->assign('QQKF',explode("#",$serviceInfo["QQKF"]));
            }

//            $this->_config_seo('title', "在线客服" . ' - ' . Lang::get('member_center'));

            $this->display('service.kfqq.html');
        }else{
           $zxfks=$_POST["zxkfs"];
           if(zxfks==""){
                exit;
           }
            $serviceInfo=$serviceInfo_model->get("service_id=".$user["user_id"]);
            if(!$serviceInfo["id"] || $serviceInfo["id"]<=0){
                $service_info_data=array(
                    "service_id"=>$user["user_id"],
                    "type"=>1,
                    "update_time"=>gmtime(),
                );
                $serviceInfo_model->add($service_info_data);
            }

           $id=$serviceInfo_model->db_query("update ".DB_PREFIX."service_info set QQKF='$zxfks' where service_id=".$user["user_id"]." and type=1");
           if($id>0){
               echo "修改成功!";
           }else{
               echo "修改失败!";
           }


        }

    }

    function spzx(){
       $qaApp=new  My_qaApp();
       $qaApp->index();
    }
    function my_qa_reply(){
        $qaApp=new  My_qaApp();
        $qaApp->reply();
    }
    function my_qa_edit_reply(){
        $qaApp=new  My_qaApp();
        $qaApp->edit_reply();
    }
    function my_qa_drop(){
        $qaApp=new  My_qaApp();
        $qaApp->drop();
    }
    function clts(){
       $toushuApp=new  Goods_toushuApp();
       $toushuApp->index();
    }

    function my_ts_reply(){
        $toushuApp=new  Goods_toushuApp();
        $toushuApp->reply();
    }
    function my_ts_edit_reply(){
        $toushuApp=new  Goods_toushuApp();
        $toushuApp->edit_reply();
    }
    function my_ts_drop(){
        $toushuApp=new  Goods_toushuApp();
        $toushuApp->drop();
    }

    function chulishouhou(){
        $Ordershouhou = new OrdershouhouApp();
        $Ordershouhou->chulishouhou();
    }

    function yinhangka(){

        $bank_mod =& m('memberbank');
        $userInfo=$this->visitor->info;

        if($userInfo["member_type"]!="02")
        {
            $this->show_warning('set_bank_error');
            exit;
        }

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

            if($default_bank!="" && $userInfo["user_id"]>0){
                $bank_mod->db_query("update ".DB_PREFIX."member set kahao='$default_bank' where user_id=".$userInfo["user_id"]);
            }


            if($bank_code == "" || $userInfo["user_id"] == "" || $bank_kahao == "" ){
                echo "请选择银行并且输入卡号！";      //不能为空
                exit;
            }

            $data= $bank_mod->find("user_id='".$userInfo["user_id"]."' and bank_code='$bank_code'");
            if(count($data)<=0){
                echo "操作错误，不存在该银行的卡号！";      //不存在
                exit;
            }
            $bank_mod->db_query("update ".DB_PREFIX."member_bank set bank_kahao='$bank_kahao' where user_id='".$userInfo["user_id"]."' and bank_code='$bank_code'");


            $serviceInfo_model =& m('serviceInfo');
            $serviceInfo_model->db_query("update ".DB_PREFIX."service_info set kahao='$bank_kahao' where service_id=".$userInfo["user_id"]." and type=1");
            echo "修改成功！";
        }

    }

    function add_yinhangka(){
        $channel_mod =& m('channel');
        $bank_mod =& m('memberbank');
        $userInfo=$this->visitor->info;


        if($userInfo["member_type"]!="02")
        {
            $this->show_warning('set_bank_error');
            exit;
        }

        if(!$_POST){
            $page = $this->_get_page();
            $channels=$channel_mod->find(array(
                "conditions"=>"(type=2 or type=3) and status=1 ",
                'fields'=> "this.*,
                                       (select count(1) from ".DB_PREFIX."member_bank b where b.bank_code=channel.channel_code and b.user_id='".$userInfo["user_id"]."') as isbank",
                'limit' => $page['limit'],
                'count' => true
            ));
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
            if($info["bank_code"]==$bank_code){
                echo "该银行已经存在对应的卡号，如需更换卡号请到我的银行卡中更换！";      //已经存在
                exit;
            }

            $id=$bank_mod->add(array(
                "user_id"=>$userInfo["user_id"],
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



    //余额log
    function showYueLog(){

        $user = $this->visitor->get();
        $yuelog_mod =& m('yuelog');

        $this->_curitem("index");

        $conditions="";
        if($_GET["add_time_from"]){
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')>='$add_time_from'";
        }

        $type=$_GET["type"];
        if($type!="" && $type>=0){
            $conditions.=" and type=$type";
        }

        if($_GET["add_time_to"]){
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $page = $this->_get_page();
        $yuelog=$yuelog_mod->find(array(
            'conditions' => " user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*,FORMAT(this.jine,3) as jine',
            'count' => true,
            'order' => 'create_time DESC',
            'limit' => $page['limit'],
        ));

        if ($yuelog) {
            foreach ($yuelog as $key=>$y) {
                if(strpos($y['beizhu'],'_')) {
                    $yuelog[$key]['beizhu'] = sub_str($y['beizhu'],strpos($y['beizhu'],'_',strlen($y['beizhu'])));
                }
            }
        }
        $page['item_count'] = $yuelog_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('yuelog', $yuelog);
        $this->display('service.showLog.html');
    }

    //充值log
    function showChongzhiLog(){

        $user = $this->visitor->get();
        $paylog_mod =& m('paylog');

        $this->_curitem("index");

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
        $this->display('service.showLog.html');
    }

    //提现log
    function showTiXianLog(){

        $user = $this->visitor->get();
        $tixian_mod =& m('tixian');

        $this->_curitem("index");

        $conditions="";
        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and DATE_FORMAT(tixian.add_time,'%Y-%m-%d')>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
            $conditions.=" and DATE_FORMAT(tixian .add_time,'%Y-%m-%d')<='$add_time_to'";
        }


        $page = $this->_get_page();
        $tixian=$tixian_mod->find(array(
            'conditions' => "user_id =".$user['user_id'].$conditions,
            'fields'=> 'this.*',
            'count' => true,
            'order' => 'add_time DESC',
            'limit' => $page['limit'],
        ));

        $page['item_count'] = $tixian_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('tixianLog', $tixian);
        $this->display('service.showLog.html');
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

        $user_id=$this->visitor->get("user_id");
        $userInfo=$this->_member_mod->get($user_id);

        $my_banks=$bank_mod->find(array(
            "conditions"=>"user_id=".$user["user_id"]." and dropState=1 limit 0,5"
        ));
        if(count($my_banks)>0){
            foreach($my_banks as $k => $v){
                $my_banks[$k]["show_kahao"]=$this->half_replace($v["bank_kahao"]);
            }
        }
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
        $user_api = json_decode($this->user_api("","",""));
//        $userInfo["yue"]=$user_api['yue'];

        if($_GET['p'] == 'koushui') {
            $userInfo["koushui_yue"]=$user_api->koushui_yue;
        } else{
            $userInfo["unkoushui_yue"]=$user_api->unkoushui_yue;
        }

        $this->assign('channels', $channels);

        if(FAIL_TIME=="FAIL_TIME" || FAIL_TIME==""){
            $this->assign('FAIL_TIME',61);
        } else {
            $this->assign('FAIL_TIME',FAIL_TIME);
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


            if($user["member_type"]!="02"){
                $this->pop_warning(Lang::get('set_tixian_error'));
                exit;
            }

            if( empty($_POST['channel_name']) || $_POST['channel_name']=="" ||
                empty($_POST['channel_card'])       || $_POST['channel_card']=="" ||
                empty($_POST['user_name'])   || $_POST['user_name']=="" ||
                empty($_POST['jine']) || $_POST['jine']=="" ||
                empty($_POST['password2']) || $_POST['password2']==""
//                || empty($_POST['yanzhengma']) || $_POST['yanzhengma']==""
            )
            {
                $this->pop_warning(Lang::get('tixian_data_null'));
                exit;
            }

//            //验证码
//            if ($_SESSION['yanzhengma'] != $_POST['yanzhengma'])
//            {
//                $this->pop_warning(Lang::get('captcha_error'));
//                exit;
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
            $tixian_jine=$_POST["jine"];
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

//            date_default_timezone_set('Asia/Shanghai');
//            $sava_data=array(
//                "user_id"=>$user["user_id"],
//                "user_name"=>$_POST["user_name"],
//                "channel_id"=>$data2["channel_id"],
//                "kaihuhang"=>$_POST["kaihuhang"],
//                "channel_name"=>$data2["channel_name"],
//                "channel_card"=>$_POST["channel_card"],
//                "jine"=>$tixian_jine,
//                "status"=>0,
//                "add_time"=>date('Y-m-d H:i:s'),
//                "add_date"=>date('Y-m-d')
//            );

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
//            $id=$tixian_mod->add($sava_data);
//            if($id>0){
//
//                $this->_member_mod->db_query("update ".DB_PREFIX."member set yue=yue-$tixian_jine where user_id=$user_id");  //修改余额
//
//                $user=$this->_member_mod->get_info($user_id);
//                $data=array("user_id"=>$user_id,
//                    "jine"=>(0-$tixian_jine),
//                    "beizhu"=>"申请提现：历史余额为".($user["yue"]+$tixian_jine).",最新余额为".$user["yue"].",本次余额变动金额为".$tixian_jine,
//                    "create_time"=>date('Y-m-d H:i:s',time()),
//                    "type"=>3
//                );
//                $model_yuelog =& m('yuelog');
//                $model_yuelog->add($data);              //记录余额log
//
//            }
//
//            $mybans=$bank_mod->find("user_id=$user_id and user_name='".$_POST["user_name"]."' and bank_name='".$data2["channel_name"]."' and bank_code='".$data2["channel_code"]."'
//                        and bank_kahao='".$_POST["channel_card"]."'");
//
//            if(count($mybans)==0){
//                $bank_mod->add(array(
//                    "user_id"=>$user_id,
//                    "user_name"=>$_POST["user_name"],
//                    "bank_name"=>$data2["channel_name"],
//                    "bank_code"=>$data2["channel_code"],
//                    "bank_kahao"=>$_POST["channel_card"],
//                    "add_time"=>gmtime()
//                ));
//            }
//
//            header("Content-Type:text/html;charset=utf-8");
//            echo "<script>alert('提现申请成功！');</script>";
//            $this->pop_warning('ok','tixian_page');;
        }

    }


    function get_address() {

        $user_name= $_POST["user_name"];


        if($user_name==""){
            echo "<font color='red'>请输入收货人!</font>";
            exit;
        }

        $user=$this->_member_mod->get("user_name='$user_name'");
        if($user["user_id"]<1){
            echo "<font color='red'>用户名错误</font>";
            exit;
        }

        $user_id=$user["user_id"];
        $order_type     =&  ot('normal');
        $this->assign('my_address', $order_type->get_address2($user_id));
        $this->display("order.ajax_address2.html");


        /*if ($_GET){
            $order_type     =&  ot('normal');
            $this->assign('my_address', $order_type->get_address2($user_id));
            $this->display("order.ajax_address.html");
        }else{
            $data = array(
                'user_id'       => $this->visitor->get('user_id'),
                'consignee'     => trim($_GET['consignee']),
                'region_id'     => $_GET['region_id'],
                'region_name'   => $_GET['region_name'],
                'address'       => trim($_GET['address']),
                'zipcode'       => trim($_GET['zipcode']),
                'phone_tel'     => trim($_GET['phone_tel']),
                'phone_mob'     => trim($_GET['phone_mob']),
            );
            $model_address =& m('address');
            $model_address->add($data);
            $order_type     =&  ot('normal');
            $this->assign('my_address', $order_type->get_address2($user_id));
            $this->display("order.ajax_address.html");
        }*/
    }


    function password2(){
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
            /* 当前所处子菜单 */
            $this->_curmenu('password2');
            $info["phone_mob2"]=$this->half_replace($info["phone_mob"]);
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
            ));

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
            $init_code = $_SESSION["init_code"];
            if($init_code==""){
                $this->show_warning('验证码不存在！');
                return;
            }
            if($init_code!=$verifyCode){
                $this->show_warning('验证码错误！');
                return;
            }

            if ($new_password != $confirm_password){
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

    //安全中心
    function aqzx(){
        if(!$_POST){
            $user_id=$this->visitor->get('user_id');
            $user=$this->_member_mod->get_info($user_id);

            if(!empty($user["phone_mob"]) && $user["phone_mob"]>0){
                $user["show_tel"]=$this->half_replace($user["phone_mob"]);
            }
            if(!empty($user["email"]) && $user["email"]!=""){
                $user["show_email"]=$this->half_replace($user["email"]);
            }

            $this->assign('user',$user);

            $this->_config_seo('title', "安全中心" . ' - ' . Lang::get('member_center'));

            $this->display('service.aqzx.html');
        }
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

        if(!$_POST){
            $this->assign('user',$user);
            $this->display('service.manager_step1.html');
        }else{

            $type=$_POST["type"];
            $step=$_POST["step"];
            $this->assign('post_data',$_POST);
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
                    $email=$_POST["email"];
                    $new_email=$_POST["new_email"];
                    $email_active_code="";
                    $email_active_code = base64_encode(gmtime() . $email);

                    $update_email=$email;
                    //发送绑定邮件
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

                    $content = file_get_contents(ROOT_PATH . "/themes/mall/default/regist_email_content.html");
                    $content = str_replace("%s1", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
                    $content = str_replace("%s2", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
                    $sender = new SendCodeApp();
                    $sender -> send_email($email, "欢迎绑定邮箱到大集客", $content);

                    $id=$this->_member_mod->edit($user_id,array("email"=>$update_email,"email_bind_status"=>0,"email_active_code"=>$email_active_code));

                    $arr = explode("@", $email);
                    $this->assign("email_domain", $arr[1]);
                    $this->display("member.changer_sucess.html");

                }elseif($type=="changerphonemob"){
                    $new_phone_mob=$_POST["new_phone_mob"];
                    $phone_mob=$_POST["phone_mob"];

                    $mob_type = "phone_mob";

                    if($new_phone_mob!=""){
                        $phone_mob=$new_phone_mob;
                        $mob_type = "new_phone_mob";
                    }


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

    function saveImg(){
        import('image.func');
        $model_user =& m('member');
        $user_id = $this->visitor->get('user_id');
        if (!empty($_FILES['portrait']) && $_FILES['portrait']["size"]>0 && $user_id>0)
        {
            $portrait = $this->_upload_portrait($user_id);
            if ($portrait === false)
            {
                return;
            }
            if(file_exists(DIS_PATH . '/' . $portrait)){
                /*生成压缩图*/
                $urls = explode('.',$portrait);
                $path = $urls[0] . '_' . '130X130' . '.' . $urls[1];
                if(!file_exists(DIS_PATH .'/' . $path)){
                    make_thumb(ROOT_PATH . '/' . $portrait, ROOT_PATH .'/' . $path, 130, 130, 90);
                }
            } else {
                $urls = explode('.',$portrait);
                $path = $urls[0] . '_' . '130X130' . '.' . $urls[1];

                make_thumb(ROOT_PATH.'/'.$portrait, ROOT_PATH .'/' . $path, 130, 130, 90);
            }
            $data['portrait'] = $portrait;
            $model_user->edit($user_id , $data);

            $this->show_message('edit_imgOk');
        }
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

    function qianyue(){
        $memberqianyue_mod =& m('memberqianyue');
        $user = $this->visitor->info;
        $page = $this->_get_page();

        $conditions = "";

        if($_GET["add_time_from"])
        {
            $add_time_from=$_GET["add_time_from"];
            $conditions.=" and member_qianyue.add_time>='$add_time_from'";
        }

        if($_GET["add_time_to"])
        {
            $add_time_to=$_GET["add_time_to"];
             $conditions.=" and date_format(member_qianyue.add_time,'%Y-%m-%d')<='$add_time_to'";
        }
        if($_GET["user_name"])
        {
            $conditions.=" and member_qianyue.user_name like '%".$_GET["user_name"]."%'";
        }

        if($_GET["status"] != "")
        {
            $conditions.=" and member_qianyue.status =".$_GET["status"];
        }

        $qianyueData = $memberqianyue_mod->find(array(
                'conditions' => "region_id=".$user["region_id"].$conditions,
                'limit' => $page['limit'],
                'count' => true,
                'order' => "add_time desc"
         ));
        $page['item_count'] = $memberqianyue_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('data', $qianyueData);

        $this->_config_seo('title', "签约业务员申请" . ' - ' . Lang::get('member_center'));

        $this->display('service.qianyue.html');
    }

    //新增签约业务员申请
    function add_qianyue(){
        if(!$_POST){
            $user = $this->visitor->get();
            //$this->pr($user);exit;
            $this->assign('user', $user);
            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));
            $this->display('tuiguang_shenqing2.html');
        }else{
            //header('Content-type: text/json');

            if(!$_POST){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入签约申请信息","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            $member_user_name = $_POST["member_user_name"];
            if (!$member_user_name){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入用户名","error_lbl"=>"region_id")).");</script>";
                exit;
            }
            $model_member =& m('member');
            $info = $model_member->get("user_name='$member_user_name'");
            if($info == null || $info == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"用户名不存在","error_lbl"=>"region_id")).");</script>";
                exit;
            }
            if($info["spreader_type"] == 1 || $info["spreader_type"] == "1"){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"该用户已经是签约业务员","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            $account_type = $_POST["account_type"];
            $region_id = $_POST["region_id"];
            $region_name = $_POST["region_name"];
            $user_name = $_POST["user_name"];
            $shenfenzheng = $_POST["shenfenzheng"];
            $phone_mob = $_POST["phone_mob"];
            $email = $_POST["email"];

            $company_name = $_POST["company_name"];
            $company_address = $_POST["company_address"];

            if($account_type == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择帐号类型","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            if($account_type == "1" && $company_name == ""){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"请输入公司名称","error_lbl"=>"company_name"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入公司名称","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            if($account_type == "1" && $company_address == ""){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"请输入公司地址","error_lbl"=>"company_address"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入公司地址","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            if($region_id == "" || $region_id < 1){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id")).");</script>";
                exit;
            }
            $region_mod =& m('region');
            if(count($region_mod->get_options($region_id)) > 0 ){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"地区必须选择到最后1级","error_lbl"=>"region_id"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"地区必须选择到最后1级","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            if($user_name == ""){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"请输入真实姓名","error_lbl"=>"user_name"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入真实姓名","error_lbl"=>"user_name")).");</script>";
                exit;
            }

            if($shenfenzheng == ""){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"请输入身份证号码","error_lbl"=>"shenfenzheng"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入身份证号码","error_lbl"=>"shenfenzheng")).");</script>";
                exit;
            }

            if($phone_mob == ""){
                /*echo json_encode(array("flag"=>"error","error_msg"=>"请输入身份证号码","error_lbl"=>"phone_mob"));
                exit;*/
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"联系人手机号码","error_lbl"=>"phone_mob")).");</script>";
                exit;
            }

            if($email == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入电子邮箱","error_lbl"=>"email")).");</script>";
                exit;
            }


            $memberqianyue_mod =& m('memberqianyue');

            $user = $this->visitor->info;
            $data["account_type"] = $account_type;
            $data["region_id"] = $region_id;
            $data["region_name"] = $region_name;
            $data["user_name"] = $user_name;
            $data["shenfenzheng"] = $shenfenzheng;
            $data["phone_mob"] = $phone_mob;
            $data["email"] = $email;
            $data["status"] = 0;
            date_default_timezone_set('Asia/Shanghai');
            $data["add_time"] = date('Y-m-d H:i:s');

            if($_FILES['hetong']['name']!=null){
                $file = $_FILES['hetong'];
                if ($file['error'] != UPLOAD_ERR_OK){
                    echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择合同","error_lbl"=>"hetong")).");</script>";
                    exit;
                }
                import('uploader.lib');
                $uploader = new Uploader();
                $uploader->allowed_type(IMAGE_FILE_TYPE);
                $uploader->addFile($file);
                $uploader->root_dir(ROOT_PATH);

                list($usec, $sec) = explode(" ", microtime());
                $name=((float)$usec + (float)$sec);
                $tmp=explode(".",$name);
                $time_s=$tmp[1];
                if($time_s == ""){
                    $time_s = "00";
                }elseif(strlen($time_s) == 1){
                    $time_s = $time_s."0";
                }
                date_default_timezone_set('Asia/Shanghai');
                $filename = date('ymdHis').$time_s."_".$user["user_id"];
                $hetong =  $uploader->save(Conf::get('up_dir') , $filename);

                if($hetong == null || $hetong ==""){
                    echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"上传合同失败，请稍后重试","error_lbl"=>"region_id")).");</script>";
                    exit;
                }
                $data["hetong"] = $hetong;

            }

            if($account_type == 1){
                $data["company_name"] = $company_name;
                $data["company_address"] = $company_address;
            }
            $rs = 0;
            $tmp = $memberqianyue_mod->get("user_id=".$info["user_id"]);
            if( $tmp != "" && $tmp["user_id"] >0){
                $rs = $memberqianyue_mod->edit($info["user_id"],$data);
            }else{
                $data["user_id"] = $info["user_id"];
                $rs = $memberqianyue_mod->add($data);
            }
            $memberqianyue_mod->commit_transaction();
            /*if($rs>0){
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"ok")).");</script>";
                exit;
            }else{
                echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"网络繁忙，请稍后重试","error_lbl"=>"net_error")).");</script>";
                exit;
            }*/
            echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"ok")).");</script>";
            exit;

        }

    }

    function qianyue_view(){
        $memberqianyue_mod =& m('memberqianyue');
        $user = $this->visitor->info;
        $user_id = $_GET["id"];

        if($user_id == ""){
            $this->show_warning("没有找到签约推广客信息");
            exit;
        }
       $qianyue = $memberqianyue_mod->get("user_id=$user_id and region_id=".$user["region_id"]);
       if( !$qianyue)  {
           $this->show_warning("没有找到签约业务员信息");
           exit;
       }

        $this->assign('data', $qianyue);

        $this->_config_seo('title', "签约业务员申请" . ' - ' . Lang::get('member_center'));

        $this->display('service.qianyue.view.html');
    }

    function qianyue_upload(){

        //header('Content-type: text/json');

        $memberqianyue_mod =& m('memberqianyue');
        $user = $this->visitor->info;
        $user_id = $_POST["uid"];

        if($user_id == ""){
            //echo json_encode(array("flag"=>"error","error_msg"=>"没有找到签约推广客信息"));
            echo "<script type=\"text/javascript\">window.parent.callback_saveimg(".json_encode(array("flag"=>"error","error_msg"=>"没有找到签约推广客信息")).");</script>";
            exit;
        }
        $qianyue = $memberqianyue_mod->get("user_id=$user_id and region_id=".$user["region_id"]);
        if( !$qianyue)  {
            //echo json_encode(array("flag"=>"error","error_msg"=>"没有找到签约推广客信息"));
            echo "<script type=\"text/javascript\">window.parent.callback_saveimg(".json_encode(array("flag"=>"error","error_msg"=>"没有找到签约推广客信息")).");</script>";
            exit;
        }

        $file = $_FILES['hetong'];
        if ($file['error'] != UPLOAD_ERR_OK){
            //echo json_encode(array("flag"=>"error","error_msg"=>"请选择合同"));
            echo "<script type=\"text/javascript\">window.parent.callback_saveimg(".json_encode(array("flag"=>"error","error_msg"=>"请选择合同")).");</script>";
            exit;
        }
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);

        list($usec, $sec) = explode(" ", microtime());
        $name=((float)$usec + (float)$sec);
        $tmp=explode(".",$name);
        $time_s=$tmp[1];
        $file_name = date('ymdHis').$time_s;

        $hetong =  $uploader->save(Conf::get('up_dir'), $file_name);

        if($hetong == "" ){
            //echo json_encode(array("flag"=>"error","error_msg"=>"上传失败"));
            echo "<script type=\"text/javascript\">window.parent.callback_saveimg(".json_encode(array("flag"=>"error","error_msg"=>"上传失败")).");</script>";
            exit;
        }

        $rs = $memberqianyue_mod ->edit($user_id,array("hetong" => $hetong));
        $memberqianyue_mod->commit_transaction();
        if($rs<1){
            //echo json_encode(array("flag"=>"error","error_msg"=>"上传失败"));
            echo "<script type=\"text/javascript\">window.parent.callback_saveimg(".json_encode(array("flag"=>"error","error_msg"=>"上传失败")).");</script>";
            exit;
        }
        echo "<script type=\"text/javascript\">window.parent.callback_saveimg(".json_encode(array("flag"=>"ok")).");</script>";
        //echo json_encode(array("flag"=>"ok"));
        exit;

    }

    function get_service_api(){
        $dataInfo=$this->visitor->info;
        echo $this->user_api($dataInfo["user_id"],$dataInfo["region_id"],"query_server");
    }

    function check_qianyue_user(){
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name){
            echo ecm_json_encode(false);
            exit;
        }
        $flg =true;
        $model_member =& m('member');
        $info = $model_member->get("user_name='$user_name'");
        if($info == null || $info == ""){
            echo ecm_json_encode(false);
            exit;
        }
        if($info["spreader_type"] == 1 || $info["spreader_type"] == "1"){
            echo ecm_json_encode(false);
            exit;
        }
        echo ecm_json_encode(true);
        exit;

    }

    function bind_user(){
        if($this->visitor->get("member_type")!="02") {
            $this->show_warning("只有服务中心主帐号才能执行此操作！");
            exit;
        }

        if(!$_POST){
            $this->assign("data",$this->_member_mod->getOne("SELECT m1.user_name AS 'user_name' FROM ecm_member m1,ecm_member m2 WHERE m1.`user_id` =m2.`bind_user_id` AND m2.`user_id`=".$this->visitor->get("user_id")));
            $this->_config_seo('title', "绑定推广账号" . ' - ' . Lang::get('member_center'));
            $this->display('service.bind_user.html');
        }else{
            $user_name = $_POST["user_name"];
            if(empty($user_name) || $user_name ==""){
                $this->_member_mod->edit($this->visitor->get("user_id"),array("bind_user_id"=>0));
                $this->show_message("操作成功！");
                exit;
            }
            $t_user = $this->_member_mod->get("user_name ='$user_name' and dropState=1 and member_type='01'");
            if(empty($t_user)){
                $this->display('service.bind_user.html');
                echo "<script>js_fail('用户名不存在【用户已被删除或者用户类型不是普通帐号！】');</script>";
                exit;
            }

            $this->_member_mod->edit($this->visitor->get("user_id"),array("bind_user_id"=>$t_user["user_id"]));
            //header("Location: index.php?app=service&act=bind_user");
            $this->show_message("操作成功！");
        }

    }

    function vshop_all(){
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $userInfo = $user_mod->get_info($user['user_id']);
        if( $userInfo["member_type"]!="02" && $userInfo["member_type"]!="03"){
            $this->index();
        }

        $orderBy = " ".$_GET['orderBy'];
        $ascDesc = " ".$_GET['ascDesc'];
        $shop_id=$_GET['shop_id'];
        $this->assign("shop_id",$shop_id);

        $sql ="SELECT v.*,
                        (SELECT COUNT(1) FROM ecm_order o WHERE o.shop_id=v.user_id AND o.shop_id> 0 and (type='vshop' or type='mobile,vshop')) AS order_count,
                        (SELECT SUM(o.order_amount) FROM ecm_order o WHERE o.shop_id=v.user_id AND o.shop_id > 0 and (type='vshop' or type='mobile,vshop')) AS sales_count,
                        (SELECT shop_name FROM ecm_member WHERE v.user_id=user_id) AS shop_name
            FROM ecm_vshop v
            WHERE  v.status=1 and v.region_id=".$user['region_id'];
        $create_time_from = $_GET['create_time_from'];
        $this->assign("create_time_from",$create_time_from);
        if($create_time_from !=null && $create_time_from != " "){
            $sql .=" and  v.create_time >='".$create_time_from."'";
        }
        $create_time_to = $_GET['create_time_to'];
        $this->assign("create_time_to",$create_time_to);
        if($create_time_to  !=null && $create_time_to  != " "){
            $sql .=" and  v.create_time <='".$create_time_to."'";
        }

        if($shop_id && $shop_id > 0 ){
            $sql .= " AND v.user_id=".$shop_id;
        }

        if( $orderBy && $orderBy != " " && $orderBy !=null){
            $sql .= " order by ".$orderBy;
        }else{
            $sql .= " order by v.create_time ";
        }
        if( $ascDesc && $ascDesc != ""){
            $sql .= " ".$ascDesc;
        }else{
            $sql .= " desc";
        }

        $vshopList= $user_mod->db->getAll($sql);

        $this->assign("vshopList",$vshopList);
        $this->assign("vshopListCount",count($vshopList));
        $this->_curitem("vshop_all");
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
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display("service.vshop_all.html");


    }
    function vshop_order(){
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $userInfo = $user_mod->get_info($user['user_id']);
        if( $userInfo["member_type"]!="02" && $userInfo["member_type"]!="03"){
            $this->index();
        }
        $vshop_mod = &m("vshop");
        $order_mod= &m("order");
        $vshops = $vshop_mod->findAll("region_id=".$userInfo['region_id']." and status=1");
        $vshopList = array();
        $vshopIdList = array();
        if( $vshops && $vshops!=null && count($vshops) > 0  ){
            foreach($vshops as $vshop){
                $current_user = $user_mod->get("user_id=".$vshop['user_id']);
                if($current_user && $current_user['member_type']=="01" && $current_user['spreader_type']==1 && $current_user['shop_name']!= null && $current_user['shop_name'] != "" ){
                    array_push($vshopList,$current_user);
                    array_push($vshopIdList,$current_user['user_id']);
                }
            }
        }
        //如果有精品集客小店，就去找相关订单
        if($vshopList > 0 && count($vshopIdList) > 0 ){
            $conditions = " (type='vshop' or type='mobile,vshop') ";
            $type = $_GET['type'];
            $add_time_from=$_GET['add_time_from'];
            $add_time_to=$_GET['add_time_to'];
            $order_sn = $_GET['order_sn'];
            $ascDesc =$_GET['orderSalesAscDesc'];
            $buyer_name = $_GET['buyer_name'];
            $shop_id=$_GET['shop_id'];
            $this->assign("type",$type);
            $this->assign("add_time_from",$add_time_from);
            $this->assign("add_time_to",$add_time_to);
            $this->assign("order_sn",$order_sn);
            $this->assign("shop_id",$shop_id);
            $this->assign("buyer_name",$buyer_name);


            if( $type == "pending" ){//待付款
                $conditions .=" and status = ".ORDER_PENDING;
            }elseif( $type == "submitted" ){//已付款
                $conditions .=" and status = ".ORDER_SUBMITTED;
            }elseif( $type == "accepted" ){//待发货
                $conditions .=" and status = ".ORDER_ACCEPTED;
            }elseif( $type == "shipped" ){//已发货
                $conditions .=" and status = ".ORDER_SHIPPED;
            }elseif( $type == "finished" ){//已完成
                $conditions .=" and status = ".ORDER_FINISHED;
            }elseif( $type == "canceled" ){//已取消
                $conditions .=" and status = ".ORDER_CANCELED;
            }
            if( $add_time_from !=null && $add_time_from != "" ){
                $conditions.=" and add_time >= ".strtotime($add_time_from)."";
            }
            if( $add_time_to != null && $add_time_to != "" ){
                $conditions.=" and add_time <= ".strtotime($add_time_to)."";
            }
            if( $order_sn != null && $order_sn != "" ){
                $conditions.=" and order_sn='".$order_sn."'";
            }
            if( $shop_id != null && $shop_id != "" && $shop_id  > 0 ) {
                $conditions.=" and shop_id=".$shop_id;
            }else{
                $conditions.=" and shop_id ".db_create_in($vshopIdList);
            }
            if( $buyer_name != null && $buyer_name != "" ){
                $conditions.=" and buyer_name='".$buyer_name."'";
            }
            if( $ascDesc != null && $ascDesc != "" ){
                $conditions.=" ".$ascDesc;
            }
            $page = $this->_get_page();
            $data=array(
                'conditions'    => $conditions,
                'count'         => true,
                'fields'        => "this.*",
                'limit'         => $page['limit'],
                'order'         => 'add_time DESC',
                'include'       =>  array(
                    'has_ordergoods',       //取出商品
                ),
            );
            $orders = $order_mod->findAll($data);
//            var_dump($orders);exit;
            $orderextm_mod =& m('orderextm');

            foreach ($orders as $key1 => $order){
                $orderextm = $orderextm_mod->get($key1);
                if($orderextm["order_id"] != "" && $orderextm["order_id"] >0 ){
                    $orders[$key1]["consignee"] = $orderextm["consignee"];
                    $orders[$key1]["phone_mob"] = $orderextm["phone_mob"];
                    $orders[$key1]["phone_tel"] = $orderextm["phone_tel"];
                }
                if ($order['order_goods']) {
                    foreach ($order['order_goods'] as $key2 => $goods){
                        empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
                    }
                }
            }

            $page['item_count'] = $order_mod->getCount();
            $this->_format_page($page);
            $this->assign('orders', $orders);
            $page['orders']=$orders;
            $this->assign('page_info', $page);
            $this->assign('types', array('all' => Lang::get('all_orders'),
                'pending' => Lang::get('pending_orders'),
                'submitted' => Lang::get('submitted_orders'),
                'accepted' => Lang::get('accepted_orders'),
                'shipped' => Lang::get('shipped_orders'),
                'finished' => Lang::get('finished_orders'),
                'canceled' => Lang::get('canceled_orders')));
            $this->_curmenu($type);
        }
        $this->_curitem("vshop_order");
        $this->assign("vshopList",$vshopList);
        $this->assign("vshops",$vshops);
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
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display("service.vshop_order.html");
    }


    function vshop_yongjin(){
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $userInfo = $user_mod->get_info($user['user_id']);
        if( $userInfo["member_type"]!="02" && $userInfo["member_type"]!="03"){
            $this->index();
        }
        $vshop_mod = &m("vshop");
        $order_mod= &m("order");
        $vshops = $vshop_mod->findAll("region_id=".$userInfo['region_id']." and status=1");
        $vshopIdList = array();
        if( $vshops && $vshops!=null && count($vshops) > 0  ){
            foreach($vshops as $vshop){
                $current_user = $user_mod->get("user_id=".$vshop['user_id']);
                if($current_user && $current_user['member_type']=="01" && $current_user['spreader_type']==1 && $current_user['shop_name']!= null && $current_user['shop_name'] != "" ){
                    array_push($vshopIdList,$current_user['user_id']);
                }
            }
        }
        //如果有精品集客小店，就去找相关订单
        if( $vshopIdList && count( $vshopIdList ) > 0 ){
            $this->assign("vshopIdList",$vshopIdList);
            $conditions = " (type='vshop' or type='mobile,vshop') ";
            $add_time_from=$_GET['add_time_from'];
            $add_time_to=$_GET['add_time_to'];
            $order_sn = $_GET['order_sn'];
            $shop_id=$_GET['shop_id'];
            $this->assign("add_time_from",$add_time_from);
            $this->assign("add_time_to",$add_time_to);
            $this->assign("order_sn",$order_sn);
            $this->assign("shop_id",$shop_id);

            if( $add_time_from !=null && $add_time_from != "" ){
                $conditions.=" and add_time >= ".strtotime($add_time_from)."";
            }
            if( $add_time_to != null && $add_time_to != "" ){
                $conditions.=" and add_time <= ".strtotime($add_time_to)."";
            }
            if( $order_sn != null && $order_sn != "" ){
                $conditions.=" and order_sn='".$order_sn."'";
            }
            if( $shop_id != null && $shop_id != "" && $shop_id  > 0 ) {
                $conditions.=" and shop_id=".$shop_id;
            }else{
                $conditions.=" and shop_id ".db_create_in($vshopIdList);
            }

            $page = $this->_get_page();
            $data=array(
                'conditions'    => $conditions ." and status=".ORDER_FINISHED,
                'count'         => true,
                'fields'        => "this.*",
                'limit'         => $page['limit'],
                'order'         => 'add_time DESC',
                'include'       =>  array(
                    'has_ordergoods',       //取出商品
                ),
            );
            $orders = $order_mod->findAll($data);
            $orderextm_mod =& m('orderextm');

            $yue_log_mod = &m("yuelog");

            foreach ($orders as $key1 => $order){
                $orderextm = $orderextm_mod->get($key1);
                if($orderextm["order_id"] != "" && $orderextm["order_id"] >0 ){
                    $orders[$key1]["consignee"] = $orderextm["consignee"];
                    $orders[$key1]["phone_mob"] = $orderextm["phone_mob"];
                    $orders[$key1]["phone_tel"] = $orderextm["phone_tel"];
                    $orders[$key1]["region_name"] = $orderextm["region_name"];
                    $orders[$key1]["address"] = $orderextm["address"];
                    $orders[$key1]["shipping_id"] = $orderextm["shipping_id"];
                    $orders[$key1]["shipping_fee"] = $orderextm["shipping_fee"];
                    $yongjin = $yue_log_mod->getOne("select sum(jine) from ecm_yue_log where user_id=".$userInfo['user_id']." and order_sn = ".$orders[$key1]['order_sn']." and type=6 and beizhu like '%服务中心%'");
                    $orders[$key1]["yong_jin"] =  $yongjin ; //服务中心获取每笔订单的佣金
                }
                if ($order['order_goods']) {
                    foreach ($order['order_goods'] as $key2 => $goods){
                        empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
                    }
                }
            }

            $page['item_count'] = $order_mod->getCount();
            $this->_format_page($page);
            $page['orders']=$orders;

            $this->assign('page_info', $page);
        }
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
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->_curitem("vshop_yongjin");
        $this->display("service.vshop_yongjin.html");
    }

    function vshop_bind_page(){
        $this->display("service.vshop_bind.html");
    }
    function vshop_cancel_page(){
        $this->assign("shop_id",$_GET['shop_id']);
        $this->display("service.vshop_cancel.html");
    }
    function vshop_bind(){
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $vshop_mod = & m('vshop');
        $userInfo = $user_mod->get_info($user['user_id']);
        if( $userInfo["member_type"]!="02" && $userInfo["member_type"]!="03"){
            $this->json_result(0,"非法用户!");
            return;
        }
        $shop_id = $_POST['shop_id'];
        if(!$shop_id || $shop_id <= 0 ){
            $this->json_result(0,"集客小店号参数错误!");
            return;
        }
        $shopOwner = $user_mod->get("user_id=".$shop_id);
        if( !$shopOwner || $shopOwner['spreader_type'] != 1 || $shopOwner['shop_name'] == null || $shopOwner['shop_name'] == " "){
            $this->json_result(0,"该集客小店不存在!");
            return;
        }
        $vshop = $vshop_mod->get("user_id=".$shop_id);
        date_default_timezone_set("PRC");
        if( $vshop && $vshop['status'] == 1 ){
            $this->json_result(0,"该集客小店已经被服务中心绑定!");
            return;
        }elseif( $vshop && $vshop['status'] != 1 ){//审核通过
            $data = array(
                "region_id"=>$user['region_id'],
                "region_name"=>$user['region_name'],
                "create_time"=>date('Y-m-d H:i:s',time()),
                "status" => 1,
                "close_time"=>null,
                "close_reason"=>null,
            );
            $vshop_mod->edit($shop_id,$data);
            $vshop_temp = $vshop_mod->get("user_id=".$shop_id);
            if( $vshop_temp && $shop_id == $vshop_temp['user_id'] ){
                $this->json_result(1,"绑定成功!");
                return;
            }else{
                $this->json_result(0,"绑定失败!");
                return;
            }
        }else{//新增
            $data = array(
                "user_id"=>$shop_id,
                "region_id"=>$user['region_id'],
                "region_name"=>$user['region_name'],
                "create_time"=>date('Y-m-d H:i:s',time()),
                "status" => 1,
                "close_time"=>null,
                "close_reason"=>null,
            );
            $vshop_mod->add($data);
            $vshop_temp = $vshop_mod->get("user_id=".$shop_id);
            if( $vshop_temp && $shop_id == $vshop_temp['user_id'] ){
                $this->json_result(1,"绑定成功!");
                return;
            }else{
                $this->json_result(0,"绑定失败!");
                return;
            }
        }


    }
    function vshop_cancel(){
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $vshop_mod = & m('vshop');
        $userInfo = $user_mod->get_info($user['user_id']);
        if( $userInfo["member_type"]!="02" && $userInfo["member_type"]!="03"){
            $this->json_result(0,"非法用户!");
            return;
        }
        $shop_id = $_POST['shop_id'];
        if(!$shop_id || $shop_id <= 0 ){
            $this->json_result(0,"精品集客小店号参数错误!");
            return;
        }
        $shopOwner = $user_mod->get("user_id=".$shop_id);
        if( !$shopOwner || $shopOwner['spreader_type'] != 1 || $shopOwner['shop_name'] == null || $shopOwner['shop_name'] == " "){
            $this->json_result(0,"该精品集客小店不存在!");
            return;
        }
        $vshop = $vshop_mod->get("user_id=".$shop_id);
        if( !$vshop ){
            $this->json_result(0,"该精品集客小店的资格已经被取消!");
            return;
        }
        $close_reason  = $_POST['close_reason'];
        date_default_timezone_set("PRC");
        $result = $vshop_mod->edit($vshop['user_id'],array("status"=>2,"close_reason"=>$close_reason,"close_time"=>date('Y-m-d H:i:s',time())));
        if($result == 1){
            $this->json_result(1,"成功取消该精品店的资格!");
            return;
        }

    }



    function vshop_verify(){
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $userInfo = $user_mod->get_info($user['user_id']);
        if( $userInfo["member_type"]!="02" && $userInfo["member_type"]!="03"){
            $this->index();
        }
    }




    //商品推荐（服务中心推荐托管商家的商品）
    function vshop_goods(){
        $goodsApp=new My_goodsApp();
        $sgcates = $goodsApp->_get_sgcategory_options();
        $this->assign('scategories', $sgcates);


        //本服务中心下的所有托管商家
        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];
        $userInfo=$this->_member_mod->get($fwzxId);
        $conditions="";
        if(!empty($userInfo['region_id'])){
            $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
        }
        $users=$this->_member_mod->find(array(
            "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
            "fields"    =>"user_id,user_name"
        ));
        $query_conditions="";
        if(count($users)>0 && $fwzx_into["member_type"]=="02"){
            foreach($users as $k=>$v){
                $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
            }
            $this->assign('users', $users);//本服务中心下的所有子账户
        }
        $store_mod =& m('store');
        $stores = $store_mod->find(array(
            'conditions' => " (fwzx=$fwzxId $query_conditions) and s.store_id > 0 and tuoguan=1 and s.dropState=1 and state=1".$conditions,
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name'
        ));
        $this->assign('stores', $stores);//本服务中心下的所有托管商家



        //取出所有托管商家下的商品

        $sql="select g.goods_id,g.pd_id, g.store_id, g.type, g.goods_name, g.cate_id, g.cate_name ,g.if_show, g.closed, g.add_time,g.default_image,g.dropState,
                     g.price,g.org_price,gs.stock,
                     s.store_name,(1-g.org_price/g.price) AS yongjinbili
              from ecm_goods g left join ecm_goods_spec gs on g.default_spec=gs.spec_id
              left join ecm_store s on s.store_id=g.store_id";
        $sqlCount="select count(*)
              from ecm_goods g left join ecm_goods_spec gs on g.goods_id=gs.goods_id
              left join ecm_store s on s.store_id=g.store_id";
        $conditions =" where 1=1 ";
        if (trim($_GET['keyword'])){
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str})";
        }
        $conditions .=" and type='material' and g.dropState =1 and g.if_show=1 and g.closed=0 and s.dropState=1 and state=1 and s.store_id > 0 and tuoguan=1 and s.fwzx=".$userInfo['user_id'];
        $conditions .=" and ( (1-g.org_price/g.price) between 0.4 AND  0.85)";
        if($_GET['store_id'] && $_GET['store_id'] > 0 ){
            $conditions.=" and g.store_id=".$_GET['store_id'];
        }
        if($_GET['add_time_from'] !=null && $_GET['add_time_from'] != " "){
            $conditions.=" and g.add_time >=".strtotime($_GET['add_time_from']);
        }
        if($_GET['add_time_to'] != null && $_GET['add_time_to'] !=" "){
            $conditions.=" and g.add_time <=".strtotime($_GET['add_time_to']);
        }
        if($_GET['cate_id'] >  0 ){
            $conditions.=" and (g.cate_id =".$_GET['cate_id']." or g.cate_id_1=".$_GET['cate_id'].")";
        }
        $sql.=$conditions;
        $sqlCount.=$conditions;
        $page = $this->_get_page(20);
//        echo $sql;
        $goods_list = $this->_goods_mod->findByPage($sql, $page['curr_page'],$page['pageper']);
        $page['item_count'] = $this->_goods_mod->getOne($sqlCount);
        foreach ($goods_list as $key => $goods){
            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
            if($goods['price'] > 0 && $goods['org_price'] > 0 ) {
                $yongjinbili = round($goods['yongjinbili'],4)*100;
                $goods_list[$key]['yongjinbili']= $yongjinbili;
            }else{
                $goods_list[$key]['yongjinbili']=0;
            }

        }
        $page['goodsList']=$goods_list;
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->_curitem("vshop_goods");
        $this->display("service.vshop_goods.html");
    }



    function vshop_recomGoods(){
        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];
        $userInfo=$this->_member_mod->get($fwzxId);

        $rids = Array(1 => "精品集客小店--本地特卖");
        $vshopRecom_mod =& m('vshopRecommend');
        $position = $_GET["position"];
        if (!$position || $position == '') {
            $position = $_POST["position"];
        }
        if (!$position || $position == '') {
            $position = 1;
        }
        date_default_timezone_set("PRC");
        if (IS_POST) {
            if ($_POST["goods_ids"] && $_POST["goods_ids"] !=" ") {
                if (array_key_exists($position, $rids)) {
                    foreach (explode(",", $_POST["goods_ids"]) as $key=>$goods_id) {
                        if ( $goods_id != "" ){
                            $good_temp = $vshopRecom_mod->get("position=".$position." and goods_id=".$goods_id);
                            if( !$good_temp ){
                                $data=array("goods_id"=>$goods_id,
                                    "region_id"=>$userInfo['region_id'],
                                    "region_name"=>$userInfo['region_name'],
                                    "status"=>0,
                                    "create_time"=>date('Y-m-d H:i:s',time()),
                                    "position"=>1
                                );
                                $id = $vshopRecom_mod->add($data);
                            }
                        }else{
                            continue;
                        }
                    }
                }
            }
        } else {
            if ($_GET["_m"] == 'del') {
                if (array_key_exists($position, $rids)) {
                    $id = $_GET["id"];
                    $vshopRecom_mod->db->query("delete from ecm_vshop_recom_goods where id = " . $id);
                }
            }
        }
        $sql ="SELECT g.goods_id ,g.goods_name,g.org_price,g.price,g.default_image,vg.id,vg.status,vg.unpass_reason,vg.update_time,vg.region_id,vg.region_name,vg.create_time
               FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
               WHERE vg.region_id=".$userInfo['region_id']."  AND vg.POSITION=".$position;

        $list =  $vshopRecom_mod->goodsList($sql,1,30);
        $this->assign("position", $position);
        $this->assign("list", $list);
        $this->display("service.vshop_recomGoods.html");
    }


    //采购中心审核商品(商品来自于服务中心推荐到精品集客小店的托管商品)
    function vshop_goods_manage(){
        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];
        $userInfo=$this->_member_mod->get($fwzxId);
        if( !$userInfo ||  $userInfo["member_type"] != "04" ){
            exit("illegal accessing!");
        }
        $vshopRecommend_mod=&m("vshopRecommend");
        $vshop_service = $vshopRecommend_mod->getAll("SELECT region_id,region_name FROM ecm_vshop_recom_goods  GROUP BY region_id order by region_id ");
        $this->assign("services",$vshop_service);

        $goodsApp=new My_goodsApp();
        $sgcates = $goodsApp->_get_sgcategory_options();
        $this->assign('scategories', $sgcates);

        $page   =   $this->_get_page(20);
        $sql="SELECT g.goods_id ,g.goods_name,g.default_image,g.ticheng,g.cate_name,g.price,g.org_price,gs.stock,vg.id,vg.position,vg.update_time,vg.region_id,vg.region_name,vg.status
               FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
               left  join ecm_goods_spec gs on g.default_spec=gs.spec_id
               where g.if_show=1 and g.closed=0 and dropState=1 ";
        $sqlCount="SELECT COUNT(*)
                   FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
                where g.if_show=1 and g.closed=0 and dropState=1 ";

//        var_dump($_GET);
        if( $_GET['create_time_from'] && $_GET['create_time_from'] !=" " ){
            $sql .=" and vg.create_time >='".$_GET['create_time_from']."'";
            $sqlCount .=" and vg.create_time >='".$_GET['create_time_from']."'";
        }
        if( $_GET['create_time_to'] && $_GET['create_time_to'] !=" " ){
            $sql .=" and vg.create_time <='".$_GET['create_time_to']."'";
            $sqlCount .=" and vg.create_time <='".$_GET['create_time_to']."'";
        }
        if( $_GET['cate_id'] && $_GET['cate_id'] > 0 ){
            $sql .=" and g.cate_id=".$_GET['cate_id'];
            $sqlCount .=" and g.cate_id=".$_GET['cate_id'];
        }
        if($_GET['goods_name'] && $_GET['goods_name'] !=null && $_GET['goods_name'] !=' '){
            $sql.=" and g.goods_name like'%".$_GET['goods_name']."%'";
            $sqlCount.=" and g.goods_name like'%".$_GET['goods_name']."%'";
            $this->assign("goods_name",$_GET['goods_name']);
        }
        if($_GET['position'] &&  $_GET['position'] >= 1){
            $sql.=" and vg.position=".$_GET['position'];
            $sqlCount.=" and vg.position=".$_GET['position'];
            $this->assign("position",$_GET['position']);
        }
        if( $_GET['status'] &&  intval($_GET['status']) >= 0){
            $sql.=" and vg.status=".intval($_GET['status']);
            $sqlCount.=" and vg.status=".intval($_GET['status']);
            $this->assign("status",$_GET['status']);
        }elseif($_GET['status'] =="0_0"){
            $sql.=" and vg.status=0";
            $sqlCount.=" and vg.status=0";
            $this->assign("status","0");
        }
        if($_GET['region_id'] &&  $_GET['region_id'] >= 0){
            $sql.=" and vg.region_id=".$_GET['region_id'];
            $sqlCount.=" and vg.region_id=".$_GET['region_id'];
            $this->assign("region_id",$_GET['region_id']);
        }
        $sql .=" ORDER BY vg.update_time desc";
//        echo $sql;
        $goodsList= $vshopRecommend_mod->goodsList($sql,$page['curr_page'],$page['pageper']);
        $page['item_count'] = $vshopRecommend_mod->getOne($sqlCount);
        foreach ($goodsList as $key => $goods){
            $goodsList[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
            if($goods['price'] > 0 && $goods['org_price'] > 0 ) {
                $yongjinbili = round(1-$goods['org_price']/$goods['price'],4);
                $goodsList[$key]['yongjibili']= $yongjinbili*100;
            }else{
                $goodsList[$key]['yongjibili']=0;
            }

        }
        $page['goodsList']=$goodsList;
        $this->_format_page($page);
        $this->assign("page_info",$page);

        $this->_curitem("vshop_goods_manage");
        $this->display("service.vshop_goods_manage.html");
    }
    function vshop_goods_shenhe_page(){
        $this->assign("id",$_GET['id']);
        $this->assign("status",$_GET['status']);
        $this->display("service.vshop_goods_shenhe.html");
    }
    function vshop_goods_shenhe(){
        $fwzx_into = $this->visitor->get();
        $fwzxId=$fwzx_into['user_id'];
        $userInfo=$this->_member_mod->get($fwzxId);
        if( !$userInfo ||  $userInfo["member_type"] != "04" ){
            exit("illegal accessing!");
        }
        date_default_timezone_set("PRC");
        $vshopRecommend_mod=&m("vshopRecommend");
        $data=array(
            "update_time"=>date('Y-m-d H:i:s',time()),
            "operator_id"=>$userInfo["user_id"],
            "operator_name"=>$userInfo["user_name"],
        );
        $status=0;
        if($_GET['status']=="1"){
            $status=1;
            $data['unpass_reason']='';
        }else{
            $status=2;
            $data['unpass_reason']=$_GET['unpass_reason'];
        }
        $data['status']=$status;
        $result = $vshopRecommend_mod->edit($_GET['id'],$data);
        if( $result > 0 ){
            if($status=="1"){
                $this->json_result(1,"审核通过");
            }else{
                $this->json_result(1,"审核不通过");
            }
        }else{
            $this->json_result(1,"审核失败,请稍后再试!");
        }
    }

    function _get_member_submenu()
    {
        $order_app = "index.php?app=service&act=vshop_order";
        $menus = array(
            array(
                'name'  => 'all_orders',
                'url'   => $order_app.'&type=all_orders',
            ),
            array(
                'name' => 'pending',
                'url' => $order_app.'&amp;type=pending',
            ),
            array(
                'name' => 'submitted',
                'url' => $order_app.'&amp;type=submitted',
            ),
            array(
                'name' => 'accepted',
                'url' => $order_app.'&amp;type=accepted',
            ),
            array(
                'name' => 'shipped',
                'url' => $order_app.'&amp;type=shipped',
            ),
            array(
                'name' => 'finished',
                'url' => $order_app.'&amp;type=finished',
            ),
            array(
                'name' => 'canceled',
                'url' => $order_app.'&amp;type=canceled',
            ),

        );
        return $menus;
    }
    //实体店门贴
    function mentie(){
        $store_id = $_GET['store_id'];
        $store_mod = &m('store');
        $store  = $store_mod->get('store_id='.$store_id." and state=1 and dropState=1");

        $mentie = $store_mod->getOne("select * from ecm_store_mentie where store_id=".$store_id);
        $this->assign("mentie",$mentie);
        $this->assign("store",$store);
        $this->display("service.mentie.html");
    }
}
?>
