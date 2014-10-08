<?php
define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);

define('SMALL_STORE_LOGO_WIDTH',100);
define('SMALL_STORE_LOGO_HEIGHT',100);

define('MIDDLE_STORE_LOGO2_WIDTH',60);
define('MIDDLE_STORE_LOGO2_HEIGHT',60);


class My_storeApp extends StoreadminbaseApp
{
    var $_store_id;
    var $_store_mod;
    var $_uploadedfile_mod;
    var $_store_uploadfile_mod;

    function __construct()
    {
        $this->My_storeApp();
    }
    function My_storeApp()
    {
        parent::__construct();

        if($this->visitor->get("member_type") == "04" &&  $this->visitor->get("user_name") =="djk11001" && $_GET["app"] == "service"){
            $this->_store_id = $_GET["id"];
        }else{
            $this->_store_id  = intval($this->visitor->get('manage_store'));
        }
        $this->_store_mod =& m('store');
        $this->_uploadedfile_mod = &m('uploadedfile');
        $this->_store_uploadfile_mod = &m('storeuploadedfile');
        include_once(ROOT_PATH . '/app/make_image.app.php');
    }

    function index(){
        $member_mod =&m('member');
        $user = $member_mod->get_info($this->visitor->get('user_id'));
        $this->assign("user",$user);

        $tmp_info = $this->_store_mod->get(array(
            'conditions' => $this->_store_id,
            'join'       => 'belongs_to_sgrade',
            'fields'     => 'domain, functions',
        ));
        $functions = $tmp_info['functions'] ? explode(',', $tmp_info['functions']) : array();
        $subdomain_enable = false;

        if (ENABLED_SUBDOMAIN && in_array('subdomain', $functions))
        {
            $subdomain_enable = true;
        }
        $store = $this->_store_mod->get_info($this->_store_id);

        if ($store['tel2']&&strpos($store['tel2'],',')) {
            $store['tel2'] = substr($store['tel2'],0,strrpos($store['tel2'],','));
            $this->assign('tel_add',substr($store['tel2'],strrpos($store['tel2'],',')+1,strlen($store['tel2'])));
        }
        if (!IS_POST)
        {
            //传给iframe参数belong, item_id
            $this->assign('belong', BELONG_STORE);
            $this->assign('id', $this->_store_id);


            foreach ($functions as $k => $v)
            {
                $store['functions'][$v] = $v;
            }

            $this->assign('store', $store);
            $this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_STORE,
                'item_id' => $this->_store_id,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
            )));

            extract($this->_get_theme());
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));



            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));
            //echo "test";exit;
            /* 属于店铺的附件 */
            $files_belong_store = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = ' . $this->_store_id . ' AND belong = ' . BELONG_STORE . ' AND item_id =' . $this->_store_id,
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));
            //商铺所属分类
            $scgcate_mode = & m('scategory');
            $category_store = & m ('categorystore');
            $sql = "SELECT parent_id,cate_id,cate_name FROM {$scgcate_mode->table}  WHERE cate_id = (SELECT cate_id FROM {$this->_store_mod->table} AS s,{$category_store->table} AS cs WHERE cs.store_id = s.store_id AND s.store_id =".$this->_store_id.")";
            $res = $scgcate_mode->db->getAll($sql);
            $str = "";
            foreach ($res as $cate) {
                $parent = $scgcate_mode->get($cate['parent_id']);
                $str = $parent['cate_name'];
                $str .=" > ".$cate['cate_name'];
            }
            $this->assign('scategory_str',$str);
