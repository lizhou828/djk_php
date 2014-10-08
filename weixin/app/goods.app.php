<?php

define("NAME",serialize(array(  34=>"首尔超市",
    35=>"跟上韩流",
    36=>"生活家园",
    37=>"美丽世界",
    38=>"韩国旅游",
    39=>"韩国特展",
    22 => '当地土特产',
    45 => '休闲食品',
    20 => '营养健康',
    19 => '有机食品',
    42 => '品牌集',
    44 => '集市特卖',)));

/**
 *    默认控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class GoodsApp extends MallbaseApp
{
     function index(){
         $goods_id = $_GET['goods_id'];
         $reg_name = $_GET['region_name'];
         $goods_model = & m('goods');
         $goodsspec_model = & m('goodsspec');
         $goodsimage_model = & m('goodsimage');
         $goods = $goods_model -> get($goods_id);
         $spec1 = array();
         $spec2 = array();
         //查询运费
         if($reg_name != ""){
             $yf = $this -> get_yunfei($reg_name,$goods['shipping_id']);
             $this -> assign("yf",$yf);
             $this -> assign("reg_name",$reg_name);
         }
         //查询商品规格
         if($goods['spec_name_1'] != "" || $goods['spec_name_2'] != ""){
             $goodsspec = $goodsspec_model -> find("goods_id = " . $goods_id);
             foreach($goodsspec as $spec){
                 array_push($spec1,$spec['spec_1']);
                 array_push($spec2,$spec['spec_2']);
             }
             $spec1 = array_unique($spec1);
             $spec2 = array_unique($spec2);
//             print_r($spec1)."-----";
//             print_r($spec2);exit;
             $this -> assign("spec1",$spec1);
             $this -> assign("spec2",$spec2);
         }
         if($goods['store_id'] != ""){
            $goods_store = $goods_model -> find("type='material' and store_id = " . $goods['store_id'] . " AND if_show = 1 AND closed = 0 AND dropState = 1 AND store_id > 0 limit 0,5");
         }
         if(count($goods_store) > 0){
             $this -> assign("goods_store",$goods_store);
         }
         $yunfei = $this -> _get_yunfei($goods_id);
         $goodsimage = $goodsimage_model -> find("goods_id = " . $goods_id." order by sort_order");
         $this -> assign("goodsimage",$goodsimage);
         $this -> assign("yunfei",$yunfei);
         $this -> assign("goods",$goods);
         if( $_GET['shop_id'] && intval($_GET['shop_id']) > 0 ){
             $user_mod = &m("member");
             $shopOwner = $user_mod->get('user_id='.$_GET['shop_id']);
             $this -> assign("shopOwner",$shopOwner);
             $this -> assign("shop_id",$_GET['shop_id']);
         }
         if($_GET['vshop'] == "1"){
             $this -> assign("vshop",$_GET['vshop']);
         }

         $this -> display("goods.info.html");
     }

    function region(){
        $goods_id = $_POST['goods_id'];
        if($goods_id == ""){
            $goods_id = $_GET['goods_id'];
        }
        $yunfei = $this -> _get_yunfei($goods_id);
        $this -> assign("yunfei",$yunfei);
        $this -> assign("goods_id",$goods_id);
        if( $_GET['shop_id'] && intval($_GET['shop_id']) > 0 ){
            $this -> assign("shop_id",$_GET['shop_id']);
        }
        $this -> display("goods.region.html");
    }

    /* 运费 */
    function  _get_yunfei($id) {
        $yunfei = & m("yunfei");
        $goods_model = & m("goods");
        $goods = $goods_model -> get($id);
        if(!$goods) {
            return false;
        }
        $regions = null;
        $region_mode = &m('region');
        if(isset($goods["shipping_id"])) {
            $sql = "SELECT * FROM {$region_mode->table} reg WHERE reg.parent_id =1 AND EXISTS(SELECT 1 FROM {$yunfei->table} yf WHERE yf.diqu = reg.region_name AND yf.shipping_id=".$goods["shipping_id"].")
                UNION SELECT * FROM {$region_mode->table} reg1 WHERE reg1.parent_id =1 AND  EXISTS(SELECT 1 FROM {$region_mode->table} reg WHERE reg.parent_id =reg1.region_id AND EXISTS(SELECT 1 FROM {$yunfei->table} yf  WHERE yf.diqu = reg.region_name and yf.shipping_id=".$goods["shipping_id"]."))";
            $regions= $yunfei->db->getAll($sql);
        }
        if(!$regions[0]) {
            return false;
        }
        return $regions;
    }


    //选中的运费
    function  get_yunfei($reg_name,$shipping_id) {
        $condition = " diqu ='".$reg_name."' AND shipping_id =".$shipping_id;
        $model_yunfei = &m("yunfei");
        $yunfei= $model_yunfei->get(array('conditions'=>$condition));
        return $yunfei;
    }


    function xuanze(){
        $goods_id = $_POST['goods_id'];
        if($goods_id == ""){
            $goods_id = $_GET['goods_id'];
        }
        $goods_model = & m('goods');
        $goodsspec_model = & m('goodsspec');
        $goods = $goods_model -> get($goods_id);
        $spec1 = array();
        $spec2 = array();
        //查询商品规格
        if($goods['spec_name_1'] != "" || $goods['spec_name_2'] != ""){
            $goodsspec = $goodsspec_model -> find("goods_id = " . $goods_id);
            foreach($goodsspec as $spec){
                array_push($spec1,$spec['spec_1']);
                array_push($spec2,$spec['spec_2']);
            }
            $spec1 = array_unique($spec1);
            $spec2 = array_unique($spec2);
//             print_r($spec1)."-----";
//             print_r($spec2);exit;
            $this -> assign("spec1",$spec1);
            $this -> assign("spec2",$spec2);
        }
        if( intval($_GET['shop_id']) > 0 ){
            $this -> assign("shop_id",$_GET['shop_id']);
        }
        if( $_GET['vshop'] =="1" ){
            $this -> assign("vshop",$_GET['vshop']);
        }
        $this -> assign("goods",$goods);
        $this -> display("goods.xuanze.html");
    }

       function index1(){
            $goods_model = & m('goods');
            $goodsImage_model = & m('goodsimage');
            $gcategory_model = & m('gcategory');
            $recommend_model = & m('recommend');
            $tuijian = $recommend_model -> get("recom_name = '" . conf::get('phone_goods_tuijian')  . "'");
            $hanguoguan = $recommend_model -> get("recom_name = '" . conf::get('phone_goods_hanguoguan')  . "'");
            $jksptuijian = $recommend_model -> get("recom_name = '" . conf::get('phone_goods_jksp')  . "'");
            $datu = $recommend_model -> get("recom_name = '" . conf::get('phone_goods_datu')  . "'");
            $jksp = $this -> _get_goods_remmeond($jksptuijian['recom_id'],3);
            $tuijian = $this -> _get_goods_remmeond($tuijian['recom_id'],2);
            $hanguoguan = $this -> _get_goods_remmeond($hanguoguan['recom_id'],6);
//            $banner = $this -> _get_goods_remmeond($banner['recom_id'],1);
            $datu = $this -> _get_goods_remmeond($datu['recom_id'],1);
//            $banner['image'] = $goodsImage_model -> get('goods_id = ' . $banner['goods_id'])['image_url'];
            if($datu){
                $datu_iamge = $goodsImage_model -> get('goods_id = ' . $datu[0]['goods_id']." order by sort_order");
            }

           //推荐大图
           $brand1 = $this->get_guanggao('weixin_shouye_1_brand');

           //家用电器
           $jydqs = $this->get_guanggao('weixin_shouye_2_name');
           $jydq = $this->get_guanggao('weixin_shouye_2_brand');
           foreach($jydq as $jy){
               $jydqBrand = $jy;
               break;
           }
           $this -> assign("jydqs",$jydqs);
           $this -> assign("jydqBrand",$jydqBrand);

           //美容化妆
           $mrhzs = $this->get_guanggao('weixin_shouye_3_name');
           $mrhz = $this->get_guanggao('weixin_shouye_3_brand');
           foreach($mrhz as $mr){
               $mrhzBrand = $mr;
               break;
           }
           $this -> assign("mrhzs",$mrhzs);
           $this -> assign("mrhzBrand",$mrhzBrand);

           //电脑数码
           $dnsms = $this->get_guanggao('weixin_shouye_4_name');
           $dnsm = $this->get_guanggao('weixin_shouye_4_brand');
           foreach($dnsm as $dn){
               $dnsmBrand = $dn;
               break;
           }
           $this -> assign("dnsms",$dnsms);
           $this -> assign("dnsmBrand",$dnsmBrand);

           //服饰鞋帽
           $fsxms = $this->get_guanggao('weixin_shouye_5_name');
           $fsxm = $this->get_guanggao('weixin_shouye_5_brand');
           foreach($fsxm as $fs){
               $fsxmBrand = $fs;
               break;
           }
           $this -> assign("fsxms",$fsxms);
           $this -> assign("fsxmBrand",$fsxmBrand);

           //吃货天堂
           $chtts = $this->get_guanggao('weixin_shouye_6_name');
           $chtt = $this->get_guanggao('weixin_shouye_6_brand');
           foreach($chtt as $ch){
               $chttBrand = $ch;
               break;
           }
           $this -> assign("chtts",$chtts);
           $this -> assign("chttBrand",$chttBrand);

           //居家生活
           $jjshs = $this->get_guanggao('weixin_shouye_7_name');
           $jjsh = $this->get_guanggao('weixin_shouye_7_brand');
           foreach($jjsh as $jj){
               $jjshBrand = $jj;
               break;
           }
           $this -> assign("jjshs",$jjshs);
           $this -> assign("jjshBrand",$jjshBrand);

           //妈咪宝贝
           $mmbbs = $this->get_guanggao('weixin_shouye_8_name');
           $mmbb = $this->get_guanggao('weixin_shouye_8_brand');
           foreach($mmbb as $mm){
               $mmbbBrand = $mm;
               break;
           }
           $this -> assign("mmbbs",$mmbbs);
           $this -> assign("mmbbBrand",$mmbbBrand);

           foreach($brand1 as $br1){
               $bd1 = $br1;
               break;
           }
           $this->assign('lunbo',$this->get_guanggao('weixin_shouye_lunbo'));
           $this->assign('brand1',$bd1);

            $datu[0]['image'] = $datu_iamge['image_url'];
            $gcategory = $gcategory_model -> get(1284);
            $gcategorys = $gcategory_model -> find("parent_id = " . $gcategory['cate_id'] . " limit 0,6");
//            $this -> assign("banner",$banner);
            $this -> assign('jksp',$jksp);
            $this -> assign("datu",$datu[0]);
            $this -> assign("tuijian",$tuijian);
            $this -> assign("hanguoguan",$hanguoguan);
            $this -> assign("gcategory",$gcategory);
            $this -> assign("gcategorys",$gcategorys);

           $userId = $this -> visitor ->get("user_id");
           $user_mod = &m("member");
           $user = $user_mod->get('user_id='.$userId);
           $this->assign("user",$user);
           $this -> display("goods.index.html");
       }

    /*推荐商品*/
    function _get_goods_remmeond($id,$num)
    {
        $recom_mod =& m('recommend');
        $data = $recom_mod->get_recommended_goods($id, $num, true);
//        $this->pr($data);
//        exit;
        return $data;
    }

    function goodsList(){
        $r_id = $_GET['r_id'];
        $sn = $_GET['sn'];
        $key = $_GET['key'];
        $cate_id = $_GET['cate_id'];
        $goods_model = & m('goods');
        $flag = $_GET['flag'];
        $page = $_GET['page'];
        $keyWord = $_GET['keyWord'];
        $ifAdd = $_GET['ifAdd'];

        if($r_id == 39){
            $this -> display("hgg.hgtz.html");
        }else{
            if($r_id == ""){
                if($keyWord == ""){
                    $count = $goods_model -> db -> getOne("select count(*) from ecm_goods where (cate_id = " . $cate_id . " OR cate_id_1 = " .$cate_id . "
                                                     OR cate_id_2 = " . $cate_id . " OR cate_id_3 = " .$cate_id . " OR cate_id_4 = ".$cate_id  .")  AND if_show = 1 AND type != 'weidian' AND closed = 0 AND dropState = 1 AND store_id > 0 AND if_jifen = 0");
                }else{
                    $count = $goods_model -> db -> getOne("select count(*) from ecm_goods where goods_name like '%" .$keyWord . "%' AND pd_id != 5 AND if_show = 1 AND type != 'weidian' AND closed = 0 AND dropState = 1 AND store_id > 0 AND if_jifen = 0");
                }
            }else{
                    $goods = $this -> _get_goods_remmeond($r_id,100000);
                    $count = count($goods);
                if($r_id == 39){
                    $goods1 = $this -> _get_goods_remmeond(50,100000);
                    $count = $count + count($goods1);
                }
            }
            // 分页
            $totalPage = 0;
            if($count % 20 == 0){
                $totalPage = $count / 20;
            }else{
                $totalPage = intval($count / 20) + 1;
            }
            if($page == ""){
                $page = 1;
            }
            if($flag == "1"){
                if($page >= $totalPage){$page = $totalPage;}else{$page = $page + 1;}
            }
            if($flag == "2"){
                if($page <= 1){$page = 1;}else{$page = $page - 1;}
            }

    //        //显示更多
    //        $limit = "";
    //        if($count == 0){
    //            $page = 20;
    //        }else{
    //            if($page >= $count){
    //                $limit = " LIMIT 0,".$page;
    //            }else{
    //                if($page != "" || $page != null){
    //                    if($ifAdd == ""){
    //                        $page = $page + 20;
    //                    }
    //                    $limit = " LIMIT 0,".$page;
    //                }else{
    //                    $page = 20;
    //                    $limit = " LIMIT 0,".$page;
    //                }
    //            }
    //        }


            // 分页
            if($r_id == ""){
                if($keyWord == ""){
                    if($key == ""){
                        $sql = "SELECT * FROM ecm_goods AS gs LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
                                             OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.dropState = 1 AND gs.if_jifen = 0 AND gs.if_show = 1 AND gs.type != 'weidian' AND gs.closed = 0 ORDER BY gt.views DESC LIMIT " . ($page - 1) * 20 . ",20";
                    }else{
                        $sql = "SELECT * FROM ecm_goods AS gs WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
                                             OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.dropState = 1 AND gs.if_jifen = 0 AND gs.if_show = 1 AND gs.type != 'weidian' AND gs.closed = 0 ORDER BY gs.price LIMIT " . ($page - 1) * 20 . ",20";
                    }
                }else{
                    if($key == ""){
                        $sql = "SELECT * FROM ecm_goods AS gs LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id WHERE
                                           gs.goods_name like '%" . $keyWord . "%'  AND gs.pd_id != 5 AND gs.dropState = 1 AND gs.if_jifen = 0 AND gs.if_show = 1 AND gs.type != 'weidian' AND gs.closed = 0 ORDER BY gt.views DESC LIMIT " . ($page - 1) * 20 . ",20";
                    }else{
                        $sql = "SELECT * FROM ecm_goods AS gs WHERE  gs.goods_name like '%" . $keyWord . "%' AND gs.pd_id != 5 AND gs.dropState = 1 AND gs.if_jifen = 0 AND gs.if_show = 1 AND gs.type != 'weidian' AND gs.closed = 0 ORDER BY gs.price LIMIT " . ($page - 1) * 20 . ",20";
                    }
                }
            }

            //显示更多
    //        if($r_id == ""){
    //            if($keyWord == ""){
    //                if($key == ""){
    //                    $sql = "SELECT * FROM ecm_goods AS gs LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
    //                                         OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND store_id > 0 ORDER BY gt.views DESC " . $limit;
    //                }else{
    //                    $sql = "SELECT * FROM ecm_goods AS gs WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
    //                                         OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND store_id > 0 ORDER BY gs.price " . $limit;
    //                }
    //            }else if($r_id == ""){
    //                if($key == ""){
    //                    $sql = "SELECT * FROM ecm_goods AS gs LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id WHERE
    //                                       gs.goods_name like '%" . $keyWord . "%' AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND store_id > 0 ORDER BY gt.views DESC " . $limit;
    //                }else{
    //                    $sql = "SELECT * FROM ecm_goods AS gs WHERE  gs.goods_name like '%" . $keyWord . "%' AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND store_id > 0 ORDER BY gs.price " . $limit;
    //                }
    //            }
    //        }
            if($cate_id != ""){
                $gcategory_model = & m('gcategory');
                $gt = $gcategory_model -> get($cate_id);
                $this -> assign("gt",$gt);
            }
            if($r_id != ""){
                $goods2 = array();
                $goods = $this -> _get_goods_remmeond($r_id,10000);
                foreach($goods as $gd){
                    array_push($goods2,$gd);
                }
                if($r_id == 39){
                    $goods1 = $this -> _get_goods_remmeond(50,10000);
                    foreach($goods1 as $gs){
                        array_push($goods2,$gs);
                    }
                }
                $page1 = ($page - 1) * 20;
                $goods = array();
                for($i = 0;$i < 20;$i++){
                    if(count($goods2[$page1]) > 0){
                       array_push($goods,$goods2[$page1]);
                       $page1++;
                    }else{
                       $page1++;
                    }
                }
                $name = unserialize(NAME);
                $name = $name[$r_id];
                $this -> assign("name",$name);
            }else{
                $goods = $goods_model -> db -> getAll($sql);
            }

            $count1 = count($goods);
            $this -> assign("r_id",$r_id);
            $this -> assign("count",$count1);
            $this -> assign("totalCount",$count);
            $this -> assign("key",$key);
            $this -> assign("keyWord",$keyWord);
            $this -> assign("cate_id",$cate_id);
            $this -> assign("goods",$goods);
            $this -> assign("page",$page);
            $this -> assign("sn",$sn);
            $this -> assign("totalPage",$totalPage);
            if($sn == ""){
                $this -> display("goods.list.html");
            }else{
                $this -> display("goods.list1.html");
            }
        }
    }

    function xq(){
        $goods_id = $_GET['goods_id'];
        $goods_model = & m('goods');
        $goods = $goods_model -> get($goods_id);
        $goods['description'] = str_replace('img','img1',$goods['description']);
        if( $_GET['shop_id'] && intval($_GET['shop_id']) > 0 ){
            $user_mod = &m("member");
            $shopOwner = $user_mod->get('user_id='.$_GET['shop_id']);
            if($_GET['vshop'] == "1"){
                $this -> assign("vshop",$_GET['vshop']);
            }
            $this -> assign("shopOwner",$shopOwner);
            $this -> assign("shop_id",$_GET['shop_id']);
        }
        $this -> assign('goods',$goods);
        $goodsimage_model = & m('goodsimage');
        $goodsimage = $goodsimage_model -> find("goods_id = " . $goods['goods_id']." order by sort_order");
        $this -> assign('goodsimage',$goodsimage);
        $this -> display("goods.xq.html");
    }
    function big_img(){
        if(!$_GET['image_id'] && $_GET['image_id']==null || $_GET['image_id']==""){
            echo "image_id参数错误";
            return;
        }
        $goodsimage_model = & m('goodsimage');
        $goodsimage = $goodsimage_model -> get($_GET['image_id']);
        if(!$goodsimage || $goodsimage['goods_id'] <= 0 ){
            echo "没有这个商品!";
            return;
        }else{
            $this->assign("goodsimage",$goodsimage);
            $this->display("goods.big_img.html");
        }
    }
}
?>
