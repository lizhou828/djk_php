<?php
/**
 * 找回密码控制器
 * @author cheng
 */
class Find_passwordApp extends MallbaseApp
{
    var $_password_mod;
    function __construct()
    {
        $this->Find_passwordApp();
    }

    function Find_passwordApp()
    {
        parent::FrontendApp();
        $this->_password_mod = &m("member");
    }

    /**
     * 显示文本框及处理提交的用户信息
     *
     */
    function index()
    {
        $this->import_resource('jquery.plugins/jquery.validate.js');

        $msm_time = $_SESSION["msm_time"];
        if($msm_time != "" && $msm_time >0 && gmtime() - $msm_time < 120){
            $this->assign('t_time',120-(gmtime() - $msm_time));
        }

        if(!IS_POST)
        {
            $this->display("find_password.html");
        }
        else
        {

            if( !$_POST["step"] || $_POST["step"]==1 ){
                $user_name       = $_POST["username"];
                $captcha         = $_POST["captcha"];
                $session_captcha = base64_decode($_SESSION["captcha"]);
                if($user_name == "" || $captcha  == "" ||  $session_captcha ==""){
                    $this->display("find_password.html");
                    $_POST="";
                    echo "<script>js_fail(\"用户名/手机号码/邮箱，验证码不能为空！\");</script>";
                    exit;
                }
                if($captcha != $session_captcha ){
                    $this->display("find_password.html");
                    $_POST="";
                    echo "<script>js_fail(\"验证码错误！\");</script>";
                    exit;
                }

                $list=$this->_password_mod->find("user_name='$user_name' or email='$user_name' or phone_mob='$user_name'");
                if( count($list) == 0 ){
                    $this->display("find_password.html");
                    $_POST="";
                    echo "<script>js_fail(\"用户名/手机号码/邮箱 不存在！\");</script>";
                    exit;
                }else  if( count($list) > 1 ){
                    $this->display("find_password.html");
                    $_POST="";
                    echo "<script>js_fail(\"用户名/手机号码/邮箱 错误！\");</script>";
                    exit;
                }
                foreach($list as $k=>$v){
                    $this->assign('user', $v);
                    $_POST["step"]="2";
                    if($v['question']) {
                        $qu =  unserialize($v['question']);
                        $this->assign('qu',$qu);
                    }
                    $this->display("find_password.html");

                }
            }else if($_POST["step"]==2 ){

//                $this->pr($_POST);
                $find_pwd_type=$_POST["find_pwd_type"];
                $user_id=$_POST["user_id"];

                if( $user_id == "" || $user_id <=0 ){
                    $_POST = "";
                    $_POST["step"]="1";
                    $this->display("find_password.html");
                    echo "<script>js_fail(\"没有找到用户信息！\");</script>";
                    exit;
                }

                if( $find_pwd_type == "" ){
                    $_POST = "";
                    $_POST["step"]="2";
                    $this->display("find_password.html");
                    echo "<script>js_fail(\"请选择找回密码方式！\");</script>";
                    exit;
                }

                $user=$this->_password_mod->get("user_id='$user_id'");
                $this->assign('user', $user);
                if($user['question']) {
                    $qu =  unserialize($user['question']);
                    $this->assign('qu',$qu);
                }
                $word = $this->_rand();
                $md5word = md5($word);
                if($find_pwd_type == "find_email" ){

                    if( $user["user_id"] > 0 && $user["email"] != "" && $user["email_bind_status"] == 1 ){
                        $this->_password_mod->edit($user['user_id'], array('activation' => "{$md5word}"));
                        $mail = get_mail('touser_find_password', array('user' => $user, 'word' => $word));
                        $title = addslashes($mail['subject']);
                        $content = "请点击下面的链接找回密码！如果点击无效，请手动复制链接，然后粘贴到浏览器的地址栏中访问:" . SITE_URL . "/index.php?app=find_password&act=set_password&id=".$user_id."&activation={$md5word}<br>
                    <a href='".SITE_URL."/index.php?app=find_password&act=set_password&id=".$user_id."&activation={$md5word}'>点击找回密码</a>";
                        $content .= "<br>本邮件为系统自动发送，请勿回复！";
                        include_once(ROOT_PATH . '/app/sendcode.app.php');
                        $sender = new SendCodeApp();
                        $sender->send_email($user["email"],$title,$content);
                        $this->display("find_password_send_email.html");
                    }

                }else if($find_pwd_type == "find_phone_mob" ){

                    $phone_mob  = $_POST["phone_or_tel"];
                    $yanzhengma = "1234";
                    $init_code = "1234";

                    if($yanzhengma == "" || $init_code == "" ){
                        $_POST = "";
                        $_POST["step"]="2";
                        $this->display("find_password.html");
                        echo "<script>js_fail(\"手机验证码不能为空！\");</script>";
                        exit;
                    }
                    if($user["phone_mob"] != $phone_mob ){
                        $_POST = "";
                        $_POST["step"]="2";
                        $this->display("find_password.html");
                        echo "<script>js_fail(\"没有找到手机号码！\");</script>";
                        exit;
                    }
                    if($yanzhengma != $init_code ){
                        $this->show_warning('手机验证码错误');
                        exit;
                    }
                    $this->_password_mod->edit($user['user_id'], array('activation' => "{$md5word}"));
                    header("Location: index.php?app=find_password&act=set_password&id=".$user_id."&activation={$md5word}");
                } else if($find_pwd_type =="find_question"){
                    $question = $_POST["question"];
                    $answer = $_POST["answer"];
                    if  ($question) {
                        foreach ($question as $key=>$q) {
                            $questions[$user_id]['name'][$key]=$q;
                            $questions[$user_id]['answer'][$key]=$answer[$key];
                        }
                    }

                    if($user["question"] != serialize($questions)){
                        $_POST = "";
                        $_POST["step"]="2";
                        $this->display("find_password.html");
                        echo "<script>js_fail(\"您填的密保答案与您设置的密保答案不一致！\");</script>";
                        exit;
                    }
                    $this->_password_mod->edit($user['user_id'], array('activation' => "{$md5word}"));
                    header("Location: index.php?app=find_password&act=set_password&id=".$user_id."&activation={$md5word}");
                }

            }

            ///
        }
    }

