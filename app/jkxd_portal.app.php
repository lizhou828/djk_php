<?php

/**
 * Class Jkxd_portalApp
 * 集客小店：前台控制器
 * @author liz
 */
class Jkxd_portalApp extends MemberbaseApp{

    var $member_mod;
    var $order_mod;
    var $jifenlog_mod;
    var $yuelog_mod;
    var $user_id;
    var $user;
    var $goods_mod;
    var $store_mod;
    var $store_category_mod;
    var $gcategory_mod;
    var $jkxd_site_url;
    var $member_finance_mod;
    var $vShop_mod;
    var $shopOwnerId;

    function __construct(){
        $this->Jkxd_portalApp();
    }

    function Jkxd_portalApp(){
        parent::__construct();
        $this->member_mod =&m('member');
        $this->vShop_mod =&m('vshop');
        $this->member_finance_mod =&m('memberfinance');
        $this->order_mod =&m('order');
        $this->jifenlog_mod =&m('jifenlog');
        $this->user_id= $this->visitor->get('user_id');
        $this->yuelog_mod =&m('yuelog');
        $this->goods_mod =&m('goods');
        $this->store_mod = &m("store");
        $this->gcategory_mod = &m("gcategory");
        $this->store_category_mod=&m("categorystore");
        $this->jkxd_site_url = $this->changeSiteUrl();
        $this->user = $this->member_mod->get_info($this->visitor->get('user_id'));
        $this->assign('user', $this->member_mod->get_info($this->visitor->get('user_id')));
        $this->assign('member', $this->member_mod->get_info($this->visitor->get('user_id')));

        $shopOwnerId = $_GET['id'];
        //未带集客小店id参数
        if( !$shopOwnerId || $shopOwnerId <= 0){
            $current_user = $this->user;
            if( $current_user && $current_user['spreader_type'] == 1 && $current_user['shop_name'] != null && $current_user['shop_name'] != "" ){
                $shopOwnerId = $current_user['user_id'];
            }else{
                $shopOwnerId = null;
            }
        }
        $this->shopOwnerId = $shopOwnerId;
        $this->assign("shopOwnerId",$shopOwnerId);
        if( $shopOwnerId && $shopOwnerId >0 ){
            $member_finance = $this->member_finance_mod->get("user_id=".$shopOwnerId);
            $vshop = $this->vShop_mod->get("user_id=".$shopOwnerId." and status=1");
        }
        if( $vshop && $vshop['user_id'] > 0 ){
            $this->assign("vshop",$vshop);
            $vshop_recom_goods=&m("vshopRecommend");
            $temaiGoodsCount = $vshop_recom_goods->getOne("select count(*)  from ecm_vshop_recom_goods where status=1 and position=1 and region_id=".$vshop['region_id']);
            $this->assign("temaiGoodsCount",$temaiGoodsCount);
        }
        $this->assign("jkxd_shouyi",$this->jkxd_shouyi());
        $this->assign("jifen",$member_finance['jifen']);

        $this->assign("hot_keywords",$this->getHotKeywords());
        $this->assign("jkxd_site_url",$this->changeSiteUrl());
    }
    function jkxd_shouyi(){
        $shopOwnerId = $_GET['id'];
        if(!$shopOwnerId){
            $shopOwnerId = $this->user_id;
        }
        $shouyi = $this->yuelog_mod->getOne("SELECT SUM(yue_log.jine) FROM ecm_yue_log yue_log WHERE yue_log.user_id  = ".$shopOwnerId." and yue_type=1  AND yue_log.TYPE =6");
        $data = array(2622=>1000,
            2625=>1000,
            969=>1000,
            5438=>100,
            6216=>1000,
            8722=>100,
            189=>100,
            6209=>100,
            6273=>100,
            236=>1000,
            6300=>100,
            2629=>100,
            2631=>100,
            2638=>100,
            2676=>100,
            5616=>1000,
            5348=>1000,
            5503=>100,
            5614=>100,
            2683=>1000,
            6040=>100,
            2623=>1000,
            4165=>1000,
            210=>100,
            2684=>100
        );
        if($data[$shopOwnerId]);{
            $shouyi +=$data[$shopOwnerId];
        }
        return $shouyi;
    }

    /**
     * 生成集客小店专属域名
     * @return string
     * @author liz
     */
    function changeSiteUrl(){
        $url = site_url();

        if($url==null || $url =="") return $url;

        $id = $_GET['id'] ? $_GET['id'] :$_POST['id'];

        if(!$id || $id==null|| $id==""){
            return $url;
        }
        $vshop = $this->vShop_mod->get("user_id=".$id." and status=1");

        if( strpos($_SERVER['QUERY_STRING'],"act=vshop")  &&   $vshop !=null ){
            $url="http://v.".$id.".dajike.com";
        }else{
            $url="http://www.".$id.".dajike.com";
        }

        return $url;
    }


    function getHotKeywords(){
        $keywords = explode(',', conf::get('jkxd_hot_search'));
        return $keywords;
    }

    /**
     * 集客小店——网站首页入口
     */
    function index(){
        $this->_config_seo(array(
            'title' => '集客小店-专为个人开设的无需投入、无需库存、无需处理物流与售后、无需客服，只需利用碎片时间和个人社交圈就可进行营销推广 - 大集客网上商城',
        ));

        //获取12个推荐到集客小店首页的商品
        $recommendMod = &m("recommend");
        $goodsList = $recommendMod->get_recommended_goods(52,12);
        $this->assign("apply",$_GET['apply']);
        $this->assign("goodsList",$goodsList);
        $this->display("jkxd_portal.index.html");
    }


