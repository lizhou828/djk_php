<?php

/**
 *    售货员控制器，其扮演实际交易中柜台售货员的角色，你可以这么理解她：你告诉我（售货员）要买什么东西，我会询问你你要的收货地址是什么之类的问题
＊        并根据你的回答来生成一张单子，这张单子就是“订单”
 *
 * @author    Garbin
 * @param    none
 * @return    void
 */
class OrderApp extends ShoppingbaseApp
{
    //  order.status
    //  0  取消订单
    //  11 待支付
    //  20 待发货
    //  30 已发货
    //  40 交易成功/完成
    function __construct()
    {

        $this->OrderApp();
    }

    function OrderApp()
    {

        parent::__construct();
        include_once(ROOT_PATH . '/app/member.app.php');
    }

    /**
     *    填写收货人信息，选择配送，支付方式。
     *
     * @author    Garbin
     * @param    none
     * @return    void
     */
    function index()
    {

        $this->assign('TO_PAY_URL', TO_PAY_URL);
        $order_type =& ot('normal');
        if (IS_POST && $_POST['goods'] == 'cart') {
            if (CART_IS_COOKIE == 'ON') {
                $cart_list = ecm_getcookie('cart');
                if ($cart_list != "") {
                    $stores_ids = $this->getCookieStoreId($cart_list);
                    foreach ($stores_ids as $stores_id) {
                        $goods_info = $this->getCookieGoodsInfo($cart_list, $stores_id, $order_type, "");
                        $goods_beyond = $this->_check_beyond_stock($goods_info['items']);
                    }
                    if ($goods_beyond) {
                        $str_tmp = '';
                        foreach ($goods_beyond as $goods) {
                            $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods
                                ['stock'];
                        }
                        $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
                        return;
                    }
                    $this->_curlocal(
                        LANG::get('create_order')
                    );

                    $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
                    //      $this->assign('goods_info', $goods_info);
                    $this->assign('order_goods', $this->_get_order_goods($stores_ids, $order_type, "", $cart_list));
                    $this->assign('my_address', $order_type->get_address());
                    $this->assign('regions', $order_type->get_regions());

                    $this->display("order.form.html");
                } else {
                    $this->show_message("请重新提交购物车订单");
                    header("Location:index.php?app=cart");
                }
            } else {
                $_POST['store_id'] = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
                $store_ids = $_POST['store_id'];
                if (!$store_ids) {
                    $this->show_message("请重新提交购物车订单");
                    header("Location:index.php?app=cart");
                }
                foreach ($store_ids as $store_id) {
                    $goods_info = $this->_get_goods_info($store_id, $order_type, "");
                    /*  检查库存 */
                    $goods_beyond = $this->_check_beyond_stock($goods_info['items']);

                    if ($goods_beyond) {
                        $str_tmp = '';
                        foreach ($goods_beyond as $goods) {
                            $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods
                                ['stock'];
                        }
                        $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
                        return;
                    }
                }

                $this->_curlocal(
                    LANG::get('create_order')
                );

                $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
                //      $this->assign('goods_info', $goods_info);
                $this->assign('order_goods', $this->_get_order_goods($store_ids, $order_type, ""));
                $this->assign('my_address', $order_type->get_address());
                $this->assign('regions', $order_type->get_regions());
                $this->display("order.form.html");
            }
        } else {
            $goods_info = $this->get_groupbuy_info();
//            if ($goods_info === false)
//            {
//                /* 购物车是空的 */
//                $this->show_warning('goods_empty');
//
//                return;
//            }

//            /*  检查库存 */
//            $goods_beyond = $this->_check_beyond_stock($goods_info['items']);
//            if ($goods_beyond)
//            {
//                $str_tmp = '';
//                foreach ($goods_beyond as $goods)
//                {
//                    $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods['stock'];
//                }
//                $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
//                return;
//            }

            /* 根据商品类型获取对应订单类型 */
            $goods_type =& gt($goods_info['type']);
            $order_type =& ot($goods_info['otype']);

            /* 显示订单表单 */
            $form = $order_type->get_order_form($goods_info['store_id']);
            if ($form === false) {
                $this->show_warning($order_type->get_error());

                return;
            }
            $this->_curlocal(
                LANG::get('create_order')
            );
            $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
            $order_form = array($goods_info['store_id'] => array(
                'goodsList' => array($goods_info['goods_id'] => array(
                    'goods_id' => $goods_info['goods_id'],
                    'goods_name' => $goods_info['goods_name'],
                    'goods_image' => $goods_info['goods_image'],
                    'subtotal' => $goods_info['subtotal'],
                    'quantity' => $goods_info['quantity'],
                    'price' => $goods_info['price'],
                    'specification' => $goods_info['groupbuy_desc'],
                    'shipping' => $this->get_shiiping($this->get_goods($goods_info['goods_id']), $this->get_regionname($order_type, "")),
                )),
                'store_info' => array(
                    'store_id' => $goods_info['store_id'],
                    'store_name' => $goods_info['store_name'],
                )
            ));
            $this->assign('order_goods', $order_form);
            $this->assign('my_address', $order_type->get_address());
            $this->assign('regions', $order_type->get_regions());
            $this->display("order.groupbuy.html");
        }
    }

    //获取团购信息
    function get_groupbuy_info()
    {
        $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : 0;
        $user_id = $this->visitor->get('user_id');
        if (!$group_id || !$user_id) {
            return false;
        }

        /* 获取团购记录详细信息 */
        $model_groupbuy =& m('groupbuy');
        $groupbuy_info = $model_groupbuy->get(array(
            'join' => 'be_join, belong_store, belong_goods',
            'conditions' => $model_groupbuy->getRealFields("gb.group_id={$group_id} AND  this.state=1"),
            'fields' => 'store.store_id, store.store_name, goods.goods_id, goods.goods_name, goods.default_image,gb.group_desc,gb.spec_price',
        ));
        if (empty($groupbuy_info)) {
            return false;
        }
        $groupbuy_info["quantity"] = $_POST["quantity"];

        $goods_mode = & m('goods');
        $goods = $goods_mode->get($groupbuy_info['goods_id']);
        /* 库存信息 */
        $model_goodsspec = & m('goodsspec');
        $goodsspec = $model_goodsspec->get('goods_id=' . $groupbuy_info['goods_id']);

        /* 获取商品信息 */
//        $spec_quantity = unserialize($groupbuy_info['spec_quantity']);
        $spec_price = unserialize($groupbuy_info['spec_price']);

        $goods_image = empty($groupbuy_info['default_image']) ? Conf::get('default_goods_image') : $groupbuy_info['default_image'];
        foreach ($spec_price as $spec_id => $spec_info) {
            $the_price = $spec_price[$spec_id]['price'];
        }
        $subtotal = $groupbuy_info["quantity"] * $the_price;
        $groupbuy_items = array(
            'goods_id' => $groupbuy_info['goods_id'],
            'goods_name' => $groupbuy_info['goods_name'],
            'goods_image' => $goods_image,
            'subtotal' => $subtotal,
            'stock' => $goodsspec['stock'],
            'quantity' => $groupbuy_info['quantity'],
            'groupbuy_desc' => $groupbuy_info['groupbuy_desc'],
            'amount' => $subtotal,
            'store_id' => $groupbuy_info['store_id'],
            'store_name' => $groupbuy_info['store_name'],
            'type' => 'material',
            'otype' => 'groupbuy',
            'price' => $the_price,
            'allow_coupon' => false,
        );
        $this->assign('group_id', $group_id);
        return $groupbuy_items;
    }

