<?php


class HggApp extends MallbaseApp
{
    function __construct()
    {
        $this->HggApp();

        include_once(ROOT_PATH . '/app/search.app.php');
        include_once(ROOT_PATH . '/app/goods.app.php');
        include_once(ROOT_PATH . '/app/bdsh.app.php');
    }
    function HggApp()
    {
        parent::__construct();
    }
    function index()
    {
        //商品分类
        $this->assign('hgg_category',$this->_list_gcategory($_GET["pd_id"]));
        /*精品推荐*/
//        if ($_GET['beta']) {
        $this->assign('recommendeds', $this->_get_goods_remmeond());
//        }
        /*品牌精选*/
        $this->assign('brands',$this->_get_hot_brand());

        $this->assign('brands1',$this->_get_hot_brand());
        /*商品排行*/
//        $this->assign('topSales', $this -> _get_top_sale());
        /*商品模块展示*/
        $cache = & cache_server();
        $goods_module = $cache->get('good_module');
        if($goods_module === false){
            $goods_module1 = $this->goods_model(conf::get('hgg_index_layer_1'));
            $goods_module2 = $this->goods_model(conf::get('hgg_index_layer_2'));
            $goods_module3 = $this->goods_model(conf::get('hgg_index_layer_3'));
            $goods_module4 = $this->goods_model(conf::get('hgg_index_layer_4'));
            $goods_module5 = $this->goods_model(conf::get('hgg_index_layer_5'));
            $goods_module6 = $this->goods_model(conf::get('hgg_index_layer_6'));
            $goods_module = array('goods_module1' => $goods_module1, 'goods_module2' => $goods_module2,
            'goods_module3' => $goods_module3, 'goods_module4' => $goods_module4,'goods_module5' => $goods_module5,'goods_module6' => $goods_module6);
            $cache->set('good_module',$goods_module,1800);
        }
//        if ($_GET['beta']) {
            $this->assign('goods_module1', $goods_module['goods_module1']);
            $this->assign('goods_module2', $goods_module['goods_module2']);
            $this->assign('goods_module3', $goods_module['goods_module3']);
            $this->assign('goods_module4', $goods_module['goods_module4']);
            $this->assign('goods_module5', $goods_module['goods_module5']);
            $this->assign('goods_module6', $goods_module['goods_module6']);
//        }
        $this->_config_seo('title', Lang::get('hgg_title'));
        $this->assign('pd_id',$_GET['pd_id']);
        $this->display('hgg.index.html');
    }

    //品牌精选
    function _get_hot_brand() {
//        $goods_mode = & m('goods');
//        $sql ="SELECT DISTINCT b.brand_id,b.brand_name, b.brand_logo FROM
//        ".DB_PREFIX."brand AS b, ".DB_PREFIX."goods AS g where  g.if_show=1 AND g.dropState=1 AND b.dropState=1 and g.brand_id = b.brand_id AND g.if_ruzhu =1";
//        $brands= $goods_mode->db->getAll($sql);
        $brands = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14');
        return $brands;
    }

    /*推荐商品*/
    function _get_goods_remmeond()
    {
        $cache_server =& cache_server();
        $key = 'best_goods';
        $num = '10';
        $data = $cache_server->get($key);

        if ($data === false) {
            $recom_mod =& m('recommend');
            $data = $recom_mod->get_recommended_goods(conf::get('hgg_index_jinpin'), $num, true, '10');
            $cache_server->set($key, $data, 1800);
        }
//        $this->pr($data);
//        exit;
        return $data;
    }


