<?php

/*分类控制器*/
class CategoryApp extends MallbaseApp
{
    /* 商品分类 */
    function index()
    {
        $gcategory_model = & m('gcategory');
        $gcategorys = $gcategory_model -> find("parent_id = 0 and dropState = 1 and if_show = 1 and store_id = 0 limit 0,10");
        $goods_model = & m('goods');
        foreach($gcategorys as $key=>$gcategory){
            $sql = "SELECT * FROM ecm_goods AS gs WHERE (gs.cate_id = " .$gcategory['cate_id'] ." OR gs.cate_id_1 = " .$gcategory['cate_id']." OR gs.cate_id_2 = ".$gcategory['cate_id']."
                                 OR gs.cate_id_3 = ".$gcategory['cate_id']." OR gs.cate_id_4 = ".$gcategory['cate_id'].") AND gs.dropState = 1 AND gs.if_show = 1 AND gs.closed = 0 AND gs.store_id > 0 order by gs.goods_id limit 0,10";
            $goods = $goods_model -> db -> getAll($sql);
            foreach($goods as $ke=>$value){
                if($value['default_image'] != ""){
                    $good = $goods[$ke];
                    break;
                }
            }
            $gtr = $gcategory_model -> find("parent_id = " . $gcategory['cate_id'] ." and dropState = 1");
            $gcategorys[$key]['gtr'] = $gtr;
            $gcategorys[$key]['image'] = $good['default_image'];
        }
        $this -> assign("gcategorys",$gcategorys);
        $this->display('cate.list.html');
    }


    function index1()
    {
        $cate_id = $_GET['cate_id'];
        $gcategory_model = & m('gcategory');
        $goods_model = & m('goods');
        $gt = $gcategory_model -> get($cate_id);
        $gcategorys = $gcategory_model -> find("parent_id = " . $cate_id ." and dropState = 1 and if_show = 1 and store_id = 0");
        foreach($gcategorys as $key=>$gcategory){
            $sql = "SELECT COUNT(*) FROM ecm_goods WHERE (cate_id = " . $gcategory['cate_id'] . " OR cate_id_1 = " .$gcategory['cate_id'] . "
                                            OR cate_id_2 = " . $gcategory['cate_id'] . " OR cate_id_3 = " . $gcategory['cate_id'] . " OR cate_id_4 = ".$gcategory['cate_id']  .")  AND dropState = 1 AND if_show = 1 AND closed = 0 AND store_id > 0";
            $count = $goods_model -> db -> getOne($sql);
            $gcategorys[$key]['count'] = $count;
        }
        $goods = $goods_model -> find("(cate_id = " . $cate_id . " OR cate_id_1 = " .$cate_id . "
                                            OR cate_id_2 = " . $cate_id . " OR cate_id_3 = " .$cate_id . " OR cate_id_4 = ".$cate_id  .")  AND dropState = 1 AND if_show = 1 AND closed = 0 AND store_id > 0 limit 0,3");
        $this -> assign("goods",$goods);
        $this -> assign("gt",$gt);
        $this -> assign("gcategorys",$gcategorys);
        $this->display('cate.list1.html');
    }

        /* 店铺分类 */
    function store()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());
        /* 取得商品分类 */
        $scategorys = $this->_list_scategory();
        /* 取得最新店铺 */
        $new_stores = $this->_new_stores(5);
        /* 取得推荐店铺 */
        $recommended_stores = $this->_recommended_stores(5);
        /* 当前位置 */
        $_curlocal=array(
            array(
                'text'  => Lang::get('index'),
                'url'   => 'index.php',
            ),
            array(
                'text'  => Lang::get('scategory'),
                'url'   => '',
            ),
        );
        $this->assign('_curlocal',$_curlocal);
        $this->assign('new_stores', $new_stores);
        $this->assign('recommended_stores', $recommended_stores);
        $this->assign('scategorys', $scategorys);

        $this->_config_seo('title', Lang::get('store_category') . ' - '. Conf::get('site_title'));
        $this->display('category.store.html');
    }

        /* 取得商品分类 */
    function _list_gcategory()
    {
        $cache_server =& cache_server();
        $key = 'page_goods_category';
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
            $gcategories = $gcategory_mod->get_list(-1,true);
    
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
            $data = $tree->getArrayList(0);

            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

            /* 取得店铺分类 */
    function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $scategories = $scategory_mod->get_list(-1,true);

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

            /* 取得最新店铺 */
    function _new_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions' => 'state = 1 and s.dropState=1 ',
            'order' => 'add_time DESC',
            'join'  => 'belongs_to_user',
            'limit' => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }

        return $stores;
    }

          /* 取得推荐店铺 */
    function _recommended_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions'    => 'recommended=1 AND state = 1 and s.dropState=1',
            'order'         => 'sort_order',
            'join'          => 'belongs_to_user',
            'limit'         => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }
        return $stores;
    }

    function hgg(){
        $rids = Array(
            34=>"首尔超市",
            35=>"跟上韩流",
            36=>"生活家园",
            37=>"美丽世界",
            38=>"韩国旅游",
            39=>"韩国特展"
        );
        $recom_mod =& m('recommend');
        $hgg = array();
        foreach($rids as $key=>$value){
            $re_name = $value;
            $hgg[$key]['name'] = $re_name;
            $hgg[$key]['goods'] = $this -> _get_goods_remmeond($key,2);
        }
        $this -> assign("hgg",$hgg);
        $this -> assign("title","韩国馆");
        $this -> display("hgg.list.html");
    }

    function tsdj(){
        $rids = array(
            22 => '当地土特产',
            45 => '休闲食品',
            20 => '营养健康',
            19 => '有机食品',
        );
        $recom_mod =& m('recommend');
        $hgg = array();
        foreach($rids as $key=>$value){
            $re_name = $value;
            $hgg[$key]['name'] = $re_name;
            $hgg[$key]['goods'] = $this -> _get_goods_remmeond($key,2);
        }
        $this -> assign("hgg",$hgg);
        $this -> assign("title","特色大集");
        $this -> display("hgg.list.html");
    }

    function gczg(){
        $rids = array(
            42 => '品牌集',
            44 => '集市特卖',
        );
        $recom_mod =& m('recommend');
        $hgg = array();
        foreach($rids as $key=>$value){
            $re_name = $value;
            $hgg[$key]['name'] = $re_name;
            $hgg[$key]['goods'] = $this -> _get_goods_remmeond($key,2);
        }
        $this -> assign("hgg",$hgg);
        $this -> assign("title","工厂直供");
        $this -> display("hgg.list.html");
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

}

?>