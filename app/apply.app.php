<?php

/* 申请开店 */
class ApplyApp extends MallbaseApp
{

    function __construct()
    {
        $this->ApplyApp();
    }
    function ApplyApp() {
        parent::__construct();
        include_once(ROOT_PATH . '/app/make_image.app.php');
    }
    function index()
    {

        if($_GET["success"]){
            $this->assign('success', "success");
            $this->display("apply.step2.html");
            exit;
        }

        $store_mod =& m('store');
        $store=$store_mod->get(array(
            'conditions' =>" s.store_id=".$this->visitor->get('user_id'),
            'fields'      =>" this.*,
                                   (SUBSTRING_INDEX(s.ticheng * 100,'.',1)) as ticheng2,
                                   (select user_name from ".DB_PREFIX."member where user_id=s.store_id)        as user_name,
                                   (select cate_id from ".DB_PREFIX."category_store where store_id=s.store_id) as cate_id
                                   ",
        ));

        if($store>0 && $store["dropState"]==0){
            $this->show_warning('您的店铺已经关闭');
            return;
        }

        $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        /* 判断是否开启了店铺申请 */
        if (!Conf::get('store_allow'))
        {
            $this->show_warning('apply_disabled');
            return;
        }

        /* 只有登录的用户才可申请 */
        if (!$this->visitor->has_login)
        {
            $this->login();
            return;
        }




        /* 已申请过或已有店铺不能再申请 */
        if ($store)
        {
            if ($store['state'] && $store['state'] !=3)
            {
                $this->show_warning('user_has_store');
                return;
            }
            else
            {
                if ($step != 2)
                {
                    $this->show_warning('user_has_application');
                    return;
                }
            }
        }
        $sgrade_mod =& m('sgrade');

        switch ($step)
        {
            case 1:
                $sgrades = $sgrade_mod->find(array(
                    'order' => 'sort_order',
                ));
                foreach ($sgrades as $key => $sgrade)
                {
                    if (!$sgrade['goods_limit'])
                    {
                        $sgrades[$key]['goods_limit'] = LANG::get('no_limit');
                    }
                    if (!$sgrade['space_limit'])
                    {
                        $sgrades[$key]['space_limit'] = LANG::get('no_limit');
                    }
                    $arr = explode(',', $sgrade['functions']);
                    $subdomain = array();
                    foreach ( $arr as $val)
                    {
                        if (!empty($val))
                        {
                            $subdomain[$val] = 1;
                        }
                    }
                    $sgrades[$key]['functions'] = $subdomain;
                    unset($arr);
                    unset($subdomain);
                }
                $this->assign('domain', ENABLED_SUBDOMAIN);
                $this->assign('sgrades', $sgrades);

                $this->_config_seo('title', Lang::get('title_step1') . ' - ' . Conf::get('site_title'));
                $this->display('apply.step1.html');
                break;
            case 2:
                $sgrade_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                $sgrade = $sgrade_mod->get($sgrade_id);
                if (empty($sgrade))
                {
                    $this->show_message('request_error',
                        'back_step1', 'index.php?app=apply');
                         exit;
                }

                if (!IS_POST)
                {
                    $region_mod =& m('region');
                    $this->assign('site_url', site_url());
                    $this->assign('regions', $region_mod->get_options(0));
                    $this->assign('scategories', $this->_get_scategory_options());
                    $this->assign('scategories_ticheng', $this->_get_scategory_min_ticheng());


                    $init_tichengs2[1]=array();
                    for($i=5;$i<=85;$i++){
                        $init_tichengs2[1][$i]=$i;
                    }
                    $this->assign('init_tichengs2',$init_tichengs2);

                    /* 导入jQuery的表单验证插件 */
                    $this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));

                    $this->_config_seo('title', Lang::get('title_step2') . ' - ' . Conf::get('site_title'));
                    $this->assign('store', $store);
                    $scategory = $store_mod->getRelatedData('has_scategory', $this->visitor->get('user_id'));
                    if ($scategory)
                    {
                        $scategory = current($scategory);
                    }

                    $this->assign('editor_upload', $this->_build_upload(array(
                        'obj' => 'EDITOR_SWFU',
                        'belong' => BELONG_STORE,
                        'item_id' => $this->visitor->get('user_id'),
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

                    $this->assign('ECM_ID', $_COOKIE['ECM_ID']);
                    $this->assign('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);

                    $storeuploadedfile_mod =& m('storeuploadedfile');
                    $storeuploadedfile = $storeuploadedfile_mod->find("store_id=".$this->visitor->get('user_id')." and dropState=1");
                    if($storeuploadedfile && count($storeuploadedfile)>0){
                        $this->assign('storeuploadedfile', $storeuploadedfile);
                    }

                    $this->assign('scategory', $scategory);
                    $this->display('apply.step2.html');
                }
                else
                {
                    echo "<meta charset='utf-8' />";
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

                    $ticheng =0;

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
                        $ticheng=$_POST["ticheng"];
                        if($ticheng/100 < $scategories[$shangjialeixing]){
                            //$this->show_warning('您选择的佣金比例不能小于店铺的最小佣金比例<br><br><a href="javascript:history.go(-1)">返回上一页</a>');
                            //echo json_encode(array("flag"=>"error","error_msg"=>"您选择的佣金比例不能小于店铺的最小佣金比例","error_lbl"=>"ticheng"));
                            echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"您选择的比例不能小于店铺的最小比例","error_lbl"=>"ticheng")).");</script>";
                            exit;
                        }
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

                    if($_FILES['store_logo'] && $_FILES['store_logo']['name']!=""){
                        $store_logo = $this->_upload_image2($store_id,"store_logo");
                        if($store_logo == ""){
                            echo "<script type=\"text/javascript\">window.parent.callback_applystore(".json_encode(array("flag"=>"error","error_msg"=>"上传店铺logo失败！","error_lbl"=>"store_logo")).");</script>";
                            print_r($_FILES['store_logo']);
                            exit;
                        }
                        $data["store_logo"] = $store_logo;
                    }

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

                    /*根据address ,region_name通过百度地图api上获取经纬度*/
                    $array = $this->get_latitude_longitude_by_address($data["address"],$data["region_name"]);
                    if(count($array) != 0 && $array["latitude"] != "" && $array["longitude"] != ""){
                        $data["latitude"] = $array["latitude"];
                        $data["longitude"] = $array["longitude"];
                    }

                    /* 判断是否已经申请过 */
                    $state = $this->visitor->get('state');

                    if(($state && $state == STORE_APPLYING) ||($state && $state == STORE_UNPASS )) {
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
                    if ($cate_id > 0)
                    {
                        $store_mod->createRelation('has_scategory', $store_id, $cate_id);
                    }
                    $this->_do_login($store_id);

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
                break;
            default:
                header("Location:index.php?app=apply&step=1");
                break;
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

    function check_name()
    {
        $store_name = empty($_GET['store_name']) ? '' : trim($_GET['store_name']);
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);

        $store_mod =& m('store');
        if (!$store_mod->unique($store_name, $store_id))
        {
            echo ecm_json_encode(false);
            return;
        }
        echo ecm_json_encode(true);
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

                $filename  = 'store_' . $store_id.gmtime() . '_' . $i;
                $data['image_' . $i] = $uploader->save($dirname, $filename);
            }
        }
        return $data;
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
}

?>