    //生产订单
    function add()
    {
        /* 在此获取生成订单的两个基本要素：用户提交的数据（POST），商品信息（包含商品列表，商品总价，商品总数量，类型），所属店铺 */
        // $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
        // $global = gmtime();
        $orders = "";
        $spec_ids = $_POST['spec_ids'];
        $spec_ids = explode('|',$spec_ids);
        $count = count($spec_ids);
        unset($spec_ids[$count - 1]);
        $cellphone = $_POST['cellphone'];
        $cart_list = $this -> selected($spec_ids);
        if ($cart_list != "") {
            $store_ids = $this->getCookieStoreId($cart_list);
        } else {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }
        $post_script = $_POST['postscript'];
        $model_address = & m("address");
        if($_POST['adr_checked'] == "" && $_GET['adr_checked'] == ""){
            echo json_encode(array("flag"=>"error"));
            return;
        }
        $address = $model_address->get($_POST['adr_checked']);
        $order_type =& ot('normal');
        if (!$store_ids) {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }
        $c_list = ecm_getcookie("cart");
        foreach($spec_ids as $spec_id){
            if($c_list != ""){
                $c_list = $this -> dropCart($spec_id, $c_list);
            }
        }
        ecm_setcookie("cart",$c_list,time() + 86400);
        foreach ($store_ids as $key => $store_id) {
            $postscript = $post_script[$key];
            if ($orders == "") {
                $orders = $this->_product_order($store_id,$postscript,$cellphone, $order_type, $address['region_id'],$cart_list);
            } else {
                $orders = $orders . "," . $this->_product_order($store_id,$postscript, $cellphone,$order_type, $address['region_id'],$cart_list);
            }
        }
        /* 到收银台付款 */
        //header('Location:index.php?app=cashier&order_sns=' . $orders);
        if($orders != ""){
            echo json_encode(array("flag"=>"ok","orders"=>$orders));
            return;
        }else{
            echo json_encode(array("flag"=>"no"));
            return;
        }
    }

    //根据商家及收货地址生成订单
    function _product_order($store_id, $postscript,$cellphone, $order_type, $adr,$cart_list)
    {
        /* 发送邮件 */
        $model_order =& m('order');
        $order_sns = "";
        if ($cart_list != "") {
            //获取cookie中的商品
            $goods_info = $this->getCookieGoodsInfo($cart_list, $store_id, $order_type, $adr);
            //获取cookie中的商品，在数据统计中会用到
            $goods_info_statistic = $this->getCookieGoodsInfo($cart_list, $store_id, $order_type, $adr);
        } else {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }

        if ($goods_info === false) {
            /* 购物车是空的 */
            $this->show_warning('goods_empty');
            return;
        }
        $flag = 1;
        foreach($goods_info['items'] as $gds){
            if($gds['pd_id'] == 5){
                $flag = 2;
            }
        }
        /* 根据商品类型获取对应的订单类型 */
        $order_type =& ot($goods_info['otype']);
        $model_address = & m("address");
        $address = $model_address->get($_POST['adr_checked']);
        $_POST['address_options'] = $_POST['adr_checked'];
        $_POST['consignee'] = $address['consignee'];
        $_POST['region_name'] = $address['region_name'];
        $_POST['region_id'] = $address['region_id'];
        $_POST['phone_tel'] = $address['phone_tel'];
        $_POST['phone_mob'] = $address['phone_mob'];
        $_POST['address'] = $address['address'];
        $_POST['postscript'] = empty($postscript) ? null : $postscript;
        if($flag == 2){
            $_POST['cellphone'] = $cellphone;
        }else{
            $_POST['cellphone'] = null;
        }
        /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
        $store_model = & m('store');
        $member_mo = & m('member');
        $store = $store_model->get($store_id);
        //$boolean = false;
        $spec_mod = & m('goodsspec');
        $max_shiiping = array();
        $orderIdArray=array();
        if (!empty($goods_info['items'])) {

            //移动商城正常下单购买的商品（）
            $normalGoods = array();
            $normalGoodsShipping =array();

            foreach ($goods_info['items'] as $key => $gd) {
                $max_shiiping[$key] =$goods_info['shippingfee'];
                $spec = $spec_mod->get_info($gd['spec_id']);
                $goods_info['items'][$key]['org_price'] = $spec['org_price'];
                $goods_info['items'][$key]['price'] = doubleval($gd['price']);


                //一、假如在集客小店里面下的订单，就要分单处理
                if($gd['shop_id'] != null &&  $gd['shop_id'] != ""){
                    $goods_info['ticheng'] = doubleval($gd['ticheng']);
                    $goods_info['amount'] = $gd['quantity'] * $gd['price'];
                    $goods_info['quantity'] = $gd['quantity'];
                    // $boolean = true;
                    if( $gd['vshop'] && $gd['vshop'] == "1"){
                        $goods_info['type'] ='mobile,vshop';
                    } else{
                        $goods_info['type'] ='mobile,shop';
                    }
                    $goods_info['shop_id'] = $gd['shop_id'] ;
                    $goods_info['items'] = array($key => $gd);
                    $_POST['shipping']= $gd['shippingFee'];
                    $order_id = $order_type->submit_order(array(
                        'goods_info' => $goods_info, //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
                        'post' => $_POST, //用户填写的订单信息
                    ));
                    $order_info_hgg = $model_order->get($order_id);
                    if ($order_sns=="") {
                        $order_sns = $order_info_hgg['order_sn'];
                    }else {
                        $order_sns=$order_sns.",".$order_info_hgg['order_sn'];
                    }
                    array_push($orderIdArray,$order_info_hgg['order_id']);
                }else{
                    array_push($normalGoodsShipping,$gd['shippingFee']);
                    array_push($normalGoods,$gd);
                    continue;
                }
            }
            if(count($normalGoods) > 0){
//                echo "处理前的goods_info=";var_dump($goods_info);echo "<br/>";
//                echo "normalGoods=";var_dump($normalGoods);echo "<br/>";

                unset($goods_info['items']);//清空原有的数据
                $goods_info['items']=array();
                $goods_info['amount']=0;
                $goods_info['ticheng']=0;
                $goods_info['quantity']=0;
                foreach($normalGoods as $key=> $goods){

                    array_push($goods_info['items'],$goods);
                    $spec = $spec_mod->get_info($goods['spec_id']);
                    $goods_info['items'][$key]['org_price'] = $spec['org_price'];
                    $goods_info['items'][$key]['price'] = doubleval($spec['price']);

                    $goods_info['type'] ='mobile';
                    $goods_info['shop_id'] =null;
                    $goods_info['ticheng'] += doubleval($goods['ticheng']);
                    $goods_info['amount'] += $goods['quantity'] * $spec['price'];
                    $goods_info['quantity'] += intval($goods['quantity']);
                }
                if (count($normalGoodsShipping)>0) {
                    rsort($normalGoodsShipping);
                    $_POST['shipping']= current($normalGoodsShipping) ;
                } else {
                    $_POST['shipping']= 0 ;
                }
//                echo "处理后的goods_info=";
//                var_dump($goods_info);exit;
                $order_id = $order_type->submit_order(array(
                    'goods_info' => $goods_info, //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
                    'post' => $_POST, //用户填写的订单信息
                ));
                $order_info_hgg = $model_order->get($order_id);
                if ($order_sns=="") {
                    $order_sns = $order_info_hgg['order_sn'];
                }else {
                    $order_sns=$order_sns.",".$order_info_hgg['order_sn'];
                }
                array_push($orderIdArray,$order_info_hgg['order_id']);
            }
//        echo "order_sns=".$order_sns;

        }
        if(count($orderIdArray) == 0){
            $this->show_warning($order_type->get_error());
            return;
        }

        foreach($orderIdArray as $orderId){
            /* 下单完成后清理商品，如清空购物车，或将团购拍卖的状态转为已下单之类的 */
            $this->_clear_goods($orderId, $store_id);
            /* 发送邮件 */
            $model_order =& m('order');
            /* 减去商品库存 */
            $model_order->change_stock('-', $order_id);
            /* 获取订单信息 */
            $order_info = $model_order->get($order_id);
            /* 发送事件 */
            $feed_images = array();
            foreach ($goods_info['items'] as $_gi) {
                $feed_images[] = array(
                    'url' => SITE_URL . '/' . $_gi['goods_image'],
                    'link' => SITE_URL . '/' . url('app=goods&id=' . $_gi['goods_id']),
                );
            }
            $this->send_feed('order_created', array(
                'user_id' => $this->visitor->get('user_id'),
                'user_name' => addslashes($this->visitor->get('user_name')),
                'seller_id' => $order_info['seller_id'],
                'seller_name' => $order_info['seller_name'],
                'store_url' => SITE_URL . '/' . url('app=store&id=' . $order_info['seller_id']),
                'images' => $feed_images,
            ));

            $buyer_address = $this->visitor->get('email');
            $model_member =& m('member');
            $member_info = $model_member->get($goods_info['store_id']);
            $seller_address = $member_info['email'];

            /* 发送给买家下单通知 */
            $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
            $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));

