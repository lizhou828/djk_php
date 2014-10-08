<?php

define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);

/* 品牌申请状态 */
define('BRAND_PASSED', 1);
define('BRAND_REFUSE', 0);

/* 商品管理控制器 */
class Goods_viewApp extends StorebaseApp
{
    var $_goods_mod;
    var $_uploadedfile_mod;



    function __construct()
    {
        $this->Goods_viewApp();
    }
    function Goods_viewApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
        $this->_uploadedfile_mod =& m('uploadedfile');
    }

    function index()
    {
//        $this->pr($_POST);exit;
        $goods_file_ids = $_POST["goods_file_id"];
        $conditions = "";
        $images = array();
        if (count($goods_file_ids)>0) {
            foreach ($goods_file_ids as $key=>$goods_img_id) {
               if ($conditions=="") {
                   $conditions= " file_id = ".$goods_img_id;
               } else {
                   $conditions.=" or file_id = ".$goods_img_id;
               }
            }
            $images= $this->_uploadedfile_mod->find(
                array("conditions" => $conditions,)
            );
        } else {
            $images= array(0=>array(
                "file_path" =>  Conf::get('default_goods_image'),
            ));
        }

        $description = $_POST["description"];
        $description = str_replace("\\\"", "", $description);
        $this->assign('goods_name', $_POST["goods_name"]);
        $this->assign('brand', $_POST["brand"]);
        $this->assign('cate_name', $_POST["cate_name"]);
        $this->assign('description', $description);
        $this->assign('price', $_POST["price"]);
//        $this->assign('goods_name', $_POST["goods_name"]);
//        $this->assign('goods_name', $_POST["goods_name"]);
        $this->assign('goods_imges', $images);
        $this->display('goods_view.index.html');
    }



}

?>
