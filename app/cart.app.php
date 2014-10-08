<?php

/**
 *    购物车控制器，负责会员购物车的管理工作，她与下一步售货员的接口是：购物车告诉售货员，我要买的商品是我购物车内的商品
 *
 *    @author    Garbin
 */

class CartApp extends MallbaseApp
{
    /**
     *    列出购物车中的商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        if(CART_IS_COOKIE == 'ON'){
            $cart_list = ecm_getcookie('cart');
            if($cart_list != ''){
                $carts = $this->getCookieGoods($cart_list);
                $this->_curlocal(
                    LANG::get('cart')
                );
                $this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));
                $this->assign('carts', $carts);

                $this->display('cart1.index.html');
            }else{
                $this->_cart_empty();
                return;
            }
        }else{
            $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
            $carts = $this->_get_carts($store_id);
            $this->_curlocal(
                LANG::get('cart')
            );
            $this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));
            if (empty($carts))
            {
                $this->_cart_empty();
                return;
            }

            $this->assign('carts', $carts);
            $this->display('cart.index.html');
        }
    }

    /**
     *    放入商品(根据不同的请求方式给出不同的返回结果)
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        $spec_id   = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
        $type =  isset($_GET['type']) ? intval($_GET['type']) : 0;
        if (!$spec_id || !$quantity)
        {
            return;
        }
            /* 是否有商品 */
            $spec_model =& m('goodsspec');
            $spec_info  =  $spec_model->get(array(
                'fields'        => 'g.store_id, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,g.is_send, gs.spec_1, gs.spec_2, gs.stock, gs.price',
                'conditions'    => $spec_id,
                'join'          => 'belongs_to_goods',
            ));

         if($spec_info['goods_id'] == Conf::get('MinGoodsId')){
            if ($quantity > 2) {
                $this->json_error('此类商品限购2套！');
                return;
            }
         }

            if (!$spec_info)
            {
                $this->json_error('no_such_goods');
                /* 商品不存在 */
                return;
            }

