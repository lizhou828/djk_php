<?php

/* 商品 */
class GoodsApp extends StorebaseApp
{
    var $_goods_mod;
    var $_kfs;
    function __construct()
    {
        $this->GoodsApp();

    }
    function GoodsApp()
    {
        parent::__construct();
        include_once(ROOT_PATH . '/app/readMemcache.app.php');
        $this->_goods_mod =& m('goods');
        $kf_data[5594] = array("user_name"=>"djkkf001","nick_name"=>"丹顶鹤");
        $kf_data[5595] = array("user_name"=>"djkkf002","nick_name"=>"鹦鹉");
        $kf_data[5596] = array("user_name"=>"djkkf003","nick_name"=>"信鸽");
        $kf_data[5597] = array("user_name"=>"djkkf004","nick_name"=>"信天翁");
        $kf_data[5598] = array("user_name"=>"djkkf005","nick_name"=>"白鹭");
        $this->kfs = $kf_data;
    }

    function index()
    {

        /* 参数 id */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        $shop_id = empty($_GET['shop_id']) ? null : intval($_GET['shop_id']);
        $this->assign("shop_id",$shop_id);
        $member_mod  = &m("member");
        $this->assign('user', $member_mod->get_info($this->visitor->get('user_id')));
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        if ($_GET['readonly'] =="true") {
            $this->assign("readonly","true");
        }
        /* 可缓存数据 */
        $member_mode = &m('member');
        $data = $this->_get_common_info($id);
        $store = $data['store_data'];
        if($store['region_name']!=null) {
            $store['region_name'] = str_replace('中国','',$store['region_name']);
        }
        if ($store['fwzx']!=null) {

            $user = $member_mode->get($store['fwzx']);
            if ($user['member_type']=='04') {
                $this->assign('hide',"hide");
            }
        }
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 更新浏览次数 */
        $this->_update_views($id);

        //是否开启验证码
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }
        $this->assign('yunfei',$this->_get_yunfei($id));

        $gcategorys = $this->_list_gcategory(1);
        $this->assign('gcategorys_left', $gcategorys);

        $this->assign('showfenxiang',"showfenxiang");

        if ($store['tuoguan']==1 && $store['fwzx']) {
            $this->assign('is_tuoguan','false');
        }
        $this->assign('chat',$this->_get_chat($data['goods']['store_id']));
        //商品评论
        $comments = $this->comments();
        $this->assign('page_info',$comments['page_info']);
        $this->assign('goods_comments',$comments);
        //销售记录
        $this->assign('sales_list',$this->saleslog());
        $this->assign('qa_info',$this->qa());
        $this->assign('guest_comment_enable', Conf::get('guest_comment'));
        $this->assign('pd_id',$_GET['pd_id']);
        $this->assign('index',1);
//        $this->assign('type',$_GET['type']);
//        $this->assign('ret_url', urlencode(SITE_URL."index.php?app=goods&id=".$_GET['id']));
        $this->display('goods.index.html');
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

    //获得左侧菜单
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
                    $arr=$gcategory_mod->get_list($cate_id,$shown=true);
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
                            $soncategory = $gcategory_mod->get_list($v["cate_id"],$shown=true);
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

