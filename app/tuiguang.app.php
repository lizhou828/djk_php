<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class TuiguangApp extends MemberbaseApp
{
    var $_feed_enabled = false;
    var $_uploadedfile_mod;
    function __construct()
    {
        $this->TuiguangApp();
        $this->_member_mod =& m('member');
        $this->_store_mod =& m('store');

        $member_mod =&m('member');
        $user = $member_mod->get_info($this->visitor->get('user_id'));
        $this->assign("user",$user);

		$this->_curitem(empty($_GET["act"])?'tuiguang':$_GET["act"]);
        include_once(ROOT_PATH . '/app/make_image.app.php');
		$dataInfo=$this->visitor->info;	
                $this->assign('tuiguangURL', "/reg/".$dataInfo['user_id']);

        $this->assign('tuiguangUSER', base64_encode($dataInfo['user_name']));

        if( (empty($dataInfo["member_type"]) || $dataInfo["member_type"]!="01") && $_GET["act"] != "view" && $_GET["act"] != "product"  && $_GET["act"] != "help"
            && $_GET["act"] != "reg_store"){
			echo "<script>location='index.php';</script>";
		}
        $this->_uploadedfile_mod = &m('uploadedfile');
        include_once(ROOT_PATH . '/includes/MSM.php');                    //短信发送
        //print_r($init_user);
    }
    function TuiguangApp()
    {
        parent::__construct();
        $this->assign('appName', $_GET["app"]);
        $this->_store_mod =& m('store');
        $this->_goods_mod =& m('goods');


    }

    function index()
    {
         $_GET["act"]="tuiguang";
		 $this->tuiguang();
    }

    function view(){

        $user = $this->visitor->info;
        $memberqianyue_mod =& m('memberqianyue');
        $qianyue = $memberqianyue_mod->get("user_id=".$user["user_id"]);
        if($qianyue){
            $this->assign('qianyue', $qianyue);
        }

        $this->display("tuiguang_view.html");
    }

    function shenqing(){
        if($_GET["success"]){
            $this->assign('success', 1);
            $this->display("tuiguang_shenqing.html");
            exit;
        }
        $user = $this->visitor->info;
        $memberqianyue_mod =& m('memberqianyue');
        $region_mod =& m('region');
        $this->assign('regions', $region_mod->get_options(0));
        $qianyue = $memberqianyue_mod->get("user_id=".$user["user_id"]);
        if($qianyue){
            $this->assign('qianyue', $qianyue);
        }

        $this->display("tuiguang_shenqing.html");
    }

    function sava_shenqing(){
        header('Content-type: text/json');
        if(!$_POST){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入签约申请信息","error_lbl"=>"user_name"));
            exit;
        }

        $account_type = $_POST["account_type"];
        $region_id = $_POST["region_id"];
        $region_name = $_POST["region_name"];
        $user_name = $_POST["user_name"];
        $shenfenzheng = $_POST["shenfenzheng"];
        $phone_mob = $_POST["phone_mob"];
        $email = $_POST["email"];

        $company_name = $_POST["company_name"];
        $company_address = $_POST["company_address"];

        if($account_type == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请选择帐号类型","error_lbl"=>"account_type"));
            exit;
        }

        if($account_type == "1" && $company_name == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入公司名称","error_lbl"=>"company_name"));
            exit;
        }

        if($account_type == "1" && $company_address == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入公司地址","error_lbl"=>"company_address"));
            exit;
        }

        if($region_id == "" || $region_id < 1){
            echo json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id"));
            exit;
        }

        $region_mod =& m('region');
        if(count($region_mod->get_options($region_id)) > 0 ){
            echo json_encode(array("flag"=>"error","error_msg"=>"地区必须选择到最后1级","error_lbl"=>"region_id"));
            exit;
        }
        if($user_name == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入真实姓名","error_lbl"=>"user_name"));
            exit;
        }

        if($shenfenzheng == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入身份证号码","error_lbl"=>"shenfenzheng"));
            exit;
        }

        if($phone_mob == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入手机号码","error_lbl"=>"phone_mob"));
            exit;
        }

        if($email == ""){
            echo json_encode(array("flag"=>"error","error_msg"=>"请输入电子邮箱","error_lbl"=>"email"));
            exit;
        }

        $memberqianyue_mod =& m('memberqianyue');

        $user = $this->visitor->info;
        $data["account_type"] = $account_type;
        $data["region_id"] = $region_id;
        $data["region_name"] = $region_name;
        $data["user_name"] = $user_name;
        $data["shenfenzheng"] = $shenfenzheng;
        $data["phone_mob"] = $phone_mob;
        $data["email"] = $email;
        $data["status"] = 0;
        date_default_timezone_set('Asia/Shanghai');
        $data["add_time"] = date('Y-m-d H:i:s');

        if($account_type == 1){
            $data["company_name"] = $company_name;
            $data["company_address"] = $company_address;
        }
        $rs = 0;
        $tmp = $memberqianyue_mod->get("user_id=".$user["user_id"]);
        if( $tmp != "" && $tmp["user_id"] >0){
            $rs = $memberqianyue_mod->edit($user["user_id"],$data);
        }else{
            $data["user_id"] = $user["user_id"];
            $rs = $memberqianyue_mod->add($data);
        }
        echo json_encode(array("flag"=>"ok"));

    }

    function daima(){
        $this->display("tuiguang_daima.html");
    }

    function product(){
        $this->display("tuiguang_product.html");
    }

    function help(){
        $this->display("tuiguang_help.html");
    }

	function tuiguang()
    {
        import('uploader.lib');
        include "phpqrcode/phpqrcode.php";
        /* 当前用户中心菜单 */
        $this->_curitem('tuiguang');
        /* 当前所处子菜单 */
        $this->_curmenu('tuiguang');
        $this->import_resource(array(
            'script' => 'jquery.plugins/jquery.validate.js',
        ));
        $this->display("member.to_tuiguang.html");
    }

    /* 取得分类最小提成 */
    function _get_scategory_min_ticheng()
    {
        $mod =& m('scategory');
        $scategories = $mod->find(array
            (
                "conditions"=> " dropState=1 order by cate_id desc",
                'fields' => 'cate_id,changshang_ticheng,dailishang_ticheng,lingshoushang_ticheng,shitidian_ticheng',
            )
        );
        return $scategories;
    }

	function uploadStore()
	{
		if(!$_POST)
		{


			 	 $user_id = $this->visitor->get('user_id');
				/* 当前用户中心菜单 */
				$this->_curitem('uploadStore');
				/* 当前所处子菜单 */
				$this->_curmenu('uploadStore');


		 		$this->assign('scategories', $this->_get_scategory_options());
                $this->assign('scategories_ticheng', $this->_get_scategory_min_ticheng());

	            $region_mod =& m('region');
	            $this->assign('regions', $region_mod->get_options(0));
				
				$user_id = $this->visitor->get('user_id');    
				$userList=$this->_member_mod->find(array
										(
										 "conditions"=> " 1=1 and t_id=$user_id and (select count(1) from ".DB_PREFIX."store where store_id=member.user_id)<1 ",
										 'fields' => 'user_name,user_id,nick_name,im_qq',
										));
	           

	            /* 导入jQuery的表单验证插件 */
	            $this->import_resource(array(
	                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
	            ));


                $init_tichengs2[1]=array();
                for($i=5;$i<=85;$i++){
                    $init_tichengs2[1][$i]=$i;
                }

                $this->assign('init_tichengs2',$init_tichengs2);

                $region_mod =& m('region');
                $this->assign('regions', $region_mod->get_options(0));

				$this->assign('userList', $userList);


                /*$this->assign('editor_upload', $this->_build_upload(array(
                    'obj' => 'EDITOR_SWFU',
                    'belong' => BELONG_STORE,
                    'item_id' => $this->_store_id,
                    'button_text' => Lang::get('bat_upload'),
                    'button_id' => 'editor_upload_button',
                    'progress_id' => 'editor_upload_progress',
                    'upload_url' => 'index.php?app=swfupload',
                    'if_multirow' => 1,
                )));
                $this->assign('build_editor', $this->_build_editor(array(
                    'name' => 'description',
                    'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
                )));*/

            $this->assign('ECM_ID', $_COOKIE['ECM_ID']);
            $this->assign('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);

                $this->_config_seo('title', "提交商家资料" . ' - ' . Lang::get('member_center'));

                $member_mod =&m('member');
                $user = $member_mod->get_info($this->visitor->get('user_id'));
                $this->assign("user",$user);
       			$this->display("tuiguang.uploadStore.html");
		 }else
		 {
             echo "<meta charset='utf-8' />";
             //header('Content-type: text/json');
             $store_id=$_POST["store_id"];
             $store_mod  =& m('store');
             if(!$store_id)
             {
                 return;
             }

             $store_name= $_POST['store_name'];
             if($_POST['store_name'] == ""){
                 //$this->show_warning('请选择地区<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"店铺名不能为空","error_lbl"=>"store_name"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"店铺名不能为空","error_lbl"=>"store_name")).");</script>";
                 exit;
             }
             if (!$store_mod->unique($store_name, 0)){
                 //echo json_encode(array("flag"=>"error","error_msg"=>"店铺名已经存在","error_lbl"=>"store_name"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"店铺名已经存在","error_lbl"=>"store_name")).");</script>";
                 exit;
             }

             if($_POST['region_id']<=1){
                 //$this->show_warning('请选择地区<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id")).");</script>";
                 exit;
             }

             $cate_id = intval($_POST['cate_id']);
             if($cate_id<=0){
                 //$this->show_warning('请选择店铺分类<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择店铺分类","error_lbl"=>"cate_id"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择店铺分类","error_lbl"=>"cate_id")).");</script>";
                 exit;
             }
             if(!$_POST["ticheng"] || $_POST["ticheng"]=="" || $_POST["ticheng"]==0){
                 //$this->show_warning('请选择佣金比例<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择佣金比例","error_lbl"=>"ticheng"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择比例","error_lbl"=>"ticheng")).");</script>";
                 exit;
             }

             if($_POST['description2'] ==""){
                 //$this->show_warning('请选择地区<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请输入店铺简短描述","error_lbl"=>"description2")).");</script>";
                 exit;
             }

             if(!$_FILES['image_1']){
                 //$this->show_warning('请选择地区<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请上传手持身份证","error_lbl"=>"image_1")).");</script>";
                 exit;
             }
             if(!$_FILES['image_2']){
                 //$this->show_warning('请选择地区<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择地区","error_lbl"=>"region_id"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请上传营业执照","error_lbl"=>"image_2")).");</script>";
                 exit;
             }

             $shangjialeixing=$_POST["seller_type"];       //商家类型

             if($shangjialeixing ==""){
                 //$this->show_warning('请选择商家类型比例<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"请选择商家类型","error_lbl"=>"shangjialeixing"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请选择商家类型","error_lbl"=>"seller_type")).");</script>";
                 exit;
             }
             $ticheng=0;
             $shangjialeixing+=1;
             if($shangjialeixing == 1 ){
                 $shangjialeixing="changshang_ticheng";
             }
             elseif($shangjialeixing == 2 ){
                 $shangjialeixing="dailishang_ticheng";
             }
             elseif($shangjialeixing == 3 ){
                 $shangjialeixing="lingshoushang_ticheng";
             }
             elseif($shangjialeixing == 4 ){
                 $shangjialeixing="shitidian_ticheng";
                 $ticheng=$_POST["ticheng"];
             }

             //$tichengs=explode("##",$_POST["ticheng"]);


             $scategory_mod =& m('scategory');
             $scategories = $scategory_mod->get_info($cate_id);
             if($shangjialeixing == 4 && $ticheng/100 < $scategories[$shangjialeixing]){
                // $this->show_warning('您选择的佣金比例不能小于店铺的最小佣金比例<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                 //echo json_encode(array("flag"=>"error","error_msg"=>"您选择的佣金比例不能小于店铺的最小佣金比例","error_lbl"=>"ticheng"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"您选择的比例不能小于店铺的最小比例","error_lbl"=>"ticheng")).");</script>";
                 exit;
             }

             $member_mod  =& m('member');
             $data = array(
                 'store_id'     => $store_id,
                 'store_name'   => $_POST['store_name'],
                 'owner_name'   => $_POST['owner_name'],
                 'owner_card'   => $_POST['owner_card'],
                 'region_id'    => $_POST['region_id'],
                 'region_name'  => $_POST['region_name'],
                 'address'      => $_POST['address'],
                 'zipcode'      => $_POST['zipcode'],
                 'tel'          => $_POST['tel'],
                 'sgrade'       => 2,
                 //'apply_remark' => $_POST['apply_remark'],
                 'state'        => 0,
                 //'jibie'        =>$tichengs[0],
                 'ticheng'        =>$ticheng/100,
                 'yyzz'          => $_POST["yyzz"],
                 'add_time'     => gmtime(),
                 'tuoguan'      =>0,
                 'description2' => $_POST["description2"],
                 'seller_type'  => $_POST["seller_type"]
                 //'yaoqingma'     =>empty($_SESSION["invi_code"])?Conf::get('yaoqingma'):base64_decode(trim($_SESSION["invi_code"])),
             );

             if($_POST["description"] && $_POST["description"]!=""){
                 $data["description"] = $_POST["description"];
             }

             $image = $this->_upload_image($store_id);
             if ($this->has_error())
             {
                 //$this->show_warning($this->get_error());
                 //echo json_encode(array("flag"=>"error","error_msg"=>$this->get_error(),"error_lbl"=>"owner_name"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>$this->get_error(),"error_lbl"=>"owner_name")).");</script>";
                 return;
             }

//             if($_POST["seller_type"] == 3){         //if 是实体店
//                 if(!$_FILES['shitidian_img']){
//                     echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>"请上传实体店铺照片","error_lbl"=>"shitidian_img")).");</script>";
//                     exit;
//                 }else{
//                     $shitidian_img = $this->_upload_image2($store_id,"shitidian_img");
//                     if($shitidian_img == ""){
//                         echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请上传实体店铺照片","error_lbl"=>"shitidian_img")).");</script>";
//                         exit;
//                     }
//                     $data["shitidian_img"]    = $shitidian_img;
//
//                     if($shitidian_img != '' ){
//                         import('image.func');
//                         /*生成压缩图*/
//                         $logo_urls = explode('.',$shitidian_img);
//                         $logo_path = $logo_urls[0] . '_' . '280X170' . '.' . $logo_urls[1];
//                         make_thumb(ROOT_PATH . '/' . $shitidian_img, ROOT_PATH .'/' . $logo_path, 280, 170, 90);
//                     }

//                 }
//             }



             // 判断是否已经申请过
             //$state = $this->visitor->get('state');
			
			 $storeState=$store_mod->get(array(
			 				"conditions"=> " store_id=$store_id ",
							'fields' => 'state',				
			 ));
			 
			 $state=$storeState["state"];
             if ($state != '' && $state == STORE_APPLYING)
             {
                 $store_mod->edit($store_id, array_merge($data, $image));
             }
             else
             {
                 $store_mod->add(array_merge($data, $image));
             }

             if ($store_mod->has_error())
             {
                 //$this->show_warning($store_mod->get_error());
                 //echo json_encode(array("flag"=>"error","error_msg"=>$store_mod->get_error(),"error_lbl"=>"owner_name"));
                 echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"error","error_msg"=>$store_mod->get_error(),"error_lbl"=>"owner_name")).");</script>";
                 return;
             }


             $cate_id = intval($_POST['cate_id']);
             $store_mod->unlinkRelation('has_scategory', $store_id);
             if ($cate_id > 0)
             {
                 $store_mod->createRelation('has_scategory', $store_id, $cate_id);
             }

             $store_uploaded_file = $_POST["store_uploaded_file"];
             $storeuploadedfile_mod =& m('storeuploadedfile');
             if($store_uploaded_file && count($store_uploaded_file)>0){
                 for($i=0;$i<count($store_uploaded_file);$i++){
                     $file_date["store_id"] = $store_id;
                     $file_date["file_path"] = $store_uploaded_file[$i];
                     $file_date["dropState"] = 1;
                     $file_date["add_time"] = gmtime();
                     $file_date["if_default"] = 0;
                     $make_app = new Make_imageApp();
                     $make_app->_upload_store_image($file_date["file_path"]);
                     $storeuploadedfile_mod->add($file_date);
                 }
             }
             if($_POST["if_default"] !=""){
                 $storeuploadedfile_mod->db_query("update ".DB_PREFIX."store_image set if_default=0 where store_id=$store_id");
                 $storeuploadedfile_mod->db_query("update ".DB_PREFIX."store_image set if_default=1 where store_id=$store_id and file_path='".$_POST["if_default"]."'");
             }

             $this->send_feed('store_created', array(
                 'user_id'   => $this->visitor->get('user_id'),
                 'user_name'   => $this->visitor->get('user_name'),
                 'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
                 'seller_name'   => $data['store_name'],
             ));
			 
			 $this->_hook('after_opening', array('user_id' => $store_id));
             /*$this->show_message('上传商家资料成功',
                 'index', 'index.php?app=tuiguang');*/
             //echo json_encode(array("flag"=>"ok"));
             echo "<script type=\"text/javascript\">window.parent.callback_uploadStore(".json_encode(array("flag"=>"ok")).");</script>";
		 }		
	}

    /* 上传图片 */
    function _upload_image2($store_id,$file_name)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $file = $_FILES[$file_name];
        if ($file['error'] == UPLOAD_ERR_OK){
            if (empty($file)){
                return "";
            }
            $uploader->addFile($file);
            if (!$uploader->file_info()){
                $this->_error($uploader->get_error());
                return "";
            }
            $uploader->root_dir(ROOT_PATH);
            $up_dir=Conf::get('up_dir');
            $dirname   = $up_dir. '/application';
            list($usec, $sec) = explode(" ", microtime());
            $name=((float)$usec + (float)$sec);
            $tmp=explode(".",$name);
            $time_s=$tmp[1];
            if($time_s == ""){
                $time_s = "00";
            }elseif(strlen($time_s) == 1){
                $time_s = $time_s."0";
            }
            date_default_timezone_set('Asia/Shanghai');
            $sn = date('ymdHis').$time_s;
            $filename  = 'store_' . $store_id.$sn;
            return  $uploader->save($dirname, $filename);
        }
    }

	function tuiguang_member()
	{
	    $user_id = $this->visitor->get('user_id');
        
        /* 当前用户中心菜单 */
        $this->_curitem('tuiguang_member');
        /* 当前所处子菜单 */
        $this->_curmenu('tuiguang_member');
		
		$conditions = "  member.t_id=$user_id and member_type='01'";
		
		if(!empty($_GET["user_name"]) && $_GET["user_name"]!="")
		{
		   $u_name=$_GET["user_name"];
		   $conditions.=" and (user_name like '%$u_name%' or real_name like '%$u_name%')";
		}
		
		if(!empty($_GET["email"]) && $_GET["email"]!="")
		{
		   $email=$_GET["email"];
		   $conditions.=" and email like '%$email%' "; 
		}

        if($_GET["add_time_from"])
        {
            $add_time_from=gmstr2time($_GET["add_time_from"]);
            $conditions.=" and reg_time >='$add_time_from' ";
        }
        if($_GET["add_time_to"])
        {
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            $conditions.=" and reg_time <= '$add_time_to' ";
        }
		
        $page = $this->_get_page();	
		
        $members = $this->_member_mod->find(array(
            'conditions' => $conditions,            
            'fields'=> 'this.*',
            'limit' => $page['limit'],
            'count' => true,
            'order' => "reg_time desc"
        ));

		$page['item_count'] = $this->_member_mod->getCount();
		
        $this->_format_page($page);	                
        $this->assign('page_info', $page);		
		$this->assign('members',$members);
		$this->assign('viewType',"member");

        $this->_config_seo('title', "我的好友" . ' - ' . Lang::get('member_center'));

        $this->display("tuiguang.member.html");
	}
	
	function tuiguang_store()
	{
	    $user_id = $this->visitor->get('user_id');      
        /* 当前用户中心菜单 */
        if($_GET["app"]=="jkxd"){
            $this->_curitem('jkxd_store');
        }else{
            $this->_curitem('tuiguang_member');
        }


		$conditions = " member.t_id=$user_id and member_type='01' and s.state <> 0";
		
		if(!empty($_GET["store_name"]) && $_GET["store_name"]!="")
		{
		   $store_name=$_GET["store_name"];
		   $conditions.=" and (s.store_name like '%$store_name%' or s.owner_name like '%$store_name%')"; 
		}
		
		if(!empty($_GET["region_name"]) && $_GET["region_name"]!="")
		{
		   $region_name=$_GET["region_name"];
		   $conditions.=" and s.region_name like '%$region_name%' "; 
		}
		
        $page = $this->_get_page();

        if($_GET["add_time_from"])
        {
            $add_time_from=gmstr2time($_GET["add_time_from"]);
            $conditions.=" and add_time >='$add_time_from' ";
        }
        if($_GET["add_time_to"])
        {
            $add_time_to=gmstr2time_end($_GET["add_time_to"]);
            $conditions.=" and add_time <= '$add_time_to'";
        }

        $stores = $this->_store_mod->find(array(
            'conditions' => $conditions." and s.dropState=1",
            'fields'=> 'this.*,member.user_name,member.email',
			'join'  => 'belongs_to_user',
            'limit' => $page['limit'],
            'count' => true,
            'order' => "reg_time desc"
        ));

		$page['item_count'] = $this->_store_mod->getCount();
        $this->_format_page($page);	                
        $this->assign('page_info', $page);		
		$this->assign('stores',$stores);
		$this->assign('viewType',"store");

        $region_mod =& m('region');
        $this->assign('regions', $region_mod->get_options(0));

        if($_GET["app"]=="jkxd"){
            $this->display("jkxd.my_store.html");
        }else{
            $this->display("tuiguang.member.html");
        }

        
	}
	
	function shouyi()
	{

	    $user_id = $this->visitor->get('user_id');

        $model_order =& m('order');
        $page = $this->_get_page();
        $user_info = $this->visitor->get();

        $jifenlog_mod =& m('jifenlog');
        $yuelog_mod =& m('yuelog');

        if(!$_GET["orderBy"] || $_GET["orderBy"] == "jine"){

            $buyer_name = $_GET["buyer_name"];
            $seller_name = $_GET["seller_name"];

            $conditions_query = "";
            if($buyer_name != ""){
                $conditions_query.=" and  ord.buyer_name ='$buyer_name'";
            }

            if($seller_name != ""){
                $conditions_query.=" and  ord.seller_name ='$seller_name'";
            }

            $conditions=" and yue_log.type = 6  and EXISTS (select 1 from ecm_order ord where ord.order_sn =yue_log.order_sn and  ord.type ='material' $conditions_query)";

            if($_GET["add_time_from"]){
                $add_time_from=$_GET["add_time_from"];
                $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')>='$add_time_from'";
            }

            if($_GET["order_sn"] != ""){
                $order_sn=$_GET["order_sn"];
                $conditions.=" and order_sn='$order_sn'";
            }


            if($_GET["add_time_to"]){
                $add_time_to=$_GET["add_time_to"];
                $conditions.=" and DATE_FORMAT(yue_log.create_time,'%Y-%m-%d')<='$add_time_to'";
            }
            $page = $this->_get_page();
            $yuelog=$yuelog_mod->find(array(
                'conditions' => " user_id =".$user_id.$conditions,
                'fields'=> 'this.*',
                'count' => true,
                'order' => 'create_time DESC',
                'limit' => $page['limit'],
            ));

            foreach($yuelog as $k=>$v){
                $yuelog[$k]["order_info"]=$model_order->get(array(
                                                                    "conditions"=> "order_sn='".$v["order_sn"]."'",
                                                                    "fields"    =>  "this.*,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.seller_id) AS \"t_id_seller\",
                                                           (SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.buyer_id) AS \"t_id_buyer\""
                ));
            }

            $page['item_count'] = $yuelog_mod->getCount();
            $this->_format_page($page);

            $this->assign('page_info', $page);
            $this->assign('shouyi', $yuelog);
        }else{

            $buyer_name = $_GET["buyer_name"];
            $seller_name = $_GET["seller_name"];
            $conditions=" and jifen_log.type = 6";
            if($buyer_name != ""){
                $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =jifen_log.order_sn and  ord.buyer_name ='$buyer_name')";
            }

            if($seller_name != ""){
                $conditions.=" and EXISTS (select 1 from ecm_order ord where ord.order_sn =jifen_log.order_sn and  ord.seller_name ='$seller_name')";
            }

            if($_GET["order_sn"] != ""){
                $order_sn=$_GET["order_sn"];
                $conditions.=" and order_sn='$order_sn'";
            }

            if($_GET["add_time_from"]){
                $add_time_from=$_GET["add_time_from"];
                $conditions.=" and DATE_FORMAT(jifen_log.create_time,'%Y-%m-%d')>='$add_time_from'";
            }

            if($_GET["add_time_to"]){
                $add_time_to=$_GET["add_time_to"];
                $conditions.=" and DATE_FORMAT(jifen_log.create_time,'%Y-%m-%d')<='$add_time_to'";
            }

            $page = $this->_get_page();
            $jifenlog=$jifenlog_mod->find(array(
                'conditions' => " user_id =".$user_id.$conditions,
                'fields'=> 'this.*',
                'count' => true,
                'order' => 'create_time DESC',
                'limit' => $page['limit'],
            ));

            foreach($jifenlog as $k=>$v){
                $jifenlog[$k]["order_info"]=$model_order->get(array(
                                                                "conditions"=> "order_sn='".$v["order_sn"]."'",
                                                                "fields"    =>  "this.*,(SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.seller_id) AS \"t_id_seller\",
                                                       (SELECT t_id FROM ".DB_PREFIX."member WHERE user_id=order_alias.buyer_id) AS \"t_id_buyer\""
                ));
            }

            $page['item_count'] = $jifenlog_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('shouyi', $jifenlog);
        }

        $this->assign('jifen_shouyi', $jifenlog_mod->getOne("SELECT SUM(jifen) FROM ecm_jifen_log WHERE user_id  = $user_id AND TYPE = 6"));
