<?php

class StoreApp extends StorebaseApp
{
    var $_kfs;
    var $_member_mod;
    function __construct(){
        $this->StoreApp();
    }
    function StoreApp(){
        parent::__construct();
        $kf_data[5594] = array("user_name"=>"djkkf001","nick_name"=>"丹顶鹤");
        $kf_data[5595] = array("user_name"=>"djkkf002","nick_name"=>"鹦鹉");
        $kf_data[5596] = array("user_name"=>"djkkf003","nick_name"=>"信鸽");
        $kf_data[5597] = array("user_name"=>"djkkf004","nick_name"=>"信天翁");
        $kf_data[5598] = array("user_name"=>"djkkf005","nick_name"=>"白鹭");
        $this->kfs = $kf_data;
        $this->_member_mod =& m('member');
    }

    function index()
    {
        /* 店铺信息 */
        $_GET['act'] = 'index';
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);


        /* 取得友情链接 */
        $this->assign('partners', $this->_get_partners($id));

        /* 取得推荐商品 */
        $this->assign('recommended_goods', $this->_get_recommended_goods($id));
//        $this->assign('new_groupbuy', $this->_get_new_groupbuy($id));

        /* 取得最新商品 */
        $this->assign('new_goods', $this->_get_new_goods($id));

        /*获取店铺商品*/
        $data = $this->_get_good_list($id);
        $this->assign('store_goods',$data['goods_list']);
        $this->assign("page_info",$data['page']);

        $this->assign('chat',$this->_get_chat($id));
        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store', $store['store_name']);

