<?php

/**
 *    卖家预售管理控制器
 *
 *    @author    Hyber
 *    @usage    none
 */
class Seller_temaiApp extends StoreadminbaseApp
{
    var $_store_id;
    var $_goods_mod;
    var $_store_mod;
    var $_yushou_mod;
    var $_last_update_id;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_temaiApp();
    }

    function Seller_temaiApp()
    {
        parent::__construct();

        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_goods_mod =& m('goods');
        $this->_store_mod =& m('store');
        $this->_yushou_mod =& m('yushou');
    }

    function index()
    {
        /* 取得列表数据 */
//        $conditions = $this->_get_query_conditions(array(
//            array(      //按团购状态搜索
//                'field' => 'state',
//                'name'  => 'state',
//                'handler' => 'yushou_state_translator',
//            ),
//            array(      //按团购名称搜索
//                'field' => 'group_name',
//                'name'  => 'group_name',
//                'equal' => 'LIKE',
//            ),
//        ));
//        // 标识有没有过滤条件
//        if ($conditions)
//        {
//            $this->assign('filtered', 1);
//        }
//
        $page   =   $this->_get_page(10);    //获取分页信息
//
//        $filter="";
//        if($_GET["add_time_from"])
//        {
//            $add_time_from=strtotime($_GET["add_time_from"]);
//            $filter .= " AND gb.start_time>='$add_time_from'";
//        }
//        if($_GET["add_time_to"])
//        {
//            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
//            $filter .= " AND gb.end_time<='$add_time_to'";
//        }
//
//        $store_id="";
//        if($_GET["app"]=="service"){
//
//              if($_GET["store_id"]!="-1" && $_GET["store_id"]!="")
//              {
//                  $store_id.= $_GET["store_id"];
//              }else{
//                  $user = $this->visitor->get();
//                  $fwzxId=$user['user_id'];
//                  $userInfo=$this->_member_mod->get($fwzxId);
//                  //只获取该服务中心下的
//                  $conditions2="";
//                  if(!empty($userInfo['region_id']))
//                  {
//                      $conditions2.=" and region_id ='".$userInfo['region_id']."'";
//                  }
//                  $store_id.="(SELECT store_id FROM ".DB_PREFIX."store WHERE fwzx=$fwzxId AND tuoguan=1 AND dropState=1 $conditions2)";
//
//              }
//        }else{
//            $store_id = $this->_store_id;
//        }
        $store_id = $this->_store_id;
        $temai_list = $this->_yushou_mod->find(
            array(
                'conditions' => "ys.store_id in (".$store_id.") and type =2",
                'order' => 'ys.id DESC',
                'limit' => $page['limit'],  //获取当前页的数据
                'count' => true
            )
        );
//        $page['item_count'] = $this->_yushou_mod->getCount();   //获取统计的数据

//        if ($ids = array_keys($yushou_list))
//        {
//            $quantity = $this->_yushou_mod->get_join_quantity($ids);
//            $order_count = $this->_yushou_mod->get_order_count($ids);
//        }
        foreach ($temai_list as $key => $temai)
        {
            $goods = $this->_goods_mod->get($temai['goods_id']);
            $temai_list[$key]['goods_name'] = $goods['goods_name'];
            if(!empty($goods['default_image'])) {
                $temai_list[$key]['default_image'] =$goods['default_image'];
            } else {
                $temai_list[$key]['default_image'] = Conf::get('default_goods_image');
            }
        }
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('temai_manage'), 'index.php?app=seller_temai',
                         LANG::get('temai_list'));

        /* 当前用户中心菜单 */

//
//        if($_GET["app"]=="service"){
//            $this->_curitem($_GET["act"]);
//            $this->_curmenu('service');
//        }else{
            $this->_curitem('temai_manage');
            /* 当前所处子菜单 */
            $this->_curmenu('temai_list');
//        }


        $this->_format_page($page);
