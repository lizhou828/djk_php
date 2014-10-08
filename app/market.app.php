<?php


class MarketApp extends MallbaseApp
{
    function index()
    {
        $this->assign('market', 2); // 标识当前页面是超市，用于设置导航状态
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo('title', Lang::get('market'));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));

        $this->assign('market_category',$this->_list_gcategory($_GET["pd_id"]));

        $this->assign('new_goods',$this->_get_new_goods());
        $this->assign('goods_list',$this->goods_model());
        $this->display('market.index.html');
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    //超市分类
    function get_market_sub() {

        $this->assign('hot_keywords', $this->_get_hot_keywords());
        $this->_config_seo('title', Lang::get('market'));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $param = $this->_get_query_param();
        if (empty($param['market_area'])) {
            header("Location:index.php?app=market&pd_id=2");
        } else {
            $regin_mode = & m("region");
            if (!empty($param['market_reg_id'])) {
                $city = $regin_mode->get_options($param['market_reg_id']);
                $this->assign('city',$city);
            }
            if (!empty($param['market_city_id'])) {
                $diqu = $regin_mode->get_options($param['market_city_id']);
                $this->assign('diqu',$diqu);
            }
            $this->assign('market_category',$this->_list_gcategory(2));
            $this->assign('son_category',$this->get_son_category($param['market_class']));

            $this->assign("filter",$this->_get_filter($param));
            $data = $this->get_goods($param);
            $this->assign('goods_list',$data['goods_list']);
            $this->assign('page_info',$data['page']);
            $this->display('market.sub.html');
        }

    }
    //获取子分类
    function get_son_category($cate_id) {
        $gcategory_mod = & m('gcategory');
        if ($cate_id!='') {
            $childrens =   $gcategory_mod->get_list($cate_id);
        }
        return $childrens;
    }
    //获取参数
    function _get_query_param() {
        static $res = null;
        if ($res === null)
        {
            $res = array();
            // area
            $market_area = isset($_GET['market_area']) ? trim($_GET['market_area']) : '';
            $res['market_area'] = $market_area;

            if(isset($_GET['pd_id'])) {
                $res['pd_id'] = $_GET['pd_id'];
            }

            if(isset($_GET['brand_id'])) {
                $res['brand_id'] = $_GET['brand_id'];
            }
            if(isset($_GET['market_class'])) {
                $res['market_class'] = $_GET['market_class'];
            }
            if(isset($_GET['market_subcateid'])) {
                $res['market_subcateid'] = $_GET['market_subcateid'];
            }
            if(isset($_GET['market_reg_id'])) {
                $res['market_reg_id'] = $_GET['market_reg_id'];
            }
            if(isset($_GET['market_city_id'])) {
                $res['market_city_id'] = $_GET['market_city_id'];
            }
            if(isset($_GET['market_diqu_id'])) {
                $res['market_diqu_id'] = $_GET['market_diqu_id'];
            }
            if(isset($_GET['market_order'])) {
                $res['market_order'] = $_GET['market_order'];
            }
            if(isset($_GET['market_order_key'])) {
                $res['market_order_key'] = $_GET['market_order_key'];
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
            if (isset($param['market_area']))
            {
                $filters['market_area'] = $param['market_area'];
            }
            if(isset($param['brand_id'])){
                $filters['brand_id'] = $param['brand_id'];
            }
            if(isset($param['market_class'])) {
                $filters['market_class'] = $param['market_class'];
            }
            if(isset($param['market_subcateid'])) {
                $filters['market_subcateid'] = $param['market_subcateid'];
            }
            if(isset($param['market_reg_id'])) {
                $filters['market_reg_id'] = $param['market_reg_id'];
            }
            if(isset($param['market_city_id'])) {
                $filters['market_city_id'] = $param['market_city_id'];
            }
            if(isset($param['market_diqu_id'])) {
                $filters['market_diqu_id'] = $param['market_diqu_id'];
            }
            if(isset($param['market_order'])) {
                $filters['market_order'] = $param['market_order'];
            }
            if(isset($param['market_order_key'])) {
                $filters['market_order_key'] = $param['market_order_key'];
            }
        }

        return $filters;
    }


    /*新品*/
    function _get_new_goods()
    {
        $num = '5';
        $recom_mod =& m('recommend');
        $data = $recom_mod->get_recommended_goods(conf::get('market_new_goods'), $num, true, '10');
        return $data;
    }

    //商品筛选
    function get_goods($param) {
        $goods_mod = & m('goods');
        $conditions = " 1=1 ";
        $region_mod = & m('region');

        if (!empty($param['market_diqu_id'])) {
            $conditions.=" and exists (select 1 from ecm_store s where s.store_id=g.store_id and s.region_id=".$param['market_diqu_id'].")";
        }else if (!empty($param['market_city_id'])) {
            $reiong_list =  $region_mod->get_options($param['market_city_id']);
            if(count($reiong_list)>0){
                $region_ids="";
                foreach($reiong_list as $k => $v){
                    if($region_ids==""){
                        $region_ids="s.region_id=".$k;
                    }else{
                        $region_ids.=" or s.region_id=".$k;
                    }
                }
                $conditions.=" and exists (select 1 from ecm_store s where s.store_id=g.store_id and ($region_ids))";
            }

        }else if(!empty($param['market_reg_id'])) {
            $reiong_list =  $region_mod->get_options($param['market_reg_id']);
            $region_ids = "";
            if (count($reiong_list)>0) {
                foreach ($reiong_list as $key=>$reg) {
                    $tmp1 = $region_mod->get_options($key);
                    foreach ($tmp1 as $k=>$val) {
                        if($region_ids==""){
                            $region_ids=" s.region_id=".$k;
                        }else{
                            $region_ids .=" or s.region_id=".$k;
                        }
                    }
                }

               $conditions.=" and exists (select 1 from ecm_store s where s.store_id=g.store_id and ($region_ids))";
          }
        }
        if (!empty($param['market_class'])) {
            $conditions.=" and cate_id_1= ".$param['market_class'];
        }
        if (!empty($param['market_subcateid'])) {
            $conditions.=" and cate_id_2= ".$param['market_subcateid'];
        }
        $page   =   $this->_get_page(12);
        $goods_list = $goods_mod->get_list(array(
          'conditions' => $conditions." and g.dropState=1 and g.if_show=1 and g.if_jifen=0 ",
          'count' => true,
          'order'      => (( $param['market_order_key']==null ||  $param['market_order_key']=="")?"views":$_GET['order'])." ".$param['market_order'],
          'limit' => $page['limit'],
        ));
        $data['goods_list'] = $goods_list;
        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $data['page'] = $page;
        return $data;
    }


    /*商品模块展示*/
    function goods_model()
    {
        $_pdgcategory_mod = bm("pdcategory");
        $gcategory_mod = & bm('gcategory');
        $goods_mod = & m('goods');
        $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
        $goods_list = null;

        if($cate_ids) {
            foreach ($cate_ids as $key=>$cate_id) {

               $goods_list[$key] = $gcategory_mod->get($cate_id);
               $goods_list[$key]['children_cate']= $gcategory_mod->get_children($cate_id);
               $goods_list[$key]['goods'] =  $goods_mod->get_list(array(
                    'conditions' => "if_show = 1 AND closed = 0 and if_jifen=0  AND s.state =" . STORE_OPEN,
                    'scate_ids' => $cate_id,
//                    'order' => 'sales DESC',
                    'fields' => 'g.goods_id, g.goods_name',
                    'join' => 'belongs_to_store',
                    'limit' => 12,  false, true
                ));
            }
        }
//        $this->print_r($goods_list);
//        exit;
        return $goods_list;
    }

}

?>
