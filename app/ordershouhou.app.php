<?php
/*
 * 订单售后
 *
 * */

class OrdershouhouApp extends MemberbaseApp
{

    var $shouhou_mod;
    var $userInfo;
    var $mb_mod;
    var $order_mod;

    function __construct()
    {
        $this->Ordershouhou();
    }
    function Ordershouhou()
    {
        parent::__construct();
        $this->shouhou_mod = & m('ordershouhou');
        $this->mb_mod =& m('member');
        $this->order_mod=&  m('order');

        $this->userInfo=$this->visitor->info;

    }

    function index(){
        $this->shouhou_mod->test();

    }

    function order_goods(){

    }


    //买家发起售后申请
    function shenqingshouhou(){
        $user=$this->userInfo;

        if(!$_POST){
            if($user["member_type"]!="01")
            {
                print "<font color='red'><br><br><center>服务中心和采购账号不能购买东西！</center><br><br></font>";
                exit;
            }

            $orderId=$_GET["order_id"];
            $goodsId=$_GET["goods_id"];

            if($orderId=="" || $goodsId=="")
            {
                print "<font color='red'><br><br><center>操作错误，必须提交1个订单号和商品编号</center><br><br></font>";
                exit;
            }


            $ordergoods_mod =& m('ordergoods');
            $goodscount=$ordergoods_mod->getOne("select count(1) from ".DB_PREFIX."order_goods  WHERE  order_id=$orderId  AND goods_id=$goodsId");

            if($goodscount!=1)
            {
                print "<font color='red'><br><br><center>当前商品和订单不一致!</center><br><br></font>";
                exit;
            }

            $ordergoods=$ordergoods_mod->get(array(
                                            "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId "
            ));

            $this->assign('ordergoods', $ordergoods);

            if($_GET["shouhou_id"])
            {
                $shouhou=$this->shouhou_mod->get_info($_GET["shouhou_id"]);
                $this->assign('shouhou', $shouhou);
            }
            $this->display('order_shouhou.html');
        }else{

            $orderId=$_POST["order_id"];
            $goodsId=$_POST["goods_id"];

            $ordergoods_mod =& m('ordergoods');
            $ordergoods=$ordergoods_mod->get(array(
                "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId "
            ));

            $order=$this->order_mod->get_info($orderId);

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
                $this->shouhou_mod->edit($_POST["shouhou_id"],$date);
                $id=$_POST["shouhou_id"];
            }else{
                $id=$this->shouhou_mod->add($date);
            }

            $imgs = $this->_upload_image($order["buyer_id"],'content_img');

            //$this->pr($imgs);

            if(count($imgs)>0){
                $imgData=array();

                $shouhou=$this->shouhou_mod->get_info($id);

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
                $this->shouhou_mod->edit($id,$imgData);
            }


            $this->show_message('tijiaotoushu_ok',
                'back_list', 'index.php?app=buyer_order&p=buyer'
            );
        }
    }

    //买家取消售后申请
    function quxiaoshouhou(){
        if($_POST)
        {
            $id=$_POST["shouhou_id"];
            $rs=$this->shouhou_mod->edit($id,array("status"=>-1));
            if($rs){
                $this->show_message('quxiaoshouhou_ok',
                    'back_list', 'index.php?app=buyer_order&p=buyer'
                );
            }
        }

    }

    //买家确认
    function showshouhou(){
        $user=$this->userInfo;
        $userId=$user["user_id"];
        if(!$_POST){

            $orderId   =$_GET["order_id"];
            $goodsId   =$_GET["goods_id"];
            $shouhouId =$_GET["shouhou_id"];

            if($orderId=="" || $goodsId=="" || $shouhouId=="")
            {
                print "<font color='red'><br><br><center>操作错误，必须提交1个订单号和商品编号和售后编号</center><br><br></font>";
                exit;
            }


            $ordergoods_mod =& m('ordergoods');
            $goodscount=$ordergoods_mod->getOne("select count(1) from ".DB_PREFIX."order_goods  WHERE  order_id=$orderId  AND goods_id=$goodsId");

            if($goodscount!=1)
            {
                print "<font color='red'><br><br><center>当前商品和订单不一致!</center><br><br></font>";
                exit;
            }

            $shouhou=$this->shouhou_mod->get_info($shouhouId);

            $ordergoods=$ordergoods_mod->get(array(
                "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId"
            ));

            $this->assign('shouhou', $shouhou);
            $this->assign('ordergoods', $ordergoods);
            $this->assign('shouhouId', $shouhouId);

            $this->display('show_shouhou.html');
        }else{
            $orderId   = $_POST["order_id"];
            $goodsId   = $_POST["goods_id"];
            $shouhouId = $_POST["shouhou_id"];
            $status = $_POST["status"];

            $ordergoods_mod =& m('ordergoods');
            $goodscount=$ordergoods_mod->getOne("select count(1) from ".DB_PREFIX."order_goods  WHERE  order_id=$orderId  AND goods_id=$goodsId");

            if($goodscount!=1)
            {
                print "<font color='red'><br><br><center>当前商品和订单不一致!</center><br><br></font>";
                exit;
            }

            if($status!=0 && $status!=2)
            {
                print "<font color='red'><br><br><center>操作错误!</center><br><br></font>";
                exit;
            }
            $this->shouhou_mod->edit($shouhouId,array(
                                                "status"=>$status));

                $flg="querenok";
            if($status==0){
                $flg="querenshibai";
            }
            $this->show_message($flg,
                'back_list', 'index.php?app=buyer_order&p=buyer'
            );

        }
    }


    //卖家发起售后申请
    function chulishouhou(){


        $user=$this->userInfo;

        if(!$user["store_id"] && $_GET["app"]!="service")
        {
            print "<font color='red'><br><br><center>没有找到店铺信息，请检查是否已经登入！</center><br><br></font>";
            exit;
        }

        $userId=$user["store_id"];
        if(!$_POST){

            $orderId   =$_GET["order_id"];
            $goodsId   =$_GET["goods_id"];
            $shouhouId =$_GET["shouhou_id"];

            if($orderId=="" || $goodsId=="" || $shouhouId=="")
            {
                print "<font color='red'><br><br><center>操作错误，必须提交1个订单号和商品编号和售后编号</center><br><br></font>";
                exit;
            }


            $ordergoods_mod =& m('ordergoods');
            $goodscount=$ordergoods_mod->getOne("select count(1) from ".DB_PREFIX."order_goods  WHERE  order_id=$orderId  AND goods_id=$goodsId");

            if($goodscount!=1)
            {
                print "<font color='red'><br><br><center>当前商品和订单不一致!</center><br><br></font>";
                exit;
            }


            $ordergoods=$ordergoods_mod->get(array(
                "conditions"=>"order_goods.goods_id=$goodsId and order_id=$orderId"
            ));

            $shouhou=$this->shouhou_mod->get_info($shouhouId);

            $this->assign('shouhou', $shouhou);
            $this->assign('ordergoods', $ordergoods);
            $this->assign('shouhouId', $shouhouId);
            if($_GET["app"]=="service"){
                $this->assign('orderType', 1);
            }
            $this->display('seller_shouhou.html');
        }else{

            $orderId=$_POST["order_id"];
            $goodsId=$_POST["goods_id"];
            $shouhouId=$_POST["shouhou_id"];
            $content_reply=$_POST["content_reply"];
            $this->shouhou_mod->edit($_POST["shouhou_id"],array(
                                                            "content_reply"=>$content_reply,
                                                            "time_reply"=>gmtime(),
                                                            "status"=>1));


            $imgs = $this->_upload_image($userId,'reply_img');

            if(count($imgs)>0){
                $imgData=array();

                $shouhou=$this->shouhou_mod->get_info($shouhouId);

                if($imgs["reply_img1"]!="")
                {
                    $imgData["reply_img1"]=$imgs["reply_img1"];
                    $this->deleteFileInfo($shouhou['reply_img1']);
                }
                if($imgs["reply_img2"]!="")
                {
                    $imgData["reply_img2"]=$imgs["reply_img2"];
                    $this->deleteFileInfo($shouhou['reply_img2']);
                }
                $this->shouhou_mod->edit($_POST["shouhou_id"],$imgData);
            }

            if($_GET["app"]=="service")
            {
                $this->show_message('chulishouhou_ok',
                    'back_list', 'index.php?app=service&act=orders&step=1&p=service'
                );
            }else{
                $this->show_message('chulishouhou_ok',
                    'back_list', 'index.php?app=seller_order&p=seller'
                );
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

}
?>