//        $this->assign('yue_shouyi', $yuelog_mod->getOne("SELECT SUM(jine) FROM ecm_yue_log WHERE user_id  = $user_id AND TYPE = 6"));
//        $memberfinance_mod = & m('memberfinance');
        $user_api = json_decode($this->user_api("","",""));
//        $user_api = $memberfinance_mod->get($user_info['user_id']);
        $shouyi=$user_api->koushui_yue;
        $this->assign('yue_shouyi', $shouyi);
        $this->_config_seo('title', "我的收益" . ' - ' . Lang::get('member_center'));

        $this->_curmenu('shouyi');
        $this->display('shouyi.html');

	}

    function _get_member_submenu()
    {

        $array = array(
			array(
                'name' => 'tuiguang',
                'url' => 'index.php?app=tuiguang&act=tuiguang',
            ),
			array(
                'name' => 'tuiguang_member',
                'url' => 'index.php?app=tuiguang&act=tuiguang_member',
            ),            
            array(
                'name' => 'tuiguang_store',
                'url' => 'index.php?app=tuiguang&act=tuiguang_store',
            ),
            array(
                'name' => 'uploadStore',
                'url' => 'index.php?app=tuiguang&act=uploadStore',
            ),
            array(
                'name' => 'shouyi',
                'url' => 'index.php?app=tuiguang&act=shouyi',
            ),
      
        );
        foreach($array as $k=>$v)
        {
            if($v['name']==$_GET["act"])
            {
                return array(
                                array(
                                    'name' => $v['name'],
                                    'url' => 'index.php?app=tuiguang&act='.$v['name'],
                                ),);
                exit;
            }
        }
        return $array;
    }

    function  get_erweima() {
//        header('Content-type: text/json');
        $url = "https://chart.googleapis.com/chart?cht=qr&chs=220x220&choe=UTF-8&chld=L|2&chl=http://192.168.0.103/reg/c2VsbGVy";

        $curl = curl_init();

// 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_URL, $url);

// 设置header
        curl_setopt($curl, CURLOPT_HEADER, 1);

// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

// 运行cURL，请求网页
        $data = curl_exec($curl);

// 关闭URL请求
        curl_close($curl);

// 显示获得的数据
       var_dump($data) ;

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

    /* 上传图片 */
    function _upload_image($store_id)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $data = array();
        for ($i = 1; $i <= 3; $i++)
        {
            $file = $_FILES['image_' . $i];
            if ($file['error'] == UPLOAD_ERR_OK)
            {
                if (empty($file))
                {
                    continue;
                }
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $this->_error($uploader->get_error());
                    return false;
                }

                $uploader->root_dir(ROOT_PATH);
                $up_dir=Conf::get('up_dir');
                $dirname   = $up_dir. '/application';
                $filename  = 'store_' . $store_id .time().'_' . $i;
                $data['image_' . $i] = $uploader->save($dirname, $filename);
            }
        }
        return $data;
    }


    //帮人注册
    function brzc(){
        /* 当前用户中心菜单 */
        $userId = $this->visitor->get('user_id');
        $flag =  $this->_member_mod->getOne("select count(1) from ecm_store where store_id=$userId and seller_type=3 and dropState=1 and state=1");
        $channel_mod =& m('channel');
        $this->_curitem('brzc');
        $channels=$channel_mod->find(array(
            "conditions"=>" (type=2 or type=3) and status=1 and channel_code!='ZHIFUBAO' and channel_code!='UPOP' and channel_code !='CAIFUTONG'",
            'limit' => 100,
            'count' => true
        ));
        $this->assign('flag', $flag);
        $this->assign('channels', $channels);
        if(!$_POST){
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', "帮人注册" . ' - ' . Lang::get('member_center'));
            $this->display('tuiguang.brzc.html');
        }else{

            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));

            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];
            $confirm_password  = $_POST['confirm_password'];
            $real_name2  = $_POST['real_name2'];
            $code = $_POST['code'];
            $se_code = $_SESSION['brzccode'];

            if($user_name == "" || $password == "" || $confirm_password =="" || $real_name2 == ""){
                $this->display('tuiguang.brzc.html');
                echo "<script>js_fail(\"用户名，密码，确认密码，真实姓名不能为空\");</script>";
                exit;
            }
            if (substr(strtoupper($user_name),0,3) == "DJK") {
                $this->display('tuiguang.brzc.html');
                echo "<script>js_fail(\"二次密码不一致\");</script>";
                exit;
            }

            $list=$this->_member_mod->find("user_name='$user_name' or email='$user_name' or phone_mob='$user_name'");
            if(count($list)>0){
                $this->display('tuiguang.brzc.html');
                echo "<script>js_fail(\"用户名已经存在\");</script>";
                exit;
            }

            /*$list=$this->_member_mod->find("phone_mob='$phone_mob'");
            if(count($list)>0){
                $this->display('tuiguang.brzc.html');
                echo "<script>js_fail(\"手机号码已经存在\");</script>";
                exit;
            }*/

            /*if($code == "" || $se_code == "" || !($code == $se_code)){
                $this->display('tuiguang.brzc.html');
                echo "<script>js_fail(\"验证码错误\");</script>";
                exit;
            }*/

            $data = array("user_name" => $user_name,
                           "password " => md5($password),
                            "t_id"      => $this->visitor->get('user_id'),
                            "reg_time" =>gmtime(),
                            "member_type"=>"01",
                            "email_bind_status"=>0,
                            "real_name" => $real_name2,
                            "nick_name"    =>$user_name,
                        //    "jifen"         =>5              //帮人注册送5积分
            );

            if (is_mobile($user_name)) {
                $data["phone_mob"]  = $user_name;
                $data["phone_mob_bind_status"]  = 0;
            }
            ini_set('date.timezone','Asia/Shanghai');
            //邮箱注册，生成邮箱绑定激活码
            if (is_email($user_name)) {
                $data['email'] = $user_name;
                $data['email_active_code'] = base64_encode(gmtime() . $user_name);

                //发送绑定邮件
                $content = file_get_contents(ROOT_PATH . "/themes/mall/default/brzc_email_content.html");

                $content = str_replace("%s1", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $data['email_active_code'], $content);
                $content = str_replace("%s2", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $data['email_active_code'], $content);

                $content = str_replace("%userName", $user_name, $content);
                $content = str_replace("%tuserName", $this->visitor->get('user_name'), $content);
                $content = str_replace("%jifen", 5, $content);
                $content = str_replace("%password", $password, $content);
                $content = str_replace("%date", date('Y年m月d日',time()), $content);
                include_once(ROOT_PATH . '/app/sendcode.app.php');
                $sender = new SendCodeApp();
                $sender -> send_email($user_name, "欢迎注册成为大集客用户", $content);
            }
            $smsclient = new SMSClient();
            $id=$this->_member_mod->add($data);
            $posbind_mob =& m('posbind');
            //是实体店商家
            if($flag == 1 && $id>0) {
                $bank_card = trim($_POST["bank_card"]);
                $m_phone = $_POST["m_phone"];

                $hiddenbankcard = bank_hidden($bank_card);
                $bank_bind = $posbind_mob->get("bank_card='$hiddenbankcard' and state = 1");

                if ($bank_bind) {
                    $this->display('tuiguang.brzc.html');
                    echo "<script>js_fail(\"银行卡已经存在，请重新绑定！\");</script>";
                    exit;
                }
                if($bank_card != null && $bank_card !=""){
                    $bindData["user_name"]=$user_name;
                    $bindData["bank_name"]=$_POST["bank_name"];
                    $bindData["bank_code"]=$_POST["bank_code"];
                    $bindData["bank_card"]= $bank_card;
                    $bindData["full_bank_card"]= $bank_card;
                    $bindData["real_name"]=$_POST["real_name"];
                    $bindData["shenfenzheng"]=$_POST["shengfenzheng"];
                    $bindData["m_phone"]=$m_phone;
                    ini_set('date.timezone','Asia/Shanghai');
                    $bindData["create_time"]=date('Y-m-d H:i:s',time());
                    $bindData["state"]=1;
                    $bindData["user_id"]=$id;

                    $id = $posbind_mob->add($bindData);
                }
                //发送短信
                if ($m_phone != null && $m_phone != "" && is_mobile($m_phone)) {
                    $content = "您好！您的好友".$this->visitor->get('user_name')."帮您注册成为大集客会员，您的用户名".$user_name."，密码".$password."。,该操作基于对方告知您的情况下，相关纠纷均与大集客无涉，祝使用愉快！【大集客】";
                    $smsclient->sendSMS($m_phone, $content);
                    if ($_POST['test'] == '1') {
                        echo $content; exit;
                    }
                }
            }
            $posbind_mob->commit_transaction();
            $params["type"] = "regist";
            $params["userId"] = $id;
            $params["tId"] =$this->visitor->get('user_id');
            $params["orderId"] ="";
            $params["userName"] ="";
            $params["channelCode"] ="";
            $params["channelName"] ="";
            $params["channelCard"] ="";
            $params["jine"] ="0";
            $params["regionId"] ="0";
            $Client = new HttpClient(JAVA_LOCATION);
            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
            $pageContents = HttpClient::quickPost($url, $params);


            if (is_mobile($user_name)) {
                $content = "您好！您的好友".$this->visitor->get('user_name')."帮您注册为大集客会员，您的用户名".$user_name."，密码".$password."。该操作基于对方告知您的情况下，相关纠纷均与大集客无涉，祝使用愉快！【大集客】";
                $smsclient->sendSMS($user_name, $content);
                if ($_POST['test'] == '1') {
                    echo $content; exit;
                }
            }

            $_POST="";
            if($id>0){
                $this->show_message('brzc_ok',"index","index.php?app=tuiguang&act=brzc&p=tuiguang");
            }else{
                $this->display('tuiguang.brzc.html');
                echo "<script>js_fail(\"注册失败\");</script>";
                exit;
            }
        }
    }

    function down_img(){

//      $downfile = "http://192.168.1.124/index.php?app=tuiguang&act=generateCode";
        $downfile = "https://chart.googleapis.com/chart?cht=qr&chs=220x220&choe=UTF-8&chld=L|2&chl=".SITE_URL."/reg/".$this->visitor->get('user_id').".html";

        ob_clean();
        $fileres = file_get_contents($downfile);
        header("Content-Type: application/force-download");
        header('Content-type: application/x-'.$fileext);
        header('Content-Disposition: attachment; filename='.gmtime().rand(0,9999).'.png');
        echo $fileres;

    }

    function generateCode(){
        include "phpqrcode/phpqrcode.php";
        $dataInfo=$this->visitor->info;
        $value = SITE_URL . "/reg/".base64_encode($dataInfo['user_id']);
        $errorCorrectionLevel = "L";
        $matrixPointSize = "4";
        ob_clean();
        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
    }

    function sendEmail(){
        $email = $_POST['email'];
        if(!is_email($email)){
            echo 'ERROR';
            exit;
        }
        $dataInfo=$this->visitor->info;
        $code = $this -> generate_code(7);
        $_SESSION['brzccode'] = $code;
        $this -> sendzcdemail($email,$dataInfo['user_name'],$code);
    }

    function sendzcdemail($email,$username,$code) {
        //发送绑定邮件
        if ($email != null) {
            $date = date('Y-m-d',time());
            $content = file_get_contents(ROOT_PATH . "/themes/mall/default/brzcRegist_email_content.html");
            $content = str_replace("%s1",$username, $content);
            $content = str_replace("%s2",$code , $content);
            $content = str_replace("%s3",$date , $content);
            include_once(ROOT_PATH . '/app/sendcode.app.php');
            $sender = new SendCodeApp();
            $sender -> send_email($email, "欢迎注册大集客", $content);
        }
    }

    function generate_code($len = 4)
    {
        $chars = '1234567890';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
            $arr[$i] = $chars[$i];
        }
        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }

    function reg_store(){

        if($_GET["success"]){
            $this->assign('success', "success");
            $this->display("tuiguang.reg_store.html");
            exit;
        }

        if(!$_POST)
        {
            $user_id = $this->visitor->get('user_id');

            $this->assign('scategories', $this->_get_scategory_options());
            $this->assign('scategories_ticheng', $this->_get_scategory_min_ticheng());

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $user_id = $this->visitor->get('user_id');
            $userList=$this->_member_mod->find(array
            (
                "conditions"=> " 1=1 and t_id=$user_id and (select count(1) from ".DB_PREFIX."store where store_id=member.user_id)<1 ",
                'fields' => 'user_name,user_id',
            ));


            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
            ));

            $this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_STORE,
                'item_id' => 0,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
            )));
            //extract($this->_get_theme());
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));

            $this->assign('id', 0);

            $init_tichengs2[1]=array();
            for($i=5;$i<=85;$i++){
                $init_tichengs2[1][$i]=$i;
            }

            $this->assign('init_tichengs2',$init_tichengs2);

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->assign('userList', $userList);

            $this->_config_seo('title', "提交商家资料" . ' - ' . Lang::get('member_center'));

            if($_SESSION["files_store"] && $_SESSION["files_store"]!=""){
                $files_store = explode("###",$_SESSION["files_store"]);
                $this->assign('files_store', $files_store);
            }
            $this->assign('ECM_ID', $_COOKIE['ECM_ID']);
            $this->assign('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);

            $this->display("tuiguang.reg_store.html");
        }else
        {
            echo "<meta charset='utf-8' />";
            $user_name= $_POST['user_name'];
            if($user_name == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"用户名不能为空","error_lbl"=>"user_name")).");</script>";
                exit;
            }
            $password= $_POST['password'];
            if($password == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"密码不能为空","error_lbl"=>"password")).");</script>";
                exit;
            }
            $password_confirm= $_POST['password_confirm'];
            if($password_confirm == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"确认密码不能为空","error_lbl"=>"password_confirm")).");</script>";
                exit;
            }

            if($password != $password_confirm){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"二次密码不一致","error_lbl"=>"password_confirm")).");</script>";
                exit;
            }

            $member_mod =& m('member');
            $info = $member_mod->get("user_name='$user_name' or email='$user_name' or phone_mob='$user_name'");
            if (!empty($info)){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"用户名已存在","error_lbl"=>"user_name")).");</script>";
                exit;
            }


            $store_type = $_POST["store_type"];
            $seller_type = $_POST["seller_type"];
            if($store_type == "" || $store_type >1 ){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请选择店铺类型!","error_lbl"=>"store_type")).");</script>";
                exit;
            }
            $region_id=$_POST["region_id"];
            $region_mod =& m('region');
            $data=$region_mod->get_options($region_id);
            if(count($data)>0){
                /*$this->show_warning('diqucuowu');
                return;*/
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"选择地区错误，必须选择到最后一级!","error_lbl"=>"region_id")).");</script>";
                exit;
            }

            $cate_id=$_POST["cate_id"];
            if($cate_id==0){
                /*$this->show_warning('fenleicuowu');
                return;*/
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，必须选择商家分类!","error_lbl"=>"cate_id")).");</script>";
                exit;
            }


            if($_POST['store_name'] == "" ){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，必须输入店铺名!","error_lbl"=>"store_name")).");</script>";
                exit;
            }

            if($_POST['address'] == "" ){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，必须输入地址!","error_lbl"=>"address")).");</script>";
                exit;
            }

            if($_POST['tel'] == "" ){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，必须输入手机号码!","error_lbl"=>"tel")).");</script>";
                exit;
            }

            $store_mod  =& m('store');
            $store_id = $this->visitor->get('user_id');

            if($store_mod->get("store_name='".$_POST['store_name']."' and store_id!=".$store_id)){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，店铺名已存在!","error_lbl"=>"store_name")).");</script>";
                exit;
            }

            $description2 = $_POST["description2"];
            if($description2 == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，请输入店铺简短描述!","error_lbl"=>"description2")).");</script>";
                exit;
            }

            if($seller_type == ""){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"输入错误，请选择商家类型!","error_lbl"=>"seller_type")).");</script>";
                exit;
            }

            $ticheng=$_POST["ticheng"];
            $shangjialeixing=$seller_type;       //商家类型

            $shangjialeixing+=1;

            $scategory_mod =& m('scategory');
            $scategories = $scategory_mod->get_info($cate_id);

            if($shangjialeixing == 1 ){
                $shangjialeixing="changshang_ticheng";
            }
            elseif($shangjialeixing == 2 ){
                $shangjialeixing="dailishang_ticheng";
            }
            elseif($shangjialeixing == 3 ){
                $shangjialeixing="lingshoushang_ticheng";
            }
            elseif($shangjialeixing == 4 ){
                $shangjialeixing="shitidian_ticheng";
            }

            if($ticheng/100 < $scategories[$shangjialeixing]){
                //$this->show_warning('您选择的佣金比例不能小于店铺的最小佣金比例<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                //echo json_encode(array("flag"=>"error","error_msg"=>"您选择的佣金比例不能小于店铺的最小佣金比例","error_lbl"=>"ticheng"));
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"您选择的比例不能小于店铺的最小比例","error_lbl"=>"ticheng")).");</script>";
                exit;
            }


            $userData = array(
                "user_name"   => $user_name,
                "password"    => md5($password),
                "member_type" => "01",
                "nick_name"   => $user_name,
                "reg_ip"      => real_ip(),
                "reg_time"   => gmtime(),
                "phone_mob"  => $_POST["tel"]
            );


            $member_mod  =& m('member');
            $store_id = $member_mod->add($userData);

            if($store_id<1){
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"提交失败，服务器繁忙请稍候重试！","error_lbl"=>"user_name")).");</script>";
                exit;
            }

            $data = array(
                'store_id'     => $store_id,
                'store_name'   => $_POST['store_name'],
                //'owner_name'   => $_POST['owner_name'],
                //'owner_card'   => $_POST['owner_card'],
                'region_id'    => $_POST['region_id'],
                'region_name'  => $_POST['region_name'],
                'address'      => $_POST['address'],
                'zipcode'      => $_POST['zipcode'],
                'tel'          => $_POST['tel'],
                'tel2'          => $_POST['tel2'],
                'sgrade'       => $sgrade['grade_id'],
                //'apply_remark' => $_POST['apply_remark'],
                // 'state'        => $sgrade['need_confirm'] ? 0 : 1,
                'state'        =>0,  //新开的店铺统一需要审核
                'add_time'     => gmtime(),
                'store_type'  => $store_type,
                'description2' => $description2,
                'seller_type' => $seller_type,
                'ticheng'        =>$ticheng/100,
                //'yaoqingma'     =>empty($_SESSION["invi_code"])?Conf::get('yaoqingma'):base64_decode(trim($_SESSION["invi_code"])),
            );


            if($_POST["description"] && $_POST["description"]!=""){
                $data["description"] = $_POST["description"];
            }

            if($store_type == 0){
                if($_POST['owner_name'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请输入店主姓名","error_lbl"=>"owner_name")).");</script>";
                    exit;
                }
                if($_POST['owner_card'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请输入身份证号","error_lbl"=>"owner_card")).");</script>";
                    exit;
                }
                if($_FILES['image_1'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请上传身份证","error_lbl"=>"image_1")).");</script>";
                    exit;
                }
                $data["owner_name"] = $_POST['owner_name'];
                $data["owner_card"] = $_POST['owner_card'];

                $image_1 = $this->_upload_image2($store_id,"image_1");

                if($image_1 == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"上传身份证失败，服务器繁忙","error_lbl"=>"image_1")).");</script>";
                    exit;
                }

                $data["image_1"]    = $image_1;

            }else{

                if($_POST['yyzz'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请输入企业名称","error_lbl"=>"yyzz")).");</script>";
                    exit;
                }
                if($_POST['owner_name_1'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请输入法人代表","error_lbl"=>"owner_name_1")).");</script>";
                    exit;
                }
                if($_FILES['image_1_1'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请上传法人代表身份证","error_lbl"=>"image_1_1")).");</script>";
                    exit;
                }
                if($_FILES['image_2'] == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请上传营业执照副本","error_lbl"=>"image_2")).");</script>";
                    exit;
                }

                $data["yyzz"] = $_POST['yyzz'];
                $data["owner_name"] = $_POST['owner_name_1'];

                $image_1_1 = $this->_upload_image2($store_id,"image_1_1");
                if($image_1_1 == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"上传法人代表身份证失败，服务器繁忙","error_lbl"=>"image_1_1")).");</script>";
                    exit;
                }
                $image_2 =$this->_upload_image2($store_id,"image_2");
                if($image_2 == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"上传营业执照副本失败，服务器繁忙","error_lbl"=>"image_2")).");</script>";
                    exit;
                }

                $data["image_1"]    =  $image_1_1;
                $data["image_2"] = $image_2;
            }

            if($_FILES['store_logo'] && $_FILES['store_logo']['name']!=""){
                $store_logo = $this->_upload_image2($store_id,"store_logo");
                if($store_logo == ""){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"上传店铺logo失败！","error_lbl"=>"store_logo")).");</script>";
                    exit;
                }
                $data["store_logo"] = $store_logo;
            }

            if($_POST["seller_type"] == 3){         //if 是实体店
                if(!$_FILES['shitidian_img']){
                    echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请上传实体店铺照片","error_lbl"=>"shitidian_img")).");</script>";
                    exit;
                }else{
                    $shitidian_img = $this->_upload_image2($store_id,"shitidian_img");
                    if($shitidian_img == ""){
                        echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"请上传实体店铺照片","error_lbl"=>"shitidian_img")).");</script>";
                        exit;
                    }
                    $data["shitidian_img"]    = $shitidian_img;
                    if($shitidian_img != '' ){
                        import('image.func');
                        /*生成压缩图*/
                        $logo_urls = explode('.',$shitidian_img);
                        $logo_path = $logo_urls[0] . '_' . '280X170' . '.' . $logo_urls[1];
                        make_thumb(ROOT_PATH . '/' . $shitidian_img, ROOT_PATH .'/' . $logo_path, 280, 170, 90);
                    }
                }
            }

            /*$image = $this->_upload_image($store_id);
            if ($this->has_error())
            {
                echo "<script type=\"text/javascript\">document.domain ='".DOMAIN."';window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>$this->get_error(),"error_lbl"=>"store_name")).");</script>";
                exit;
            }*/

            $cookie_t_id  = ecm_getcookie("t_id");
            $session_t_id = $_SESSION['t_id'];
            $t_id =base64_decode($session_t_id!=""? $session_t_id : $cookie_t_id);
            //验证邀请码
            $member_mod  =& m('member');
            $dateInfo=$member_mod->get(array(
                'conditions' => "1=1 AND user_name ='$t_id'",
                'fields' => 'user_name,user_id',));


