<?php

/* 安全中心 */
class AqzxApp extends MallbaseApp
{
    function __construct(){
        parent::__construct();
        $this->_member_mod =& m('member');
    }

    function aqzx_savaemail(){
        $code=$_GET["code"];
        if($code==""){
            exit("非法操作!");
        }

        $data=$this->_member_mod->find("email_code='$code'");
        if(count($data)==0 || count($data)>1){
            exit("链接已经失效!");
        }

        $email=base64_decode(substr($code,22));
        foreach($data as $k=>$v){
            if($v["user_id"]<=0){
                exit("非法的用户!");
            }

            $id=$this->_member_mod->edit($v["user_id"],array("email"=>$email,"email_code"=>""));
            if($id>0){

                $this->_do_login($v["user_id"]);
                $this->show_message('changeremail_true',
                    'back_list', 'index.php'
                );
            }else{
                exit("邮箱已被占用!");
            }
        }

    }





}

?>
