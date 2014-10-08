<?php

/**
 *    收银台控制器，其扮演的是收银员的角色，你只需要将你的订单交给收银员，收银员按订单来收银，她专注于这个过程
 *
 *    @author    Garbin
 */
class CashierApp extends ShoppingbaseApp
{
    /**
     *    根据提供的订单信息进行支付
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {

        $_GET['order_sns'] = isset($_GET['order_sns']) ? $_GET['order_sns'] : 0;
        $order_sns = $_GET['order_sns'] ;
        $order_model =& m('order');

        $user_info = $this->visitor->get();

        $user_id=$user_info['user_id'];
        $model_member =& m('member');
        $user= $model_member->get($user_id);

        //$this->pr($user);
        $orders=NULL;

        $jines=0;

        /*
        if($_GET["order_id"]!="" && $_GET["order_id"]!=0){
            $order = $order_model->get("order_id='".$_GET["order_id"]."' and buyer_id=$user_id and status=11");
            $orders=array("1"=>$order);
            $orders["order_sn"]=$order["order_sn"];
        }
        else if($session_id!="" && $session_id!=0){
            $orders = $order_model->find("session_id='".$session_id."' and buyer_id=$user_id and status=11" );
        }*/

        $order_sns_arr=explode(",",$order_sns);
        if(count($order_sns_arr)==0)
        {
            $this->show_warning('no_such_order');
            return;
        }

        foreach($order_sns_arr as $k=>$v){
            $order = $order_model->get("order_sn='".$v."' and buyer_id=$user_id and status=11");
            if($order["order_id"]<=0)
            {
                $this->show_warning('no_such_order');
                return;
            }
            $orders["orders"][$v]=array("order_sn"=>$v, "order_amount"=>$order["order_amount"]);
            $jines+=$order["order_amount"];
        }

        $orders["jines"]=$jines;

        $this->assign('allOrders', $orders);
        $this->assign('order_sns', $order_sns);

        $this->assign('yue',     $user["yue"]);

        $this->assign('chajia', $jines-$user["yue"]);

        $this->assign('jines', $jines);

        $this->assign('payments', $all_payments);
        $this->_curlocal(
            LANG::get('cashier')
        );
        $this->assign('TO_PAY_URL', TO_PAY_URL);
        $this->_config_seo('title', Lang::get('confirm_payment') . ' - ' . Conf::get('site_title'));
        $this->display('cashier.payment.html');
    }

    /**
     *    确认支付
     *
     *    @author    Garbin
     *    @return    void
     */
    function goto_pay()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $order_model =& m('order');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }

        #可能不合适
        if ($order_info['payment_id'])
        {
            $this->_goto_pay($order_id);
            return;
        }

        /* 验证支付方式 */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        if (!$payment_info)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* 保存支付方式 */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );

        /* 如果是货到付款，则改变订单状态 */
        if ($payment_info['payment_code'] == 'cod')
        {
            $edit_data['status']    =   ORDER_SUBMITTED;
        }

        $order_model->edit($order_id, $edit_data);

        /* 开始支付 */
        $this->_goto_pay($order_id);
    }

    /**
     *    线下支付消息
     *
     *    @author    Garbin
     *    @return    void
     */
    function offline_pay()
    {
        if (!IS_POST)
        {
            return;
        }
        $order_id       = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $pay_message    = isset($_POST['pay_message']) ? trim($_POST['pay_message']) : '';
        if (!$order_id)
        {
            $this->show_warning('no_such_order');
            return;
        }
        if (!$pay_message)
        {
            $this->show_warning('no_pay_message');

            return;
        }
        $order_model =& m('order');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }
        $edit_data = array(
            'pay_message' => $pay_message
        );

        $order_model->edit($order_id, $edit_data);

        /* 线下支付完成并留下pay_message,发送给卖家付款完成提示邮件 */
        $model_member =& m('member');
        $seller_info   = $model_member->get($order_info['seller_id']);
        $mail = get_mail('toseller_offline_pay_notify', array('order' => $order_info, 'pay_message' => $pay_message));
        $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

        $this->show_message('pay_message_successed',
            'view_order',   'index.php?app=buyer_order',
            'close_window', 'javascript:window.close();');
    }

    function _goto_pay($order_id)
    {
        header('Location:index.php?app=cashier&order_id=' . $order_id);
    }

    function order_success() {
//
//
//        $total_amount = null;
//       foreach($orders as $order) {
//           $total_amount += $order["order_amount"];
//       }
//
//        $this->assign("total_amount",$total_amount);
//
//        $this->display('cashier.payment.html');
    }
}

?>
