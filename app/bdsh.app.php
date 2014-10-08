<?php


class BdshApp extends MallbaseApp
{
    var $_goods_mod;
    var $_store_mod;
    var $_category_store_mod;
    var $_scategory_mod;
    function __construct()
    {
        $this->BdshApp();
    }
    function BdshApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
        $this->_store_mod =& m('store');
        $this->_category_store_mod=& m('categorystore');
        $this->_scategory_mod=& m('scategory');
        $this->assign('bdsh_category',$this->_list_gcategory(5));
    }
    function index()
    {
        $param = $this->_get_query_param();
        $filter =  $this->_get_filter($param);
        $this->assign('bdsh', 3); // 标识当前页面是首页，用于设置导航状态
//        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        //设置title
        $this->_config_seo(array('title'=> $filter['bdsh_area'].Lang::get('bdsh')));
        //获取菜单分类


        $this->assign('quyu',$this->get_quyu($param['bdsh_reg_id']));
        //获取频道下分类ID
        $_pdgcategory_mod = bm("pdcategory");
        $cate_ids = null;
        if($_GET["pd_id"]) {
            $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
        }
        //根据地区获取商品
        $goods_module1 =  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_1"));
        $goods_module2 =  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_2"));
        $goods_module3 = $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_3"));
        $goods_module4 =  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_4"));
        $goods_module5 =  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_5"));
        $goods_module6 =  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_6"));
        $goods_module7=  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_7"));
        $goods_module8 = $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_8"));
        $goods_module9 =  $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_9"));
//        $this->pr($goods_module['goods_module1']);
//        exit;
        $this->assign('goods_module1',$goods_module1);
        $this->assign('goods_module2', $goods_module2);
        $this->assign('goods_module3', $goods_module3);
        $this->assign('goods_module4', $goods_module4);
        $this->assign('goods_module5', $goods_module5);
        $this->assign('goods_module6', $goods_module6);
        $this->assign('goods_module7', $goods_module7);
        $this->assign('goods_module8', $goods_module8);
        $this->assign('goods_module9', $goods_module9);
//        $data = $this->get_goods_list($cate_ids,$param,Conf::get("bdsh_index_layer_1"));

        $this->assign('bd_gcategory',$this->gcategory());
//        $this->assign("goods_list",$data['goods_list']);
//        $this->assign("page_info",$data['page']);
        $this->assign('filters',$filter);
        $this->display('bdsh.index.html');
    }

    function get_quyu($region_id) {
        $region_mode = & m('region');
        $quyu = $region_mode->get_options($region_id);
        return $quyu;
    }

    function  gcategory() {
        $cate_mode = &m('gcategory');
        $_category = array(
            '1'=>array('cate_name'=>Conf::get("bdsh_index_layer_1_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_1'))),
            '2'=>array('cate_name'=>Conf::get("bdsh_index_layer_2_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_2'))),
            '3'=>array('cate_name'=>Conf::get("bdsh_index_layer_3_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_3'))),
            '4'=>array('cate_name'=>Conf::get("bdsh_index_layer_4_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_4'))),
            '5'=>array('cate_name'=>Conf::get("bdsh_index_layer_5_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_5'))),
            '6'=>array('cate_name'=>Conf::get("bdsh_index_layer_6_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_6'))),
            '7'=>array('cate_name'=>Conf::get("bdsh_index_layer_7_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_7'))),
            '8'=>array('cate_name'=>Conf::get("bdsh_index_layer_8_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_8'))),
            '9'=>array('cate_name'=>Conf::get("bdsh_index_layer_9_name"),'cate_id'=>$cate_mode->get_list(Conf::get('bdsh_index_layer_9'))),
        );
//        $this->pr($_category);
//        exit;
        return $_category;
    }

    function change_city() {
        $this->assign('shengshi',$this->get_all_city());
        $this->assign('type',$_GET['search_type']);
        $this->assign('filters',$this->_get_filter($this->_get_query_param()));
        $this->_config_seo(array('title'=> Lang::get('bdsh')));
        $this->display('bdsh.city.html');
    }
    //热门搜索
    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    function get_all_city() {
        $region_mode = & m('region');
        $sheng = $region_mode->get_options(1);
        $city = array();

        foreach ($sheng as $key=>$s) {
            $city[$key] =$region_mode->get_list($key);
            $city[$key]['shengfen'] = $s;
        }
        return $city;
    }
//   function get_hot_gcategory($goods_list) {
//       $gcategory = null;
//       $gcategory_mode = & m('gcategory');
//
//       if ($goods_list) {
//           foreach ($goods_list as $goods) {
//
//           }
//       }
//   }
    //根据分类和地区筛选商品
    function  get_goods_list($cate_ids,$param,$layer_cate) {

        $goods_mod = &m('goods');
        $conditions = "1=1 and g.pd_id =5 and g.dropState =1 and g.if_show=1";
        $region_mod = & m('region');
        //根据省市获取当地省市的商家ID
        if($param['bdsh_reg_id'] && !empty($param['bdsh_reg_id'])) {
            $reiong_list =  $region_mod->get_options($param['bdsh_reg_id']);
            if(count($reiong_list)>0){
                $region_ids="";
                foreach($reiong_list as $k => $v){
                    if($region_ids==""){
                        $region_ids="s.region_id=".$k;
                    }else{
                        $region_ids.=" or s.region_id=".$k;
                    }
                }
            }
            $conditions .=" and if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN." and exists (select 1 from ecm_store s where s.store_id=g.store_id and ($region_ids))";
        } else {
            $conditions .=" and if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN;
        }
//        if ($param['brand_id']){
//            $conditions.=" and  brand_id =".$param['brand_id'];
//        }
        if ($layer_cate) {
            $conditions.=" and cate_id_1 =".$layer_cate;
        }
        $data = null;
        $page   =   $this->_get_page(15);
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions,
//            'scate_ids' => $cate_ids,
//            'join' => 'has_goodsstatistics, belongs_to_store',
            'order'      => 'g.last_update desc ',
            'count' => true,
            'limit' => $page['limit']
        ));
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
            if ($goods['org_price']&&$goods['price']) {
                $goods_list[$key]['account'] =sprintf("%.1f",($goods['org_price']/$goods['price'])*10)."折";
            } else {
                $goods_list[$key]['account'] = '5折';
            }
        }
//        $data['goods_list'] = $goods_list;
//        $page['item_count'] = $goods_mod->getCount();
//        $this->_format_page($page);
//        $data['page'] = $page;
        return $goods_list;
    }
//获取参数
    function _get_query_param() {
        static $res = null;
        if ($res === null)
        {
            $res = array();
            // area

            if(isset($_GET['pd_id'])) {
                $res['pd_id'] = $_GET['pd_id'];
            }
            if(isset($_GET['brand_id'])) {
                $res['brand_id'] = $_GET['brand_id'];
            }
            if(isset($_GET['bdsh_class'])) {
                $res['bdsh_class'] = $_GET['bdsh_class'];
            }
            if(isset($_GET['bdsh_subcateid'])) {
                $res['bdsh_subcateid'] = $_GET['bdsh_subcateid'];
            }
            if(isset($_GET['bdsh_reg_id'])) {
                $res['bdsh_reg_id'] = $_GET['bdsh_reg_id'];
            }else{
                $res['bdsh_reg_id'] =321;
            }
            if(isset($_GET['bdsh_diqu_id'])) {
                $res['bdsh_diqu_id'] = $_GET['bdsh_diqu_id'];
            }
            if(isset($_GET['bdsh_class_name'])) {
                $res['bdsh_class_name'] = $_GET['bdsh_class_name'];
            }
            if(isset($_GET['bdsh_diqu_name'])) {
                $res['bdsh_diqu_name'] = $_GET['bdsh_diqu_name'];
            }
            if(isset($_GET['bdsh_class_sub_name'])) {
                $res['bdsh_class_sub_name'] = $_GET['bdsh_class_sub_name'];
            }
            if(isset($_GET['bdsh_city_id'])) {
                $res['bdsh_city_id'] = $_GET['bdsh_city_id'];
            }
            if(isset($_GET['bdsh_order_key'])) {
                $res['bdsh_order_key'] = $_GET['bdsh_order_key'];
            }
            if(isset($_GET['bdsh_order'])) {
                $res['bdsh_order'] = $_GET['bdsh_order'];
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
            if (isset($param['bdsh_reg_id']))
            {
                $region_mode = & m('region');
                $region = $region_mode->get_info($param['bdsh_reg_id']);
                $filters['bdsh_area'] = $region['region_name'];
            }
            if(isset($param['brand_id'])){
                $filters['brand_id'] = $param['brand_id'];
            }
            if(isset($param['bdsh_class'])) {
                $filters['bdsh_class'] = $param['bdsh_class'];
            }
            if(isset($param['bdsh_subcateid'])) {
                $filters['bdsh_subcateid'] = $param['bdsh_subcateid'];
            }
            if(isset($param['bdsh_reg_id'])) {
                $filters['bdsh_reg_id'] = $param['bdsh_reg_id'];
            }else {
                $filters['bdsh_reg_id'] = 321;
            }
            if(isset($param['bdsh_city_id'])) {
                $filters['bdsh_city_id'] = $param['bdsh_city_id'];
            }
            if(isset($param['bdsh_diqu_id'])) {
                $filters['bdsh_diqu_id'] = $param['bdsh_diqu_id'];
            }
            if(isset($param['bdsh_class_name'])) {
                $filters['bdsh_class_name'] = $param['bdsh_class_name'];
            }
            if(isset($param['bdsh_diqu_name'])) {
                $filters['bdsh_diqu_name'] = $param['bdsh_diqu_name'];
            }
            if(isset($param['bdsh_class_sub_name'])) {
                $filters['bdsh_class_sub_name'] = $param['bdsh_class_sub_name'];
            }
            if(isset($param['bdsh_order_key'])) {
                $filters['bdsh_order_key'] = $param['bdsh_order_key'];
            }
            if(isset($param['bdsh_order'])) {
                $filters['bdsh_order'] = $param['bdsh_order'];
            }
        }

        return $filters;
    }


//根据分类找商品
    function get_category_goods(){

        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        //设置title

        $gcategory_mod =& bm('gcategory');

        $gcategroys = null;
        //获取参数
        $param = $this->_get_query_param();
        //记住参数
        $filter = $this->_get_filter($param);


        $regin_mode = & m("region");
        if (!empty($param['bdsh_reg_id'])) {
            $city = $regin_mode->get_options($param['bdsh_reg_id']);
            $this->assign('city',$city);
        }
        if (!empty($param['bdsh_city_id'])) {
            $diqu = $regin_mode->get_options($param['bdsh_city_id']);
            $this->assign('diqu',$diqu);
        }


        //获取子分类
        $goods_mod = &m('goods');
        $conditions = "1=1 and g.if_show = 1 and g.closed =0 and g.dropState = 1";
        //根据省市获取当地省市的商家ID

        $region_mod = & m('region');
        if (!empty($param['bdsh_city_id'])) {
            $reiong_list =  $region_mod->get_options($param['bdsh_city_id']);
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
        }else if(!empty($param['bdsh_reg_id'])) {
            $reiong_list =  $region_mod->get_options($param['bdsh_reg_id']);
            $region_ids = "";
            if (count($reiong_list)>0) {
                foreach ($reiong_list as $key=>$reg) {
                    if($region_ids==""){
                        $region_ids=" s.region_id=".$key;
                    }else{
                        $region_ids .=" or s.region_id=".$key;
                    }
                }
                $conditions.=" and exists (select 1 from ecm_store s where s.store_id=g.store_id and ($region_ids))";
            }
        }
        if (!empty($param['bdsh_class'])) {
            $conditions.=" and cate_id_1= ".$param['bdsh_class'];
        }
        if (!empty($param['bdsh_subcateid'])) {
            $conditions.=" and cate_id_2= ".$param['bdsh_subcateid'];
        }
        $conditions.=" and g.pd_id =5";
        $_pdgcategory_mod = bm("pdcategory");
//        $cate_ids= $_pdgcategory_mod->_getCategoryByPdId(5);
        $data = null;
        $page   =   $this->_get_page(24);

        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions,
//            'scate_ids' => $cate_ids,
            'join' => 'has_goodsstatistics, belongs_to_store',
            'count' => true,
            'limit' => $page['limit']
        ));
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('goods_list',$goods_list);
        $this->assign('gcategory_list',$this->_list_gcategory(5));
        $cate_id = empty($param['bdsh_class']) ? 0 : intval($param['bdsh_class']);
        $this->assign('son_category',$this->get_son_category($param['bdsh_class']));
//        $cate_ids=array();
//        if ($cate_id > 0)
//        {
//            $scategory_mod =& m('scategory');
////            $cate_ids = $scategory_mod->get_descendant($cate_id);
//            $this->assign('son_category',$scategory_mod->get_list($cate_id));
//        }

        $this->assign('filters',$filter);
        $this->assign('page_info',$page);
        $this->_config_seo(array('title'=> $filter['bdsh_area'].Lang::get('bdsh')));
        $this->display("bdsh.local.html");

    }



    //获取子分类
    function get_son_category($cate_id) {
        $gcategory_mod = & m('gcategory');
        if ($cate_id!='') {
            $childrens =   $gcategory_mod->get_list($cate_id);
        }
        return $childrens;
    }
//    //品牌秀
//    function get_brand_goods() {
//        $region_mod =& m('region');
//        $tmp=$region_mod->get_options(1);
//        $param = $this->_get_query_param();
//        $filter =$this->_get_filter($param);
//        $brands = $this->get_brand();
//        $this->assign('brands',$brands);
//        $goods_mode = & m('goods');
//        $goods_list = null;
//        $brand_id = null;
//        if ($brands) {
//            foreach ($brands as $brand) {
//                if($brand_id==null) {
//                    $brand_id = $brand["brand_id"];
//                }else{
//                    $brand_id.=",".$brand["brand_id"];
//                }
//            }
//
//            if ($param['brand_id']) {
//                $conditions = "if_show = 1 AND closed = 0 and dropState=1 and brand_id=".$param['brand_id'];
//            } else {
//                $conditions = "if_show = 1 AND closed = 0 and dropState=1 and brand_id".db_create_in($brand_id);
//            }
//
//            $page   =   $this->_get_page(15);
//            $page['item_count'] = $goods_mode->getCount();
//            $goods_list = $goods_mode->find(array(
//                'conditions'=> $conditions,
//                'count' => true,
//                'limit' => $page['limit']
//            ));
//
//            $this->assign('filters',$filter);
//            $this->assign('region',$tmp);
//            $this->_format_page($page);
//            $this->assign('goods_list',$goods_list);
//        }
//        $this->display("bdsh.brand.html");
//
//    }

    //获取商品所属分类
    function get_store_category($store_id) {
        $scategory_mode = & m('scategory');
        $category_store = & m('categorystore');
        $region_mod = & m('region');
        $store_mode = & m('store');
        $sql = "SELECT cate_id,cate_name FROM {$scategory_mode->table} WHERE cate_id = (SELECT sc.cate_id FROM {$scategory_mode->table} AS sc,{$category_store->table} AS cs WHERE sc.cate_id=cs.cate_id AND cs.store_id = ".$store_id.") OR cate_id = (SELECT sc.parent_id FROM {$scategory_mode->table} AS sc, {$category_store->table} AS cs WHERE sc.cate_id=cs.cate_id AND cs.store_id =".$store_id.")";
        $scategory = $scategory_mode->db->getAll($sql);
        $sql_region = "SELECT region_id ,region_name FROM {$region_mod->table} WHERE  region_id = (SELECT parent_id FROM {$region_mod->table} WHERE region_id = (SELECT region_id FROM {$store_mode->table} WHERE store_id = ".$store_id." )) OR region_id = (SELECT region_id FROM {$store_mode->table} WHERE store_id = ".$store_id.")";
        $region = $region_mod->db->getAll($sql_region);
        $curlocal = array_merge($region,$scategory);
        return $curlocal;
    }

////获得品牌
//    function get_brand() {
//        $_pdgcategory_mod = bm("pdcategory");
//        $cate_ids = $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
//        $gcategory_mode = & bm('gcategory');
//        $category_brand_mode = & m('categorybrand');
//        $brand_mode = & m('brand');
//        $gcategoryids = null;
//        $ids = null;
//        if($cate_ids) {
//            foreach ($cate_ids as $cate_id) {
//                $ids[] = $gcategory_mode->get_descendant($cate_id);
//            }
//        }
//        if($ids) {
//            foreach ($ids as $id){
//                foreach ($id as $child){
//                    if($gcategoryids=="") {
//                        $gcategoryids=$child;
//                    } else {
//                        $gcategoryids.=",".$child;
//                    }
//                }
//            }
//        }
//        $sql ="SELECT distinct b.brand_id,b.brand_logo,b.brand_name from {$category_brand_mode->table} as gb,{$brand_mode->table} as b where b.brand_id=gb.brand_id and b.dropState=1 and b.if_show =1 and gb.category_id ".db_create_in($gcategoryids);
//        $brands = $category_brand_mode->db->getAll($sql);
//        return $brands;
//    }

    //本地生活商家详情
    function list_detail() {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $store_mode = & m('store');
        $store = $store_mode->get_info($id);
//        $this->pr($store);exit;
        $arr_tag = array();
        if ($store['tag'] ){
            if(strpos($store['tag'],'，')) {
                $new_tag = str_replace('，',',',$store['tag']);
            }
            if(!strpos($new_tag,'，')){
                $new_tag .=',';
            }
            $tags =explode(',',$new_tag) ;
            foreach ($tags as $t) {
                array_push($arr_tag,$t);
            }
        }
        $this->assign('tag',$arr_tag);
        $this->assign('store', $store);
        $goods_mod = & m('goods');
        $goods_list = $goods_mod->get_list(array(
                'conditions'=> 's.store_id ='.$id.' and g.pd_id=5 and g.if_show = 1 and g.closed=0 and g.dropState = 1',
                'order' => 'g.goods_id DESC',
            )
        );
        $param = $this->_get_query_param();
        $filter =  $this->_get_filter($param);
        $this->assign('goods_list',$goods_list);
        $this->assign('filters',$filter);
        //面包屑
        $this->assign('curlStr',$this->get_store_category($id));
        //看过这家店还看过
        $this->assign('store_see',$this->get_store_see($id));
        //附近商家
        $this->assign('store_fujin_canting',$this->get_store_fujin(147,$id));
        $this->assign('store_fujin_yule',$this->get_store_fujin(117,$id));
        $this->assign('store_fujin_gouwu',$this->get_store_fujin(234,$id));
        //商家评论
        $data = $this->get_store_comment($id);
        $this->assign('page_info',$data['page']);
        $this->assign('store_comment',$data['store_comments']);

        $images = $this->get_store_image($id);
        $this->assign('store_image',$images);
        $this->assign('big_image',current($images));
        $this->_config_seo(array('title'=>$store['store_name']."-".$filter['bdsh_area'].Lang::get('bdsh')));

        $this->display('bdsh.sj_x.html');
    }

    //商家相册
    function get_store_image($store_id) {
        $store_uploadfile_mod = &m('storeuploadedfile');
        $store_images = $store_uploadfile_mod->find(array(
            'conditions'=> 'store_id = '.$store_id.' and dropState =1',
            'order' => 'if_default desc'
        ));
        return $store_images;
    }

    //商家评论
    function get_store_comment($id) {
        $data = null;
        $page   =   $this->_get_page(10);
        $store_comments_mod = &m('storecomments');
        $store_comments= $store_comments_mod->find(array(
            'conditions'=> 'store_id = '.$id,
            'order' => 'time_comment DESC',
            'count' => true,
            'limit' => $page['limit']
        ));
        $member_mode = & m('member');
        if ($store_comments) {
            foreach($store_comments as $key=>$sc) {
                $member = $member_mode->get($sc['user_id']);
                $store_comments[$key]['user_name'] = $member['user_name'];
            }
        }
        $page['item_count'] = $store_comments_mod->getCount();
        $this->_format_page($page);
        $data['page'] = $page;
        $data['store_comments'] = $store_comments;
        return $data;
    }
    /*看过这家店还看过*/
    function get_store_see($id) {
        $stores = array();
        $scategory_mod =& m('scategory');
        $category_store = & m('categorystore');
        $store_uploadedfile_mode = & m('storeuploadedfile');
        $store_mode = & m('store');
        $sql = "SELECT store_id FROM {$category_store->table} WHERE cate_id = (select sc.cate_id from {$scategory_mod->table} as sc,{$category_store->table} as cs where sc.shitidian_ticheng >0 and cs.store_id =".$id." and cs.cate_id = sc.cate_id)";
        $store_ids = $category_store->db->getCol($sql);
        if (!empty($store_ids)) {
            foreach($store_ids as $store_id){
                $store = $store_mode->get_info($store_id);
                if($store['seller_type'] == 3 and $store['state']== 1) {
                $stores[$store_id] = $store;
            }
        }
        if (!empty($stores)) {
            foreach ($stores as $key=>$store) {
                    $s =  $store_uploadedfile_mode->get_store_img($key);
                    $stores[$key]['shitidian_img'] = $s[0];
                }
            }
        }
        return $stores;
    }

    /**
     * 附近商家
     */
    function get_store_fujin($cate_id,$id) {
        $cate_ids = $this->_scategory_mod->get_descendant($cate_id);
        $cate_id= "";
        if (count($cate_ids)>0) {
            foreach ($cate_ids as $c) {
                if ($cate_id=="") {
                    $cate_id = $c;
                } else {
                    $cate_id.=",".$c;
                }
            }
        }
        $stores = $this->ajax_store_by_cate($cate_id,$id);
        return $stores;
    }

    //根据分类获取商家
    function ajax_store_by_cate($cate_id,$id) {

        $sql = "SELECT s.* FROM {$this->_category_store_mod->table} AS cs ,{$this->_store_mod->table} AS s ,{$this->_scategory_mod->table} AS sc
                    WHERE sc.cate_id=cs.cate_id AND cs.store_id=s.store_id  AND s.region_id = (SELECT region_id FROM {$this->_store_mod->table} WHERE s.store_id = ".$id.") and sc.cate_id in (".$cate_id.")";
        $stores = $this->_store_mod->db->getAll($sql);
        return $stores;

    }

    //对商家评论
    function comment() {
        $store_comment =& m('storecomments');
        if (!$this->visitor->has_login)
        {
            $this->show_warning('guest_comment_disabled');
            return null;
        }
        $content = (isset($_GET['content'])) ? trim($_GET['content']) : '';
        //$type = (isset($_POST['type'])) ? trim($_POST['type']) : '';
//        $hide_name = $this->visitor->get('user_name');
        if (empty($content))
        {
            $this->show_warning('content_not_null');
            return;
        }
        //对验证码和邮件进行判断
        $bdsh_area = $_GET['bdsh_area'];
        $bdsh_reg_id = $_GET['bdsh_reg_id'];
        $user_id = $this->visitor->get('user_id');
        $data = array(
            'comments' => $content,
            'store_id' => $_GET['store_id'],
            'store_name' => addslashes($_GET['store_name']),
            'user_id' => $user_id,
            'title'=>$_GET['title'],
            'time_comment' => gmtime()
        );
        if($store_comment->add($data)){
            echo "0";
        }else {
            echo "-1";
        }

    }
    //商品详细
    function goods_detail() {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $param = $this->_get_query_param();
        $filter =  $this->_get_filter($param);
        $this->assign('filters',$filter);
        $goods = $this->_goods_mod->get_info($id);
        if ($goods['if_show']!=1||$goods['dropState']!=1||$goods['closed']!=0) {
            $this->show_warning('商品未上架或不存在！');
            return;
        }
        $this->assign('curlStr',$this->get_curl_category($goods['store_id'],$id));
        $this->assign('goods_list',$this->goods_by_store($goods['store_id']));
        //商家信息
        $store=$this->_store_mod->get($goods['store_id']);
        $dates = 0;
        if($goods['start_time']){
            $dates =round(($goods['end_time']-$goods['start_time'])/3600/24) ;
        }
        $this->assign('dates',$dates);
        $this->assign('store',$store);
        //看过此商品还看过
        $this->assign('goods_see',$this->get_goods_by_cateid($goods['cate_id']));
        $this->_config_seo('title', $goods['goods_name']);
        $this->assign("goods",$goods);
        $this->display("bdsh.goods.html");
    }

    //商品分类
    function get_curl_category($store_id,$goods_id){
        $gcategory_mode = & m('gcategory');
//        $category_store = & m('categorystore');
        $goods_mode = & m('goods');
        $region_mod = & m('region');
        $store_mode = & m('store');
        $goods = $goods_mode->get_info($goods_id);
        $gcategory = $this->_get_curlocal($goods['cate_id']);
        $sql_region = "SELECT region_id ,region_name FROM {$region_mod->table} WHERE  region_id = (SELECT parent_id FROM {$region_mod->table} WHERE region_id = (SELECT region_id FROM {$store_mode->table} WHERE store_id = ".$store_id." )) OR region_id = (SELECT region_id FROM {$store_mode->table} WHERE store_id = ".$store_id.")";
        $region = $region_mod->db->getAll($sql_region);
        $curlocal = array_merge($region,$gcategory);
        return $curlocal;
    }


    function _get_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        foreach ($parents as $category)
        {
            $curlocal[] = array('cate_id' => $category['cate_id'], 'cate_name' => $category['cate_name']);
        }

        return $curlocal;
    }
    //此商家商品
    function goods_by_store($store_id) {
        $goods_list =$this->_goods_mod->get_list(array(
                'conditions' => 's.store_id ='.$store_id." and g.pd_id = 5",
                'order' => 'g.goods_id DESC'
            )
        ) ;
        return $goods_list;
    }

    //看过此商品还看过
    function get_goods_by_cateid($cate_id) {
        $goods_list =$this->_goods_mod->get_list(array(
                'conditions' => 'g.cate_id ='.$cate_id.' and g.dropState=1 and g.if_show=1  and  g.pd_id =5',
            )
        ) ;
        return $goods_list;
    }
    //商家列表
    function store()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());
        $param = $this->_get_query_param();
        $filters = $this->_get_filter($param);
        $scategory_mod =& m('scategory');
        $cate_id = empty($param['bdsh_class']) ? 0 : intval($param['bdsh_class']);
        /* 取得该分类及子分类cate_id */
        if  (!empty($param['bdsh_subcateid'])) {
            $condition_id = ' AND cate_id = '.$param['bdsh_subcateid'];
        } else {
            $cate_ids=array();
            $condition_id='';
            if ($cate_id > 0)
            {
                $cate_ids = $scategory_mod->get_descendant($cate_id);
            }
            /* 店铺分类检索条件 */
            $condition_id=implode(',',$cate_ids);
            $condition_id && $condition_id = ' AND cate_id IN(' . $condition_id . ')';
        }
        if($cate_id>0) {
            $this->assign('son_category',$scategory_mod->get_list($cate_id));
        }
        $this->assign('filters',$filters);
        $regin_mode = & m("region");
        if (!empty($_GET['bdsh_reg_id'])) {
            $city = $regin_mode->get_options($_GET['bdsh_reg_id']);
            $this->assign('diqu',$city);
        }


        /* 其他检索条件 */
//        if (!empty($param['bdsh_class'])) {
//            $conditions.=" and cate_id= ".$param['bdsh_class'];
//        }
        $conditions ="1=1";
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
                $conditions.=" and ($region_ids)";
            }
        }
        $conditions .= ' and seller_type = 3 and state = 1';
        $model_store =& m('store');
        $page   =   $this->_get_page(12);   //获取分页信息
        $stores = $model_store->find(array(
            'conditions'  => 's.dropState=1 ' . $condition_id . $conditions,
            'limit'   => $page['limit'],
            'order'   => empty($_GET['order']) || !in_array($_GET['order'], array('credit_value desc')) ? 'sort_order DESC' : $_GET['order'],
            'join'    => 'belongs_to_user,has_scategory',
            'count'   => true   //允许统计
        ));
        $model_goods = &m('goods');
//        $this->pr($stores);exit;
        foreach ($stores as $key => $store)
        {
            //店铺logo

            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $new_tag = $store['tag'];
            if ($store['tag'] ){
                if(strpos($store['tag'],'，')) {
                    $new_tag = str_replace('，',',',$store['tag']);
                }
                if(!strpos($new_tag,'，')){
                    $new_tag .=',';
                }
                $tags =explode(',',$new_tag) ;
                $arr_tag = array();
                foreach ($tags as $t) {
                    array_push($arr_tag,$t);
                }
                $stores[$key]['tag'] =$arr_tag;
            }
            $uploadedimage = $this->get_store_image($store['store_id']);
            $image = current($uploadedimage);
            $stores[$key]['image'] =  $image['file_path'];
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
//        $this->_curlocal($this->_get_store_curlocal($cate_id));
        $scategorys = $this->_list_scategory();
//        $this->pr($stores);exit;
        $this->assign('stores', $stores);
        $this->assign('cate_id', $cate_id);
        $this->assign('scategorys', $scategorys);
        $this->assign('renqi_store',$this->renqi_stores());
        $this->assign('page_info', $page);
        /* 配置seo信息 */
        $this->_config_seo(array('title'=> $filters['bdsh_area'].Lang::get('bdsh')));
        $this->display('search.store.html');
    }



    /* 取得店铺分类 */
    function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $sql = "SELECT * FROM {$scategory_mod->table}  WHERE dropState=1 and cate_id IN ( SELECT parent_id FROM {$scategory_mod->table} WHERE shitidian_ticheng > 0) AND parent_id = 0";
//        $sql = "SELECT * FROM {$scategory_mod->table}  WHERE parent_id = 0 and dropState=1 ";
        $scategories = $scategory_mod->db->getAll($sql);

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }
    //人气商家
    function renqi_stores() {
        $store_mode = & m('store');
        $sql = "select  * from {$store_mode->table} where dropState=1 and state=1 and seller_type =3 limit 1,5";
        $store_uploadedfile_mode = & m('storeuploadedfile');
        $data=$store_mode->db->getAll($sql);
        if(count($data)>0) {
            foreach ($data as $key=>$store) {
                $s =  $store_uploadedfile_mode->get_store_img($store['store_id']);
                $data[$key]['shitidian_img'] = $s[0];
            }
        }
        return $data;
    }
    //全部商品
    function ajax_get_all_goods() {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $store_mode = & m('store');
        $store = $store_mode->get_info($id);
        $arr_tag = array();
        if ($store['tag']) {
            if (strpos($store['tag'],'，')) {
                $new_tag = $store['tag'];
                $tags =explode('，',$new_tag) ;
                foreach ($tags as $t) {
                    array_push($arr_tag,$t);
                }
            }
        }
        $this->assign('tag',$arr_tag);
        $this->assign('store', $store);
        $goods_mod = & m('goods');
        $goods_list = $goods_mod->get_list(array(
                'conditions'=> 's.store_id ='.$id.' and g.pd_id=5 and g.dropState=1 and g.if_show=1',
                'order' => 'g.goods_id DESC',
            )
        );
        //商家相册
        $images = $this->get_store_image($id);
        $this->assign('store_image',$images);
        $this->assign('big_image',current($images));
        $param = $this->_get_query_param();
        $filter =  $this->_get_filter($param);
        $this->assign('goods_list',$goods_list);
        $this->assign('filters',$filter);
        //面包屑
        $this->assign('curlStr',$this->get_store_category($id));
        //看过这家店还看过
        $this->assign('store_see',$this->get_store_see($id));
        //附近商家
        $this->assign('store_fujin_canting',$this->get_store_fujin(147,$id));
        $this->assign('store_fujin_yule',$this->get_store_fujin(117,$id));
        $this->assign('store_fujin_gouwu',$this->get_store_fujin(234,$id));
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $goods_mod = & m('goods');
        $page   =   $this->_get_page(15);
        $goods_list = $goods_mod->get_list(array(
                'conditions'=> 's.store_id ='.$id.' and g.pd_id=5 and g.dropState=1 and g.if_show=1',
                'order' => 'g.goods_id DESC',
                'count' => true,
                'limit'=>$page['limit']
            )
        );
        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info',$page);
        $this->assign('goods_list',$goods_list);
        $this->display("bdsh.sj_x_goods.html");
    }
}

?>