    /**
     * 显示设置密码及处理提交的新密码信息
     *
     */
    function set_password()
    {
        if (!IS_POST)
        {
            if (!isset($_GET['id']) || !isset($_GET['activation']) || empty($_GET['activation']))
            {
                $this->show_warning("request_error",
                    'back_index', 'index.php');
                return ;
            }
            $id = intval(trim($_GET['id']));
            $activation = trim($_GET['activation']);
            $res = $this->_password_mod->get_info($id);

            if ($activation != $res['activation'])
            {
                $this->show_warning("invalid_link",
                    'back_index', 'index.php');
                return ;
            }
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display("set_password.html");
        }
        else
        {
            if (empty($_POST['new_password']) || empty($_POST['confirm_password']))
            {
                $_POST = "";
                $_POST["step"]="3";
                $this->display("set_password.html");
                echo "<script>js_fail(\"新密码不能为空！\");</script>";
                exit;
            }
            if (trim($_POST['new_password']) != trim($_POST['confirm_password']))
            {
                $_POST = "";
                $_POST["step"]="3";
                $this->display("set_password.html");
                echo "<script>js_fail(\"确认密码不能为空！\");</script>";
                exit;
            }
            $password = $_POST['new_password'];
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20)
            {
                $_POST = "";
                $_POST["step"]="3";
                $this->display("set_password.html");
                echo "<script>js_fail(\"密码长度必须在6-20位之间！\");</script>";
                exit;
            }

            $id = intval($_GET['id']);

            $password=md5($password);

            $ret = $this->updatePwd($id,$password);
            /*$sql = "UPDATE ".DB_PREFIX."member SET activation='',password='$password' WHERE user_id = $id";
            $ret = $this->_password_mod->db_query($sql);*/
            if($ret>0){
                $_POST = "";
                $_POST["step"]="4";
                $this->display("set_password.html");
                $this->_password_mod->commit_transaction();
                $this->_do_login($id);
                echo "<script>setTimeout(function(){location ='index.php';},3000)</script>";
                exit;
            }else{
                $_POST = "";
                $_POST["step"]="3";
                $this->display("set_password.html");
                echo "<script>js_fail(\"修改密码失败\");</script>";
                exit;
            }
        }

    }

    function updatePwd($id,$password){
       return $ret = $this->_password_mod->edit($id, array('activation' => '',"password" => $password));
    }

    /**
     * 构造15位的随机字符串
     *
     * @return string | 生成的字符串
     */
    function _rand()
    {
        $word = $this->generate_code();
        return $word;
    }

    function generate_code($len = 15)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++)
        {
            $arr[$i] = $chars[$i];
        }

        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }

    function test() {
        $this->_password_mod->db_query("UPDATE ecm_member SET activation='',password='e3ceb5881a0a1fdaad01296d7554868d' WHERE user_id = 2668");
    }
}
?>