        /* 如果是自己店铺的商品，则不能购买 */
        if ($this->visitor->get('manage_store'))
        {
            if ($spec_info['store_id'] == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }


        /* 是否添加过 */
        /*cookie方式*/
        if(CART_IS_COOKIE == 'ON'){
            $cart_list = ecm_getcookie('cart');
            if($cart_list != ''){
                $list = explode('|',$cart_list);
                if(count($list) > 0){
                    foreach($list as $ls){
                        $l = explode(',',$ls);
                        foreach($l as $pa){
                            $pm = explode('^',$pa);
                            if($pm[0] == 'spec_id' && $pm[1] == $spec_id){
                                $this->json_error('goods_already_in_cart');
                                return;
                            }
                        }
                    }
                }
            }
        }else{
            /*数据库方式*/
            $model_cart =& m('cart');
            $item_info  = $model_cart->get("spec_id={$spec_id} AND session_id='" . SESS_ID . "'");
            if (!empty($item_info))
            {
                $this->json_error('goods_already_in_cart');

                return;
            }
        }
        if($type==0) {
         if ($quantity > $spec_info['stock'])
            {
            $this->json_error('no_enough_goods');
            return;
            }
        }
        $spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
        $spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];
        $specification = $spec_1 . ' ' . $spec_2;
        /* 将商品加入购物车 */
        if(CART_IS_COOKIE == 'ON'){
            /*放入cookie,格式为
            '商品属性名^属性值,商品属性名^属性值|商品属性名^属性值,商品属性名^属性值|'*/
            $cart_item = 'user_id^' . $this->visitor->get('user_id');
            $cart_item .= ',store_id^' . $spec_info['store_id'];
            $cart_item .= ',spec_id^' . $spec_id;
            $cart_item .= ',goods_id^' . $spec_info['goods_id'];
            $cart_item .= ',specification^' . addslashes(trim($specification));
            $cart_item .= ',price^' . $spec_info['price'];
            $cart_item .= ',quantity^' .  $quantity;

            //判断是否是集客小店的商品
            if($_GET["shop_id"] && $_GET["shop_id"]>0){
                $cart_item .= ',shop_id^' . $_GET["shop_id"];
            }
            //判断是否是精品集客小店的商品 ( vshop == 1 为精品集客小店的标识)
            if( $_GET["vshop"] && $_GET["vshop"] == "1" ){
                $cart_item .= ',vshop^' . $_GET["vshop"];
            }
            $cart_item .= ',is_send^' . addslashes($spec_info['is_send']) . '|';

            if($cart_list == ''){
                $cart_list = $cart_item;
            }else{
                $cart_list .= $cart_item;
            }

            ecm_setcookie('cart',$cart_list,time() + 86400);
//            echo ecm_getcookie('cart');exit;
            $count = 0;
            $amount = 0;
            $kinds = 0;
            $a = explode('|',$cart_list);
            foreach($a as $b){
                $kinds = 1 + intval($kinds);
                $c = explode(',',$b);
                $cn = 0;
                $price = 0;
                foreach($c as $d){
                    $e = explode('^',$d);
                    if($e[0] == 'quantity') {
                        $count = intval($count) + intval($e[1]);
                        $cn = intval($e[1]);
                    }
                    if($e[0] == 'price'){
                        $price = doubleval($e[1]);
                    }
                }
                $amount = doubleval($amount) + intval($cn) * doubleval($price);
            }
            $cart_status = array(
                'status'    =>  array(
                    'quantity'  =>  $count,      //总数量
                    'amount'    =>  $amount,      //总金额
                    'kinds'     =>  intval($kinds) - 1,      //总种类
                ),
                'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
            );
        } else{
            /*放入数据库*/
            $cart_item = array(
                'user_id'       => $this->visitor->get('user_id'),
                'session_id'    => SESS_ID,
                'store_id'      => $spec_info['store_id'],
                'spec_id'       => $spec_id,
                'goods_id'      => $spec_info['goods_id'],
                'goods_name'    => addslashes($spec_info['goods_name']),
                'specification' => addslashes(trim($specification)),
                'price'         => $spec_info['price'],
                'quantity'      => $quantity,
                'goods_image'   => addslashes($spec_info['default_image']),
                'is_send'   => addslashes($spec_info['is_send']),
                'shop_id'   => $_GET['shop_id'],
                'vshop'   => $_GET['vshop'],
            );

            /* 添加并返回购物车统计即可 */
            $cart_model =&  m('cart');
            $cart_model->add($cart_item);
            $cart_status = $this->_get_cart_status();
        }
            /* 更新被添加进购物车的次数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');

        $this->json_result(array(
            'cart'      =>  $cart_status['status'],  //返回购物车状态
        ), 'addto_cart_successed');
    }

    /**
     *    丢弃商品
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
        if(CART_IS_COOKIE == 'ON'){
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
        }else{
            $model_cart =& m('cart');
            $droped_rows = $model_cart->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
            if (!$droped_rows)
            {
                return;
            }

            /* 返回结果 */
            $dropped_data = $model_cart->getDroppedData();
            $store_id     = $dropped_data[$rec_id]['store_id'];
            $cart_status = $this->_get_cart_status();
            $this->json_result(array(
                'cart'  =>  $cart_status['status'],                      //返回总的购物车状态
                'amount'=>  $cart_status['carts'][$store_id]['amount']   //返回指定店铺的购物车状态
            ),'drop_item_successed');
        }
    }

    /**
     *    更新购物车中商品的数量，以商品为单位，AJAX更新
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function update()
    {
        $spec_id  = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        if (!$spec_id || !$quantity)
        {
            /* 不合法的请求 */
            return;
        }

        /* 判断库存是否足够 */
        $model_spec =& m('goodsspec');
        $spec_info  =  $model_spec->get($spec_id);
        if($spec_info['goods_id'] == Conf::get('MinGoodsId')){
            if ($quantity > 2) {
                $this->json_error('此类商品限购2套！');
                return;
            }
        }
        $goods_mod = &m('goods');
        $gd = $goods_mod->get($spec_info['goods_id']);


        if (empty($spec_info))
        {
            /* 没有该规格 */
            $this->json_error('no_such_spec');
            return;
        }

        if ($gd["pd_id"]!=5) {
            if ($quantity > $spec_info['stock'])
            {
                /* 数量有限 */
                $this->json_error('no_enough_goods');
                return;
            }
        }

        /*cookie方式修改*/
        if(CART_IS_COOKIE == 'ON'){
            $cart_list = ecm_getcookie('cart');
            if($cart_list != ''){
                $list = explode('|',$cart_list);
                if($list != ''){
                    $subtotal = 0;
                    $amount = 0;
                    $cart = array('quantity' => 0, 'amount' => 0, 'kinds' => 0);
                    $kinds = 0;
                    $q = 0;
                    $a = 0;
                    foreach($list as $k => $ls){
                        $kinds = $kinds + 1;
                        $lt = explode(',',$ls);
                        foreach($lt as $k1 => $param){
                            $elmet = explode('^',$param);
                            if($elmet[0] == 'goods_id'){
                               $goodsId = $elmet[1];
                            }
                            if($elmet[0] == 'store_id'){
                                $store_id = $elmet[1];
                            }
                            if($elmet[0] == 'spec_id'){
                                $spec_id1 = $elmet[1];
                                $goods_spec = & m('goodsspec');
                                $spec = $goods_spec->get($spec_id);
                            }
                            if($elmet[0] == 'quantity'){
                                $quantity1 = $elmet[1];
                            }
                            if($elmet[0] == 'price'){
                                $price = $spec['price'];
                            }

                            if($elmet[0] == 'specification'){
                                $specification = $elmet[1];
                            }
                            if($elmet[0] == 'is_send'){
                                $isSend = $elmet[1];
                            }
                        }
                        if($spec_id1 == $spec_id){
                            $quantity1 = $quantity;
                            $cart_item = 'user_id^' . $this->visitor->get('user_id');
                            $cart_item .= ',store_id^' . $store_id;
                            $cart_item .= ',spec_id^' . $spec_id1;
                            $cart_item .= ',goods_id^' . $goodsId;
                            $cart_item .= ',specification^' . $specification;
                            $cart_item .= ',price^' .$price;
                            $cart_item .= ',quantity^' .  $quantity1;
                            $cart_item .= ',is_send^' . $isSend;
                            $list[$k] = $cart_item;
                            $amount = intval($quantity1) * doubleval($price);
                            $subtotal = $amount;
                        }
                            $q .= intval($quantity1);
                            $a .= doubleval($price) * intval($quantity1);
                    }
                    array_pop($list);
//                    print_r($list);exit;
                    $cartList = join('|',$list) . '|';
                    ecm_setcookie('cart',$cartList,time() + 86400);
                    $cart['quantity'] = $q;
                    $cart['amount'] = $a;
                    $cart['kinds'] = $kinds;
                }
            }
            $this->json_result(array(
                'cart'      =>  $cart,                     //返回总的购物车状态
                'subtotal'  =>  $subtotal,                                  //小计
                'amount'    =>  $amount  //店铺购物车总计
            ), 'update_item_successed');
        }else{
            /* 数据库方式修改数量 */
            $where = "spec_id={$spec_id} AND session_id='" . SESS_ID . "'";
            $model_cart =& m('cart');

            /* 获取购物车中的信息，用于获取价格并计算小计 */
            $cart_spec_info = $model_cart->get($where);
            if (empty($cart_spec_info))
            {
                /* 并没有添加该商品到购物车 */
                return;
            }

            $store_id = $cart_spec_info['store_id'];

            /* 修改数量 */
            $model_cart->edit($where, array(
                'quantity'  =>  $quantity,
            ));
            /* 小计 */
            $subtotal   =   $quantity * $cart_spec_info['price'];
            /* 返回JSON结果 */
            $cart_status = $this->_get_cart_status();
            $this->json_result(array(
                'cart'      =>  $cart_status['status'],                     //返回总的购物车状态
                'subtotal'  =>  $subtotal,                                  //小计
                'amount'    =>  $cart_status['carts'][$store_id]['amount']  //店铺购物车总计
            ), 'update_item_successed');
        }
    }

    /**
     *    获取购物车状态
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_cart_status()
    {
        /* 默认的返回格式 */
        $data = array(
            'status'    =>  array(
                'quantity'  =>  0,      //总数量
                'amount'    =>  0,      //总金额
                'kinds'     =>  0,      //总种类
            ),
            'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
        );

        /* 获取所有购物车 */
        $carts = $this->_get_carts();
        if (empty($carts))
        {
            return $data;
        }
        $data['carts']  =   $carts;
        foreach ($carts as $store_id => $cart)
        {
            $data['status']['quantity'] += $cart['quantity'];
            $data['status']['amount']   += $cart['amount'];
            $data['status']['kinds']    += $cart['kinds'];
        }

        return $data;
    }

    /**
     *    购物车为空
     *
     *    @author    Garbin
     *    @return    void
     */
    function _cart_empty()
    {
        $this->display('cart.empty.html');
    }

    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();

        /* 获取所有购物车中的内容 */
        $where_store_id = $store_id ? ' AND cart.store_id=' . $store_id : '';

        /* 只有是自己购物车的项目才能购买 */
        $where_user_id = $this->visitor->get('user_id') ? " AND cart.user_id=" . $this->visitor->get('user_id') : '';
        $cart_model =& m('cart');
        $cart_items = $cart_model->find(array(
            'conditions'    => 'session_id = \'' . SESS_ID . "'" . $where_store_id . $where_user_id,
            'fields'        => 'this.*,store.store_name',
            'join'          => 'belongs_to_store',
        ));
        if (empty($cart_items))
        {
            return $carts;
        }
        $kinds = array();
        foreach ($cart_items as $item)
        {
            /* 小计 */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            $kinds[$item['store_id']][$item['goods_id']] = 1;

            /* 以店铺ID为索引 */
            empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts[$item['store_id']]['goods'][]    = $item;
        }

        foreach ($carts as $_store_id => $cart)
        {
            $carts[$_store_id]['kinds'] =   count(array_keys($kinds[$_store_id]));  //各店铺的商品种类数
        }

        return $carts;
    }

    function getCookieGoods($cart_list){
        $stores = array();
        $list = explode('|',$cart_list);
        foreach($list as $ls){
            $lt = explode(',',$ls);
            foreach($lt as $param){
                $elmet = explode('^',$param);
                    if($elmet[0] == 'store_id'){
                        array_push($stores,$elmet[1]);
                }
            }
        }
       $stores = array_unique($stores);
       if(count($stores) > 0){
           $data = array();
           foreach($stores as $store){
               $good_list = array();
               foreach($list as $ls){
                   if($ls != ''){
//                       print_r($ls);
                       $lt = explode(',',$ls);
                       $shop_id = null;
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
                               $price = $spec['price'];
                           }
                           if($elmet[0] == 'shop_id'){
                               $shop_id = $elmet[1];
                           }
                           if($elmet[0] == 'specification'){
                               $specification = $elmet[1];
                           }
                       }
                       if($store == $store_id){
                           $goods['spec_id'] = $spec_id;
                           $goods['specification'] = $specification;
                           $goods['quantity'] = $quantity;

                           $goods['subtotal'] = intval($quantity) * doubleval($price);
                           $goods['price'] = $price;
                           $goods['shop_id'] = $shop_id;        //标识是从那个集客小店点击进来的商品
                           array_push($good_list,$goods);
                       }
                   }
              }
               $list1 = array();
               $store_model = & m('store');
               $myStore = $store_model -> get($store);
               $list1['store_name'] = $myStore['store_name'];
               $list1['goods'] = $good_list;
               $data[$store] = $list1;
           }
       }

//         $this->pr($data);
//         exit;

        return $data;
    }

    function cart_kinds(){
        if(CART_IS_COOKIE == 'ON'){
            $cart_list = ecm_getcookie('cart');
//            echo $cart_list;exit;
            $kids = 0;
            if($cart_list != ''){
                $cart = explode('|',$cart_list);
                if(count($cart) > 0){
                    foreach($cart as $ct){
                        $kids = intval($kids) + 1;
                    }
                }
                $kids = intval($kids) - 1;
            }
        }else{
            $cart =& m('cart');
            $kids = $cart->get_kinds(SESS_ID, $this->visitor->get('user_id'));
        }
        echo $kids;
    }
}

?>