        $this->_config_seo('title', $store['store_name'] . ' - ' . Conf::get('site_title'));
        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info($store));

        $gcategorys = $this->_list_gcategory(1);
        $this->assign('gcategorys_left', $gcategorys);


        $store_user = $this->_member_mod->get_info($id);
        //默认由商家聊天
        $data_kf [$store["store_id"]] = array("user_name"=>$store["store_name"],"nick_name"=>$store_user['nick_name']);;
        $receive_data = $data_kf;
        //是否开启了由采购管理商品聊天
        if(IF_KEFU ==1){
            $receive_data = $this->kfs;
        }else{
            //服务中心自己聊天
            if($store["fwzx"]>0 && $store["tuoguan"] == 1){
                $fwzx = $store["fwzx"];
                $tmp_user = $this->_member_mod->get("user_id=".$fwzx);
                if($tmp_user!=null){
                    if($tmp_user["member_type"] =="04"){
                        $receive_data = $this->kfs;
                    }else{
                        $tmp_user_name = $tmp_user["user_name"];
                        if (substr(strtoupper($tmp_user_name),0,9) == "DJKHANGUO") {
                            //韩国馆的客服
                            //$receive_data = $this->_member_mod->find("member_type in ('02','03') and dropState=1 and status=2 and user_name like 'djkhanguo%' order by member_type limit 0,10");
                            $receive_data = array(5316=>array("user_name"=>"djkhanguo","nick_name"=>"韩国馆(한국관)","djk"=>1));
                        }else{
                            //普通服务中心的客服
                            $receive_data = $this->_member_mod->find("member_type in ('02','03') and dropState=1 and status=2 and region_id=".$tmp_user["region_id"]." order by member_type limit 0,10");
                        }
                    }

                }
            }
        }
        $this->assign('receive_data', $receive_data);

        $this->display('store.index.html');
    }

    function search()
    {
        /* 店铺信息 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 搜索到的商品 */
        $this->_assign_searched_goods($id);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('goods_list')
        );

        $this->_config_seo('title', Lang::get('goods_list') . ' - ' . $store['store_name']);
        $this->display('store.search.html');
    }

    //获取聊天的ＱＱ号码
    function _get_chat($store_id){
        $service_info_mode = & m('serviceInfo');
        $store_mode = & m('store');
        $store = $store_mode->get($store_id);
        $hoama = null;
//        $service_info = $service_info_mode->get($store_id);
        if ($store['im_qq']) {
            $hoama[0] = $store['im_qq'];
        }elseif ($store['fwzx']!=null&&$store['tuoguan']!=null) {
            $sql = "select QQKF from {$service_info_mode->table} where service_id =".$store['fwzx']." and type=1";
            $res  =   $service_info_mode->db->getCol($sql);

            if(strpos($res[0],'#')) {
                $str = explode('#',$res[0]);
                foreach ($str as $key=>$val) {
                    $hoama[$key] = $val;
                }
            } else {
                $hoama = $res;
            }
        }  else {
            $hoama = null;
        }
        return $hoama;
    }

    function groupbuy()
    {
        /* 店铺信息 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 搜索团购 */
        empty($_GET['state']) &&  $_GET['state'] = 'on';
        $conditions = '1=1';
        if ($_GET['state'] == 'on')
        {
            $conditions .= ' AND gb.state ='. GROUP_ON .' AND gb.end_time>' . gmtime();
            $search_name = array(
                array(
                    'text'  => Lang::get('group_on')
                ),
                array(
                    'text'  => Lang::get('all_groupbuy'),
                    'url'  => url('app=store&act=groupbuy&state=all&id=' . $id)
                ),
            );
        }
        else if ($_GET['state'] == 'all')
        {
            $conditions .= ' AND gb.state '. db_create_in(array(GROUP_ON,GROUP_END,GROUP_FINISHED));
            $search_name = array(
                array(
                    'text'  => Lang::get('all_groupbuy')
                ),
                array(
                    'text'  => Lang::get('group_on'),
                    'url'  => url('app=store&act=groupbuy&state=on&id=' . $id)
                ),
            );
        }

        $page = $this->_get_page(16);
        $groupbuy_mod = &m('groupbuy');
        $groupbuy_list = $groupbuy_mod->find(array(
            'fields'    => 'goods.default_image, gb.group_name, gb.group_id, gb.spec_price, gb.end_time, gb.state',
            'join'      => 'belong_goods',
            'conditions'=> $conditions . ' AND gb.store_id=' . $id ,
            'order'     => 'group_id DESC',
            'limit'     => $page['limit'],
            'count'     => true
        ));
        $page['item_count'] = $groupbuy_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        if (empty($groupbuy_list))
        {
            $groupbuy_list = array();
        }
        foreach ($groupbuy_list as $key => $_g)
        {
            empty($groupbuy_list[$key]['default_image']) && $groupbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
            $tmp = current(unserialize($_g['spec_price']));
            $groupbuy_list[$key]['price'] = $tmp['price'];
            if ($_g['end_time'] < gmtime())
            {
                $groupbuy_list[$key]['group_state'] = group_state($_g['state']);
            }
            else
            {
                $groupbuy_list[$key]['lefttime'] = lefttime($_g['end_time']);
            }
        }
        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('groupbuy_list')
        );

        $this->assign('groupbuy_list', $groupbuy_list);
        $this->assign('search_name', $search_name);
        $this->_config_seo('title', $search_name[0]['text'] . ' - ' . $store['store_name']);
        $this->display('store.groupbuy.html');
    }

    function article()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        /* 店铺信息 */
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id']
        );

        $this->_config_seo('title', $store['store_name']);
        $this->display('store.article.html');
    }

    /* 信用评价 */
    function credit()
    {
        /* 店铺信息 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        /* 取得评价过的商品 */
        if (!empty($_GET['eval']) && in_array($_GET['eval'], array(1,2,3)))
        {
            $conditions = "AND evaluation = '{$_GET['eval']}'";
        }
        else
        {
            $conditions = "";
            $_GET['eval'] = '';
        }
        $page = $this->_get_page(6);
        $order_goods_mod =& m('ordergoods');
        $goods_list = $order_goods_mod->find(array(
            'conditions' => "seller_id = '$id' AND evaluation_status = 1 AND is_valid = 1 " . $conditions,
            'join'       => 'belongs_to_order',
            'fields'     => 'buyer_id, buyer_name, anonymous, evaluation_time, goods_id, goods_name, specification, price, quantity, goods_image, evaluation, comment',
            'order'      => 'evaluation_time desc',
            'limit'      => $page['limit'],
            'count'      => true,
        ));
        $this->assign('goods_list', $goods_list);

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        /* 按时间统计 */
        $stats = array();
        for ($i = 0; $i <= 3; $i++)
        {
            $stats[$i]['in_a_week']        = 0;
            $stats[$i]['in_a_month']       = 0;
            $stats[$i]['in_six_month']     = 0;
            $stats[$i]['six_month_before'] = 0;
            $stats[$i]['total']            = 0;
        }

        $goods_list = $order_goods_mod->find(array(
            'conditions' => "seller_id = '$id' AND evaluation_status = 1 AND is_valid = 1 ",
            'join'       => 'belongs_to_order',
            'fields'     => 'evaluation_time, evaluation',
        ));
        foreach ($goods_list as $goods)
        {
            $eval = $goods['evaluation'];
            $stats[$eval]['total']++;
            $stats[0]['total']++;

            $days = (gmtime() - $goods['evaluation_time']) / (24 * 3600);
            if ($days <= 7)
            {
                $stats[$eval]['in_a_week']++;
                $stats[0]['in_a_week']++;
            }
            if ($days <= 30)
            {
                $stats[$eval]['in_a_month']++;
                $stats[0]['in_a_month']++;
            }
            if ($days <= 180)
            {
                $stats[$eval]['in_six_month']++;
                $stats[0]['in_six_month']++;
            }
            if ($days > 180)
            {
                $stats[$eval]['six_month_before']++;
                $stats[0]['six_month_before']++;
            }
        }
        $this->assign('stats', $stats);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('credit_evaluation')
        );

        $this->_config_seo('title', Lang::get('credit_evaluation') . ' - ' . $store['store_name']);
        $this->display('store.credit.html');
    }

    /* 取得友情链接 */
    function _get_partners($id)
    {
        $partner_mod =& m('partner');
        return $partner_mod->find(array(
            'conditions' => "store_id = '$id'",
            'order' => 'sort_order',
        ));
    }

    /* 取得推荐商品 */
    function _get_recommended_goods($id, $num = 8)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $goods_list = $goods_mod->find(array(
            'conditions' => "closed = 0 AND if_show = 1 AND recommended = 1 and dropState=1",
            'fields'     => 'goods_name, default_image, price',
            'limit'      => $num,
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }

        return $goods_list;
    }

    function _get_new_groupbuy($id, $num = 12)
    {
        $model_groupbuy =& m('groupbuy');
        $groupbuy_list = $model_groupbuy->find(array(
            'fields'    => 'goods.default_image, this.group_name, this.group_id, this.spec_price, this.end_time',
            'join'      => 'belong_goods',
            'conditions'=> $model_groupbuy->getRealFields('this.state=' . GROUP_ON . ' AND this.store_id=' . $id . ' AND end_time>'. gmtime()),
            'order'     => 'group_id DESC',
            'limit'     => $num
        ));
        if (empty($groupbuy_list))
        {
            $groupbuy_list = array();
        }
        foreach ($groupbuy_list as $key => $_g)
        {
            empty($groupbuy_list[$key]['default_image']) && $groupbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
            $tmp = current(unserialize($_g['spec_price']));
            $groupbuy_list[$key]['price'] = $tmp['price'];
            $groupbuy_list[$key]['lefttime'] = lefttime($_g['end_time']);
        }

        return $groupbuy_list;
    }

    /* 取得最新商品 */
    function _get_new_goods($id, $num = 8)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $goods_list = $goods_mod->find(array(
            'conditions' => "closed = 0 AND if_show = 1 and dropState=1",
            'fields'     => 'goods_name, default_image, price',
            'order'      => 'add_time desc',
            'limit'      => $num,
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }

        return $goods_list;
    }

    /* 搜索到的结果 */
    function _assign_searched_goods($id)
    {
        $cate_goods_mode = & m('categorygoods');
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $search_name = LANG::get('all_goods');

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'name'  => 'keyword',
                'equal' => 'like',
            ),
        ));
        if ($conditions)
        {
            $search_name = sprintf(LANG::get('goods_include'), $_GET['keyword']);
            $sgcate_id   = 0;
        }
        else
        {
            $sgcate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        }

        if ($sgcate_id > 0)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => $id));
            $sgcate = $gcategory_mod->get_info($sgcate_id);
            $search_name = $sgcate['cate_name'];

            $sgcate_ids = $gcategory_mod->get_descendant_ids($sgcate_id);
        }
        else
        {
            $sgcate_ids = array();
        }

        /* 排序方式 */
        $orders = array(
            'add_time desc' => LANG::get('add_time_desc'),
            'price asc' => LANG::get('price_asc'),
            'price desc' => LANG::get('price_desc'),
        );
        $this->assign('orders', $orders);
