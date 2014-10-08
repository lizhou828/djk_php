<?php

class GroupbuyApp extends MallbaseApp
{

    var $_groupbuy_mod;
    var $_goods_mod;
    var $_visitor;

    function __construct()
    {
        $this->GroupbuyApp();
    }

    function GroupbuyApp()
    {
        $this->_groupbuy_mod = &m('groupbuy');
        $this->_goods_mod = &m('goods');

        parent::__construct();
        $this->_visitor = $this->visitor->info;
    }

    function index()
    {

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_groupbuy');
            return false;
        }
        $group = $this->_groupbuy_mod->get(array(
            'conditions' => 'group_id=' . $id . ' AND gb.state<>' . GROUP_PENDING,
            'join' => 'belong_store,belong_goods',
            'fields' => 'gb.*,s.owner_name,g.goods_id,g.price,default_spec'
        ));

        if (empty($group))
        {
            $this->show_warning('no_such_groupbuy');
            return;
        }
        $group['spec_price'] = unserialize($group['spec_price']);
        $group['groupbuy_price'] = $group['spec_price'][$group['default_spec']]['price'];

        $group['discount'] =sprintf("%.1f",($group['groupbuy_price']/$group['price'])*10);
        $group['jiesheng'] = $group['price']- $group['groupbuy_price'] ;
        $group['lefttime'] =lefttime($group['end_time']);
        $quantity = $this->_groupbuy_mod->get_join_quantity($group['group_id']);
        $group['quantity'] = empty($group['quantity']) ? 0 : $quantity['quantity'];
//        $this->print_r($group);
        $goods = $this->_query_goods_info($group['goods_id']);
        empty( $goods['_images'][0]['image_url']) &&  $goods['_images'][0]['image_url'] = Conf::get('default_goods_image');


        $model_store = & m('store');
        $store = $model_store->get('store_id='.$goods['store_id']);
//        $this->print_r($goods);
//        exit;
        $this->assign("goods",$goods);
        $this->assign("store",$store);
        $this->assign("group",$group);
        $data['views'] = $group['views'] + 1; // 浏览数

//
        $this->assign('recommended_goods', $this->_get_recommended_goods($goods["store_id"]));
        $this->assign('recommended_groupbuy', $this->_recommended_groupbuy(2));
        $this->assign('last_join_groupbuy', $this->_last_join_groupbuy(2));
        $this->display('groupbuy.index.html');

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

