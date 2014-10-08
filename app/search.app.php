<?php

/* 定义like语句转换为in语句的条件 */
define('MAX_ID_NUM_OF_IN', 10000); // IN语句的最大ID数
define('MAX_HIT_RATE', 0.05);      // 最大命中率（满足条件的记录数除以总记录数）
define('MAX_STAT_PRICE', 10000);   // 最大统计价格
define('PRICE_INTERVAL_NUM', 5);   // 价格区间个数
define('MIN_STAT_STEP', 50);       // 价格区间最小间隔
define('NUM_PER_PAGE', 36);        // 每页显示数量
define('ENABLE_SEARCH_CACHE', true); // 启用商品搜索缓存
define('SEARCH_CACHE_TTL', 3600);  // 商品搜索缓存时间

class SearchApp extends MallbaseApp
{


    var $_gcategory_mod;
    var $_goodsattr_mod;
    var $_attribute_mod;

    /* 构造函数 */
    function __construct()
    {
        $this->SearchApp();
    }

    function SearchApp()
    {
        parent::__construct();
        include_once(ROOT_PATH . '/app/my_goods.app.php');
        include_once(ROOT_PATH . '/app/goods.app.php');
        $this->_attribute_mod =& m('attribute');
        $this->_gcategory_mod =& bm('gcategory');
        $this->_goodsattr_mod=& m('goodsattr');
    }
    function checkRegion(){
        $region_id=$_GET["region_id"];

        $region_mod =& m('region');
        $data=$region_mod->get_options($region_id);

        if(count($data)>0){
            echo ecm_json_encode(false);
        }else{
            echo ecm_json_encode(true);
        }
    }

    //生成所有省的json  queryAllProvinces.js
    function testshen()
    {
        header('Content-type: text/json');
        $region_mod =& m('region');
        $tmp=$region_mod->get_options(1);
        //$this->print_r($region_mod->get_options(1));
        $array1=array();
        $array2=array();
        foreach($tmp as $k=>$v)
        {
            $a1=array("id"=>$k,"updateTime"=>gmtime(),"provinceName"=>$v,"indexId"=>$k);
            array_push($array1,$a1);
        }
        $array2=array("success"=>true,"isException"=>false,"exception"=>false,"province"=>null,"provinceId"=>null,"provinces"=>$array1,"successResultValue"=>null);
        echo json_encode($array2);
    }
    //生成所有市的json  queryCities.js
    function testshi()
    {
        header('Content-type: text/json');
        $region_mod =& m('region');
        $tmp=$region_mod->get_options(1);
        //$this->print_r($region_mod->get_options(1));
        $array1=array();
        $array2=array();
        $i=0;
        foreach($tmp as $k=>$v)
        {
            $tmp1=$region_mod->get_options($k);
            foreach($tmp1 as $k1=>$v1)
            {
                if($i<=15)
                {
                    $a1=array("name"=>$v1,"id"=>$k1,"cityPinyin"=>null,"lastModifyTime"=>null,"provinceId"=>$k,"hotCity"=>true,"cityShortPY"=>null);
                    array_push($array1,$a1);
                }else
                {
                    $a1=array("name"=>$v1,"id"=>$k1,"cityPinyin"=>null,"lastModifyTime"=>null,"provinceId"=>$k,"hotCity"=>null,"cityShortPY"=>null);
                    array_push($array1,$a1);
                }
                $i++;
            }
        }
        $array2=array("success"=>true,"isException"=>false,"area"=>null,"cities"=>$array1,"city"=>null,"cityId"=>null,"exception"=>false,"jiageCities"=>null,"proId"=>null,"successResultValue"=>null);
       // $this->print_r($array2);
        echo json_encode($array2);
    }
    //生成所有区的json  queryAllAreas.js
    function testqu()
    {
        header('Content-type: text/json');
        $region_mod =& m('region');
        $tmp=$region_mod->get_options(1);
        //$this->print_r($region_mod->get_options(1));
        $array1=array();
        $array2=array();
        foreach($tmp as $k=>$v)
        {
            $tmp1=$region_mod->get_options($k);
            foreach($tmp1 as $k1=>$v1)
            {
                $tmp2=$region_mod->get_options($k1);
                foreach($tmp2 as $k2=>$v2)
                {
                    $a1=array("id"=>$k2,"cityId"=>$k1,"cityName"=>$v1,"areaName"=>$v2,"updateTime"=>gmtime(),"provinceId"=>$k,"pinYin"=>null,"pinYinChar"=>null,"isShowWithCity"=>0);
                    array_push($array1,$a1);
                }
            }
        }
        $array2=array("success"=>true,"isException"=>false,"area"=>null,"areaId"=>null,"areaName"=>null,"areas"=>$array1,"cityId"=>false,"exception"=>false,"successResultValue"=>null,"updateTime"=>null);
        //$this->print_r($array1);
        echo json_encode($array2);
    }

