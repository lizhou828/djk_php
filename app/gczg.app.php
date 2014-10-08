<?php


class GczgApp extends MallbaseApp
{
    function index()
    {
        $region_mod =& m('region');
        $tmp = $region_mod->get_options(1);
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo('title', Lang::get('gczg'));
        $filter = array();
        if ($_GET['gczg_area']) {
            $filter['gczg_area'] = $_GET['gczg_area'];
        }
        $cate_ids = null;
        $gcategory_mode = & bm('gcategory');
        $_pdgcategory_mod = bm("pdcategory");
        if ($_GET["cate_id"]) {
            $cate_ids = $gcategory_mode->get_descendant($_GET["cate_id"]);
        } else {
            $cate_ids = $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
        }
//        $this->assign('hot_goods', $this->_get_hot_goods($cate_ids));
        $this->assign('hot_goods', $this->_get_hot_goods());
//        $data = $this->get_goods_byarea($_GET['gczg_area'], $cate_ids);
        $data = $this->get_tuijian_list();
        $this->assign("goods_list", $data['goods_list']);
        $this->assign('gczg_category', $this->_list_gcategory($_GET["pd_id"]));
        $this->assign('filter', $filter);
        $this->assign('region', $tmp);
        $this->assign("page_info",$data['page']);
        $this->display('gczg.index.html');
    }


///热销关键字
    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

//根据地区获取商品
    function  get_goods_byarea($param, $cate_ids)
    {
        $store_mode = & m('store');
        $goods_mode = & m('goods');
        $sql = "select region_name,store_id from {$store_mode->table} where state=1 and dropState = 1";
        $res = $store_mode->db->query($sql);
        $data = null;
        while ($row = $store_mode->db->fetchRow($res)) {
            $data[$row['store_id']] = $row['region_name'];
        }
        $region_name = null;
        if ($data) {
            foreach ($data as $key => $val) {
                $arr = explode("\t", $val);
                $region_name[$key] = $arr[1];
            }
        }

        $stroe_ids = null;
        foreach ($region_name as $key => $str) {
            if ($param["gczg_area"] == $str || $param["gczg_area"] == "") {
                if ($stroe_ids == null) {
                    $stroe_ids = $key;
                } else {
                    $stroe_ids .= "," . $key;
                }
            }
        }
        $page   =   $this->_get_page(18);

        $goods_list = $goods_mode->get_list(array(
            'conditions' => " g.if_show = 1 AND g.closed = 0  and g.dropState=1 and g.store_id " . db_create_in($stroe_ids) . " and cate_id_1 " . db_create_in($cate_ids),
            'count' => true,
            'limit' => $page['limit']
        ));

        $data['goods_list'] = $goods_list;
        $page['item_count'] = $goods_mode->getCount();
        $this->_format_page($page);
        $data['page'] = $page;
        return $data;
    }

//推荐商品
    function get_tuijian_list()
    {
        $recom_mod =& m('recommend');
        $goods_list = $recom_mod->get_recommended_goods(Conf::get('gczg-index-list'), 10000, true, 0);
        $data['goods_list'] = $goods_list;
        return $data;
    }

    function get_brand_goods() {
        $recom_mod =& m('recommend');
        $data = $recom_mod->get_recommended_goods(Conf::get("gczg-brand"), 30, true, 0);
        $this->assign("goods_list", $data);
        $this->display("gczg.brand.html");
    }