    /*商品模块展示*/
    function goods_model($recom_id)
        {
            $num = '10';
//        $cache_server =& cache_server();
//        $key ="goods_model_'".$recom_id."'";
//        $data = $cache_server->get($key);
//        if($data === false)
//        {
            $recom_mod =& m('recommend');
            $gcategory_mod = & bm('gcategory');
            $goods_mod = & m('goods');
            $brand_mod = & m('brand');
            $cate_goods_list = $recom_mod->get_recommended_goods($recom_id,$num, true, 0);
            $gcategorys_goods = array();
            $catIds = array();
            $brand_names = array();
            $brands = array();
            foreach ($cate_goods_list as $values) {
                array_push($catIds,$values["cate_id_1"]);
                if ($values["brand"] != "" && $values["brand"] != null) {
                    array_push($brand_names, $values["brand"]);
                }
            }
            foreach (array_unique(array_values($brand_names)) as $brand_name) {
                array_push($brands, $brand_mod->find(array(
                    'conditions' => "brand_name ='" . $brand_name . "'",
                    'limit' => 10,
                )));
            }
            foreach (array_unique(array_values($catIds)) as $catId) {
                array_push($gcategorys_goods, array("fachercate" => $gcategory_mod->get($catId), array("childcate" => $gcategory_mod->get_children($catId, false))));
            }

            $img_goods_list = $recom_mod->get_recommended_goods($recom_id, $num, true, 0);
            $txt_goods_list = $recom_mod->get_recommended_goods($recom_id, $num, true, 0);
//        $top_goods_sale = $recom_mod->get_recommended_goods($recom_id,10,true,0);
//
            $top_goods_sale =null;
            if($catIds) {
                $top_goods_sale = $goods_mod->get_list(array(
                    'conditions' => "if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN,
                    'scate_ids' => array_unique(array_values($catIds)),
                    'order' => 'sales DESC',
                    'fields' => 'g.goods_id, g.goods_name',
                    'join' => 'has_goodsstatistics, belongs_to_store',
                    'limit' => $num,  false, true
                ));
            }

//            $cache_server->set($key, array(
//                'gcategorys_goods' => $gcategorys_goods,
//                'img_goods_list' => $img_goods_list,
//                'txt_goods_list' => $txt_goods_list,
//                'top_goods_sale'=> $top_goods_sale,
//                'brands'=> $brands,
//            ), 18000);
//        }
            return array(
                'gcategorys_goods' => $gcategorys_goods,
                'img_goods_list' => $img_goods_list,
                'txt_goods_list' => $txt_goods_list,
                'top_goods_sale' => $top_goods_sale,
                'brands' => $brands,
            );
        }

    //商品展示
    function search(){
        //商品分类
        $this->assign('hgg_category',$this->_list_gcategory($_GET["pd_id"]));
        $searchApp = new SearchApp();
        $param = $this->_get_query_param();
        if (empty($param))
        {
            header('Location: index.php?app=category');
            exit;
        }
        if (isset($param['cate_id']) && $param['layer'] === false)
        {
            $this->show_warning('no_such_category');
            return;
        }
        $this->assign('filters', $this->_get_filter($param));
        /* 按分类、品牌、地区、价格区间统计商品数量 */
        $stats = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);
        $this->assign('categories', $stats['by_category']);
        $this->assign('category_count', count($stats['by_category']));

        $this->assign('brands', $stats['by_brand']);

        $this->assign('brand_count', count($stats['by_brand']));

        $this->assign('price_intervals', $stats['by_price']);

        $this->assign('regions', $stats['by_region']);

        $this->assign('ky',$param['keyword']);


//        /* 排序 */
//        $orders = $searchApp->_get_orders();
//        $this->assign('orders', $orders);

        $this->assign('hot_keywords', $this->_get_hot_keywords());
        /* 分页信息 */


        /*
        $page['first_link'] = $page["page_links"][1]; // 首页链接
        $page['last_link'] =  $page["page_links"][$page["page_count"]]; // 尾页链接*/
        //$this->assign('page_links_2', array("first_link"=>$page["page_links"][1],"last_link"=> $page["page_links"][$page["page_count"]]));


        $page = $this->_get_page(NUM_PER_PAGE);
        /* 商品列表 */
        $sgrade_mod =& m('sgrade');
        $sgrades    = $sgrade_mod->get_options();
        $conditions = $this->_get_goods_conditions($param);
        $attrid = $searchApp->_get_attrid_condition($param);
        $attrvalue = $searchApp->_get_attrvalue_condition($param);
        $goods_mod2  =& m('goods');
        $goods_list = $goods_mod2->get_list(array(
            'attrid' => $attrid,
            'attrvalue' => $attrvalue,
            'conditions' => $conditions,
            'order'      => (($_GET['order']==null || $_GET['order']=="")?"views":$_GET['order'])." ".$param['sort_order'],
            'count' => true,
            'limit'      => $page['limit'],
        ));
        $page['item_count'] = $goods_mod2->getCount();