    /* 搜索商品 */
    function index()
    {
        // 查询参数
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

//        $this->assign('brands',$this->_get_brands($param['cate_id']));

//        $getAttrs = $_GET['attrvalue'];

        /* 筛选条件 */

//        $this->pr($param);
        $this->assign('filters', $this->_get_filter($param));
        /* 按分类、品牌、地区、价格区间统计商品数量 */
        $stats = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);
//        $this->pr($stats['by_category']);
//        exit;
        $this->assign('categories', $stats['by_category']);
        $this->assign('category_count', count($stats['by_category']));

        $this->assign('brands', $stats['by_brand']);

        $this->assign('brand_count', count($stats['by_brand']));

        $this->assign('price_intervals', $stats['by_price']);

        $this->assign('regions', $stats['by_region']);


//        $this->pr($this->_get_filter($param));
//        exit;

//        $gcategorys = $this->_list_gcategory();
//        $this->assign('gcategorys', $gcategorys);
//        $this->assign('attrvalueArray', $attrvalueArray);

        $this->assign('region_count', count($stats['by_region']));
        if (isset($param['cate_id'])) {
            $this->assign('goodsattr',$this->get_goods_attr($param['cate_id']));
        }

        $this->assign('ky',$param['keyword']);

//        $this->pr($param['keyword']);exit;
        /* 排序 */
        $orders = $this->_get_orders();
        $this->assign('orders', $orders);

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
        $attrid = $this->_get_attrid_condition($param);
        $attrvalue = $this->_get_attrvalue_condition($param);
        $goods_mod2  =& m('goods');
//        $this->pr($conditions);
//        exit;
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

        /* 商品展示方式 */
        $display_mode = ecm_getcookie('goodsDisplayMode');
        if (empty($display_mode) || !in_array($display_mode, array('list', 'squares')))
        {
            $display_mode = 'squares'; // 默认格子方式
        }
        $this->assign('display_mode', $display_mode);

        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());

        $this->assign('goods_list2', $goods_list2);

        /* 当前位置 */
        $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
        $this->_curlocal($this->_get_goods_curlocal($cate_id));

        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info('goods', $cate_id));
        /*浏览历史*/


//        $this->pr("----");exit;
        $this->assign('goods_history', $this->_get_goods_history());

        $curlocalArray=$this->_get_goods_curlocal($cate_id);
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
        $this->display('search.goods.html');
    }
