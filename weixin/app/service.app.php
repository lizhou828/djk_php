<?php

/**
 *    默认控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class ServiceApp extends MallbaseApp
{
     function index(){
         $this -> display("member.index_buyer.html");
     }

     function find(){
         $keyW = $_GET['key'];
         $city = $_GET['city'];
         $quxian = $_GET['quxian'];
         $page = $_GET['page'];
         $member_model = & m('member');
         $qx = $_GET['qx'];
         $serviceInfo_model = & m('serviceInfo');
         $keyWord = "";
         $totalCount = 0;
         if($keyW != "" && $keyW != "请输入城市名或其他关键字"){
             $keyWord = $keyWord . $keyW;
         }else{
             if($city != "" && $city != "请选择"){
                 $keyWord = $keyWord . $city;
             }
             if($quxian != "" && $quxian != "请选择"){
                 if($keyWord != ""){
                     $keyWord = $keyWord . "\t" . $quxian;
                 }else{
                     $keyWord = $keyWord . $quxian;
                 }
             }
             if($qx != "" && $qx != "请选择"){
                 if($keyWord != ""){
                     $keyWord = $keyWord . "\t" . $qx;
                 }else{
                     $keyWord = $keyWord . $qx;
                 }
             }
         }
         if($keyWord == ""){
             $service = array();
         }else{
             $sql = "SELECT mm.user_id, mm.region_name, si.service_tel, si.service_address FROM {$member_model->table} mm
	                  LEFT JOIN {$serviceInfo_model->table} si ON mm.user_id = si.service_id  WHERE si.type=1 AND mm.member_type='02'
	                  AND mm.dropState=1 AND mm.region_name LIKE '%{$keyWord}%'";
             $serviceCount = $member_model -> getAll($sql);
             $totalCount = count($serviceCount);
             $limit = "";
             if($page > $totalCount){
                 $limit = " LIMIT 0,".$page;
                 $sql = $sql . $limit;
             }else{
                 if($page != "" || $page != null){
                     $page = $page + 20;
                     $limit = " LIMIT 0,".$page;
                     $sql = $sql . $limit;
                 }else{
                     $page = 20;
                     $limit = " LIMIT 0,".$page;
                     $sql = $sql . $limit;
                 }
             }
             $service = $member_model -> getAll($sql);
         }
         $count = count($service);
         if($count > 0){
             foreach($service as $key=>$se){
                 $re = explode("\t",$se['region_name']);
                 $cn = count($re);
                 $service[$key]['region_name'] = $re[$cn - 1];
             }
         }
         $this -> assign("service1",$service);
         $this -> assign("count",$count);
         $this -> assign("totalCount",$totalCount);
         $this -> assign("page",$page);
         $this -> assign("key",$keyW);
         $this -> assign("city",$city);
         $this -> assign("quxian",$quxian);
         $this -> display("service.list.html");
     }

    function find_region(){
        $id = $_POST['id'];
        if($id == ""){
            $id = 2;
        }
        $region_model = & m('region');
        $reg = array();
        $region = $region_model -> find("parent_id = " . $id ." and dropState = 1");
        foreach($region as $re){
            $arr = array("region_id" => $re['region_id'], "region_name" => $re['region_name']);
            array_push($reg,$arr);
        }
        echo json_encode(array("region"=>$reg));
        exit;
    }

    function service_form(){
        $region_model = & m('region');
        $region = $region_model -> find("parent_id = 1 and dropState = 1");
        $this -> assign("region",$region);
        $this -> display("service.form.html");
    }

    function findQx(){
        $region_id = $_POST['region_id'];
        $city = $_POST['city'];
        $region_model = & m('region');
        $region = $region_model -> find("parent_id = " . $region_id . " and dropState = 1");
        $re = array();
        foreach($region as $rg){
            array_push($re,$rg);
        }
        echo json_encode(array("region"=>$re));
        return;
    }
}
?>
