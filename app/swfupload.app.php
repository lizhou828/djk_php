<?php

define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);


define('INDEX_TOP_WIDTH',180);
define('INDEX_TOP_HEIGHT',190);

define('INDEX_CENTER_WIDTH',160);
define('INDEX_CENTER_HEIGHT',160);

define('INDEX_LEFT_WIDTH',80);
define('INDEX_LEFT_HEIGHT',80);

define('GOOD_BIG_WIDTH',310);
define('GOOD_BIG_HEIGHT',310);

define('NEW_GOOD_SMALL_WIDTH',100);
define('NEW_GOOD_SMALL_HEIGHT',100);

define('NEW_GOOD_MIDDLE_WIDTH',220);
define('NEW_GOOD_MIDDLE_HEIGHT',220);


define('NEW_GOOD_BIG_WIDTH',350);
define('NEW_GOOD_BIG_HEIGHT',350);

define('NEWS_GOOD_MIDDLE_WIDTH',280);
define('NEWS_GOOD_MIDDLE_HEIGHT',170);

define('BDSH_INDEX_GOOD_WIDTH',280);
define('BDSH_INDEX_GOOD_HEIGHT',200);


define('BDSH_GOOD_DETAIL_WIDTH',340);
define('BDSH_GOOD_DETAIL_HEIGHT',240);

define('BDSH_GOOD_LIST_WIDTH',150);
define('BDSH_GOOD_LIST_HEIGHT',90);

define('BDSH_GOOD_DETAIL1_WIDTH',280);
define('BDSH_GOOD_DETAIL1_HEIGHT',170);

define('BDSH_GOOD_LIST1_WIDTH',220);
define('BDSH_GOOD_LIST1_HEIGHT',220);


/**
 *    swfupload批量上传控制器
 *
 *    @author    Hyber
 *    @usage    none
 */

class SwfuploadApp extends StoreadminbaseApp
{
    var $belong; // 上传的文件所属的模型
    var $mod_uploadedfile; //上传文件模型
    var $mod_goods; //商品模型
    var $mod_goods_image; //商品相册模型
    var $item_id = 0; // 所属模型的ID
    var $save_path; // 储存路径
    var $store_id; // 店铺ID
    var $instance = null; //实例（如同时商品可以有两个实例:相册和描述）
    function __construct()
    {
        $this->SwfuploadApp();
    }
    function SwfuploadApp()
    {
        /* 建立会话 */
        if (isset($_POST["ECM_ID"]) && isset($_POST['HTTP_USER_AGENT']))
        {
            $_COOKIE['ECM_ID'] = $_POST["ECM_ID"];
            $_SERVER['HTTP_USER_AGENT'] = $_POST['HTTP_USER_AGENT'];
        }
        else
        {
            $this->json_error('no_post_params_authorize');
            exit();
        }

        parent::__construct();

        /* 初始化 */

        /* 归属 */
        if (isset($_POST['belong']))
        {
            $this->belong = $_POST['belong'];
        }
        else
        {
            $this->json_error('no_post_param_belong');
            exit();
        }

        /* 定位 */
        if (isset($_POST['item_id'])){
            $this->item_id = $_POST['item_id'];
        }
        else
        {
            $this->json_error('no_post_param_item_id');
            exit();
        }

        /* 实例 */
        if (isset($_GET['instance']))
        {
            $this->instance = $_GET['instance'];
        }


        $this->store_id = $this->visitor->get('manage_store');

        if($_POST["up_store_id"]){
            $this->store_id= $_POST["up_store_id"];
        }

        switch ($this->belong)
        {
            case BELONG_ARTICLE :   $this->save_path = 'data/files/store_' . $this->store_id . '/article';
            break;
            case BELONG_STORE :     $this->save_path = 'data/files/store_' . $this->store_id . '/other';
            break;
            case BELONG_GOODS :     $this->save_path = 'data/files/store_' . $this->store_id . '/goods_' . (time() % 200);
            break;
        }


        $this->mod_uploadedfile = &m('uploadedfile');
        $this->mod_goods = &m('goods');
        $this->mod_goods_image = &m('goodsimage');
    }
    function index()
    {
        $this->_upload_file();
    }
    function taobao_image(){

        $this->_upload_taobao_image();
    }
    function _upload_taobao_image()
    {
        $ret_info = array(); // 返回到客户端的信息
        $file = $_FILES['Filedata'];

        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            $this->json_error('no_upload_file');
            exit();
        }