//商品分类


    function _get_goods_history($num = 9)
    {
        $goods_list = array();
        $goods_mod = &m('goods');
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows =$goods_mod->find(array(
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
//        $goods_ids[] = $id;
//        if (count($goods_ids) > $num)
//        {
//            unset($goods_ids[0]);
//        }
        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));

        return $goods_list;
    }

    /* 搜索店铺 */
    function store()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());
        $param = $this->_get_query_param();
        $filters = $this->_get_filter($param);
        /* 取得该分类及子分类cate_id */
        $cate_id = empty($param['bdsh_class']) ? 0 : intval($param['bdsh_class']);
        $cate_ids=array();
        $condition_id='';
        if ($cate_id > 0)
        {
            $scategory_mod =& m('scategory');
            $cate_ids = $scategory_mod->get_descendant($cate_id);
            $this->assign('son_category',$scategory_mod->get_list($cate_id));
        }
        $this->assign('filters',$filters);
        $regin_mode = & m("region");
        if (!empty($_GET['bdsh_reg_id'])) {
            $city = $regin_mode->get_options($_GET['bdsh_reg_id']);
            $this->assign('diqu',$city);
        }
        /* 店铺分类检索条件 */
        $condition_id=implode(',',$cate_ids);
        $condition_id && $condition_id = ' AND cate_id IN(' . $condition_id . ')';

        /* 其他检索条件 */
        if (!empty($param['bdsh_class'])) {
            $conditions.=" and cate_id= ".$param['bdsh_class'];
        }
        if (!empty($param['bdsh_diqu_id'])) {
            $conditions = ' and s.region_id='.$param['bdsh_diqu_id'];
        } else {
            $reiong_list =  $regin_mode->get_options($param['bdsh_reg_id']);
            $region_ids = "";
            if (count($reiong_list)>0) {
                foreach ($reiong_list as $key=>$reg) {
                    if($region_ids==""){
                        $region_ids=" s.region_id=".$key;
                    }else{
                        $region_ids .=" or s.region_id=".$key;
                    }
                }
                $conditions.=" and ($region_ids) and seller_type =3";
            }
        }
        $model_store =& m('store');
        $page   =   $this->_get_page(10);   //获取分页信息
        $stores = $model_store->find(array(
            'conditions'  => 's.dropState=1 and state = ' . STORE_OPEN . $condition_id . $conditions,
            'limit'   =>$page['limit'],
            'order'   => empty($_GET['order']) || !in_array($_GET['order'], array('credit_value desc')) ? 'sort_order' : $_GET['order'],
            'join'    => 'belongs_to_user,has_scategory',

            'count'   => true   //允许统计
        ));

        $model_goods = &m('goods');

        foreach ($stores as $key => $store)
        {
            //店铺logo
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');

            //商品数量
            $stores[$key]['goods_count'] = $model_goods->get_count_of_store($store['store_id']);

            //等级图片
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $stores[$key]['credit_image'] = $this->_view->res_base . '/images/' . $model_store->compute_credit($store['credit_value'], $step);

        }
        $page['item_count']=$model_store->getCount();   //获取统计数据
        $this->_format_page($page);

        /* 当前位置 */
        $this->_curlocal($this->_get_store_curlocal($cate_id));
        $scategorys = $this->_list_scategory();
        $this->assign('stores', $stores);
        $this->assign('cate_id', $cate_id);
        $this->assign('scategorys', $scategorys);
