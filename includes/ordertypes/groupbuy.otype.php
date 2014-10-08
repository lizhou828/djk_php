<?php

include_once(ROOT_PATH . '/includes/ordertypes/normal.otype.php');

/**
 *    团购的订单
 *
 *    @author    Garbin
 *    @usage    none
 */
class GroupbuyOrder extends NormalOrder
{
    var $_name = 'groupbuy';

    function submit_order($data)
    {
        /* 释放goods_info和post两个变量 */
        extract($data);
        /* 处理订单基本信息 */

        $base_info = $this->_handle_order_info($goods_info, $post);

        if (!$base_info)
        {
            /* 基本信息验证不通过 */

            return 0;
        }
//        $this->print_r($base_info);
        /* 处理订单收货人信息 */

        $consignee_info = $this->_handle_consignee_info($goods_info, $post);
        if (!$consignee_info)
        {
            /* 收货人信息验证不通过 */
            return 0;
        }
        /* 至此说明订单的信息都是可靠的，可以开始入库了 */

        /* 插入订单基本信息 */
        //订单总实际总金额，可能还会在此减去折扣等费用
        $base_info['order_amount']  =   $base_info['goods_amount']+$consignee_info["shipping_fee"] ;

        /* 如果优惠金额大于商品总额和运费的总和 */
        if ($base_info['order_amount'] < 0)
        {
            $base_info['order_amount'] = 0;
            $base_info['discount'] = $base_info['goods_amount'] ;
        }

        $order_model =& m('order');
        $order_id    = $order_model->add($base_info);

        if (!$order_id)
        {
            /* 插入基本信息失败 */
            $this->_error('create_order_failed');
            return 0;
        }

        /* 插入收货人信息 */
        $consignee_info['order_id'] = $order_id;
        $order_extm_model =& m('orderextm');
        $order_extm_model->add($consignee_info);

        /* 插入商品信息 */
        $goods_items = array();
        foreach ($goods_info['items'] as $key => $value)
        {
            $goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['spec_id'],
                'specification' =>  $value['specification'],
                'price'         =>  $value['price'],
                'quantity'      =>  $value['quantity'],
                'goods_image'   =>  $value['goods_image'],
                'is_send' => $value['is_send'],
                'cellphone' => $post['cellphone'],
            );
        }
        $order_goods_model =& m('ordergoods');
        $order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入
        return $order_id;
    }
}

?>
