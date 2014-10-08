<?php


class DefaultApp extends MallbaseApp
{
    function index()
    {

        /*if(is_mobile_request()){
            header("Location: ".site_url()."/weixin");
            exit;
        }*/

        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态
        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */

//        echo "<pre>";
//        print_r($gcategorys);
//        echo "</pre>";
        /*商城公告*/
        $news_ad = $this->get_news_ad(ACC_NOTICE);
        /*精品推荐*/
        $this->assign('recommendeds', $this->_get_goods_remmeond());

        $this->assign('hot_category',$this->_get_hot_category());
        /*商品排行*/
//        $this->assign('topSales', $this -> _get_top_sale());
        /*友情链接*/
        $this->assign('linkurls', $this->_get_link_url());
        /*商品模块展示*/
        $cache = & cache_server();
        $goods_module = $cache->get('good_module');
        if($goods_module === false){
            $goods_module1 = $this->goods_model(conf::get('goods_shouye_jinkou'));
            $goods_module2 = $this->goods_model(conf::get('goods_tuijian_1'));
            $goods_module3 = $this->goods_model(conf::get('goods_tuijian_2'));
            $goods_module4 = $this->goods_model(conf::get('goods_tuijian_3'));
            $goods_module5 = $this->goods_model(conf::get('goods_tuijian_4'));
            $goods_module6 = $this->goods_model(conf::get('goods_tuijian_5'));
            $goods_module7 = $this->goods_model(conf::get('goods_tuijian_6'));
            $goods_module8 = $this->goods_model(conf::get('goods_tuijian_7'));

            $goods_module = array(
                'goods_module1' => $goods_module1,
                'goods_module2' => $goods_module2,
                'goods_module3' => $goods_module3,
                'goods_module4' => $goods_module4,
                'goods_module5' => $goods_module5,
                'goods_module6' => $goods_module6,
                'goods_module7' => $goods_module7,
                'goods_module8' => $goods_module8);
            $cache->set('good_module',$goods_module,1800);
        }

        $this->assign('hgg_category', Conf::get('hgg_category'));
        $this->assign('goods_module1', $goods_module['goods_module1']);
        $this->assign('goods_module2', $goods_module['goods_module2']);
        $this->assign('goods_module3', $goods_module['goods_module3']);
        $this->assign('goods_module4', $goods_module['goods_module4']);
        $this->assign('goods_module5', $goods_module['goods_module5']);
        $this->assign('goods_module6', $goods_module['goods_module6']);
        $this->assign('goods_module7', $goods_module['goods_module7']);
        $this->assign('goods_module8', $goods_module['goods_module8']);

        $this->_config_seo(array(
            //'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
            'title' => Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->assign('news_ad', $news_ad); //商城公告

        $this->assign('site_index', 1);
        $this->assign('huanden',$this->get_guanggao('shouye_huandeng'));

        $this->assign('layer_1',$this->get_guanggao('shouye_layer_1'));
//        $this->pr($this->get_guanggao('shouye_layer_1'));
//        exit;
        $this->assign('layer_2',$this->get_guanggao('shouye_layer_2'));
        $this->assign('layer_3',$this->get_guanggao('shouye_layer_3'));
        $this->assign('layer_4',$this->get_guanggao('shouye_layer_4'));
        $this->assign('layer_5',$this->get_guanggao('shouye_layer_5'));
        $this->assign('layer_6',$this->get_guanggao('shouye_layer_6'));
        $this->assign('layer_7',$this->get_guanggao('shouye_layer_7'));
        $this->assign('layer_8',$this->get_guanggao('shouye_layer_8'));
        $this->assign('shouye_huandeng_right',$this->get_guanggao('shouye_huandeng_right'));
        $this->assign('shouye_top',$this->get_guanggao('shouye_top'));
        $this->assign('shouye_xiaotu',$this->get_guanggao('shouye_xiaotu'));
        $this->assign('shouye_layer_1_brand',$this->get_guanggao('shouye_layer_1_brand'));
        $this->assign('shouye_layer_2_brand',$this->get_guanggao('shouye_layer_2_brand'));
        $this->assign('shouye_layer_3_brand',$this->get_guanggao('shouye_layer_3_brand'));
        $this->assign('shouye_layer_4_brand',$this->get_guanggao('shouye_layer_4_brand'));
        $this->assign('shouye_layer_5_brand',$this->get_guanggao('shouye_layer_5_brand'));
        $this->assign('shouye_layer_6_brand',$this->get_guanggao('shouye_layer_6_brand'));
        $this->assign('shouye_layer_7_brand',$this->get_guanggao('shouye_layer_7_brand'));
        $this->assign('shouye_layer_8_brand',$this->get_guanggao('shouye_layer_8_brand'));
        $this->display('index.html');
    }

    function get_chat(){
        if(IS_CHAT!="ON"){
            exit;
        }
        $user = $this->visitor->get();
        if(substr($user['user_name'], 0, 5) == 'djkkf' || $_SESSION["if_show_chat"] ==1 || $_GET["init_chat"]){
            $_SESSION["if_show_chat"] = 1;
            $this->display('index_chat.html');
        }
    }


    //获取登录用户对象
    function get_vistor() {
        $user = $this->visitor->get();
        $data['member_type'] = $user['member_type'];
        $data['user_name'] = $user['user_name'];
        $data['user_id'] = $user['user_id'];
        if (preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$user['nick_name'])){
            $user['nick_name'] = substr_replace($user['nick_name'],'****',-8,4);
        }
        $data['nick_name'] = $user['nick_name'];

        echo json_encode($data);
    }

        function _get_hot_category(){
           $category =  conf::get('hot_category');
           return $category;
        }

//    function _get_news($acc){
//        $acategory_mod =& m('acategory');
//        $article_mod =& m('article');
//        $data = $article_mod->find(array(
//            'conditions' => 'cate_id=' . $acategory_mod->get_ACC($acc) . ' AND if_show = 1',
//            'order' => 'sort_order ASC, add_time DESC',
//            'fields' => 'article_id, title, add_time',
//            'limit' => 7,
//        ));
//        return $data;
//    }



    /*取得商城公告.*/
    function get_news_ad($code)
    {
        $cache_server =& cache_server();
        $key = 'notice';
        $data = $cache_server->get($key);
        if ($data === false) {
            $acategory_mod =& m('acategory');
            $article_mod =& m('article');
            $data = $article_mod->find(array(
                'conditions' => 'cate_id=' . $acategory_mod->get_ACC($code) . ' AND if_show = 1 and article.dropState=1',
                'order' => 'sort_order ASC, add_time DESC',
                'fields' => 'article_id, title, add_time',
                'limit' => 7,
            ));
            $cache_server->set($key, $data, 1800);
        }
        return array(
            'notices' => $data,
        );
    }


    /*推荐商品*/
    function _get_goods_remmeond()
    {
        $cache_server =& cache_server();
        $key = 'best_goods';
        $num = '5';
        $data = $cache_server->get($key);
        if ($data === false) {
            $recom_mod =& m('recommend');
            $data = $recom_mod->get_recommended_goods(conf::get('goods_jinping'), $num, true, '10');
            $cache_server->set($key, $data, 1800);
        }
//        $this->pr($data);
//        exit;
        return $data;
    }

    /*商品排行*/
    /* function _get_top_sale()
     {
         $cache_server =& cache_server();
         $key = 'top_sale';
         $data = $cache_server->get($key);
         if($data === false)
         {
             $goods_mod =& m('goods');
             $data = $goods_mod->find(array(
                 'conditions' => "if_show = 1 AND closed = 0 AND s.state =" . STORE_OPEN,
                 'order' => 'sales DESC',
                 'fields' => 'g.goods_id, g.goods_name',
                 'join' => 'has_goodsstatistics, belongs_to_store',
                 'limit' => 10,
             ));
             $cache_server->set($key, $data,1800);
         }
         return $data;
     }*/

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
        $cate_goods_list = $recom_mod->get_recommended_goods($recom_id,6, true, 0);
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
            array_push($gcategorys_goods, array("fachercate" => $gcategory_mod->get("cate_id=$catId and dropState=1"), array("childcate" => $gcategory_mod->get_children($catId, false))));
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
                'limit' => 10,  false, true
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


    /*友情链接*/
    function _get_link_url()
    {
        $cache_server =& cache_server();
        $key = 'link_url';
        $data = $cache_server->get($key);
        if ($data === false) {
            $partner_mod =& m('partner');
            $data = $partner_mod->find(array(
                'conditions' => "store_id = 0",
                'order' => 'sort_order',
                'limit' => 15,
            ));
            $cache_server->set($key, $data, 86000);
        }
        return $data;
    }

    function ydshop() {

        $this->_config_seo(array(
            'title' => '大集客移动商城-集合全国土特产、本地生活、工厂直供、进口商品等为一体的大型B2C、C2C、O2O综合移动终端平台，正品低价、品质保障、放心服务！',
        ));
        $this->display('ydshop.html');
    }

        function sjbActive() {
            $num = '1000';
            $this->_config_seo(array(
                'title' => '2014年世界杯纪念金钞同步限量发行',
            ));
            $recom_mod =& m('recommend');
            $data = array();
            $page   =   $this->_get_page(41);
            $list =  $recom_mod->get_recommended_goods2(Conf::get('sjb-tuijian'), $num, true, 0, 0,array('limit'=>$page['limit']));
            $page['item_count'] =count($recom_mod->get_recommended_goods(Conf::get('sjb-tuijian'), $num, true, 0, 0));
            $this->_format_page($page);
            $data['page'] = $page;
            $this->assign('goods_list',$list);
            $this->assign('page_info',$data['page']);
            $this->display('sjbActive.html');
        }
}

?>
