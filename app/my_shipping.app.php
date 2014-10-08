<?php

/**
 *    我的收货地址控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_shippingApp extends StoreadminbaseApp
{
    function index()
    {
        /* 取得列表数据 */
        $model_shipping =& m('shipping');

        $store_id=$this->visitor->get('manage_store');

        $user = $this->visitor->get();
        $user_id=$user['user_id'];
        $member_mod =& m('member');
        $userInfo=$member_mod->get($user_id);

        $user_ids="";       //服务中心子账号

        /* 当前用户中心菜单 */
        if($_GET["app"]=="service")
        {

            $user_ids=$member_mod->find(array(
                "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
                "fields"    =>"user_id"
            ));
            $query_conditions="";
            if(count($user_ids)>0 && $userInfo["member_type"]=="02"){
                foreach($user_ids as $k=>$v){
                    $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                }

            }



            $this->assign('peisong', 1);
            if($_GET["store_id"]!="" && $_GET["store_id"]!="-1"){
                $store_id=$_GET["store_id"];
            }else{
                $query="";
                if(!empty($userInfo['region_id']))
                {
                    $query.=" and region_id ='".$userInfo['region_id']."'";
                }
                $store_id="(select store_id from ".DB_PREFIX."store where (fwzx=$user_id $query_conditions) and dropState=1 and state=1 $query )";

            }


            $conditions1="";
            //只获取该服务中心下的
            if(!empty($userInfo['region_id']))
            {
                $conditions1.=" and s.region_id ='".$userInfo['region_id']."'";
            }





            $store_mod =& m('store');
            $stores = $store_mod->find(array(
                'conditions' => " (fwzx=$user_id $query_conditions) and tuoguan=1 and s.dropState=1 and state=1".$conditions1,
                'join'  => 'belongs_to_user',
                'order' => "sort_order",
                'fields'=> 'this.*,member.user_name'
            ));
            // $this->print_r($stores);
            $this->assign('stores', $stores);


            $this->_curitem(empty($_GET["act"])?'service':$_GET["act"]);
        }else
        {
            $this->_curitem('my_shipping');
        }
        $page = $this->_get_page();
        $shippings     = $model_shipping->find(array(
            'conditions'    => "store_id in ($store_id) ",
            'fields'=> "this.*,(select store_name from ".DB_PREFIX."store where  dropState=1 and state=1 and store_id=shipping.store_id) as store_name",
            'limit' => $page['limit'],
            'count' => true,
        ));

        $page['item_count'] = $model_shipping->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