    /* 运费 */
    function  _get_yunfei($id) {
        $yunfei = &m("yunfei");
        $goods = $this->_goods_mod->get_info($id);
        if(!$goods) {
            $regions=-2;
        }
        $region_mode = &m('region');
        if(isset($goods["shipping_id"])) {
            $sql = "SELECT * FROM {$region_mode->table} reg WHERE reg.parent_id =1 AND EXISTS(SELECT 1 FROM {$yunfei->table} yf WHERE yf.diqu = reg.region_name AND yf.shipping_id=".$goods["shipping_id"].")
                UNION SELECT * FROM {$region_mode->table} reg1 WHERE reg1.parent_id =1 AND  EXISTS(SELECT 1 FROM {$region_mode->table} reg WHERE reg.parent_id =reg1.region_id AND EXISTS(SELECT 1 FROM {$yunfei->table} yf  WHERE yf.diqu = reg.region_name and yf.shipping_id=".$goods["shipping_id"]."))";
            $regions= $yunfei->db->getAll($sql);
        }
        if(!$regions[0]) {
            $regions = -1;
        }

        return $regions;
    }
//获取市级地区
    function get_diqu() {
        header('Content-type: text/json');
        $regid = empty($_POST['region_id']) ? 0 :$_POST['region_id'];
        $shipping_id = empty($_POST['shipping_id']) ? 0 :$_POST['shipping_id'];
        $reg_model = & m('region');
        $sql = "SELECT yf.* FROM ecm_region AS reg ,ecm_yunfei AS yf  WHERE reg.parent_id = ".$regid."  AND yf.shipping_id = ".$shipping_id." AND yf.diqu = reg.region_name";
        $model_yunfei = &m("yunfei");
        $reg = $reg_model -> get($regid);
        $yunfei= $model_yunfei->db->getAll($sql);
        if(count($yunfei) < 2){
            foreach($yunfei as $yf){
                if($yf['region_name'] == $reg['region_name']);
                $yunfei = '';
            }
        }
        echo json_encode($yunfei);
    }

//选中的运费
    function  get_ajax_yunfei() {
        header('Content-type: text/json');
        $reg_name = empty($_GET['reg_name']) ? 0 :$_GET['reg_name'];
        $shipping_id = empty($_GET['shipping_id']) ? 0 :$_GET['shipping_id'];
        $condition = " diqu ='".$reg_name."' AND shipping_id =".$shipping_id;
        $model_yunfei = &m("yunfei");
        $yunfei= $model_yunfei->get(array('conditions'=>$condition));
        echo json_encode($yunfei);
    }

    /* 商品评论 */
    function comments()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 赋值商品评论 */
        $data = $this->_get_goods_comment($id, 10);
//        $this->_assign_goods_comment($data);
        return $data;
    }

    /* 销售记录 */
    function saleslog()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 赋值销售记录 */
        $data = $this->_get_sales_log($id, 10);
        return $data;
