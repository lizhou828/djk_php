<?php

class StoreApp extends StorebaseApp
{
    /*判断是否登录，没登陆的去登录*/
    function isLogin(){
        $u_id = $_GET['u_id'];
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
            if($u_id != ""){
                header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&u_id=" . $u_id. "&ret_url=" . $ret_url);
            }else{
                header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
            }
        }
    }
    function verify($storeId){
        $store_mod =&m("store");
        $member_mod =&m("member");
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        $this->assign("user",$user);
        if( $storeId && $storeId > 0 ){
            $store = $store_mod->get('store_id='.$storeId." and state=1 and dropState=1 and seller_type=3");
            if( $store && $store['store_id'] >  0 && $store['seller_type'] == 3){
                $this->assign("store",$store);
            }else{
                header("location: {$site_url}/weixin/index.php?app=goods&act=index1");
            }
        }
    }
    //店铺首页
    function index(){
        $storeId = $_GET['id'];
        $this->verify($storeId);

        @session_start();
        $_SESSION['t_id']=$storeId;
        ecm_setcookie("t_id", $storeId, time() + 60*60*24*7);

        $page = $this->_get_good_list($storeId);
        $this->assign("page",$page);
        $this->display("store.index.html");
    }

    //扫码下单页面
    function setOrderPage(){
        $storeId = $_GET['id'];
        if( !$storeId || $storeId <= 0 ||$storeId ==null ){
            echo "<script type='text/javascript'>alert('店铺id参数错误!');history.go(-1)</script>";
            return;
        }else{
            $store_mod =&m("store");
            $store = $store_mod->get('store_id='.$storeId." and state=1 and dropState=1 and seller_type=3");
            if( $store && $store['store_id'] >  0 && $store['seller_type'] == 3){
                $this->assign("store",$store);
            }
        }

        @session_start();
        $_SESSION['t_id']=$storeId;
        ecm_setcookie("t_id", $storeId, time() + 60*60*24*7);

        $this->display("store.setOrderPage.html");
    }
    //扫码下单业务
    function setOrder(){
        $storeId=$_POST['id'];
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        if(!$user || $user['user_id'] <= 0 ){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?app=store&act=setOrderPage&id=".$storeId;
                $ret_url = urlencode($ret_url);
            }
            $this->json_result(-1,$ret_url);//未登录
            return;
        }
        if($user && $user['member_type'] != "01"){  //非普通会员账号
            $this->json_result(-2,"只有普通会员才能下订单！");
            return;
        }
        if( $storeId > 0 && $userId > 0 && $storeId == $userId ){  //不能购买自己店的商品
            $this->json_result(-2,"不能购买自己店的商品！");
            return;
        }
        $this->verify($storeId);
        $store_mod =&m("store");
        $store = $store_mod->get('store_id='.$storeId." and state=1 and dropState=1 and seller_type=3");

        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        $goods_name=$_POST['goods_name'];
        if( !$goods_name || trim($goods_name) =="" || $goods_name=='请输入商品名(2-20个字)'  ){
            $this->json_result(0,"商品名称不能为空!");
            return;
        }
        $order_count=$_POST['order_count'];
        if( !$order_count || $order_count < 0  || $goods_name=='请输入合法订单总额'  ){
            $this->json_result(0,"请输入合法订单总额!");
            return;
        }

        if( $order_count > 1000000 ){
            $this->json_result(0,"单笔不能超过1000000!");
            return;
        }

        $daifu=$_POST['daifu'];
        $order_mod = &m('order');

        $sumOrderAmountSql = "select sum(order_amount) as sm from ecm_order where FROM_UNIXTIME(add_time, '%Y-%m-%d') = curdate() and type='two' and status!=11 and status!=0 and seller_id = " . $store['store_id'];

        $sumOrderAmount = $order_mod -> getOne($sumOrderAmountSql);

        if ($sumOrderAmount && ($sumOrderAmount + $order_count) > 20000) {
            //$this->json_result(0, "商家当日总营业额不能超过20000!");
            //return;
        }

        include_once(ROOT_PATH."/includes/order.base.php");
        $ticheng = $store['ticheng'];
        if (!$ticheng || $ticheng == null || $ticheng < 0.06) {
            $this->json_result(0, "该商家异常!");
            return;
        }
        //$ticheng = $ticheng <=0.08 ? 0.08 : $ticheng;
        $orderData=array(
            'order_sn'   =>  BaseOrder::_gen_order_sn(),
            "type"=>"two",
            "extension"=>"normal",
            "buyer_id"=>$user['user_id'],
            "buyer_name"=>$user['user_name'],
            "buyer_email"=>$user['email'],
            "seller_id"=>$store['store_id'],
            "seller_name"=>$store['store_name'],
            "status"=>11,//待支付
            "add_time"=>time(),
            "payment_code"=>"",
            "out_trade_sn"=>"",
            "pay_message"=>"",
            "finished_time"=>0,
            "goods_amount"=>$order_count,
            "discount"=>0.00,
            "order_amount"=>$order_count,
            "evaluation_status"=>0,
            "evaluation_time"=>0,
            "anonymous"=>0,
            "postscript"=>"",
            "pay_alter"=>0,
            "session_id"=>0,
            "cancel_status"=>0,
            "koushui_yue"=>0.00,
            "ticheng"=> $ticheng ,
            "jine"=> $ticheng * $order_count,
        );

        if($daifu && $daifu == 1){
            $orderData['daifu_user_id']=$store['store_id'];
            $orderData['is_daifu'] =$daifu;
        }else{
            $orderData['daifu_user_id']=0;
            $orderData['is_daifu'] =0;
        }

        $order_id = $order_mod->add($orderData);

        $order= $order_mod->get($order_id);

        if( !$order || $order['order_sn'] != $orderData['order_sn']){
            $this->json_result(0,"未能生成订单，请稍后再试！");
            return;
        }

        if($order && $order['order_id'] >  0 ){
            $order_goods_mod = &m('ordergoods');
            $order_goods=array(
                "order_id"=>$order['order_id'],
                "goods_id"=>0,
                "goods_name"=>$goods_name,
                "spec_id"=>0,
                "price"=>$order_count,
                "quantity"=>1,
                "comment"=>"",
                "is_send"=>0,
                "cellphone"=>"",
                "jifen"=>0,
                "org_price"=>(1-$store['ticheng'])*$order_count,
            );
            $order_goods_id = $order_goods_mod->add($order_goods);
            if($order_goods_id  && $order_goods_id > 0 && $order['is_daifu']==1){//商家代付
                $this->json_result(1,$order['order_sn']);
            }elseif($order_goods_id  && $order_goods_id > 0 && $order['is_daifu']==0){//买家自己付款
                $this->json_result(2,$order['order_sn']);
            }else{
                $this->json_result(0,"系统错误：生成订单失败！");
            }
        }else{
            $this->json_result(0,"系统错误：生成订单失败！");
        }
    }


    function _get_good_list($store_id){
        $goods_mod = &m('goods');
        $page   =   $this->_get_page(30);
        $sql = "SELECT g.goods_id,g.goods_name,g.store_id,g.price,g.org_price,g.default_image
                FROM ecm_goods g
                WHERE  g.TYPE='material'
                AND g.if_show = 1  AND g.closed=0 and g.if_jifen=0
                AND g.dropState =1 AND g.store_id =".$store_id;
        $sqlCount = "select count(*) from ecm_goods where type='material' and if_jifen=0 and if_show = 1 and closed=0 and dropState =1 and store_id = ".$store_id;
        $goods_list = $goods_mod->db->getAll($sql);
        $page['item_count'] = $goods_mod->getOne($sqlCount);
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
        $page['goodsList'] = $goods_list;

        $this->_format_page($page);
        return $page;
    }

    /* 搜索到的结果 */
    function search(){
        $id = $_GET['id'];
        $goods_mod = &m('goods');
        $page = $this->_get_page(60);
        $keyword = $_GET['keyword'];
        $store_mod =&m("store");
        if( $id && $id > 0 ){
            $store = $store_mod->get('store_id='.$id." and state=1 and dropState=1");
            $this->assign("store",$store);
        }
        $sql = "SELECT g.* FROM ecm_goods g WHERE g.store_id=".$id." and g.type='material' AND g.dropState=1 AND g.if_show=1 AND g.closed=0";
        $sqlCount = "SELECT count(*) FROM ecm_goods g WHERE g.store_id=".$id." and  g.type='material' AND g.dropState=1 AND g.if_show=1 AND g.closed=0";
        if( $keyword && $keyword !=null && trim($keyword) != "" ){
            $sql .=" and g.goods_name like '%".trim($keyword)."%'";
            $sqlCount .=" and g.goods_name like '%".trim($keyword)."%'";
        }
        $goods_list = $goods_mod->getAll($sql);

        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }
        $this->assign('searched_goods', $goods_list);
        $page['goodsList'] = $goods_list;
        $page['item_count'] = $goods_mod->getOne($sqlCount);
        $this->_format_page($page);
        $this->assign('page', $page);
        $this->assign('keyword', $keyword);
        $this->display("store.index.html");
    }
    /**
     * 根据区域、详细地址，生成经纬度，以数组的形式返回
     * @param $regionName
     * @param $address
     * @return array(latitude,longitude);
     * @author liz
     */
    function get_latitude_longitude_by_address($address,$regionName){
        if($address == "" || $regionName == ""){
            return array();
        }
        $url = "http://api.map.baidu.com/geocoder?address=%s&output=xml&key=lcCMY1xxIFG2mUh5TOG8iwTa&city=%s";
        $fullUrl = sprintf($url,$address,$regionName);
        $xml= $this->post($fullUrl);
        $resultXML = simplexml_load_string($xml);
        $status = $resultXML->status;
        $result = $resultXML->result;
        $lat = $result->location->lat;
        $lng = $result->location->lng;
        $array = array("status"=>strval($status),"latitude"=>strval($lat),"longitude"=>strval($lng));
        return $array;
    }



}

?>