        $this->_format_page($page);
        //$this->pr($goods_list);
        $this->assign('page_info', $page);
        $goods_mod  =& m('goods');
        //热卖商品
        $goods_list2 = $goods_mod->get_list(array(
            'conditions' => $conditions,
            'order'      => 'sales desc',
            'limit'      => 2,
        ));

        foreach ($goods_list as $key => $goods)
        {
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store_mod =& m('store');
            $goods_list[$key]['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($goods['credit_value'], $step);
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            $goods_list[$key]['grade_name'] = $sgrades[$goods['sgrade']];
        }

        $this->assign('goods_list', $goods_list);


        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());

        $this->assign('goods_list2', $goods_list2);

        /* 当前位置 */
        $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
        $this->_curlocal($this->_get_goods_curlocal($cate_id,$param),$param);

        /* 配置seo信息 */
        $this->_config_seo($searchApp->_get_seo_info('goods', $cate_id));
        /*浏览历史*/
//        $this->assign('goods_history', $this->_get_goods_history(0));

        $curlocalArray=$this->_get_goods_curlocal($cate_id,$param);
        $curlocalStr="";
        if(count($curlocalArray)>0)
        {
            $curlocalStr=$curlocalArray[count($curlocalArray)-1];
        }
        $this->assign('curlocalStr', $curlocalStr);
        $this->assign('cate_id', $cate_id);