    //品牌
//    function get_brand_goods()
//    {
//        $this->_config_seo('title', Lang::get('gczg'));
//        $region_mod =& m('region');
//        $tmp = $region_mod->get_options(1);
//        $param = $this->_get_query_param();
//        $filter = $this->_get_filter($param);
//        $brands = $this->get_brand();
//        $this->assign('brands', $brands);
//        $goods_mode = & m('goods');
//        $goods_list = null;
//        $brand_name = null;
//        if ($brands) {
//            foreach ($brands as $brand) {
//                if ($brand_name == null) {
//                    $brand_id = $brand["brand_id"];
//                } else {
//                    $brand_id .= "," . $brand["brand_id"];
//                }
//            }
////            $this->pr($brand_name);exit;
////            $this->pr(db_create_in($brand_id));exit;
//            $conditions = " g.if_show = 1 and g.closed = 0 and g.dropState=1 and g.brand_id ".db_create_in($brand_id);
//            if (!empty($param['gczg_area'])) {
//                $store_ids = $this->get_store_byarea($param['gczg_area']);
//                $conditions.="and g.store_id " . db_create_in($store_ids);
//            }
//          $page['item_count'] = $goods_mode->getCount();
//          $goods_list = $goods_mode->get_list(array(
//                'conditions' => $conditions,
//                 'count' => true,
//                 'limit' => $page['limit']
//            ));
//        }
//
//        $this->assign('filters', $filter);
//        $this->assign('region', $tmp);
//        $page   =   $this->_get_page(18);
//        $this->_format_page($page);
//        $data['page'] = $page;
//        $this->assign("goods_list", $goods_list);
//        $this->assign("page_info",$data['page']);
//        $this->assign('hot_keywords', $this->_get_hot_keywords());
//        $this->display("gczg.brand.html");
//    }

    /*热销品*/
//    function _get_hot_goods($cate_ids)
//    {
//        $goods_mod = & m('goods');
//
//        $goods_list = $goods_mod->get_list(array(
//            'conditions' => "if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN,
//            'scate_ids' => $cate_ids,
//            'order' => 'sales DESC',
//            'join' => 'has_goodsstatistics, belongs_to_store',
//            'limit' => 5, false, true
//        ));
//        foreach ($goods_list as $key => $goods) {
//            if (strpos($goods['default_image'], '/') === false) {
//                $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
//            }
//        }
//        return $goods_list;
//    }

    //推荐
    function  _get_hot_goods(){
        $recom_mod =& m('recommend');
        $data = $recom_mod->get_recommended_goods(Conf::get("gczg-index-hot"), 30, true, 0);
        return $data;
    }

//获取预售
//    function get_yushou_goods()
//    {
//        $this->_config_seo('title', Lang::get('gczg'));
//        $gcategory_mode = & bm('gcategory');
//        $goods_mod = & m('goods');
//        $param = $this->_get_query_param();
//        $filter = $this->_get_filter($param);
//        $yushou_mode = & m('yushou');
//        $store_ids = $this->get_store_byarea($param);
//
//        if ($store_ids) {
//            $yushou_list = $yushou_mode->find(array(
//                'conditions' => 'state=1 and store_id ' . db_create_in($store_ids)." and type=".$param['type'],
//            ));
//        } else {
//            $yushou_list = $yushou_mode->find(array(
//                'conditions' => "state=1 and store_id=0 and type=".$param['type'],
//            ));
//        }
//        $goods_ids = null;
//        if ($yushou_list) {
//            foreach ($yushou_list as $yushou) {
//                if ($goods_ids == null) {
//                    $goods_ids = $yushou['goods_id'];
//                } else {
//                    $goods_ids .= "," . $yushou['goods_id'];
//                }
//            }
//        }
//        $cate_ids = $yushou_mode->get_cate_id($param['type']);
//        $gcategory_list = null;
//
//        $conditions = 'if_show = 1 AND closed = 0 and dropState=1 and cate_id_1'.db_create_in($cate_ids);
//        if ($param['gczg_class']) {
//            $conditions .= ' and cate_id_1 = ' . $param['gczg_class'];
//        }
//        if ($goods_ids) {
//            $conditions .= ' and goods_id ' . db_create_in($goods_ids);
//            $page = $this->_get_page(10);
//            $goods_list = $goods_mod->find(array(
//                'conditions' => $conditions,
//                'limit' => $page['limit'],
//            ));
//        }
//        foreach ($cate_ids as $cate_id) {
//            $gcategory_list[] = $gcategory_mode->get($cate_id);
//        }
//        $region_mod =& m('region');
//        $tmp = $region_mod->get_options(1);
//        $this->assign('gcategory_list', $gcategory_list);
//        $this->assign('goods_list', $goods_list);
//        $this->assign('filters', $filter);
//        $this->assign('region', $tmp);
//        $this->display('gczg.yushou.html');
//    }
    function get_yushou_goods(){
        $param = $this->_get_query_param();
        $filter = $this->_get_filter($param);
        $this->_config_seo('title', Lang::get('gczg'));
        $recom_mod =& m('recommend');
        if($param['type'] ==1) {
            $goods_list = $recom_mod->get_recommended_goods(Conf::get('gczg-yushou'), 10000, true, 0);
        } else {
            $goods_list = $recom_mod->get_recommended_goods(Conf::get('gczg-temai'), 10000, true, 0);
        }
        $this->assign('filters', $filter);
        $this->assign('goods_list',$goods_list);
        $this->display('gczg.yushou.html');
    }
//获取参数
    function _get_query_param()
    {
        static $res = null;
        if ($res === null) {
            $res = array();
            // area
            $gczg_area = isset($_GET['gczg_area']) ? trim($_GET['gczg_area']) : '';
            $res['gczg_area'] = $gczg_area;
            if (isset($_GET['gczg_brand'])) {
                $res['brand_id'] = $_GET['gczg_brand'];
            }
            if (isset($_GET['gczg_class'])) {
                $res['gczg_class'] = $_GET['gczg_class'];
            }
            if (isset($_GET['gczg_subcateid'])) {
                $res['gczg_subcateid'] = $_GET['gczg_subcateid'];
            }
            if (isset($_GET['pd_id'])) {
                $res['pd_id'] = $_GET['pd_id'];
            }
            if (isset($_GET['type'])) {
                $res['type'] = $_GET['type'];
            }
        }
        return $res;
    }