    /**
     * 我的集客小店首页
     */
    function my_jkxd(){

        //判断是否为手机客户端来的
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        //if ($_GET['test'] == '1') { echo $user_agent; exit;}
        //echo $user_agent;
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                if ($_GET['test'] == '1') { echo $user_agent ."<br>". $device; exit;}
                if (!stristr($user_agent, "ipad")) $is_mobile = true;
                break;
            }
        }
        if($is_mobile && $_GET['id']){
            header("location: {$site_url}/weixin/index.php?app=jkxd_portal&act=my_jkxd&id=".$_GET['id']."&u_id=".$_GET['id']);
        }

        $c_user = $this->visitor->get();

        if($_GET["mobile"]==1){
            header("location: {$site_url}/weixin/index.php?app=jkxd_portal&act=my_jkxd&id=".$c_user['user_id']);
        }
        if(!$_GET['id']){
            header("location: index.php?app=jkxd_portal&act=my_jkxd&id=".$c_user['user_id']);
        }
        $this->_config_seo(array(
            'title' => '集客小店-专为个人开设的无需投入、无需库存、无需处理物流与售后、无需客服，只需利用碎片时间和个人社交圈就可进行营销推广 - 大集客网上商城',
        ));

        $shopOwnerId= $_GET['id'];
        $shopOwner = $this->member_mod->get("user_id=".$shopOwnerId);

        $user=$this->user;

        $member_finance = "";
        if(!$shopOwner || $shopOwner['shop_name']==null || $shopOwner['spreader_type'] != 1){
            echo"<meta charset='utf-8'>";
            echo("您访问的集客小店没有开通!");
            if($user && $user['spreader_type']==1 && $user['shop_name']!=null){
                header("location: $SITE_URL/index.php?app=jkxd_portal&act=my_jkxd&id=".$user['user_id']);
            }else{
                header("location: $SITE_URL/index.php?app=jkxd_portal");
            }
        }else{
//            $vShop = $this->vShop_mod->get("user_id=".$shopOwner['user_id']);
//            if( $vShop && $vShop['user_id'] > 0  && $vShop['region_id'] > 0 && $vShop['region_name'] != ""){
//                header("location: $SITE_URL/index.php?app=jkxd_portal&act=vshop&id=".$shopOwner['user_id']);
//                exit;
//            }
            if($shopOwner['user_id'] == $this->user_id){
                $this->assign("shopOwner",$this->user);
            }else{
                $this->assign("shopOwner",$shopOwner);
            }
            @session_start();
            $_SESSION['t_id']=$shopOwner['user_id'];
            setcookie("t_id", $shopOwner['user_id'], time() + 9999999);
        }


        //大集客商城 begin
        $page = $this->getGoodsByRecommendId(52,80,"","rg.id","desc");
        $this->assign("recommendGoodsCount",$page['item_count']);
        $this->assign("recommendGoodsList",$page['goodsList']);

        //韩国直购 begin
        $page = $this->getGoodsByRecommendId(53,80,"","rg.id","desc");
        $this->assign("recommendHggGoodsCount",$page['item_count']);
        $this->assign("recommendHggGoodsList",$page['goodsList']);
        //韩国直购 end





        // 获取所有(自己所推荐的)店铺
        $storeIdList = $this->getTuijianStore($shopOwnerId);

        // 1 获取所有(自己所推荐的)店铺的所有商品的类别