        $file['filename'] = substr($file['name'], 0, strpos($file['name'], '.'));

        $store_id=$this->store_id;
        if($_POST["up_store_id"]!="" && $_POST["up_store_id"]!=0){
            $store_id=$_POST["up_store_id"];
        }

        /* 取得商品信息检查是否有该图片的记录 */

        //$find_goods = $this->mod_goods->get("default_image='{$file['filename']}'");
        $find_goods = $this->mod_goods->find(array(
            'conditions' => "default_image LIKE '%" . $file['filename'] . ";%' AND store_id=" . $store_id,
        ));

        if (!$find_goods)
        {
            $this->json_error(array(
                array('msg'=>'db_no_such_image', 'obj'=>$file['name']),
            ));
            exit();
        }

        /* 根据mime类型还原真实图片名 */
        $file['imagesize'] = @getimagesize($file['tmp_name']); // 对于非图片文件可能会有问题
        $file['mime'] = $file['imagesize']['mime'];
        $file['extension'] = substr($file['mime'], strpos($file['mime'], '/')+1); // 真实后缀
        $file['name'] = $file['filename'] . '.' . $file['extension'];

        import('uploader.lib'); // 导入上传类
        import('image.func');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); // 限制文件类型
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 限制单个文件大小2M
        $uploader->addFile($file);
        if (!$uploader->file_info())
        {
            $this->json_error($uploader->get_error());
            exit();
        }
        foreach ($find_goods as $key =>$goods)
        {
            static $uploaded_file = NULL;
            /* 取得剩余空间（单位：字节），false表示不限制 */
            $store_mod  =& m('store');
            $settings   = $store_mod->get_settings($store_id);
            $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $this->mod_uploadedfile->get_file_size($store_id) : false;

            /* 判断能否上传 */
            if ($remain !== false)
            {
                if ($remain < $file['size'])
                {
                    $this->json_error('space_limit_arrived');
                    exit();
                }
            }

             /* 指定保存位置的根目录 */
            $uploader->root_dir(ROOT_PATH);
            $filename  = $uploader->random_filename();
            $up_dir=Conf::get('up_dir');

            if ($uploaded_file ===NULL)
            {
                /* 上传 */
                $uploaded_file = $file_path = $uploader->save($up_dir, $filename); // 保存到指定目录
                if (!$file_path)
                {
                    $this->json_error('file_save_error');
                    exit();
                }
            }
            else
            {
                //$this->save_path = 'data/files/store_' . $this->store_id . '/goods_' . (time() % 200);
                $this->save_path=$up_dir;
                $file_content = file_get_contents($uploaded_file);
                $file_path = $up_dir . '/' . $filename."_".time() % 200 . '.' . $file['extension'];
                file_put_contents($file_path, $file_content);
            }

            /* 附件入库 */
            $data = array(
                'store_id'  => $store_id,
                'file_type' => $file['mime'],
                'file_size' => $file['size'],
                'file_name' => $file['name'],
                'file_path' => $file_path,
                'belong'    => $this->belong,
                'item_id'   => $goods['goods_id'],
                'add_time'  => gmtime(),
                'session_id'  => SESS_ID,
            );
            $file_id = $this->mod_uploadedfile->add($data);
            if (!$file_id)
            {
                $this->json_error('file_add_error');
                exit();
            }

            /* 生成缩略图 */
            $thumbnail = dirname($file_path) . '/small_' . basename($file_path);
            make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH .'/' . $thumbnail, THUMB_WIDTH, THUMB_HEIGHT, THUMB_QUALITY);

            /*生成压缩图*/
            $sizes = array(INDEX_TOP_WIDTH . 'X' . INDEX_TOP_HEIGHT, INDEX_CENTER_WIDTH . 'X' . INDEX_CENTER_HEIGHT,
                INDEX_LEFT_WIDTH . 'X' .INDEX_LEFT_HEIGHT, GOOD_BIG_WIDTH . 'X'. GOOD_BIG_HEIGHT,
                NEW_GOOD_BIG_WIDTH .'X' . NEW_GOOD_BIG_HEIGHT,
                NEW_GOOD_MIDDLE_WIDTH . 'X' . NEW_GOOD_MIDDLE_HEIGHT,
                NEW_GOOD_SMALL_WIDTH. 'X' .NEW_GOOD_SMALL_HEIGHT,
                NEWS_GOOD_MIDDLE_WIDTH. 'X' .NEWS_GOOD_MIDDLE_HEIGHT
            );
            $url =  dirname($file_path) . '/' . basename($file_path);
            $urls = explode('.',$url);
            for($i = 0; $i < count($sizes); $i++){
                $path = $urls[0] . '_' . $sizes[$i] . '.' . $urls[1];
                $size = explode('X', $sizes[$i]);
                make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH .'/' . $path, $size[0], $size[1], THUMB_QUALITY);
            }

            /* 更新商品相册 */
            $data = array(
                'goods_id'   => $goods['goods_id'],
                'image_url'  => $file_path,
                'thumbnail'  => $thumbnail,
                'sort_order' => 255,
                'file_id'    => $file_id,
            );
            if (!$this->mod_goods_image->add($data))
            {
                $this->json_error($this->mod_goods_imaged->get_error());
                return false;
            }

            /* 更新商品默认图片 */
            $remain_image = str_replace($file['filename'] . ';', '', $goods['default_image']);
            if ($remain_image) // default_image字段中有超过一张图片
            {
                $this->mod_goods->edit($goods['goods_id'], array('default_image' => $remain_image));
            }
            else
            {
                $this->mod_goods->edit($goods['goods_id'], array('default_image' => $thumbnail));
            }
        }
        /* 返回客户端 */
        $ret_info =array(
            'file_id'   => $file_id,
            'file_path' => $file_path
        );
        $this->json_result($ret_info);
    }


    function upload_store_image()
    {
        $ret_info = array(); // 返回到客户端的信息
        $file = $_FILES['Filedata'];

        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            $this->json_error('no_upload_file');
            exit();
        }


        $file['filename'] = substr($file['name'], 0, strpos($file['name'], '.'));
        /* 根据mime类型还原真实图片名 */
        $file['imagesize'] = @getimagesize($file['tmp_name']); // 对于非图片文件可能会有问题
        $file['mime'] = $file['imagesize']['mime'];
        $file['extension'] = substr($file['mime'], strpos($file['mime'], '/')+1); // 真实后缀
        $file['name'] = $file['filename'] . '.' . $file['extension'];

        import('uploader.lib'); // 导入上传类
        import('image.func');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); // 限制文件类型
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 限制单个文件大小2M
        $uploader->addFile($file);
        if (!$uploader->file_info())
        {
            $this->json_error($uploader->get_error());
            exit();
        }
        static $uploaded_file = NULL;
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);
        $filename  = $uploader->random_filename();
        $up_dir=Conf::get('up_dir');

        if ($uploaded_file ===NULL){
            /* 上传 */
            $uploaded_file = $file_path = $uploader->save($up_dir, $filename); // 保存到指定目录
            if (!$file_path){
                $this->json_error('file_save_error');
                exit();
            }
        }
        else {
            //$this->save_path = 'data/files/store_' . $this->store_id . '/goods_' . (time() % 200);
            $this->save_path=$up_dir;
            $file_content = file_get_contents($uploaded_file);
            $file_path = $up_dir . '/' . $filename."_".time() % 200 . '.' . $file['extension'];
            file_put_contents($file_path, $file_content);
        }
        /*生成压缩图*/
        /*$sizes = array(INDEX_TOP_WIDTH . 'X' . INDEX_TOP_HEIGHT);
        $url =  dirname($file_path) . '/' . basename($file_path);
        $urls = explode('.',$url);
        for($i = 0; $i < count($sizes); $i++){
            $path = $urls[0] . '_' . $sizes[$i] . '.' . $urls[1];
            $size = explode('X', $sizes[$i]);
            make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH .'/' . $path, $size[0], $size[1], THUMB_QUALITY);
        }*/
        //echo "<script type=\"text/javascript\">window.parent.upload_ok($file_path);</script>";

        $files_store = $_SESSION["files_store"];
        if($files_store && $files_store!=""){
            $img_array =explode("###",$files_store);
            if(!in_array($file_path,$img_array)){
                $files_store.="###".$file_path;
                $_SESSION["files_store"] = $files_store;
            }
        }else{
            $_SESSION["files_store"] = $file_path;
        }

        /* 返回客户端 */
        $ret_info =array(
            'file_id'   => 0,
            'file_path' => $file_path
        );
        //cho "<script type=\"text/javascript\">window.parent.upload_ok('$file_path');</script>";
        $this->json_result($ret_info);
    }


    function _upload_file()
    {
        $ret_info = array(); // 返回到客户端的信息
        $file = $_FILES['Filedata'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            $this->json_error('no_upload_file');
            exit();
        }
        import('uploader.lib'); // 导入上传类
        import('image.func');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); // 限制文件类型
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 限制单个文件大小2M
        $uploader->addFile($file);
        if (!$uploader->file_info())
        {
            $this->json_error($uploader->get_error());
            exit();
        }

        /* 取得剩余空间（单位：字节），false表示不限制 */
        $store_mod  =& m('store');
        $settings   = $store_mod->get_settings($this->store_id);
        $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $this->mod_uploadedfile->get_file_size($this->store_id) : false;
        $user = $this->visitor->get();
        /* 判断能否上传 */
        if ($remain !== false)
        {
            if ($remain < $file['size'])
            {
                $this->json_error('space_limit_arrived');
                exit();
            }
        }