    //获得品牌
    function get_brand()
    {
        $_pdgcategory_mod = bm("pdcategory");
        $cate_ids = $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
        $gcategory_mode = & bm('gcategory');
        $category_brand_mode = & m('categorybrand');
        $brand_mode = & m('brand');
        $gcategoryids = null;
        $ids = null;
        if ($cate_ids) {
            foreach ($cate_ids as $cate_id) {
                $ids[] = $gcategory_mode->get_descendant($cate_id);
            }
            if ($ids) {
                foreach ($ids as $id) {
                    foreach ($id as $child) {
                        if ($gcategoryids == "") {
                            $gcategoryids = $child;
                        } else {
                            $gcategoryids .= "," . $child;
                        }
                    }
                }
            }
            $sql = "SELECT distinct b.brand_id,b.brand_logo,b.brand_name from {$category_brand_mode->table} as gb,{$brand_mode->table} as b where b.brand_id=gb.brand_id and b.dropState=1 and b.if_show =1 and gb.category_id " . db_create_in($gcategoryids);
            $brands = $category_brand_mode->db->getAll($sql);
            return $brands;
        }
        return null;
    }

//记住参数
    function _get_filter($param)
    {
        static $filters = null;
        if ($filters === null) {
            $filters = array();
            if (isset($param['gczg_area'])) {
                $filters['gczg_area'] = $param['gczg_area'];
            }
            if (isset($param['brand_id'])) {
                $filters['brand_id'] = $param['brand_id'];
            }
            if (isset($param['gczg_class'])) {
                $filters['gczg_class'] = $param['gczg_class'];
            }
            if (isset($param['gczg_subcateid'])) {
                $filters['gczg_subcateid'] = $param['gczg_subcateid'];
            }
            if (isset($param['pd_id'])) {
                $filters['pd_id'] = $param['pd_id'];
            }
            if (isset($param['type'])) {
                $filters['type'] = $param['type'];
            }
        }
        return $filters;
    }

    function get_store_byarea($param)
    {
        $store_mode = & m('store');
        $sql = "select region_name,store_id from {$store_mode->table} where state=1 and dropState = 1";
        $res = $store_mode->db->query($sql);
        $data = null;
        while ($row = $store_mode->db->fetchRow($res)) {
            $data[$row['store_id']] = $row['region_name'];
        }
        $region_name = null;
        if ($data) {
            foreach ($data as $key => $val) {
                if ($data[$key]) {
                    $arr = explode("\t", $val);
                    $region_name[$key] = $arr[1];
                }
            }
        }
        $stroe_ids = null;
        if (!$param['gczg_area']) {
            $stroe_ids= array_keys($data);
        } else {
            foreach ($region_name as $key => $str) {
                if ($param["gczg_area"] == $str) {
                    if ($stroe_ids == null) {
                        $stroe_ids = $key;
                    } else {
                        $stroe_ids .= "," . $key;
                    }
                }
            }
        }
        return $stroe_ids;
    }

}

?>
