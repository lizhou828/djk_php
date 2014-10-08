<?php

/* 会员控制器 */
class ShenhefwzxApp extends BackendApp
{
    var $_user_mod;

    function __construct()
    {

        $this->UserApp();

    }

    function UserApp()
    {

        parent::__construct();

        $this->_user_mod =& m('member');
    }

    //测试后台事务
    function test_db()
    {
        $model_address =& m('address');
        $model_member =& m('member');

        $model_address->db_query("INSERT INTO ecm_test(info) VALUE ('test1')");
        $model_address->db_query("INSERT INTO ecm_test(info) VALUE ('test2')");
        $model_member->db_query("INSERT INTO ecm_test(info) VALUE ('test1000000000000000000000000000000000000000')");
        $model_member->db_query("INSERT INTO ecm_test(info) VALUE1 ('test1000000000000000000000000000000000000000')");
        echo "ok";

    }

    /**验证服务中心唯一**/
    function checkRegion_name()
    {
        $region_name=$_POST["region_name"];
        $region_id=$_POST["region_id"];

        $users = $this->_user_mod->find(array(
            'fields' => 'this.user_id',
            'conditions' => "(region_name='$region_name' || region_id='$region_id') and member_type='02' and region_name!='' and status in (1,2)",
        ));

        if(empty($users) || count($users)==0)
        {
            echo "1";
        }else
        {
            echo "0";
        }
    }