//        if ()
        $page = $this->_get_page(12);
        if ($_GET['cate_id']) {
            $sql = "SELECT g.* FROM {$cate_goods_mode->table} cg,{$goods_mod->table} g WHERE g.goods_id = cg.goods_id AND cg.cate_id=".$_GET['cate_id']." AND g.dropState=1 AND g.if_show=1 AND g.closed=0";
            $goods_list = $goods_mod->getAll($sql);
        } else {
            $goods_list = $goods_mod->get_list(array(
                'conditions' => 'closed = 0 AND if_show = 1 and g.dropstate=1 ' . $conditions,
                'count' => true,
                'order' => empty($_GET['order']) || !isset($orders[$_GET['order']]) ? 'add_time desc' : $_GET['order'],
                'limit' => $page['limit'],
            ), $sgcate_ids);
        }
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }
        $this->assign('searched_goods', $goods_list);

        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        //$this->print_r($page);
        $this->assign('search_name', $search_name);
    }

    /**
     * 取得文章信息
     */
    function _get_article($id)
    {
        $article_mod =& m('article');
        return $article_mod->get_info($id);
    }

    function _get_seo_info($data)
    {
        $seo_info = $keywords = array();
        $seo_info['title'] = $data['store_name'] . ' - ' . Conf::get('site_title');
        $keywords = array(
            str_replace("\t", ' ', $data['region_name']),
            $data['store_name'],
        );
        //$seo_info['keywords'] = implode(',', array_merge($keywords, $data['tags']));
        $seo_info['keywords'] = implode(',', $keywords);
        $seo_info['description'] = sub_str(strip_tags($data['description']), 10, true);
        return $seo_info;
    }

    /* 取得商品分类 */
    function _list_gcategory($pid)
    {
        $cache_server =& cache_server();
        $key = 'page_goods_category';
        $data = $cache_server->get($key);

//        if ($data === false) {
        $gcategory_mod =& bm('gcategory', array('_store_id' => 0));

        $_pdgcategory_mod = bm("pdcategory");
        $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($pid);
        $gcategories = array();
        if ($cate_ids) {
            foreach ($cate_ids as $cate_id) {
                if ($gcategory_mod->get($cate_id)) {
                    $gcategories[] = $gcategory_mod->get($cate_id);
                    $arr=$gcategory_mod->get_list($cate_id);
                    foreach($arr as $k=>$v)
                    {
                        $gcategories[]=array(
                            'cate_id'=>$v["cate_id"],
                            'store_id'=>$v["store_id"],
                            'cate_name'=>$v["cate_name"],
                            'parent_id'=>$v["parent_id"],
                            'sort_order'=>$v["sort_order"],
                            'if_show'=>$v["if_show"],
                        );
                        if ($gcategory_mod->get_list($v["cate_id"])) {
                            $soncategory = $gcategory_mod->get_list($v["cate_id"]);
                            foreach ($soncategory as $val) {
                                $gcategories[]=array(
                                    'cate_id'=>$val["cate_id"],
                                    'store_id'=>$val["store_id"],
                                    'cate_name'=>$val["cate_name"],
                                    'parent_id'=>$val["parent_id"],
                                    'sort_order'=>$val["sort_order"],
                                    'if_show'=>$val["if_show"],
                                );
                            }
                        }
                    }
                }
            }
        }
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        $data = $tree->getArrayList(0);
        $cache_server->set($key, $data, 3600);
//        }
        return $data;
    }

    function _get_good_list($store_id){
        $goods_mod = &m('goods');
        $data = null;
        $page   =   $this->_get_page(30);
        $conditions = "if_show = 1 and dropState =1 and store_id = ".$store_id;
        $goods_list = $goods_mod->find(array(
            'conditions' => $conditions,
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
}

?>
