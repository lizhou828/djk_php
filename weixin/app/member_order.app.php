<?php
/**
 * Created by JetBrains PhpStorm.
 * User: liz
 * Date: 13-11-25
 * Time: 下午4:48
 * To change this template use File | Settings | File Templates.
 */
/**
 * 微商城用户中心:订单管理
*/
class Member_orderApp extends MallbaseApp{

    //订单详情
    function detail(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        $order_id = $_GET['order_id'];
        $model_order =& m('order');
        $order_info = $model_order->get("order_id=".$order_id);
        if (!$order_info){
            header("Content-Type:text/html;charset=utf-8");
            echo "该订单不存在";
            return;
        }


        $type=$_GET['type'];
        if( $type=="seller" && $order_info['seller_id'] != $userId){
            header("Content-Type:text/html;charset=utf-8");
            echo "不能查看别人的订单！";
            return;
        }
        if( $type=="buyer" && $order_info['buyer_id'] != $userId){
            header("Content-Type:text/html;charset=utf-8");
            echo "不能查看别人的订单！";
            return;
        }


        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods){
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
        $this->assign('order', $order_info);
        $invoiceArray = explode("|",$order_info['invoice_no']);

        if($order_info['invoice_no']  && count($invoiceArray) == 2){
            $this->assign('wuliugongsi', $invoiceArray[0]);
            $this->assign('wuliudanhao', $invoiceArray[1]);
        }
        $this->assign('user', $user);
        $this->assign("goods",$order_detail['data']['goods_list']);

        $this->assign("order_extm",$order_detail['data']['order_extm']);


        $type=$_GET['type'];
        //卖家身份查看订单详细(已付款的订单可以发货)
        if($type == 'seller'){
            if($_GET['msg'] && $_GET['msg']==1){
                $this->assign("msg","发货成功!");
            }
            if( $order_info['invoice_no'] && count(explode("|",$order_info['invoice_no']))> 1 ){
                $invoice = explode("|",$order_info['invoice_no']);
                $this->assign("kuaidigongsi" ,$invoice[0]);
                $this->assign("invoice_no" ,$invoice[1]);
            }
            $this->assign("type",$type);
            $this->display('member_order.detail_seller.html');
        //买家身份查看订单详细（未付款的可以付款）
        }else{
            $this->display('member_order.detail.html');
        }

    }

    //订单列表
    function all(){

        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        if(!$user){
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . rawurlencode("index.php?app=member_order&act=all&type=buyer"));
            return;
        }

        $user_id=$_GET['user_id'];
        if($user_id && $user_id != 0 && $user_id != $this -> visitor ->get("user_id")){
            header("Content-Type:text/html;charset=utf-8");
            echo "<div style='margin-top: 50px;text-align: center;font-size: 28px;' onclick='javascript:history.go(-1)'>不能查看别人的订单！</div>";
            return;
        }


        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $order_mod =& m('order');
        $conditions = "";

        //订单时间段
        $startTime=null;
        $endTime=null;
        if( $_GET['time'] == 'threeMonthsAgo' ){//三个月前
            $endTime = strtotime("-3 months");
            $conditions .= "  add_time <= ".$endTime." and";
        }else{//最近3个月
            $endTime = time();
            $startTime = strtotime("-3 months");
            $conditions .= "  add_time >=".$startTime." and add_time <= ".$endTime." and";
        }
        $this->assign("time",$_GET['time']);


        //订单状态
        $status = $_GET['status'];

        if( $status == 'all_orders' || $status==""){
            $conditions .= "  status >= 0 ";
        }elseif($status == ORDER_SUBMITTED){//已付款状态（查询已付款的和待发货的）
            $conditions .= "  status in(10,20)";
        }else{
            $conditions .= "  status =".$status;
        }
        $this->assign("status",$status);




        //商家以seller or buyer身份查看订单
        $type=$_GET['type'];
        $this->assign("type",$type);