//        $this->assign('renqi_store',$this->renqi_stores());
        $this->assign('page_info', $page);
        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info('store', $cate_id));
        $this->display('search.store.html');
    }


    function groupbuy()
    {
        $this->_config_seo('title', Lang::get('tuangou'));
        empty($_GET['state']) &&  $_GET['state'] = 'on';
        $conditions = '1=1';
        $region_mod =& m('region');
        $tmp=$region_mod->get_options(1);
        // 排序
        $orders = array(
            'group_id desc'          => Lang::get('select_pls'),
            'views desc'     => Lang::get('views'),
        );

        if ($_GET['state'] == 'on')
        {
            $orders['end_time asc'] = Lang::get('lefttime');
            $conditions .= ' AND gb.state ='. GROUP_ON .' AND gb.end_time>' . gmtime();
        }
        elseif ($_GET['state'] == 'end')
        {
            $conditions .= ' AND (gb.state=' . GROUP_ON . ' OR gb.state=' . GROUP_END . ') AND gb.end_time<=' . gmtime();
        }
        else
        {
            $conditions .= $this->_get_query_conditions(array(
                array(      //按团购状态搜索
                    'field' => 'gb.state',
                    'name'  => 'state',
                    'handler' => 'groupbuy_state_translator',
                )
            ));
        }
        $conditions .= $this->_get_query_conditions(array(
            array( //活动名称
                'field' => 'group_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'keyword',
                'type'  => 'string',
            ),
        ));

        if($_GET['groupbuy_area']) {
            $store_ids = $this->get_store_byarea($_GET['groupbuy_area']);
            if ($store_ids) {
                $conditions.=' AND g.store_id'.db_create_in($store_ids);
            } else {
                $conditions.=' AND g.store_id=0';
            }
        }
        if ($_GET['groupbuy_price']) {
            $price = $_GET['groupbuy_price'];
            if (intval($price)==1) {
                $conditions.=' AND g.price <= 500';
            } elseif(intval($price)==2) {
                $conditions.=' AND g.price > 500 and g.price <= 1000';
            } elseif(intval($price)==3) {
                $conditions.=' AND g.price > 1000 and g.price <= 2000';
            } elseif(intval($price)==4) {
                $conditions.=' AND g.price > 2000 and g.price <= 3000';
           }else {
                $conditions.=' AND g.price > 3000 and g.price <= 5000';
            }
        }
        if($_GET['groupbuy_class']){
            $conditions.=' AND g.cate_id_1 = '.$_GET['groupbuy_class'];
        }
        $page = $this->_get_page(NUM_PER_PAGE);   //获取分页信息
        $groupbuy_mod = &m('groupbuy');
        $groupbuy_list = $groupbuy_mod->find(array(
            'conditions'    => $conditions,
            'fields'        => 'gb.group_name,gb.spec_price,gb.min_quantity,gb.goods_id,gb.store_id,gb.state,gb.end_time,g.price,gb.group_desc,s.store_name',
            'join'          => 'belong_store, belong_goods',
            'limit'         => $page['limit'],
            'count'         => true,   //允许统计
            'order'         => isset($_GET['groupbuy_order_key']) && isset($orders[$_GET['groupbuy_order_key']]) ? $_GET['groupbuy_order_key'] : 'group_id desc',
        ));
        if ($ids = array_keys($groupbuy_list))
        {
            $quantity = $groupbuy_mod->get_join_quantity($ids);
        }
        foreach ($groupbuy_list as $key => $groupbuy)
        {
            $groupbuy_list[$key]['quantity'] = empty($quantity[$key]['quantity']) ? 0 : $quantity[$key]['quantity'];
            $groupbuy['spec_price'] = unserialize($groupbuy['spec_price']);
            $groupbuy_list[$key]['group_price'] = $groupbuy['spec_price'][$groupbuy['default_spec']]['price'];
            if ($groupbuy['price']!=0) {
                $groupbuy_list[$key]['discount'] =sprintf("%.1f",($groupbuy_list[$key]['group_price']/$groupbuy['price'])*10);
            }
            $groupbuy['state'] == GROUP_ON && $groupbuy_list[$key]['lefttime'] = lefttime($groupbuy['end_time']);
        }
        $this->assign('state', array(
                'on' => Lang::get('group_on'),
                'end' => Lang::get('group_end'),
                'finished' => Lang::get('group_finished'),
                'canceled' => Lang::get('group_canceled'))
        );
        $this->assign('orders', $orders);
        // 当前位置
        $this->_curlocal(array(array('text' => Lang::get('groupbuy'))));
        $this->_config_seo('title', Lang::get('groupbuy') . ' - ' . Conf::get('site_title'));
        $page['item_count'] = $groupbuy_mod->getCount();   //获取统计数据
        $this->_format_page($page);
        $this->assign('nav_groupbuy', 1); // 标识当前页面是团购列表，用于设置导航状态
        $this->assign('page_info', $page);
        $this->assign('groupbuy_list',$groupbuy_list);

       if($_GET['groupbuy_brand_id']=="") {
           $cate_ids = $groupbuy_mod->get_cate_id();
           $gcategory_model = & bm("gcategory");
           $gcategory_list =null;
           foreach ($cate_ids as $cate_id)
           {
               $gcategory_list[] =$gcategory_model->get($cate_id);
           }
           $this->assign('gcategory_list',$gcategory_list);
       } else {
           $brands = null;
           $goods_mode = & m('goods');
           $brand_mode = & m('brand');
           $sql = "SELECT brand_id,brand_name from {$brand_mode->table} b where b.brand_name in (select gd.brand from  {$groupbuy_mod->table} gb left join {$goods_mode->table} gd on gd.goods_id = gb.goods_id AND gd.if_show=1  AND gb.state=1 AND gd.dropState=1 AND gd.closed=0)";
           $brands = $brand_mode->db->getAll($sql);
           $this->assign('brand','brand');
           $this->assign('brands',$brands);
       }
           $filter= array();
           if($_GET['groupbuy_class']) {
               $filter["groupbuy_class"] = $_GET["groupbuy_class"];
            }
          if($_GET['groupbuy_area']) {
               $filter["groupbuy_area"] = $_GET["groupbuy_area"];
          }
         if($_GET['groupbuy_brand_id'] || $_GET["groupbuy_brand_id"]==0) {
             $filter['groupbuy_brand_id'] = $_GET["groupbuy_brand_id"];
          }
        if($_GET['groupbuy_price'] ) {
            $filter['groupbuy_price'] = $_GET["groupbuy_price"];
        }
        $this->assign('filters',$filter);
        $this->assign('region',$tmp);
        $this->display('search.groupbuy.html');
    }

