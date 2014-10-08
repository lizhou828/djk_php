<?php

/**
 *    用户基本操作控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class ModifyPsApp extends MallbaseApp
{
   function yanzheng(){
       $password = $_POST['password'];
       $user = $this -> visitor -> get();
       if($user['user_id'] < 1){
           header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=updatePw_yanzheng");
       }
       if($user['password'] != md5($password)){
           echo json_encode(array("flag"=>"error"));
           return;
       }else{
           echo json_encode(array("flag"=>"ok"));
           return;
       }
   }

    function form(){
        $this -> display("modifyPassword_form.html");
    }

    function update(){
        $password1 = $_POST['password1'];
        $password2 = $_POST['password2'];
        $user = $this -> visitor -> get();
        if($user['user_id'] < 1){
            header("Location: ". SITE_URL . "/weixin/index.php?app=modifyPs&act=form");
        }
        if($password1 == "" || $password2 == ""){
            echo json_encode(array("flag"=>"error1"));
            return;
        }else if(strlen($password1) < 6 ){
            echo json_encode(array("flag"=>"error3"));
            return;
        }else if($password1 != $password2){
            echo json_encode(array("flag"=>"error2"));
            return;
        }else{
            $member_model = & m("member");
            $member_model -> edit("user_id = " . $user["user_id"], "password = '" . md5("$password1") ."'");
            echo json_encode(array("flag"=>"ok"));
            return;
        }
    }

}
?>