//        $this->pr($page);

        $this->assign('shippings', $shippings);



        $this->import_resource(array(
          'script' => array(
                   array(
                      'path' => 'dialog/dialog.js',
                      'attr' => 'id="dialog_js"',
                   ),
                   array(
                      'path' => 'jquery.ui/jquery.ui.js',
                      'attr' => '',
                   ),
                   array(
                      'path' => 'jquery.plugins/jquery.validate.js',
                      'attr' => '',
                   ),
                   array(
                      'path' => 'mlselection.js',
                      'attr' => '',
                   ),
          ),
          'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('my_shipping'), 'index.php?app=my_shipping',
                         LANG::get('shipping_list'));

        /* 当前所处子菜单 */
        $this->_curmenu('shipping_list');


        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_shipping'));
        header("Content-Type:text/html;charset=" . CHARSET);

        $member_mod =&m('member');
        $user = $member_mod->get_info($this->visitor->get('user_id'));
        $this->assign("user",$user);

        $this->display('my_shipping.index.html');
    }
    function yunfei()
    {

        $yunfei_mod=& m("yunfei");
        $region_mod =& m('region');
        $diqu= $region_mod->get_options(1);
        if(!$_POST)
        {
            //$this->print_r($diqu);exit;
            $shipping_id = $_GET["shipping_id"];
            $yunfeiData=$yunfei_mod->find(array(
                'conditions' => "shipping_id =$shipping_id ",
                'fields'=> 'this.*',
            ));

            if(count($yunfeiData)!=0){
                $yunfeiData=$yunfei_mod->find(array(
                    'conditions' => "shipping_id =$shipping_id ",
                    'fields'=> 'this.*',
                ));
                $this->assign('diqu_detail', $yunfeiData);
            }
            $tmp="";
            if(count($diqu)>0){
                foreach($diqu as $k=>$v){
                    if(count($region_mod->get_options($k))>1){
                        $tmp[$k] = array("region_name"=>$v,"region_id"=>$k,"region_next"=>$region_mod->get_options($k));
                    }else{
                        $tmp[$k] = array("region_name"=>$v,"region_id"=>$k);
                    }
                }
            }
            $this->assign('diqu', $tmp);
//            $this->pr($tmp);
//            exit;
            $this->assign('shipping_id', $shipping_id);

            if($_GET["app"]=="service")
            {
                $store_id=$_GET["store_id"];
                $this->assign('peisong', 1);
            }


            if($_GET["type"])
            {
                $this->assign('type', 1);
            }
            $this->display('yunfei.html');
        }else
        {

            $shipping_id = $_POST["shipping_id"];
            $diqus=$_POST["diqu"];
            $jiages=$_POST["jiage"];
            $region_ids=$_POST["region_ids"];
            if(count($jiages)>0)
            {
                $yunfei_mod->drop("shipping_id = $shipping_id");
            }
            foreach($jiages as $k=>$v)
            {
                if($jiages[$k]!="")
                {
                    $yunfei_mod->add(array("diqu"=>$diqus[$k],"jiage"=>$v,"shipping_id"=>$shipping_id,"region_id"=>$region_ids[$k]));
                }
            }
            $this->pop_warning('ok', 'seller_order_confirm_order');
        }




    }
    function add()
    {
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_shipping'), 'index.php?app=my_shipping',
                             LANG::get('add_shipping'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_shipping');

            /* 当前所处子菜单 */
            $this->_curmenu('add_shipping');
            $this->_assign_form();
            $this->_get_regions();
            $this->assign('cod_regions', array());
            //$this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
            if($_GET["app"]=="service")
            {
                $this->assign('peisong', 1);


                $user = $this->visitor->get();
                $user_id=$user['user_id'];
                $member_mod =& m('member');
                $userInfo=$member_mod->get($user_id);
                $conditions1="";
                //只获取该服务中心下的
                if(!empty($userInfo['region_id']))
                {
                    $conditions1.=" and s.region_id ='".$userInfo['region_id']."'";
                }


                $user_ids=$this->_member_mod->find(array(
                    "conditions"=>"member_type='03' and region_id=".$userInfo["region_id"],
                    "fields"    =>"user_id"
                ));
                $query_conditions="";
                if(count($user_ids)>0 && $userInfo["member_type"]=="02"){
                    foreach($user_ids as $k=>$v){
                        $query_conditions.=" or fwzx=".$v["user_id"]; //获得子账号下面的商家
                    }

                }


                $store_mod =& m('store');
                $stores = $store_mod->find(array(
                    'conditions' => " (fwzx=$user_id $query_conditions) and tuoguan=1 and s.dropState=1 and state=1".$conditions1,
                    'join'  => 'belongs_to_user',
                    'order' => "sort_order",
                    'fields'=> 'this.*,member.user_name'
                ));
                // $this->print_r($stores);
                $this->assign('stores', $stores);

            }
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_shipping.form.html');
        }
        else
        {
            $store_id=$this->visitor->get('manage_store');
            if($_GET["app"]=="service"  && $_POST["store_id"])
            {
                $store_id= $_POST["store_id"];
            }
            $data = array(
                'store_id'      => $store_id,
                'shipping_name' => $_POST['shipping_name'],
                'shipping_desc' => $_POST['shipping_desc'],
                'enabled'       => $_POST['enabled'],
                'sort_order'    => $_POST['sort_order'],
            );
            if (!empty($_POST['cod_regions']))
            {
                $data['cod_regions']    =   serialize($_POST['cod_regions']);
            }
            //$this->print_r($data);
            $model_shipping =& m('shipping');
            $shipping_id=null;
            if (!($shipping_id = $model_shipping->add($data)))
            {
                //$this->show_warning($model_shipping->get_error());
                $this->pop_warning($model_shipping->get_error());
                return;
            }

            /*
            $yunfei_mod=& m("yunfei");
            $region_mod =& m('region');
            $tmp= $region_mod->get_options(1);
            foreach($tmp as $k=>$v)
            {
                $yunfei_mod->add(array(
                    "shipping_id"=>$shipping_id,
                    "diqu"=>$v,
                    "region_id"=>$k
                ));
            }*/

            $this->pop_warning('ok', 'my_shipping_add');
        }
    }

    /**
     *    编辑配送方式
     *
     *    @author    Garbin
     *    @return    void
     */
    function edit()
    {
        $shipping_id = isset($_GET['shipping_id']) ? intval($_GET['shipping_id']) : 0;
        if (!$shipping_id)
        {
            echo Lang::get('no_such_shipping');
            return;
        }

        /* 判断是否是自己的 */
        $model_shipping =& m('shipping');

        $store_id=$this->visitor->get('manage_store');




        if($_GET["app"]=="service"  && ($_GET["store_id"] || $_POST["store_id"]))
        {
            $store_id= ($_GET["store_id"])?$_GET["store_id"]:$_POST["store_id"];
            $this->assign('peisong', 1);
            $this->assign('shipping_id', $shipping_id);
            $this->assign('store_id', $store_id);


            $fwzx_into = $this->visitor->get();
            $fwzxId=$fwzx_into['user_id'];

            $member_mod =& m('member');
            $userInfo=$member_mod->get($fwzxId);

            $conditions="";

            //只获取该服务中心下的
            if(!empty($userInfo['region_id']))
            {
                $conditions.=" and s.region_id ='".$userInfo['region_id']."'";
            }

            $store_mod =& m('store');
            $stores = $store_mod->find(array(
                'conditions' => " fwzx=$fwzxId and tuoguan=1 and s.dropState=1 and state=1".$conditions,
                'join'  => 'belongs_to_user',
                'fields'=> 'this.*,member.user_name'
            ));
            $this->assign('stores', $stores);

        }

        $shipping = $model_shipping->get("store_id=" . $store_id . " AND shipping_id={$shipping_id}");
        if (!$shipping && $_GET["app"]!="service")
        {
            echo Lang::get('no_such_shipping');
            return;
        }
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_shipping'), 'index.php?app=my_shipping',
                             LANG::get('edit_shipping'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_shipping');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_shipping');

            $this->_get_regions();

            $cod_regions = unserialize($shipping['cod_regions']);
            !$cod_regions && $cod_regions = array();

            $this->assign('shipping', $shipping);
            $this->assign('cod_regions', $cod_regions);
            $this->assign('yes_or_no', array(1 => Lang::get('yes'), 0 => Lang::get('no')));
            $this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_shipping.form.html');
        }
        else
        {

            $data = array(
                'shipping_name' => $_POST['shipping_name'],
                'shipping_desc' => $_POST['shipping_desc'],
                'enabled'       => $_POST['enabled'],
                'sort_order'    => $_POST['sort_order'],
            );

            if($_GET["app"]=="service")
            {
                $store_id=$_POST["store_id"];
                $data["store_id"]=$store_id;
            }


            $model_shipping =& m('shipping');

            $model_shipping->edit($shipping_id, $data);
            if ($model_shipping->has_error())
            {
                //$this->show_warning($model_shipping->get_error());
                $msg = $model_shipping->get_error();
                $this->pop_warning($msg['msg']);
                return;
            }
            $this->pop_warning('ok', 'my_shipping_edit');
        }
    }

    /**
     *    删除配送方式
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function drop()
    {
        $shipping_id = isset($_GET['shipping_id']) ? trim($_GET['shipping_id']) : 0;
        if (!$shipping_id)
        {
            $this->show_warning('no_such_shipping');

            return;
        }
        $ids = explode(',', $shipping_id);//获取一个类似array(1, 2, 3)的数组
        $model_shipping  =& m('shipping');

        $store_id=$this->visitor->get('manage_store');
        if($_GET["app"]=="service"  && $_GET["store_id"])
        {
            $store_id= $_GET["store_id"];
            $this->assign('peisong', 1);
            $this->assign('store_id', $_GET["store_id"]);
        }

        $drop_count = $model_shipping->drop("store_id = " . $store_id . " AND shipping_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* 没有可删除的项 */
            $this->show_warning('no_such_shipping');

            return;
        }

        if ($model_shipping->has_error())    //出错了
        {
            $this->show_warning($model_shipping->get_error());

            return;
        }
        $yunfei_mod=& m("yunfei");
        $yunfei_mod->drop("shipping_id = $shipping_id");
        $this->show_message('drop_shipping_successed');
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
                'name'  => 'shipping_list',
                'url'   => 'index.php?app=my_shipping',
            ),
/*            array(
                'name'  => 'add_shipping',
                'url'   => 'index.php?app=my_shipping&act=add',
            ),*/
        );
        if (ACT == 'edit')
        {
            $menus[] = array(
                'name'  => 'edit_shipping',
            );
        }
        return $menus;
    }
    function _get_regions()
    {
        $model_region =& m('region');
        $regions = $model_region->get_list(0);
        if ($regions)
        {
            $tmp  = array();
            foreach ($regions as $key => $value)
            {
                $tmp[$key] = $value['region_name'];
            }
            $regions = $tmp;
        }
        $this->assign('regions', $regions);
    }
    function _assign_form()
    {
        /*赋初始值*/
        $shipping = array(
            'enabled'       => 1,
            'sort_order'    => 255,
        );
        $yes_or_no = array(
            1 => Lang::get('yes'),
            0 => Lang::get('no'),
        );
        $this->assign('yes_or_no', $yes_or_no);
        $this->assign('shipping' , $shipping);
    }
}

?>