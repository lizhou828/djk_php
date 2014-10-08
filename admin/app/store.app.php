<?php

/* 店铺控制器 */
class StoreApp extends BackendApp
{
    var $_store_mod;

    function __construct()
    {
        $this->StoreApp();
    }

    function StoreApp()
    {
        parent::__construct();
        $this->_store_mod =& m('store');
    }

    function index()
    {

        if(!empty($_GET['wait_verify'])) {
            $conditions = " s.state = 0";
        }else {
            $conditions = ' 1=1 ';
        }
        $filter = $this->_get_query_conditions(array(
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'sgrade',
            ),
        ));
        $owner_name = trim($_GET['owner_name']);
        if ($owner_name)
        {

            $filter .= " AND (user_name LIKE '%{$owner_name}%' OR owner_name LIKE '%{$owner_name}%') ";
        }

        if($_GET["tuoguan"])
        {
            $filter.=" and s.tuoguan =".$_GET["region_name"];
        }
        if($_GET["region_name"])
        {
            $filter.=" and s.region_name like '%".$_GET["region_name"]."%'";
        }

        if($_GET["seller_type"] >= 0 && $_GET["seller_type"]!=""){
            $filter.=" and s.seller_type =".$_GET["seller_type"];
        }
        if($_GET["state"] > 0 && $_GET["state"]!=""){
            $filter.=" and s.state =".$_GET["state"];
        }

