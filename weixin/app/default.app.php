<?php

/**
 *    默认控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class DefaultApp extends MallbaseApp
{
    /**
     *    微信首页
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $user = $this->visitor->get();
        $this->assign('user',$user);
        $this->display('index.html');
    }

    function index1()
    {
        $user = $this->visitor->get();
        $this->assign('user',$user);
        $this->display('index1.html');
    }


    //获取登录用户对象
    function get_vistor() {
        $user = $this->visitor->get();
        $data['member_type'] = $user['member_type'];
        $data['user_name'] = $user['user_name'];
        $data['user_id'] = $user['user_id'];
        if (preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$user['nick_name'])){
            $user['nick_name'] = substr_replace($user['nick_name'],'****',-8,4);
        }
        $data['nick_name'] = $user['nick_name'];

        echo json_encode($data);
    }
    function waiting(){
        $this->display('waiting.html');
    }
}
?>
