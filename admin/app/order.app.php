<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class OrderApp extends BackendApp
{
    /**
     *    管理
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        $search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $model_order =& m('order');
        $page   =   $this->_get_page(10);    //获取分页信息

        if( $_GET["shouhouFlg"] && $_GET["shouhouFlg"] ==1 ){
            $conditions = " and EXISTS (SELECT 1 FROM ecm_order_shouhou order_shouhou WHERE order_shouhou.order_id=order_alias.order_id)";
        }

        if( $_GET["shouhouFlg"] && $_GET["shouhouFlg"] ==2 ){
            $conditions = " and not EXISTS (SELECT 1 FROM ecm_order_shouhou order_shouhou WHERE order_shouhou.order_id=order_alias.order_id)";
        }

        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])){
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))){
             $sort  = 'add_time';
             $order = 'desc';
            }
        }
        else{
            $sort  = 'add_time';
            $order = 'desc';
        }
        $user_id = $this->visitor->get('user_id');
        if ($user_id != 1 && $user_id !=19655) {
            $conditions.=" and  type != 'pos' and type !='two'";
        }
        $orders = $model_order->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'fields'        => "this.*,(select count(1) from ".DB_PREFIX."order_shouhou shouhou where shouhou.order_id=order.order_id and shouhou.status in (0,1) ) as order_shouhou ",
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        $page['item_count'] = $model_order->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('order_pending'),
            ORDER_SUBMITTED => Lang::get('order_submitted'),
            ORDER_ACCEPTED => Lang::get('order_accepted'),
            ORDER_SHIPPED => Lang::get('order_shipped'),
            ORDER_FINISHED => Lang::get('order_finished'),
            ORDER_CANCELED => Lang::get('order_canceled'),
        ));
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条


        //$this->pr($orders);

        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('order.index.html');
    }

    /**
     *    查看
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info = $model_order->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_orderextm',
            'include'       => array(
                'has_ordergoods',   //取出订单商品
            ),
        ));

        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }

        $invoice_no =unserialize($order_info['invoice_no']);
        if($invoice_no) {
            foreach ($invoice_no as $key=>$sn) {
                $order_info['invoice_no'] = $sn;
                $this->assign('kd',$key);
            }
        }
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $order_info['group_id'] = 0;
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod =& m('groupbuy');
            $groupbuy = $groupbuy_mod->get(array(
                'fields' => 'groupbuy.group_id',
                'join' => 'be_join',
                'conditions' => "order_id = {$order_info['order_id']} ",
                )
            );
            $order_info['group_id'] = $groupbuy['group_id'];
        }
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            if (substr($goods['goods_image'], 0, 7) != 'http://')
            {
                $order_detail['data']['goods_list'][$key]['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
                $order_detail['data']['goods_list'][$key]['bili'] = (1 - ($order_detail['data']['goods_list'][$key]['org_price']/$order_detail['data']['goods_list'][$key]['price']))*100 ;
            }
        }

        $shouhou_mod = & m('ordershouhou');
        $ordershouhou = $shouhou_mod->find(array(
                                          'fields' => "this.*,
                                                (select user_name from ecm_member where user_id =ordershouhou.buyer_id) as buyer_name,
                                                (select store_name from ecm_store where store_id =ordershouhou.seller_id) as seller_name,
                                                (select goods_name from ecm_goods where goods_id =ordershouhou.goods_id) as goods_name",
                                          'conditions' => "order_id=$order_id and status in (0,1) ",
        ));
        //$this->pr($ordershouhou);
        $this->assign('ordershouhou', $ordershouhou);
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);

       // $this->pr($order_detail);

        $this->display('order.view.html');
    }


    function record(){

        $begin_time = $_GET['add_time_from'];
        $end_time = $_GET['add_time_to'];
        $pay_log_id = $_GET['pay_log_id'];
        if ($pay_log_id && trim($pay_log_id) != '') {
            $this->assign('pay_log_id', $pay_log_id);
            $pay_log_id_arr1 = explode('djkm', $pay_log_id);
            $pay_log_id_arr2 = explode('djk', $pay_log_id);
            if (count($pay_log_id_arr1) > 1) {
                $pay_log_id = $pay_log_id_arr1[1];
            } else if (count($pay_log_id_arr2) > 1) {
                $pay_log_id = $pay_log_id_arr2[1];
            }
        }

        $conditions = " ";
        if($_GET["add_time_from"]){
            $add_time_from= gmstr2time($_GET["add_time_from"]);
            $this->assign('add_time_from', $begin_time);
            $conditions.=" and add_time>='$add_time_from'";
        }
        if($_GET["add_time_to"]){
            $add_time_to= gmstr2time($_GET["add_time_to"]);
            $this->assign('add_time_to', $end_time);
            $conditions.=" and add_time<='$add_time_to'";
        }

        if ($pay_log_id && trim($pay_log_id) != '') {
            $paylogModel = & m('paylog');
            $paylog = $paylogModel->get('log_id='.$pay_log_id);
            $order_sns =  $paylog['order_sn'];
            $conditions.=" and order_sn in ($order_sns)";
        }

        $name= $_GET["user_name"];
        if ($name) {
            $conditions.=" and (buyer_name like '".$name."' or seller_name like '".$name."')";
        }

        $model_order =& m('order');
        $page   =   $this->_get_page(18);    //获取分页信息
        $orders = $model_order->find(array(
            'conditions'    => '1=1 and status <>11  ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'          =>  " add_time desc",
            'fields'       => "this.*",
            'count'        => true             //允许统计
        ));
        if(count($orders) > 0) {
            foreach ($orders as $key=>$val){
                if($orders[$key]["order_amount"]>0) {
                    $orders[$key]["fl"] = number_format2($orders[$key]["jine"]/$orders[$key]["order_amount"])*100;
                } else {
                    $orders[$key]["fl"] = 0;
                }

            }
        }

        $files=array("时间","订单号","买家会员","金额","商家会员名","佣金比例","支付的积分","订单状态","充值账户支付","收益账户支付","银联在线","快钱支付","支付宝支付","pos机","银联手机","财付通");

        $exportFlag=$_GET["exportFlag"];
        if($exportFlag ==1) {

            $sql = "SELECT FROM_UNIXTIME(o.add_time) as add_time  ,
                    o.order_sn,o.buyer_name,o.order_amount,
                    o.seller_name,o.jine/order_amount as bili,
                    o.jifen,o.status as order_status,
                    CASE WHEN o.yue >  0 THEN o.yue  END  AS d,
                    CASE WHEN o.koushui_yue > 0 THEN o.koushui_yue END AS e,
                    CASE WHEN p.pay_channel_id = 5  THEN p.pay_amount  END AS y ,
                    CASE WHEN p.pay_channel_id = 3 OR p.pay_channel_id >=300 AND p.pay_channel_id <=400 THEN p.pay_amount  END AS k ,
                    CASE WHEN p.pay_channel_id = 1 OR p.pay_channel_id >=11 AND p.pay_channel_id <=100 THEN p.pay_amount  END AS z ,
                    CASE WHEN o.TYPE = 'pos'  THEN o.order_amount END AS pos,
                    CASE WHEN p.pay_channel_id = 6 THEN p.pay_amount  END AS ys ,
                    CASE WHEN p.pay_channel_id = 2 OR p.pay_channel_id >=1000 AND p.pay_channel_id <=2000 THEN p.pay_amount END AS c
                    FROM ecm_order o LEFT JOIN ecm_pay p ON p.order_sn = o.order_sn where 1=1 and o.status <>11  ".$conditions;

//            $data=$model_order->find(array(
//                'conditions' => ' 1=1  and status <>11  '. $conditions,
//                'fields'=>" FROM_UNIXTIME(add_time) as add_time,
//                                order_sn,
//                                buyer_name,
//                                order_amount,
//                                seller_name,
//                                jine/order_amount as bili,
//                                jifen,
//                                status as order_status,
//                                CASE WHEN yue >  0 THEN yue  END  AS y,
//                                CASE WHEN koushui_yue > 0 THEN koushui_yue END AS k,
//                                CASE WHEN pay_message IS NOT NULL THEN pay_message  END AS p    ",
//                'order' => 'add_time DESC',
//                'count' => true
//            ));
            $model_order =& m('order');
            $data =  $model_order->db->getAll($sql);
            $this->exportexcel($data,$files,gmtime());
        } else {
            $page['item_count'] = $model_order->getCount();   //获取统计的数据
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('orders', $orders);
            $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
            $this->display("order.record.html");
        }
    }

    function zfye(){

        $type = $_GET["type"];
        $yueLog =& m('yuelog');
        $conditions = " 1=1";
        if($type == "1") {
            $conditions.=" and yue_type = 1";
        } elseif($type == "0"){
            $conditions.=" and yue_type = 0";
        }
        $conditions.="  and yue_log.user_id IN (SELECT user_id FROM ecm_tixian tixian  WHERE tixian.STATUS =2)";
        $page   =   $this->_get_page(18);    //获取分页信息
        $koushuiLogs = $yueLog->find(array(
            'conditions'    => $conditions ,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'          =>  " create_time desc",
            'fields'       => "this.*,(select user_name from ecm_member m where m.user_id = yuelog.user_id) as user_name",
            'count'        => true             //允许统计
        ));

        $files=array("时间","订单号","会员名","会员账户增加","会员账户减少","备注");

        $exportFlag=$_GET["exportFlag"];
        if($exportFlag ==1) {
            $data= $yueLog->find(array(
                'conditions' =>  $conditions,
                'fields'=>" create_time as create_time,
                         order_sn as order_sn,
                         (select user_name from ecm_member m where m.user_id = yuelog.user_id) as user_name,
                         jine as jine,
                         jine as jine,
                         beizhu as beizhu",
                'order' => 'create_time DESC',
                'count' => true
            ));
            $this->exportexcel($data,$files,gmtime());
        } else {
            $page['item_count'] = $yueLog->getCount();   //获取统计的数据
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('orders', $koushuiLogs);
            $this->assign('type', $type);
            $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
            $this->display("zfye.html");
        }
    }

    function zhtotal() {
        $mf_model = &m("memberfinance");
        $page   =   $this->_get_page(18);    //获取分页信息
//        $conditions = " 1=1 and (SELECT user_id FROM ecm_tixian tixian  WHERE tixian.STATUS =2)";
        $totals = $mf_model->find(array(
//            'conditions' => $conditions,
            'fields'       => "this.*, (koushui_yue+unkoushui_yue) as total,(select user_name from ecm_member m where m.user_id = member_finance.user_id) as user_name,(select SUM(jine) from ecm_yue_log yue_log where yue_log.user_id = member_finance.user_id and yue_log.type=4 and yue_log.yue_type = 0) as yye,
            (select sum(jine) from ecm_tixian tixian where status=2 and tixian.user_id = memberfinance.user_id) as jine",
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'        => 'total desc',
            'count'        => true             //允许统计
        ));
//        if(count($totals) > 0) {
//            foreach ($totals as $key=>$val){
//                $totals[$key]["total"] = $totals[$key]["koushui_yue"]+$totals[$key]["unkoushui_yue"];
//            }
//        }
         $files=array("会员名","会员总金额","缴税账户余额","非缴税账户余额","提现记录","总营业额");

        $exportFlag=$_GET["exportFlag"];
        if($exportFlag ==1) {
            $data=$mf_model->find(array(
                'fields'=>"(select user_name from ecm_member m where m.user_id = member_finance.user_id) as user_name, (koushui_yue+unkoushui_yue) as total,koushui_yue,unkoushui_yue,(select SUM(jine) from ecm_yue_log yue_log where yue_log.user_id = member_finance.user_id) as yye",
                'count' => true
            ));
            $this->exportexcel($data,$files,gmtime());
        }else{
                $page['item_count'] = $mf_model->getCount();   //获取统计的数据
                $this->_format_page($page);
                $this->assign('page_info', $page);
                $this->assign('orders', $totals);
                $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                    'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
                $this->display("total.zfye.html");
        }

    }

    function detail() {
        $yuelog_mod =& m('yuelog');
        $userId = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $ksyueSql = "SELECT yue.type,yue.create_time,yue.beizhu,yue.user_id,yue.id,yue.jine as jine ,m.user_name from ecm_yue_log yue,ecm_member m where yue.user_id =".$userId." and yue.type <> 0 and yue.type <> 1 and yue.type <> 7 and m.user_id =".$userId." and yue.yue_type = 1 order by create_time asc";
        $ksyueLog = $yuelog_mod->db->getAll($ksyueSql);
        $this->assign('ksyueLog', $ksyueLog);
        $total = $yuelog_mod->db->getOne("SELECT SUM(yue.jine) from ecm_yue_log yue where yue.user_id =".$userId." and yue.yue_type = 1 and yue.type <> 7");
        $this->assign('total', $total);
        $unyueSql = "SELECT yue.type,yue.create_time,yue.beizhu,yue.user_id,yue.id, yue.jine as jine,m.user_name from ecm_yue_log yue,ecm_member m where yue.user_id =".$userId." and yue.type <> 0 and yue.type <> 1 and yue.type <> 7 and m.user_id =".$userId." and yue.yue_type = 0 order by create_time asc";
        $unyueLog = $yuelog_mod->db->getAll($unyueSql);
        $untotal = $yuelog_mod->db->getOne("SELECT SUM(yue.jine) from ecm_yue_log yue where yue.user_id =".$userId." and yue.yue_type = 0 and  yue.type <> 0 and yue.type <> 1 and yue.type <> 7");
        $this->assign('untotal', $untotal);
        $this->assign('unksyueLog', $unyueLog);
        $this->display("zhye.detail.html");
    }

    //服务中心收益列表
    function fwzx_list() {
        $member_mode= &m("member");
        $page   =   $this->_get_page(18);    //获取分页信息
        $sql = "select m.user_id as id, m.region_name as region_name ,m.email as email, f.koushui_yue as koushui_yue from ecm_member m , ecm_member_finance f where f.user_id = m.user_id and m.member_type ='02' and m.status =2 and m.region_name !='' group by m.user_id" ;

        $sqlCount ="select count(1) from ecm_member m , ecm_member_finance f where f.user_id = m.user_id and m.member_type ='02' and m.status =2 and  m.region_name !='' group by m.user_id " ;

        $data= $member_mode->getAll($sql);
        $member_mode->_updateLastQueryCount($sqlCount);
        $page['item_count'] = $member_mode->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('orders', $data);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));

//        $this->assign('zhye',)
        $this->display("fwzx_list.html");
    }

    function fwzx1() {
        $member_mode= &m("member");
        $page   =   $this->_get_page(18);    //获取分页信息
        if ($_GET['type'] && $_GET['type']=='yue') {
            $addTime = '%Y-%m';
        } else {
            $addTime = '%Y-%m-%d';
        }
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])){
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))){
                $sort  = 'addTime';
                $order = 'desc';
            }
        }
        else {
            $sort  = 'addTime';
            $order = 'desc';
        }

        $sql = "SELECT m.user_id AS id, FROM_UNIXTIME(o.add_time, '".$addTime."') AS addTime,  SUM(o.order_amount) AS amount, SUM(o.jine) AS jine, m.region_name AS fwzx FROM
              ecm_member m,
              ecm_store s,
              ecm_order o
            WHERE s.region_id = m.region_id
              and s.dropState = 1
              and m.dropState = 1
              AND m.member_type = '02'
              AND s.state = 1
              AND o.seller_id = s.store_id
              AND o.STATUS = 40
              AND m.region_name != ''
              AND (
                o.buyer_name != 'shangjia04'
                AND o.seller_name != 'shangjia04'
                AND o.seller_name != 'novayoung'
              )
             GROUP BY FROM_UNIXTIME(o.add_time, '".$addTime."')
            ORDER BY ".$sort." ".$order." limit ".$page['limit'];
        $sqlCount ="SELECT count(1) FROM
               ecm_member m,
              ecm_store s,
              ecm_order o
            WHERE s.region_id = m.region_id
              and s.dropState = 1
              and m.dropState = 1
              AND m.member_type = '02'
              AND s.state = 1
              AND m.region_name != ''
	     AND o.seller_id = s.store_id
              AND o.STATUS = 40
              AND (
                o.buyer_name != 'shangjia04'
                AND o.seller_name != 'shangjia04'
                AND o.seller_name != 'novayoung'
              )";
            $data= $member_mode->getAll($sql);
            $member_mode->_updateLastQueryCount($sqlCount);
            $page['item_count'] = $member_mode->getCount();
             $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('orders', $data);
            $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
            $this->display("fwzx.html");
    }

   function fwzx() {

       $user_id = $this->visitor->get('user_id');
//       if ($user_id != 1) {
//           exit;
//       }
        $member_mode= &m("member");
        $page   =   $this->_get_page(18);    //获取分页信息
        if ($_GET['type'] && $_GET['type']=='yue') {
            $addTime = '%Y-%m';
        } else {
            $addTime = '%Y-%m-%d';
        }

       $begin_time = $_GET['begin_time'];
       $end_time = $_GET['end_time'];

       $timeCondition = "";
       if ($begin_time && $begin_time != "") {
           $this->assign('begin_time', $begin_time);
           $begin_time = $begin_time . " 00:00:00";
           $timeCondition = " AND o.add_time >= " . gmstr2time($begin_time);
       }
       if ($end_time && $end_time != "") {
           $this->assign('end_time', $end_time);
           $end_time = $end_time . " 23:59:59";
           $timeCondition = " AND o.add_time <= " . gmstr2time($end_time);
       }
       if ($begin_time && $begin_time != "" && $end_time && $end_time != "") {
           $timeCondition = " AND (o.add_time >= " . gmstr2time($begin_time) . " AND o.add_time <= " . gmstr2time($end_time) . ")";
       }

	//更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])){
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))){
                $sort  = 'addTime';
                $order = 'desc';
            }
        }
        else{
            $sort  = 'addTime desc, amount ';
            $order = 'desc';
        }
        $sql = "SELECT m.user_id as id, FROM_UNIXTIME(o.add_time, '".$addTime."') AS addTime,  SUM(o.order_amount) AS amount, SUM(o.jine) AS jine, m.region_name AS fwzx FROM
              ecm_member m,
              ecm_store s,
              ecm_order o
            WHERE s.region_id = m.region_id
              AND m.member_type = '02'
              AND s.state = 1
              AND o.seller_id = s.store_id
              AND o.STATUS = 40
              AND (
                o.buyer_name != 'shangjia04'
                AND o.seller_name != 'shangjia04'
                AND o.seller_name != 'novayoung'
              ) " . $timeCondition  . "
             GROUP BY FROM_UNIXTIME(o.add_time, '".$addTime."'), m.region_name, m.user_id
           ORDER BY ".$sort." ".$order." limit ".$page['limit'];

        $sqlCount ="SELECT count(distinct FROM_UNIXTIME(o.add_time, '".$addTime."'), m.region_name, m.user_id) FROM
               ecm_member m,
              ecm_store s,
              ecm_order o
            WHERE s.region_id = m.region_id
              AND m.member_type = '02'
              AND s.state = 1
              AND o.seller_id = s.store_id
              AND o.STATUS = 40
              AND (
                o.buyer_name != 'shangjia04'
                AND o.seller_name != 'shangjia04'
                AND o.seller_name != 'novayoung'
              )" . $timeCondition;

       $yyeSqlCount ="SELECT sum(o.order_amount) as order_amount, sum(jine) as jine FROM
              ecm_order o
            WHERE o.STATUS = '40'
              AND (
                o.buyer_name != 'shangjia04'
                AND o.seller_name != 'shangjia04'
                AND o.seller_name != 'novayoung'
              )" . $timeCondition;
            $yye = $member_mode->getAll($yyeSqlCount);
            $this->assign('yye', $yye);

            $jiangjinSql = "select sum(jine * count) from ecm_jiangpin where 1=1 ";
            $timeCondition = "";
            if ($begin_time && $begin_time != "") {
               $timeCondition = " AND create_time >= '" . $begin_time . "'";
            }
            if ($end_time && $end_time != "") {
               $timeCondition = " AND create_time <= '" . $end_time . "'";
            }
            if ($begin_time && $begin_time != "" && $end_time && $end_time != "") {
               $timeCondition = " AND (create_time >= '" . $begin_time . "' AND create_time <= '" . $end_time . "')";
            }
            $jiangjin = $member_mode->getOne($jiangjinSql . $timeCondition);
            $this->assign('jiangjin', $jiangjin);

           $chouJiangSql = "select sum(jine) from ecm_choujiang_detail where 1=1 ";
           $timeCondition = "";
           if ($begin_time && $begin_time != "") {
               $timeCondition = " AND create_time >= '" . $begin_time . "'";
           }
           if ($end_time && $end_time != "") {
               $timeCondition = " AND create_time <= '" . $end_time . "'";
           }
           if ($begin_time && $begin_time != "" && $end_time && $end_time != "") {
               $timeCondition = " AND (create_time >= '" . $begin_time . "' AND create_time <= '" . $end_time . "')";
           }
           $choujiang = $member_mode->getOne($chouJiangSql . $timeCondition);
           $this->assign('choujiang', $choujiang);

           $tichengSql = "select sum(jine) from ecm_ticheng_detail where user_id > 0 ";
           $timeCondition = "";
           if ($begin_time && $begin_time != "") {
               $timeCondition = " AND create_time >= '" . $begin_time . "'";
           }
           if ($end_time && $end_time != "") {
               $timeCondition = " AND create_time <= '" . $end_time . "'";
           }
           if ($begin_time && $begin_time != "" && $end_time && $end_time != "") {
               $timeCondition = " AND (create_time >= '" . $begin_time . "' AND create_time <= '" . $end_time . "')";
           }
           $ticheng = $member_mode->getOne($tichengSql . $timeCondition);
           $this->assign('ticheng', $ticheng);
            $this->assign('毛', $yye[0]['jine'] -($choujiang + $ticheng));

            $data= $member_mode->getAll($sql);
            $member_mode->_updateLastQueryCount($sqlCount);
            $page['item_count'] = $member_mode->getCount();
             $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('orders', $data);
            $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
            $this->display("fwzx.html");
    }


    //服务中心业绩
    function fwzx_shouyi() {
        $fwzxId = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if(!$fwzxId) {
            $this->show_message('服务中心不存在!',
                'back_list',        'index.php?app=order&act=fwzx_list');
            exit;
        }
        $member_mode = &m('member');
        $userInfo=$member_mode->get($fwzxId);
        if ($_GET['type'] && $_GET['type']=='yue') {
            $addTime = '%Y-%m';
        }else {
            $addTime = '%Y-%m-%d';
        }
        $begin_time = $_GET['add_time_from'];
        $end_time = $_GET['add_time_to'];

        $timeCondition = "";
        if ($begin_time && $begin_time != "") {
            $this->assign('begin_time', $begin_time);
            $begin_time = $begin_time . " 00:00:00";
            $timeCondition = " AND create_time >= " . gmstr2time($begin_time);
        }
        if ($end_time && $end_time != "") {
            $this->assign('end_time', $end_time);
            $end_time = $end_time . " 23:59:59";
            $timeCondition = " AND create_time <= " . gmstr2time($end_time);
        }
        if ($begin_time && $begin_time != "" && $end_time && $end_time != "") {
            $timeCondition = " AND (create_time >= " . gmstr2time($begin_time) . " AND create_time <= " . gmstr2time($end_time) . ")";
        }
        $this->assign("fwzx",$userInfo['region_name']);
        $this->assign("id",$fwzxId);
        $page   =   $this->_get_page(18);    //获取分页信息
        $yue_log = & m("yuelog");
        $sqltotal = "select SUM(jine) from ecm_yue_log where user_id =".$fwzxId." and yue_type =1 and type=6";
        $this->assign("total",$yue_log->getOne($sqltotal));
        $sql = "SELECT   DATE_FORMAT(create_time,'".$addTime."') createTime,user_id, SUM(jine) as jine FROM ecm_yue_log where user_id =".$fwzxId." and yue_type =1  and type = 6".$timeCondition." group by DATE_FORMAT(create_time,'".$addTime."') order by create_time desc";
        $sqlCount = "SELECT  count(1) FROM ecm_yue_log where user_id =".$fwzxId." and yue_type =1 and type =6".$timeCondition." group by DATE_FORMAT(create_time,'".$addTime."')";

        $data = $yue_log->getAll($sql);
        $this->_format_page($page);
        $yue_log->_updateLastQueryCount($sqlCount);
        $page['item_count'] = $yue_log->getOne($sqlCount);
        $this->assign('page_info', $page);
        $this->assign('data', $data);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display("fwzx_shouyi.html");
    }

    function fwzx_detail() {

        $fwzxId = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if(!$fwzxId) {
            $this->show_message('服务中心不存在!',
                'back_list',        'index.php?app=order&act=fwzx_list');
            exit;
        }
        $store_mode = &m('store');
        $member_mode = &m('member');
        $field = 's.store_name';
        $conditions=" 1=1 and member.dropState=1 and s.dropState=1 ";
        $conditions .= $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),
            array(
            'field' => 'add_time',
            'name'  => 'add_time_from',
            'equal' => '>=',
            'handler'=> 'gmstr2time',
        ),
            array(
            'field' => 'add_time',
            'name'  => 'add_time_to',
            'equal' => '<=',
            'handler'   => 'gmstr2time_end',
        )));


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
            $sort  = 'jine';
            $order = 'desc';
        }
        $page = $this->_get_page(18);
        $userInfo=$member_mode->get($fwzxId);
        $this->assign("fwzx",$userInfo['region_name']);
        if(!empty($userInfo['region_id'])){
            $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
        }
        $stores =$store_mode->find(array(
            'conditions' => $conditions,
            'join'  => 'belongs_to_user',
            'fields'=> "this.*,
                      member.user_name,member.im_qq,member.nick_name,
                      (select count(1) from ".DB_PREFIX."goods g  where g.store_id = s.store_id and g.dropState<>0)  as goodsCount,
                      (select count(1) from ".DB_PREFIX."order ord  where ord.seller_id = s.store_id and ord.status =40) as orderCount,
                      (select user_name from ".DB_PREFIX."member m where  m.user_id=s.fwzx and m.dropState=1) as service_user,
                      (select count(1) from ".DB_PREFIX."member m where  m.t_id=s.store_id and m.dropState=1) as tuserCount,
                      (select SUM(order_amount) from ".DB_PREFIX."order ord where  ord.seller_id=s.store_id and ord.status=40) as jine",
            'limit' => $page['limit'],
            'count' => true,
            'order' => "$sort $order"
        ));
        $this->assign('stores', $stores);
        $page['item_count'] = $store_mode->getCount();
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->display("fwzx_detail.html");
    }

    function ajax_store_record() {
        $order_mode = & m("order");
        $seller_id =  $_POST['id'];

        $orders=$order_mode->find(array(
            'conditions'    => " order_alias.seller_id=" . $seller_id." and order_alias.status = 40",
            'fields' => 'order_id,order_sn,seller_name,buyer_name,order_amount,add_time,type'
        ));
        $this->assign("orders",$orders);
        $this->display("ajax_store_record.html");
    }

    function store_ranking() {

        $store_mode = &m('store');
        $field = 's.store_name';
        $conditions=" 1=1 and member.dropState=1 and s.dropState=1 ";
        $conditions .= $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),
            array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            )));
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])) {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))) {
                $sort  = 'sort_order';
                $order = '';
            }
        } else {
            $sort  = 'jine';
            $order = 'desc';
        }
        $page = $this->_get_page(18);
        $stores =$store_mode->find(array(
            'conditions' => $conditions,
            'join'  => 'belongs_to_user',
            'fields'=> "this.*,
                      member.user_name,member.im_qq,member.nick_name,
                      (select count(1) from ".DB_PREFIX."goods g  where g.store_id = s.store_id and g.dropState<>0)  as goodsCount,
                      (select count(1) from ".DB_PREFIX."order ord  where ord.seller_id = s.store_id and ord.status =40) as orderCount,
                      (select user_name from ".DB_PREFIX."member m where  m.user_id=s.fwzx and m.dropState=1) as service_user,
                      (select count(1) from ".DB_PREFIX."member m where  m.t_id=s.store_id and m.dropState=1) as tuserCount,
                      (select SUM(order_amount) from ".DB_PREFIX."order ord where  ord.seller_id=s.store_id and ord.status=40) as jine",
            'limit' => $page['limit'],
            'count' => true,
            'order' => "$sort $order"
        ));
        $page['item_count'] = $store_mode->getCount();
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign("stores",$stores);
        $this->display("store_ranking.html");
    }


    function tixian() {
        $fwzxId =$_GET['id'] ;
        if(!$fwzxId) {
            $this->show_message('服务中心不存在!',
                'back_list',        'index.php?app=order&act=fwzx_list');
            exit;
        }
        $member_mode = &m('member');
        $member_finance_mode = &m('memberfinance');
        $member_bank_mode = & m('memberbank');
        $tixian = & m("tixian");
        $channel_mode = & m('channel');
        $yue_log_mode = & m("yuelog");
        $userInfo=$member_mode->get($fwzxId);
        $this->assign("fwzx",$userInfo['region_name']);
        $this->assign("member",$userInfo);
        $this->assign("id",$fwzxId);
        $member_bank = $member_bank_mode->get(array(
            'conditions' => " user_id=".$fwzxId." and dropState = 1 and if_default = 1"
        ));

        $memberfin_finance = $member_finance_mode->get("user_id='$fwzxId'");
        $this->assign('koushui_yue',$memberfin_finance['koushui_yue']);
        if(!IS_POST) {
            $begin_time = $_GET['add_time_from'];
            $end_time = $_GET['add_time_to'];

            $conditions = " 1=1 ";
            if($_GET["add_time_from"]){
                $add_time_from=$_GET["add_time_from"];
                $this->assign('add_time_from', $begin_time);
                $conditions.=" and add_time>='$add_time_from'";
            }
            if($_GET["add_time_to"]){
                $add_time_to=$_GET["add_time_to"];
                $this->assign('add_time_to', $end_time);
                $conditions.=" and add_time<='$add_time_to'";
            }
//            if ($begin_time && $begin_time != "" && $end_time && $end_time != "") {
//                $conditions .= " AND (add_time >= " . gmstr2time($begin_time) . " AND add_time <= " . gmstr2time($end_time) . ")";
//            }
            $conditions.= " and user_id =".$fwzxId;
            $page   =   $this->_get_page(18);    //获取分页信息

            $tixians=$tixian->find(array(
                'conditions' =>$conditions,
                'fields'=> 'this.* ',
                'count' => true,
                'order' => 'add_time DESC',
                'limit' => $page['limit'],
            ));

            $this->assign('member_bank',$member_bank);
            $page['item_count'] = $tixian->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('data', $tixians);
            $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));

            $this->display('order_tixian.html');
        }else{
            $jine = floatval($_POST['jine']);
            $userId = $_POST['user_id'];
            $bank_name = $_POST['bank_name'];
            $kaihuhang = $_POST['kaihuhang'];
            $user_name = $_POST['user_name'];
            $bank_kahao = $_POST['bank_kahao'];
            $card_id = $_POST['card_id'];
      //      $phone_mob = $_POST['phone_mob'];
            $channel_code = $member_bank['bank_code'];

            $channel = $channel_mode->get(" channel_code='$channel_code' and status =1");



            if($jine < 0 || $jine >$memberfin_finance['koushui_yue']) {
                $this->show_message('提现金额必须大于0，且不能大于账户余额！',
                    'back_list',        'index.php?app=order&act=fwzx_list');
                exit;
            }

            if($jine > 0 && $userId) {
                $sql = "UPDATE ecm_member_finance SET koushui_yue = koushui_yue - ".$jine." WHERE user_id = ".$userId;

                $data = array(
                    'user_id' => $userId,
                    'user_name' =>$user_name,
                    'channel_id' =>$channel['channel_id'],
                    'channel_name' =>$bank_name,
                    'channel_card' =>$bank_kahao,
                    'jine'=> $jine,
                    'status'=> 1,
                    'add_time'=> date('Y-m-d H:i:s'),
                    'add_date'=> date('Y-m-d'),
                    'type' => 0,
                    'auto_jiesuan' => 0,
                    'shenfenzheng' => $card_id,
                    'kaihuhang' =>$kaihuhang,
                );

                $data_yue_log =array(
                    'user_id' => $userId,
                    'jine' => $jine,
                    'beizhu' => $userInfo['user_name'].'后台代提现'.$jine,
                    'create_time'=> date('Y-m-d H:i:s'),
                    'type' => 3,
                    'yue_type'=> 0,
                );

                if(!$tixian->add($data)) {
                    $this->show_message('提现失败！一天只能提现一次!',
                        'back_list',        'index.php?app=order&act=fwzx_list');
                    exit;
                } else {
                    if($member_finance_mode->db->query($sql) > 0 && $yue_log_mode->add($data_yue_log) > 0){
                        $this->show_message('提现成功！',
                            'back_list',        'index.php?app=order&act=fwzx_list');
                    }
                }


            }

        }
    }


}
?>