//根据地区获取商铺ID
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
        if (!$param) {
            $stroe_ids= array_keys($data);
        } else {
            foreach ($region_name as $key =>$str) {
                if ($param == trim($str)) {
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

    // 推荐团购活动
    function _recommended_groupbuy($_num)
    {
        $model_groupbuy =& m('groupbuy');
        $data = $model_groupbuy->find(array(
            'join'          => 'belong_goods',
            'conditions'    => 'gb.recommended=1 AND gb.state=' . GROUP_ON . ' AND gb.end_time>' . gmtime(),
            'fields'        => 'group_id, goods.default_image, group_name, end_time, spec_price',
            'order'         => 'group_id DESC',
            'limit'         => $_num,
        ));
        foreach ($data as $gb_id => $gb_info)
        {
            $price = current(unserialize($gb_info['spec_price']));
            empty($gb_info['default_image']) && $data[$gb_id]['default_image'] = Conf::get('default_goods_image');
            $data[$gb_id]['lefttime']   = lefttime($gb_info['end_time']);
            $data[$gb_id]['price']      = $price['price'];
        }
        return $data;
    }

    // 最新参加的团购
    function _last_join_groupbuy($_num)
    {
        $model_groupbuy =& m('groupbuy');
        $data = $model_groupbuy->find(array(
            'join' => 'be_join,belong_goods',
            'fields' => 'gb.group_id,gb.group_name,gb.group_id,groupbuy_log.add_time,gb.spec_price,goods.default_image',
            'conditions' => 'groupbuy_log.user_id > 0',
            'order' => 'groupbuy_log.add_time DESC',
            'limit' => $_num,
        ));
        foreach ($data as $gb_id => $gb_info)
        {
            $price = current(unserialize($gb_info['spec_price']));
            empty($gb_info['default_image']) && $data[$gb_id]['default_image'] = Conf::get('default_goods_image');
            $data[$gb_id]['price']      = $price['price'];
        }
        return $data;
    }



    function _get_goods_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            // array('text' => LANG::get('all_categories'), 'url' => "javascript:dropParam('cate_id')"),
            array('text' => LANG::get('all_categories'), 'url' => "index.php?app=category"),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => "index.php?app=search&cate_id=" . $category['cate_id']);
        }
        unset($curlocal[count($curlocal) - 1]['url']);

        return $curlocal;
    }

    function _get_store_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $scategory_mod =& m('scategory');
            $scategory_mod->get_parents($parents, $cate_id);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => url('app=category&act=store')),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => url('app=search&act=store&cate_id=' . $category['cate_id']));
        }
        unset($curlocal[count($curlocal) - 1]['url']);
        return $curlocal;
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
            $bdsh_area = isset($_GET['bdsh_area']) ? trim($_GET['bdsh_area']) : '';
            $res['bdsh_area'] = $bdsh_area;

            if(isset($_GET['bdsh_class'])) {
                $res['bdsh_class'] = $_GET['bdsh_class'];
            }
            if(isset($_GET['bdsh_subcateid'])) {
                $res['bdsh_subcateid'] = $_GET['bdsh_subcateid'];
            }
            if(isset($_GET['bdsh_reg_id'])) {
                $res['bdsh_reg_id'] = $_GET['bdsh_reg_id'];
            }
            if(isset($_GET['bdsh_diqu_id'])) {
                $res['bdsh_diqu_id'] = $_GET['bdsh_diqu_id'];
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
            if (isset($param['bdsh_area']))
            {
                $filters['bdsh_area'] = $param['bdsh_area'];
            }
            if(isset($param['bdsh_class'])) {
                $filters['bdsh_class'] = $param['bdsh_class'];
            }
            if(isset($param['bdsh_subcateid'])) {
                $filters['bdsh_subcateid'] = $param['bdsh_subcateid'];
            }
            if(isset($param['bdsh_reg_id'])) {
                $filters['bdsh_reg_id'] = $param['bdsh_reg_id'];
            } else {
                $filters['bdsh_reg_id'] = 321;
            }
            if(isset($param['bdsh_diqu_id'])) {
                $filters['bdsh_diqu_id'] = $param['bdsh_diqu_id'];
            }
//            if (isset($param['region_id']))
//            {
//                $region_mod =& m('region');
//                $row = $region_mod->get(array(
//                    'conditions' => $param['region_id'],
//                    'fields' => 'region_name'
//                ));
//                $filters['region_id'] = array('key' => 'region_id', 'name' => LANG::get('region'), 'value' => $row['region_name']);
//            }
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

    /**
     * 取得查询条件语句
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_goods_conditions($param)
    {
        /* 组成查询条件 */

        $conditions = " g.if_show = 1 AND g.closed = 0 and g.dropState=1 AND s.state = 1 and g.if_jifen=0 and g.pd_id != 5"; // 上架且没有被禁售，店铺是开启状态,
        if (isset($param['keyword']))
        {
            $conditions .= $this->_get_conditions_by_keyword($param['keyword'], ENABLE_SEARCH_CACHE);
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
//            $conditions .= " AND g.cate_id_".$layer."= '" . $param['cate_id'] . "'";
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
     * 根据查询条件取得分组统计信息
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @param   bool    $cached 是否缓存
     * @return  array(
     *              'total_count' => 10,
     *              'by_category' => array(id => array('cate_id' => 1, 'cate_name' => 'haha', 'count' => 10))
     *              'by_brand'    => array(array('brand' => brand, 'count' => count))
     *              'by_region'   => array(array('region_id' => region_id, 'region_name' => region_name, 'count' => count))
     *              'by_price'    => array(array('min' => 10, 'max' => 50, 'count' => 10))
     *          )
     */
    function _get_group_by_info($param, $cached)
    {
//        $data = false;
//        if ($cached)
//        {
//            $cache_server =& cache_server();
//            $key = 'group_by_info_' . var_export($param, true);
//            $data = $cache_server->get($key);
//        }

//        if ($data === false)
//        {


            $data = array(
                'total_count' => 0,
                'by_category' => array(),
                'by_brand'    => array(),
                'by_region'   => array(),
                'by_price'    => array()
            );

            $goods_mod =& m('goods');
            $store_mod =& m('store');

//            $goodsattr_mod = & m('goodsattr');
            $table = " {$goods_mod->table} g LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id";
            $conditions = $this->_get_goods_conditions($param);

            $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions." and g.dropState =1 and g.if_show=1 and g.pd_id != 5";
            $total_count = $goods_mod->getOne($sql);

//            if ($total_count > 0)
//            {
                $data['total_count'] = $total_count;
                /* 按分类统计 */
                $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
                $sql = "";



//                  $data['by_category'] = $this->_list_gcategory(-1);
//                 $this->pr( $stats['by_category'] );
//        exit;
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
                            $sql = "SELECT g.cate_id_" . ($layer+1) . " AS id, COUNT(*) AS count FROM {$table} WHERE g.cate_id_2=".$cate_id." AND g.cate_id_" . ($layer+1) . " > 0 and g.dropState=1 and g.if_show=1 GROUP BY g.cate_id_" . ($layer+1) . " ORDER BY count DESC";
                        } else {
                            $sql = "SELECT g.cate_id_" . ($layer) . " AS id, COUNT(*) AS count FROM {$table} WHERE g.cate_id_".($layer).db_create_in($gcategory)." and g.cate_id_" . ($layer) . " > 0 and g.dropState=1 and g.if_show=1 GROUP BY g.cate_id_" . ($layer) . " ORDER BY count DESC";
                        }
                    } else {
                        $sql = "SELECT g.cate_id_" . ($layer+1) . " AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_" . ($layer+1) . " > 0 and g.dropState=1 and g.if_show=1 GROUP BY g.cate_id_" . ($layer+1) . " ORDER BY count DESC";
                    }
                }
                else
                {
                    $sql = "SELECT g.cate_id_1 AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_1 > 0 AND g.dropState=1 and g.if_show=1 GROUP BY g.cate_id_1 ORDER BY count DESC";
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



//                $sql = "SELECT g.brand_id,g.brand, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " GROUP BY g.brand_id ORDER BY count DESC";
//                $by_brands = $goods_mod->db->getAllWithIndex($sql, 'brand_id');


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

                /* 按地区统计 */
                $sql = "SELECT s.region_id, s.region_name, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND s.region_id > 0 GROUP BY s.region_id ORDER BY count DESC";
                $data['by_region'] = $goods_mod->getAll($sql);

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
//            }

//            if ($cached)
//            {
//                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
//            }
//        }


//         }
        return $data;
    }

    /**
     *商品属性
     */
    function _get_conditions_by_attrvalue($attrs, $cached)
    {
        $conditions = false;

        if ($cached)
        {
            $cache_server =& cache_server();
            $key1 = 'query_conditions_of_keyword_' . join("\t", $attrs);
            $conditions = $cache_server->get($key1);
        }

        if ($conditions === false)
        {
            /* 组成查询条件 */
            $conditions = array();
            foreach ($attrs as $word)
            {
                $conditions[] = "g.goods_name LIKE '%{$word}%'";
            }
            $conditions = join(' AND ', $conditions);

            /* 取得满足条件的商品数 */
            $goods_mod =& m('goods');
            $sql = "SELECT COUNT(*) FROM {$goods_mod->table} g WHERE " . $conditions;
            $current_count = $goods_mod->getOne($sql);
            if ($current_count > 0)
            {
                if ($current_count < MAX_ID_NUM_OF_IN)
                {
                    /* 取得商品表记录总数 */
                    $cache_server =& cache_server();
                    $key2 = 'record_count_of_goods';
                    $total_count = $cache_server->get($key2);
                    if ($total_count === false)
                    {
                        $sql = "SELECT COUNT(*) FROM {$goods_mod->table}";
                        $total_count = $goods_mod->getOne($sql);
                        $cache_server->set($key2, $total_count, SEARCH_CACHE_TTL);
                    }

                    /* 不满足条件，返回like */
                    if (($current_count / $total_count) < MAX_HIT_RATE)
                    {
                        /* 取得满足条件的商品id */
                        $sql = "SELECT goods_id FROM {$goods_mod->table} g WHERE " . $conditions;
                        $ids = $goods_mod->getCol($sql);
                        $conditions = 'g.goods_id' . db_create_in($ids);
                    }
                }
            }
            else
            {
                /* 没有满足条件的记录，返回0 */
                $conditions = "0";
            }

            if ($cached)
            {
                $cache_server->set($key1, $conditions, SEARCH_CACHE_TTL);
            }
        }

        return ' AND (' . $conditions . ')';
    }


    /**
     * 根据关键词取得查询条件（可能是like，也可能是in）
     *
     * @param   array       $keyword    关键词
     * @param   bool        $cached     是否缓存
     * @return  string      " AND (0)"
     *                      " AND (goods_name LIKE '%a%' AND goods_name LIKE '%b%')"
     *                      " AND (goods_id IN (1,2,3))"
     */
    function _get_conditions_by_keyword($keyword, $cached)
    {
        $conditions = false;

        if ($cached)
        {
            $cache_server =& cache_server();
            $key1 = 'query_conditions_of_keyword_' . join("\t", $keyword);
            $conditions = $cache_server->get($key1);
        }


        if ($conditions === false)
        {
            /* 组成查询条件 */
            $conditions = array();
            foreach ($keyword as $word)
            {
//                if ($word =='酒'||strpos($word,'酒')) {
//                    $word = '**';
//                    $conditions[] = " g.goods_name LIKE '%{$word}%'";
//                } else {
//                    $this->pr($keyword);exit;
//                foreach (str_split($word,2) as $k) {
//                    $str="%".$k."%";
//                }
                    $conditions[] = "replace(g.goods_name, ' ', '') LIKE '%{$word}%'";
//                }

            }
            $conditions = join(' AND ', $conditions);

            /* 取得满足条件的商品数 */
            $goods_mod =& m('goods');
            $sql = "SELECT COUNT(*) FROM {$goods_mod->table} g WHERE " . $conditions;
            $current_count = $goods_mod->getOne($sql);
            if ($current_count > 0)
            {
                if ($current_count < MAX_ID_NUM_OF_IN)
                {
                    /* 取得商品表记录总数 */
                    $cache_server =& cache_server();
                    $key2 = 'record_count_of_goods';
                    $total_count = $cache_server->get($key2);
                    if ($total_count === false)
                    {
                        $sql = "SELECT COUNT(*) FROM {$goods_mod->table}";
                        $total_count = $goods_mod->getOne($sql);
                        $cache_server->set($key2, $total_count, SEARCH_CACHE_TTL);
                    }

                    /* 不满足条件，返回like */
                    if (($current_count / $total_count) < MAX_HIT_RATE)
                    {
                        /* 取得满足条件的商品id */
                        $sql = "SELECT goods_id FROM {$goods_mod->table} g WHERE " . $conditions;
                        $ids = $goods_mod->getCol($sql);
//                        $conditions = 'g.goods_id' . db_create_in($ids);
                    }
                }
            }
            else
            {
                /* 没有满足条件的记录，返回0 */
                $conditions = "0";
            }

            if ($cached)
            {
                $cache_server->set($key1, $conditions, SEARCH_CACHE_TTL);
            }
        }

        return ' AND (' . $conditions . ')';
    }

    /* 商品排序方式 */
    function _get_orders()
    {
        return array(
            ''                  => Lang::get('select_pls'),
            'sales desc'        => Lang::get('sales_desc'),
            'credit_value desc' => Lang::get('credit_value_desc'),
            'price asc'         => Lang::get('price_asc'),
            'price desc'        => Lang::get('price_desc'),
            'views desc'        => Lang::get('views_desc'),
            'add_time desc'     => Lang::get('add_time_desc'),
        );
    }

    function _get_seo_info($type, $cate_id)
    {
        $seo_info = array(
            'title'       => '',
            'keywords'    => '',
            'description' => ''
        );
        $parents = array(); // 所有父级分类包括本身
        switch ($type)
        {
            case 'goods':
                if ($cate_id)
                {
                    $gcategory_mod =& bm('gcategory');
                    $parents = $gcategory_mod->get_ancestor($cate_id, true);
                    $parents = array_reverse($parents);
                }
                $filters = $this->_get_filter($this->_get_query_param());
                foreach ($filters as $k => $v)
                {
                    $seo_info['keywords'] .= $v['value']  . ',';
                }
                break;
            case 'store':
                if ($cate_id)
                {
                    $scategory_mod =& m('scategory');
                    $scategory_mod->get_parents($parents, $cate_id);
                    $parents = array_reverse($parents);
                }
        }

        foreach ($parents as $key => $cate)
        {
            $seo_info['title'] .= $cate['cate_name'] . ' - ';
            $seo_info['keywords'] .= $cate['cate_name']  . ',';
            if ($cate_id == $cate['cate_id'])
            {
                $seo_info['description'] = $cate['cate_name'] . ' ';
            }
        }
        $seo_info['title'] .= Lang::get('searched_'. $type) . ' - ' .Conf::get('site_title');
        $seo_info['keywords'] .= Conf::get('site_title');
        $seo_info['description'] .= Conf::get('site_title');
        return $seo_info;
    }

    /**
     * 获取商品属性
     */
    function get_goods_attr($cate_id)
    {
        if($this->_gcategory_mod->is_leaf($cate_id)){
            $my_goodsApp = new My_goodsApp();
            $goodsAttr =$my_goodsApp->get_init_attr($cate_id);
            return $this->assign('goodsattrs',$goodsAttr);
        } else {
            $goodsAttr=$this->_goodsattr_mod->get_goods_cateid($cate_id);
            foreach ($goodsAttr as $key=>$ar) {
                $goodsAttr[$key]["def_value"] = explode(",",$ar["def_value"]);
                if ($goodsAttr[$key]["def_value"]) {
                    foreach ($goodsAttr[$key]["def_value"] as $k=>$def) {
                        if (!$def) {
                            unset($goodsAttr[$key]["def_value"][$k]);
                        }
                    }
                }
            }
            return   $this->assign('goodsattrs',$goodsAttr);
        }
    }

    /**
     * 获取商品属性查询条件
     */
    function _get_attrid_condition($param){
        if (isset($param['attr_id'])&&!empty($param['attr_id']))
        {
            $attrids = $param['attr_id'];
            $arid = "";
            $i=0;
            foreach ($attrids as $ar)
            {
                if($i==0)
                {
                    $arid.=$ar;
                }else
                {
                    $arid.=",".$ar;
                }
                $i++;
            }
            return $arid;
        }
       return null;
    }

    /**
     *
     *  获取属性值
     */
    function _get_attrvalue_condition($param) {
        if (isset($param['attr_value'])&&!empty($param['attr_value']))
        {
            $arvalues = $param['attr_value'];
            $arvalue= "";
            $i=0;
            foreach ($arvalues as $val)
            {
                if($i==0)
                {
                    $arvalue.=$val;
                }else
                {
                    $arvalue.=",".$val;
                }
                $i++;
            }
          return $arvalue;
        }
        return null;
    }
}

?>