        //一般会员
        $title="";
        $store_mod = &m("store");
        if($user['member_type']=='01'){
            //是买家还是商家
            $store = $store_mod->get("store_id =".$userId." and state=1 and dropState=1");
            if($store){
                $user['store']=$store;
                $user['store_id']=$store['store_id'];

                if( $type && $type=='seller'){
                    $conditions .= " and seller_id= ".$userId;
                    $title="卖出的商品";
                }elseif( $type && $type=='buyer'){
                    $conditions .= " and buyer_id= ".$userId;
                    $title="买到的商品";
                }
            }else{
                $conditions .= " and buyer_id= ".$userId;
            }


        //服务中心
        }elseif($user['member_type']=='02'){
            //查找服务中心子账户
            $users=$user_mod->find(array(
                "conditions"=>"member_type='03' and region_id=".$user["region_id"],
                "fields"    =>"user_id,user_name"
            ));
            $serviceCenters = array($user['user_id']);
            if(count($users)>0){
                foreach($users as $k=>$v){
                    array_push($serviceCenters,$v["user_id"]);
                }
            }
            //所有托管店铺
            $trusteeship_store = $store_mod->find(array(
                "conditions"=>"dropState =1 and tuoguan=1 and fwzx ".db_create_in($serviceCenters),
                "fields"    =>"store_id,fwzx"
            ));
            $storeArray = array();
            if($trusteeship_store && count($trusteeship_store) > 0){
                foreach($trusteeship_store as $k=>$v){
                    array_push($storeArray,$v['store_id']);
                }
            }
            $conditions.=" and seller_id".db_create_in($storeArray);
         }




        /* 查找订单 */
        $page = $this->_get_page(20);
        $orders = $order_mod->findAll(array(
            'conditions'    => "{$conditions}",
            'fields'        => "this.*",
            'count'         => true,
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));

        $page['item_count'] = $order_mod->getCount();