//            if(!empty($dateInfo))
//            {
//                $member_mod->edit(array("user_id"=>$store_id),array("t_id"=>$dateInfo["user_id"]));
//            }

            /* 判断是否已经申请过 */
            $state = $this->visitor->get('state');
            if ($state != '' && $state == STORE_APPLYING)
            {
                $store_mod->edit($store_id, $data);
            }
            else
            {
                $store_mod->add($data);
            }

            if ($store_mod->has_error())
            {
                /*$this->show_warning($store_mod->get_error());
                return;*/

                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>$store_mod->get_error(),"error_lbl"=>"store_name")).");</script>";
                exit;

            }


            $cate_id = intval($_POST['cate_id']);
            $store_mod->unlinkRelation('has_scategory', $store_id);
            if ($cate_id > 0){
                $store_mod->createRelation('has_scategory', $store_id, $cate_id);
            }

            $store_uploaded_file = $_POST["store_uploaded_file"];
            if($store_uploaded_file && count($store_uploaded_file)>0){
                $storeuploadedfile_mod =& m('storeuploadedfile');
                for($i=0;$i<count($store_uploaded_file);$i++){
                    $file_date["store_id"] = $store_id;
                    $file_date["file_path"] = $store_uploaded_file[$i];
                    $file_date["dropState"] = 1;

                    $file_date["add_time"] = gmtime();
                    $if_default = 0;
                    if($_POST["if_default"] == $store_uploaded_file[$i]){
                        $if_default =1;
                    }
                    $file_date["if_default"] = $if_default;
                    $make_app = new Make_imageApp();
                    $make_app->_upload_store_image($file_date["file_path"]);
                    $storeuploadedfile_mod->add($file_date);
                }
            }

            $params = null;
            $params["type"] = "regist";
            $params["userId"] = $store_id;
            $params["tId"] = ($t_id>0) ? $t_id : 0;
            $params["orderId"] ="";
            $params["userName"] ="";
            $params["channelCode"] ="";
            $params["channelName"] ="";
            $params["channelCard"] ="";
            $params["jine"] ="0";
            $params["regionId"] ="0";
            $Client = new HttpClient(JAVA_LOCATION);
            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
            $pageContents = HttpClient::quickPost($url, $params);



            $this->_do_login($store_id);
            $store_mod->commit_transaction();
            if ($sgrade['need_confirm'])
            {
                /*$this->show_message('apply_ok',
                    'index', 'index.php');*/
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"ok")).");</script>";
                exit;
            }
            else
            {
                $this->send_feed('store_created', array(
                    'user_id'   => $this->visitor->get('user_id'),
                    'user_name'   => $this->visitor->get('user_name'),
                    'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
                    'seller_name'   => $data['store_name'],
                ));
                $this->_hook('after_opening', array('user_id' => $store_id));
                /*$this->show_message('store_opened',
                    'index', 'index.php');*/
                echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"ok")).");</script>";
                exit;
            }
        }
    }

}
?>