    // 本期热门团购
    function _recommended_groupbuy($_num)
    {
        $model_groupbuy =& m('groupbuy');
        $data = $model_groupbuy->find(array(
            'join'          => 'belong_goods',
            'conditions'    => ' gb.state=' . GROUP_ON . ' AND gb.end_time>' . gmtime(),
            'fields'        => 'group_id,goods.price,goods.default_image, group_name, end_time, spec_price',
            'order'         => 'group_id DESC',
            'limit'         => $_num,
        ));
        foreach ($data as $gb_id => $gb_info)
        {
            $price = current(unserialize($gb_info['spec_price']));
            empty($gb_info['default_image']) && $data[$gb_id]['default_image'] = Conf::get('default_goods_image');
            $data[$gb_id]['lefttime']   = lefttime($gb_info['end_time']);
            $data[$gb_id]['price']      = $price['price'];
            $data[$gb_id]['quantity'] = $model_groupbuy->get_join_quantity($gb_info['group_id']);
            $data[$gb_id]['goods_price'] = $gb_info['price'];
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
    //获取家装集商品
     function get_pindao_goods() {
         $this->_config_seo('title', Lang::get('tuangou'));
         $_pdgcategory_mod = & m('pdcategory');
         $goods_list = null;

         if ($_GET["pd_id"]) {
           $cate_ids = $_pdgcategory_mod->_getCategoryByPdId($_GET["pd_id"]);
           $sql = "SELECT gd.*  FROM {$this->_groupbuy_mod->table} gb,{$this->_goods_mod->table} gd WHERE gb.goods_id = gd.goods_id AND
                 gd.cate_id_1 ".db_create_in($cate_ids)." AND
                 gb.state=1 AND gd.if_show=1 AND gd.closed=0 AND gd.dropState=1";
         }else {
             $sql = "SELECT gd.*  FROM {$this->_groupbuy_mod->table} gb,{$this->_goods_mod->table} gd
                       WHERE gb.goods_id = gd.goods_id AND gb.if_dingzhi =2 AND gd.if_show=1 AND gd.closed=0 AND gd.dropState=1";
         }
         $goods_list = $this->_goods_mod->db->getAll($sql);
         $this->assign('groupbuy_list',$goods_list);
         $gcategory_list =null;
         $param = $this->_get_query_param();
         $filters = $this->_get_filter($param);
         $gcategory_model = & bm("gcategory");
         if ($cate_ids) {
             foreach ($cate_ids as $cate_id)
             {
                 $gcategory_list[] =$gcategory_model->get($cate_id);
             }
         } else {
             foreach ($goods_list as $goods) {
                 $gcategory_list[] = $gcategory_model->get($goods['cate_id_1']);
             }
         }

         $region_mod =& m('region');
         $tmp=$region_mod->get_options(1);
         $this->assign('filters',$filters);
         $this->assign('region',$tmp);
         $this->assign('gcategory_list',$gcategory_list);
         $this->display('search.groupbuy.html');
     }

    //根据地区获取店铺ID
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
        if (!$param['groupbuy_area']) {
            $stroe_ids= array_keys($data);
        } else {
            foreach ($region_name as $key => $str) {
                if ($param["groupbuy_area"] == $str) {
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

    function _get_query_param()
    {
        static $res = null;
        if ($res === null) {
            $res = array();
            // area
            $groupbuy_area = isset($_GET['groupbuy_area']) ? trim($_GET['groupbuy_area']) : '';
            $res['groupbuy_area'] = $groupbuy_area;
            if (isset($_GET['groupbuy_brand'])) {
                $res['brand_id'] = $_GET['groupbuy_brand'];
            }
            if (isset($_GET['groupbuy_class'])) {
                $res['groupbuy_class'] = $_GET['groupbuy_class'];
            }
            if (isset($_GET['groupbuy_subcateid'])) {
                $res['groupbuy_subcateid'] = $_GET['groupbuy_subcateid'];
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
    
    function _get_filter($param)
    {
        static $filters = null;
        if ($filters === null) {
            $filters = array();
            if (isset($param['groupbuy_area'])) {
                $filters['groupbuy_area'] = $param['groupbuy_area'];
            }
            if (isset($param['brand_id'])) {
                $filters['brand_id'] = $param['brand_id'];
            }
            if (isset($param['groupbuy_class'])) {
                $filters['groupbuy_class'] = $param['groupbuy_class'];
            }
            if (isset($param['groupbuy_subcateid'])) {
                $filters['groupbuy_subcateid'] = $param['groupbuy_subcateid'];
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

    function _query_goods_info($goods_id)
    {
        $goods = $this->_goods_mod->get_info($goods_id);
        if ($goods['spec_qty'] ==1 || $goods['spec_qty'] ==2)
        {
            $goods['spec_name'] = htmlspecialchars($goods['spec_name_1'] . ($goods['spec_name_2'] ? ' ' . $goods['spec_name_2'] : ''));
        }
        else
        {
            $goods['spec_name'] = Lang::get('spec');
        }
        foreach ($goods['_specs'] as $key => $spec)
        {
            if ($goods['spec_qty'] ==1 || $goods['spec_qty'] ==2)
            {
                $goods['_specs'][$key]['spec'] = htmlspecialchars($spec['spec_1'] . ($spec['spec_2'] ? ' ' . $spec['spec_2'] : ''));
            }
            else
            {
                $goods['_specs'][$key]['spec'] = Lang::get('default_spec');
            }
        }
        $goods['default_image'] || $goods['default_image'] = Conf::get('default_goods_image');
        return $goods;
    }

    function _get_state_desc($state, $end_time)
    {
        $lefttime = lefttime($end_time);
        $desc = array(
            GROUP_ON    =>  Lang::get('desc_on') . ' ' . $lefttime,
            GROUP_END   =>  Lang::get('desc_end'),
            GROUP_FINISHED  => Lang::get('desc_finished'),
            GROUP_CANCELED  => Lang::get('desc_cancel'),
        );
        return $desc[$state];
    }

    function _ican($id, $state, $store_id, $act = '')
    {
        $state_permission = array(
            GROUP_PENDING   => array(),
            GROUP_ON        => array(),
            GROUP_END       => array(),
            GROUP_FINISHED  => array(),
            GROUP_CANCELED  => array()
        );
        $member_mod = &m('member');

        if ($this->_visitor['user_id'] > 0) //已登陆用户
        {
            // 是否已经参加
            $join = current($member_mod->getRelatedData('join_groupbuy', $this->_visitor['user_id'], array(
                    'conditions' => 'gb.group_id=' . $id,
                    'order' => 'gb.group_id DESC',
                    'fields' => 'gb.state'
            )));
            if ($join)
            {
                $state_permission[GROUP_ON] = array('ask', 'exit' ,'join_info'); // 咨询,退出团购,参团信息
                $state_permission[GROUP_CANCELED] = array('join_info');
                $state_permission[GROUP_FINISHED] = array('join_info', 'buy');
                $state_permission[GROUP_END] = array('join_info');
            }
            else
            {
                $state_permission[GROUP_ON] = array('ask', 'join');
            }

            if ($store_id == $this->_visitor['user_id']) // 浏览者为团购发起者
            {
                $state_permission[GROUP_ON] = array('ask');
            }
        }
        else // 游客
        {
            $state_permission[GROUP_ON] = array('ask', 'join', 'login'); // login提示需要登陆才能参加
        }

        if (empty($act))
        {
            $actions = array();
            foreach ($state_permission[$state] as $action)
            {
                $actions[$action] = true;
            }
            return $actions; // 返回该团购此状态时允许的操作
        }
        return in_array($act, $state_permission[$state]) ? true : false; // 该团购此状态是否允许执行此操作
    }

    function _import_resource()
    {
        if(in_array(ACT, array('view')))
        {
            $resource['script'][] = array( // 验证
                'path' => 'jquery.plugins/jquery.validate.js'
            );
        }
        $this->import_resource($resource);
    }

    // 取团购咨询
    function _get_groupbuy_qa($id)
    {
        $page = $this->_get_page(10);
        $groupbuy_qa = & m('goodsqa');
        $qa_info = $groupbuy_qa->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post,time_reply',
            'conditions' => '1 = 1 AND item_id = '.$id . " AND type = 'groupbuy'",
            'limit' => $page['limit'],
            'order' =>'time_post desc',
            'count' => true
        ));
        $page['item_count'] = $groupbuy_qa->getCount();
        $this->_format_page($page);
        if (!empty($_SESSION['user_info']))
        {
            $user_mod = & m('member');
            $user_info = $user_mod->get(array(
                'fields' => 'email',
                'conditions' => '1=1 AND user_id = '.$_SESSION['user_info']['user_id']
            ));
            extract($user_info);
        }
        return array(
            'email' => $email,
            'page_info' => $page,
            'qa_info' => $qa_info,
        );
    }
}

?>