        $this->assign('types', array('all'     => Lang::get('all_orders'),
            'pending' => Lang::get('pending_orders'),
            'submitted' => Lang::get('submitted_orders'),
            'accepted' => Lang::get('accepted_orders'),
            'shipped' => Lang::get('shipped_orders'),
            'finished' => Lang::get('finished_orders'),
            'canceled' => Lang::get('canceled_orders')));
        $this->assign('status', $status);
        $this->assign('orders', $orders);
        $this->assign('user', $user);
        $this->_format_page($page);
        $this->assign('page', $page);
        $this->assign('title',$title);
        $this->assign('TO_PAY_URL', TO_PAY_URL);
        if($user['store']){
            $this->display('member_order.all_seller.html');
        }else{
            $this->display('member_order.all.html');
        }

    }

    //取消订单
    function cancel(){
        //登录验证
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        //获取订单
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id){
            echo "<script type=\"text/javascript\">window.parent.js_fail('".Lang::get('no_such_order')."');</script>";
            exit;
        }
        $model_order    =&  m('order');
        /* 只有待付款的订单可以取消 */
        $order_info     = $model_order->get("order_id={$order_id}");
        if (empty($order_info)){
            $this->json_result(0,"没有这个订单 ") ;
            exit;
        }
        $model_member =& m('member');
        $order_info=$model_order->get("order_id=$order_id and status !=0");
        if(!$order_info){
            $this->json_result(0,"订单已取消!");
            exit;
        }
        $model_order->edit($order_id, array('status' => ORDER_CANCELED));
        if ($model_order->has_error()){
//                echo "<script type=\"text/javascript\">window.parent.js_fail('".$model_order->get_error()."');</script>";
            echo $model_order->get_error();
            exit;
        }

        // 加回商品库存
        $model_order->change_stock('+', $order_id);

        // 记录订单操作日志
        $order_log =& m('orderlog');
        $order_log->add(array(
            'order_id'  => $order_id,
            'operator'  => addslashes($this->visitor->get('user_name')),
            'order_status' => order_status($order_info['status']),
            'changed_status' => order_status(ORDER_CANCELED),
            'log_time'  => gmtime(),
        ));

        /* 发送给卖家订单取消通知 */
        $seller_info   = $model_member->get($order_info['seller_id']);
        $mail = get_mail('toseller_cancel_order_notify', array('order' => $order_info, 'reason' => $_POST['remark']));
        $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
        $model_member->commit_transaction();


        //是否调用接口      默认0不调用
        $flag = 0;
        if($order_info["status"]!=11 || $order_info["koushui_yue"] > 0 || $order_info["yue"] >0 || $order_info["jifen"]>0){
            $flag =1;
        }

        if($flag!=1){
            $new_data = array(
                'status'    => Lang::get('order_canceled'),
                'actions'   => array(), //取消订单后就不能做任何操作了
            );
            $this->json_result(1,"取消成功!");
            exit;
        }

        //取消订单直接调用java那边，忽略返回
        $params["type"] = "cancel_order";      //confirmOrder   cancelOrder
        $params["userId"] = $userId;
        $params["orderSn"] =$order_info["order_sn"];
        $params["queryType"] = 1;
        $params["tId"] ="0";
        $params["userName"] ="";
        $params["channelCode"] ="";
        $params["channelName"] ="";
        $params["channelCard"] ="";
        $params["jine"] ="0";
        $params["regionId"] ="0";
        $Client = new HttpClient(JAVA_LOCATION);
        $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
        $pageContents = HttpClient::quickPost($url, $params);
        /*$data = json_decode($pageContents);
        if(!$data || $data->code != 200 ){
            echo $pageContents;
            echo "<script type=\"text/javascript\">window.parent.js_fail('服务器繁忙，请稍后重试【".$data->code."】');</script>";
            exit;
        }*/

        $new_data = array(
            'status'    => Lang::get('order_canceled'),
            'actions'   => array(), //取消订单后就不能做任何操作了
        );
        $this->json_result(1,"取消成功!取消订单的金额如不能即时到帐，请在10分钟后再查阅");
    }

    //确认取消订单
    function confirmCancel(){
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $this->assign("order_id",$order_id);
        $this->display("member_order.confirmCancel.html");
    }

    //删除订单
    function delete(){
        //登录验证
        $userId = $this -> visitor ->get("user_id");
        $this->isLogin();
        //获取订单
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id){
            $this->json_result("-1","没有这个订单!");
            exit;
        }
        $model_order =&m('order');
        $model_member=&m('member');
        $order = $model_order->get("order_id=".$order_id." and buyer_id=".$userId);
        //只有未付款的订单可以删除
        if($order['status'] != ORDER_PENDING){
            $this->json_result("-1","只有未付款的订单才可以删除!");
            exit;
        }
        if( (floatval($order['yue']) + floatval($order['koushui_yue']) ) > 0 ){
            $this->json_result("-1","支付过的订单不可以删除!");
            exit;
        }
        $affectRows = $model_order->drop($order_id);
