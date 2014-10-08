<?php
class LydjApp extends MallbaseApp
{
    function index()
    {
        $region_mod =& m('region');
        $tmp=$region_mod->get_options(1);
        $this->assign('tese', 2); // 标识当前页面是首页，用于设置导航状态
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());


        $this->_config_seo('title', Lang::get('lydj'));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $param = $this->_get_query_param();
        $filter =$this->_get_filter($param);
        $this->assign('filters',$filter);
        $this->assign('region',$tmp);
//        $this->pr($this->gcategorys()) ;
//        exit;
        $_pdgcategory_mod = bm("pdcategory");
        $gcatgory_mode = m('gcategory');
//        $cate_ids = null;
//        if($_GET["pd_id"]) {
//            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
//        }
        $this->assign('goods_list',$this->_get_goods_remmeond());

        $this->display('lydj.index.html');
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    /*推荐商品*/
    function _get_goods_remmeond()
    {
        $data = null;
        $configs = Conf::get('lydj_index_goods');
        $num = '5';
        $recom_mod =& m('recommend');
        if ($configs) {
            foreach ($configs as $key=>$val) {
                $data[$val] = $recom_mod->get_recommended_goods($key, $num, true, '10');
            }
        }
        return $data;
    }

    //获取商品列表
    function get_goods_list($cate_ids) {
        $goods_mod = &m('goods');

        $goods_list = $goods_mod->get_list(array(
            'conditions' => "if_show = 1 AND closed = 0 and dropState=1 AND s.state =" . STORE_OPEN." and cate_id_1".db_create_in($cate_ids),
//            'join' => 'has_goodsstatistics, belongs_to_store',
        ));

        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
        return $goods_list;
    }

    //获取参数
    function _get_query_param() {
        static $res = null;
        if ($res === null)
        {
            $res = array();
            // area
            $lydj_area = isset($_GET['lydj_area']) ? trim($_GET['lydj_area']) : '';
            $res['lydj_area'] = $lydj_area;
            if(isset($_GET['lydj_class'])) {
                $res['lydj_class'] = $_GET['lydj_class'];
            }
            if(isset($_GET['pd_id'])) {
                $res['pd_id'] =$_GET['pd_id'];
            }
            if(isset($_GET['lydj_subcateid'])) {
                $res['lydj_subcateid'] =$_GET['lydj_subcateid'];
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
            if (isset($param['lydj_area']))
            {
                $filters['lydj_area'] = $param['lydj_area'];
            }
            if(isset($param['lydj_class'])) {
                $filters['lydj_class'] = $param['lydj_class'];
            }
            if(isset($_GET['pd_id'])) {
                $filters['pd_id'] =$_GET['pd_id'];
            }
            if(isset($_GET['lydj_subcateid'])) {
                $filters['lydj_subcateid'] =$_GET['lydj_subcateid'];
            }
        }
        return $filters;
    }

//    function get_goods_category() {
//        $region_mod =& m('region');
//        $tmp=$region_mod->get_options(1);
//        $this->_config_seo('title', Lang::get('lydj'));
//
//        $this->assign('page_description', Conf::get('site_description'));
//        $this->assign('page_keywords', Conf::get('site_keywords'));
//        $param = $this->_get_query_param();
//        $filter =$this->_get_filter($param);
//        $this->assign('filters',$filter);
//        $this->assign('region',$tmp);
//        $gcatgory_mode = & bm('gcategory');
//        $gcategorys = null;
//        $cate_ids = null;
//        if($param["lydj_class"]) {
//            $cate_ids= $gcatgory_mode->get_children($param['lydj_class']);
//            $conditions = "if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN." and cate_id_1".db_create_in($cate_ids);
//        }
//
//        if ($cate_ids) {
//            foreach ($cate_ids as $cate_id) {
//                $gcategorys[] = $gcatgory_mode->get($cate_id['cate_id']);
//            }
//        }
//        $this->assign('gcategory_list',$gcategorys);
//
//        if($param['lydj_subcateid']) {
//             $conditions = "if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN." and cate_id".db_create_in($param['lydj_subcateid']);
//        }
//
//        $goods_mod = &m('goods');
//
//
//        $goods_list = $goods_mod->get_list(array(
//            'conditions' => $conditions,
//            'join' => 'has_goodsstatistics, belongs_to_store',
//        ));
//        foreach ($goods_list as $key=>$goods) {
//            if (strpos($goods['default_image'],'/')===false) {
//                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
//            }
//        }
//        $this->assign('goods_list',$goods_list);
//
//        $this->display('lydj.sub.html');
//    }

    function get_goods_category() {



        $this->_config_seo('title', Lang::get('lydj'));

        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $param = $this->_get_query_param();
        $filter =$this->_get_filter($param);
        $this->assign('filters',$filter);

        $num = '10000';
        $recom_mod =& m('recommend');
                if($param['lydj_class']==1665) {
                    $goods_list = $recom_mod->get_recommended_goods(23, $num, true);
                } elseif($param['lydj_class']==1666) {
                    $goods_list = $recom_mod->get_recommended_goods(24,$num, true);
                } elseif ($param['lydj_class']==1667) {
                    $goods_list = $recom_mod->get_recommended_goods(25, $num, true);
                }elseif ($param['lydj_class']==1670) {
                    $goods_list = $recom_mod->get_recommended_goods(43, $num, true);

            }
        $this->assign('goods_list',$goods_list);
        $this->display('lydj.sub.html');
    }

}

?>
