<?php
class TeseApp extends MallbaseApp
{
    function index()
    {
        $this->assign('tese', 2); // 标识当前页面是首页，用于设置导航状态
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        //设置title
        $this->_config_seo('title', Lang::get('tese'));
        //获取频道下分类ID
        $_pdgcategory_mod = bm("pdcategory");
        $cate_ids = null;
        if($_GET["pd_id"]) {
            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
        }
        //热销产品
        $this->assign('hot_goods',$this->_get_hot_goods($cate_ids));
        //左菜单分类显示
        $this->assign('tese_category',$this->_list_gcategory($_GET["pd_id"]));
        //特色首页推荐商品
        $this->assign('goods_list',$this->_get_goods_list());
        $this->display('tese.index.html');
    }
    //热门搜索
    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }



    //获取参数
    function _get_query_param() {
        static $res = null;
        if ($res === null)
        {
            $res = array();
            // area
            $tese_area = isset($_GET['tese_area']) ? trim($_GET['tese_area']) : '';
            $res['tese_area'] = $tese_area;
            if(isset($_GET['brand_id'])) {
                $res['brand_id'] = $_GET['brand_id'];
            }
            if(isset($_GET['tese_cate_id'])) {
                $res['tese_cate_id'] = $_GET['tese_cate_id'];
            }
            if(isset($_GET['tese_subcateid'])) {
                $res['tese_subcateid'] = $_GET['tese_subcateid'];
            }
        }
        return $res;
    }
//记住参数
    function _get_filter($param) {
        static $filters = null;
        if ($filters === null)
        {
            $filters = array();
            if (isset($param['tese_area']))
            {
                $filters['tese_area'] = $param['tese_area'];
            }
            if(isset($param['brand_id'])){
                $filters['brand_id'] = $param['brand_id'];
            }
            if(isset($param['tese_cate_id'])) {
                $filters['tese_cate_id'] = $param['tese_cate_id'];
            }
            if(isset($param['tese_subcateid'])) {
                $filters['tese_subcateid'] = $param['tese_subcateid'];
            }
        }

        return $filters;
    }


    //当地土特产
    function get_techan_goods() {
        $this->assign('tese', 2); // 标识当前页面是首页，用于设置导航状态
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        //设置title
        $this->_config_seo('title', Lang::get('tese'));
        //左菜单分类显示
        $this->assign('tese_category',Conf::get('region'));
        //子菜单
        $this->assign('son_category',$this->gcategorys());

        $param = $this->_get_query_param();
//        //记住参数
        $filter = $this->_get_filter($param);
        //获取下级ID
        $gcategory_mod =& bm('gcategory');
        $cate_ids = null;
        $_pdgcategory_mod = bm("pdcategory");
        if($_GET["pd_id"]) {
            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
        }
//        if($_GET["tese_cate_id"]) {
//            $cate_ids = $gcategory_mod->get_descendant($_GET["tese_cate_id"]);
//        }
        //热销产品
        $this->assign('hot_goods',$this->_get_hot_goods($cate_ids));
        //获取数据
//        $data = $this->get_son_gcategory($cate_ids,'');
        $this->assign('goods_list',$this->_get_tuijian_list($param));
        //分页信息
//        $this->assign("page_info",$data['page']);
//        $this->assign('goods_list',$data['goods_list']);
        $this->display('tese.tutechan.html');
    }

    //获取省市下的分类
    function get_tutechan_sub() {
        $this->assign('tese', 3); // 标识当前页面是首页，用于设置导航状态
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        //设置title
        $this->_config_seo('title', Lang::get('tese'));
        //左菜单分类显示
        $gcategory_mod =& bm('gcategory');
        $gcategroys = null;
        $gcate= $gcategory_mod->get($_GET["tese_cate_id"]);
        if($gcategory_mod->get_depth($_GET["tese_cate_id"])==2) {
            $arr1 = null;
            $arr = $gcategory_mod->get_list($_GET["tese_cate_id"]);
            foreach ($arr as $key=>$gc) {
                $arr1[$key] = array("cate_id"=>$gc["cate_id"],"cate_name"=>$gc["cate_name"]);
//                if ($gcategory_mod->get_depth($gc["cate_id"])==4) {
////                    foreach
////                    $arr1[$key]['children'] = array("cate_id"=>$gc["cate_id"],"cate_name"=>$gc["cate_name"]);
//                }
            }
            $gcategroys[$_GET["tese_cate_id"]]["cate_info"] =  $arr1;
            $gcategroys[$_GET["tese_cate_id"]]["parent_name"] = $gcate["cate_name"];
        } else {
            $gcategroys = $gcategory_mod->get_list($_GET["tese_cate_id"]);
            foreach ($gcategroys as $key=>$gc) {
                if (!$gcategory_mod->is_leaf($gc["cate_id"])) {
                    $gcategroys[$key]["cate_info"] = $gcategory_mod->get_list($gc["cate_id"]);
                }
            }

        }
        $this->assign('tese_category',$gcategroys);
//        $this->pr($gcategroys);
//        exit;
        //获取下级ID
        $cate_ids = null;
        if($_GET["tese_cate_id"]) {
            $cate_ids = $gcategory_mod->get_descendant($_GET["tese_cate_id"]);
        }

//        //热销产品
//        $this->assign('hot_goods',$this->_get_hot_goods($cate_ids));
        //获取数据
        $data = $this->get_goods_list($cate_ids,'');
        //分页信息
        $this->assign("page_info",$data['page']);
        $this->assign('goods_list',$data['goods_list']);
        $this->display('tese.tutechan_sub.html');
    }
    //根据分类找下级分类