//        $this->pr($_SESSION);exit;
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);
        $filename  = $uploader->random_filename();

        $up_dir=Conf::get('up_dir');

        /* 上传 */
        $file_path = $uploader->save($up_dir, $this->store_id."_".$filename); // 保存到指定目录
        if (!$file_path)
        {
            $this->json_error('file_save_error');
            exit();
        }
        $file_type = $this->_return_mimetype($file_path);
        /* 文件入库 */
        $data = array(
            'store_id'  => $user["user_id"],// $this->store_id,
            'file_type' => $file_type,
            'file_size' => $file['size'],
            'file_name' => $file['name'],
            'file_path' => $file_path,
            'belong'    => $this->belong,
            'item_id'   => $this->item_id,
            'add_time'  => gmtime(),
            'session_id'  => SESS_ID,
        );

//        $this->pr($data);exit;
        $file_id = $this->mod_uploadedfile->add($data);
        if (!$file_id)
        {
            $this->json_error('file_add_error');
            exit();
        }

        if ($this->instance == 'goods_image') // 如果是上传商品相册图片
        {
            /* 生成缩略图 */
            $thumbnail = dirname($file_path) . '/small_' . basename($file_path);
            make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH .'/' . $thumbnail, THUMB_WIDTH, THUMB_HEIGHT, THUMB_QUALITY);