        //价格区间,order传回页面,
//        $this->assign('startPrice',$_GET['startPrice']);
//        $this->assign('endPrice',$_GET['endPrice']);
        $this->assign('order',$_GET['order']);
        $this->display('hgg.list.html');
    }


    function _get_goods_history($id, $num = 9)
    {
        $goods_list = array();
        $goods_mod = &m('goods');
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows =$$goods_mod->find(array(
                'conditions' => $goods_ids,
                'fields'     => 'goods_name,default_image,price',
            ));
            foreach ($goods_ids as $goods_id)
            {
                if (isset($rows[$goods_id]))
                {
                    empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
                    $goods_list[] = $rows[$goods_id];
                }
            }
        }
        $goods_ids[] = $id;
        if (count($goods_ids) > $num)
        {
            unset($goods_ids[0]);
        }
        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));

        return $goods_list;
    }
    //当前位置
    function _curlocal($arr,$pamap)
    {
        $curlocal = array();
        if (is_array($arr))
        {
            $curlocal = array_merge($curlocal, $arr);
        }
        else
        {
            $args = func_get_args();
            if (!empty($args))
            {
                $len = count($args);
                for ($i = 0; $i < $len; $i += 2)
                {
                    $curlocal[] = array(
                        'text'  =>  $args[$i],
                        'url'   =>  $args[$i+1],
                    );
                }
            }
        }

        $this->assign('_curlocal', $curlocal);
    }
    //当前分类位置
    function _get_goods_curlocal($cate_id,$param)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            // array('text' => LANG::get('all_categories'), 'url' => "javascript:dropParam('cate_id')"),
            array('text' => LANG::get('all_categories'), 'url' => "index.php?app=hgg&act=search&pd_id=".$param['pd_id']),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => "index.php?app=hgg&act=search&pd_id=".$param['pd_id']."&cate_id=" . $category['cate_id']);
        }
        unset($curlocal[count($curlocal) - 1]['url']);
        return $curlocal;
    }


    function _get_group_by_info($param, $cached)
    {
        $data = array(
            'total_count' => 0,
            'by_category' => array(),
            'by_brand'    => array(),
            'by_region'   => array(),
            'by_price'    => array()
        );

        $goods_mod =& m('goods');
        $store_mod =& m('store');

        $table = " {$goods_mod->table} g LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id";
        $conditions = $this->_get_goods_conditions($param);

        $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions." and g.dropState =1 and g.if_show=1 and g.if_ruzhu=1";
        $total_count = $goods_mod->getOne($sql);

//            if ($total_count > 0)
//            {
        $data['total_count'] = $total_count;
        /* 按分类统计 */
        $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
        $sql = "";

        if ($cate_id > 0)
        {
//                    $layer =$this->_gcategory_mod->get_layer($cate_id);
//                    $this->pr($layer);
//                    exit;

            $layer = $param['layer'];

            if ($layer ==2) {
                $gcategory_mode = & bm('gcategory');
                $father = $gcategory_mode->get($cate_id);
                $gcategory = $gcategory_mode->get_descendant($father['parent_id']);
                if($gcategory_mode->get_children($cate_id, true)) {
                    $sql = "SELECT g.cate_id_" . ($layer+1) . " AS id, COUNT(*) AS count FROM {$table} WHERE g.cate_id_2=".$cate_id." AND g.cate_id_" . ($layer+1) . " > 0 and g.dropState=1 and g.if_show=1 and g.if_ruzhu=1 GROUP BY g.cate_id_" . ($layer+1) . " ORDER BY count DESC";
                } else {
                    $sql = "SELECT g.cate_id_" . ($layer) . " AS id, COUNT(*) AS count FROM {$table} WHERE g.cate_id_".($layer).db_create_in($gcategory)." and g.cate_id_" . ($layer) . " > 0 and g.dropState=1 and g.if_show=1 and g.if_ruzhu=1 GROUP BY g.cate_id_" . ($layer) . " ORDER BY count DESC";
                }
            } else {
                $sql = "SELECT g.cate_id_" . ($layer+1) . " AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_" . ($layer+1) . " > 0 and g.dropState=1 and g.if_show=1 and g.if_ruzhu=1 GROUP BY g.cate_id_" . ($layer+1) . " ORDER BY count DESC";
            }
        }
        else
        {
            $sql = "SELECT g.cate_id_1 AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_1 > 0 AND g.dropState=1 and g.if_show=1 and g.if_ruzhu=1 GROUP BY g.cate_id_1 ORDER BY count DESC";
        }
        if ($sql)
        {
            $category_mod =& bm('gcategory');
            $children = $category_mod->get_children($cate_id, true);
            $res = $goods_mod->db->query($sql);
            $count =  $goods_mod->db->getAll($sql);
            if(empty($children)) {
                $father = $category_mod->get($cate_id);
                $children = $category_mod->get_children($father['parent_id'], true);
            }
//                    $this->pr($sql);
//                    exit;
            if(count($count)==0) {
                foreach($children as $key=>$cl) {
                    $data['by_category'][$key] = array(
                        'cate_id'   => $cl['cate_id'],
                        'cate_name' => $cl['cate_name'],
                        'count'     => 0
                    );
                }
            } else {
                while ($row = $goods_mod->db->fetchRow($res))
                {
                    $data['by_category'][$row['id']] = array(
                        'cate_id'   => $row['id'],
                        'cate_name' => $children[$row['id']]['cate_name'],
                        'count'     => $row['count']
                    );
                }
            }
        }


        /* 按品牌统计 */
        $m_brand = &m('brand');
        $category_brand_mode= & m('categorybrand');
        if($param['cate_id']) {
            $sql = "SELECT b.brand_id,b.brand_name FROM {$m_brand->table} b, {$category_brand_mode->table} cb where b.brand_id = cb.brand_id and b.dropState=1 and cb.category_id = ".$param['cate_id'];
            $by_brands = $m_brand->db->getAllWithIndex($sql,'brand_id');
        }



        /* 滤去未通过商城审核的品牌 */
        if ($by_brands)
        {
            $m_brand = &m('brand');
            $brand_conditions = db_create_in(addslashes_deep(array_keys($by_brands)), 'brand_id');
            $brands_verified = $m_brand->getCol("SELECT brand_id,brand_name FROM {$m_brand->table} WHERE " . $brand_conditions . ' AND if_show=1 and dropState=1');

            foreach ($by_brands as $k => $v)
            {
                if (!in_array($k, $brands_verified))
                {
                    unset($by_brands[$k]);
                }
            }

        }


        $data['by_brand'] = $by_brands;


        /* 按价格统计 */
        if ($total_count > NUM_PER_PAGE)
        {
            $sql = "SELECT MIN(g.price) AS min, MAX(g.price) AS max FROM {$table} WHERE" . $conditions;
            $row = $goods_mod->getRow($sql);
            $min = $row['min'];
            $max = min($row['max'], MAX_STAT_PRICE);
            $step = max(ceil(($max - $min) / PRICE_INTERVAL_NUM), MIN_STAT_STEP);
            $sql = "SELECT FLOOR((g.price - '$min') / '$step') AS i, count(*) AS count FROM {$table} WHERE " . $conditions . " GROUP BY i ORDER BY i";
            $res = $goods_mod->db->query($sql);
            while ($row = $goods_mod->db->fetchRow($res))
            {
                $data['by_price'][] = array(
                    'count' => $row['count'],
                    'min'   => $min + $row['i'] * $step,
                    'max'   => $min + ($row['i'] + 1) * $step,
                );
            }
        }
        return $data;
    }
    /**
     * 取得查询条件语句
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_goods_conditions($param)
    {
        /* 组成查询条件 */
        $searchApp = new SearchApp();
        $conditions = " g.if_show = 1 AND g.closed = 0 AND g.if_ruzhu = 1 AND s.state = 1 ";
        if (isset($param['country_id'])) {
            $conditions.=" g.country_id =".$param['country_id'];
        }
        // 上架且没有被禁售，店铺是开启状态,
        if (isset($param['keyword']))
        {
            $conditions .= $searchApp->_get_conditions_by_keyword($param['keyword'], ENABLE_SEARCH_CACHE);
        }
        if (isset($param['cate_id']))
        {
            if($param['layer']>2) {
                $gcatgory_mode = & bm('gcategory');
//                $layer = $param['layer']-1;
//                $category = $gcatgory_mode->get($param['cate_id']);
                $conditions .= " AND g.cate_id=" .$param['cate_id'] ;
            } else {
                $layer =$param['layer'];
                $conditions .= " AND g.cate_id_".$layer."= '" . $param['cate_id'] . "'";
            }
//            $conditions .= " AND g.cate_id_{$param['layer']} = '" . $param['cate_id'] . "'";
        }

        if (isset($param['brand']))
        {
            $conditions .= " AND g.brand_id = '" . $param['brand'] . "'";
        }
        if (isset($param['region_id']))
        {
            $conditions .= " AND s.region_id = '" . $param['region_id'] . "'";
        }
        if (isset($param['price']))
        {
            $min = $param['price']['min'];
            $max = $param['price']['max'];
            $min > 0 && $conditions .= " AND g.price >= '$min'";
            $max > 0 && $conditions .= " AND g.price <= '$max'";
        }

        return $conditions;
    }

    /**
     * 取得查询参数（有值才返回）
     *
     * @return  array(
     *              'keyword'   => array('aa', 'bb'),
     *              'cate_id'   => 2,
     *              'layer'     => 2, // 分类层级
     *              'brand'     => 'ibm',
     *              'region_id' => 23,
     *              'price'     => array('min' => 10, 'max' => 100),
     *          )
     */
    function _get_query_param()
    {
        static $res = null;
        if ($res === null)
        {
            $res = array();

            // keyword
            $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
            if ($keyword != '')
            {
                //$keyword = preg_split("/[\s," . Lang::get('comma') . Lang::get('whitespace') . "]+/", $keyword);
                $tmp = str_replace(array(Lang::get('comma'),Lang::get('whitespace'),' '),',', $keyword);
                $keyword = explode(',',$tmp);
                sort($keyword);
                $res['keyword'] = $keyword;
            }

            // cate_id
            if (isset($_GET['cate_id']) && ($_GET['cate_id']) > 0)
            {
                $res['cate_id'] = $cate_id = intval($_GET['cate_id']);
                $gcategory_mod  =& bm('gcategory');
                $res['layer']   = $gcategory_mod->get_layer($cate_id, true);
            }
            if (isset($_GET['brand'])&& !empty($_GET['brand']))
            {
                $brand = trim($_GET['brand']);
                $res['brand'] = $brand;
            }
            if (isset($_GET['country_id'])&& !empty($_GET['country_id']))
            {
                $country_id= trim($_GET['country_id']);
                $res['country_id'] = $country_id;
            }
            if (isset($_GET['pd_id'])&& !empty($_GET['pd_id']))
            {
                $res['pd_id'] = $_GET['pd_id'];
            }

            if(isset($_GET['hgg_class'])) {
                $res['hgg_class'] = $_GET['hgg_class'];
            }
            if(isset($_GET['hgg_subcateid'])) {
                $res['hgg_subcateid'] = $_GET['hgg_subcateid'];
            }

            if(isset($_GET['hgg_order_key'])) {
                $res['hgg_order_key'] = $_GET['hgg_order_key'];
            }
            if(isset($_GET['hgg_order'])) {
                $res['hgg_order'] = $_GET['hgg_order'];
            }

            $attrvalue = isset($_GET['attrvalue']) ? trim($_GET['attrvalue']) : '';
            if($attrvalue!='')
            {
                $values = array();
                $attrids =array();
                $attrvalue2 = explode(';',$attrvalue);
                foreach ($attrvalue2 as $arr)
                {
                    $idx = strpos($arr,",");
                    if($idx>0) {
                        $attrids[]=substr($arr,0,$idx);
                        $values[]=substr($arr,$idx+1);
                    }
                    $res['attr_value'] = $values;
                    $res['attr_id'] = $attrids;
                }
//                $arr=null;
//                //foreach()
//                //print_r($res['attrs']);
//                for($i=0;$i<count($values);$i++)
//                {
//                    $arr[$attrids[$i]]=array("attr_id"=>$attrids[$i],"attr_value"=>$values[$i]);
//                }
                $res['attrs'] = $attrvalue;
                $res['value'] = $values;
                $res['attrid']=$attrids;
            }
            // region_id
            if (isset($_GET['sort_order']) && !empty($_GET['sort_order']))
            {
                $res['sort_order'] = $_GET['sort_order'];
            }

//            if (isset($_GET['price']))
//            {
//                $arr = explode('-', $_GET['price']);
//                $min = abs(floatval($arr[0]));
//                $max = abs(floatval($arr[1]));
//                if ($min * $max > 0 && $min > $max)
//                {
//                    list($min, $max) = array($max, $min);
//                }
//
//                $res['price'] = array(
//                    'min' => $min,
//                    'max' => $max
//                );
//            }
            $startPrice = $_GET['startPrice'];
            $endPrice = $_GET['endPrice'];
            $min = abs(floatval($startPrice));
            $max = abs(floatval($endPrice));
            if($max * $min > 0 && $min > $max){
                $price = array('min' => $max , 'max' => $min);
            }else{
                $price = array('min' => $min , 'max' => $max);
            }
            $res['price'] = $price;
        }
//        $this->pr($_GET);
        return $res;
    }

    /**
     * 取得过滤条件
     */
    function _get_filter($param)
    {
        static $filters = null;
        if ($filters === null)
        {
            $filters = array();
            if (isset($param['keyword']))
            {
                $keyword = join(' ', $param['keyword']);
                $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
            }
            isset($param['brand']) && $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $param['brand']);


            if(isset($param['attrs'])&&!empty($param['attrs']))
            {
                $filters['attrs'] = $param['attrs'];
            }
            if(isset($param['value'])&&!empty($param['value']))
            {
                $filters_value= null;
                $filters_attrid=null;
                $values= $param['value'];
                $attrid = $param['attrid'];
                foreach ($values as $val) {
                    $filters_value[]=array('attr_value'=>$val);
                }
                foreach ($attrid as $ar) {
                    $filters_attrid[]=array('attr_id'=>$ar);
                }
                $filters['arvalue'] = $filters_value;
                $filters['attrid'] =  $filters_attrid;
            }

            if (isset($param['country_id'])&& !empty($param['country_id']))
            {
                $country_id= trim($param['country_id']);
                $filters['country_id'] = $country_id;
            }
            if (isset($param['pd_id'])&& !empty($param['pd_id']))
            {
                $filters['pd_id'] = $param['pd_id'];
            }
            if(isset($param['hgg_class'])) {
                $filters['hgg_class'] = $param['hgg_class'];
            }
            if(isset($param['hgg_subcateid'])) {
                $filters['hgg_subcateid'] = $param['hgg_subcateid'];
            }

            if(isset($param['hgg_order_key'])) {
                $filters['hgg_order_key'] = $param['hgg_order_key'];
            }
            if(isset($param['hgg_order'])) {
                $filters['hgg_order'] = $param['hgg_order'];
            }
            if(isset($param['hgg_area'])) {
                $filters['hgg_area'] = $param['hgg_area'];
            }

            if (isset($param['price']))
            {
                $min = $param['price']['min'];
                $max = $param['price']['max'];
                if ($min <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('le') . ' ' . price_format($max));
                }
                elseif ($max <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                }
                else
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
            }

            if(isset($param['price'])) {
                $filters['startPrice'] = $param['price']['min'];
                $filters['endPrice'] = $param['price']['max'];
            }
            if (isset($param['sort_order'])){
                $filters['sort_order'] = $param['sort_order'];
            }
        }

        return $filters;
    }

    function get_category_goods(){
        $param = $this->_get_query_param();

        $filter =  $this->_get_filter($param);
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        //获取菜单分类
        $this->assign('hgg_son_category',$this->_list_gcategory($param["pd_id"]));

        $this->assign('hgg_category',$this->_list_gcategory(10));


        $data = $this->new_goods($_GET['r_id']);
        $this->assign('new_goods',$data['goods_list']);

        $this->assign('page_info',$data['page']);
        $this->assign('filter',$filter);
//        $this->pr($data['page']);
//        exit;
        //获取频道下分类ID
//        $_pdgcategory_mod = bm("pdcategory");
//        $cate_ids = null;
//        if($_GET["pd_id"]) {
//            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
//        }
        //根据地区获取商品
//        $data = $this->get_goods_list($cate_ids,$param,'');

//        $this->assign("goods_list",$data['goods_list']);
//        $this->assign("page_info",$data['page']);

//        $this->assign('brands',$this->get_brand());
//        /* 热门搜素 */
//        $this->assign('hot_keywords', $this->_get_hot_keywords());
//        //设置title
//
//        $gcategory_mod =& bm('gcategory');
//
//        $gcategroys = null;
//        //获取参数
//        $param = $this->_get_query_param();
//        //记住参数
//        $filter = $this->_get_filter($param);
//
////        if (!empty($param['hgg_area'])&&!empty($param['hgg_reg_id'])) {
////            $regin_mode = & m("region");
////            if (!empty($param['hgg_reg_id'])) {
////                $city = $regin_mode->get_options($param['hgg_reg_id']);
////                $this->assign('city',$city);
////            }
////            if (!empty($param['hgg_city_id'])) {
////                $diqu = $regin_mode->get_options($param['hgg_city_id']);
////                $this->assign('diqu',$diqu);
////            }
////
////        }
//
//        //获取子分类
//        $goods_mod = &m('goods');
//        $conditions = "1=1";
//        //根据省市获取当地省市的商家ID
//
//        if (!empty($param['hgg_class'])) {
//            $conditions.=" and cate_id_1= ".$param['hgg_class'];
//        }
//        if (!empty($param['hgg_subcateid'])) {
//            $conditions.=" and cate_id_2= ".$param['hgg_subcateid'];
//        }
//
//        $_pdgcategory_mod = bm("pdcategory");
//        if (!empty($param['pd_id'])) {
//            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($param["pd_id"]);
//        }
//        $data = null;
//        $page   =   $this->_get_page(15);
//        $goods_list = $goods_mod->get_list(array(
//            'conditions' => $conditions,
//            'scate_ids' => $cate_ids,
//            'join' => 'has_goodsstatistics, belongs_to_store',
//            'count' => true,
//            'limit' => $page['limit']
//        ));
//        foreach ($goods_list as $key=>$goods) {
//            if (strpos($goods['default_image'],'/')===false) {
//                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
//            }
//        }
//        $page['item_count'] = $goods_mod->getCount();
//        $this->_format_page($page);
//        $this->assign('goods_list',$goods_list);
//        $this->assign('gcategory_list',$this->_list_gcategory(10));
////        $this->assign('son_category',$this->get_son_category($param['hgg_class']));
////        $this->pr($filter);
////        exit;
//        $this->assign('filters',$filter);
//        $this->assign('page_info',$page);
        $this->_config_seo('title', '韩国馆');
        $this->display("hgg.list_x.html");

    }

    function new_goods($rec_id) {
        $num = '1000';
        $data = array();
        $page   =   $this->_get_page(41);
        $recom_mod =& m('recommend');
        $_pdgcategory_mod = bm("pdcategory");

        $list =  $recom_mod->get_recommended_goods2($rec_id, $num, true, 0, 0,array('limit'=>$page['limit']));
        $data['goods_list'] = $list;
        $page['item_count'] =count($recom_mod->get_recommended_goods($rec_id, $num, true, 0, 0));
        $this->_format_page($page);
        $data['page'] = $page;
        return $data;
    }

