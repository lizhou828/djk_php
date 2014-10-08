<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Seller_orderApp extends StoreadminbaseApp
{

    function __construct(){
        $this->Seller_orderApp();
    }

    function Seller_orderApp()
    {
        parent::__construct();
    }

    function index()
    {
        $member_mod =&m('member');
        $user = $member_mod->get_info($this->visitor->get('user_id'));
        $this->assign("user",$user);

       $state = $this->visitor->get('state');
       if($_GET["app"]!="service" && $state==0)
        {
            $this->show_warning('check_store_error');

            exit;
        }
        /* 获取订单列表 */
        $this->_get_orders();
        if ($_GET['step']==2) {
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                LANG::get('benqu_orders'), 'index.php?app=service&act=bq_orders&step=2&p=service',
                LANG::get('order_list'));
            $this->_curitem('benqu_orders');
        } else{
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('order_list'));
        }

        /* 当前用户中心菜单 */
        $type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_orders';
        $this->_curitem('order_manage');

        if($_GET["app"]=="service")
        {
            $this->_curitem(empty($_GET["act"])?'service':$_GET["act"]);

            $fwzx_info = $this->visitor->get();
            $fwzxId=$fwzx_info['user_id'];

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

            $service_region_id = $fwzx_info["region_id"]?$fwzx_info["region_id"]:-1;

            if ($_GET['act']=="benqu_orders") {
                $stores = $store_mod->find(array(
                    'conditions' => " s.region_id=$service_region_id  and s.dropState=1 and state=1".$conditions,
                    'join'  => 'belongs_to_user',
                    'fields'=> 'this.*,member.user_name'
                ));
            }else {
                $stores = $store_mod->find(array(
                    'conditions' => " (fwzx=$fwzxId $query_conditions) and tuoguan=1 and s.dropState=1 and state=1".$conditions,
                    'join'  => 'belongs_to_user',
                    'fields'=> 'this.*,member.user_name'
                ));
            }

            $this->assign('stores', $stores);
           // $this->print_r($stores);
        }
        if($_GET["shouhou"]==1){
            $this->_curmenu("shouhou_order");
        }else{
            $this->_curmenu($type);
        }

        if($_GET['p']=="daifu"){
            $this->assign("p",$_GET['p']);
            $this->_curitem('daifu_order');
        }
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_manage'));
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
        $this->assign('TO_PAY_URL', TO_PAY_URL);
        /* 显示订单列表 */
        $this->display('seller_order.index.html');
    }

    /**
     *    查看订单详情
     *
     *    @author    Garbin
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order =& m('order');

        $datas=null;
        if($_GET["app"]=="service" || $_GET["viewType"]=="tuiguang")
        {
            $datas=array(
                'conditions'    => "order_alias.order_id={$order_id}",
                'join'          => 'has_orderextm',
            );
        }else
        {
            $datas=array(
                'conditions'    => "order_alias.order_id={$order_id} AND (seller_id=" . $this->visitor->get('manage_store')." or ( is_daifu=1 and daifu_user_id=".$this->visitor->get('manage_store').") )",
                'join'          => 'has_orderextm',
            );
        }


        $order_info  = $model_order->findAll($datas);
        $order_info = current($order_info);
        $order_goods_model = &m ("ordergoods");
        $order_info['jifen'] =$order_goods_model->getOne("SELECT SUM(jifen) FROM ".DB_PREFIX."order_goods WHERE order_id=$order_id");
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }
        $invoice_no =$order_info['invoice_no'];
        if($invoice_no && strpos($invoice_no,"|")) {
            $invoice_nos = explode("|",$invoice_no);
            $order_info['invoice_no'] = $invoice_nos[1];
            $this->assign('kd',$invoice_nos[0]);
        }
        /* 团购信息 */
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod = &m('groupbuy');
            $group = $groupbuy_mod->get(array(
                'join' => 'be_join',
                'conditions' => 'order_id=' . $order_id,
                'fields' => 'gb.group_id',
            ));
            $this->assign('group_id',$group['group_id']);
        }

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('view_order'));

        /* 当前用户中心菜单 */
        $this->_curitem('order_manage');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('detail'));
        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $spec_ids = array();

        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
            $spec_ids[] = $goods['spec_id'];

        }

        /* 查出最新的相应的货号 */
        $model_spec =& m('goodsspec');
        $spec_info = $model_spec->find(array(
            'conditions'    => $spec_ids,
            'fields'        => 'sku',
        ));

        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            $order_detail['data']['goods_list'][$key]['sku'] = $spec_info[$goods['spec_id']]['sku'];
        }

        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->assign('paymentType',$order_detail['data']['payment_info']['payment_id']);
        $this->display('seller_order.view.html');
    }
    /**
     *    收到货款
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function received_pay()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_PENDING);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            if($_GET["app"]=="service")
            {
                $this->assign('orderType', 1);
            }
            $this->display('seller_order.received_pay.html');
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->edit(intval($order_id), array('status' => ORDER_ACCEPTED, 'pay_time' => gmtime()));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            #TODO 发邮件通知
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件，提示等待安排发货 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_offline_pay_success_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_accepted'),
                'actions'   => array(
                    'cancel',
                    'shipped'
                ), //可以取消可以发货
            );

            $this->pop_warning('ok','seller_order_received_pay');
        }

    }

    /**
     *    货到付款的订单的确认操作
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function confirm_order()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_SUBMITTED);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            if($_GET["app"]=="service")
            {
                $this->assign('orderType', 1);
            }
            $this->display('seller_order.confirm.html');
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->edit($order_id, array('status' => ORDER_ACCEPTED));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件，订单已确认，等待安排发货 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_confirm_cod_order_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_accepted'),
                'actions'   => array(
                    'cancel',
                    'shipped'
                ), //可以取消可以发货
            );

            $this->pop_warning('ok','seller_order_confirm_order');;
        }
    }

    /**
     *    调整费用
     *
     *    @author    Garbin
     *    @return    void
     */
    function adjust_fee()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_PENDING));
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        $model_orderextm =& m('orderextm');
        $shipping_info   = $model_orderextm->get($order_id);
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->assign('shipping', $shipping_info);
            if($_GET["app"]=="service")
            {
                $this->assign('orderType', 1);
            }
            $this->display('seller_order.adjust_fee.html');
        }
        else
        {
            /* 配送费用 */
            $shipping_fee = isset($_POST['shipping_fee']) ? abs(floatval($_POST['shipping_fee'])) : 0;
            /* 折扣金额 */

            $goods_amount = $order_info["goods_amount"] ;
            /* 订单实际总金额 */
            $order_amount = round($goods_amount + $shipping_fee, 2);
            if ($order_amount <= 0)
            {
                /* 若商品总价＋配送费用扣队折扣小于等于0，则不是一个有效的数据 */
                $this->pop_warning('invalid_fee');

                return;
            }
            $data = array(
                'goods_amount'  => $goods_amount,    //修改商品总价
                'order_amount'  => $order_amount,     //修改订单实际总金额
                'pay_alter' => 1    //支付变更
            );

            if ($shipping_fee != $shipping_info['shipping_fee'])
            {
                /* 若运费有变，则修改运费 */

                $model_extm =& m('orderextm');
                $model_extm->edit($order_id, array('shipping_fee' => $shipping_fee));
            }
            $model_order->edit($order_id, $data);

            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status($order_info['status']),
                'remark'    => Lang::get('adjust_fee'),
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件通知，订单金额已改变，等待付款 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_adjust_fee_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'order_amount'  => price_format($order_amount),
            );

            $this->pop_warning('ok','seller_order_adjust_fee');
        }
    }

    /**
     *    待发货的订单发货
     *
     *    @author    Garbin
     *    @return    void
     */
    function shipped()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_ACCEPTED, ORDER_SHIPPED));
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        if (!IS_POST)
        {
            /* 显示发货表单 */
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('kdgs', Conf::get('kdgs'));
            if ($order_info['invoice_no'] && strpos($order_info['invoice_no'],"|")) {
                  $invoice_nos = explode("|",$order_info['invoice_no']);
//                $this->pr($invoice_nos);exit;
                  $order_info['invoice_no'] = $invoice_nos[1];
                  $this->assign('kd',$invoice_nos[0]);
            }


            $this->assign('order', $order_info);
//            $invoice_no =
            if($_GET["app"]=="service")
            {
                $this->assign('orderType', 1);
            }

            $this->display('seller_order.shipped.html');
        }
        else
        {
            if (!$_POST['invoice_no'])
            {
                $this->pop_warning('invoice_no_empty');

                return;
            }
            $invoice_no = $_POST['kdgs'].'|'.$_POST['invoice_no'];

            $edit_data = array('status' => ORDER_SHIPPED, 'invoice_no' => $invoice_no);
            $is_edit = true;
            if (empty($order_info['invoice_no']))
            {
                /* 不是修改发货单号 */
                $edit_data['ship_time'] = gmtime();
                $is_edit = false;
            }
            $model_order->edit(intval($order_id), $edit_data);
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            #TODO 发邮件通知
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_SHIPPED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));


            /* 发送给买家订单已发货通知 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $order_info['invoice_no'] = $edit_data['invoice_no'];
            $mail = get_mail('tobuyer_shipped_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_shipped'),
                'actions'   => array(
                    'cancel',
                    'edit_invoice_no'
                ), //可以取消可以发货
            );
            if ($order_info['payment_code'] == 'cod')
            {
                $new_data['actions'][] = 'finish';
            }

            $this->pop_warning('ok','seller_order_shipped');
        }
    }


    //去掉积分
    function cancel_jifen($order_id){
        $model_member =& m('member');
        $model_order    =&  m('order');
        if($order_id!=""){

            $user = $this->visitor->get();

            $order = null;

            if($_GET["app"] == "service" ){
                $order=$model_order->get("order_id=$order_id");
            }else{
                $order=$model_order->get("order_id=$order_id AND seller_id=" . $this->visitor->get('user_id'));
            }

            if(!$order || $order["status"] == 0 ){
                //$this->show_warning('订单不存在！');
                $this->pop_warning('订单不存在!');
                exit;
            }
            $buyer_id=$order['buyer_id'];
            $order_goods_model =& m('ordergoods');

            $jifen = $order_goods_model->getOne("SELECT SUM(jifen) FROM ".DB_PREFIX."order_goods WHERE order_id=$order_id");
            if($jifen>0){
                $rs = $model_member->db_query("update ".DB_PREFIX."member set jifen=jifen+$jifen where user_id=$buyer_id");  //修改积分
                if($rs>0){
                    $buyer=$model_member->get_info($buyer_id);
                    ini_set('date.timezone','Asia/Shanghai');
                    $data=array("user_id"=>$buyer_id,
                        "jifen"=>$jifen,
                        "beizhu"=>"取消订单退回积分到买家账号，订单号:".$order["order_sn"],
                        "create_time"=>date('Y-m-d H:i:s',time()),
                        "type"=>3
                    );
                    $jifenlog_mod =& m('jifenlog');
                    $jifenlog_mod->add($data);              //记录积分log
                }
            }
        }

    }

    /**
     *    取消订单
     *
     *    @author    Garbin
     *    @return    void
     */
    function cancel_order()
    {

        echo "<meta charset='utf-8' />";
        /* 取消的和完成的订单不能再取消 */
        //list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_PENDING, ORDER_ACCEPTED, ORDER_SHIPPED));
        $order_id = $_GET['order_id'];
        if ( ! $_GET['order_id'] ||  $_GET['order_id'] ==null || $_GET['order_id'] == " "){
            echo "没有这样的订单";
            exit;
        }
        $status = array(ORDER_SUBMITTED, ORDER_PENDING, ORDER_ACCEPTED, ORDER_SHIPPED);
        $order_ids = explode(',', $order_id);
        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* 只有已发货的货到付款订单可以收货 */
        $conditions="";
        if($_GET["app"]=="service")
        {
            $conditions="order_id" . db_create_in($order_ids) . " AND status " . db_create_in($status) . $ext;
        }else
        {
            $conditions="order_id" . db_create_in($order_ids) . " AND (seller_id=" . $this->visitor->get('manage_store') ." or ( is_daifu=1 and daifu_user_id= seller_id )". " ) AND status " . db_create_in($status) . $ext;
        }

        $order_info     = $model_order->find(array(
            'conditions'    => $conditions,
        ));
        $ids = array_keys($order_info);
        if (!$order_info)
        {
            echo "没有这样的订单";
            exit;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=utf-8');
            $this->assign('orders', $order_info);
            //$this->assign('ordersType', $_GET["ordersType"]);
            if($_GET["app"]=="service")
            {
                $this->assign('orderType', 1);
            }
            $this->assign('order_id', count($ids) == 1 ? current($ids) : implode(',', $ids));
            $this->display('seller_order.cancel.html');
        }
        else
        {
            $model_order    =&  m('order');

            $order_sn = "";
            foreach ($ids as $val){
                $id = intval($val);
                $order_info=$model_order->get("order_id=$id and status !=0 ");
                if(!$order_info){
                    echo "<script type=\"text/javascript\">window.parent.js_fail('订单号不存在');</script>";
                    exit;
                }
                if($order_sn == ""){
                    $order_sn = $order_info["order_sn"];
                }else{
                    $order_sn .= ",".$order_info["order_sn"];
                }
            }


            $flag = 0;  //是否调用接口      默认0不调用

            foreach ($ids as $val)
            {
                $id = intval($val);
                $model_member =& m('member');
                $model_yuelog =& m('yuelog');
                //类型为取消订单并且退回余额
                $order_info=$model_order->get_info($id);

                if($flag!=1 &&($order_info["status"]!=11 ||  $order_info["koushui_yue"] > 0 || $order_info["yue"] >0 || $order_info["jifen"]>0)){
                    $flag =1;
                }
                
                $model_order->edit($id, array('status' => ORDER_CANCELED));
                if ($model_order->has_error()){
                    echo "<script type=\"text/javascript\">window.parent.js_fail('".$model_order->has_error()."');</script>";
                    continue;
                }

                /* 加回订单商品库存 */
                $model_order->change_stock('+', $id);
                $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
                /* 记录订单操作日志 */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'  => $id,
                    'operator'  => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info['status']),
                    'changed_status' => order_status(ORDER_CANCELED),
                    'remark'    => $cancel_reason,
                    'log_time'  => gmtime(),
                ));

                /* 发送给买家订单取消通知 */
                $model_member =& m('member');
                $buyer_info   = $model_member->get($order_info[$id]['buyer_id']);
                $mail = get_mail('tobuyer_cancel_order_notify', array('order' => $order_info[$id], 'reason' => $_POST['remark']));
                $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

                $new_data = array(
                    'status'    => Lang::get('order_canceled'),
                    'actions'   => array(), //取消订单后就不能做任何操作了
                );

            }

            $model_order->commit_transaction();


            if($flag!=1){
                $this->pop_warning('ok', 'seller_order_cancel_order');
                exit;
            }

            //直接调用java那边，忽略返回
            $params["type"] = "cancel_order";      //confirmOrder   cancelOrder
            $user = $this->visitor->get();
            if($_GET["app"]=="service"){
                if($user["member_type"] == 4 ){
                    $params["userId"] = $this->visitor->get('user_id');
                    $params["regionId"] =0;
                }else{
                    $params["userId"] = $this->visitor->get('user_id');
                    $params["regionId"] =$user["region_id"];
                }
            }else{
                $params["userId"] = $this->visitor->get('user_id');
                $params["regionId"] =0;
            }
            $queryType = 2;
            if($user["member_type"] == 2 || $user["member_type"] ==3 ){
                $queryType = 3;
            }else if($user["member_type"] == 4 ){
                $queryType = 4;
            }
            $params["queryType"] = $queryType;
            $params["orderSn"] =$order_sn;
            $params["tId"] ="";
            $params["userName"] ="";
            $params["channelCode"] ="";
            $params["channelName"] ="";
            $params["channelCard"] ="";
            $params["jine"] ="";

            $Client = new HttpClient(JAVA_LOCATION);
            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
            $pageContents = HttpClient::quickPost($url, $params);
            /*$data = json_decode($pageContents);
            if(!$data || $data->code != 200){
                echo $pageContents;
                echo "<script type=\"text/javascript\">window.parent.js_fail('服务器繁忙，请稍后重试【".$data->code."】！');</script>";
                exit;
            }*/

            $this->pop_warning('ok', 'seller_order_cancel_order');

        }

    }

    /**
     *    完成交易(货到付款的订单)
     *
     *    @author    Garbin
     *    @return    void
     */
    function finished()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_SHIPPED, 'payment_code=\'cod\'');
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            /* 当前用户中心菜单 */
            $this->_curitem('seller_order');
            /* 当前所处子菜单 */
            $this->_curmenu('finished');
            $this->assign('_curmenu','finished');
            $this->assign('order', $order_info);
            $this->display('seller_order.finished.html');
        }
        else
        {
            $now = gmtime();
            $model_order    =&  m('order');
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'pay_time' => $now, 'finished_time' => $now));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 更新累计销售件数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $model_ordergoods =& m('ordergoods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
            foreach ($order_goods as $goods)
            {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
            }


            /* 发送给买家交易完成通知，提示评论 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_cod_order_finish_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array(), //完成订单后就不能做任何操作了
            );

            $this->pop_warning('ok');
        }

    }

    /**
     *    获取有效的订单信息
     *
     *    @author    Garbin
     *    @param     array $status
     *    @param     string $ext
     *    @return    array
     */
    function _get_valid_order_info($status, $ext = '')
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {

            return array();
        }
        if (!is_array($status))
        {
            $status = array($status);
        }

        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* 只有已发货的货到付款订单可以收货 */

        $conditions="order_id={$order_id} AND seller_id=" . $this->visitor->get('manage_store') . " AND status " . db_create_in($status) . $ext;
        if($_GET["app"]=="service")
        {
            $conditions="order_id={$order_id}  AND status " . db_create_in($status) . $ext;
        }

        $order_info     = $model_order->get(array(
            'conditions'    => $conditions,
        ));
        if (empty($order_info))
        {

            return array();
        }

        return array($order_id, $order_info);
    }
    /**
     *    获取订单列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders()
    {

        $page = $this->_get_page();
        $model_order =& m('order');

        !$_GET['type'] && $_GET['type'] = 'all_orders';

        $conditions = '';

        // 团购订单
        if (!empty($_GET['group_id']) && intval($_GET['group_id']) > 0)
        {
            $groupbuy_mod = &m('groupbuy');
            $order_ids = $groupbuy_mod->get_order_ids(intval($_GET['group_id']));
            $order_ids && $conditions .= ' AND order_alias.order_id' . db_create_in($order_ids);
        }

        $conditions .= $this->_get_query_conditions(array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
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


          if($_GET["shouhou"])
           {
               $conditions.=" and (select count(1) from ".DB_PREFIX."order_shouhou shouhou where order_alias.order_id=shouhou.order_id and shouhou.status in (0,1) )>0";
           }
          if( $_GET['order_type']){
              $conditions.=" and type='".$_GET['order_type']."'";
          }

        /* 查找订单 */

        // **********************************
        $v=$this->visitor->get();


        $seller_id=($this->visitor->get('manage_store')==null || $this->visitor->get('manage_store')=="")?$v["user_id"]:$this->visitor->get('manage_store');
        $data_=array(
            'conditions'    => "seller_id=" . $seller_id . "{$conditions}",
            'fields'        => "this.*,
                            (order_amount-yue-koushui_yue) as yue_jine,(yue+koushui_yue) as zhifu ",

            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
            'has_ordergoods',       //取出商品
            ),
        );
        if($_GET['p']=='daifu'){
            $queryCondition = " is_daifu=1 and daifu_user_id=".$v['user_id'];
            if( $_GET['buyer_name'] && $_GET['buyer_name'] != ' '){
                $queryCondition .= " AND buyer_name ='".$_GET['buyer_name']."'";
            }
            if($_GET['add_time_from'] && $_GET['add_time_from'] != " "){
                $queryCondition .= " AND add_time>=".strtotime($_GET['add_time_from'])."";
            }
            if($_GET['add_time_to'] && $_GET['add_time_to'] != " "){
                $queryCondition .= " AND add_time <=".strtotime($_GET['add_time_to'])."";
            }
            if($_GET['order_sn'] && $_GET['order_sn'] !=" "){
                $queryCondition .= " AND order_sn ='".$_GET['order_sn']."'";
            }
            $data_['conditions']= $queryCondition;
        }

        if($_GET["app"]=="service")
        {
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

            $seller_type = $_GET["seller_type"];
            if ($seller_type != null && $seller_type != "" && $seller_type !=4) {
                $query.=" and s.seller_type=$seller_type ";
            }

            $user_ids=$member_mod->find(array(
                "conditions"=>"member_type='03' and region_id=".$service_info["region_id"],
                "fields"    =>"user_id"
            ));
            $query_conditions="";
            if($service_info["member_type"]=="04") {
                $conditions.=" and type != 'pos' and type != 'two'"; //采购账号过滤扫描下单和pos机刷卡订单
            }
            if($service_info['user_name'] != 'djk11001') {
                if(count($user_ids)>0 && $service_info["member_type"]=="02"){
                    foreach($user_ids as $k=>$v){
                        $query_conditions.=" or s.fwzx=".$v["user_id"]; //获得子账号下面的商家
                    }
                }
                $service_region_id = $user["region_id"]?$user["region_id"]:-1;
                //服务中心查询托管订单
                $last_conditions = "EXISTS (select 1 from ".DB_PREFIX."store s where (s.fwzx=$visitorId $query_conditions) and s.dropState=1 and s.tuoguan=1 $query and order_alias.seller_id = s.store_id  ) {$conditions}";
                //服务中心查询本区订单
//                echo($last_conditions);
                if($_GET["act"] == "benqu_orders"){
                    $last_conditions = "EXISTS (select 1 from ".DB_PREFIX."store s where  s.dropState=1 and s.tuoguan=0 $query and order_alias.seller_id = s.store_id  ) {$conditions}";
                }
            } else {
                $last_conditions = " type != 'pos' and type != 'two' {$conditions}";
            }

            $data_=array(
                'conditions'    => $last_conditions,
                'count'         => true,
                'fields'        => "this.*,(order_amount-yue-koushui_yue) as yue_jine, (yue+koushui_yue) as zhifu ",
                'limit'         => $page['limit'],
                'order'         => 'add_time DESC',
                'include'       =>  array(
                    'has_ordergoods',       //取出商品
                ),
            );
        }

        $orders = $model_order->findAll($data_);

        $orderextm_mod =& m('orderextm');

        foreach ($orders as $key1 => $order)
        {
            $orderextm = $orderextm_mod->get($key1);
            if($orderextm["order_id"] != "" && $orderextm["order_id"] >0 ){
                $orders[$key1]["consignee"] = $orderextm["consignee"];
                $orders[$key1]["phone_mob"] = $orderextm["phone_mob"];
                $orders[$key1]["phone_tel"] = $orderextm["phone_tel"];
            }

            if ($order['order_goods']) {
//                $this->print_r($order['order_goods']);
//            exit;
                foreach ($order['order_goods'] as $key2 => $goods)
                {
                    empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
                    $shohou_mod = & m('ordershouhou');
                    $ordershouhou=$shohou_mod->get(array(
                        "conditions"=>"goods_id=".$goods["goods_id"]." and order_id=".$order["order_id"]." and status not in (-1,2)"
                    ));
                    if($ordershouhou["id"]!=""){
                        $orders[$key1]['order_goods'][$key2]["shouhou_id"]=$ordershouhou["id"];
                        $orders[$key1]['order_goods'][$key2]["status"]=$ordershouhou["status"];
  //                    if($ordershouhou["status"]==0 || $ordershouhou["status"]==1)
                        if($ordershouhou["status"]==0)
                        {
                            $orders[$key1]['shouhouFlg']=1;
                        }
                    }
                }
            }
        }

        $page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('types', array('all' => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
    }

    //单个订单详情
    function order_goods(){
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order =& m('order');

        $conditions = ($_GET["app"]!="service")? " AND seller_id=" . $this->visitor->get('user_id'):"";
        $order_info = $model_order->get(array(
            'fields'        => "*, order.add_time as order_add_time",
            'conditions'    => "order_id={$order_id}".$conditions,
            'join'          => 'belongs_to_store',
        ));

        if (!$order_info) {
            $this->show_warning('no_such_order');
            return;
        }

        /* 团购信息 */
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod = &m('groupbuy');
            $group = $groupbuy_mod->get(array(
                'join' => 'be_join',
                'conditions' => 'order_id=' . $order_id,
                'fields' => 'gb.group_id',
            ));
            $this->assign('group_id',$group['group_id']);
        }

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            LANG::get('my_order'), 'index.php?app=buyer_order',
            LANG::get('view_order'));

        /* 当前用户中心菜单 */
        $this->_curitem('order_manage');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }

        $this->assign('order', $order_info);


        if(count($order_detail['data']['goods_list']) >0 ){
            foreach($order_detail['data']['goods_list'] as $k => $goods ){
                $order_id = $goods["order_id"];

                $shohou_mod = & m('ordershouhou');
                $ordershouhou=$shohou_mod->get(array(
                    "conditions"=>"goods_id=".$goods["goods_id"]." and order_id=".$goods["order_id"]." and status not in (-1,2)"
                ));
                if($ordershouhou["id"]!=""){
                    $order_detail['data']['goods_list'][$key]["shouhou_id"]=$ordershouhou["id"];
                    $order_detail['data']['goods_list'][$key]["status"]=$ordershouhou["status"];
//                    if($ordershouhou["status"]==0 || $ordershouhou["status"]==1)
                    if($ordershouhou["status"]==0)
                    {
                        $order_detail['data']['goods_list'][$key]['shouhouFlg']=1;
                    }
                }

            }
        }
        $this->assign($order_detail['data']);
        //$this->pr($order_detail['data']);
        $this->display('buyer_order.goodsview.html');
    }

    function shouhou(){

        /* 当前用户中心菜单 */
        $this->_curitem('order_manage');

        $model_order =& m('order');
        $shouhou_mod = & m('ordershouhou');

        if(!$_POST){
            $orderId=$_GET["order_id"];
            $goodsId=$_GET["goods_id"];

            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

            if($orderId=="" || $goodsId==""){
                $this->show_warning('请选择订单商品');
                exit;
            }

            $userInfo = $this->visitor->info;

            $ordergoods_mod =& m('ordergoods');
            $ordergoods=$ordergoods_mod->get(array(
                "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId ",
                'fields' => 'this.*,(select seller_name from '.DB_PREFIX.'order where order_id=ordergoods.order_id) as seller_name,
                        (select seller_id from '.DB_PREFIX.'order where order_id=ordergoods.order_id) as seller_id',
            ));

            if(!$ordergoods){
                $this->show_warning('订单号错误!');
                exit;
            }

            $this->assign('ordergoods', $ordergoods);

            if($_GET["shouhou_id"])
            {
                $shouhou=$shouhou_mod->get(array(
                    "conditions"=>"id=".$_GET["shouhou_id"]
                ));
                $this->assign('shouhou', $shouhou);
            }

            $this->display('seller_order.shouhou.html');
        }else{
            $orderId=$_POST["order_id"];
            $goodsId=$_POST["goods_id"];
            $shouhou_id=$_POST["shouhou_id"];
            $content_reply=$_POST["content_reply"];

            if($orderId == "" || $goodsId == "" || $shouhou_id == ""){
                $this->display('seller_order.shouhou.html');
                echo "<script>js_fail(\"订单号错误\");</script>";
                exit;
            }

            $user_info=$this->visitor->info;

            $conditions = ($_GET["app"]!="service")? "AND seller_id=" . $this->visitor->get('user_id'):"";

            $shouhou = $shouhou_mod->get("id=$shouhou_id ".$conditions);

            if($shouhou == "" || $shouhou["id"] <=0 ){
                $this->display('buyer_order.shouhou.html');
                echo "<script>js_fail(\"没有找到售后信息\");</script>";
                exit;
            }

            $ordergoods_mod =& m('ordergoods');
            $ordergoods=$ordergoods_mod->get(array(
                "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId "
            ));

            if(!$ordergoods){
                $this->display('seller_order.shouhou.html');
                echo "<script>js_fail(\"订单号商品不存在\");</script>";
                exit;
            }

            $id = $shouhou_mod->edit($shouhou_id,array(
                "content_reply"=>$content_reply,
                "time_reply"=>gmtime(),
                "status"=>1));

            $imgs = $this->_upload_image($user_info["user_id"],'reply_img');


            if(count($imgs)>0){
                $imgData=array();
                if($imgs["reply_img1"]!=""){
                    $imgData["reply_img1"]=$imgs["reply_img1"];
                    $this->deleteFileInfo($shouhou['reply_img1']);
                }
                if($imgs["reply_img2"]!=""){
                    $imgData["reply_img2"]=$imgs["reply_img2"];
                    $this->deleteFileInfo($shouhou['reply_img2']);
                }
                $shouhou_mod->edit($shouhou_id,$imgData);
            }

            if($id>0){
                $this->assign('msg', "订单售后提交处理成功！<br><a href='index.php?app=seller_order&act=shouhou&order_id={$orderId}&goods_id={$goodsId}&shouhou_id={$shouhou_id}'>返回继续操作</a>");
                $_POST="";
                $this->display('buyer_order.shouhou.success.html');
            }
        }

    }

    /* 上传图片 */
    function _upload_image($user_id,$url)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $data = array();
        for ($i = 1; $i <= 3; $i++)
        {
            $file = $_FILES[$url .$i];
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
                $dirname   = $up_dir. '/shouhou_chuli';
                $filename  = 'user_' . $user_id."_".gmtime() . '_' . $i;
                $data[$url . $i] = $uploader->save($dirname, $filename);
            }
        }
        return $data;
    }

    /*三级菜单*/
    function _get_member_submenu()
    {
        if($_GET["app"]=="service")
        {
            $act = $_GET["act"] == "benqu_orders" ? "benqu_orders" : "orders";
            return array(
                array(
                    'name' => 'all_orders',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=all_orders',
                ),
                array(
                    'name' => 'pending',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=pending',
                ),
                array(
                    'name' => 'submitted',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=submitted',
                ),
                array(
                    'name' => 'accepted',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=accepted',
                ),
                array(
                    'name' => 'shipped',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=shipped',
                ),
                array(
                    'name' => 'finished',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=finished',
                ),
                array(
                    'name' => 'canceled',
                    'url' => 'index.php?app=service&act='.$act.'&amp;type=canceled',
                ),
                array(
                    'name' => 'shouhou_order',
                    'url' => 'index.php?app=service&act='.$act.'&shouhou=1&type=all_orders',
                ),
            );
        }
        $array = array(
            array(
                'name' => 'all_orders',
                'url' => 'index.php?app=seller_order&amp;type=all_orders',
            ),
            array(
                'name' => 'pending',
                'url' => 'index.php?app=seller_order&amp;type=pending',
            ),
            array(
                'name' => 'submitted',
                'url' => 'index.php?app=seller_order&amp;type=submitted',
            ),
            array(
                'name' => 'accepted',
                'url' => 'index.php?app=seller_order&amp;type=accepted',
            ),
            array(
                'name' => 'shipped',
                'url' => 'index.php?app=seller_order&amp;type=shipped',
            ),
            array(
                'name' => 'finished',
                'url' => 'index.php?app=seller_order&amp;type=finished',
            ),
            array(
                'name' => 'canceled',
                'url' => 'index.php?app=seller_order&amp;type=canceled',
            ),
            array(
                'name' => 'shouhou_order',
                'url' => 'index.php?app=seller_order&shouhou=1&type=all_orders',
            ),
        );
        return $array;
    }

    function drop_goods(){
        $order_id= $_GET['order_id'];
        $goods_id = $_GET['goods_id'];
        $quantity = $_GET['quantity'];
        $user = $this->visitor->get();

        $params["goodsId"] =$goods_id;
        $params["goodsNum"] =$quantity;
        $params["userId"] =$user['user_id'];

        $params["orderId"] =$order_id;
        $params["type"] ="cancel_goods";

        $Client = new HttpClient(JAVA_LOCATION);
        $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;

        $pageContents = HttpClient::quickPost($url, $params);
        //echo $pageContents;
        $data = json_decode($pageContents);

        if(!$data ||  $data->code != 200 ){
            echo "更改订单商品失败，请稍后重试【".$data->code."】！";
        } else {
           echo"订单商品更改成功！";
        }
    }

    function updateOrder(){
        $order_sn= $_GET['order_sn'];
        $goods_ids = $_GET['goods_ids'];
        $quantity = $_GET['quantity'];

        $params["goodsIds"] =$goods_ids;
        $params["goodsNums"] =$quantity;

        $params["orderSn"] =$order_sn;
        $user = $this->visitor->get();
        $params["userId"] =$user['user_id'];

        $Client = new HttpClient(JAVA_LOCATION);
        $url = "http://".JAVA_LOCATION."/dajike-account/recall/recallFahuoOrder.action";


        $pageContents = HttpClient::quickPost($url, $params);
        //echo $pageContents;
        $data = json_decode($pageContents);

        if(!$data ||  $data->code != 200 ){
            echo "更改订单商品失败，请稍后重试【".$data->code."】！";
        } else {
            echo"订单商品更改成功！";
        }
    }


    //特定情况取消订单
    function cell_order() {
        $order_sn= $_GET['order_sn'];
        $quantity = $_GET['quantity'];
        $status = $_GET['status'];
        $params["goodsNums"] =$quantity;

        $params["orderSn"] =$order_sn;
        $user = $this->visitor->get();
        $params["userId"] =$user['user_id'];

        $params["cancelType"] = "1";

        $Client = new HttpClient(JAVA_LOCATION);
        if($status =='20'|| $status=='30') {
            $url = "http://".JAVA_LOCATION."/dajike-account/recall/recallFahuoOrder.action";
        }
        if($status =='40') {
            $url = "http://".JAVA_LOCATION."/dajike-account/recall/recallConfirmOrder.action";
        }




        $pageContents = HttpClient::quickPost($url, $params);
        //echo $pageContents;
        $data = json_decode($pageContents);

        if(!$data ||  $data->code != 200 ){
            echo "取消订单失败，请稍后重试【".$data->code."】！";
        } else {
            echo"取消订单成功！";
        }
    }
}

?>