//            if ($_POST['pd_id'] && $_POST['pd_id'] == 5) {
////                $this->pr($_POST['pd_id']);
////                exit;
//                $sizes = array(BDSH_INDEX_GOOD_WIDTH . 'X' . BDSH_INDEX_GOOD_HEIGHT,
//                    BDSH_GOOD_DETAIL_WIDTH . 'X' . BDSH_GOOD_DETAIL_HEIGHT,
//                    BDSH_GOOD_LIST_WIDTH . 'X' .BDSH_GOOD_LIST_HEIGHT,
//                    BDSH_GOOD_DETAIL1_WIDTH .'X' . BDSH_GOOD_DETAIL1_HEIGHT,
//                    BDSH_GOOD_LIST1_WIDTH .'X' . BDSH_GOOD_LIST1_HEIGHT,
//                );
//            }else if ($_POST['pd_id'] != 5){
                $sizes = array(INDEX_TOP_WIDTH . 'X' . INDEX_TOP_HEIGHT, INDEX_CENTER_WIDTH . 'X' . INDEX_CENTER_HEIGHT,
                    INDEX_LEFT_WIDTH . 'X' .INDEX_LEFT_HEIGHT, GOOD_BIG_WIDTH .'X' . GOOD_BIG_HEIGHT,
                    NEW_GOOD_BIG_WIDTH .'X' . NEW_GOOD_BIG_HEIGHT,
                    NEW_GOOD_MIDDLE_WIDTH . 'X' . NEW_GOOD_MIDDLE_HEIGHT,
                    NEW_GOOD_SMALL_WIDTH. 'X' .NEW_GOOD_SMALL_HEIGHT,
                    NEWS_GOOD_MIDDLE_WIDTH. 'X' .NEWS_GOOD_MIDDLE_HEIGHT,
                    BDSH_INDEX_GOOD_WIDTH.'X'.BDSH_INDEX_GOOD_HEIGHT,
                    BDSH_GOOD_DETAIL_WIDTH.'X'.BDSH_GOOD_DETAIL_HEIGHT,
                    BDSH_GOOD_LIST_WIDTH.'X'.BDSH_GOOD_LIST_HEIGHT
                );
