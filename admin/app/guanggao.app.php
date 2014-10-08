<?php
//广告app
class GuanggaoApp extends BackendApp{

   var $guanggao_mod;
   var $guanggao_position;
    function __construct()
    {
        $this->GuanggaoApp();
    }
   function GuanggaoApp(){
        parent::BackendApp();
        $this->guanggao_mod =  & m('guanggao');
        $this->guanggao_position =  Conf::get('guanggao_position');
        $this->assign('guanggao_position',$this->guanggao_position);
       $this->assign('guanggao_position_json',ecm_json_encode($this->guanggao_position));
   }
   function index(){

       $conditions ='1=1';

       if ($_GET['title']) {
           $conditions.= " and position = '".$_GET['title']."'";
       }

       $page = $this->_get_page();
        $guanggao = $this->guanggao_mod->find(array(
                                  "conditions" =>$conditions,
                                  "fields"      =>"this.*",
                                   'count'=>true,
                                   'limit'=>$page['limit'],
        ));
        $page['item_count'] = $this->guanggao_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info',$page);
        $position_arr= $this->guanggao_position;

       if(count($guanggao)>0){
           foreach($guanggao as $k=>$v){
                  $guanggao[$k]["position_name"] = $position_arr[$v['position']]['position'];
           }
       }

        $this->assign('guanggao',$guanggao);
        $this->display('guanggao.index.html');
   }

   function edit(){
       if(!$_POST){
           $id = $_GET["id"];
           if(empty($id) || $id<0){
               $this->show_warning('新增广告失败');
               exit;
           }
           $guanggao = $this->guanggao_mod->get($id);
           $this->assign('guanggao',$guanggao);
           $this->display('guanggao.form.html');
       }else{
           if(empty($_POST["id"]) && $_POST["id"] ==""){
               $this->show_warning('没有找到广告');
               exit;
           }
           $data["title"] = $_POST["title"];
           $data["des"] = $_POST["des"];
           $data["link"] = $_POST["link"];
           $data["sort"] = $_POST["sort"];
           $data["position"] = $_POST["position"];
           ini_set('date.timezone','Asia/Shanghai');
           $data["create_time"] = date('Y-m-d H:i:s',time());
           $data["create_user_id"] = $this->visitor->get("user_id");
           $data["create_user_name"] = $this->visitor->get("user_name");
           if($_FILES["img"]){
               import('uploader.lib');
               $uploader = new Uploader();
               $uploader->allowed_type(IMAGE_FILE_TYPE);
               $uploader->allowed_size(SIZE_STORE_CERT); // 400KB
               $img = $_FILES['img'];
               if ($img !="" && $img['error'] == UPLOAD_ERR_OK){
                   if (empty($img)){
                       exit;
                   }
                   $uploader->addFile($img);
                   if (!$uploader->file_info()){
                       $this->_error($uploader->get_error());
                       return false;
                   }
                   $uploader->root_dir(ROOT_PATH);
                   $dirname   = Conf::get('up_dir'). '/application';
                   $filename  = 'site_' . gmtime();
                   $data["img"] = $uploader->save($dirname, $filename);
               }
           }
           $id = $this->guanggao_mod->edit($_POST["id"],$data);
           if($id>0){
               $this->show_message('修改广告成功');
           }else{
               $this->show_message('修改广告失败');
           }

       }
   }

   function drop(){
       $id = $_GET["id"];
       if(empty($id) || $id<0){
           $this->show_warning('没有找到广告');
           exit;
       }
       $rs = $this->guanggao_mod->edit($id,array("status"=>0));
       if($rs>0){
           $this->show_message('删除成功');
       }else{
           $this->show_warning("删除失败");
       }
   }

   function undrop(){
       $id = $_GET["id"];
       if(empty($id) || $id<0){
           $this->show_warning('没有找到广告');
           exit;
       }
       $rs = $this->guanggao_mod->edit($id,array("status"=>1));
       if($rs>0){
           $this->show_message('恢复成功');
       }else{
           $this->show_warning("恢复失败");
       }
   }

   function add(){
       if(!$_POST){
           $this->display('guanggao.form.html');
       }else{
           $data["title"] = $_POST["title"];
           $data["des"] = $_POST["des"];
           $data["link"] = $_POST["link"];
           $data["sort"] = $_POST["sort"];
           $data["position"] = $_POST["position"];
           ini_set('date.timezone','Asia/Shanghai');
           $data["create_time"] = date('Y-m-d H:i:s',time());
           $data["create_user_id"] = $this->visitor->get("user_id");
           $data["create_user_name"] = $this->visitor->get("user_name");
           if($_FILES["img"]){
               import('uploader.lib');
               $uploader = new Uploader();
               $uploader->allowed_type(IMAGE_FILE_TYPE);
               $uploader->allowed_size(SIZE_STORE_CERT); // 400KB
               $img = $_FILES['img'];
               if ($img !="" && $img['error'] == UPLOAD_ERR_OK){
                   if (empty($img)){
                       exit;
                   }
                   $uploader->addFile($img);
                   if (!$uploader->file_info()){
                       $this->_error($uploader->get_error());
                       return false;
                   }
                   $uploader->root_dir(ROOT_PATH);
                   $dirname   = Conf::get('up_dir'). '/application';
                   $filename  = 'site_' . gmtime();
                   $data["img"] = $uploader->save($dirname, $filename);
               }
           }
           $id = $this->guanggao_mod->add($data);
           if($id>0){
               $this->show_message('新增广告成功');
           }else{
               $this->show_warning('新增广告失败');
           }

       }
   }



}

?>