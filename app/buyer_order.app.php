<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Buyer_orderApp extends MemberbaseApp
{
    function __construct(){
        $this->Buyer_orderApp();
    }

    function Buyer_orderApp(){
        parent::__construct();
    }

    function index()
    {
        /* 获取订单列表 */
        $this->_get_orders();

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_order',
                         LANG::get('order_list'));

        /* 当前用户中心菜单 */
        if($_GET["app"] == "jkxd" && $_GET["act"] == "jkxd_order"){
            $this->_curitem('jkxd_order');
        }else{
            $this->_curitem('my_order');
        }
        if($_GET["shouhou"]==1){
            $this->_curmenu('shouhou_order');
        }else{
            $this->_curmenu($_GET["type"]);
        }

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_order'));
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


        $member_mod =&m('member');
        $user = $member_mod->get_info($this->visitor->get('user_id'));
        $this->assign("user",$user);

        if($_GET["app"] == "jkxd" && $_GET["act"] == "jkxd_order"){
            $this->display('jkxd.jkxd_order.html');
        }else{
            $this->display('buyer_order.index.html');
        }


    }


    //单个订单详情
    function order_goods(){
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order =& m('order');
        //$order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        $order_info = $model_order->get(array(
            'fields'        => "*, order.add_time as order_add_time",
            'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join'          => 'belongs_to_store',
        ));
        if (!$order_info)
        {
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
        $this->_curitem('my_order');

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

                    if($ordershouhou["status"]==0 || $ordershouhou["status"]==1)
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
        $this->_curitem('my_order');

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
            $this->display('buyer_order.shouhou.html');
        }else{
            $orderId=$_POST["order_id"];
            $goodsId=$_POST["goods_id"];

            if($orderId == "" || $goodsId == ""){
                $this->display('buyer_order.shouhou.html');
                echo "<script>js_fail(\"订单号错误\");</script>";
                exit;
            }

            $ordergoods_mod =& m('ordergoods');
            $ordergoods=$ordergoods_mod->get(array(
                "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId "
            ));

            if(!$ordergoods){
                $this->display('buyer_order.shouhou.html');
                echo "<script>js_fail(\"订单号商品不存在\");</script>";
                exit;
            }
            $order=$model_order->get_info($orderId);
            $date=array(
                "buyer_id"=>$order["buyer_id"],
                "seller_id"=>$order["seller_id"],
                "order_id"=>$_POST["order_id"],
                "goods_id"=>$_POST["goods_id"],
                "goods_price"=>$ordergoods["price"],
                "content_post"=>$_POST["content_post"],
                "type"=>$_POST["type"],
                "jine"=>$_POST["jine"],
                "time_post"=>gmtime()
            );

            $id="";
            if($_POST["shouhou_id"]){
                $date["status"]=0;
                $shouhou_mod->edit($_POST["shouhou_id"],$date);
                $id=$_POST["shouhou_id"];
            }else{
                $id=$shouhou_mod->add($date);
            }

            $imgs = $this->_upload_image($order["buyer_id"],'content_img');

            //$this->pr($imgs);

            if(count($imgs)>0){
                $imgData=array();

                $shouhou=$shouhou_mod->get_info($id);

                if($imgs["content_img1"]!="")
                {
                    $imgData["content_img1"]=$imgs["content_img1"];
                    $this->deleteFileInfo($shouhou['content_img1']);
                }
                if($imgs["content_img2"]!="")
                {
                    $imgData["content_img2"]=$imgs["content_img2"];
                    $this->deleteFileInfo($shouhou['content_img2']);
                }
                $shouhou_mod->edit($id,$imgData);
            }

            if($id>0){
                $this->assign('msg', "订单售后提交处理成功！<br><a href='index.php?app=buyer_order&act=shouhou&order_id={$orderId}&goods_id={$goodsId}&shouhou_id={$id}'>返回继续操作</a>");
                $_POST="";
                $this->display('buyer_order.shouhou.success.html');
            }
        }

    }

    function quxiaoshouhou(){

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');

        $user_info=$this->visitor->info;
        $shouhou_mod = & m('ordershouhou');
        if($_POST["shouhou_id"] && $user_info["user_id"] >0 ){
            $id=$_POST["shouhou_id"];
            $rs=$shouhou_mod->edit($id,array("status"=>-1,"buyer_id"=>$user_info["user_id"]));
            if($rs >0 ){
                $this->assign('msg', "取消订单售后成功！<br><a href='index.php?app=buyer_order'>返回订单列表继续操作</a>");
                $_POST="";
                $this->display('buyer_order.shouhou.success.html');
            }else{
                $this->assign('msg', "已经取消订单售后成功，请勿重复操作！<br><a href='index.php?app=buyer_order'>返回订单列表继续操作</a>");
                $_POST="";
                $this->display('buyer_order.shouhou.success.html');
            }
        }
    }

    function update_shouhou(){

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');

        $shouhou_mod = & m('ordershouhou');
        if($_POST){
            $orderId   = $_POST["order_id"];
            $goodsId   = $_POST["goods_id"];
            $shouhouId = $_POST["shouhou_id"];
            $status = $_POST["status"];

            //$this->pr($_POST);

            if($orderId == "" || $goodsId == "" || $shouhouId == "" || $status == ""){
                $this->show_warning('订单号错误!');
                exit;
            }

            $ordergoods_mod =& m('ordergoods');
            $goodscount=$ordergoods_mod->getOne("select count(1) from ".DB_PREFIX."order_goods  WHERE  order_id=$orderId  AND goods_id=$goodsId");

            if($goodscount!=1){
                $this->show_warning('当前商品和订单不一致!');
                exit;
            }

            if($status!=0 && $status!=2){
                $this->show_warning('操作错误!');
                exit;
            }
            $rs=$shouhou_mod->edit($shouhouId,array(
                "status"=>$status));
            if( $rs >0 ){
                $this->assign('msg', "处理成功！<br><a href='index.php?app=buyer_order&act=shouhou&order_id=$orderId&goods_id=$goodsId&shouhou_id=$shouhouId'>返回订单列表继续操作</a>");
                $_POST="";
                $this->display('buyer_order.shouhou.success.html');
            }else{
                $this->assign('msg', "处理成功，请勿重复处理！<br><a href='index.php?app=buyer_order&act=shouhou&order_id=$orderId&goods_id=$goodsId&shouhou_id=$shouhouId'>返回订单列表继续操作</a>");
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
        //$order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        $order_info = $model_order->get(array(
            'fields'        => "*, order.add_time as order_add_time",
            'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join'          => 'belongs_to_store',
            ));
        $order_goods_model = &m ("ordergoods");
        $order_info['jifen'] =$order_goods_model->getOne("SELECT SUM(jifen) FROM ".DB_PREFIX."order_goods WHERE order_id=$order_id");
        if (!$order_info)
        {
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
        $this->_curitem('my_order');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
        $invoice_no =$order_info['invoice_no'];
        if($invoice_no && strpos($invoice_no,'|')) {
            $invoice_nos = explode("|",$invoice_no);
            $order_info['invoice_no'] = $invoice_nos[1];
            $this->assign('kd',$invoice_nos[0]);
        }
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('buyer_order.view.html');
    }


    //去掉积分
    function cancel_jifen($order_id){
        $model_member =& m('member');
        $model_order    =&  m('order');
        if($order_id!=""){

            $order=$model_order->get("order_id=$order_id AND buyer_id=" . $this->visitor->get('user_id'));

            if(!$order || $order["status"] == 0 ){
                $this->show_warning('订单不存在！');
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
                  echo $jifenlog_mod->add($data);              //记录积分log
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
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            /*echo Lang::get('no_such_order');
            return;*/
            echo "<script type=\"text/javascript\">window.parent.js_fail('".Lang::get('no_such_order')."');</script>";
            exit;

        }
        $model_order    =&  m('order');
        /* 只有待付款的订单可以取消 */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status " . db_create_in(array(ORDER_PENDING, ORDER_SUBMITTED)));
        if (empty($order_info))
        {
            /*echo Lang::get('no_such_order');
            return;*/
            echo "<script type=\"text/javascript\">window.parent.js_fail('".Lang::get('no_such_order')."');</script>";
            exit;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=utf-8');
            $this->assign('order', $order_info);
            $this->display('buyer_order.cancel.html');
        }
        else
        {
            echo "<meta charset='utf-8' />";
            $type=$_POST["type"];
            $model_member =& m('member');
            $model_yuelog =& m('yuelog');
            $order_info=$model_order->get("order_id=$order_id and status !=0");
            if(!$order_info){
                echo "<script type=\"text/javascript\">window.parent.js_fail('订单已取消');</script>";
                exit;
            }

            $flag = 0;  //是否调用接口      默认0不调用
            if($order_info["status"]!=11 || ($order_info["koushui_yue"] > 0) || $order_info["yue"] >0 || $order_info["jifen"]>0){
                $flag =1;
            }

            /* $model_order->edit($order_id, array('status' => ORDER_CANCELED));
            if ($model_order->has_error()){
                echo "<script type=\"text/javascript\">window.parent.js_fail('".$model_order->get_error()."');</script>";
                exit;
            }*/

            // 加回商品库存
            $model_order->change_stock('+', $order_id);
            $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
            // 记录订单操作日志
            /* $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_CANCELED),
                'remark'    => $cancel_reason,
                'log_time'  => gmtime(),
            )); */


            /* 发送给卖家订单取消通知 */
            $seller_info   = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_cancel_order_notify', array('order' => $order_info, 'reason' => $_POST['remark']));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            
            if($flag!=1){
				$model_order->edit($order_id, array('status' => ORDER_CANCELED));
				if ($model_order->has_error()){
					echo "<script type=\"text/javascript\">window.parent.js_fail('".$model_order->get_error()."');</script>";
					exit;
				}
				$order_log =& m('orderlog');
				$order_log->add(array(
					'order_id'  => $order_id,
					'operator'  => addslashes($this->visitor->get('user_name')),
					'order_status' => order_status($order_info['status']),
					'changed_status' => order_status(ORDER_CANCELED),
					'remark'    => $cancel_reason,
					'log_time'  => gmtime(),
				));
                $new_data = array(
                    'status'    => Lang::get('order_canceled'),
                    'actions'   => array(), //取消订单后就不能做任何操作了
                );
				$model_member->commit_transaction();
                $this->pop_warning('ok');
                exit;
            }
			$model_member->commit_transaction();
            //取消订单直接调用java那边，忽略返回
            $params["type"] = "cancel_order";      //confirmOrder   cancelOrder
            $params["userId"] = $this->visitor->get('user_id');
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

            $this->pop_warning('ok');
        }

    }

    /**
     *    确认订单
     *
     *    @author    Garbin
     *    @return    void
     */
    function confirm_order()
    {
        echo "<meta charset='utf-8' />";
        $choujiangquan_mod =& m('choujiangquan');
        $this->assign('TO_JIESUAN_URL', TO_JIESUAN_URL);
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
//            echo Lang::get('no_such_order');
//            return;
            $this->pop_warning(Lang::get('no_such_order'));
            exit;
        }
        $model_order    =&  m('order');
        /* 只有已发货的订单可以确认 */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status=" . ORDER_SHIPPED);
        if (empty($order_info))
        {
            /*echo Lang::get('no_such_order');

            return;*/
            $this->pop_warning(Lang::get('no_such_order'));
            exit;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buyer_order.confirm.html');
        }
        else
        {

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
            // $data = json_decode($pageContents);
            /*if(!$data || $data->code != 200){
                echo $pageContents;
                $this->pop_warning("服务器繁忙,请稍后重试【".$data->code."】!");
                exit;
            }*/


            $this->pop_warning('ok','','index.php?app=buyer_order&act=evaluate&order_id='.$order_id);

        }

    }

    /**
     *    给卖家评价
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function evaluate()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 验证订单有效性 */
        $model_order =& m('order');
        $order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if ($order_info['status'] != ORDER_FINISHED)
        {
            /* 不是已完成的订单，无法评价 */
            $this->show_warning('cant_evaluate');

            return;
        }
        if ($order_info['evaluation_status'] != 0)
        {
            /* 已评价的订单 */
            $this->show_warning('already_evaluate');

            return;
        }
        $model_ordergoods =& m('ordergoods');

        if (!IS_POST)
        {
            /* 显示评价表单 */
            /* 获取订单商品 */
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($goods_list as $key => $goods)
            {
                empty($goods['goods_image']) && $goods_list[$key]['goods_image'] = Conf::get('default_goods_image');
            }
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                             LANG::get('my_order'), 'index.php?app=buyer_order',
                             LANG::get('evaluate'));
            $this->assign('goods_list', $goods_list);
            $this->assign('order', $order_info);

            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('credit_evaluate'));
            $this->display('buyer_order.evaluate.html');
        }
        else
        {
            $evaluations = array();
            /* 写入评价 */
            foreach ($_POST['evaluations'] as $rec_id => $evaluation)
            {
                if ($evaluation['evaluation'] <= 0 || $evaluation['evaluation'] > 3)
                {
                    $this->show_warning('evaluation_error');

                    return;
                }
                switch ($evaluation['evaluation'])
                {
                    case 3:
                        $credit_value = 1;
                    break;
                    case 1:
                        $credit_value = -1;
                    break;
                    default:
                        $credit_value = 0;
                    break;
                }
                $evaluations[intval($rec_id)] = array(
                    'evaluation'    => $evaluation['evaluation'],
                    'comment'       => $evaluation['comment'],
                    'credit_value'  => $credit_value
                );
            }
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($evaluations as $rec_id => $evaluation)
            {
                $model_ordergoods->edit("rec_id={$rec_id} AND order_id={$order_id}", $evaluation);
                $goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_list[$rec_id]['goods_id']);
                $goods_name = $goods_list[$rec_id]['goods_name'];
                $this->send_feed('goods_evaluated', array(
                    'user_id'   => $this->visitor->get('user_id'),
                    'user_name'   => $this->visitor->get('user_name'),
                    'goods_url'   => $goods_url,
                    'goods_name'   => $goods_name,
                    'evaluation'   => Lang::get('order_eval.' . $evaluation['evaluation']),
                    'comment'   => $evaluation['comment'],
                    'images'    => array(
                        array(
                            'url' => SITE_URL . '/' . $goods_list[$rec_id]['goods_image'],
                            'link' => $goods_url,
                        ),
                    ),
                ));
            }

            /* 更新订单评价状态 */
            $model_order->edit($order_id, array(
                'evaluation_status' => 1,
                'evaluation_time'   => gmtime()
            ));

            /* 更新卖家信用度及好评率 */
            $model_store =& m('store');
            $model_store->edit($order_info['seller_id'], array(
                'credit_value'  =>  $model_store->recount_credit_value($order_info['seller_id']),
                'praise_rate'   =>  $model_store->recount_praise_rate($order_info['seller_id'])
            ));

            /* 更新商品评价数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_list as $goods)
            {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'comments=comments+1');


            $this->show_message('evaluate_successed',
                'back_list', 'index.php?app=buyer_order');
        }
    }

    /**
     *    获取订单列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders()
    {
        $page = $this->_get_page(10);
        $model_order =& m('order');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
        $con = array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按店铺名称搜索
                'field' => 'seller_name',
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
        );

        $conditions = $this->_get_query_conditions($con);

        if($_GET["shouhou"]){
            $conditions.=" and (select count(1) from ".DB_PREFIX."order_shouhou shouhou where order_alias.order_id=shouhou.order_id and shouhou.status in (0,1) )>0";
        }
        $query = "buyer_id=" . $this->visitor->get('user_id');
        if($_GET['order_type'] ){
            $query.= " and type='".$_GET['order_type']."'";
        }
        //如过是集客小店的菜单
        if($_GET["app"] == "jkxd" && $_GET["act"] == "jkxd_order"){
            $query = " shop_id =".$this->visitor->get('user_id');
        }

        if  ($_GET['type'] !='canceled') {
            $conditions.=" and status <> 0";
        }
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => $query."{$conditions}",
            'fields'        => "this.*,
                            (select tel from ".DB_PREFIX."store where order_alias.seller_id=store_id) as store_tel,
                            (order_amount-yue-koushui_yue) as yue_jine ,(yue+koushui_yue) as zhifu,
                            (select count(1) from ecm_pay_log pl where pl.order_sn=order_alias.order_sn and (pl.status = 1 OR (pl.status = 3 AND  ((UNIX_TIMESTAMP(NOW())- UNIX_TIMESTAMP(pl.`create_time`))/3600)<36))) ifPay",
            'count'         => true,
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品from ecm_pay_log pl WHERE  (pl.status = 1 OR (pl.status = 3 AND ((UNIX_TIMESTAMP(NOW())- UNIX_TIMESTAMP(pl.`create_time`))/3600)<36))
            ),
        ));
        foreach ($orders as $key1 => $order){
            if($order['order_goods']) {
            foreach ($order['order_goods'] as $key2 => $goods){
                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
                $shohou_mod = & m('ordershouhou');
                $ordershouhou=$shohou_mod->get(array(
                    "conditions"=>"goods_id=".$goods["goods_id"]." and order_id=".$order["order_id"]." and status not in (-1,2)"
                ));
                if($ordershouhou["id"]!=""){
                    $orders[$key1]['order_goods'][$key2]["shouhou_id"]=$ordershouhou["id"];
                    $orders[$key1]['order_goods'][$key2]["status"]=$ordershouhou["status"];
//                    if($ordershouhou["status"]==0 || $ordershouhou["status"]==1){
                    if($ordershouhou["status"]==0 ){
                        $orders[$key1]['shouhouFlg']=1;
                    }
                }
            }
            }
        }
        $page['item_count'] = $model_order->getCount();
        $this->assign('types', array('all'     => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->_format_page($page);

        //$this->pr($orders);exit;

        $this->assign('page_info', $page);
    }

    function _get_member_submenu()
    {
        $order_app = "index.php?app=buyer_order";
        if($_GET["app"] == "jkxd" && $_GET["act"] == "jkxd_order"){
            $order_app = "index.php?app=jkxd&act=jkxd_order";
        }

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
            array(
                'name'  => 'shouhou_order',
                'url'   =>$order_app.'&shouhou=1',
            ),
        );
        return $menus;
    }

    //异步处理，用户完成订单后不等待返回直接下一步
    function sendManager()
    {
        $orderSn=$_POST["orderSn"]?$_POST["orderSn"]:$_GET["orderSn"];
        $URL = TO_JIESUAN_URL;             //$URL为请求的路径
        try{
            //$URL = Conf::get('sendToJavaURL');//$URL为请求的路径

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);     	 curl_setopt($ch, CURLOPT_URL,$URL);
            curl_setopt($ch, CURLOPT_POST, 1 ); 			     curl_setopt($ch, CURLOPT_HEADER, 0 ) ;
            curl_setopt($ch,CURLOPT_POSTFIELDS, "orderSn=".$orderSn);   //fileName为请求的参数
            $data=curl_exec($ch);
            curl_close($ch);
        }
        catch(Exception $err)
        {
            file_get_contents($URL."?orderSn=".$orderSn);
        }

    }

}

?>