//        $this->_assign_sales_log($data);
//        $this->assign('yunfei',$this->_get_yunfei($id));
//        $this->display('goods.saleslog.html');
    }
    /*商品咨询*/
    function qa()
    {
        $goods_qa =& m('goodsqa');
        $id = intval($_GET['id']);
        $type = $_GET['type'];
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        if(!IS_POST)
        {
            $data = $this->_get_common_info($id);
            if ($data === false)
            {
                return;
            }
            else
            {
                $this->_assign_common_info($data);
            }
            $data = $this->_get_goods_qa($id, 10);
//            $this->_assign_goods_qa($data);
            if (!empty($type)) {
                $qa_info = $data['qa_info'];
                $jsonData = ecm_json_encode($qa_info);
                echo $jsonData;
            } else {
                return $data;
            }

            //是否开启验证码
            if (Conf::get('captcha_status.goodsqa'))
            {
                $this->assign('captcha', 1);
            }
//            $this->assign('yunfei',$this->_get_yunfei($id));
//            $this->assign('guest_comment_enable', Conf::get('guest_comment'));
//            /*赋值产品咨询*/
//            $this->display('goods.qa.html');
        }
        else
        {
            /* 不允许游客评论 */
            if (!Conf::get('guest_comment') && !$this->visitor->has_login)
            {
                $this->show_warning('guest_comment_disabled');
                return null;
            }
            $content = (isset($_POST['content'])) ? trim($_POST['content']) : '';
            //$type = (isset($_POST['type'])) ? trim($_POST['type']) : '';
            $email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
            $hide_name = (isset($_POST['hide_name'])) ? trim($_POST['hide_name']) : '';
            if (empty($content))
            {
                $this->show_warning('content_not_null');
                return;
            }
            //对验证码和邮件进行判断

            if (Conf::get('captcha_status.goodsqa'))
            {
                if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
                {
                    $this->show_warning('captcha_failed');
                    return;
                }
            }
            if (!empty($email) && !is_email($email))
            {
                $this->show_warning('email_not_correct');
                return;
            }
            $user_id = empty($hide_name) ? $_SESSION['user_info']['user_id'] : 0;
            $conditions = 'g.goods_id ='.$id;
            $goods_mod = & m('goods');
            $ids = $goods_mod->get(array(
                'fields' => 'store_id,goods_name',
                'conditions' => $conditions
            ));
            extract($ids);
            $data = array(
                'question_content' => $content,
                'type' => 'goods',
                'item_id' => $id,
                'item_name' => addslashes($goods_name),
                'store_id' => $store_id,
                'email' => $email,
                'user_id' => $user_id,
                'time_post' => gmtime(),
            );
            if ($goods_qa->add($data))
            {
                header("Location: index.php?app=goods&id={$id}");
                exit;
            }
            else
            {
                $this->show_warning('post_fail');
                exit;
            }
        }
    }

    /**
     * 取得公共信息
     *
     * @param   int     $id
     * @return  false   失败
     *          array   成功
     */
    function _get_common_info($id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_goods_' . $id;
        $data = $cache_server->get($key);
        $cached = true;


        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);

            /* 商品信息 */
            $goods = $this->_goods_mod->get_info($id);
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] == 0)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();

            $data['goods'] = $goods;

            /* 店铺信息 */
            if (!$goods['store_id'])
            {
                $this->show_warning('store of goods is empty');
                return false;
            }
            $this->set_store($goods['store_id']);

            $data['store_data'] = $this->get_store_data();

            /* 当前位置 */
            $data['cur_local'] = $this->_get_curlocal($goods['cate_id']);
            $data['goods']['related_info'] = $this->_get_related_objects($data['goods']['tags']);
            /* 分享链接 */
            $data['share'] = $this->_get_share($goods);

            $cache_server->set($key, $data, 1800);
        }
        if ($cached)
        {
            $this->set_store($data['goods']['store_id']);
        }

        return $data;
    }

    function _get_related_objects($tags)
    {
        if (empty($tags))
        {
            return array();
        }
        $tag = $tags[array_rand($tags)];
        $ms =& ms();

        return $ms->tag_get($tag);
    }

    /* 赋值公共信息 */
    function _assign_common_info($data)
    {
        /* 商品信息 */
        $goods = $data['goods'];
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        //默认由商家聊天
        $data_kf [$data['store_data']["store_id"]] = array("user_name"=>$data['store_data']["store_name"],"nick_name"=>$data['store_data']['store_owner']['nick_name']);;
        $receive_data = $data_kf;
        $member_mod =& m('member');
        //是否开启了由采购管理商品聊天
        if(IF_KEFU ==1){
            $receive_data = $this->kfs;
        }else{
            //服务中心自己聊天
            if($data['store_data']["fwzx"]>0 && $data['store_data']["tuoguan"] == 1){
                $fwzx = $data['store_data']["fwzx"];
                $tmp_user = $member_mod->get("user_id=".$fwzx);
                if($tmp_user!=null){
                    if($tmp_user["member_type"] =="04"){
                        $receive_data = $this->kfs;
                    }else{
                        $tmp_user_name = $tmp_user["user_name"];
                        if (substr(strtoupper($tmp_user_name),0,9) == "DJKHANGUO") {
                            //韩国馆的客服
                            //$receive_data = $member_mod->find("member_type in ('02','03') and dropState=1 and status=2 and user_name like 'djkhanguo%' order by member_type limit 0,10");
                            $receive_data = array(5316=>array("user_name"=>"djkhanguo","nick_name"=>"韩国馆(한국관)","djk"=>1));


                        }else{
                            //普通服务中心的客服
                            $receive_data = $member_mod->find("member_type in ('02','03') and dropState=1 and status=2 and region_id=".$tmp_user["region_id"]." order by member_type limit 0,10");
                        }
                    }

                }
            }
        }

        //$this->pr($receive_data);exit;

        $this->assign('receive_data', $receive_data);

        /* 店铺信息 */
        $this->assign('store', $data['store_data']);

        /* 浏览历史 */
        $this->assign('goods_history', $this->_get_goods_history($data['id']));

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* 当前位置 */
        $this->_curlocal($data['cur_local']);

        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info($data['goods']));

        /* 商品分享 */
        $this->assign('share', $data['share']);

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }

    /* 取得浏览历史 */
    function _get_goods_history($id, $num = 9)
    {
        $goods_list = array();
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows = $this->_goods_mod->find(array(
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

    /* 取得销售记录 */
    function _get_sales_log($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $sales_list = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND status = '" . ORDER_FINISHED . "'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, add_time, anonymous, goods_id, specification, price, quantity, evaluation',
            'count' => true,
            'order' => 'add_time desc',
            'limit' => $page['limit'],
        ));
        $data['sales_list'] = $sales_list;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_sales'] = $page['item_count'] > $num_per_page;

        return $data;
    }

    /* 赋值销售记录 */
    function _assign_sales_log($data)
    {
        $this->assign('sales_list', $data['sales_list']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('more_sales', $data['more_sales']);
    }

    /* 取得商品评论 */
    function _get_goods_comment($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page(10);
        $order_goods_mod =& m('ordergoods');
        $comments = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND evaluation_status = '1'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, anonymous, order_alias.evaluation_time, comment, evaluation',
            'count' => true,
            'order' => 'order_goods.evaluation_time desc',
            'limit' => $page['limit'],
        ));
//        $readMemcacheApp = new readMemcacheApp();
//        $rcomments = $readMemcacheApp->get_comment($goods_id);
//
//        $data['comments'] = array_merge($comments,$rcomments);
        $data['comments'] = $comments;
        $page['item_count'] = count($data['comments']);
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_comments'] = $page['item_count'] > $num_per_page;
        return $data;
    }

    /* 赋值商品评论 */
    function _assign_goods_comment($data)
    {
        $this->assign('goods_comments', $data['comments']);
        $this->assign('page_info',      $data['page_info']);
        $this->assign('more_comments',  $data['more_comments']);
    }

    /* 取得商品咨询 */
    function _get_goods_qa($goods_id,$num_per_page)
    {
        $page = $this->_get_page($num_per_page);
        $goods_qa = & m('goodsqa');
        $qa_info = $goods_qa->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post,time_reply',
            'conditions' => '1 = 1 AND item_id = '.$goods_id . " AND type = 'goods'",
            'limit' => $page['limit'],
            'order' =>'time_post desc',
            'count' => true
        ));
        $page['item_count'] = $goods_qa->getCount();
        $this->_format_page($page);

        //如果登陆，则查出email
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

    /* 赋值商品咨询 */
    function _assign_goods_qa($data)
    {
        $this->assign('email',      $data['email']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('qa_info',    $data['qa_info']);
    }

    /* 更新浏览次数 */
    function _update_views($id)
    {
        $goodsstat_mod =& m('goodsstatistics');
        $goodsstat_mod->edit($id, "views = views + 1");
    }

    /**
     * 取得当前位置
     *
     * @param int $cate_id 分类id
     */
    function _get_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => url('app=category')),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => url('app=search&cate_id=' . $category['cate_id']));
        }
        $curlocal[] = array('text' => LANG::get('goods_detail'));

        return $curlocal;
    }

    function _get_share($goods)
    {
        $m_share = &af('share');
        $shares = $m_share->getAll();
        $shares = array_msort($shares, array('sort_order' => SORT_ASC));
        $goods_name = ecm_iconv(CHARSET, 'utf-8', $goods['goods_name']);
        $goods_url = urlencode(SITE_URL . '/' . str_replace('&amp;', '&', url('app=goods&id=' . $goods['goods_id'])));
        $site_title = ecm_iconv(CHARSET, 'utf-8', Conf::get('site_title'));
        $share_title = urlencode($goods_name . '-' . $site_title);
        foreach ($shares as $share_id => $share)
        {
            $shares[$share_id]['link'] = str_replace(
                array('{$link}', '{$title}'),
                array($goods_url, $share_title),
                $share['link']);
        }
        return $shares;
    }

    function _get_seo_info($data)
    {
        $seo_info = $keywords = array();
        $seo_info['title'] = $data['goods_name'] . ' - ' . Conf::get('site_title');
        $keywords = array(
            $data['brand'],
            $data['goods_name'],
            $data['cate_name']
        );
        $seo_info['keywords'] = implode(',', array_merge($keywords, $data['tags']));
        $seo_info['description'] = sub_str(strip_tags($data['description']), 10, true);
        return $seo_info;
    }

    //保存远程图片
    function sava_goods_img($goods_id = ''){

        $goodsId=($_GET["id"])?$_GET["id"]:$_POST["id"];

        if($goodsId==0 || $goodsId==""){$goodsId = $goods_id;}

        if($goodsId==0 || $goodsId==""){exit;}
        $data=$this->_goods_mod->get(array(
            'conditions' => "goods_id =$goodsId
                                             and checkState=0"
        ));
        if($data == null || $data["goods_id"]==0 || $data["goods_id"]==""){
            echo "已经修改完毕！"."<br>";
        }else{
            $contentStr=$data["description"];

            include_once ROOT_PATH."/includes/upload.class.php";
            $upload = new upload();

            $arrayimageurl = $temp = $imagereplace = array();
            $string = stripslashes($contentStr);
            $downremotefile = true;
            preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $string, $temp, PREG_SET_ORDER);

            $status=1;
            if(is_array($temp) && !empty($temp)) {
                foreach($temp as $tempvalue) {
                    $tempvalue[2] = str_replace('\"', '', $tempvalue[2]);
                    if(strlen($tempvalue[2])){
                        $arrayimageurl[] = $tempvalue[2];
                    }
                }

                $arrayimageurl = array_unique($arrayimageurl);

                if($arrayimageurl) {
                    foreach($arrayimageurl as $tempvalue) {
                        $imageurl = $tempvalue;
                        $imagereplace['oldimageurl'][] = $imageurl;
                        $attach['ext'] = $upload->fileext($imageurl);

                        $tp=explode("http://",$imageurl);
                        if(count($tp)>1)
                        {

                            $checkStr=count(explode(IMG_URL,$imageurl));
                            if($checkStr>1){              //内网的图片直接跳过
                                continue;
                            }


                            if(@fopen($imageurl,'r'))
                            {
                                $filename=$upload->GrabImage($tempvalue,"");
                                if($filename!="")
                                {
                                    $tempvaluesize = $this->getfilesize($imageurl);
                                    $filenamesize = $this->getfilesize(IMG_URL."/".$filename);
                                    echo "imageurl:".$imageurl.'<br>';
                                    echo 'tempvaluesize:'.$tempvaluesize . '----------' . 'filenamesize:'.$filenamesize.'<br>';
                                    //判断原图片是否和保存后图片大小一致
                                    if($tempvaluesize == $filenamesize){
                                        $contentStr=str_replace($tempvalue,IMG_URL."/".$filename,$contentStr);
                                        echo "$tempvalue"."----------------".IMG_URL."/".$filename."<br><br><br>";
                                    }else{
                                        echo "图片错误或者原图片和抓取下来保存后图片大小不一致"."<br><br><br>";
                                    }
                                }
                            }else{
                                //有部分图片可能暂时不存在,不修改状态，下次继续修改
                                $status=0;
                            }
                        }else
                        {
                            continue;
                        }
                    }


                }

            }

            $updateData=array("description"=>addslashes($contentStr),"checkState"=>$status);
            $this->_goods_mod->edit($goodsId,$updateData);
            echo "修改成功"."<br>";
        }
    }

    //获取远程文件大小
    function remote_filesize($uri,$user='',$pw='')
    {
        // start output buffering
        ob_start();
        // initialize curl with given uri
        $ch = curl_init($uri);
        // make sure we get the header
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // make it a http HEAD request
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        // if auth is needed, do it here
        if (!empty($user) && !empty($pw))
        {
            $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $okay = curl_exec($ch);
        curl_close($ch);
        // get the output buffer
        $head = ob_get_contents();
        // clean the output buffer and return to previous
        // buffer settings
        ob_end_clean();

        // gets you the numeric value from the Content-Length
        // field in the http header
        $regex = '/Content-Length:\s([0-9].+?)\s/';
        $count = preg_match($regex, $head, $matches);

        // if there was a Content-Length field, its value
        // will now be in $matches[1]
//        if (isset($matches[1]))
//        {
        $size = $matches[1];
//        }
//        else
//        {
//            $size = 'unknown';
//        }
        //$last=round($size/(1024*1024),3);
        //return $last.' MB';
        return $size;
    }

    function getfilesize($url){
        return strlen(file_get_contents($url));
    }


    function replace_goods_index(){
        $goods_mod = & m('goods');
        $sql = "select count(*) from {$goods_mod->table}";
        $count = $goods_mod -> db -> getCol($sql);
        $this -> assign('count',$count[0]);
        $this -> display('goods.replace.html');
    }

    function replace_goods_desImg(){
        $strat = abs(floatval($_POST['start']));
        $end = abs(floatval($_POST['end']));
        $goods_mod = & m('goods');
        $sql = "select * from  {$goods_mod->table} order by goods_id limit ";
        $sql .= $strat . ',' . $end;
        $goods = $goods_mod->getAll($sql);
        if(count($goods) > 0){
            foreach($goods as $good){
                echo $good['goods_id'].'-----'.$good['goods_name'].'<br>';
                $this->sava_goods_img($good['goods_id']);
            }
        }
    }

    function test(){
        echo strlen(file_get_contents("http://tbphoto2.bababian.com/upload5/zdfs/10012-10050/10036-01.jpg")). '<br>';
        echo $this->remote_filesize("http://tbimgdx023.bababian.com/upload5/zdfs/10012-10050/10036-09.jpg");
    }

    //保存远程图片
    function getNum(){

        $j=0;
        $str="";
        $data=$this->_goods_mod->find(array(
            'conditions' => " (
                             `description_tmp` LIKE  '%2013/0907_down%'
                                OR  `description_tmp` LIKE  '%2013/0908_down%'
                                OR  `description_tmp` LIKE  '%2013/0909_down%'
                                OR  `description_tmp` LIKE  '%2013/0910_down%'
                                OR  `description_tmp` LIKE  '%2013/0911_down%'
                                OR  `description_tmp` LIKE  '%2013/0912_down%'
                                OR  `description_tmp` LIKE  '%2013/0913_down%'
                                OR  `description_tmp` LIKE  '%2013/0914_down%'
                             )
                                AND dropState =1
                               "
        ));

        if(count($data)<=0){echo "没有数据！";exit;}

        foreach($data as $k=>$val){
            $contentStr=$data[$k]["description_tmp"];

            include_once ROOT_PATH."/includes/upload.class.php";
            $upload = new upload();

            $arrayimageurl = $temp = $imagereplace = array();
            $string = stripslashes($contentStr);
            $downremotefile = true;
            preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $string, $temp, PREG_SET_ORDER);

            $i=0;
            $arr_tmp=null;
            foreach($temp as $k1=>$v){
                $arr_tmp[$i]=$v[2];
                $i++;
            }
            $arr_tmp1=array_unique($arr_tmp);
            if(count($arr_tmp)!=count($arr_tmp1)){
                $j+=1;
                if($str==""){
                    $str=$k;
                }else{
                    $str.=",".$k;
                }
            }
        }

        echo $j;
        echo "<br><br>".$str;

    }
}

?>