//    function  get_son_gcategory() {
//
//        /* 热门搜素 */
//        $this->assign('hot_keywords', $this->_get_hot_keywords());
//        //设置title
//        $gcategory_mod =& bm('gcategory');
//
//        $gcategroys = null;
//        //获取参数
//        $param = $this->_get_query_param();
//        //记住参数
//        $filter = $this->_get_filter($param);
//
//        //子分类筛选
//        if($param['tese_subcateid']) {
//            $cate_ids = $param['tese_subcateid'];
//        } else {
//            $cate_ids = $gcategory_mod->get_descendant($param["tese_cate_id"]);
//        }
//        //获取子分类
//        $gcategroys = $gcategory_mod->get_children($param["tese_cate_id"]);
//        $goods_mod = &m('goods');
//        $conditions = "1=1";
//        //根据省市获取当地省市的商家ID
//        if($param['tese_area'] && !empty($param['tese_area'])) {
//            $store_ids = $this->get_store_byarea($param);
//            $conditions .=" and if_show = 1 AND closed = 0  and cate_id ".db_create_in($cate_ids)." AND s.state =" . STORE_OPEN."  and s.store_id ".db_create_in($store_ids);
//        } else {
//            $conditions .=" and if_show = 1 AND closed = 0  and cate_id ".db_create_in($cate_ids)." AND s.state =" . STORE_OPEN;
//        }
//        $data = null;
//        $page   =   $this->_get_page(18);
//        $goods_list = $goods_mod->get_list(array(
//            'conditions' => $conditions,
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
//        $this->assign('tese_category',$gcategroys);
//        $this->assign('filters',$filter);
//        $this->assign('page_info',$page);
//        $this->_config_seo('title', Lang::get('tese'));
//        if(empty($_GET['pd_id'])){
//            $this->assign('category',$gcategory_mod->get($param["tese_cate_id"]));
//        }
//        $this->display('tese.local.html');
//
//    }


    function get_son_gcategory() {
        $gcategory_mod =& bm('gcategory');
        $param = $this->_get_query_param();
//        $this->assign('tese_category',$gcategroys);
//        //记住参数
        $filter = $this->_get_filter($param);
        $this->assign('filters',$filter);
        $this->assign('goods_list',$this->_get_tuijian_list($param));
//        if(empty($_GET['pd_id'])){
         $this->assign('category',$gcategory_mod->get($param["tese_cate_id"]));
//        }
        $this->display('tese.local.html');
    }
    //获取当地土特产地区下显示的小分类
    function  gcategorys() {
        $gcategory_mod =& bm('gcategory');
        $region = Conf::get('region');
        $gcategorys = null;
        foreach ($region as $key=>$diqu) {
            foreach($diqu  as $cate_id=>$pro) {
                if ($gcategory_mod->get($cate_id)) {
                    $gcategorys[$cate_id]=$gcategory_mod->get_list($cate_id);
                    if($gcategorys[$cate_id]) {
                        foreach ($gcategorys[$cate_id] as $key=>$child) {
                            if ($gcategory_mod->get_list($child["cate_id"])) {
                                $gcategorys[$cate_id][$key]["cate_name"] = $child["cate_name"];
                                $gcategorys[$cate_id][$key] = $gcategory_mod->get_list($child["cate_id"]);
                            }
                        }
                    }
                }
            }
        }
        return $gcategorys;
    }
    /*热销品*/
    function _get_hot_goods($cate_ids)
    {
        $goods_mod = &m('goods');

        $goods_list = $goods_mod->get_list(array(
            'conditions' => "if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN,
            'scate_ids' => $cate_ids,
            'order' => 'sales DESC',
            'join' => 'has_goodsstatistics, belongs_to_store',
            'limit' => 5,  false, true
        ));
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
      return $goods_list;
    }


    /*特色大集 --首页展示*/
    function _get_goods_list()
    {
        $data = null;
        $configs = Conf::get('tese_index_goods');
        $num = '6';
        $recom_mod =& m('recommend');
        if ($configs) {
            foreach ($configs as $key=>$val) {
                $data[$val] = $recom_mod->get_recommended_goods($key, $num, true, '10');
            }
        }
        return $data;
    }

    //推荐
    function _get_tuijian_list($param)
    {
        $config = 22;
        if($param['tese_cate_id']&&$param['tese_cate_id']==1613) {
            $config = 22;
        } else if($param['tese_cate_id']&&$param['tese_cate_id']==1614){
            $config = 21;
        } else if($param['tese_cate_id']&&$param['tese_cate_id']==1615){
            $config = 20;
        }else if($param['tese_cate_id']&&$param['tese_cate_id']==1616){
            $config = 19;
        }else if($param['tese_cate_id']&&$param['tese_cate_id']==1617){
            $config = 45;
        }
        $recom_mod =& m('recommend');
        $data = $recom_mod->get_recommended_goods($config, 30, true, 0);
        return $data;
    }
    //分类下的商品列表
    function get_goods_list($cate_ids,$param) {
        $goods_mod = &m('goods');
        $conditions = "1=1";
        //根据省市获取当地省市的商家ID
         if($param['tese_area'] && !empty($param['tese_area'])) {
             $store_ids = $this->get_store_byarea($param);
             $conditions .=" and if_show = 1 AND closed = 0  and cate_id_1 ".db_create_in($cate_ids)." AND s.state =" . STORE_OPEN." and s.store_id ".db_create_in($store_ids);
         } else {
             $conditions .=" and if_show = 1 AND closed = 0  and cate_id_1 ".db_create_in($cate_ids)." AND s.state =" . STORE_OPEN;
         }
        $data = null;
        $page   =   $this->_get_page(18);
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions,
            'join' => 'has_goodsstatistics, belongs_to_store',
            'count' => true,
            'limit' => $page['limit']
        ));
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
        $data['goods_list'] = $goods_list;
        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $data['page'] = $page;
        return $data;
    }

    //限时抢购
      function get_goods_qianggou() {
          $groupbuy_mode = & m('groupbuy');
          $goods_mod = & m('goods');
          $_pdgcategory_mod = bm("pdcategory");
          $cate_ids = null;
          $gcategory_model = & bm("gcategory");
          $gcategory_list =null;
          $sql = "SELECT gd.* FROM {$groupbuy_mode->table} gb, {$goods_mod->table} gd WHERE gd.goods_id=gb.goods_id AND gd.cate_id_1 IN (SELECT category_id FROM {$_pdgcategory_mod->table} WHERE pd_id =3) ";
          $goods_list= $goods_mod->db->getAll($sql);
          if ($goods_list) {
              foreach ($goods_list as $key=>$goods) {
                  $gcategory_list[$key] = $gcategory_model->get($goods['cate_id']);
              }
          }
          $this->assign('gcategory_list',$gcategory_list);
          $this->assign('goods_list',$goods_list);
          $this->display('tese.qianggou.html');
      }
}

?>