//            }
            /*生成压缩图*/

            $url =  dirname($file_path) . '/' . basename($file_path);
            $urls = explode('.',$url);
            for($i = 0; $i < count($sizes); $i++){
                $path = $urls[0] . '_' . $sizes[$i] . '.' . $urls[1];
                $size = explode('X', $sizes[$i]);
                make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH .'/' . $path, $size[0], $size[1], THUMB_QUALITY);
            }

            /* 更新商品相册 */
            $data = array(
                'goods_id'   => $this->item_id,
                'image_url'  => $file_path,
                'thumbnail'  => $thumbnail,
                'sort_order' => 255,
                'file_id'    => $file_id,
            );


            if (!$this->mod_goods_image->add($data))
            {
                $this->json_error($this->mod_goods_imaged->get_error());
                return false;
            }
            $ret_info = array_merge($ret_info, array('thumbnail' => $thumbnail));
        }



        /* 返回客户端 */
        $ret_info = array_merge($ret_info, array(
            'file_id'   => $file_id,
            'file_path' => $file_path,
            'instance'  => $this->instance,
        ));


        $this->json_result($ret_info);
    }

    function _return_mimetype($filename)
    {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
        switch(strtolower($fileSuffix[1]))
        {
            case "js" :
                return "application/x-javascript";

            case "json" :
                return "application/json";

            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpeg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/".strtolower($fileSuffix[1]);

            case "css" :
                return "text/css";

            case "xml" :
                return "application/xml";

            case "doc" :
            case "docx" :
                return "application/msword";

            case "xls" :
            case "xlt" :
            case "xlm" :
            case "xld" :
            case "xla" :
            case "xlc" :
            case "xlw" :
            case "xll" :
                return "application/vnd.ms-excel";

            case "ppt" :
            case "pps" :
                return "application/vnd.ms-powerpoint";

            case "rtf" :
                return "application/rtf";

            case "pdf" :
                return "application/pdf";

            case "html" :
            case "htm" :
            case "php" :
                return "text/html";

            case "txt" :
                return "text/plain";

            case "mpeg" :
            case "mpg" :
            case "mpe" :
                return "video/mpeg";

            case "mp3" :
                return "audio/mpeg3";

            case "wav" :
                return "audio/wav";

            case "aiff" :
            case "aif" :
                return "audio/aiff";

            case "avi" :
                return "video/msvideo";

            case "wmv" :
                return "video/x-ms-wmv";

            case "mov" :
                return "video/quicktime";

            case "rar" :
                return "application/x-rar-compressed";

            case "zip" :
            return "application/zip";

            case "tar" :
                return "application/x-tar";

            case "swf" :
                return "application/x-shockwave-flash";

            default :
            if(function_exists("mime_content_type"))
            {
                $fileSuffix = mime_content_type($filename);
            }
            return "unknown/" . trim($fileSuffix[0], ".");
        }
    }
}

?>