//            exit;
//        $scatory = $this->_store_mod->get(array(
//            'conditions'=> $this->_store_id,
//            'join'  => 'has_scategory',
//            'fields' => 'sgrade.cate_id,sgrade.cate_name',
//        ));
            /* 当前页面信息 */
            $this->_curlocal(
                LANG::get('member_center'), 'index.php?app=member', LANG::get('my_store')
            );
            $this->_curitem('my_store');
            $this->_curmenu('my_store');
            $this->import_resource('jquery.plugins/jquery.validate.js,mlselection.js');
            $this->assign('files_belong_store', $files_belong_store);
            $this->assign('subdomain_enable', $subdomain_enable);
            $this->assign('domain_length', Conf::get('subdomain_length'));
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_store'));

            $this->display('my_store.index.html');
        }
        else
        {
            import('image.func');
            $subdomain = $tmp_info['domain'];
            if ($subdomain_enable && !$tmp_info['domain'])
            {
                $subdomain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
                if (!$this->_store_mod->check_domain($subdomain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
                {
                    $this->show_warning($this->_store_mod->get_error());

                    return;
                }
            }
            $data = $this->_upload_files();
            if ($data === false)
            {
                return;
            }
            else //删除冗余图标
            {
                if($store['store_logo'] != '' && $data['store_logo'] != '')
                {
                    $store_logo_old = pathinfo($store['store_logo']);
                    $store_logo_new = pathinfo($data['store_logo']);

                    if($store_logo_old['extension'] != $store_logo_new['extension'])
                    {
                        // unlink($store['store_logo']);
                        $this->deleteFileInfo($store['store_logo']);
                    }
                }

                if($store['store_banner'] != '' && $data['store_banner'] != '')
                {
                    $store_banner_old = pathinfo($store['store_banner']);
                    $store_banner_new = pathinfo($data['store_banner']);
                    if($store_banner_old['extension'] != $store_banner_new['extension'])
                    {
                        //unlink($store['store_banner']);
                        $this->deleteFileInfo($store['store_banner']);
                    }
                }
            }

            $store_logo_url = $data['store_logo'];
            $store_banner_url = $data['store_banner'];

            if($store_logo_url != '' ){

                /*$sizes = array(
                    SMALL_STORE_LOGO_WIDTH . 'X' . SMALL_STORE_LOGO_HEIGHT,
                    SMALL_STORE_LOGO2_WIDTH . 'X' . SMALL_STORE_LOGO2_HEIGHT,
                );
                $logo_urls = explode('.',$store_logo_url);
                for($i = 0; $i < count($sizes); $i++){
                    $path = $logo_urls[0] . '_' . $sizes[$i] . '.' . $logo_urls[1];
                    $size = explode('X', $sizes[$i]);
                    make_thumb(ROOT_PATH . '/' . $store_logo_url, ROOT_PATH .'/' . $path, $size[0], $size[1], THUMB_QUALITY);
                }*/
                $logo_urls = explode('.',$store_logo_url);
                $logo_path = $logo_urls[0] . '_' . '100X100' . '.' . $logo_urls[1];
                if(!file_exists(DIS_PATH .'/' . $logo_path)){
                    make_thumb(ROOT_PATH . '/' . $store_logo_url, ROOT_PATH .'/' . $logo_path, 100, 100, 90);
                }

                $logo_path = $logo_urls[0] . '_' . '60X60' . '.' . $logo_urls[1];
                if(!file_exists(DIS_PATH .'/' . $logo_path)){
                    make_thumb(ROOT_PATH . '/' . $store_logo_url, ROOT_PATH .'/' . $logo_path, 60, 60, 90);
                }

            }
            if($store_banner_url != '' ){
                $banner_urls = explode('.',$store_banner_url);
                $banner_path = $banner_urls[0] . '_' . '1000X120' . '.' . $banner_urls[1];
                if(!file_exists(DIS_PATH .'/' . $banner_path)){

                    make_thumb(ROOT_PATH . '/' . $store_banner_url, ROOT_PATH .'/' . $banner_path, 1000, 120, 90);
                }/*
                $banner_urls = explode('.',$store_banner_url);
                $banner_path = $banner_urls[0] . '_' . '1000X120' . '.' . $banner_urls[1];
                make_thumb(ROOT_PATH . '/' . $store_banner_url, ROOT_PATH .'/' . $banner_path, 1000, 120, 90);*/
            }

            $address = strval($_POST["address"]);
            $address = str_replace(' ' ,'',$address);
            $region_name = $_POST["region_name"];
            $region_name = str_replace("	" ,'',$region_name);
            $tel2 = $_POST['tel2'];
            $tel_add =$_POST['tel_add'];
            if ($tel_add) {
                $tel2.=",".$tel_add;
            }
            $latLng = $this->get_latitude_longitude_by_address($address,$region_name);
            $data = array_merge($data, array(
                'store_name' => $_POST['store_name'],
                'region_id'  => $_POST['region_id'],
                'region_name'=> $region_name,
                'description'=> $_POST['description'],
                'tag'=> $_POST['tag'],
                'pay_type'=> $_POST['pay_type'],
                'gongjiao_info' =>$_POST['gongjiao_info'],
                'address'    => $address,
                'description2' =>$_POST['description2'],
                'tel'        => $_POST['tel'],
                'tel2'        => $tel2,
                'im_ww'      => $_POST['im_ww'],
                'domain'     => $subdomain,
                'enable_groupbuy'   => $_POST['enable_groupbuy'],
                'enable_radar'      => $_POST['enable_radar'],
                'yye_time' => $_POST['add_time']."--".$_POST['end_time'],
                'latitude'    => $latLng['latitude'],
                'longitude'    => $latLng['longitude'],
            ));
            $this->_store_mod->edit($this->_store_id, $data);
            if($this->visitor->get("member_type") == "04" &&  $this->visitor->get("user_name") =="djk11001" && $_GET["app"] == "service"){
                header("Location: index.php?app=service&act=store_edit&id=".$this->_store_id);
            }else{
                header("Location: index.php?app=my_store&p=seller");
            }
            //$this->show_message('edit_ok');
        }
    }

    function check_img() {

        $store_logo_file = $_GET['store_logo'];

            $store_logo_img =  getimagesize($store_logo_file);

            $weight = $store_logo_img["0"];
            $height = $store_logo_img["1"];

        $store_bananer_file = $_GET['store_bananer'];
        echo "11";
    }

    function update_im_msn()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        $this->_store_mod->edit($this->_store_id, array('im_msn' => $id));
        header("Location: index.php?app=my_store");
        exit;
    }

    function drop_im_msn()
    {
        $this->_store_mod->edit($this->_store_id, array('im_msn' => ''));
        header("Location: index.php?app=my_store");
        exit;
    }

    function _get_member_submenu()
    {
        return array(
            array(
                'name' => 'my_store',
                'url'  => 'index.php?app=my_store',
            ),
            array(
                'name' => 'my_store',
                'url'  => 'index.php?app=my_store',
            ),
        );
    }

    /**
     * 上传文件
     *
     */
    function _upload_files()
    {
        import('uploader.lib');
        $data      = array();
        /* store_logo */
        $file = $_FILES['store_logo'];
        $up_dir=Conf::get('up_dir');
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_STORE_LOGO); // 20KB
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
                return false;
            }
            $uploader->root_dir(ROOT_PATH);
            $data['store_logo'] = $uploader->save($up_dir, $this->_store_id.gmtime().'_store_logo');
        }

        /* store_banner */
        $file = $_FILES['store_banner'];
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_STORE_BANNER); // 200KB
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
                return false;
            }
            $uploader->root_dir(ROOT_PATH);
            $data['store_banner'] = $uploader->save($up_dir, $this->_store_id.gmtime().'_store_banner');
        }

        return $data;
    }
        /* 异步删除附件 */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;

        if($this->visitor->get("member_type") == "04" &&  $this->visitor->get("user_name") =="djk11001"){
            $this->_store_id  = isset($_GET['store_id']) ? intval($_GET['store_id']):0;
        }
        $file = $this->_store_uploadfile_mod->get($file_id);
        if ($file_id >0  && $file['store_id'] == $this->_store_id){
            $sql = "update {$this->_store_uploadfile_mod->table} set dropState =0 where file_id =".$file_id." and store_id=".$this->_store_id;
            if($this->_store_uploadfile_mod->db->query($sql)) {
                echo "1";
            } else {
                echo "2";
            }
        } else {
            echo "0";
        }
    }

    function set_image() {
        $store = $this->_store_mod->get_info($this->_store_id);
        if(!$_POST){
            $this->_curitem('my_store');
            $this->_curmenu('my_store');
            $desc_images = $this->_store_uploadfile_mod->find(array(
                'conditions'=>'store_id='.$this->_store_id.' and dropState > 0'
            ));
            $this->assign('ECM_ID', $_COOKIE['ECM_ID']);
            $this->assign('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
            $this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_STORE,
                'item_id' => $this->_store_id,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'spanButtonPlaceholder',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
            )));
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));
//            $store_id = $this->_store_id;
//            $shitidian_img = $this->_upload_image2($store_id,"shitidian_img");
//
//            if($shitidian_img != '' ){
//                import('image.func');
//                /*生成压缩图*/
//                $logo_urls = explode('.',$shitidian_img);
//                $logo_path = $logo_urls[0] . '_' . '280X140' . '.' . $logo_urls[1];
//                make_thumb(ROOT_PATH . '/' . $shitidian_img, ROOT_PATH .'/' . $logo_path, 280, 140, 90);
//            }

            $this->assign("desc_image",$desc_images);
            $this->assign("store",$store);
            $this->display("my_store.image.html");
        }else{
            $store_uploaded_file = $_POST["store_uploaded_file"];
            $image_names = $_POST['image_name'];
            $file_ids = $_POST['file_id'];
            if($store_uploaded_file && count($store_uploaded_file)>0){
                $storeuploadedfile_mod =& m('storeuploadedfile');
                for($i=0;$i<count($store_uploaded_file);$i++){

                    $file_date["store_id"] = $this->_store_id;
                    $file_date["file_path"] = $store_uploaded_file[$i];
                    $file_date["dropState"] = 2;
                    $file_date["image_name"]=$image_names[$i];
                    $file_date["add_time"] = gmtime();
                    $if_default = 0;

                    if($_POST["if_default"] == $store_uploaded_file[$i]){
                        $if_default= 1;
                    }
                    $file_date["if_default"] = $if_default;
                    $make_app = new Make_imageApp();
                    $make_app->_upload_store_image($file_date["file_path"]);
                    if ($file_ids[$i]>0) {
                        $file_id = $storeuploadedfile_mod->get(array(
                            'conditions'=>'file_id='.$file_ids[$i].' and store_id='.$this->_store_id));
                        if ($file_id) {
                            $storeuploadedfile_mod->edit($file_id['file_id'],$file_date);
                        }
                    } else {
                        $storeuploadedfile_mod->add($file_date);
                    }
                }
                $email = '2880323930@qq.com';
                include_once(ROOT_PATH . '/app/sendcode.app.php');
                $sender = new SendCodeApp();
                $sender -> send_email($email, '实体店商家图片修改', '商家:'.$store['store_name']."的店面照有图片修改！");
            }
            $this->show_message('edit_ok');
        }

    }

    function shenhe() {
        $file_id = $_GET['file_id'];

        if  ($file_id) {
            $file_ids = explode(",",$file_id);
        }
        if($file_ids && count($file_ids)>0){
            $storeuploadedfile_mod =& m('storeuploadedfile');
            foreach($file_ids as $file_id) {
              $storeuploadedfile_mod->edit($file_id, array("dropState"=>1));
            }
            echo "0";
        }

    }

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
}

?>