            /* 发送给卖家新订单通知 */
            $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
            $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
        }

        /* 更新下单次数 */
        $model_goodsstatistics =& m('goodsstatistics');
        $goods_ids = array();
        foreach ($goods_info_statistic['items'] as $goods) {
            $goods_ids[] = $goods['goods_id'];
        }
        $model_goodsstatistics->edit($goods_ids, 'orders=orders+1');

        return $order_sns;

    }

    //团购订单表单数据组装
    function groupbuyOrder($goods, $quantity, $order_type, $adr)
    {
        $goods_info = $this->getGroupByGoods($goods, $order_type, $adr, $quantity);
        /* 优惠券数据处理 */
//        if ($goods_info['allow_coupon'] && isset($_POST['coupon_sn']) && !empty($_POST['coupon_sn']))
//        {
//            $coupon_sn = trim($_POST['coupon_sn']);
//            $coupon_mod =& m('couponsn');
//            $coupon = $coupon_mod->get(array(
//                'fields' => 'coupon.*,couponsn.remain_times',
//                'conditions' => "coupon_sn.coupon_sn = '{$coupon_sn}' AND coupon.store_id = " . $store_id,
//                'join'  => 'belongs_to_coupon'));
//            if (empty($coupon))
//            {
//                $this->show_warning('involid_couponsn');
//                exit;
//            }
//            if ($coupon['remain_times'] < 1)
//            {
//                $this->show_warning("times_full");
//                exit;
//            }
//            $time = gmtime();
//            if ($coupon['start_time'] > $time)
//            {
//                $this->show_warning("coupon_time");
//                exit;
//            }
//
//            if ($coupon['end_time'] < $time)
//            {
//                $this->show_warning("coupon_expired");
//                exit;
//            }
//            if ($coupon['min_amount'] > $goods_info['amount'])
//            {
//                $this->show_warning("amount_short");
//                exit;
//            }
//            unset($time);
//            $goods_info['discount'] = $coupon['coupon_value'];
//        }
        /* 根据商品类型获取对应的订单类型 */
        $goods_type =& gt($goods_info['type']);
        $order_type =& ot($goods_info['otype']);
//        if(!$_POST['address_options']) {
//            $this->show_warning("请选择收货地址");
//            exit;
//        }
        $model_address = & m("address");
        $address = $model_address->get($_POST['adr_checked']);
        $_POST['address_options'] = $_POST['adr_checked'];
        $_POST['consignee'] = $address['consignee'];
        $_POST['region_name'] = $address['region_name'];
        $_POST['region_id'] = $address['region_id'];
        $_POST['phone_tel'] = $address['phone_tel'];
        $_POST['phone_mob'] = $address['phone_mob'];
        $_POST['address'] = $address['address'];
//        $_POST['cellphone'] = $_POST['cellphone'] ;


        $_POST['postscript'] = empty($postscript) ? null : $postscript;
        /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
        $order_id = $order_type->submit_order(array(
            'goods_info' => $goods_info, //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
            'post' => $_POST, //用户填写的订单信息
        ));
        if (!$order_id) {
            $this->show_warning($order_type->get_error());

            return;
        }

        /* 发送邮件 */
        $model_order =& m('order');

        /* 减去商品库存 */
        $model_order->change_stock('-', $order_id);

        /* 获取订单信息 */
        $order_info = $model_order->get($order_id);

        /* 发送事件 */
        $feed_images = array();
        foreach ($goods_info['items'] as $_gi) {
            $feed_images[] = array(
                'url' => SITE_URL . '/' . $_gi['goods_image'],
                'link' => SITE_URL . '/' . url('app=goods&id=' . $_gi['goods_id']),
            );
        }
        $this->send_feed('order_created', array(
            'user_id' => $this->visitor->get('user_id'),
            'user_name' => addslashes($this->visitor->get('user_name')),
            'seller_id' => $order_info['seller_id'],
            'seller_name' => $order_info['seller_name'],
            'store_url' => SITE_URL . '/' . url('app=store&id=' . $order_info['seller_id']),
            'images' => $feed_images,
        ));

        $buyer_address = $this->visitor->get('email');
        $model_member =& m('member');
        $member_info = $model_member->get($goods_info['store_id']);
        $seller_address = $member_info['email'];

        /* 发送给买家下单通知 */
        $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
        $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));

        /* 发送给卖家新订单通知 */
        $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
        $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));

        /* 更新下单次数 */
        $model_goodsstatistics =& m('goodsstatistics');
        $goods_ids = array();
        foreach ($goods_info['items'] as $goods) {
            $goods_ids[] = $goods['goods_id'];
        }
        $model_goodsstatistics->edit($goods_ids, 'orders=orders+1');

        return $order_info["order_sn"];
    }

    //团购运费
    function groupbuy_ajax()
    {
        $ch_address = null;
        if ($_GET['address_name']) {
            $ch_address = $_GET['address_name'];
        }
        $goods_id = $_GET['goods_id'];
        if ($goods_id == null) {
            $goods_id = 0;
        }
        $goods_model = & m('goods');
        $goods = $goods_model->get($goods_id);
        $order_type =& ot('groupbuy');
        return $this->get_shiiping($this->get_goods($goods["goods_id"]), $this->get_regionname($order_type, $ch_address));

    }

    //团购订单
    function groupbuy_add()
    {
        $group_id = $_POST['group_id'];
        $model_groupbuy =& m('groupbuy');
        $groupbuy_info = $model_groupbuy->get(array(
            'join' => 'be_join, belong_store, belong_goods',
            'conditions' => $model_groupbuy->getRealFields("gb.group_id={$group_id} AND  this.state=1"),
            'fields' => 'store.store_id, store.store_name, goods.goods_id, goods.goods_name, goods.default_image,gb.group_desc,gb.spec_price',
        ));
        $spec_price = unserialize($groupbuy_info['spec_price']);
        foreach ($spec_price as $spec_id => $spec_info) {
            $the_price = $spec_price[$spec_id]['price'];
        }
        $quantity = $_POST['quantity'];
        $addr = $_POST['adr_checked'];
        $goods_model = & m('goods');
        $store_model = & m('store');
        $goods_spec = & m('goodsspec');
        $goods = $goods_model->get($groupbuy_info['goods_id']);
        $goods['price'] = $the_price;
        $spec = $goods_spec->get('goods_id=' . $groupbuy_info['goods_id']);
        if ($spec['stock'] < $quantity) {
            $str_tmp = '';
            $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods
                ['stock'];
            $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
            return;
        }
        $order_type =& ot('groupbuy');
        $model_address = & m("address");
        $address = $model_address->get($_POST['adr_checked']);
        $store = $store_model->get($goods['store_id']);
        $orders = $this->groupbuyOrder($goods, $quantity, $order_type, $address['region_name']);
    }


    //订单表单数据组装
    function _get_order_goods($store_ids, $order_type, $adr, $cart_items = '')
    {

        $cart_model =& m('cart');

        $store_model =& m('store');
        $order_goods_form = array();
//        $amout = null;
        $user_id = $this -> visitor -> get('user_id');
        $address_model = & m('address');
        $address = $address_model -> find("user_id = " . $user_id . " and state = 1");
        if($adr == ""){
            if(count($address) == 1){
                $adr1 = $address['region_name'];
                $adr = $address['region_id'];
            }else{
                $address = $address_model -> find('user_id=' . $user_id.' order by addr_id desc');
                $i = 0;
                if($address > 0){
                    foreach($address as $ad){
                        if($i < 1){
                            $adr = $ad['region_id'];
                            $adr1 = $ad['region_name'];
                            $i = $i + 1;
                        }
                    }
                }
            }
        }
        $this -> assign("addr",$adr1);
        foreach ($cart_items as $rec_id => $goods) {
            $cart_items[$rec_id]['shipping'] = $this->get_shiiping($this->get_goods($goods["goods_id"]), $this->get_regionname($order_type, $adr));
            $cart_items[$rec_id]['subtotal'] = $goods['quantity'] * $goods['price'];
//            $order_goods_form[$goods['store_id']]['store_info'] = $store_model->get($goods["store_id"]);
//            $order_goods_form[$goods['store_id']]['subtotal'] = $goods['quantity'] * $goods['price'] + $goods['shipping'];
        }
        return $cart_items;
    }

    //处理收货人地址
    function get_regionname($order_type, $adr1)
    {
        $ads_id = null;
        $i = 0;
        if (!$adr1 || $adr1 == "") {
            $adr1 = $order_type->get_address();
            foreach ($adr1 as $ads => $v) {
                if ($i == 0) {
                    $ads_id = $v["region_id"];
                }
                $i++;
            }
        } else {
            $ads_id = $adr1;
        }
        return $ads_id;
    }

    //取goods
    function get_goods($goods_id)
    {
        $model_goods = & m("goods");
        $goods = $model_goods->get($goods_id);
        return $goods['shipping_id'];
    }

    //获取运费
    function get_shiiping($shipping_id, $region_id)
    {
        $model_yunfei = & m('yunfei');
        $region_mod = & m('region');
        $region_ids = $region_mod->get_parents($region_id);

        if (!$shipping_id) {
            return 0;
        } else {
            $yf = 0;
            $yunfei = $model_yunfei->get('shipping_id=' . $shipping_id . " AND region_id =" .$region_id);
//            $this->pr($yunfei);exit;
            if ($yunfei){
                $yf =$model_yunfei->get('shipping_id=' . $shipping_id . " AND region_id =" .$region_id);
            } else {
                if(count($region_ids)>1) {
                    if ($region_ids[1]) {
                        $yf =$model_yunfei->get('shipping_id=' . $shipping_id . " AND region_id =" .$region_ids[1]);
                    }
                } else if($region_ids) {
                    $yf =$model_yunfei->get('shipping_id=' . $shipping_id . " AND region_id =" .$region_ids[0]);
                } else {
                    $yf =$model_yunfei->get('shipping_id=' . $shipping_id . " AND region_id =" .$region_id);
                }
            }
            if ($yf) {
                return $yf['jiage'];
            } else {
                return 0;
            }
        }
    }

    //ajax获取运费
    function ajax_get_shipping()
    {
        $cart_list = ecm_getcookie('cart');
        if ($cart_list != "") {
            $store_ids = $this->getCookieStoreId($cart_list);
        } else {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }
        $order_type =& ot('normal');
        if (!$store_ids) {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }
        $ch_address = null;
        if ($_GET['address_name']) {
            $ch_address = $_GET['address_name'];
        }
        foreach ($store_ids as $stores_id) {
            $goods_info = $this->getCookieGoodsInfo($cart_list, $stores_id, $order_type, "");
            $goods_beyond = $this->_check_beyond_stock($goods_info['items']);
            if ($goods_beyond) {
                $str_tmp = '';
                foreach ($goods_beyond as $goods) {
                    $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods
                        ['stock'];
                }
                $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
                return;
            }
        }
        $this->_curlocal(
            LANG::get('create_order')
        );

        $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
        //        $this->assign('goods_info', $goods_info);
        $this->assign('my_address', $order_type->get_address());
        $this->assign('order_goods', $this->_get_order_goods($store_ids, $order_type, $ch_address, $cart_list));
        $this->display("order.ajax_goods.html");
    }

    function ajax_getYf(){
        $id = $_POST['id'];
        $sepc_ids = $_POST['sepc_ids'];
//        $ch_address = $_POST['address_name'];
        $spec_ids = explode('|',$sepc_ids);
        $count = count($spec_ids);
        unset($spec_ids[$count - 1]);
        $cart_list = ecm_getcookie('cart');
        $address_model = & m('address');
        $address = $address_model -> get($id);
        $ch_address = $address['region_id'];
        $total = 0;
        $totalPrice = 0;
        $order_type =& ot('normal');
        if($cart_list != ""){
            $goods = $this -> getGoods($cart_list, $spec_ids);
            $stores_ids = $this->getCookieStoreId($cart_list);
//            $order_goods=$this->_get_order_goods($stores_ids, $order_type,$ch_address, $this -> getGoods($cart_list,$spec_ids));
//            echo "order_goods=";
//            var_dump($order_goods);exit;
            foreach($stores_ids as $storeId){
                $shiping = array(); //总运费
                $jkxd_shipping = array();//手机版集客小店订单中商品的运费
                $normal_shipping=array();//移动商城订单中商品的运费
                foreach($this->_get_order_goods($stores_ids, $order_type,$ch_address, $this -> getGoods($cart_list,$spec_ids)) as $gds){
                    if($gds['store_id'] == $storeId){
                        if( $gds['shop_id'] && $gds['shop_id'] > 0 ){
                            array_push($jkxd_shipping,$gds['shipping']);
                        }else{
                            array_push($normal_shipping,$gds['shipping']);
                        }
                    }
                }
                if( count( $normal_shipping ) > 0 ){
                    rsort($normal_shipping);
                    array_push($shiping,current($normal_shipping));
                }
                if( count($jkxd_shipping) > 0){
                    $shiping = array_merge($shiping,$jkxd_shipping);
                }
                if(count( $shiping ) > 0){
                    foreach( $shiping as $sp){
                        $total = $total + $sp;
                    }
                }
            }

            foreach($goods as $good){
                $totalPrice = $totalPrice + $good['subtotal'];
            }
            echo json_encode(array("flag"=>"ok","yunfei"=>$total,"totalPrice"=>$totalPrice + $total,"address1"=>$address['region_name']));
        }else{
            echo json_encode(array("flag"=>"null"));
        }
        return;
    }

    function save_address()
    {
        /*  检查是否添加收货人地址  */
        if ($_POST) {
            $data = array(
                'user_id' => $this->visitor->get('user_id'),
                'consignee' => trim($_POST['user_name']),
                'region_id' => $_POST['region_id'],
                'region_name' => $_POST['region_name'],
                'address' => trim($_POST['address']),
                'zipcode' => trim($_POST['zipcode']),
                'phone_tel' => trim($_POST['phone_tel']),
                'phone_mob' => trim($_POST['phone_mob']),
                'state' => 1,
            );
            $model_address =& m('address');
            $id = $model_address->add($data);
            if(!isset($id) || $id == "" || $id == null){
                echo json_encode(array("flag"=>"error"));
            }else{
                echo json_encode(array("flag"=>"ok"));
            }
            return;
        }
    }

    function save_address2()
    {
        $user_id = "";
        /*  检查是否添加收货人地址  */
        if ($_GET) {
            $order_type =& ot('normal');
            $this->assign('my_address', $order_type->get_address2($user_id));
            $this->display("order.ajax_address.html");
        } else {
            $data = array(
                'user_id' => $this->visitor->get('user_id'),
                'consignee' => trim($_GET['consignee']),
                'region_id' => $_GET['region_id'],
                'region_name' => $_GET['region_name'],
                'address' => trim($_GET['address']),
                'zipcode' => trim($_GET['zipcode']),
                'phone_tel' => trim($_GET['phone_tel']),
                'phone_mob' => trim($_GET['phone_mob']),
            );
            $model_address =& m('address');
            $model_address->add($data);
            $order_type =& ot('normal');
            $this->assign('my_address', $order_type->get_address2($user_id));
            $this->display("order.ajax_address.html");
        }
    }

    /**
     *    获取外部传递过来的商品
     *
     * @author    Garbin
     * @param    none
     * @return    void
     */
    function _get_goods_info($store_id, $order_type, $adr)
    {
        $return = array(
            'items' => array(), //商品列表
            'quantity' => 0, //商品总量
            'amount' => 0, //商品总价
            'store_id' => 0, //所属店铺
            'store_name' => '', //店铺名称
            'type' => null, //商品类型
            'otype' => 'normal', //订单类型
            'allow_coupon' => true, //是否允许使用优惠券
            'jifen_amount'=>0,
        );
        switch ($_GET['goods']) {
            case 'groupbuy':
                /* 团购的商品 */

            default:
                /* 从购物车中取商品 */
                if (!$store_id) {
                    return false;
                }
                $cart_model =& m('cart');
                $cart_items = $cart_model->find(array(
                    'conditions' => "user_id = " . $this->visitor->get('user_id') . " AND store_id = {$store_id} AND session_id='" . SESS_ID . "'",
                    'join' => 'belongs_to_goodsspec',
                ));
                if (empty($cart_items)) {
                    return false;
                }

                $spec_mod = & m('goodsspec');
                $store_model =& m('store');
                $store_info = $store_model->get($store_id);

                foreach ($cart_items as $rec_id => $goods) {
                    $return['quantity'] += $goods['quantity']; //商品总量
                    $return['amount'] += $goods['quantity'] * $goods['price']; //商品总价
                    $return['jifen_amount']+= intval($goods['if_jifen']);
                    $cart_items[$rec_id]['subtotal'] = $goods['quantity'] * $goods['price']; //小计
                    empty($goods['goods_image']) && $cart_items[$rec_id]['goods_image'] = Conf::get('default_goods_image');
                    if ($adr != "") {
                        $cart_items[$rec_id]['shippingfee'] = $this->get_shiiping($this->get_goods($goods["goods_id"]), $this->get_regionname($order_type, $adr));
                    }
//                    if (doubleval($good['org_price']) > 0  && doubleval($good['org_price']) < doubleval($good['price']))
//                    {
//                        $return['ticheng'] = 1- number_format((doubleval($good['org_price'])/doubleval($good['price'])),4);
//                    }
//                    $return['ticheng_jine'] = $goods['quantity'] * (doubleval($goods['price']) - doubleval($goods['org_price']));
                }
                $return['items'] = $cart_items;
                $return['store_id'] = $store_id;
                $return['store_name'] = $store_info['store_name'];
                $return['type'] = 'material';
                $return['otype'] = 'normal';
                $return['postscript'] = $_POST['postscript'];
                break;
        }

        return $return;
    }

    /*根据店铺id获取cookie中的商品*/
    function getCookieGoodsInfo($cart_list, $storeId, $order_type, $adr)
    {
        $return = array(
            'items' => array(), //商品列表
            'quantity' => 0, //商品总量
            'amount' => 0, //商品总价
            'store_id' => 0, //所属店铺
            'store_name' => '', //店铺名称
            'type' => null, //商品类型
            'otype' => 'normal', //订单类型
            'allow_coupon' => true, //是否允许使用优惠券
            'jifen_amount' => 0,
//            'ticheng_jine' =>0
        );
        switch ($_GET['goods']) {
            case 'groupbuy':
                /* 团购的商品 */

            default:
                if (!$storeId || $cart_list == '') {
                    return false;
                }
                $cart = explode('|', $cart_list);
                $cart_items = array();
                foreach ($cart as $cart1) {
                    if ($cart1 != '') {
                        $car = explode(',', $cart1);
                        $shop_id=null;
                        $vshop=null;
                        foreach ($car as $ca) {
                            $elmet = explode('^', $ca);
                            if ($elmet[0] == 'goods_id') {
                                $goods_model = & m('goods');
                                $good = $goods_model->get($elmet[1]);
                            }
                            if ($elmet[0] == 'store_id') {
                                $store_id = $elmet[1];
                            }
                            if ($elmet[0] == 'spec_id') {
                                $spec_id = $elmet[1];
                                $goods_spec = & m('goodsspec');
                                $spec = $goods_spec->get($spec_id);
                            }
                            if ($elmet[0] == 'quantity') {
                                $quantity = $elmet[1];
                            }
                            if ($elmet[0] == 'price') {
                                $price =$spec['price'];
                            }
                            if ($elmet[0] == 'specification') {
                                $specification = $elmet[1];
                            }
                            if($elmet[0] == 'shop_id'){
                                $shop_id = $elmet[1];
                            }
                            if($elmet[0] == 'vshop'){
                                $vshop = $elmet[1];
                            }
                        }
                        if ($store_id == $storeId) {
                            $good['quantity'] = $quantity;
                            $good['goods_image'] = $good['default_image'];
                            $good['spec_id'] = $spec_id;
                            $good['specification'] = $specification;
                            $good['description'] = '';
                            $good['description_tmp'] = '';
                            if($shop_id && $shop_id != null && $shop_id != ""  ){
                                $good['shop_id'] = $shop_id;
                            }
                            if( $vshop == "1"){
                                $good['vshop'] = $vshop;
                            }
                            $good['stock'] = $spec['stock'];
                            /*把属于此店铺的商品添加进集合*/
                            array_push($cart_items, $good);
                            $return['quantity'] = intval($return['quantity']) + intval($quantity);
                            $return['amount'] = doubleval($return['amount']) + intval($quantity) * doubleval($price);
                            $return['jifen_amount'] = intval($return['jifen_amount']) + intval($good['if_jifen']*$good['if_jifen']);
//                            if (doubleval($good['org_price']) > 0
//                                && doubleval($good['org_price']) < doubleval($good['price']))
//                            {
//                                $return['ticheng'] = 1- number_format((doubleval($good['org_price'])/doubleval($good['price'])),4);
//                            }
//                            $return['ticheng_jine'] =   intval($quantity)*doubleval($good['price'])-(intval($quantity) * doubleval($good['org_price']));
                        }
                    }
                }
                $shippfee = array();
                $normalGoodsShipping =array();
                $jkxdGoodsShipping =array();
                $store_model =& m('store');
                $store_info = $store_model->get($storeId);
                $goodList = array();
                foreach ($cart_items as $rec_id => $goods) {
                    empty($goods['goods_image']) && $cart_items[$rec_id]['goods_image'] = Conf::get('default_goods_image');
                    $shipping_temp = $this->get_shiiping($this->get_goods($goods["goods_id"]), $this->get_regionname($order_type, $adr));

                    $goods['shippingFee'] = $shipping_temp;
                    array_push($goodList,$goods);
                    if( $goods['shop_id']  && $goods['shop_id'] > 0 ){
                        array_push($jkxdGoodsShipping,$shipping_temp);
                    }else{
                        array_push($normalGoodsShipping,$shipping_temp);
                    }

                }

                $return['items'] = $goodList;
                $return['store_id'] = $storeId;
                $return['store_name'] = $store_info['store_name'];
                $return['type'] = 'mobile';
                $return['otype'] = 'normal';
                $return['postscript'] = $_POST['postscript'];
                if( count($normalGoodsShipping) > 0 ){
                    rsort($normalGoodsShipping);
                    array_push($shippfee,current($normalGoodsShipping));
                }
                if( count($jkxdGoodsShipping) > 0 ){
                    $shippfee = array_merge($shippfee,$jkxdGoodsShipping);
                }
                $totalShipping = 0;
                if (count($shippfee)>0) {
                    foreach($shippfee as $ship){
                        $totalShipping = $totalShipping + $ship;
                    }
                }
                $return['shippingfee'] = $totalShipping;
                break;
        }
        return $return;
    }

    /**
     *    下单完成后清理商品
     *
     * @author    Garbin
     * @return    void
     */
    function _clear_goods($order_id, $store_id)
    {
        switch ($_GET['goods']) {
            case 'groupbuy':
                /* 团购的商品 */
                $model_groupbuy =& m('groupbuy');
                $model_groupbuy->updateRelation('be_join', $_GET['group_id'], $this->visitor->get('user_id'), array(
                    'order_id' => $order_id,
                ));
                break;
            default: //购物车中的商品
                /* 订单下完后清空指定购物车 */
//                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
//                $store_id = $_GET['store_id'];
                if (!$store_id) {
                    return false;
                }
                if (CART_IS_COOKIE == 'ON') {
                    /*cookie设置要响应到页面才能生效!!!!!*/
//                    ecm_setcookie('cart', '', time() + 86400);
                } else {
                    $model_cart =& m('cart');
                    $model_cart->drop("store_id = {$store_id} AND session_id='" . SESS_ID . "'");
                }
                //优惠券信息处理
                if (isset($_POST['coupon_sn']) && !empty($_POST['coupon_sn'])) {
                    $sn = trim($_POST['coupon_sn']);
                    $couponsn_mod =& m('couponsn');
                    $couponsn = $couponsn_mod->get("coupon_sn = '{$sn}'");
                    if ($couponsn['remain_times'] > 0) {
                        $couponsn_mod->edit("coupon_sn = '{$sn}'", "remain_times= remain_times - 1");
                    }
                }
                break;
        }
    }

    /**
     * 检查优惠券有效性
     */
    function check_coupon()
    {
        $coupon_sn = $_GET['coupon_sn'];
        $store_id = is_numeric($_GET['store_id']) ? $_GET['store_id'] : 0;
        if (empty($coupon_sn)) {
            $this->js_result(false);
        }
        $coupon_mod =& m('couponsn');
        $coupon = $coupon_mod->get(array(
            'fields' => 'coupon.*,couponsn.remain_times',
            'conditions' => "coupon_sn.coupon_sn = '{$coupon_sn}' AND coupon.store_id = " . $store_id,
            'join' => 'belongs_to_coupon'));
        if (empty($coupon)) {
            $this->json_result(false);
            exit;
        }
        if ($coupon['remain_times'] < 1) {
            $this->json_result(false);
            exit;
        }
        $time = gmtime();
        if ($coupon['start_time'] > $time) {
            $this->json_result(false);
            exit;
        }


        if ($coupon['end_time'] < $time) {
            $this->json_result(false);
            exit;
        }

        // 检查商品价格与优惠券要求的价格

        $model_cart =& m('cart');
        $item_info = $model_cart->find("store_id={$store_id} AND session_id='" . SESS_ID . "'");
        $price = 0;
        foreach ($item_info as $val) {
            $price = $price + $val['price'] * $val['quantity'];
        }
        if ($price < $coupon['min_amount']) {
            $this->json_result(false);
            exit;
        }
        $this->json_result(array('res' => true, 'price' => $coupon['coupon_value']));
        exit;

    }

    function _check_beyond_stock($goods_items)
    {
//        print_r($goods_items);exit;
        $goods_beyond_stock = array();
        if (!$goods_items) {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }
        foreach ($goods_items as $rec_id => $goods) {
            if ($goods['pd_id'] != 5 && $goods['quantity'] > $goods['stock']) {
                $goods_beyond_stock[$goods['spec_id']] = $goods;
            }
        }
        return $goods_beyond_stock;

    }

    /*获取cookie中购物车的店铺id数组*/
    function getCookieStoreId($cart_list)
    {
        $stores = array();
        $list = explode('|', $cart_list);
        foreach ($list as $ls) {
            $lt = explode(',', $ls);
            foreach ($lt as $param) {
                $elmet = explode('^', $param);
                if ($elmet[0] == 'store_id') {
                    array_push($stores, $elmet[1]);
                }
            }
        }
        $stores = array_unique($stores);
        return $stores;
    }

    function getGroupByGoods($goods, $order_type, $adr, $quantity)
    {
        $cart_items = array();
        $return = array(
            'items' => array(), //商品列表
            'quantity' => 0, //商品总量
            'amount' => 0, //商品总价
            'store_id' => 0, //所属店铺
            'store_name' => '', //店铺名称
            'type' => null, //商品类型
            'otype' => 'normal', //订单类型
            'allow_coupon' => true, //是否允许使用优惠券
        );
        if ($adr != "") {
            $goods['shippingfee'] = $this->get_shiiping($this->get_goods($goods["goods_id"]), $this->get_regionname($order_type, $adr));
        }
        $store_model = & m('store');
        $store = $store_model->get($goods['store_id']);
        array_push($cart_items, $goods);
        $return['items'] = $cart_items;
        $return['store_id'] = $goods['store_id'];
        $return['store_name'] = $store['store_name'];
        $return['type'] = 'material';
        $return['otype'] = 'groupbuy';
        $return['postscript'] = $_POST['postscript'];
        $return['quantity'] = $quantity;
        $return['amount'] = intval($quantity) * doubleval($goods['price']);
        return $return;
    }

    //取积分
    function get_ajax_jifen()
    {
        $member = new MemberApp();
        $data=$member->user_api('','','');
        echo $data;
    }

    function phoneList(){
        $spec_ids = $_POST["spec_ids"];
        if(!isset($spec_ids)){
            $spec_id = $_GET["spec_id"];
            $spec_ids = explode('|',$spec_id);
            $count = count($spec_ids);
            unset($spec_ids[$count - 1]);
        }
        $this->assign('TO_PAY_URL', TO_PAY_URL);
        $order_type =& ot('normal');
        $cart_list = $this -> selected($spec_ids);
        if ($cart_list != "") {
            $stores_ids = $this->getCookieStoreId($cart_list);
            foreach ($stores_ids as $stores_id) {
                $goods_info = $this->getCookieGoodsInfo($cart_list, $stores_id, $order_type, "");
                $goods_beyond = $this->_check_beyond_stock($goods_info['items']);
            }
            if ($goods_beyond) {
                $str_tmp = '';
                foreach ($goods_beyond as $goods) {
                    $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods
                        ['stock'];
                }
                $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
                return;
            }
            $this->_curlocal(
                LANG::get('create_order')
            );

            $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
            //      $this->assign('goods_info', $goods_info);
            $this->assign('order_goods', $this->_get_order_goods($stores_ids, $order_type, "", $this -> getGoods($cart_list,$spec_ids)));
            $totalSP = 0;
            $totalPrice = 0;
            $flag = 1;
            foreach($this->_get_order_goods($stores_ids, $order_type, "", $this -> getGoods($cart_list,$spec_ids)) as $gds){
                $totalPrice = $totalPrice + $gds['subtotal'];
                if($gds['pd_id'] == 5){
                    $flag = 2;
                }
            }
            $this -> assign('flag',$flag);
            foreach($stores_ids as $storeId){
                $shiping = array(); //总运费
                $jkxd_shipping = array();//手机版集客小店订单中商品的运费
                $normal_shipping=array();//移动商城订单中商品的运费
                foreach($this->_get_order_goods($stores_ids, $order_type,"", $this -> getGoods($cart_list,$spec_ids)) as $gds){
                    if($gds['store_id'] == $storeId){
                        if( $gds['shop_id'] && $gds['shop_id'] > 0 ){
                            array_push($jkxd_shipping,$gds['shipping']);
                        }else{
                            array_push($normal_shipping,$gds['shipping']);
                        }
                    }
                }
                if( count( $normal_shipping ) > 0 ){
                    rsort($normal_shipping);
                    array_push($shiping,current($normal_shipping));
                }
                if( count($jkxd_shipping) > 0){
                    $shiping = array_merge($shiping,$jkxd_shipping);
                }
                if(count( $shiping ) > 0){
                    foreach( $shiping as $sp){
                        $totalSP = $totalSP + $sp;
                    }
                }
            }
            $specId = "";
            foreach($spec_ids as $spec_id){
                $specId = $specId . $spec_id ."|";
            }
            $this->assign('spec_ids', $specId);
            $this->assign('totalPrice', $totalPrice);//商品总价格
            $this->assign('totalSP', $totalSP);//总运费
            $this->assign('total', $totalSP + $totalPrice);//订单总金额
            $this->assign('my_address', $order_type->get_address());
            $this->assign('regions', $order_type->get_regions());

            $this->display("order.form.html");
        } else {
            $this->show_message("请重新提交购物车订单");
            header("Location:index.php?app=cart");
        }
    }


    function dropCart($spec_id, $cart_list)
    {
        /* 从购物车中删除 */
        if($cart_list != ''){
            $list = explode('|',$cart_list);
            if($list != ''){
                foreach($list as $k => $li){
                    $param = explode(',',$li);
                    $speId = 0;
                    foreach($param as $pa){
                        $element = explode('^',$pa);
                        if($element[0] == 'spec_id'){
                            $speId = $element[1];
                        }
                    }
                    if($spec_id == $speId){
                        array_splice($list, $k, 1);
                    }
                }
                array_pop($list);
                if(count($list) < 1){$cart_item = '';}else{$cart_item = join('|',$list) . '|';}
                return $cart_item;
            }
        }
    }

    function getGoods($cart_list, $spec_ids){
        $list = explode('|',$cart_list);
        $good_list = array();
        $totalPrice = 0;
        foreach($spec_ids as $s_id){
            foreach($list as $ls){
                if($ls != ''){
                    $lt = explode(',',$ls);
                    foreach($lt as $param){
                        $elmet = explode('^',$param);
                        if($elmet[0] == 'goods_id'){
                            $goods_model = & m('goods');
                            $goods = $goods_model -> get($elmet[1]);
                        }
                        if($elmet[0] == 'store_id'){
                            $store_id = $elmet[1];
                        }
                        if($elmet[0] == 'spec_id'){
                            $spec_id = $elmet[1];
                            $goods_spec = & m('goodsspec');
                            $spec = $goods_spec->get($spec_id);
                        }
                        if($elmet[0] == 'quantity'){
                            $quantity = $elmet[1];
                        }
                        if($elmet[0] == 'price'){
                            $price = $elmet[1];
                        }
                        if($elmet[0] == 'specification'){
                            $specification = $elmet[1];
                        }
                        if($elmet[0] == 'shop_id'){
                            $shop_id = $elmet[1];
                        }
                    }
                    //                       echo '<br>'.$store . '----' .$store_id.'<br>';
                    if($s_id == $spec_id){
                        $goods['spec_id'] = $spec_id;
                        $goods['specification'] = $specification;
                        $goods['shop_id'] = $shop_id;//标示来自于哪个集客小店的商品
                        $goods['quantity'] = $quantity;
                        $goods['description'] = "";
                        $goods['price'] = $spec['price'];
                        $goods['subtotal'] = intval($quantity) * doubleval($spec['price']);
                        array_push($good_list,$goods);
                    }
                }
            }
        }
        return $good_list;
    }

    function add_address(){
        $spec_ids = $_GET['spec_ids'];
        if($spec_ids == ""){
            $spec_ids = $_POST['spec_ids'];
        }
        $order_type     =&  ot('normal');
        $this->assign('regions',$order_type->get_regions());
        $this->assign('spec_ids',$spec_ids);
        $this -> display("xz_add.html");
    }

    /**
     *    丢弃购物车中的商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function drop()
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        $rec_id = isset($_GET['rec_id']) ? intval($_GET['rec_id']) : 0;
        if (!$rec_id)
        {
            return;
        }
        /* 从购物车中删除 */
        $cart_list = ecm_getcookie('cart');
        if($cart_list != ''){
            $list = explode('|',$cart_list);
            if($list != ''){
                foreach($list as $k => $li){
                    $param = explode(',',$li);
                    $speId = 0;
                    foreach($param as $pa){
                        $element = explode('^',$pa);
                        if($element[0] == 'spec_id'){
                            $speId = $element[1];
                        }
                    }
                    if($rec_id == $speId){
                        array_splice($list, $k, 1);
                    }
                }
                array_pop($list);
                if(count($list) < 1){$cart_item = '';}else{$cart_item = join('|',$list) . '|';}
                ecm_setcookie('cart',$cart_item,time() + 86400);
            }
        }
        $this->json_result(array(
            'cart'  =>  array(),                      //返回总的购物车状态
            'amount'=>  0   //返回指定店铺的购物车状态
        ),'drop_item_successed');
    }

    function selected($spec_ids)
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        $cart_list = ecm_getcookie('cart');
        if($cart_list != ''){
            $ca_list = array();
            $list = explode('|',$cart_list);
            if($list != ''){
                foreach($list as $k => $li){
                    $param = explode(',',$li);
                    $speId = 0;
                    foreach($param as $pa){
                        $element = explode('^',$pa);
                        if($element[0] == 'spec_id'){
                            $speId = $element[1];
                        }
                    }
                    foreach($spec_ids as $rec_id){
                        if($rec_id == $speId){
                            array_push($ca_list,$list[$k]);
                        }
                    }
                }
                if(count($ca_list) < 1){$cart_item = '';}else{$cart_item = join('|',$ca_list) . '|';}
                return $cart_item;
            }
        }
        return "";
    }

    function ok(){
        $order_sn = $_GET['order_sn'];
        $sn = explode(",",$order_sn);
        $order_model = & m('order');
        $total = 0;
        $order_sn_all = "";
        foreach($sn as $s){
            if($s != ""){
                $order = $order_model -> get("order_sn = " . $s);
                if($order_sn_all == ""){
                    $order_sn_all = $order['order_sn'];
                }else{
                    $order_sn_all = $order_sn_all . "," .$order['order_sn'];
                }
                $total = $total + $order['order_amount'];
            }
        }
//        $this -> assign("order1",$order);
        if(count($sn) > 1){
            $this -> assign("flag","...");
        }
        $this -> assign("order_sn_all",$order_sn_all);
        $this -> assign("total",$total);
        $this -> assign("order_sn",$sn);
        $this -> display("order.ok.html");
    }
}

?>