        if($_GET["add_time_from"])
        {
            $add_time_from=gmstr2time($_GET["add_time_from"]);
            $filter.=" and add_time >='$add_time_from' ";
        }
        if($_GET["add_time_to"])
        {
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            $filter.=" and add_time <= '$add_time_to' ";
        }

        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'sort_order';
                $order = '';
            }
        }
        else
        {
            $sort  = 'add_time';
            $order = 'desc';
        }

        $this->assign('filter', $filter);
        $conditions .= $filter;

        $page = $this->_get_page();
        $stores = $this->_store_mod->find(array(
            'conditions' => $conditions." and s.dropState=1",
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name',
            'limit' => $page['limit'],
            'count' => true,
            'order' => "$sort $order"
        ));


        $sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $this->assign('sgrades', $grades);

        $states = array(
            STORE_APPLYING  => LANG::get('wait_verify'),
            STORE_OPEN      => Lang::get('open'),
            STORE_CLOSED    => Lang::get('close'),
            STORE_UNPASS    => Lang::get('unpass'),
        );
        foreach ($stores as $key => $store)
        {
            $stores[$key]['sgrade'] = $grades[$store['sgrade']];
            $stores[$key]['state'] = $states[$store['state']];
            $certs = empty($store['certification']) ? array() : explode(',', $store['certification']);
            for ($i = 0; $i < count($certs); $i++)
            {
                $certs[$i] = Lang::get($certs[$i]);
            }
            $stores[$key]['certification'] = join('<br />', $certs);
        }
        $this->assign('stores', $stores);

        $region_mod =& m('region');
        $this->assign('regions', $region_mod->get_options(0));

        $page['item_count'] = $this->_store_mod->getCount();
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->_format_page($page);
        $this->assign('filtered', $filter? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);

        $this->display('store.index.html');
    }
    function test()
    {
        if (!IS_POST)
        {
            $sgrade_mod =& m('sgrade');
            $grades = $sgrade_mod->find();
            if (!$grades)
            {
                $this->show_warning('set_grade_first');
                return;
            }
            $this->display('store.test.html');
        }
        else
        {
            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];

            /* 连接到用户系统 */
            $ms =& ms();
            $user = $ms->user->get($user_name, true);
            if (empty($user))
            {
                $this->show_warning('user_not_exist');
                return;
            }
            if ($_POST['need_password'] && !$ms->user->auth($user_name, $password))
            {
                $this->show_warning('invalid_password');

                return;
            }

            $store = $this->_store_mod->get_info($user['user_id']);
            if ($store)
            {
                if ($store['state'] == STORE_APPLYING)
                {
                    $this->show_warning('user_has_application');
                    return;
                }
                else
                {
                    $this->show_warning('user_has_store');
                    return;
                }
            }
            else
            {
                header("Location:index.php?app=store&act=add&user_id=" . $user['user_id']);
            }
        }
    }

    function add()
    {
        $user_id = $_GET['user_id'];
        if (!$user_id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        if (!IS_POST)
        {
            /* 取得会员信息 */
            $user_mod =& m('member');
            $user = $user_mod->get_info($user_id);
            $this->assign('user', $user);

            $this->assign('store', array('state' => STORE_OPEN, 'recommended' => 0, 'sort_order' => 65535, 'end_time' => 0));

            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $this->assign('states', array(
                STORE_OPEN   => Lang::get('open'),
                STORE_CLOSED => Lang::get('close'),
            ));

            $this->assign('recommended_options', array(
                '1' => Lang::get('yes'),
                '0' => Lang::get('no'),
            ));

            $this->assign('scategories', $this->_get_scategory_options());

			$this->assign('init_jibie', Conf::get('jibie'));
            $this->assign('init_ticheng', Conf::get('ticheng'));

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
            ));

            $init_tichengs[1]=array();
            for($i=5;$i<=85;$i++){
                $init_tichengs[1][$i]=$i;
            }
            $this->assign('init_tichengs',$init_tichengs);

            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
            $this->display('store.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_store_mod->unique(trim($_POST['store_name'])))
            {
                $this->show_warning('name_exist');
                return;
            }
            $domain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
            if (!$this->_store_mod->check_domain($domain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
            {
                $this->show_warning($this->_store_mod->get_error());

                return;
            }

            $member_mod  =& m('member');
			$t_id=$_POST['t_id'];

			$relId=Conf::get('t_id');

			if(!empty($t_id) && $t_id!="")
			{
					$dateInfo=$member_mod->get(array(
											'conditions' => "1=1 AND user_name ='$t_id'",
	                    					'fields' => 'user_name,user_id',));
					if(!empty($dateInfo))
					{
						$relId=$dateInfo["user_id"];
						$member_mod->edit(array("user_id"=>$id),array("t_id"=>$relId));

					}else
					{
						header("Content-Type:text/html;charset=utf-8");
						echo "<center><br><br><br><><br><br><br><font size=3 color=red>邀请码不存在,请检查!<font>";
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?app=store&act=add&user_id=$user_id'>返回重新编辑</a></center>";
						return;
					}
			}

            $ticheng=$_POST["ticheng"];
            $data = array(
                'store_id'     => $user_id,
                'store_name'   => $_POST['store_name'],
                'owner_name'   => $_POST['owner_name'],
                'owner_card'   => $_POST['owner_card'],
                'region_id'    => $_POST['region_id'],
                'region_name'  => $_POST['region_name'],
                'address'      => $_POST['address'],
                'zipcode'      => $_POST['zipcode'],
                'tel'          => $_POST['tel'],
                'sgrade'       => $_POST['sgrade'],
                'end_time'     => empty($_POST['end_time']) ? 0 : gmstr2time(trim($_POST['end_time'])),
                'state'        => $_POST['state'],
                'recommended'  => $_POST['recommended'],
                'sort_order'   => $_POST['sort_order'],
                'add_time'     => gmtime(),
                'domain'       => $domain,
               // 'jibie'        => (empty($_POST['jibie'])?0:$_POST['jibie']),
                'ticheng'      => $ticheng/100,
                'pos_sn'       =>$_POST["pos_sn"]?$_POST["pos_sn"]:null
            );
            $certs = array();
            isset($_POST['autonym']) && $certs[] = 'autonym';
            isset($_POST['material']) && $certs[] = 'material';
            $data['certification'] = join(',', $certs);

            if ($this->_store_mod->add($data) === false)
            {
                $this->show_warning($this->_store_mod->get_error());
                return false;
            }

            $this->_store_mod->unlinkRelation('has_scategory', $user_id);
            $cate_id = intval($_POST['cate_id']);
            if ($cate_id > 0)
            {
                $this->_store_mod->createRelation('has_scategory', $user_id, $cate_id);
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=store',
                'continue_add', 'index.php?app=store&amp;act=test'
            );
        }
    }
    /*
     * 获得邀请码
     * **/
	function get_RPC_User()
	{
		$inputString = $_POST['queryString'];
        $dataTmp=null;

        if($_POST["type"])
        {
            $dataTmp=$this->_store_mod->get_RPC_User2($inputString);
        }else
        {
            $dataTmp=$this->_store_mod->get_RPC_User($inputString);
        }

		if(count($dataTmp)>0)
		{
            echo "<br>";
		    foreach($dataTmp as $key => $value)
			{
			 if(!empty($value))
			 {
			 	 echo '<li onClick="fill(\''.$value.'\');">'.$value.'</li>';
			 }

		    }
		}else
		{
            echo '<br>';
		    echo '<li>没有结果</li>';
		}
	}

    function edit()
    {

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $store=$this->_store_mod->get(array(
                'conditions' =>" s.store_id=$id ",
                'fields'      =>" this.*,(SUBSTRING_INDEX(s.ticheng * 100,'.',1)) as ticheng2 ",
            ));

            if (!$store)
            {
                $this->show_warning('store_empty');
                return;
            }
            {
                $certs = explode(',', $store['certification']);
                foreach ($certs as $cert)
                {
                    $store['cert_' . $cert] = 1;
                }
            }

            $this->assign('store', $store);

            $this->assign('init_jibie', Conf::get('jibie'));
            $this->assign('init_ticheng', Conf::get('ticheng'));

            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $this->assign('states', array(
                STORE_OPEN   => Lang::get('open'),
                STORE_CLOSED => Lang::get('close'),
            ));

            $this->assign('recommended_options', array(
                '1' => Lang::get('yes'),
                '0' => Lang::get('no'),
            ));

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->assign('scategories', $this->_get_scategory_options());

            $scates = $this->_store_mod->getRelatedData('has_scategory', $id);
            $this->assign('scates', array_values($scates));

            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
            ));

            $init_tichengs[1]=array();
            for($i=5;$i<=85;$i++){
                $init_tichengs[1][$i]=$i;
            }
            $this->assign('init_tichengs',$init_tichengs);


            $storeuploadedfile_mod =& m('storeuploadedfile');
            $storeuploadedfile = $storeuploadedfile_mod->find("store_id=$id and dropState=1");
            if($storeuploadedfile && count($storeuploadedfile)>0){
                //$this->pr($storeuploadedfile);
                $this->assign('storeuploadedfile', $storeuploadedfile);
            }

            $store_type = 1;  // 默认0是个人店铺
            if($store["fwzx"]!="" && $store["fwzx"]>0){
                $member_mod =& m('member');
                $fw_user = $member_mod->get($store["fwzx"]);
                if($fw_user!=null){
                    if($fw_user["member_type"] == "04"){
                        $store_type =3;   //采购
                    }else{
                        $store_type =2;   //服务中心
                    }
                }
            }
            $this->assign('store_type',$store_type);

            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
            $this->display('store.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_store_mod->unique(trim($_POST['store_name']), $id))
            {
                $this->show_warning('name_exist');
                return;
            }



            $store_info = $this->_store_mod->get_info($id);
            $domain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
            if ($domain && $domain != $store_info['domain'])
            {
                if (!$this->_store_mod->check_domain($domain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
                {
                    $this->show_warning($this->_store_mod->get_error());

                    return;
                }
            }

            $ticheng=$_POST["ticheng"];
            $pos_sn = $_POST["pos_sn"];
            if (trim($pos_sn) == "") $pos_sn = null;
            $data = array(
                'store_name'   => $_POST['store_name'],
                'owner_name'   => $_POST['owner_name'],
                'owner_card'   => $_POST['owner_card'],
                'region_id'    => $_POST['region_id'],
                'region_name'  => $_POST['region_name'],
                'address'      => $_POST['address'],
                'zipcode'      => $_POST['zipcode'],
                'tel'          => $_POST['tel'],
                'sgrade'       => $_POST['sgrade'],
                'end_time'     => empty($_POST['end_time']) ? 0 : gmstr2time(trim($_POST['end_time'])),
                'state'        => $_POST['state'],
                'sort_order'   => $_POST['sort_order'],
                'recommended'  => $_POST['recommended'],
                'domain'       => $domain,
                //'yaoqingma'    => (empty($_POST['yaoqingma'])?Conf::get('yaoqingma'):$_POST['yaoqingma']),
                //'jibie'        => (empty($_POST['jibie'])?0:$_POST['jibie']),
                'ticheng'      => $ticheng/100
            );
            if ($pos_sn != null) {
                $data["pos_sn"] = $pos_sn;
            }


            $data['state'] == STORE_CLOSED && $data['close_reason'] = $_POST['close_reason'];
            $certs = array();
            isset($_POST['autonym']) && $certs[] = 'autonym';
            isset($_POST['material']) && $certs[] = 'material';
            $data['certification'] = join(',', $certs);

			//验证邀请码
			$member_mod  =& m('member');
			$t_id=(empty($_POST['t_id'])?Conf::get('t_id'):$_POST['t_id']);
            if($t_id!=""){
                $dateInfo=$member_mod->get(array(
                    'conditions' => "1=1 AND user_name ='$t_id'",
                    'fields' => 'user_name,user_id',));
                if(!empty($dateInfo))
                {
                    $member_mod->edit(array("user_id"=>$id),array("t_id"=>$dateInfo["user_id"]));
                }else
                {
                    header("Content-Type:text/html;charset=utf-8");
                    echo "<center><br><br><br><br><br><br><font size=3 color=red>邀请码不存在,请检查!<font>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?app=store&act=edit&id=$id'>返回重新编辑</a></center>";
                    return;
                }
            }

            if($pos_sn != null && $pos_sn !="" ){
               $if_pos =  $this->_store_mod->getOne("select 1 from ecm_store where pos_sn='$pos_sn' and store_id != $id ");
               if($if_pos!=null){
                   $this->show_warning('pos 机编号重复！');
                   exit;
               }
            }

            //$this->pr($data);exit;

            $old_info = $this->_store_mod->get_info($id); // 修改前的店铺信息
            $this->_store_mod->edit($id, $data);

            $this->_store_mod->unlinkRelation('has_scategory', $id);
            $cate_id = intval($_POST['cate_id']);
            if ($cate_id > 0)
            {
                $this->_store_mod->createRelation('has_scategory', $id, $cate_id);
            }

            /* 如果修改了店铺状态，通知店主 */
            if ($old_info['state'] != $data['state'])
            {
                $ms =& ms();
                if ($data['state'] == STORE_CLOSED)
                {
                    // 关闭店铺
                    $subject = Lang::get('close_store_notice');
                    //$content = sprintf(Lang::get(), $data['close_reason']);
                    $content = get_msg('toseller_store_closed_notify',array('reason' => $data['close_reason']));
                }
                else
                {
                    // 开启店铺
                    $subject = Lang::get('open_store_notice');
                    $content = Lang::get('toseller_store_opened_notify');
                }
                $ms->pm->send(MSG_SYSTEM, $old_info['store_id'], '', $content);
                $this->_mailto($old_info['email'], $subject, $content);
            }

            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('edit_ok',
                'back_list',    'index.php?app=store&page=' . $ret_page,
                'edit_again',   'index.php?app=store&amp;act=edit&amp;id=' . $id
            );
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();
       if (in_array($column ,array('recommended','sort_order')))
       {
           $data[$column] = $value;
           $this->_store_mod->edit($id, $data);
           if(!$this->_store_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_store_to_drop');
            return;
        }

        /*
        $ids = explode(',', $id);
        foreach ($ids as $id)
        {
            $this->_drop_store_image($id); // 注意这里要先删除图片，再删除店铺，因为删除图片时要查店铺信息
        }
        if (!$this->_store_mod->drop($ids))
        {
            $this->show_warning($this->_store_mod->get_error());
            return;
        }*/

        //物理删除  改造 成 逻辑删除  2013-08-02
        if (!$this->_store_mod->edit("store_id in ($id)",array("dropState"=>0)))
        {
            $this->show_warning($this->_store_mod->get_error());
            return;
        }


        /* 通知店主 */
        $user_mod =& m('member');
        $users = $user_mod->find(array(
            'conditions' => "user_id" . db_create_in($ids),
            'fields'     => 'user_id, user_name, email',
        ));
        foreach ($users as $user)
        {
            $ms =& ms();
            $subject = Lang::get('drop_store_notice');
            $content = get_msg('toseller_store_droped_notify');
            $ms->pm->send(MSG_SYSTEM, $user['user_id'], $subject, $content);
            $this->_mailto($user['email'], $subject, $content);
        }

        $this->show_message('drop_ok');
    }

    /* 更新排序 */
    function update_order()
    {
        if (empty($_GET['id']))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $ids = explode(',', $_GET['id']);
        $sort_orders = explode(',', $_GET['sort_order']);
        foreach ($ids as $key => $id)
        {
            $this->_store_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

    /* 查看并处理店铺申请 */
    function view()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $store = $this->_store_mod->get_info($id);
            if (!$store)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $sgrade_mod =& m('sgrade');
            $sgrades = $sgrade_mod->get_options();
            $store['sgrade'] = $sgrades[$store['sgrade']];
            $this->assign('store', $store);

            $scates = $this->_store_mod->getRelatedData('has_scategory', $id);
            $this->assign('scates', $scates);

            $tichengs=Conf::get('ticheng');
            $this->assign('init_tichengs',$tichengs);
            //$this->pr($tichengs);

            $storeuploadedfile_mod =& m('storeuploadedfile');
            $storeuploadedfile = $storeuploadedfile_mod->find("store_id=$id and dropState=1");
            if($storeuploadedfile && count($storeuploadedfile)>0){
                //$this->pr($storeuploadedfile);
                $this->assign('storeuploadedfile', $storeuploadedfile);
            }
            $store_type = 1;  // 默认0是个人店铺
            if($store["fwzx"]!="" && $store["fwzx"]>0){
                $member_mod =& m('member');
                $fw_user = $member_mod->get($store["fwzx"]);
                if($fw_user!=null){
                   if($fw_user["member_type"] == "04"){
                       $store_type =3;   //采购
                   }else{
                       $store_type =2;   //服务中心
                   }
                }
            }

            $this->assign('store_type',$store_type);
            $this->display('store.view.html');
        }
        else
        {
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            /* 批准 */
            if (isset($_POST['agree']))
            {
                $this->_store_mod->edit($id, array(
                    'state'      => STORE_OPEN,
                    'add_time'   => gmtime(),
                    'sort_order' => 65535,
                    'ticheng'=>$_POST["ticheng"]
                ));

                $content = get_msg('toseller_store_passed_notify');
                $ms =& ms();
                $ms->pm->send(MSG_SYSTEM, $id, '', $content);
                $store_info = $this->_store_mod->get_info($id);
                $this->send_feed('store_created', array(
                    'user_id'   =>  $store_info['store_id'],
                    'user_name'   => $store_info['user_name'],
                    'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_info['store_id']),
                    'seller_name'   => $store_info['store_name'],
                ));

                $model_user =& m('member');
                //$model_user->edit($id,array("spreader_type"=>2));

                $this->_hook('after_opening', array('user_id' => $id));
                $this->show_message('agree_ok',
                    'edit_the_store', 'index.php?app=store&amp;act=edit&amp;id=' . $id,
                    'back_list', 'index.php?app=store&wait_verify=1&page=' . $ret_page
                );
            }
            /* 拒绝 */
            elseif (isset($_POST['reject']))
            {
                $reject_reason = trim($_POST['reject_reason']);
                if (!$reject_reason)
                {
                    $this->show_warning('input_reason');
                    return;
                }
                /*
                $content = get_msg('toseller_store_refused_notify', array('reason' => $reject_reason));
                $ms =& ms();
                $ms->pm->send(MSG_SYSTEM, $id, '', $content);

                $this->_drop_store_image($id); // 注意这里要先删除图片，再删除店铺，因为删除图片时要查店铺信息
                $this->_store_mod->drop($id);*/

                //禁止删掉店铺信息
                $this->_store_mod->edit($id,array("state"=>3,"unpass_reason"=>$reject_reason));   //设置为审核不通过


                $this->show_message('reject_ok',
                    'back_list', 'index.php?app=store&wait_verify=1&page=' . $ret_page
                );
            }
            else
            {
                $this->show_warning('Hacking Attempt');
                return;
            }
        }
    }

    function batch_edit()
    {
        if (!IS_POST)
        {
            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');
            $this->display('store.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $ids = explode(',', $id);
            $data = array();
            if ($_POST['region_id'] > 0)
            {
                $data['region_id'] = $_POST['region_id'];
                $data['region_name'] = $_POST['region_name'];
            }
            if ($_POST['sgrade'] > 0)
            {
                $data['sgrade'] = $_POST['sgrade'];
            }
            if ($_POST['certification'])
            {
                $certs = array();
                if ($_POST['autonym'])
                {
                    $certs[] = 'autonym';
                }
                if ($_POST['material'])
                {
                    $certs[] = 'material';
                }
                $data['certification'] = join(',', $certs);
            }
            if ($_POST['recommended'] > -1)
            {
                $data['recommended'] = $_POST['recommended'];
            }
            if (trim($_POST['sort_order']))
            {
                $data['sort_order'] = intval(trim($_POST['sort_order']));
            }

            if (empty($data))
            {
                $this->show_warning('no_change_set');
                return;
            }

            $this->_store_mod->edit($ids, $data);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('edit_ok',
                'back_list', 'index.php?app=store&page=' . $ret_page);
        }
    }

    function check_name()
    {
        $id         = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $store_name = empty($_GET['store_name']) ? '' : trim($_GET['store_name']);

        if (!$this->_store_mod->unique($store_name, $id))
        {
            echo ecm_json_encode(false);
            return;
        }
        echo ecm_json_encode(true);
    }

    /* 删除店铺相关图片 */
    function _drop_store_image($store_id)
    {
        $files = array();

        /* 申请店铺时上传的图片 */
        $store = $this->_store_mod->get_info($store_id);
        for ($i = 1; $i <= 3; $i++)
        {
            if ($store['image_' . $i])
            {
                $files[] = $store['image_' . $i];
            }
        }

        /* 店铺设置中的图片 */
        if ($store['store_banner'])
        {
            $files[] = $store['store_banner'];
        }
        if ($store['store_logo'])
        {
            $files[] = $store['store_logo'];
        }

        /* 删除 */
        foreach ($files as $file)
        {
            $filename = ROOT_PATH . '/' . $file;
            if (file_exists($filename))
            {
                @unlink($filename);
            }
        }
    }

    /* 取得店铺分类 */
    function _get_scategory_options()
    {
        $mod =& m('scategory');
        $scategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');

        return $tree->getOptions();
    }
}

?>