//        var_dump($affectRows);exit;
        if ($model_order->has_error()){
            echo $model_order->get_error();
            exit;
        }

        // 加回商品库存
        $model_order->change_stock('+', $order_id);

        // 记录订单操作日志
        $order_log =& m('orderlog');
        $order_log->add(array(
            'order_id'  => $order_id,
            'operator'  => addslashes($this->visitor->get('user_name')),
            'order_status' => order_status($order['status']),
//            'changed_status' => order_status(ORDER_DELETE),
            'changed_status' => "已删除",
            'log_time'  => gmtime(),
        ));

        /* 发送给卖家订单取消通知 */
        $seller_info   = $model_member->get($order['seller_id']);
        $mail = get_mail('toseller_cancel_order_notify', array('order' => $order, 'reason' => $_POST['remark']));
        $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
        $model_member->commit_transaction();
        $this->json_result($affectRows,$affectRows &&$affectRows > 0 ? "删除成功！" :"删除失败..");

    }

    //订单支付 银联手机支付
    function unionPay(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        //验证页面的链接中订单编号的合法性
        $orderSn = $_GET['orderSn'];
        $orderSnArray = "";
        if($orderSn){
            $orderSnArray = explode(",",$orderSn);
        }
        $order_mod = &m("order");
        $orderAmount = 0;
        $orderSnNew="";
        $orderAlreadyPay = 0;
        if($orderSnArray && count($orderSnArray) > 0 ){
            foreach($orderSnArray as $key){
                $order = $order_mod->get("order_sn=".$key);
                //过滤掉不合法的订单
                if( !$key|| $key['buyer_id'] != $key['user_id'] || $order['status'] !=ORDER_PENDING){
                    continue;
                    //合法订单：生成订单编号字符串(以逗号隔开)，计算订单总金额
                }else{
                    if($orderSnNew==""){
                        $orderSnNew = $order['order_sn'];
                    }else{
                        $orderSnNew = $orderSnNew .",".$order['order_sn'];
                    }
                    $orderAmount += $order['order_amount'];     //计算订单总金额
                    $orderAlreadyPay += ( floatval($order['yue']) + floatval($order['koushui_yue']) );
                }
            }
            $orderStillNeedToPay = floatval($orderAmount) - $orderAlreadyPay;
            if($order['is_daifu']==1){//如果是商家代付的订单
                $user_id=$order['buyer_id'];
            }else{
                $user_id=$user['user_id'];
            }
            $url="?userId=".$user_id."&orderSn=".$orderSnNew."&payType=1&WIDtotal_fee=".$orderStillNeedToPay."&WIDsubject=&WIDbody=&out_trade_no=&WIDseller_email=".SELLER_EMAIL;
            header("Location:"."{$SITE_URL}/dajike-account/upmpPay.action".$url);
        }else{
            echo "没有这样的订单！";
            exit;
        }
    }
    //银联手机支付结果
    function upmpResult(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $this->assign("user",$user);

        $payStatus =$_GET['payStatus'];
        if($payStatus != "true" &&  $payStatus!="false"){
            echo "illegal upmpPay status";
            exit;
        }
        $this->assign("payStatus",$payStatus);
        $this->display("member_order.upmpResult.html");
    }




    //订单支付 支付宝手机支付
    function alliyPay(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        //验证页面的链接中订单编号的合法性
        $orderSn = $_GET['orderSn'];
        $orderSnArray = "";
        if($orderSn){
            $orderSnArray = explode(",",$orderSn);
        }
        $order_mod = &m("order");
        $orderAmount = 0;
        $orderSnNew="";
        $orderAlreadyPay = 0;
        if($orderSnArray && count($orderSnArray) > 0 ){
            foreach($orderSnArray as $key){
                $order = $order_mod->get("order_sn=".$key);
                //过滤掉不合法的订单
                if( !$key|| $key['buyer_id'] != $key['user_id'] || $order['status'] !=ORDER_PENDING){
                    continue;
                //合法订单：生成订单编号字符串(以逗号隔开)，计算订单总金额
                }else{
                    if($orderSnNew==""){
                        $orderSnNew = $order['order_sn'];
                    }else{
                        $orderSnNew = $orderSnNew .",".$order['order_sn'];
                    }
                    $orderAmount += $order['order_amount'];     //计算订单总金额
                    $orderAlreadyPay += ( floatval($order['yue']) + floatval($order['koushui_yue']) );
                }
            }
            $orderStillNeedToPay = floatval($orderAmount) - $orderAlreadyPay;

            if($order['is_daifu']==1){//如果是商家代付的订单
                $user_id=$order['buyer_id'];
            }else{
                $user_id=$user['user_id'];
            }
            $url="?userId=".$user_id."&orderSn=".$orderSnNew."&payType=1&phoTotal_fee=".$orderStillNeedToPay."&WIDsubject=&WIDbody=&out_trade_no=&WIDseller_email=".SELLER_EMAIL;
            header("Location:".PAY_ORDER_BY_MOBILE.$url);
        }else{
            echo "没有这样的订单！";
            exit;
        }
    }

    //支付宝支付结果
    function alliyPayResult(){
        $log_id= $_GET['log_id'];
        $payStatus =$_GET['payStatus'];
        if(!$log_id || $log_id==""|| intval($log_id) <= 0){
            echo "非法log_id！";
            exit;
        }
        $paylogMod = &m("paylog");
        $payLog = $paylogMod->get("log_id=".$log_id);
        if(!$payLog || $payLog['log_id'] <= 0 ){
            echo "非法log_id！!";
            exit;
        }
        if($payStatus != "true" &&  $payStatus!="false"){
            echo "非法支付状态";
            exit;
        }
        $this->assign("payLog",$payLog);
        $order_sn = $payLog['order_sn'];
        $orderSnArray = explode(",",$order_sn);
        $order_mod = &m("order");
        $order = $order_mod->get("order_sn=".current($orderSnArray));

        $this->assign("orderSnArray",$orderSnArray);;
        $this->assign("payStatus",$payStatus);
        if($order && $order["type"]=="weidian"){
//            header("Location:".);
        }else{
            $this->display("member_order.alliyPayResult.html");
        }
    }


    //登录验证
    function isLogin(){
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        if(!$user){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
        }else{
            return true;
        }

    }





    /**
     * 卖家发货：订单已付款（买家），等待发货
     * order.status==20
     */
    function sendGoodsPage(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        $store_mod =&m('store');
        $store = $store_mod->get("store_id=".$user['user_id']);
        if(!$store || $store['store_id'] < 0){
            exit('非法操作！');//只有卖家能发货
        }

        $order_id =$_GET['order_id'];
        $type=$_GET['type'];
        if($type != 'seller'){
            exit('非法操作！');//只有卖家能发货
        }
        $this->assign("type",$type);

        $sendMethod= $_GET['$sendMethod'] ? $_GET['$sendMethod'] : $_POST['$sendMethod'];
        $this->assign("sendMethod",$sendMethod);
        $this->assign("order_id",$order_id);
        $this->assign('kdgs', Conf::get('kdgs'));
        $this->display("member_order.sendGoodsPage.html");
    }

    function sendGoodsPage1(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        $store_mod =&m('store');
        $store = $store_mod->get("store_id=".$user['user_id']);
        if(!$store || $store['store_id'] < 0){
            exit('非法操作！');//只有卖家能发货
        }

        $order_id =$_GET['order_id'];
        $type=$_GET['type'];
        if($type != 'seller'){
            exit('非法操作！');//只有卖家能发货
        }
        $this->assign("type",$type);

        $sendMethod= $_GET['sendMethod'] ? $_GET['sendMethod'] : $_POST['sendMethod'];
        $this->assign("sendMethod",$sendMethod);
        $this->assign("order_id",$order_id);

        $kdgs = $_GET['kdgs'];
        $this->assign('kdgs', $kdgs);

        $this->display("member_order.sendGoodsPage1.html");
    }

    function sendGoods(){

        $this->isLogin();

        $order_id = $_POST['order_id'] ? $_POST['order_id'] : $_GET['order_id'];
        $model_order = &m("order");
        $order_info = $model_order->get("order_id=".$order_id);
        if(empty($order_id) || $order_id == "" || !$order_info){
            $this->json_result(0,"没有这个订单！");
            return;
        }
        if($order_info['status'] ==ORDER_SHIPPED ){
            $this->json_result(0,"该订单已发货！");
            return;
        }elseif($order_info['status'] ==ORDER_FINISHED ){
            $this->json_result(0,"已完成订单不能发货！");
            return;
        }elseif($order_info['status'] ==ORDER_CANCELED ){
            $this->json_result(0,"已取消订单不能发货！");
            return;
        }

        $edit_data = "";
        //发货：无需物流（针对虚拟商品、服务性质的商品、本地生活中上门取件的商品）,更改订单状态即可,改成已发货)
        $sendMethod = $_POST['sendMethod'] ? $_POST['sendMethod'] :$_GET['sendMethod'];
        if($sendMethod=='wuxuwuliu'){
            $edit_data = array( 'status' => ORDER_SHIPPED,"ship_time"=>gmtime());
            //更改失败
            if(!$model_order->edit(intval($order_info['order_id']), $edit_data)){
                $this->json_result(0,"发货失败，请稍后操作！");
                return;
            }else{
                #TODO 发邮件通知
                /* 记录订单操作日志 */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'  => $order_id,
                    'operator'  => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info['status']),
                    'changed_status' => order_status(ORDER_SHIPPED),
                    'log_time'  => gmtime(),
                ));

                /* 发送给买家订单已发货通知 */
                $model_member =& m('member');
                $buyer_info   = $model_member->get($order_info['buyer_id']);
                $order_info['invoice_no'] = $edit_data['invoice_no'];
                $mail = get_mail('tobuyer_shipped_notify', array('order' => $order_info));
                $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
                $this->json_result(1,"发货成功！");
            }
        //快递发货
        }elseif($sendMethod=='kuaidi'){
            if (!$_POST['wuliudanhao']){
                $this->json_result(0,"物流单号不能为空！");
                return;
            }
            $wuliudanhao = $_POST['kdgs'].'|'.$_POST['wuliudanhao'];
            $edit_data = array('status' => ORDER_SHIPPED, 'invoice_no' => $wuliudanhao);
            if (empty($order_info['invoice_no'])){
                //发货时间
                $edit_data['ship_time'] = gmtime();
            }
            $model_order->edit(intval($order_id), $edit_data);
            if ($model_order->has_error()){
                $this->json_result(0,"发货失败，请稍后操作！");
                return;
            }else{
                #TODO 发邮件通知
                /* 记录订单操作日志 */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'  => $order_id,
                    'operator'  => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info['status']),
                    'changed_status' => order_status(ORDER_SHIPPED),
                    'log_time'  => gmtime(),
                ));

                /* 发送给买家订单已发货通知 */
                $model_member =& m('member');
                $buyer_info   = $model_member->get($order_info['buyer_id']);
                $order_info['invoice_no'] = $edit_data['invoice_no'];
                $mail = get_mail('tobuyer_shipped_notify', array('order' => $order_info));
                $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
                $this->json_result(1,"发货成功！");
            }
        }else{
            $this->json_result(0,"发货方式参数丢失，发货失败！");
        }


    }

    /**
     * 买家确认收货
     */
    function confirmReceive(){
        $this->isLogin();
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : intval($_POST['order_id']);
        if (!$order_id){
            $this->json_result(0,"没有这个订单！");
            exit;
        }
        $model_order    =&  m('order');
        /* 只有已发货的订单可以确认 */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status=" . ORDER_SHIPPED);
        if (empty($order_info)){
            $this->json_result(0,"只有已发货的订单可以确认订单！");
            exit;
        }
        if (!IS_POST){
            $this->json_result(0,"非法操作!");
        }else{
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));
            if ($model_order->has_error()){
                $this->pop_warning($model_order->get_error());
                exit;
            }
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark'    => Lang::get('buyer_confirm'),
                'log_time'  => gmtime(),
            ));

            /* 发送给卖家买家确认收货邮件，交易完成 */
            $model_member =& m('member');
            $seller_info   = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_finish_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array('evaluate'),
            );

            /* 更新累计销售件数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $model_ordergoods =& m('ordergoods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
            foreach ($order_goods as $goods)
            {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
            }

            $model_order->commit_transaction();

            //忽略java那边返回
            $params["type"] = "confirm_order";      //confirmOrder   cancelOrder
            $params["userId"] = $this->visitor->get('user_id');
            $params["orderSn"] =$order_info["order_sn"];
            $params["queryType"] = 1;
            $params["tId"] ="";
            $params["userName"] ="";
            $params["channelCode"] ="";
            $params["channelName"] ="";
            $params["channelCard"] ="";
            $params["jine"] ="";
            $params["regionId"] ="";
            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
            $Client = new HttpClient(JAVA_LOCATION);
            $pageContents = HttpClient::quickPost($url, $params);

            $this->json_result(1,"确认收货订单成功！");

        }
    }

    //余额支付页面
    function payPage(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $member_finance = $this->getMemberFinance($userId);
        $user = $user_mod->get('user_id='.$userId);
        $this->assign("user",$user);
        $this->assign("member_finance",$member_finance);


        $orderAll = $this->getOrderByOrderSn($_GET['orderSn']);
        $this->assign("payMethodName",$_GET['payMethodName']);
        $this->assign("payMethodCode",$_GET['payMethodCode']);
        $this->assign("code",$_GET['code']);
        $this->assign("orderAll",$orderAll);
        $this->display("member_order.payPage.html");
    }

    //余额支付时,余额不足，用支付宝等其他网银支付
    function partPayPage(){
        $this->isLogin();
        $orderSnArray = explode(",",$_GET['orderSn']);
        if(count($orderSnArray) == 0){
            exit("使用余额支付后，返回的订单参数错误!");
        }
        $ordersAll = $this->getOrderByOrderSn($_GET['orderSn']);
        $this->assign("ordersAll",$ordersAll);
        $this->assign("useYue",$_GET['useYue']);
        $this->assign("orderSn",$_GET['orderSn']);
        $this->assign("useKoushuiYue",$_GET['useKoushuiYue']);
        $this->assign("code",$_GET['code']);
        $this->display("member_order.partPayPage.html");
    }
    //支付结果
    function payResultPage(){
        $this->isLogin();
        $orderSnArray = explode(",",$_GET['orderSn']);
        if(count($orderSnArray) == 0){
            exit("使用余额支付后，返回的订单参数错误!");
        }
        $ordersAll = $this->getOrderByOrderSn($_GET['orderSn']);
        $this->assign("ordersAll",$ordersAll);
        $this->assign("useYue",$_GET['useYue']);
        $this->assign("orderSn",$_GET['orderSn']);
        $this->assign("useKoushuiYue",$_GET['useKoushuiYue']);
        $this->assign("code",$_GET['code']);
        $this->display("member_order.payResultPage.html");
    }
    function payMethod(){
        $this->assign("orderSn",$_GET['orderSn']);
        $this->display("member_order.payMethod.html");
    }


    //订单支付
    function pay(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $this->assign("user",$user);
        if( !$user || $user['user_id'] <= 0 || !$_GET['userId'] || intval($_GET['userId']) <= 0 || $user['user_id'] != $_GET['userId']){
            header("Content-Type:text/html;charset=utf-8");
            exit("非法访问!");
            return;
        }
        $orderSnArray = explode(',',$_GET['orderSn']);
        if(!$_GET['orderSn'] || count($orderSnArray) == 0 ){
            header("Content-Type:text/html;charset=utf-8");
            exit("订单编号错误");
            return;
        }
        $orderSnAll = $_GET['orderSn'];
        $useYue = $_GET['useYue'];
        $useKoushuiYue = $_GET['useKoushuiYue'];
        $data = array("orderSn"=>$orderSnAll,"useYue"=>$useYue,"useKoushuiYue"=>$useKoushuiYue,"userId"=>$userId);
        $url ="";
        if($_GET['is_enough_pay'] && $_GET['is_enough_pay'] == "true"){
            $url = SITE_URL."/dajike-account/mobileYue.action";
        }elseif($_GET['is_enough_pay'] == "false"){
            $url = SITE_URL."/dajike-account/mobilePartYue.action";
        }else{
            header("Content-Type:text/html;charset=utf-8");
            exit("参数错误");
            return;
        }
        //向JAVA端发送支付请求
        $resultJson = $this->post($url,$data);
        $resultArray = json_decode($resultJson,true);
        if(!$resultArray || $resultArray == null|| count($resultArray) == 0){
            header("Content-Type:text/html;charset=utf-8");
            exit("网络繁忙，请稍后再试!");
            return;
        }
        //支付成功
        if($resultArray['code'] == true){
            //一次性支付完
            if($_GET['is_enough_pay'] == "true"){
                header("Location:{$site_url}/weixin/index.php?app=member_order&act=payResultPage&orderSn="
                    .$resultArray['orderSn']."&code=".$resultArray['code']."&useYue=".$resultArray['useYue']."&useKoushuiYue=".$resultArray['useKoushuiYue']);
            //支付一部分
            }else{
                header("Location:{$site_url}/weixin/index.php?app=member_order&act=payPage&orderSn="
                    .$orderSnAll."&code=".$resultArray['code']."&useYue=".$useYue."&useKoushuiYue=".$useKoushuiYue.
                    "&is_enough_pay=".$_GET['is_enough_pay']."&payMethodName=".$_GET['payMethodName']."&payMethodCode=".$_GET['payMethodCode']);
            }
        //支付失败
        }else{
            header("Location:{$site_url}/weixin/index.php?app=member_order&act=payResultPage&orderSn="
                .$resultArray['orderSn']."&code=".$resultArray['code']."&useYue=".$resultArray['useYue']."&useKoushuiYue=".$resultArray['useKoushuiYue']);
        }
    }
    //发送post请求
    function post($url, $jsonData = ''){
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//允许接收post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($jsonData));//http_build_query减少发送时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);//连接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//执行超时时间
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        return $result;
    }

    //余额支付时，验证支付密码
    function validatePayPassword(){
        if( !IS_POST ){
            $this->json_result(0,"非法访问!");
            exit;
        }else{
            $userId = $_POST['userId'];
            if( !$userId  || $userId == null || $userId=="" || $userId <= 0 ){
                $this->json_result(0,"缺少参数！");
                exit;
            }
            $user_mod = &m("member");
            $user = $user_mod->get('user_id='.$userId);
            if( !$user || $user['user_id'] <= 0){
                $this->json_result(0,"没有这个用户!");
                exit;
            }
            if( !$user['password2'] || $user['password2'] == null || $user['password2'] == "" ){
                $this->json_result(0,"还未开通支付密码!");
                exit;
            }
            $password2 = $_POST['password2'];
            if( !$password2  || $password2 == null || $password2==""  ){
                $this->json_result(0,"支付密码不能空！");
                exit;
            }
            if( $user['password2'] != md5( $password2 ) ){
                $this->json_result(0,"支付密码错误！");
                exit;
            }
            if( $user['password2'] == md5( $password2 )){
                $this->json_result(1,"支付密码正确！");
            }
        }
    }

    /**
     * 根据订单编号获取订单信息
     * @param $orderSn 订单编号（多个订单编号以英文输入法状态下的逗号隔开）
     * @return array   返回订单信息的数组，如果没有，则返回一个空数组
     *                  array()里面的元素：orders,orderAmount,orderAlreadyPay,orderStillNeedToPay,orderSnAll
     * @author liz
     * @description 此方法专用于（手机版）订单支付业务
     * @date 2014-03-07
     */
    function getOrderByOrderSn($orderSn){
        $orders = array();
        if(!$orderSn || $orderSn== null || $orderSn==""|| !is_string($orderSn)){
            return $orders;
        }
        $orderSnArray = explode(",",$orderSn);
        if(!$orderSnArray || $orderSnArray==null || count($orderSnArray) ==0 ){
            return $orders;
        }
        $orderAmount=0;
        $orderAlreadyPay = 0;
        $orderSnAll = "";
        $order_mod = &m("order");
        foreach($orderSnArray as $order_sn){
            if(!$order_sn || $order_sn==null || $order_sn==""){
                continue;
            }
            $order = $order_mod->get("order_sn=".$order_sn);
            if( $order && $order['order_id'] > 0 ){
                array_push($orders,$order);
                $orderAmount += floatval($order['order_amount']);
                $orderAlreadyPay += ( floatval($order['yue']) + floatval($order['koushui_yue']) );
                if( $orderSnAll=="" ){
                    $orderSnAll = $order['order_sn'];
                }else{
                    $orderSnAll = $orderSnAll.",".$order['order_sn'];
                }
            }
        }
        $orderStillNeedToPay =  $orderAmount - $orderAlreadyPay ;
        $orderStillNeedToPay = sprintf("%.2f", $orderStillNeedToPay);//保留两位小数并四舍五入
        $orderStillNeedToPay = floatval($orderStillNeedToPay);

        $ordersAll = array(
            "orders"                =>$orders,
            "orderAmount"          =>$orderAmount,
            "orderAlreadyPay"      =>$orderAlreadyPay,
            "orderStillNeedToPay" =>$orderStillNeedToPay,
            "orderSnAll"           =>$orderSnAll
        );
        return $ordersAll;
    }

}