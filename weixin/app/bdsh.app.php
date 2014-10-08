<?php

class BdshApp extends MallbaseApp
{
    function index()
    {
         $region_model = & m('region');
         $rgid = $_GET['rgid'];
         if($rgid == ""){
             $city = $this -> getCity();
         }else{
             $region = $region_model -> get($rgid);
             $city = $region['region_name'];
         }
         $gcategory_model = & m('gcategory');
         $goods_model = & m('goods');
         $cate_sql = "SELECT DISTINCT(gg.cate_id),gg.cate_name FROM ecm_pd_category AS pc JOIN ecm_gcategory AS gg ON gg.cate_id = category_id
                                                                    AND gg.if_show = 1 AND pc.pd_id = 5 AND gg.dropState = 1 AND gg.parent_id = 0";
         $cates = $gcategory_model -> db -> getAll($cate_sql);
         foreach($cates as $key=>$cate){
             $sql = "SELECT * FROM ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id WHERE (gs.cate_id = " .$cate['cate_id'] ." OR gs.cate_id_1 = " .$cate['cate_id']." OR gs.cate_id_2 = ".$cate['cate_id']."
             OR gs.cate_id_3 = ".$cate['cate_id']." OR gs.cate_id_4 = ".$cate['cate_id'].") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 LIMIT 0,10";
             $goods = $goods_model -> db -> getAll($sql);
             foreach($goods as $ke=>$value){
                 $good = array();
                 if($value['default_image'] != ""){
                     $good = $goods[$ke];
                     break;
                 }
             }
             $cates[$key]['image'] = $good['default_image'];
         }
         $this -> assign("rgid",$rgid);
         $this -> assign("cates",$cates);
         $this -> assign("city",$city);
         $this -> display("bdsh.index.html");
    }

    function goodsList(){
        $cate_id = $_GET['cate_id'];
        $gcategory_model = & m('gcategory');
        $region_model = & m('region');
        $goods_model = & m('goods');
        $page = $_GET['page'];
        $flag = $_GET['flag'];
        $keyWord = $_GET['keyWord'];
        $region_id = $_GET['region_id'];
        $order = $_GET['order'];
        $dis = $_GET['dis'];
        $rgid = $_GET['rgid'];
        //计算总条数
        if($region_id != ""){
            $sql1 = " and st.region_id = " . $region_id;
        }else{
            $sql1 = "";
            if($rgid == ""){
                $city = $this -> getCity();
                $region = $region_model -> get("region_name = '" . $city . "' and parent_id != 1 and dropState = 1");
                $rgid = $region['region_id'];
            }
            $regions = $region_model -> find('parent_id = ' .$rgid . ' and dropState = 1');
            foreach($regions as $res){
               if($sql1 == ""){
                   $sql1 = " and (st.region_id = " . $res['region_id'];
               }else{
                   $sql1 = $sql1 . " or st.region_id = " . $res['region_id'];
               }
            }
            $sql1 = $sql1 . ")";
        }
        if($cate_id != ""){
            $totalSql = "SELECT count(*) FROM ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
             OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5" . $sql1;
        }
        if($keyWord != ""){
            $totalSql = "select count(*) from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id where goods_name like '%" .$keyWord . "%' AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5" . $sql1;
        }
        if($cate_id == "" && $keyWord == ""){
            $totalSql = "select count(*) from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id where gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5" . $sql1;
        }
        $count = $goods_model -> db -> getOne($totalSql);


        //分页  计算总页数
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
        $this -> assign('page',$page);
        $this -> assign('flag',$flag);
        $this -> assign('totalPage',$totalPage);


        //根据当前页获取数据
        if($cate_id != ""){
            //排序方式
            if($order != ""){
                if($order == 'hot'){
                    $sql = "SELECT * FROM ecm_goods AS gs  LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id
                                                       LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id
                                WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
                                OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " ORDER BY gt.views DESC LIMIT " . ($page - 1) * 20 .",20";
                }else{
                    $sql = "SELECT * FROM ecm_goods AS gs  LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
                             OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " ORDER BY gs.price LIMIT " . ($page - 1) * 20 .",20";
                }
            }else{
                $sql = "SELECT * FROM ecm_goods AS gs  LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id WHERE (gs.cate_id = " .$cate_id ." OR gs.cate_id_1 = " .$cate_id." OR gs.cate_id_2 = ".$cate_id."
                        OR gs.cate_id_3 = ".$cate_id." OR gs.cate_id_4 = ".$cate_id.") AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " LIMIT " . ($page - 1) * 20 .",20";
            }

            $category = $gcategory_model -> get($cate_id);
            $this -> assign('category',$category);
            $goods = $goods_model -> db -> getAll($sql);
        }
        if($keyWord != ""){
            if($order != ''){
                if($order == 'hot'){
                $sql = "select * from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id
                                                   LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id
                where goods_name like '%" .$keyWord . "%' AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " ORDER BY gt.views DESC LIMIT " . ($page - 1) * 20 .",20";
                }else{
                    $sql = "select * from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id
                    where goods_name like '%" .$keyWord . "%' AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " ORDER BY gs.price LIMIT " . ($page - 1) * 20 .",20";
                }
            }else{
                $sql = "select * from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id  where goods_name like '%" .$keyWord . "%' AND gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " LIMIT " . ($page - 1) * 20 .",20";
            }
            $goods = $goods_model -> db -> getAll($sql);
        }
        if($cate_id == "" && $keyWord == ""){
            if($order != ''){
                if($order == 'hot'){
                    $sql = "select * from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id
                                                   LEFT JOIN ecm_goods_statistics AS gt ON gs.goods_id = gt.goods_id
                where gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " ORDER BY gt.views DESC LIMIT " . ($page - 1) * 20 .",20";
                }else{
                    $sql = "select * from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id
                    where gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " ORDER BY gs.price LIMIT " . ($page - 1) * 20 .",20";
                }
            }else{
                $sql = "select * from ecm_goods AS gs LEFT JOIN ecm_store AS st ON gs.store_id = st.store_id  where gs.if_show = 1 AND gs.closed = 0 AND gs.dropState = 1 AND gs.store_id > 0 AND gs.pd_id = 5 " . $sql1 . " LIMIT " . ($page - 1) * 20 .",20";
            }
            $goods = $goods_model -> db -> getAll($sql);
            $this -> assign('ct',"全部");
        }
        if($region_id != ""){
            $region_model = & m('region');
            $region = $region_model -> get($region_id);
            $this -> assign('region',$region);
        }
        $this -> assign('rgid',$rgid);
        $this -> assign('dis',$dis);
        $this -> assign('order',$order);
        $this -> assign('region_id',$region_id);
        $this -> assign('keyWord',$keyWord);
        $this -> assign('goods',$goods);
        $this -> assign('count1',count($goods) - 1);
        $this -> assign('cate_id',$cate_id);
        $this -> display("bdsh.goods.html");
    }

    function region(){
        $keyWord = $_GET['keyWord'];
        $cate_id = $_GET['cate_id'];
        $order = $_GET['order'];
        $rgid = $_GET['rgid'];
        $city = $this -> getCity();
        $region_model = & m('region');
        $dis = $_GET['dis'];
        if($rgid != ""){
            $region = $region_model -> get($rgid);
        }else{
            $regions = $region_model -> find("region_name = '" . $city . "' and parent_id != 1 and dropState = 1");
            foreach($regions as $key=>$value){
                $region = $regions[$key];
            }
        }
        $regions = $region_model -> find("parent_id = " . $region['region_id'] . " and dropState = 1");
        $this -> assign("rgid",$rgid);
        $this -> assign('dis',$dis);
        $this -> assign('regions',$regions);
        $this -> assign('region',$region);
        $this -> assign('keyWord',$keyWord);
        $this -> assign('cate_id',$cate_id);
        $this -> assign('order',$order);
        $this -> assign('count',count($regions) - 1);
        $this -> display('bdsh.region.html');
    }

    function order(){
        $region_id = $_GET['region_id'];
        $keyWord = $_GET['keyWord'];
        $cate_id = $_GET['cate_id'];
        $dis = $_GET['dis'];
        $rgid = $_GET['rgid'];
        $this -> assign("rgid",$rgid);
        $this -> assign('dis',$dis);
        $this -> assign('keyWord',$keyWord);
        $this -> assign('cate_id',$cate_id);
        $this -> assign('region_id',$region_id);
        $this -> display("bdsh.order.html");
    }

    function cate(){
        $region_id = $_GET['region_id'];
        $order = $_GET['order'];
        $keyWord = $_GET['keyWord'];
        $dis = $_GET['dis'];
        $rgid = $_GET['rgid'];
        $gcategory_model = & m('gcategory');
        $cate_sql = "SELECT DISTINCT(gg.cate_id),gg.cate_name FROM ecm_pd_category AS pc JOIN ecm_gcategory AS gg ON gg.cate_id = category_id
                                                                    AND gg.if_show = 1 AND pc.pd_id = 5 AND gg.dropState = 1 AND gg.parent_id = 0";
        $cates = $gcategory_model -> db -> getAll($cate_sql);
        foreach($cates as $key=>$ct){
            $gtg = $gcategory_model -> find("parent_id = " . $ct['cate_id'] . " and dropState = 1 and if_show = 1 and store_id = 0");
            $cates[$key]['gcategorys'] = $gtg;
        }
        $this -> assign("rgid",$rgid);
        $this -> assign('cates',$cates);
        $this -> assign('dis',$dis);
        $this -> assign('keyWord',$keyWord);
        $this -> assign('region_id',$region_id);
        $this -> assign('order',$order);
        $this -> display("bdsh.cate.html");
    }

    function xzregion(){
        $region_model = & m('region');
        $regions = $region_model -> find('parent_id = 1 and dropState = 1');
        foreach($regions as $key => $value){
            $rgs = $region_model -> find('parent_id = ' .$value['region_id'] . ' and dropState = 1');
            $regions[$key]['cld'] = $rgs;
        }
        $city = $this -> getCity();
        $this -> assign('city',$city);
        $this -> assign('regions',$regions);
        $this -> display('bdsh.xzregion.html');
    }

    function ajax_yzCity(){
        $cityName = $_POST['cityName'];
        if($cityName == "" || $cityName == "输入城市名"){
            echo json_encode(array("flag"=>"null"));
            return;
        }
        $region_model = & m('region');
        $region = $region_model -> get("region_name = '" . $cityName . "' and parent_id != 1 and dropState = 1");
        if($region == ""){
            echo json_encode(array("flag"=>"error"));
            return;
        }
        echo json_encode(array("flag"=>"ok","rgid"=>$region['region_id']));
        return;
    }

}

?>