//        $goodsCategorySql = "SELECT cate_id,cate_name
//                         FROM ecm_goods
//                         WHERE if_show=1 and closed=0 and if_jifen=0 and type='material' and dropState=1 and store_id ".db_create_in($storeIdList)."
//                         GROUP BY cate_id,cate_name";
//        $goodsCategoryList =$this->goods_mod->findByPage($goodsCategorySql,1,1);
//        $this->assign("goodsCategoryList",$goodsCategoryList);

        // 2 获取所有(自己所推荐的)店铺的所有商品

        if($storeIdList != null && count($storeIdList) > 0 ){
                $page   =   $this->_get_page(80);
                $goodsSql = "SELECT * FROM ecm_goods
                             WHERE if_show=1 and closed=0 and if_jifen=0 and type='material' and dropState=1  and store_id ".db_create_in($storeIdList);
                $sqlCount= "SELECT count(*) FROM ecm_goods
                             WHERE if_show=1 and closed=0 and if_jifen=0 and type='material' and dropState=1 and store_id ".db_create_in($storeIdList);
                $goodsList =$this->goods_mod->findByPage($goodsSql,$page['curr_page'],$page['pageper']);
                $goodsList = $this->calculateYongJin($goodsList);
                $page['item_count']=$this->goods_mod->resultCount($sqlCount);
                $this->_format_page($page);
                $page['goodsList'] = $goodsList;
                $this->assign("page_info",$page);
        }


        // 3 没有推荐商家，取后台推荐的商品
        if ( $goodsList == null || count($goodsList) == 0) {
            $page = $this->getGoodsByRecommendId(54,80,"","rg.id","desc");
            $this->assign("recommendHotGoodsCount",$page['item_count']);
            $this->assign("recommendHotGoodsList",$page['goodsList']);
        }
        $type =$_GET['type'];
        $this->assign("type",$type);
        $this->assign("shopOwner",$shopOwner);
        $this->display("jkxd_portal.my_jkxd.html");
    }

    /**
     * 获取该集客小店自己所推荐的商家
     * @author liz
     * @param $shopOwnerId    集客小店店主id(其实就是member的user_id)
     * @return array          推荐的商家店铺id(数组形式)
     */
    function getTuijianStore($shopOwnerId,$sellerType=null){

        $sql = "select * from {$this->store_mod->table} where store_id in(
             select user_id from {$this->member_mod->table} where t_id=".$shopOwnerId.")
            and state=1 and dropState=1";
        if($sellerType != null && $sellerType != ""){
            $sql.=" and seller_type=".$sellerType;
        }

        $recommendStoreList = $this->store_mod->db->getAll($sql);
        $recommendArray = array();
        foreach($recommendStoreList as $recommend){
            array_push($recommendArray,$recommend['store_id']);
        }

        //  如果该小店店主是卖家的身份，则还要显示自己的店铺的商品
        $shopStoreSql = "select * from {$this->store_mod->table} where store_id=$shopOwnerId and state=1 and dropState=1";
        $shopStore = $this->store_mod->db->getAll($shopStoreSql);
        if($shopStore != null  && count($shopStore) > 0 ){
            array_push($recommendArray,$shopOwnerId);
        }
        return $recommendArray;
    }


    /**
     * 申请开通集客小店（静态页面）
     */
    function apply_jkxd(){
        $user = $this->user;
        $shopOwnerId = $_GET['id'];
        if($user && $user['member_type'] != '01'){
            echo '<div style="height:206px;line-height: 206px;text-align: center;font-size:14px; ">对不起，只有普通会员身份才可以申请集客小店!</div>';
            exit;
        }
        if($user &&  $user['spreader_type'] ==1 && $user['shop_name']!= null){
            echo '<div style="height:206px;line-height: 206px;text-align: center;font-size:14px; ">你已经申请过了集客小店，请不要重复申请!</div>';
            exit;
        }
        if($user && $user['im_qq'] != null && $user['im_qq'] != ""){

        }
        $this->assign("user",$user);
        $this->assign("shopOwnerId",$shopOwnerId);
        $this->display("jkxd_portal.apply_jkxd.html");
    }

    /**
     * 申请集客小店（业务逻辑）
     */
    function apply(){
        $t_id="";
        $shopOwnerId = $_POST['shopOwnerId'];
        if(!$shopOwnerId || $shopOwnerId==null || $shopOwnerId==""){
            //有邀请人就写入邀请人，没有就写入默认的邀请人
            @session_start();
            $cookie_t_id  = ecm_getcookie("t_id");
            $session_t_id = $_SESSION['t_id'];
            $t_id = 0;
            if ($cookie_t_id || $session_t_id ) {
                if(is_numeric($session_t_id!=""? $session_t_id : $cookie_t_id)){
                    $t_id =$session_t_id!=""? $session_t_id : $cookie_t_id;
                }else{
                    $t_id =base64_decode($session_t_id!=""? $session_t_id : $cookie_t_id);
                }
                $dateInfo=$this->member_mod->get_info($t_id);
                if (!empty($dateInfo) && $dateInfo["member_type"] == "01") {
                    $t_id = $dateInfo['user_id'];
                }
            }
        }else{
            $t_id = $shopOwnerId;
        }

        include_once(ROOT_PATH . '/includes/MSM.php');                    //短信发送
        $smsclient = new SMSClient();

        $user_id="";
        //未登录的状态申请集客小店
        if(!$this->user){
            $user_name=$_POST['user_name'];
            $password=$_POST['password'];
            $password = md5($password);
            $shop_name=$_POST['shop_name'];
            $user= $this->member_mod->get("user_name='".$user_name."' and password='".$password."'");

            if (!$user || $user['user_id'] <= 0 ){
                $this->json_result(0,"用户名或密码错误!");
            }elseif($user && $user['member_type'] != '01'){
                $this->json_result(0,"只有普通会员才能申请集客小店!");
            }else{
                $ms =& ms(); //连接用户中心
                $this->_do_login($user['user_id']);
                /* 同步登陆外部系统 */
                $synlogin = $ms->user->synlogin($user['user_id']);

                //该账号已开通过集客小店
                if($user['spreader_type'] == 1 && $user['shop_name'] != null && $user['shop_name'] != ""){
                    $this->json_result($user['user_id'],1);
                }else{
                    $this->member_mod->edit($user['user_id'],array('shop_name'=>$shop_name,"spreader_type"=>1));
                    if ($user["phone_mob"] !=null && $user["phone_mob"] != ""){
                        $content = "恭贺您,您的集客小店开业了，无需客服、无需发货、无需售后服务，只要你通过一键分享，就可赚取销售产品70%的奖励，祝你生意兴隆！【大集客】";
                        $smsclient->sendSMS($user["phone_mob"], $content);
                    }

                    $this->json_result($user['user_id'],"成功申请集客小店！");
                }
            }
            //已登录的状态申请集客小店
        }else{
            $user = $this->user;
            if(!$user){
                $this->json_result(0,"登录状态过期，请重新登录");
            }
//            没有申请集客小店的权限
            elseif($user && $user['member_type'] != '01'){
                $this->json_result(0,"只有普通会员才能申请集客小店！");
                //申请过集客小店
            }elseif($user['spreader_type']==1 && $user['shop_name']!=null && $user['shop_name'] != ""){
                $this->json_result($user['user_id'],1);
            }else{
                $shop_name=$_POST['shop_name'];
                $this->member_mod->edit($user['user_id'],array('shop_name'=>$shop_name,"spreader_type"=>1));
                if ($user["phone_mob"] !=null && $user["phone_mob"] != ""){
                    $content = "恭贺您,您的集客小店开业了，无需客服、无需发货、无需售后服务，只要你通过一键分享，就可赚取销售产品70%的奖励，祝你生意兴隆！【大集客】";
                    $smsclient->sendSMS($user["phone_mob"], $content);
                }
                $this->json_result($user['user_id'],"成功申请集客小店！");
            }
        }
    }

    /**
     * ajax检测用户名是否可用
     */
    function checkUserName(){
        $user_name = $_GET['user_name'];
        $user = $this->member_mod->find("user_name='". $user_name."' or email='".$user_name."' or phone_mob='".$user_name."'");
        $message="";
        if(!$user || $user['user_id'] < 0){
            $message="会员账号不存在!";
        }else{
            $message="ok";
        }
        $this->json_result($message);
    }
    /**
     * ajax检测集客小店店铺名是否存在
     */
    function checkShopName(){
        $shop_name = $_GET['shop_name'];
        $user = $this->member_mod->find("shop_name='".$shop_name."'");

        $message="";
        if( count($user) > 0 ){

            $message="该集客小店店铺名称已存在!";
        }else{
            $message="ok";
        }
        $this->json_result($message);
    }

    /**
     * 跳转到申请成功页面
     */
    function apply_success(){
        $user_id =$_GET['id'];
        $user = $this->member_mod->get("user_id="+$user_id);
        if(!$user || $user['user_id'] == null || $user['user_id']==""){
            echo"illegal access!";
            exit;
        }else{
            $this->assign("shopOwner",$user);
            $this->assign("type","apply_success");
            $this->display("jkxd_portal.apply_success.html");
        }

    }

    /**
     * 集客小店：商品列表页（公共）
     */
    function goodsList(){
        $this->_config_seo(array(
            'title' => '集客小店-专为个人开设的无需投入、无需库存、无需处理物流与售后、无需客服，只需利用碎片时间和个人社交圈就可进行营销推广 - 大集客网上商城',
        ));
        //  获取集客小店店主信息
        $shopOwnerId= $_GET['id'] ? $_GET['id'] : $_POST['id'];
        $shopOwner = $this->verify($shopOwnerId);

        $this->assign("shopOwner",$shopOwner);


        if($shopOwner['user_id'] == $this->user_id){
            $this->assign("shopOwner",$this->user);
        }else{
            $this->assign("shopOwner",$shopOwner);
        }


        // 获取所有(自己所推荐的)店铺
        $storeIdList = $this->getTuijianStore($shopOwnerId);

        $type = $_GET['type'];
        $cateId = $_GET['category_id'];
        $order_by = $_GET['order_by'];
        $desc_asc = $_GET['desc_asc'];
        $this->assign("category_id",$cateId);
        $this->assign("order_by",$order_by);
        $this->assign("desc_asc",$desc_asc);

        $page="";
        $cates = Array();
        if(!$type || $type == null || $type == ""){
            if( $storeIdList && count($storeIdList) > 0 ){
                // 1 获取所有(自己所推荐的)店铺的所有商品的类别
                $goodsCategorySql = "SELECT cate_id,cate_name, count(cate_id) as c
                         FROM ecm_goods
                         WHERE if_show=1 and type='material' and closed=0 and dropState=1 and store_id ".db_create_in($storeIdList)."
                         GROUP BY cate_id,cate_name";
                $cates = $this->goods_mod->findByPage($goodsCategorySql,1,60);

                // 2 获取所有(自己所推荐的)店铺的所有商品
                $page   =   $this->_get_page(60);
                $goodsSql = "SELECT * FROM ecm_goods
                         WHERE if_show=1 and type='material' and closed=0 and dropState=1 and store_id ".db_create_in($storeIdList);
                $sqlCount= "SELECT count(*) FROM ecm_goods
                         WHERE if_show=1 and type='material' and closed=0 and dropState=1 and store_id ".db_create_in($storeIdList);
                if( $cateId != null && $cateId != "" && $cateId > 0){
                    $goodsSql .=" and cate_id=".$cateId;
                    $sqlCount .=" and cate_id=".$cateId;
                }
                if( $order_by != null && $order_by != "" ){
                    $goodsSql .= " order by ".$order_by;
                }
                if($desc_asc != null && $desc_asc != ""){
                    $goodsSql .= " ".$desc_asc;
                }
                $goodsList =$this->goods_mod->findByPage($goodsSql,$page['curr_page'],$page['pageper']);
                $goodsList = $this->calculateYongJin($goodsList);
                $page['item_count']=$this->goods_mod->resultCount($sqlCount);
                $this->_format_page($page);
                $page['goodsList'] = $goodsList;
                $this->assign("page_info",$page);
            }



            // 3 若(自己所推荐的)店铺没有商品，则取默认的商品
            if(!$goodsList || count($goodsList) <= 0){
                $page = $this->getGoodsByRecommendId(54,60, $cateId,$order_by,$desc_asc);
                $cates = $page["cates"];
            }

        }elseif($type=="djk"){
            $page = $this->getGoodsByRecommendId(52,60, $cateId,$order_by,$desc_asc);
            $cates = $page["cates"];

        }elseif($type=="hgg"){
            $page = $this->getGoodsByRecommendId(53,60, $cateId,$order_by,$desc_asc);
            $cates = $page["cates"];

        }elseif($type=="search"){
            $keyword = $_GET['keyword'];
            $this->assign("keyword",$keyword);
            $page = $this->searchInShop($shopOwnerId,$keyword,$cateId,$order_by,$desc_asc);
            $cates = $page["cates"];
        }


        $this->assign("page_info",$page);
        $this->assign("type",$type);
        $this->assign("goodsCategoryList",$cates);


        $this->display("jkxd_portal.goodsList.html");



    }

    /**
     * 根据商品类别id 获取属于自己集客小店的商品
     */
    function getGoodsByCateId(){
        //  获取集客小店店主信息
        $shopOwnerId= $_GET['id'] ? $_GET['id'] : $_POST['id'];
        $shopOwner = $this->verify($shopOwnerId);
        $this->assign("shopOwner",$shopOwner);

        // 获取所有(自己所推荐的)店铺
        $storeIdList = $this->getTuijianStore($shopOwnerId);
        // 获取所有(自己所推荐的)店铺
        $category_id = $_GET['category_id'];
        $order_by = $_GET['order_by'];
        $desc_asc = $_GET['desc_asc'];
        $conditions="";
        if($category_id!=null && $category_id!="" && $order_by!=null && $order_by!=""){
            $conditions .= " and cate_id=".$category_id." order by ".$order_by. " ".$desc_asc;
        }

        $page   =   $this->_get_page(20);
        $goodsSql = "SELECT * FROM ecm_goods
                         WHERE if_show=1 and closed=0 and dropState=1 and store_id ".db_create_in($storeIdList).$conditions;
        $sqlCount= "SELECT count(*) FROM ecm_goods
                         WHERE if_show=1 and closed=0 and dropState=1 and store_id ".db_create_in($storeIdList).$conditions;
        $goodsList =$this->goods_mod->findByPage($goodsSql,$page['curr_page'],$page['pageper']);
        $goodsList = $this->calculateYongJin($goodsList);
        $page['item_count']=$this->goods_mod->resultCount($sqlCount);
        $this->_format_page($page);
        $page['goodsList'] = $goodsList;
        $this->assign("page_info",$page);
        $this->display("jkxd_portal.goods_query_list.html");

    }

    /**
     * 集客小店id验证
     * @param $shopOwnerId
     * @return shopOwner
     * @author liz
     */
    function verify($shopOwnerId){
        $shopOwner = null;
        $user =$this->user;
        if($shopOwnerId != null && $shopOwnerId != ""){
            $shopOwner = $this->member_mod->get("user_id=".$shopOwnerId);
            @session_start();
            $_SESSION['t_id']=$shopOwner['user_id'];
            setcookie("t_id", $shopOwner['user_id'], time() + 9999999);
        }
        if(!$shopOwner || $shopOwner['shop_name']==null || $shopOwner['spreader_type'] != 1){
            echo"<meta charset='utf-8'>";
            echo("您访问的集客小店没有开通!");
            if($user && $user['spreader_type']==1 && $user['shop_name']!=null){
                header("location: index.php?app=jkxd_portal&act=my_jkxd&id=".$user['user_id']);
            }else{
                header("location: index.php?app=jkxd_portal");
            }
        }
        return $shopOwner;
    }


    /**
     * 引导页一
     */
    function guide_one(){
        $this->display("jkxd_portal.guide_one.html");
    }
    /**
     * 引导页二
     */
    function guide_two(){
        $this->display("jkxd_portal.guide_two.html");
    }
    /**
     * 引导页三
     */
    function guide_three(){
        $this->display("jkxd_portal.guide_three.html");
    }

    /**
     * 在集客小店内搜索商品
     * @author liz
     * @param $shopOwnerId  集客小店id
     * @param $keyword      搜索关键字
     * @return array        返回page对象
     */
    function searchInShop($shopOwnerId,$keyword,$cateId=null,$order_by=null,$desc_asc=null){
        // 获取所有(自己所推荐的)店铺
        $storeIdList = $this->getTuijianStore($shopOwnerId);

        $sql="SELECT distinct g.goods_id,g.goods_name,g.default_image,g.price,g.org_price,g.add_time,g.start_time
                FROM ecm_goods g
                LEFT JOIN ecm_recommended_goods rg ON rg.goods_id=g.goods_id
                WHERE  g.dropstate=1 and g.type='material' AND g.if_show=1 AND g.closed=0 ";
        $sqlCount="SELECT count(distinct g.goods_id)
                FROM ecm_goods g
                LEFT JOIN ecm_recommended_goods rg ON rg.goods_id=g.goods_id
                WHERE  g.dropstate=1 and g.type='material' AND g.if_show=1 AND g.closed=0 ";
        $cateSql="SELECT g.cate_id, g.cate_name, count(distinct g.goods_id) as c
                FROM ecm_goods g
                LEFT JOIN ecm_recommended_goods rg ON rg.goods_id=g.goods_id
                WHERE  g.dropstate=1  and g.type='material' AND g.if_show=1 AND g.closed=0 ";
        if( $storeIdList != null && count($storeIdList) > 0 ){
            $sql .=" AND ( g.store_id ".db_create_in($storeIdList)." OR rg.recom_id =52 OR rg.recom_id=53 ) ";
            $sqlCount .=" AND ( g.store_id ".db_create_in($storeIdList)." OR rg.recom_id =52 OR rg.recom_id=53 ) ";
            $cateSql .=" AND ( g.store_id ".db_create_in($storeIdList)." OR rg.recom_id =52 OR rg.recom_id=53 ) ";
        }else{
            $sql .= " AND ( rg.recom_id =52 OR rg.recom_id=53 OR rg.recom_id=54) ";
            $sqlCount .= " AND ( rg.recom_id =52 OR rg.recom_id=53 OR rg.recom_id=54) ";
            $cateSql .= " AND ( rg.recom_id =52 OR rg.recom_id=53 OR rg.recom_id=54) ";
        }

        if($keyword != null && trim($keyword)!=""){
            $conditions = "";
            $keywords = explode(" ",$keyword);
            foreach ($keywords as $word){
                $conditions .= " and g.goods_name LIKE '%{$word}%'";
            }
            $sql .=$conditions;
            $sqlCount .= $conditions;
            $cateSql  .= $conditions;
        }
        if($cateId > 0 ){
            $sql .= " and g.cate_id=".$cateId;
            $sqlCount .= " and g.cate_id=".$cateId;
        }

        $sql .= " GROUP BY g.goods_id" ;
        $cateSql  .= " group by g.cate_id, g.cate_name";

        if( $order_by != null && $order_by != "" ){
            $sql .= " order by ".$order_by;
        }
        if($desc_asc != null && $desc_asc != ""){
            $sql .= " ".$desc_asc;
        }

        $page   =   $this->_get_page(60);
        $goodsList = $this->goods_mod->findByPage($sql,$page['curr_page'],$page['pageper']);
        $cates = $this->goods_mod->getAll($cateSql);
        $page["cates"] = $cates;
        $count = $this->goods_mod->resultCount($sqlCount);
        $goodsList = $this->calculateYongJin($goodsList);
        $this->assign("goodsList",$goodsList);
        $page['item_count']=$count;
        $this->_format_page($page);
        $page['goodsList'] = $goodsList;
        return $page;
    }

    /**
     * 根据推荐id 获取推荐的商品（分页）
     * @param $recommendId  推荐位置id
     * @param $perPage  每页显示结果集条数
     * @param $cateId   商品类别id(可选)
     * @param $order_by 排序字段(可选)
     * @param $desc_asc 排序顺序(可选)
     * @return array    商品数组
     * @author liz
     */
    function getGoodsByRecommendId($recommendId,$perPage,$cateId=null,$order_by=null,$desc_asc=null,$vshop=null){
        $page   =   $this->_get_page($perPage);
        $sql = "select g.goods_id,g.goods_name,g.price,g.org_price,g.default_image,g.add_time from ecm_goods g".
            " left join ecm_recommended_goods rg on g.goods_id = rg.goods_id ".
            " where rg.recom_id = ".$recommendId." and g.type='material' and g.closed = 0  and g.if_show = 1 and g.dropState=1 and g.if_jifen=0 AND  g.goods_id IS NOT NULL";
        $sqlCount = "select count(distinct g.goods_id) from ecm_goods g".
            " left join ecm_recommended_goods rg on g.goods_id = rg.goods_id ".
            " where  rg.recom_id = ".$recommendId." and g.type='material' and g.closed = 0  and g.if_show = 1 and g.dropState=1 and g.if_jifen=0 AND  g.goods_id IS NOT NULL";
        if($cateId > 0 ){
            $sql .= " and g.cate_id=".$cateId;
            $sqlCount .= " and g.cate_id=".$cateId;
        }

        $sql .= " GROUP BY g.goods_id" ;


        if( $order_by != null && $order_by != "" ){
            $sql .= " order by ".$order_by;
        }else{
            $sql .= " order by rg.id";
        }


        if($desc_asc != null && $desc_asc != ""){
            $sql .= " ".$desc_asc;
        }else{
            $sql .= " desc";
        }
        $catesSql = "select g.cate_id, g.cate_name, count(distinct g.goods_id) as c from ecm_goods g".
            " left join ecm_recommended_goods rg on g.goods_id = rg.goods_id ".
            " where  rg.recom_id = ".$recommendId." and g.type='material' and g.closed = 0  and g.if_show = 1 and g.dropState=1 and g.if_jifen=0 AND  g.goods_id IS NOT NULL
            group by g.cate_id, g.cate_name";
        $recommendMod = &m("recommend");
        $recommendGoodsList = $recommendMod->goodsList($sql,$page['curr_page'],$page['pageper']);
        $count = $recommendMod->resultCount($sqlCount);
        $cates = $recommendMod->getAll($catesSql);
        $recommendGoodsList = $this->calculateYongJin($recommendGoodsList,$vshop);
        $page['item_count']=$count;
        $this->_format_page($page);
        $page['goodsList'] =$recommendGoodsList;
        $page["cates"] = $cates;
        return $page;
    }





    /**
     * qq账号申请
     */
    function qq_apply(){
        $shopOwnerId = $_GET['shopOwnerId'];
        //@session_start();
        $_SESSION['t_id']=$shopOwnerId;
        ecm_setcookie("t_id", $shopOwnerId, time() + 60*60*24);

        setcookie("qq_apply_jike_shop", '', time(), "/", "www.dajike.com");
        ecm_setcookie("qq_apply_jike_shop", 1, time() + 60*60*24);
        header("Location: $SITE_URL/Connect2.0/example/oauth/index.php");
    }


    /**
     * qq登录的用户，在登录后，如果没设置密码，则必须设置密码，方便以后直接用集客小店号登录
     */
    function qq_setPasswordPage(){
        $this->assign("type",$_GET['type']);
        $this->display("jkxd_portal.qq_setPasswordPage.html");
    }
    function qq_setPassword(){
        $password1 = $_POST['password1'];
        $password2 = $_POST['password2'];
        if(!$password1 || $password1 ==null || $password1==""){
            $this->json_result("0","密码为空!");
        }elseif(!$password2 || $password2 ==null || $password2==""){
            $this->json_result("0","确认密码为空!");
        }elseif($password1 != $password2){
            $this->json_result("0","两次输入的密码不一致!");
        }else{
            $user = $this->user;
            $member_mod = &m("member");
            $result = $member_mod->edit("user_id=".$user['user_id'],array("password"=>md5($password1)));
            if($result){
                $this->json_result("1","设置密码成功!");
            }else{
                $this->json_result("0","密码已设置!");
            }

        }
    }



    /**
     * 根据商品的集合，计算商品的佣金(仅限集客小店内的商品)
     * @author liz
     * @param $goodsList
     * @return array
     */
    function calculateYongJin($goodsList,$vshop=null){

        $list =array();
        $rules_mod = &m('rule');
        if( $vshop=="vshop"){
            $rules_mod = &m('vshopRule');
        }

        if( $goodsList && count($goodsList) > 0 && is_array($goodsList) ) {
            foreach($goodsList as $goods){
                $ratio = 1 -($goods['org_price'] / $goods['price']);
                $ratio = ((int)($ratio*100))/100;
                $rule = $rules_mod->get("ratio=".$ratio);
                $yongjin = $goods['price']  * $rule['jikexiaodian'];
                $goods['yongjin'] = $yongjin;
                array_push($list,$goods);
            }
        }

        return $list;

    }

    /**
     * 精品集客小店
     */
    function vShop(){
        //判断是否为手机客户端来的
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                if ($_GET['test'] == '1') { echo $user_agent ."<br>". $device; exit;}
                if (!stristr($user  _agent, "ipad")) $is_mobile = true;
                break;
            }
        }
        $shopOwnerId= $this->shopOwnerId;
        if( $shopOwnerId && $shopOwnerId > 0 ){
            $user_temp = $this->member_mod->get("user_id=".$shopOwnerId);
        }
        if(!$user_temp || $user_temp['user_id'] <= 0 ){
            if($is_mobile){
                header("location: {$site_url}/weixin/index.php?app=jkxd_portal");
            }else{
                header("location: {$site_url}/index.php?app=jkxd_portal");
            }
        }
        $shopOwner = null;
        $vShop = null;
        if( $user_temp && $user_temp['user_id'] > 0 && $user_temp['spreader_type']==1 && $user_temp['shop_name'] !=null  && $user_temp['shop_name'] !=""){
            $shopOwner = $user_temp;
            $vShop = $this->vShop_mod->get("user_id=".$shopOwner['user_id']." and status=1");
        }

        //手机版
        if( $is_mobile){
            if( !$shopOwner ){
                header("location: {$site_url}/weixin/index.php?app=jkxd_portal");
            }elseif( $vShop ){
                header("location: {$site_url}/weixin/index.php?app=jkxd_portal&act=vshop&id=".$shopOwner['user_id']."&u_id=".$shopOwner['user_id']);
            }
        }
        if($shopOwner['user_id'] == $this->user_id){
            $this->assign("shopOwner",$this->user);
        }else{
            $this->assign("shopOwner",$shopOwner);
        }
        @session_start();
        $_SESSION['t_id']=$shopOwner['user_id'];
        ecm_setcookie("t_id", $shopOwner['user_id'], time() + 9999999);

        $this->_config_seo(array(
            'title' => '精品集客小店-专为个人开设的无需投入、无需库存、无需处理物流与售后、无需客服，只需利用碎片时间和个人社交圈就可进行营销推广的网上商城 - 大集客网上商城',
        ));

        //大集客精品
        $djk_jingpin= $this->getGoodsByRecommendId(56,80,"","rg.id","desc","vshop");

        //本地特卖（服务中心推荐托管商家的商品）
        $bendi_temai =$this->vshop_goods($shopOwnerId,1,"","vg.update_time","");

        $this->assign("bendi_temai",$bendi_temai);
        $this->assign("djk_jingpin",$djk_jingpin);
        $this->display("jkxd_portal.vShop.html");
    }

    /**
     * 精品集客小店-商品列表
     */
    function vshop_list(){
        $this->_config_seo(array(
            'title' => '精品集客小店-专为个人开设的无需投入、无需库存、无需处理物流与售后、无需客服，只需利用碎片时间和个人社交圈就可进行营销推广的网上商城 - 大集客网上商城',
        ));
        $shopOwnerId =$this->shopOwnerId;
        if( $shopOwnerId && $shopOwnerId > 0 ){
            $shopOwner =$this->member_mod->get("user_id=".$shopOwnerId);
        }else{
            exit("精品集客小店号参数丢失！");
        }

        $this->assign("shopOwner",$shopOwner);
        $this->assign("topCateId",$_GET['topCateId']);
        $this->assign("goodCateId",$_GET['goodCateId']);

        $order_by = $_GET['order_by'];
        $this->assign("order_by",$order_by);

        $desc_asc = $_GET['desc_asc'];
        $this->assign("desc_asc",$desc_asc);
        if($_GET['type']=="search"){
            $page=$this->searchInVshop($_GET['keyword'],$order_by,$desc_asc);
            $this->assign("keyword",$_GET['keyword']);
        }elseif( $_GET['type']=="localHot" ){
            $page=$this->vshop_goods($shopOwnerId,1,$_GET["goodCateId"],$order_by,$desc_asc);
        }elseif($_GET['type']=='jingpin'){
            $page = $this->getGoodsByRecommendId(56,80,$_GET["goodCateId"],"rg.id","desc","vshop");
//            $page = $this->getVshopGoodsByCategoryId(56,50,$_GET['topCateId'],$_GET["goodCateId"],$order_by,$desc_asc,"vshop");
        }
        $this->assign("page_info",$page);
        $this->assign("type",$_GET['type']);
        $this->display("jkxd_portal.vshop_list.html");
    }

    //服务中心推荐商品到精品集客小店（$position=1 表示 服务中心推荐托管商家的商品）
    function vshop_goods($shopOwnerId,$position,$goodCateId ,$order_by,$desc_asc){
        if( $shopOwnerId && $shopOwnerId > 0 ){
            $vshop = $this->vShop_mod->get("user_id=".$shopOwnerId." and status=1");
        }else{
            return null;
        }
        if(!$vshop){
            return null;
        }
        $vshopRecommend_mod = &m("vshopRecommend");
        $page   =   $this->_get_page(30);
        $sql ="SELECT g.goods_id ,g.goods_name,g.org_price,g.price,g.default_image,vg.update_time,vg.region_id,vg.region_name,vg.create_time
               FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
               WHERE vg.region_id=".$vshop['region_id']." AND vg.STATUS=1 and g.type='material' and g.if_show=1 and g.closed=0 and g.dropState=1 and g.if_jifen=0";
        $sqlCount="SELECT COUNT(DISTINCT vg.goods_id)
                   FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
                   WHERE vg.region_id=".$vshop['region_id']." AND vg.STATUS=1 and g.type='material' and g.if_show=1 and g.closed=0 and g.dropState=1 and g.if_jifen=0";
        $catesSql="SELECT g.cate_id, g.cate_name, COUNT( DISTINCT g.cate_id) AS c
                   FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
                   WHERE vg.region_id=".$vshop['region_id']." AND vg.STATUS=1 and g.type='material' and g.if_show=1 and g.closed=0 and g.dropState=1 and g.if_jifen=0";
        if($position >= 1){
            $sql .=" and vg.position=".$position;
            $sqlCount .=" and vg.position=".$position;
            $catesSql .=" and vg.position=".$position;
        }
        if( $goodCateId && $goodCateId > 0 ){
            $sql .=" and g.cate_id=".$goodCateId;
            $sqlCount .=" and g.cate_id=".$goodCateId;
        }
        $sql .=" GROUP BY vg.goods_id ";
        $catesSql.=" group by g.cate_id, g.cate_name";

        if( $order_by != null && $order_by != "" ){
            $sql .= " order by ".$order_by;
        }else{
            $sql .= " order by vg.update_time";
        }
        if($desc_asc != null && $desc_asc != ""){
            $sql .= " ".$desc_asc;
        }else{
            $sql .= " desc";
        }
        $goodsList= $vshopRecommend_mod->goodsList($sql,$page['curr_page'],$page['pageper']);
        $goodsList = $this->calculateYongJin($goodsList,"vshop");
        $cates = $vshopRecommend_mod->getAll($catesSql);
        $page['item_count'] = $vshopRecommend_mod->getOne($sqlCount);
        $page['goodsList']=$goodsList;
        $page["cates"] = $cates;
        $this->_format_page($page);
        return $page;
    }

    //精品集客小店——搜索
    function searchInVshop($keyword ,$order_by=null,$desc_asc=null){
        $shopOwnerId= $this->shopOwnerId;
        if( $shopOwnerId && $shopOwnerId > 0 ){
            $vshop = $this->vShop_mod->get("user_id=".$shopOwnerId." and status=1");
        }else{
            return null;
        }
        if(!$vshop){
            return null;
        }
        //在精品集客小店(本地特卖)里面搜索
        $sql1 ="SELECT  g.goods_id ,g.goods_name,g.org_price,g.price,g.default_image,g.add_time,g.start_time
               FROM ecm_vshop_recom_goods vg LEFT JOIN ecm_goods g ON vg.goods_id=g.goods_id
               WHERE vg.region_id=".$vshop['region_id']." AND vg.STATUS=1 AND vg.POSITION=1 and g.type='material' and g.if_show=1 and g.closed=0 and g.dropState=1 and g.if_jifen=0 ";
        //在精品集客小店(精品分类)里面搜索
        $sql2 =  "SELECT  g.goods_id,g.goods_name,g.org_price,g.price,g.default_image,g.add_time,g.start_time
               FROM ecm_goods g
               LEFT JOIN ecm_recommended_goods rg ON rg.goods_id=g.goods_id
               WHERE g.type='material' and  g.dropstate=1 AND g.if_show=1 AND g.closed=0 AND rg.recom_id=56";

        if($keyword != null && trim($keyword)!=""){
            $conditions = "";
            $keywords = explode(" ",$keyword);
            foreach ($keywords as $word){
                $conditions .= " and g.goods_name LIKE '%{$word}%'";
            }

            $sql1 .=$conditions;
            $sql2 .=$conditions;
            $sql= $sql1." UNION ".$sql2;
            $sqlCount = "SELECT COUNT(*) FROM ( ".$sql." ) AS total";

        }else{
            return null;
        }


        if( $order_by != null && $order_by != "" ){
            $sql .= " order by ".$order_by;
        }
        if($desc_asc != null && $desc_asc != ""){
            $sql .= " ".$desc_asc;
        }

        $page   =   $this->_get_page(60);

        $goodsList = $this->goods_mod->findByPage($sql,$page['curr_page'],$page['pageper']);
        $count = $this->goods_mod->resultCount($sqlCount);
        $goodsList = $this->calculateYongJin($goodsList,"vshop");
        $this->assign("goodsList",$goodsList);
        $page['item_count']=$count;
        $this->_format_page($page);
        $page['goodsList'] = $goodsList;
        return $page;
    }


}

?>
