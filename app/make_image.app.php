<?php
define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);

define('SMALL_STORE_IMAGE_WIDTH',60);
define('SMALL_STORE_IMAGE_HEIGHT',60);

define('MIDDLE_STORE_IMAGE_WIDTH',200);
define('MIDDLE_STORE_IMAGE_HEIGHT',200);

define('BIG_STORE_IMAGE_WIDTH',340);
define('BIG_STORE_IMAGE_HEIGHT',240);



class Make_imageApp extends StoreadminbaseApp
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
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_store_mod =& m('store');
        $this->_uploadedfile_mod = &m('uploadedfile');
        $this->_store_uploadfile_mod = &m('storeuploadedfile');
    }


    function _upload_store_image($file_path){
        import('image.func');
        import('uploader.lib');
        if  ($file_path) {
        $url = (DIS_PATH!=null && DIS_PATH!="DIS_PATH") ? DIS_PATH : ROOT_PATH;
            /*生成压缩图*/
            $sizes = array(
                SMALL_STORE_IMAGE_WIDTH . 'X' . SMALL_STORE_IMAGE_HEIGHT,
                MIDDLE_STORE_IMAGE_WIDTH . 'X' . MIDDLE_STORE_IMAGE_HEIGHT,
                BIG_STORE_IMAGE_WIDTH . 'X' .BIG_STORE_IMAGE_HEIGHT,
            );
            $urls = explode('.',$file_path);
            for($i = 0; $i < count($sizes); $i++){
                $path = $urls[0] . '_' . $sizes[$i] . '.' . $urls[1];
                $size = explode('X', $sizes[$i]);
                make_thumb($url . '/' . $file_path, $url .'/' . $path, $size[0], $size[1], THUMB_QUALITY);
            }
        }
    }
}

?>