    function index()
    {
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $_GET['field_name'],
                'name'  => 'field_value',
                'equal' => 'like',
            ),
        ));
        $type=(empty($_GET["type"]) || $_GET["type"]=="")?"01":$_GET["type"];

		if(!empty($_GET["region_name"]) && trim($_GET["region_name"])!="")
		{
		   $conditions.=" and member.region_name like '%".$_GET["region_name"]."%'";
		}

        if(isset($_GET["status"]) && $_GET["status"]!=-1)
        {
            $status=$_GET["status"];
            $conditions.=" and member.status =$status ";
            $this->assign('status',$status);
        }
        if($_GET["jiaose"])
        {
            if($_GET["jiaose"]=="1")
            {
                $conditions.="and member.region_name !=''";
            }
            else if($_GET["jiaose"]=="2")
            {
                $conditions.="and member.region_name =''";
            }
            $this->assign('jiaose', $_GET["jiaose"]);

        }

        //更新排序
        if (isset($_GET['sort']) && !empty($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'user_id';
             $order = 'asc';
            }
        }
        else
        {
            if (isset($_GET['sort']) && empty($_GET['order']))
            {
                $sort  = strtolower(trim($_GET['sort']));
                $order = "";
            }
            else
            {
                $sort  = 'user_id';
                $order = 'asc';
            }
        }
        $page = $this->_get_page();

        $users=null;
        if($type=="02"){
            $users = $this->_user_mod->find(array(
                'join' => 'has_store,manage_mall',
                'fields' => "this.*,
                          store.store_id,
                          userpriv.store_id as priv_store_id,
                          userpriv.privs,
                          (select service_jibie from ".DB_PREFIX."service_info where type=1 and service_id=member.user_id ORDER BY id LIMIT 0, 1) as service_jibie",
                'conditions' => "1=1 and member.dropState=1 and member_type='$type'" . $conditions,
                'limit' => $page['limit'],
                'order' => "$sort $order",
                'count' => true,
            ));

            if(count($users)>0)
            {
                $fwzx_jibie=Conf::get('fwzx_jibie');
                foreach($users as $k=>$v){
                    $users[$k]["service_jibie"]=$fwzx_jibie[$v["service_jibie"]];
                }
            }
        }else{
            $users = $this->_user_mod->find(array(
                'join' => 'has_store,manage_mall',
                'fields' => 'this.*,store.store_id,userpriv.store_id as priv_store_id,userpriv.privs',
                'conditions' => "1=1 and member.dropState=1 and member_type='$type'" . $conditions,
                'limit' => $page['limit'],
                'order' => "$sort $order",
                'count' => true,
            ));
        }
        foreach ($users as $key => $val)
        {
            if ($val['priv_store_id'] == 0 && $val['privs'] != '')
            {
                $users[$key]['if_admin'] = true;
            }
        }
        $this->assign('users', $users);
        $page['item_count'] = $this->_user_mod->getCount();
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);
		$this->assign('query_region_name',$_GET["region_name"]);
        $this->assign('member_type', $type);

        /* 导入jQuery的表单验证插件 */
        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->assign('query_fields', array(
            'user_name' => LANG::get('user_name'),
            'email'     => LANG::get('email'),
            'real_name' => LANG::get('real_name'),
//            'phone_tel' => LANG::get('phone_tel'),
//            'phone_mob' => LANG::get('phone_mob'),
        ));

        $region_mod =& m('region');
        $this->assign('regions', $region_mod->get_options(0));


        if($_GET["jiaose"])
        {
            $this->assign('jiaose',$_GET["jiaose"]);
        }

        $this->assign('sort_options', array(
            'reg_time DESC'   => LANG::get('reg_time'),
            'last_login DESC' => LANG::get('last_login'),
            'logins DESC'     => LANG::get('logins'),
        ));
        $this->display('user.index.html');
    }

    function add()
    {


    	$type=(empty($_GET["type"]) || $_GET["type"]=="")?"01":$_GET["type"];
        if (!IS_POST)
        {
            $this->assign('user', array(
                'gender' => 0,
            ));
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $ms =& ms();
            $this->assign('member_type', $type);
            $this->assign('act', "add");

            if($type=="02")
            {
                $this->assign('fwzx_jibie', Conf::get('fwzx_jibie'));
            }

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            if($_GET["jiaose"])
            {
                $this->assign('jiaose', $_GET["jiaose"]);
            }

            $this->assign('set_avatar', $ms->user->set_avatar());
            $this->display('user.form.html');
        }
        else
        {
            $jiaose=$_POST["jiaose"];

            $user_name = trim($_POST['user_name']);
            $password  = trim($_POST['password']);
            $email     = trim($_POST['email']);
            $real_name = trim($_POST['real_name']);
            $gender    = trim($_POST['gender']);
            $im_qq     = trim($_POST['im_qq']);
            $im_msn    = trim($_POST['im_msn']);

			$region_name    = trim($_POST['region_name']);
			$region_id    = trim($_POST['region_id']);
			$t_id    = trim($_POST['t_id']);



			//验证邀请码
			//$real_id=Conf::get('t_id');
            $real_id="";
			$member_mod  =& m('member');

				if(!empty($_POST['t_id']) && $_POST['t_id'] !="")
				{
					$t_id=$_POST['t_id'];
					$dateInfo=$member_mod->get(array(
											'conditions' => "1=1 AND user_name ='$t_id'",
	                    					'fields' => 'user_name,user_id',));
					if(!empty($dateInfo))
					{
						$real_id=$dateInfo["user_id"];
					}else
					{
                        $this->show_warning('yaoqingma_is_null');
                        return;
					}

				}

            if (strlen($user_name) < 3 || strlen($user_name) > 25)
            {
                $this->show_warning('user_name_length_error');

                return;
            }

            if (strlen($password) < 6 || strlen($password) > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            /* 连接用户系统 */
            $ms =& ms();

            /* 检查名称是否已存在 */
            if (!$ms->user->check_username($user_name))
            {
                $this->show_warning($ms->user->get_error());

                return;
            }
			$model_member =& m('member');
            $data2 = array(
                "user_name"=>$user_name,
                "password"=>md5($password),
                /*"email"=>$email,*/
                "member_type"=>$type,
                "region_name"=>$region_name,
                "region_id"=>$region_id,
                'reg_time'  => gmtime(),
                't_id'  => $real_id,
                'status'=>$_POST["status"],
                'nick_name'=>$user_name
            );

            if($email!=""){
                if (!is_email($email)){
                    $this->show_warning('email_error');
                    return;
                }
                $data2["email"]=$email;
            }

	        $user_id = $model_member->add($data2);
	        if (!$user_id){
	            $this->_errors = $model_member->get_error();
	            $user_id= 0;
	        }


            if (!$user_id){
                $this->show_warning($ms->user->get_error());
                return;
            }
			$this->assign('member_type', $type);

            $params = null;
            $params["type"] = "regist";
            $params["userId"] = $user_id;
            $params["tId"] = ($real_id>0) ? $real_id : 0;
            $params["orderId"] ="";
            $params["userName"] ="";
            $params["channelCode"] ="";
            $params["channelName"] ="";
            $params["channelCard"] ="";
            $params["jine"] ="0";
            $params["regionId"] ="0";
            $Client = new HttpClient(JAVA_LOCATION);
            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
            HttpClient::quickPost($url, $params);

            if($type=="02" || $type == '04' ){
                //新增成功服务中心，写入服务中心级别
                $service_info_data=array(
                    "service_id"=>$user_id,
                    "type"=>1,
                    "service_jibie"=>$_POST["fwzx_jibie"],
                    "update_time"=>gmtime(),
                );
                $serviceInfo_model =& m('serviceInfo');
                $serviceInfo_model->add($service_info_data);
            }


            if (!empty($_FILES['portrait'])){
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false){
                    return;
                }
                $portrait && $this->_user_mod->edit($user_id, array('portrait' => $portrait));
            }
            $this->show_message('add_ok',
                'back_list',    "index.php?app=user&type=$type",
                'continue_add', "index.php?app=user&type=$type&amp;act=add"
            );
        }
    }

    /*检查会员名称的唯一性*/
    function  check_user()
    {
          $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
          if (!$user_name)
          {
              echo ecm_json_encode(false);
              return ;
          }

          /* 连接到用户系统 */
          $ms =& ms();
          echo ecm_json_encode($ms->user->check_username($user_name));
    }

    function edit()
    {

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $type=(empty($_GET["type"]) || $_GET["type"]=="")?"01":$_GET["type"];
        $jiaose=($_GET["jiaose"])?$_GET["jiaose"]:$_POST["jiaose"];

        if (!IS_POST)
        {
            /* 是否存在 */

            //$user = $this->_user_mod->get_info($id);

			 $model_member =& m('member');
			 $user = $model_member->get(array(
            'conditions' => $id,
            'join'       => 'belongs_to_user',
            'fields'     => 'this.*,
                         (select user_name from '.DB_PREFIX.'member where user_id=member.t_id) as t_id,
                         (select service_jibie from '.DB_PREFIX.'service_info where service_id='.$id.' and type=1 limit 0,1) as service_jibie',
            ));

            if (!$user)
            {
                $this->show_warning('user_empty');
                return;
            }

            if($type=="02")
            {
                $this->assign('fwzx_jibie', Conf::get('fwzx_jibie'));
            }

            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar($id));

            //$this->pr($user);

            $this->assign('user', $user);
            $this->assign('phone_tel', explode('-', $user['phone_tel']));
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));

            $this->assign('member_type', $type);
            $this->assign('act', "edit");

            if($jiaose!="")
            {
                $this->assign('jiaose', $jiaose);
            }
            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $serviceInfo_model =& m('serviceInfo');
            $serviceInfo=$serviceInfo_model->get("service_id=$id and type=1 limit 0,1");
            $this->assign('serviceInfo', $serviceInfo);

            $this->display('user.form.html');
        }
        else
        {
        	$region_name    = trim($_POST['region_name']);
            $status    = trim($_POST['status']);
			$region_id    = trim($_POST['region_id']);
			$t_id    = trim($_POST['t_id']);
            $fwzx_jibie    = trim($_POST['fwzx_jibie']);

            //echo $fwzx_jibie;exit;
			//验证邀请码
			$member_mod  =& m('member');

			$real_id=Conf::get('t_id');
			if(!empty($_POST['t_id']) && $_POST['t_id'] !="")
				{
					$t_id=$_POST['t_id'];
					$dateInfo=$member_mod->get(array(
											'conditions' => "1=1 AND user_name ='$t_id'",
	                    					'fields' => 'user_name,user_id',));
					if(!empty($dateInfo))
					{
						$real_id=$dateInfo["user_id"];
					}else
					{
                        $this->show_warning('yaoqingma_is_null');
                        return;
					}

		    }




            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
                'im_msn'    => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
//				"region_name"=>$region_name,
//				"region_id"=>$region_id,
				"t_id"=>$real_id,
                "status"=>$status,
            );

            if(!empty($region_name) && $region_name!=""){
                $data["region_name"] = $region_name;
                $data["region_id"] = $region_id;

            }

            if (!empty($_POST['password']))
            {
                $password = $_POST['password'];
                if (strlen($password) < 6 || strlen($password) > 20)
                {
                    $this->show_warning('password_length_error');

                    return;
                }
                $password=md5($password);
                $user_data['password']=$password;
                $data['password']=$password;
            }

            if($_POST['email']!=""){
                if (!is_email(trim($_POST['email'])))
                {
                    $this->show_warning('email_error');

                    return;
                }
                $data["email"]=trim($_POST['email']);
            }else{
                $data["email"]=null;
            }


            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($id);
                if ($portrait === false)
                {
                    return;
                }
                $data['portrait'] = $portrait;
            }
            //$this->pr($data);exit;
            /* 修改本地数据 */
            $this->_user_mod->edit($id, $data);
			$this->assign('member_type', $type);

            if(!empty($fwzx_jibie) && $fwzx_jibie!="")
            {
                $serviceInfo_model =& m('serviceInfo');
                $s_info=$serviceInfo_model->get(array(
                                                        "conditions"=>"service_id=$id and type=1"
                ));

                $serviceData=array(
                    'service_user'=>$_POST["real_name"],
                    'service_gender'=>$_POST["gender"],
                    'service_age'=>$_POST["service_age"],
                    'service_tel'=>$_POST["service_tel"],
                    'service_email'=>$_POST["email"],
                    'service_address'=>$_POST["service_address"],
                    'service_zhengjian'=>$_POST["service_zhengjian"],
                    'service_jibie'=>$fwzx_jibie,
                );

                if($_FILES["yingyezhizhaoImg"]['name']!=""){
                    $yingyezhizhaoImg = $this->_upload_zhengjian($id,"yingyezhizhaoImg");
                    if($yingyezhizhaoImg != null && $yingyezhizhaoImg != ""){
                        $serviceData["yingyezhizhaoImg"] = $yingyezhizhaoImg;
                    }
                }

                if($_FILES["shenfenzhengImg"]['name']!=""){
                    $shenfenzhengImg =$this->_upload_zhengjian($id,"shenfenzhengImg");
                    if($shenfenzhengImg != null && $shenfenzhengImg != ""){
                        $serviceData["shenfenzhengImg"] = $shenfenzhengImg;
                    }
                }

                if($s_info["id"]<=0)
                {
                    $serviceData["service_id"] = $id;
                    $serviceInfo_model->add($serviceData);
                }else
                {
                    $serviceInfo_model->edit($s_info["id"],$serviceData);
                }

            }

            //exit;
            $this->show_message('edit_ok',
                'back_list',    "index.php?app=user&type=$type",
                'edit_again',   "index.php?app=user&type=$type&amp;act=edit&amp;id=" . $id
            );
        }
    }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_user_to_drop');
            return;
        }
        $admin_mod =& m('userpriv');
        $type=(empty($_GET["type"]) || $_GET["type"]=="01")?"01":"02";
        if(!$admin_mod->check_admin($id))
        {
            $this->show_message('cannot_drop_admin',
                'drop_admin', "index.php?app=admin&type=$type");
            return;
        }

        //$ids = explode(',', $id);

        // 连接用户系统，从用户系统中删除会员
        $ms =& ms();
        /*if (!$ms->user->drop($ids))
        {
            $this->show_warning($ms->user->get_error());

            return;
        }*/

        //物理删除  改造 成 逻辑删除  2013-08-02
        if (!$this->_user_mod->edit("user_id in ($id)",array("dropState"=>0)))
        {
            $this->show_warning($this->_user_mod->get_error());
            return;
        }

        $this->show_message('drop_ok');
    }

    /**
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }

        import('uploader.lib');

        $type=(empty($_GET["type"]) || $_GET["type"]=="01")?"01":"02";
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
            $this->show_warning($uploader->get_error(), 'go_back', "index.php?app=user$type&amp;act=edit&amp;id=" . $user_id);
            return false;
        }

        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }

    /**
     * 上传证件
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_zhengjian($user_id,$file_name)
    {
        $file = $_FILES["$file_name"];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }

        import('uploader.lib');

        $type=(empty($_GET["type"]) || $_GET["type"]=="01")?"01":"02";
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
            $this->show_warning($uploader->get_error(), 'go_back', "index.php?app=user$type&amp;act=edit&amp;id=" . $user_id);
            return false;
        }

        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . gmtime()."_".rand(10000000,99999999), $user_id);
    }

    //审核服务中心首页
    function shenhefwzx()
    {
        $shenqing_model=& m("serviceShenqing");
        if($_GET)
        {
                /**
                 * 服务中心状态 status :
                 * 默认初始化 0
                 * 被抢注并且支付成功 变成1 ，审核中状态
                 * 后台审核通过，状态变成2正常
                 **/
                $conditions=" and 1=1 ";
                $page = $this->_get_page();

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
                    $sort  = 'id';
                    $order = 'desc';
                }

                if($_GET["service_user"])
                {
                    $service_user=$_GET["service_user"];
                    $conditions.=" and service_shenqing.service_user like '%$service_user%' ";
                }

                if($_GET["add_time_from"])
                {
                    $add_time_from=strtotime($_GET["add_time_from"]);
                    $conditions .= " AND service_shenqing.add_time>=$add_time_from";
                }

                if($_GET["add_time_to"])
                {
                    $add_time_to=gmstr2time_end($_GET["add_time_to"]);
                    $conditions .= " AND service_shenqing.add_time<=$add_time_to";
                }

                if($_GET["user_name"])
                {
                    $user_name=$_GET["user_name"];
                    $conditions.=" and member.status in (0,1,2) and member.user_name='$user_name'";
                }

                if(isset($_GET["type"]) && $_GET["type"]!="")
                {
                    $conditions .= " AND service_shenqing.type=".$_GET["type"]."";
                }

                if(isset($_GET["paymentState"]) && $_GET["paymentState"]!="")
                {
                    $conditions .= " AND service_shenqing.paymentState=".$_GET["paymentState"]."";
                }

                $this->assign('query_info', array("type"=>$_GET["type"],"paymentState"=>$_GET["paymentState"]));

                /*
                $shenqings = $shenqing_model->find(array(
                'conditions' => $conditions,
                'fields'=> 'this.*,(select user_name    from '.DB_PREFIX.'member where user_id=service_shenqing.service_id ) as user_name,
                                (select status    from '.DB_PREFIX.'member where user_id=service_shenqing.service_id ) as service_status',
                'limit' => $page['limit'],
                'count' => true,
                'order' => "$sort $order"
               ));*/
              $sql="select service_shenqing.*,member.user_name as user_name,member.region_name as region_name,member.status service_statusm,".QIANGZHU_FAIL_TIME."-(".gmtime()."-add_time) as q_time from ".DB_PREFIX."service_shenqing service_shenqing,
                ".DB_PREFIX."member member where service_shenqing.service_id=member.user_id $conditions order by $sort $order limit ".$page['limit'];
              $shenqings = $shenqing_model->getAll($sql);
              //$this->pr($shenqings);
              $shenqing_model->_updateLastQueryCount("SELECT COUNT(*) as c FROM ".DB_PREFIX."service_shenqing service_shenqing,
                ".DB_PREFIX."member member where service_shenqing.service_id=member.user_id $conditions order by $sort $order");


              //$this->pr($shenqings);
              $this->assign('shenqings', $shenqings);
              $page['item_count'] = $shenqing_model->getCount();
              $this->_format_page($page);

              $this->assign('page_info', $page);

              $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
              $this->display('service.shenhe.html');
        }
    }


    //查看申请服务人详细
    function  shenhefwzx_view()
    {
        $id=$_GET["id"];
        if(empty($id) || $id=="")
        {
            return;
        }
        $shenqing_model=& m("serviceShenqing");
        $shenqing=$shenqing_model->get(array(
            'conditions' => " id=$id ",
            'fields'     => "this.*,
                          (select region_name from ".DB_PREFIX."member where user_id=this.service_id) as region_name
                           ",
            'order'      =>  "id desc"
        ));

        $this->assign('shenqing', $shenqing);
        $this->display('service.shenhe.view.html');
    }

    //申请服务中心没有被审核通过，退款
    function tuikuan()
    {

        //调用支付接口，退款
        //退款成功后 回写 支付状态为退款成功
        if($_GET)
        {
            $service_id=$_GET["service_id"];
            $id=$_GET["id"];
            $this->checkShenqing($service_id,$id);

            $shenqing_model=& m("serviceShenqing");
            $shenqing_model->edit($id,array("paymentState"=>2,"update_time"=>gmtime()));

            $this->show_message('操作成功',
                'index', 'index.php?app=user&act=shenhefwzx');
        }



    }
    //审核服务中心
    function shenhe()
    {

        $shenqing_model=& m("serviceShenqing");
        $member_mod =& m('member');
            if($_GET)
            {
                $service_id=$_GET["service_id"];
                $id=$_GET["id"];

                $this->checkShenqing($service_id,$id);



                $flg=$_GET["flg"];
                //审核通过
                if($flg=="1")
                {
                    $serviceData=array(
                        "type"=>'1',
                        "update_time"=>gmtime(),
                    );

                    //设置当前人审核通过
                    $shenqing_model->edit($id,$serviceData);

                    //检查申请人和服务中心的对应关系
                    $shenqings = $shenqing_model->find(array(
                        'conditions' => " 1=1 and service_shenqing.type=0  and service_id=$service_id and id !=$id ",
                        'order' => "id desc"
                    ));
                    if(count($shenqings)<1){

                        //如果不小心有多个人同时申请了，设置其他的人审核不通过
                        foreach($shenqings as $key=>$shenqing)
                        {
                            //这里是先调用支付接口全部退款后马上变成已退款状态，还是先全部变成未退款,然后分别每个退款
                            // $shenqing_model->edit($shenqing["id"],array("type"=>2,"paymentState"=>3,"update_time"=>gmtime()));
                            $shenqing_model->db_query("update ".DB_PREFIX."service_shenqing set type=2,update_time=".gmtime()." where type=0 and service_id=$service_id and id !=$id");
                        }

                    }


                    //更新服务中心状态为正常账号
                    $member_mod->edit($service_id,array("status"=>2));

                    $shenqing=$shenqing_model->get_info($id);
                    $yingyezhizhaoImg=$shenqing["yingyezhizhaoImg"];
                    $shenfenzhengImg=$shenqing["shenfenzhengImg"];
                    $service_zhengjian=$shenqing["service_zhengjian"];
                    //更新服务中心的营业执照和身份证
                    $shenqing_model->db_query("update ".DB_PREFIX."service_info set yingyezhizhaoImg='$yingyezhizhaoImg',shenfenzhengImg='$shenfenzhengImg',service_zhengjian='$service_zhengjian' where service_id=$service_id and type=1");
                }
                else if($flg=="0")        //审核不通过
                {
                    //找到该服务中心下所有已付款的
                    $shenqings = $shenqing_model->find(array(
                        'conditions' => " 1=1 and service_shenqing.type=0  and service_id=$service_id",
                        'order' => "id desc"
                    ));

                    //如果只有1条记录，设置审核不通过,还原服务中心状态
                    if(count($shenqings)==1)
                    {
                        $shenqing_model->db_query("update ".DB_PREFIX."service_shenqing set type=2,update_time=".gmtime()." where type=0 and service_id=$service_id and id =$id");
                        $shenqing_model->db_query("update ".DB_PREFIX."member set status=0  where user_id=$service_id and status=1");
                    }else
                    {
                        $shenqing_model->db_query("update ".DB_PREFIX."service_shenqing set type=2,update_time=".gmtime()." where type=0 and service_id=$service_id and id =$id ");
                    }

                }
                //发送邮件通知

                $this->show_message('操作成功',
                    'index', "index.php?app=".$_GET["app"]."&act=shenhefwzx");

            }


    }


    //检查是否合法
    function checkShenqing($service_id,$id)
    {
        $shenqing_model=& m("serviceShenqing");
        $shenqingInfo=$shenqing_model->get_info($id);
        if($shenqingInfo["service_id"] != $service_id)
        {
            $this->show_warning('shenqing_error');
            exit;
        }
    }

}

?>