//        $this->_import_resource();
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('yushou_list', $temai_list);
//        $this->assign('state', array('all' => Lang::get('group_all'),
//             'pending' => Lang::get('group_pending'),
//             'on' => Lang::get('group_on'),
//             'end' => Lang::get('group_end'),
//             'finished' => Lang::get('group_finished'),
//             'canceled' => Lang::get('group_canceled'))
//        );
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('yushou_manage'));


        //$this->print_r($yushou_list);

        $member_mod =&m('member');
        $user = $member_mod->get_info($this->visitor->get('user_id'));
        $this->assign("user",$user);

        if($_GET["app"]=="service")
        {
            $this->display('service_temai.index.html');
        }else{
            $this->display('seller_temai.index.html');
        }

    }

    function add()
    {
        if (!IS_POST)
        {
            $goods_mod = &bm('goods', array('_store_id' => $this->_store_id));
            $goods_count = $goods_mod->get_count();

            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                LANG::get('temai_manage'), 'index.php?app=seller_temai',
                LANG::get('add_temai'));

            if ($goods_count == 0)
            {
                $this->show_message('has_no_goods',
                    'add_goods', 'index.php?app=seller_temai',
                    'continue_add', 'index.php?app=seller_temai&amp;act=add'
                );
                return;
            }

            /* 当前用户中心菜单 */
            if($_GET["app"]=="service"){
                $this->_curitem("yushou");
                $this->_curmenu('service');
            }else{
                $this->_curitem('temai_manage');
                /* 当前所处子菜单 */
                $this->_curmenu('add_temai');
            }





            $this->assign('group', array('max_per_user' => 0, 'end_time' => gmtime() + 7 * 24 * 3600));
            $this->assign('store_id', $this->_store_id);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('add_temai'));
            $this->_import_resource();


            $this->display('seller_temai.form.html');
        }
        else
        {
            /* 检查数据 */
            if (!$this->_handle_post_data($_POST, 0))
            {
                $this->show_warning($this->get_error());
                return;
            }
//            $yushou_info = $this->_yushou_mod->get($this->_last_update_id);
//            if ($yushou_info['state'] == GROUP_ON)
//            {
//                $_goods_info  = $this->_query_goods_info($yushou_info['goods_id']);
//                $yushou_url = SITE_URL . '/' . url('app=yushou&id=' . $yushou_info['group_id']);
//                $feed_images = array();
//                $feed_images[] = array(
//                    'url'   => SITE_URL . '/' . $_goods_info['default_image'],
//                    'link'   => $yushou_url,
//                );
//                $this->send_feed('yushou_created', array(
//                    'user_id' => $this->visitor->get('user_id'),
//                    'user_name' => $this->visitor->get('user_name'),
//                    'yushou_url' => $yushou_url,
//                    'yushou_name' => $yushou_info['group_name'],
//                    'message' => $yushou_info['group_desc'],
//                    'images' => $feed_images,
//                ));
//            }


                $this->show_message('新增特卖活动成功',
                    'back_list', 'index.php?app=seller_temai',
                    'continue_add', 'index.php?app=seller_temai&amp;act=add'
                );

        }
    }

    function edit()
    {

        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!$id)
        {
            $this->show_warning('no_such_temai');
            return false;
        }

        if (!IS_POST)
        {

            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('temai_manage'), 'index.php?app=seller_temai',
                             LANG::get('edit_temai'));

            $this->_curitem('temai_manage');


            /* 当前所处子菜单 */
            $this->_curmenu('edit_temai');

            /* 团购信息 */
            $yushou = $this->_yushou_mod->get($id);
            $yushou_spec = unserialize($yushou['yushou_spec']);
            $goods = $this->_goods_mod->get($yushou['goods_id']);
            $this->assign('yushou_spec', $yushou_spec);
            $this->assign('yushou', $yushou);
            $this->assign('goods', $goods);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('edit_temai'));