//    function new_goods($param) {
//        $recom_mod =& m('recommend');
//        $_pdgcategory_mod = bm("pdcategory");
//        if($param["pd_id"]) {
//            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($param["pd_id"]);
//        }
//        $list =  $recom_mod->get_recommended_goods(-100, 5, true,current($cate_ids),0,0);
//        return $list;
//    }
    //获取商品
    function  get_goods_list($cate_ids,$param,$layer_cate) {
        $goods_mod = &m('goods');
        $conditions = "1=1";
        //根据省市获取当地省市的商家ID
        if ($param['brand_id']){
            $conditions.=" and  brand_id =".$param['brand_id'];
        }
        if ($layer_cate) {
            $conditions.=" and cate_id_1 =".$layer_cate;
        }
        $data = null;
//        $page   =   $this->_get_page(15);
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions,
            'scate_ids' => $cate_ids,
            'join' => 'has_goodsstatistics, belongs_to_store',
            'order'      => (($param['bdsh_order_key']==null || $param['bdsh_order_key']=="")?"views":$param['bdsh_order_key'])." ".$param['bdsh_order'],
            'count' => true,
//            'limit' => $page['limit']
        ));
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
//        $data['goods_list'] = $goods_list;
//        $page['item_count'] = $goods_mod->getCount();
//        $this->_format_page($page);
//        $data['page'] = $page;
        return $goods_list;
    }

    function change_city() {
        $bdshApp = new BdshApp();
        $this->assign('shengshi',$bdshApp->get_all_city());

        $this->display('hgg.city.html');
    }

    function recommend() {
        $rids = Array(33 => "韩国馆-首页-精品推荐", 27 => "韩国馆-首页-楼层1", 28 => "韩国馆-首页-楼层2",
            29 => "韩国馆-首页-楼层3", 30 => "韩国馆-首页-楼层4", 31 => "韩国馆-首页-楼层5", 32 => "韩国馆-首页-楼层6",
            34=>"韩国馆-首尔超市-最新商品",
            35=>"韩国馆-跟上韩流-最新商品",
            36=>"韩国馆-生活家园-最新商品",
            37=>"韩国馆-美丽世界-最新商品",
            38=>"韩国馆-母婴天地-最新商品",
            39=>"韩国馆-快快直购-最新商品",
            50=>"韩国馆-韩国特展-楼层1",
        );
        $recom_mod =& m('recommend');
        $rid = $_GET["rid"];
        if (!$rid || $rid == '') {
            $rid = $_POST["rid"];
        }
        if (!$rid || $rid == '') {
            $rid = 33;
        }
        if (IS_POST) {
          if ($_POST["goods_ids"]) {
              if (array_key_exists($rid, $rids)) {
                  foreach (explode(",", $_POST["goods_ids"]) as $key=>$goods_id) {
                      if ($goods_id != ""){
                          $recom_mod->db->query("insert into ecm_recommended_goods (recom_id, goods_id, sort_order) values(" . $rid . "," . $goods_id . ",255)");
                      }
                  }
              }
          }
        } else {
            if ($_GET["_m"] == 'del') {
                if (array_key_exists($rid, $rids)) {
                    $goods_id = $_GET["goods_id"];
                    $recom_mod->db->query("delete from ecm_recommended_goods where recom_id=" . $rid . " and goods_id = " . $goods_id);
                }
            }
        }
        $list =  $recom_mod->get_recommended_goods($rid, 10000, true, 0, 0);
        $this->assign("rid", $rid);
        $this->assign("list", $list);
        $this->display("hgg.recommend.html");
    }

    //促销
    function change_chuxiao() {
        $data = $this->new_goods(39);
        $this->assign('new_goods',$data['goods_list']);
        if($_GET['type']==2) {
            $data1 = $this->new_goods(50);
            $this->assign('tezhan_goods2',$data1['goods_list']);
            $this->display("hgg.huodong2.html");
        } else {
            $this->assign("pd_id",17);
            $this->assign('page_info',$data['page']);
            $this->display("hgg.list_x.html");
        }

    }
}

?>