//            $this->_import_resource();


            $this->display('seller_temai.form.html');


        }
        else
        {
            /* 检查数据 */
            if (!$this->_handle_post_data($_POST, $id))
            {
                $this->show_warning($this->get_error());
                return;
            }
            if($_GET["app"]=="service"){
                $this->show_message('edit_temai_ok',
                    'back_list', 'index.php?app=service&act=tuangou',
                    'continue_edit', 'index.php?app=service&act=tuangou_edit&id=' . $id
                );
            }else{
                $this->show_message('edit_temai_ok',
                    'back_list', 'index.php?app=seller_temai',
                    'continue_edit', 'index.php?app=seller_temai&act=edit&id=' . $id
                );
            }

        }
    }

    function drop()
    {
        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!$id)
        {
            $this->show_warning('no_such_temai');
            return false;
        }
//        if (!$this->_ican($id, ACT))
//        {
//            $this->show_warning('Hacking Attempt');
//            return;
//        }
        if (!$this->_yushou_mod->get($id))
        {
            $this->show_warning($this->_yushou_mod->get_error());

            return;
        }
        $data = $this->_yushou_mod->get($id);
        $data['state'] = 2;
        $this->_yushou_mod->edit($id,$data);
        $this->show_message('drop_temai_successed');
    }





    /**
     * 检查提交的数据
     */
    function _handle_post_data($post, $id = 0)
    {
//        if ($post['if_publish'] == 1 || gmstr2time($post['start_time']) <= gmtime())
//        {
//            $post['start_time'] = gmtime(); //立即发布
//            $post['state'] = GROUP_ON;
//        }
//        else
//        {
//            $post['start_time'] = gmstr2time($post['start_time']);
//            $post['state'] = GROUP_PENDING;
//        }
//        if (intval($post['end_time']))
//        {
//            $post['end_time'] = gmstr2time_end($post['end_time']);
//        }
//        else
//        {
//            $this->_error('fill_end_time');
//            return false;
//        }
//        if ($post['end_time'] < $post['start_time'])
//        {
//            $this->_error('start_not_gt_end');
//            return false;
//        }


        if (($post['goods_id'] = intval($post['goods_id'])) == 0)
        {
            $this->_error('fill_goods');
            return false;
        }
//        if (empty($post['spec_id']) || !is_array($post['spec_id']))
//        {
//            $this->_error('fill_spec');
//            return false;
//        }
//        foreach ($post['spec_id'] as $key => $val)
//        {
//            if (empty($post['group_price'][$key]))
//            {
//                $this->_error('invalid_group_price');
//                return false;
//            }
//            $spec_price[$val] = array('price' => number_format($post['group_price'][$key], 2, '.', ''));
//        }

        $store_id=$this->_store_id;
        if($_POST["store_id"]){
            $store_id=$_POST["store_id"];
        }

        $yushou_spec = null;
        if($post['yushou_quitity']) {
            $yushou_quitity = $post['yushou_quitity'];
            foreach ($yushou_quitity as $key=>$qui) {
                if (!empty($post['yushou_price'][$key])&&!empty($qui))
                {
                    $yushou_spec[$key] = array("yushou_quitity"=>$qui,"yushou_price"=>$post['yushou_price'][$key]);
                }
            }
        }
        $data = array(
            'yushou_name' => $post['yushou_name'],
            'yushou_desc' => $post['yushou_desc'],
//            'start_time' => $post['start_time'],
//            'end_time'   => $post['end_time'] - 1,
            'goods_id'   => $post['goods_id'],
            'yushou_spec' => serialize($yushou_spec),
//            'min_quantity' => $post['min_quantity'],
//            'max_per_user' => $post['max_per_user'],
            'state'        => $post['state'],
            'type' =>   $post['type'],
            'store_id'     => $store_id
        );
        if ($id > 0)
        {
            $this->_yushou_mod->edit($id, $data);
            if ($this->_yushou_mod->has_error())
            {
                $this->_error($this->_yushou_mod->get_error());
                return false;
            }
        }
        else
        {

            if (!($id = $this->_yushou_mod->add($data)))
            {
                $this->_error($this->_yushou_mod->get_error());
                return false;
            }
        }
        $this->_last_update_id = $id;

        return true;
    }

    function query_goods_info()
    {
        $goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
        if ($goods_id)
        {
            $goods = $this->_query_goods_info($goods_id);
            $this->json_result($goods);
        }
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
    function query_goods()
    {
        $goods_mod = &bm('goods', array('_store_id' => $this->_store_id));

        /* 搜索条件 */
        $conditions = "1 = 1";
        if (trim($_GET['goods_name']))
        {
            $str = "LIKE '%" . trim($_GET['goods_name']) . "%'";
            $conditions .= " AND (goods_name {$str})";
        }

        if (intval($_GET['sgcate_id']) > 0)
        {
            $cate_mod =& bm('gcategory', array('_store_id' => $this->visitor->get('manage_store')));
            $cate_ids = $cate_mod->get_descendant(intval($_GET['sgcate_id']));
        }
        else
        {
            $cate_ids = 0;
        }

        /* 取得商品列表 */
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions . ' AND g.if_show=1 AND g.closed=0',
            'order' => 'g.add_time DESC',
            'limit' => 100,
        ), $cate_ids);

        foreach ($goods_list as $key => $val)
        {
            $goods_list[$key]['goods_name'] = htmlspecialchars($val['goods_name']);
        }
        $this->json_result($goods_list);
    }

    function _import_resource()
    {
        if(in_array(ACT, array('index' , 'add', 'edit')))
        {
            $resource['script'][] = array( // JQUERY UI
                'path' => 'jquery.ui/jquery.ui.js'
            );
        }
        if(in_array(ACT, array('index', 'add', 'edit')))
        {
            $resource['script'][] = array( // 对话框
                'attr' => 'id="dialog_js"',
                'path' => 'dialog/dialog.js'
            );
        }
        if(in_array(ACT, array('add', 'edit')))
        {
            $resource['script'][] = array( // 验证
                'path' => 'jquery.plugins/jquery.validate.js'
            );
        }
        if(in_array(ACT, array('add', 'edit'))) //日历相关
        {
            $resource['script'][] = array(
                'path' => 'jquery.ui/i18n/' . i18n_code() . '.js'
            );
            $resource['style'] .= 'jquery.ui/themes/ui-lightness/jquery.ui.css';
        }
        $this->import_resource($resource);
    }

    /**
     *    三级菜单
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'temai_list',
                'url'   => 'index.php?app=seller_temai',
            ),
        );
        if (ACT == 'add' || ACT == 'edit' || ACT == 'desc' || ACT == 'cancel')
        {
            $menus[] = array(
                'name' => ACT . '_temai',
                'url'  => '',
            );
        }
        return $menus;
    }

    function _ican($id, $act = '')
    {
        $state_permission = array(
            GROUP_PENDING   => array('start', 'edit', 'drop','tuangou_start', 'tuangou_edit', 'tuangou_drop'),
            GROUP_ON        => array('cancel', 'desc', 'log', 'finished', 'export_ubbcode','tuangou_cancel', 'tuangou_desc',
                                       'tuangou_log', 'tuangou_finished', 'tuangou_export_ubbcode'),
            GROUP_END       => array('cancel', 'desc', 'finished', 'log','tuangou_cancel', 'tuangou_desc', 'tuangou_finished', 'tuangou_log'),
            GROUP_FINISHED  => array('drop', 'log', 'view_order','tuangou_drop', 'tuangou_log', 'tuangou_view_order'),
            GROUP_CANCELED  => array('drop', 'log','tuangou_drop', 'tuangou_log')
        );

        $store_id=$this->_store_id;

        if($_GET["app"]=="service")
        {
            $user = $this->visitor->get();
            $fwzxId=$user['user_id'];
            $userInfo=$this->_member_mod->get($fwzxId);
            //只获取该服务中心下的
            $conditions2="";
            if(!empty($userInfo['region_id']))
            {
                $conditions2.=" and region_id ='".$userInfo['region_id']."'";
            }
            $store_id="(SELECT store_id FROM ".DB_PREFIX."store WHERE fwzx=$fwzxId AND tuoguan=1 AND dropState=1 $conditions2)";
        }

        $group = $this->_yushou_mod->get(array(
            'join'       => 'belong_goods',
            'conditions' => "gb.group_id=" . $id . " AND g.store_id in($store_id)",
            'fields'     => 'gb.state',
        ));


        if (!$group)
        {
            return false; // 越权或没有该团购
        }


        if($_GET["app"]=="service" &&  $_GET["act"] !="tuangou"){
            return in_array($_GET["act"], $state_permission[$group['state']]) ? true : false; // 该团购此状态是否允许执行此操作
        }

        if (empty($act))
        {
            return $state_permission[$group['state']]; // 返回该团购此状态时允许的操作
        }
        return in_array($act, $state_permission[$group['state']]) ? true : false; // 该团购此状态是否允许执行此操作
    }

    function export_ubbcode()
    {
        $code = '';
        $crlf = "\\n";
        $group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        /* 团购信息 */
        $group = $this->_yushou_mod->get($group_id);
        $group['spec_price'] = unserialize($group['spec_price']);
        $goods = $this->_query_goods_info($group['goods_id']);
        foreach ($goods['_specs'] as $key => $spec)
        {
            if (!empty($group['spec_price'][$spec['spec_id']]))
            {
                $goods['_specs'][$key]['group_price'] = $group['spec_price'][$spec['spec_id']]['price'];
            }
        }

        /* 默认图片 */
        $goods['default_image'] && $code .= '[img]' . SITE_URL . '/' . $goods['default_image'] . '[/img]' . $crlf;

        /* 团购名称 */
        $code .= '[b]' . Lang::get('group_name') . ':[/b]' . addslashes($group['group_name']) . $crlf ;

        /* 团购说明 */
        $code .= '[b]' . Lang::get('group_desc') . ':[/b]' . addslashes($group['group_desc']) . $crlf;
        $code .= '[b]' . Lang::get('group_min_quantity') . ':[/b]' . $group['min_quantity'] . $crlf;

        /* 规格 */
        if ($goods['spec_qty'] == 0)
        {
            $code .= '[b]' . Lang::get('group_price') . ':[/b][color=Red]' . price2ubb($goods['_specs'][0]['group_price']) . '[/color](' . Lang::get('price') . ':' . price2ubb($goods['_specs'][0]['price']) . ')' .$crlf;
        }
        elseif ($goods['spec_qty'] == 1 || $goods['spec_qty'] == 2)
        {
            $code .= '[b]' . Lang::get('group_price') . ':[/b]' . $crlf;
            foreach ($goods['_specs'] as $goods)
            {
                 isset($goods['group_price']) && $code .=  addslashes($goods['spec_1']) . '  ' . addslashes($goods['spec_2']) . '[color=Red]' . price2ubb($goods['group_price']) . '[/color](' . Lang::get('price') . ':' . price2ubb($goods['price']) . ')' . $crlf;
            }
            $code .= $crlf;
        }

        /* 购买地址 */
        $url = SITE_URL . '/' . url('app=yushou&id=' . $group_id);
        $url = str_replace('&amp;', '&' , $url);
        $code .= '[b]' . Lang::get('join_yushou') . ':[/b]' . '[url=' .$url . ']' . $url . '[/url]';

        $this->assign('code', $code);
        $this->assign('alert_code', str_replace("\n", '\\n', $code));

        header("Content-type:text/html;charset=" . CHARSET, true);
        $this->display('export_ubbcode.html');
    }
}

function price2ubb($price)
{
    return str_replace('&yen;', ' RMB', price_format($price));
}

?>
