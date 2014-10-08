<?php

/**
 *    前台控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class FrontendApp extends ECBaseApp
{
    function __construct()
    {
        $this->FrontendApp();
        include_once(ROOT_PATH . '/includes/HttpClient.class.php');
    }
    function FrontendApp()
    {
        Lang::load(lang_file('common'));
        Lang::load(lang_file(APP));
        parent::__construct();

        $user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        $this->assign('member',$info);


        // 判断商城是否关闭
        if (!Conf::get('site_status'))
        {
            $this->show_warning(Conf::get('closed_reason'));
            exit;
        }
        # 在运行action之前，无法访问到visitor对象
    }
    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = ROOT_PATH . '/themes';
        $this->_view->compile_dir   = ROOT_PATH . '/temp/compiled/mall';
        $this->_view->res_base      = STATIC_URL . '/themes';
        $this->_config_seo(array(
            'title' => Conf::get('site_title'),
            'description' => Conf::get('site_description'),
            'keywords' => Conf::get('site_keywords')
        ));
    }
    function display($tpl)
    {
        //微信openId获取
        $code = $_GET['code'];
        if($code == ""){
            $code = $_POST['code'];
        }
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        if($code != "" && !$user){
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx7ac999e64dae7632&secret=e5e2555765f061e4ed2d3305a5155162&code=".$code."&grant_type=authorization_code";
            $re = $this -> post($url);
            $data = ecm_json_decode($re,1);
            $openId = $data['openid'];
            if($openId != ""){
                ecm_setcookie("openId",$openId,time() + 86400);
                $member_model = & m('member');
                $member = $member_model -> get("weixin_id = '".$openId."'");
                if($member['user_id'] > 0){
                    $this -> _do_login($member['user_id']);
                }
            }
        }

        $u_id = $_GET['u_id'];
        if($u_id != "" & $u_id > 0 ){
            ecm_setcookie("u_id",$u_id,time() + 86400);
        }

        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $user_id = $this -> visitor ->get("user_id");
        if($u_id == "" && $user_id > 0 && strpos($url,'app') != false && !$_POST){
            $url = $url . "&u_id=" . $user_id;
            header("Location: http://".$url);
        }

        if(CART_IS_COOKIE == 'ON'){
            $cart_list = ecm_getcookie('cart');
//            echo $cart_list;exit;
            $kids = 0;
            if($cart_list != ''){
                $cart = explode('|',$cart_list);
                if(count($cart) > 0){
                    foreach($cart as $ct){
                       $kids = intval($kids) + 1;
                    }
                }
                $kids = intval($kids) - 1;
            }
        }else{
            $cart =& m('cart');
            $kids = $cart->get_kinds(SESS_ID, $this->visitor->get('user_id'));
        }

        $this->assign('cart_goods_kinds',$kids);

		/*
        echo $this->visitor;
        exit;*/

        /*商城文章*/
        //帮助中心
        $cache = & cache_server();
        $article = $cache->get('article');
        if( $article === false){
            $help = $this->_get_news(ACC_HELP);
            //店主之家
            $home = $this->_get_news(ACC_HOME);
            //支付方式
            $pay = $this->_get_news(ACC_PAY);
            //售后服务
            $service = $this->_get_news(ACC_SERVICE);
            //客服中心
            $center = $this->_get_news(ACC_CENTER);
            //关于我们
            $about = $this->_get_news(ACC_ABOUT);
            $article = array('help'=> $help, 'home' => $home, 'pay' => $pay, 'service' => $service, 'center' => $center, 'about' => $about);
            $cache->set('article',$article,3600);
        }
        $this->assign('help',$article['help']);
        $this->assign('home',$article['home']);
        $this->assign('pay',$article['pay']);
        $this->assign('service',$article['service']);
        $this->assign('center',$article['center']);
        $this->assign('about',$article['about']);

        /* 新消息 */
        $this->assign('new_message', isset($this->visitor) ? $this->_get_new_message() : '');
        $this->assign('navs', $this->_get_navs());  // 自定义导航
        $this->assign('acc_help', ACC_HELP);        // 帮助中心分类code
        $this->assign('site_title', Conf::get('site_title'));
        $this->assign('site_logo', Conf::get('site_logo'));
        $this->assign('statistics_code', Conf::get('statistics_code')); // 统计代码
        $current_url = explode('/', $_SERVER['REQUEST_URI']);
        $count = count($current_url);
        $this->assign('current_url',  $count > 1 ? $current_url[$count-1] : $_SERVER['REQUEST_URI']);// 用于设置导航状态(以后可能会有问题)

        $ret_url="";
        if (!empty($_GET['ret_url']) && $_GET['ret_url'] != "" ){
            $ret_url = trim($_GET['ret_url']);
        }
        else{
            if (isset($_SERVER['HTTP_REFERER'])){
                $ret_url = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            }
            else{
                $ret_url ='/weixin/index.php';
            }
        }
        if(stristr($ret_url,"phoneList") != ""){
            $ret_url = '/weixin/index.php?app=cart';
        }
        if(!$_POST){
            $this->assign('ret_url', rawurlencode($ret_url));
        }

        parent::display($tpl);
    }



    //发送http请求
    function post($url, $jsonData = ''){
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        return $result;
    }

    /*根据类型获取文章*/
    function _get_news($acc){
        $acategory_mod =& m('acategory');
        $article_mod =& m('article');
        $data = $article_mod->find(array(
            'conditions' => 'cate_id=' . $acategory_mod->get_ACC($acc) . ' AND if_show = 1 and article.dropState=1',
            'order' => 'sort_order ASC, add_time DESC',
            'fields' => 'article_id, title, add_time',
            'limit' => 7,
        ));
        return $data;
    }

    function generate_code($len = 4)
    {
        $chars = '1234567890';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
            $arr[$i] = $chars[$i];
        }
        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }

    //根据位置获取广告
    function get_guanggao($position) {
        $guanggao_mod = & m('guanggao');
        $guanggao_list =$guanggao_mod->find(array(
                'conditions'=> "position='".$position."' and status =1",
                'order'=> 'sort desc'
            )
        );
        return $guanggao_list;
    }

    /**
     * 手机登录大集客微商城
     */
    function login()
    {

        if ($this->visitor->has_login)
        {
            //$this->show_warning('has_login');
            header("Location: ".SITE_URL . '/weixin/index.php');
            return;
        }
        if (!IS_POST)
        {
            if (!empty($_GET['ret_url']))
            {
                $ret_url = trim($_GET['ret_url']);
            }
            else
            {
//                if (isset($_SERVER['HTTP_REFERER']))
//                {
//                    $ret_url = $_SERVER['HTTP_REFERER'];
//                }
//                else
//                {
                    $ret_url = '/weixin/index.php';
//                }
            }
            if(stristr($ret_url,"phoneList") != ""){
                $ret_url = '/weixin/index.php?app=cart';
            }
            /* 防止登陆成功后跳转到登陆、退出的页面 */
            $ret_url = strtolower($ret_url);
            if (str_replace(array('act=login', 'act=logout',), '', $ret_url) != $ret_url)
            {
                $ret_url = SITE_URL . '/weixin/index.php';
            }

            if (Conf::get('captcha_status.login'))
            {
                $this->assign('captcha', 1);
            }
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
            $this->assign('ret_url1',SITE_URL . rawurldecode($ret_url));
            $this->_curlocal(LANG::get('user_login'));
            $this->_config_seo('title', Lang::get('user_login') . ' - ' . Conf::get('site_title'));

            $login_name = ecm_getcookie("login_name");

            if($login_name!=""){
                $this->assign('login_name', $login_name);
            }
            $this->assign('loginError', "");
            $this->display('login.html');

            /* 同步退出外部系统 */
            if (!empty($_GET['synlogout']))
            {
                $ms =& ms();
                echo $synlogout = $ms->user->synlogout();
            }
        }
        else
        {


            if (Conf::get('captcha_status.login') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->show_warning('captcha_failed');

                return;
            }

            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];

            $ms =& ms();
            $user_id = $ms->user->auth($user_name, $password);

            //记住用户名到cookie
            if($_POST["sava_user"]){
                ecm_setcookie("login_name",$_POST["user_name"],time() + 9999999);
            }

            if (!$user_id)
            {
                /* 未通过验证，提示错误信息 */
                //$this->show_warning($ms->user->get_error());
                //print_r($ms->user->get_error());
                //header("Location: index.php?app=member&act=login&synlogout=1&msg=auth_failed_byStatus");
                echo json_encode(array("flag"=>"error"));
                return;
            }
            else
            {

                $openId = ecm_getcookie('openId');

                if($openId != ""){
                    $member_model = & m('member');
                    $member_model -> edit($user_id,array("weixin_id"=>$openId));
                }

                /* 通过验证，执行登陆操作 */
                $this->_do_login($user_id);



                /* 同步登陆外部系统 */
                $synlogin = $ms->user->synlogin($user_id);
            }

            if (!empty($_GET['ret_url'])){
                $ret_url = SITE_URL.trim($_GET['ret_url']);
            }
            else{
                if (isset($_SERVER['HTTP_REFERER'])){
                    $ret_url = $_SERVER['HTTP_REFERER'];
                }
                else{
                    $ret_url = SITE_URL . '/weixin/index.php';
                }
            }
            /* 防止登陆成功后跳转到登陆、退出的页面 */
            $ret_url = strtolower($ret_url);
            if (str_replace(array('act=login', 'act=logout',), '', $ret_url) != $ret_url){
                $ret_url = SITE_URL . '/weixin/index.php';
            }

            $tmp=explode("weixin/index.php",$ret_url);

            echo json_encode(array("flag"=>"ok"));
            return;

            /*exit;
            $this->show_message(
                Lang::get('login_successed') . $synlogin,
                'back_before_login', rawurldecode($_POST['ret_url']),
                'enter_member_center', 'index.php?app=member'
            );*/

        }
    }

    /**
     *    手机注册大集微商城
     *
     *    @author    liz
     *    @return    void
     */
//    function register() {
//        $member_mod  =& m('member');
//        $msm_time = $_SESSION["msm_time"];
//        if($msm_time != "" && $msm_time >0 && gmtime() - $msm_time < 120){
//            $this->assign('t_time',120-(gmtime() - $msm_time));
//        }
//        if ($this->visitor->has_login) {
//            $this->show_warning('has_login');
//            return;
//        }
//        if (!IS_POST) {
//            if (!empty($_GET['ret_url'])) {
//                $ret_url = trim($_GET['ret_url']);
//            } else {
//                if (isset($_SERVER['HTTP_REFERER'])) {
//                    $ret_url = $_SERVER['HTTP_REFERER'];
//                } else {
//                    $ret_url = SITE_URL . '/weixin/index.php';
//                }
//            }
//            $this->assign('ret_url', rawurlencode($ret_url));
//            $this->_curlocal(LANG::get('user_register'));
//            $this->_config_seo('title', Lang::get('user_register') . ' - ' . Conf::get('user_center'));
//
//            if (Conf::get('captcha_status.register')) {
//                $this->assign('captcha', 1);
//            }
//            if(FAIL_TIME=="FAIL_TIME" || FAIL_TIME==""){
//                $this->assign('FAIL_TIME',61);
//            } else {
//                $this->assign('FAIL_TIME',FAIL_TIME);
//            }
//
//            /* 导入jQuery的表单验证插件 */
//            $this->import_resource('jquery.plugins/jquery.validate.js');
//            $this->display('register.html');
//        }
//        else {
//            $user_name = trim($_POST['user_name']);
//            $password  = $_POST['password'];
//            $phone_mob = null;
//            $email = null;
//            $email_active_code = null;
//
//            //做注册限制，每个ip最大注册为50个
////            $reg_ip   = real_ip();
////            $sql="SELECT COUNT(1) FROM ".DB_PREFIX."member WHERE reg_ip = '$reg_ip' and FROM_UNIXTIME(reg_time, '%Y%m%d' ) = DATE_FORMAT(NOW(),'%Y%m%d')";
////            $regCount = $this->_member_mod->getOne($sql);
////
////            //最大注册为可配置
////            $REG_MAX=(REG_MAX<=0 || REG_MAX=="" || REG_MAX == "REG_MAX")?50:REG_MAX;
////            if($regCount > $REG_MAX){
////                $this->import_resource('jquery.plugins/jquery.validate.js');
////                $_POST["errorMsg"]="user_reg_max";
////                $this->assign('postData', $_POST);
////                $this->display('member.register.html');
////                return;
////            }
//
//            //用户已经存在
//            if (substr(strtoupper($user_name),0,3) == "DJK") {
//                $this -> show_warning('register_username_error');
//                //header("Location: index.php?app=member&act=register&msg=register_username_exists");
//                return;
//            }
//            //请接受服务条款
//            if (!$_POST['agree']) {
//                //$this->show_warning('agree_first');
//                //header("Location: index.php?app=member&act=register&msg=agree_first");
//                $this->import_resource('jquery.plugins/jquery.validate.js');
//                $_POST["errorMsg"]="agree_first";
//                $this->assign('postData', $_POST);
//                $this->display('member.register.html');
//                return;
//            }
//            if (Conf::get('captcha_status.register') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
//                //$this->show_warning('captcha_failed');
//                //header("Location: index.php?app=member&act=register&msg=captcha_failed");
//                $this->import_resource('jquery.plugins/jquery.validate.js');
//                $_POST["errorMsg"]="captcha_failed";
//                $this->assign('postData', $_POST);
//                $this->display('member.register.html');
//                return;
//            }
//            //两次输入的密码不一致
//            if ($password != $_POST['password_confirm']) {
//                // $this->show_warning('inconsistent_password');
//                //header("Location: index.php?app=member&act=register&msg=inconsistent_password");
//                $this->import_resource('jquery.plugins/jquery.validate.js');
//                $_POST["errorMsg"]="inconsistent_password";
//                $this->assign('postData', $_POST);
//                $this->display('member.register.html');
//                return;
//            }
//            $passlen = strlen($password);
//            $user_name_len = strlen($user_name);
//            //用户名长度只能在3-20位字符之间
//            if ($user_name_len < 3 || $user_name_len > 25) {
//                //$this->show_warning('user_name_length_error');
//                //header("Location: index.php?app=member&act=register&msg=user_name_length_error");
//                $this->import_resource('jquery.plugins/jquery.validate.js');
//                $_POST["errorMsg"]="user_name_length_error";
//                $this->assign('postData', $_POST);
//                $this->display('member.register.html');
//                return;
//            }
//            //密码长度只能在6-20位字符之间
//            if ($passlen < 6 || $passlen > 20) {
//                //$this->show_warning('password_length_error');
//                //header("Location: index.php?app=member&act=register&msg=password_length_error");
//                $this->import_resource('jquery.plugins/jquery.validate.js');
//                $_POST["errorMsg"]="password_length_error";
//                $this->assign('postData', $_POST);
//                $this->display('member.register.html');
//                return;
//            }
//
//            //手机注册时候，短信验证码是否正确
//            if (is_mobile($user_name)) {
//                if (!$_POST["yanzhengma"] || $_POST["yanzhengma"] != $_SESSION["yanzhengma"]) {
//                    //header("Location: index.php?app=member&act=register&msg=yanzhengmaerror");
//                    $this->import_resource('jquery.plugins/jquery.validate.js');
//                    $_POST["errorMsg"]="yanzhengmaerror";
//                    $this->assign('postData', $_POST);
//                    $this->display('member.register.html');
//                    return;
//                }
//                $phone_mob = $user_name;
//            }
//            //用户已经存在
//            $data=$this->_member_mod->find("(user_name='". $user_name. "' or phone_mob='".$user_name."' or email='".$user_name."')");
//            if(count($data)>0){
//                //header("Location: index.php?app=member&act=register&msg=mob_or_email_find");
//                $this->import_resource('jquery.plugins/jquery.validate.js');
//                $_POST["errorMsg"]="mob_or_email_find";
//                $this->assign('postData', $_POST);
//                $this->display('register.html');
//                return;
//            }
//
//            //邮箱注册，生成邮箱绑定激活码
////            if (is_email($user_name)) {
////                $email = $user_name;
////                $email_active_code = base64_encode(gmtime() . $email);
////            }
//
//            $t_id = null;
//            //有邀请人就写入邀请人，没有就写入默认的邀请人
//            $cookie_t_id  = ecm_getcookie("t_id");
//            $session_t_id = $_SESSION['t_id'];
//
//            if ($cookie_t_id || $session_t_id) {
//                //$t_id = iconv("gb2312","UTF-8",base64_decode(trim(( ($session_t_id!="") ? $session_t_id : $cookie_t_id) )));
//                $t_id =base64_decode(trim(( ($session_t_id!="") ? $session_t_id : $cookie_t_id) ));
//                $dateInfo=$member_mod->get_info($t_id);
//                if (!empty($dateInfo)) {
//                    $t_id = $dateInfo["user_id"];
//                }else{
//                    $t_id = "";
//                }
//            }
//
//            $ms =& ms(); //连接用户中心
//            $local_data['user_name']       = $user_name;
//            $local_data['password']        = md5($password);
//            $local_data['nick_name']       = $user_name;
//            $local_data['reg_ip']          =real_ip();
//            if ($t_id != null && $t_id != "") {
//                //$local_data['jifen']=5;         //推广连接进来的送5积分
//                $local_data['t_id'] = $t_id;
//            }
//            $local_data['reg_time']         = gmtime();
//            $local_data['member_type']     = "01";
//            if ($phone_mob != null) {
//                $local_data['phone_mob'] = $phone_mob;
//                $local_data['phone_mob_bind_status'] = 1;
//            }
//            if ($email != null) {
//                $local_data['email'] = $email;
//                $local_data['email_active_code'] = $email_active_code;
//                $local_data['email_bind_status'] = 0;
//            }
//            if( $_GET['weixin_id'] != null ){
//                $local_data['weixin_id'] = $_GET('weixin_id');
//            }
//            $user_id = $member_mod->add($local_data);
//            if (!$user_id)
//            {
//                $this->show_warning($ms->user->get_error());
//                return;
//            }
//
//            /*if ($t_id != null) {
//                ini_set('date.timezone','Asia/Shanghai');
//                $data=array("user_id"=>$user_id,
//                    "jifen"=>5,
//                    "beizhu"=>"您通过推广连接注册,您获得5积分！",
//                    "create_time"=>date('Y-m-d H:i:s',time()),
//                    "type"=>3
//                );
//                $jifenlog_mod =& m('jifenlog');
//                $jifenlog_mod->add($data);              //记录积分log
//            }*/
//
//            $member_mod->commit_transaction();
//
//            $params = null;
//            $params["type"] = "regist";
//            $params["userId"] = $user_id;
//            $params["tId"] = ($t_id>0) ? $t_id : 0;
//            $params["orderId"] ="";
//            $params["userName"] ="";
//            $params["channelCode"] ="";
//            $params["channelName"] ="";
//            $params["channelCard"] ="";
//            $params["jine"] ="0";
//            $params["regionId"] ="0";
//            $Client = new HttpClient(JAVA_LOCATION);
//            $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
//            $pageContents = HttpClient::quickPost($url, $params);
//            //$data =json_decode($pageContents);           这里忽略反馈结果
//
//            //发送绑定邮件
////            if ($email != null) {
////                $content = file_get_contents(ROOT_PATH . "/themes/mall/default/regist_email_content.html");
////                $content = str_replace("%s1", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
////                $content = str_replace("%s2", SITE_URL . "/index.php?app=sendcode&act=active&code=" . $email_active_code, $content);
////                include_once(ROOT_PATH . '/app/sendcode.app.php');
////                $sender = new SendCodeApp();
////                $sender -> send_email($email, "欢迎绑定邮箱到大集客", $content);
////            }
//
//            $this->_hook('after_register', array('user_id' => $user_id));
//            //登录
//            $this->_do_login($user_id);
//
//            /* 同步登陆外部系统 */
////            $synlogin = $ms->user->synlogin($user_id);
//
//            /*$this->show_message(Lang::get('register_successed') . $synlogin,
//                'index', 'index.php?app=member&p=userInfo&type=first'
//            );*/
//            header("Location: /weixin/index.php?app=member&act=register_success");
//        }
//    }

    function pop_warning ($msg, $dialog_id = '',$url = '')
    {
        if($msg == 'ok')
        {
            if(empty($dialog_id))
            {
                $dialog_id = APP . '_' . ACT;
            }
            if (!empty($url))
            {
                echo "<script type=\"text/javascript\">document.domain ='".DOMAIN."';window.parent.location.href='".$url."';</script>";
            }
            echo "<script type=\"text/javascript\">document.domain ='".DOMAIN."';window.parent.js_success('" . $dialog_id ."');</script>";
        }
        else
        {
            header("Content-Type:text/html;charset=".CHARSET);
            $msg = is_array($msg) ? $msg : array(array('msg' => $msg));
            $errors = '';
            foreach ($msg as $k => $v)
            {
                $error = $v[obj] ? Lang::get($v[msg]) . " [" . Lang::get($v[obj]) . "]" : Lang::get($v[msg]);
                $errors .= $errors ? "<br />" . $error : $error;
            }
            echo "<script type=\"text/javascript\">document.domain ='".DOMAIN."';window.parent.js_fail('" . $errors . "');</script>";
        }
    }

    function logout()
    {
        $this->visitor->logout();
        ecm_setcookie("u_id","",time() + 86400);
        ecm_setcookie("t_id","",time() + 86400);
        $url = "Location: index.php?app=member&act=login&synlogout=1";
        if($_GET['ret_url']){
            $url.="&ret_url=".$_GET['ret_url'];
        }
        /* 跳转到登录页，执行同步退出操作 */
        header($url);
        return;
    }

    /* 执行登录动作 */
    function _do_login($user_id)
    {
        $mod_user =& m('member');

        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',                 //关联查找看看是否有店铺
            //'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id,member_type,member.region_id,member.region_name,member.nick_name,member.jifen,spreader_type,nick_name',
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id,member_type,member.region_id,member.region_name,member.nick_name,spreader_type,nick_name',
        ));


        /* 店铺ID */
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];

        /* 保证基础数据整洁 */
        //unset($user_info['store_id']);

        /* 分派身份 */
        $this->visitor->assign($user_info);



        /* 更新用户登录信息 */
        $mod_user->edit("user_id = '{$user_id}'", "last_login = '" . gmtime()  . "', last_ip = '" . real_ip() . "', logins = logins + 1");

        /* 更新购物车中的数据 */
        $mod_cart =& m('cart');
        $mod_cart->edit("(user_id = '{$user_id}' OR session_id = '" . SESS_ID . "') AND store_id <> '{$my_store}'", array(
            'user_id'    => $user_id,
            'session_id' => SESS_ID,
        ));

        /* 去掉重复的项 */
        $cart_items = $mod_cart->find(array(
            'conditions'    => "user_id='{$user_id}' GROUP BY spec_id",
            'fields'        => 'COUNT(spec_id) as spec_count, spec_id, rec_id',
        ));
        if (!empty($cart_items))
        {
            foreach ($cart_items as $rec_id => $cart_item)
            {
                if ($cart_item['spec_count'] > 1)
                {
                    $mod_cart->drop("user_id='{$user_id}' AND spec_id='{$cart_item['spec_id']}' AND rec_id <> {$cart_item['rec_id']}");
                }
            }
        }
    }

    /* 取得导航 */
    function _get_navs()
    {
        $cache_server = & cache_server();
        $key = 'common.navigation';
        $data = $cache_server->get($key);
        if($data === false)
        {
            $data = array(
                'header' => array(),
                'middle' => array(),
                'footer' => array(),
            );
            $nav_mod =& m('navigation');
            $rows = $nav_mod->find(array(
                'order' => 'type, sort_order',
            ));
            foreach ($rows as $row)
            {
                $data[$row['type']][] = $row;
            }
            $cache_server->set($key, $data, 86400);
        }

        return $data;
    }

    /**
     *    获取JS语言项
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function jslang()
    {
        $lang = Lang::fetch(lang_file('jslang'));
        parent::jslang($lang);
    }

    /**
     *    视图回调函数[显示小挂件]
     *
     *    @author    Garbin
     *    @param     array $options
     *    @return    void
     */
    function display_widgets($options)
    {
        $area = isset($options['area']) ? $options['area'] : '';
        $page = isset($options['page']) ? $options['page'] : '';
        if (!$area || !$page)
        {
            return;
        }
        include_once(ROOT_PATH . '/includes/widget.base.php');

        /* 获取该页面的挂件配置信息 */
        $widgets = get_widget_config($this->_get_template_name(), $page);

        /* 如果没有该区域 */
        if (!isset($widgets['config'][$area]))
        {
            return;
        }

        /* 将该区域内的挂件依次显示出来 */
        foreach ($widgets['config'][$area] as $widget_id)
        {
            $widget_info = $widgets['widgets'][$widget_id];
            $wn     =   $widget_info['name'];
            $options=   $widget_info['options'];

            $widget =& widget($widget_id, $wn, $options);
            $widget->display();
        }
    }

    /**
     *    获取当前使用的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        return 'default';
    }

    /**
     *    获取当前使用的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        return 'default';
    }

    /**
     *    当前位置
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _curlocal($arr)
    {
        $curlocal = array(array(
            'text'  => Lang::get('index'),
            'url'   => SITE_URL . '/weixin/index.php',
        ));
        if (is_array($arr))
        {
            $curlocal = array_merge($curlocal, $arr);
        }
        else
        {
            $args = func_get_args();
            if (!empty($args))
            {
                $len = count($args);
                for ($i = 0; $i < $len; $i += 2)
                {
                    $curlocal[] = array(
                        'text'  =>  $args[$i],
                        'url'   =>  $args[$i+1],
                    );
                }
            }
        }

        $this->assign('_curlocal', $curlocal);
    }
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new UserVisitor());
    }
}
/**
 *    前台访问者
 *
 *    @author    Garbin
 *    @usage    none
 */
class UserVisitor extends BaseVisitor
{
    var $_info_key = 'user_info';

    /**
     *    退出登录
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function logout()
    {
        /* 将购物车中的相关项的session_id置为空 */
        $mod_cart =& m('cart');
        $mod_cart->edit("user_id = '" . $this->get('user_id') . "'", array(
            'session_id' => '',
        ));

        /* 退出登录 */
        parent::logout();
    }
}
/**
 *    商城控制器基类
 *
 *    @author    Garbin
 *    @usage    none
 */
class MallbaseApp extends FrontendApp
{


    function getip()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        {
            $ip = getenv("HTTP_CLIENT_IP");
        } else
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"),
                    "unknown"))
            {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                {
                    $ip = getenv("REMOTE_ADDR");
                } else
                    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],
                            "unknown"))
                    {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else
                    {
                        $ip = "unknown";
                    }
        return $ip;
    }

    function getCity(){
        $ip = $this -> getip();
//        $ip="219.139.210.155";
        $url='http://www.ip138.com/ips138.asp?ip='.$ip.'&action=2';
//echo $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
//设置URL，可以放入curl_init参数中
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1");
//设置UA
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。如果不加，即使没有echo,也会自动输出
        $content = curl_exec($ch);
//执行
        curl_close($ch);
//关闭
//echo $content;
//<li>本站主数据：湖北省武汉市 电信</li>
        $content=iconv('GB2312', 'UTF-8', $content);//content本身是gb3212的文件是utf8的所以要转码
        preg_match('/本站主数据：(?<mess>(.*))市(.*)<\/li><li>/',$content,$arr);//print_r($arr['mess']);exit;
//查询注意事项
        if(strripos($arr['mess'],"省") > 0){
            $city1 = explode("省",$arr['mess']);
            $city = $city1['1'];
        }else{
            $city = $arr['mess'];
        }
        return $city;
    }


    /*判断是否登录，没登陆的去登录*/
    function isLogin(){
        $u_id = $_GET['u_id'];
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);
        if(!$user){
            if (isset($_SERVER['QUERY_STRING'])){
                $ret_url = "/weixin/index.php?" . $_SERVER['QUERY_STRING'];
                $ret_url = urlencode($ret_url);
            }
            else{
                $ret_url ='/weixin/index.php';
            }
            if($u_id != ""){
                header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&u_id=" . $u_id. "&ret_url=" . $ret_url);
            }else{
                header("Location: ". SITE_URL . "/weixin/index.php?app=member&act=login&ret_url=" . $ret_url);
            }
        }
    }

    /**
     * @description 根据用户id 获取member_finance 相关信息(保留小数点后两位数组，不四舍五入)
     * @param $userId
     * @return member_finance
     * @author liz
     */
    function getMemberFinance($userId){
        $member_finance = array();
        if(!$userId || $userId==null || $userId=="" || $userId <=0 ){
            return $member_finance;
        }
        $member_finance_mod = &m("memberfinance");
        $member_finance_info = $member_finance_mod->get("user_id=".$userId);
        if(!$member_finance_info || count($member_finance_info) ==0){
            return $member_finance;
        }
        $member_finance_info['unkoushui_yue'] = ((int)( $member_finance_info['unkoushui_yue']*100))/100;
        $member_finance_info['koushui_yue'] = ((int)( $member_finance_info['koushui_yue']*100))/100;
        return $member_finance_info;
    }

    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && in_array(APP, array('apply')))
        {
            header('Location: index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

            return;
        }

        /* 取得商品分类 */
        $cache = & cache_server();
        $gcategorys = $cache->get('gcategorys');
        if($gcategorys === false){
            $gcategorys = $this->_list_gcategory(1);
            $cache->set('gcategorys',$gcategorys,3600);
        }
        $this->assign('gcategorys_left', $gcategorys);
        //获取所有省市
        $region_mod =& m('region');
        $tmp=$region_mod->get_options(1);
        $this->assign('region',$tmp);
        $this->assign('gcategorys_left', $gcategorys);
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        parent::_run_action();
    }
//热门搜索


//自动获取access_token
    function get_access_token(){
        session_start();
        if( isset($_SESSION['access_token']) && isset($_SESSION['expires_in']) &&  isset($_SESSION['fetch_time'])){
            $seconds = time() - intval($_SESSION['fetch_time']);
        };
        //超过2小时，自动获取access_token
        if( !isset($seconds)|| $seconds >= 7200 ){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx7ac999e64dae7632&secret=e5e2555765f061e4ed2d3305a5155162";
            $jsonResult =  $this->post($url);
            $array = json_decode($jsonResult,true);
            $_SESSION['access_token'] = $array['access_token'];
            $_SESSION['expires_in'] = $array['expires_in'];
            $_SESSION['fetch_time'] = time();
            return $array;
        //两小时之内，在session获取access_token信息
        }else{
            return array("access_token"=>$_SESSION['access_token'],
                           "expires_in"=>$_SESSION['expires_in'],
                           "fetch_time"=>$_SESSION['fetch_time']);
        }
    }


//发送客服消息
    function sendClientServiceMsg($openId,$msg){
        $result = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$result['access_token'];
        $json = "{
                \"touser\":\"$openId\",
                \"msgtype\":\"text\",
                \"text\":
                    {
                        \"content\":\"$msg\"
                    }
               }";
        $this->post($url,$json);
    }

//发送http请求
    function post($url, $jsonData = ''){
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        return $result;
    }

    /**
     *  计算两组经纬度坐标 之间的距离
     *   params ：lat1 纬度1； lng1 经度1； lat2 纬度2； lng2 经度2；
     *   return : m
     */
    function getDistance($lat1, $lng1, $lat2, $lng2){
        $lat1 = floatval($lat1);
        $lng1 = floatval($lng1);
        $lat2 = floatval($lat2);
        $lng2 = floatval($lng2);
        $EARTH_RADIUS = 6378.137;
        $radLat1 = $this->rad($lat1);
        //echo $radLat1;
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) +
            cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s *$EARTH_RADIUS;
        //$s结果形式：
        //1.4544330558519千米(精确坐标)
        //1.4544101877862千米（标准坐标）
        //精确坐标与标准坐标误差2cm
        return round(floatval($s)*1000);
    }
    function rad($d){
        return $d * 3.1415926535898 / 180.0;
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    //根据地区名称获取店铺ID
    function get_store_byarea($param) {
        $store_mode = & m('store');

        $sql = "select region_name,store_id from {$store_mode->table} where state=1 and dropState = 1";
        $res = $store_mode->db->query($sql);
        $data =null;
        while ($row = $store_mode->db->fetchRow($res))
        {
            $data[$row['store_id']] = $row['region_name'];
        }
        $region_name = null;
        if($data) {
            foreach ($data as $key=>$val) {
                $arr = explode("\t",$val);
                $region_name[$key] =$arr[1];
            }
        }
        $stroe_ids = null;
        foreach ($region_name as $key=>$str) {
            if($param["area"]==$str) {
                if($stroe_ids==null) {
                    $stroe_ids= $key;
                }else {
                    $stroe_ids.=",".$key;
                }
            }
        }
        return $stroe_ids;
    }

    //获得左侧菜单
    /* 取得商品分类 */
    function _list_gcategory($pid)
    {
        $cache_server =& cache_server();
        $key = 'page_goods_category';
        $data = $cache_server->get($key);

//        if ($data === false) {
        $gcategory_mod =& bm('gcategory', array('_store_id' => 0));

        $_pdgcategory_mod = bm("pdcategory");
        $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($pid);
        $gcategories = array();
        if ($cate_ids) {
            foreach ($cate_ids as $cate_id) {
                if ($gcategory_mod->get($cate_id)) {
                    $gcategories[] = $gcategory_mod->get($cate_id);
                    $arr=$gcategory_mod->get_list($cate_id,$shown=true);
                    foreach($arr as $k=>$v)
                    {
                        $gcategories[]=array(
                            'cate_id'=>$v["cate_id"],
                            'store_id'=>$v["store_id"],
                            'cate_name'=>$v["cate_name"],
                            'parent_id'=>$v["parent_id"],
                            'sort_order'=>$v["sort_order"],
                            'if_show'=>$v["if_show"],
                        );
                        if ($gcategory_mod->get_list($v["cate_id"])) {
                            $soncategory = $gcategory_mod->get_list($v["cate_id"],$shown=true);
                            foreach ($soncategory as $val) {
                                $gcategories[]=array(
                                    'cate_id'=>$val["cate_id"],
                                    'store_id'=>$val["store_id"],
                                    'cate_name'=>$val["cate_name"],
                                    'parent_id'=>$val["parent_id"],
                                    'sort_order'=>$val["sort_order"],
                                    'if_show'=>$val["if_show"],
                                );
                            }
                        }
                    }
                }
            }
        }
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        $data = $tree->getArrayList(0);
        $cache_server->set($key, $data, 3600);
//        }
        return $data;
    }



    function _config_view()
    {
        parent::_config_view();

        $this->_view->template_dir = ROOT_PATH . "/weixin/templates/";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mall/";
        $this->_view->res_base     = STATIC_URL . "/weixin/templates/style/";
    }

    /* 取得支付方式实例 */
    function _get_payment($code, $payment_info)
    {
        include_once(ROOT_PATH . '/includes/payment.base.php');
        include(ROOT_PATH . '/includes/payments/' . $code . '/' . $code . '.payment.php');
        $class_name = ucfirst($code) . 'Payment';

        return new $class_name($payment_info);
    }

    /**
     *   获取当前所使用的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        $template_name = Conf::get('template_name');
        if (!$template_name)
        {
            $template_name = 'default';
        }

        return $template_name;
    }

    /**
     *    获取当前模板中所使用的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        $style_name = Conf::get('style_name');
        if (!$style_name)
        {
            $style_name = 'default';
        }

        return $style_name;
    }
}

/**
 *    购物流程子系统基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class ShoppingbaseApp extends MallbaseApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {
            if (!IS_AJAX)
            {
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }
       /*服务中心和托管商家不能购买商品*/
        if ($this->visitor->get('member_type') != '01')
        {
//            $this->pr('服务中心或者托管商家不能购买商品');
            $this->show_message('服务中心或者托管的商家不能购买商品',
                'index', 'index.php?app=cart');
            return;
        }

        parent::_run_action();
    }
}

/**
 *    用户中心子系统基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class MemberbaseApp extends MallbaseApp
{
    function _run_action()
    {
        if($_GET["app"]=="service" && ($_GET["act"]=="register_service" ||  $_GET["act"]=="serachService" ||  $_GET["act"]=="queryByqiangzhu"))
        {

        }else
        {
            /* 只有登录的用户才可访问 */
            if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user','check_invi_code','check_user2','cj','qq_login','check_user_or_phone_mob','check_pwd')))
            {
                if (!IS_AJAX)
                {
                    header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                    return;
                }
                else
                {
                    $this->json_error('login_please');
                    return;
                }
            }


            @session_start();
            if($_GET["p"])
            {
                $_SESSION["p"]=$_GET["p"];
            }
        }

        $mod_user =& m('member');
        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$this->visitor->get('user_id')}'"
        ));
        $this->assign('login_user', $user_info);
        parent::_run_action();
    }
    /**
     *    当前选中的菜单项
     *
     *    @author    Garbin
     *    @param     string $item
     *    @return    void
     */
    function _curitem($item)
    {
    	@session_start();
		if($_GET["p"])
		{
			$_SESSION["p"]=$_GET["p"];
		}
		$this->assign('pl',$_SESSION["p"]);
        $this->assign('has_store', $this->visitor->get('has_store'));
        //$this->pr($this->_get_member_menu());
        $this->assign('_member_menu', $this->_get_member_menu());
        $this->assign('_curitem', $item);
        $mod_user =& m('member');
        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$this->visitor->get('user_id')}'",
            'join'          => 'has_store',
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id ,member_type,member.region_name,member.region_id,member.nick_name,member.jifen',
        ));
        $this->assign('init_user', $user_info);
    }
    /**
     *    当前选中的子菜单
     *
     *    @author    Garbin
     *    @param     string $item
     *    @return    void
     */
    function _curmenu($item)
    {
        $_member_submenu = $this->_get_member_submenu();
        foreach ($_member_submenu as $key => $value)
        {
            $_member_submenu[$key]['text'] = $value['text'] ? $value['text'] : Lang::get($value['name']);
        }

        $this->assign('_member_submenu', $_member_submenu);
        $this->assign('_curmenu', $item);
    }
    /**
     *    获取子菜单列表
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_member_submenu()
    {
        return array();
    }
    /**
     *
     *判断是否是采购员
     *
     **/
    function _checkService()
    {
        $this->_member_mod =& m('member');
        $user = $this->visitor->get();
        $userInfo=$this->_member_mod->get(array(
                                          "conditions"=>"user_id=".$user['user_id'],
                                          "fields"    => "this.*,(select dropState from ".DB_PREFIX."store s where s.store_id=member.user_id) as \"store_status\""
        ));
        return $userInfo;
    }
    /**
     *    获取用户中心全局菜单列表
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_member_menu()
    {
        $menu = array();
        $validata=$this->_checkService();
        /* 我的ECMall */
     if ($validata["member_type"]=="01"){
         $menu['my_ecmall'] = array(
            'name'  => 'my_ecmall',
             'id'   => 'userInfo',
            'text'  => Lang::get('my_ecmall'),
            'submenu'   => array(
                'overview'  => array(
                    'text'  => Lang::get('overview'),
                    'url'   => 'index.php?app=member&p=userInfo',
                    'name'  => 'overview',
                    'icon'  => 'ico1',
                ),
                'my_profile'  => array(
                    'text'  => Lang::get('my_profile'),
                    'url'   => 'index.php?app=member&act=profile&p=userInfo',
                    'name'  => 'my_profile',
                    'icon'  => 'ico2',
                ),
                'cj'  => array(
                    'text'  => Lang::get('woyaochoujiang'),
                    'url'   => 'index.php?app=member&act=cj&p=userInfo',
                    'name'  => 'cj',
                    'icon'  => 'cj',
                ),
                'qiangzhu'  => array(
                    'text'  => Lang::get('wodeqiangzhu'),
                    'url'   => 'index.php?app=member&act=qiangzhu&p=userInfo',
                    'name'  => 'qiangzhu',
                    'icon'  => 'qiangzhu',
                ),
                'aqzx'  => array(
                    'text'  => Lang::get('aqzx'),
                    'url'   => 'index.php?app=member&act=aqzx&p=userInfo',
                    'name'  => 'aqzx',
                    'icon'  => 'ico18',
                ),
                'bank' => array(
                    'text'  => Lang::get('bank'),
                    'url'   => 'index.php?app=member&act=bank&p=userInfo',
                    'name'  => 'bank',
                    'icon'  => 'bank',
                ),
            ),
        );


        /* 我是买家 */
        $menu['im_buyer'] = array(
            'name'  => 'im_buyer',
            'id'=>  'buyer',
            'text'  => Lang::get('im_buyer'),
            'submenu'   => array(
                'my_order'  => array(
                    'text'  => Lang::get('my_order'),
                    'url'   => 'index.php?app=buyer_order&p=buyer',
                    'name'  => 'my_order',
                    'icon'  => 'ico5',
                ),
               /* 'my_groupbuy'  => array(
                    'text'  => Lang::get('my_groupbuy'),
                    'url'   => 'index.php?app=buyer_groupbuy',
                    'name'  => 'my_groupbuy',
                    'icon'  => 'ico21',
                ),*/
                'my_question' =>array(
                    'text'  => Lang::get('my_question'),
                    'url'   => 'index.php?app=my_question&p=buyer',
                    'name'  => 'my_question',
                    'icon'  => 'ico17',

                ),
                'my_favorite'  => array(
                    'text'  => Lang::get('my_favorite'),
                    'url'   => 'index.php?app=my_favorite&p=buyer',
                    'name'  => 'my_favorite',
                    'icon'  => 'ico6',
                ),
                'my_address'  => array(
                    'text'  => Lang::get('my_address'),
                    'url'   => 'index.php?app=my_address&p=buyer',
                    'name'  => 'my_address',
                    'icon'  => 'ico7',
                ),
            ),
        );

        if (!$this->visitor->get('has_store') && Conf::get('store_allow') || $validata["store_status"]==0)
        {
            $menu['overview'] = array(
                'text' => Lang::get('apply_store'),
                'url'  => 'index.php?app=apply&step=2&id=2&p=seller',
            );
        }
        if ($this->visitor->get('manage_store') && $validata["store_status"]==1 )
        {
            /* 指定了要管理的店铺 */
            $menu['im_seller'] = array(
                'name'  => 'im_seller',
                'id'    => 'seller',
                'text'  => Lang::get('im_seller'),
                'submenu'   => array(),
            );

            $menu['im_seller']['submenu']['my_goods'] = array(
                'text'  => Lang::get('my_goods'),
                'url'   => 'index.php?app=my_goods&p=seller',
                'name'  => 'my_goods',
                'icon'  => 'ico8',
            );
            $menu['im_seller']['submenu']['groupbuy_manage'] = array(
                'text'  => Lang::get('groupbuy_manage'),
                'url'   => 'index.php?app=seller_groupbuy&p=seller',
                'name'  => 'groupbuy_manage',
                'icon'  => 'ico22',
            );
            $menu['im_seller']['submenu']['my_qa'] = array(
                'text'  => Lang::get('my_qa'),
                'url'   => 'index.php?app=my_qa&p=seller',
                'name'  => 'my_qa',
                'icon'  => 'ico18',
            );
            $menu['im_seller']['submenu']['my_category'] = array(
                'text'  => Lang::get('my_category'),
                'url'   => 'index.php?app=my_category&p=seller',
                'name'  => 'my_category',
                'icon'  => 'ico9',
            );
            $menu['im_seller']['submenu']['order_manage'] = array(
                'text'  => Lang::get('order_manage'),
                'url'   => 'index.php?app=seller_order&p=seller',
                'name'  => 'order_manage',
                'icon'  => 'ico10',
            );
            $menu['im_seller']['submenu']['my_store']  = array(
                'text'  => Lang::get('my_store'),
                'url'   => 'index.php?app=my_store&p=seller',
                'name'  => 'my_store',
                'icon'  => 'ico11',
            );
            $menu['im_seller']['submenu']['my_shipping'] = array(
                'text'  => Lang::get('my_shipping'),
                'url'   => 'index.php?app=my_shipping&p=seller',
                'name'  => 'my_shipping',
                'icon'  => 'ico14',
            );
            /*$menu['im_seller']['submenu']['my_navigation'] = array(
                'text'  => Lang::get('my_navigation'),
                'url'   => 'index.php?app=my_navigation&p=seller',
                'name'  => 'my_navigation',
                'icon'  => 'ico15',
            );*/
            $menu['im_seller']['submenu']['yushou_manage'] = array(
                'text'  => Lang::get('yushou_manage'),
                'url'   => 'index.php?app=seller_yushou&p=seller&p=seller',
                'name'  => 'yushou_manage',
                'icon'  => 'ico22',
            );
            $menu['im_seller']['submenu']['temai_manage'] = array(
                'text'  => Lang::get('temai_manage'),
                'url'   => 'index.php?app=seller_temai&p=seller&p=seller',
                'name'  => 'temai_manage',
                'icon'  => 'ico22',
            );
        }

        $menu['im_tuiguang'] = array(
            'name'  => 'im_tuiguang',
            'text'  => Lang::get('im_tuiguang'),
            'id'    =>'tuiguang',
            'submenu'   => array(
                'tuiguang'  => array(
                    'text'  => Lang::get('tuiguang'),
                    'url'   => 'index.php?app=tuiguang&act=tuiguang&p=tuiguang',
                    'name'  => 'tuiguang',
                    'icon'  => 'tuiguang',
                ),
                'tuiguang_member'  => array(
                    'text'  => Lang::get('tuiguang_member'),
                    'url'   => 'index.php?app=tuiguang&act=tuiguang_member&p=tuiguang',
                    'name'  => 'tuiguang_member',
                    'icon'  => 'tuiguang_member',
                ),
                /*'tuiguang_store'  => array(
                    'text'  => Lang::get('tuiguang_store'),
                    'url'   => 'index.php?app=tuiguang&act=tuiguang_store&p=tuiguang',
                    'name'  => 'tuiguang_store',
                    'icon'  => 'tuiguang_store',
                ),*/
                'uploadStore'  => array(
                    'text'  => Lang::get('uploadStore'),
                    'url'   => 'index.php?app=tuiguang&act=uploadStore&p=tuiguang',
                    'name'  => 'uploadStore',
                    'icon'  => 'service',
                ),
                'brzc'  => array(
                    'text'  => '帮人注册',
                    'url'   => 'index.php?app=tuiguang&act=brzc&p=tuiguang',
                    'name'  => 'brzc',
                    'icon'  => 'service',
                ),
                'shouyi'  => array(
                    'text'  => Lang::get('shouyi'),
                    'url'   => 'index.php?app=tuiguang&act=shouyi&p=tuiguang',
                    'name'  => 'shouyi',
                    'icon'  => 'shouyi',
                ),

            ));
     }

        if ($validata["member_type"]=="02" || $validata["member_type"]=="03" || $validata["member_type"]=="04")
        {
            $showTxt=Lang::get('service_info2');
            $showTitle=Lang::get('service');
            if($validata["member_type"]=="04")
            {
                $showTxt=Lang::get('service_info1');
                $showTitle=Lang::get('caigou_title');
            }
            $menu['im_service'] = array(
                'name'  => 'im_service',
                'text'  => $showTitle,
                'id'    => 'service',
            );
            if($validata["member_type"]=="02"){
                $menu['im_service']['submenu']['index']=array(
                    'text'  => '帐号概览',
                    'url'   => 'index.php?app=service&p=service&tuoguan=1',
                    'name'  => 'index',
                    'icon'  => 'ico6',
                );
                $menu['im_service']['submenu']['service_info']=array(
                    'text'  => $showTxt,
                    'url'   => 'index.php?app=service&act=service_info&p=service',
                    'name'  => 'service_info',
                    'icon'  => 'ico6',
                );

                $menu['im_service']['submenu']['service_info_shouyi']=array(
                    'text'  => Lang::get('shouyi'),
                    'url'   => 'index.php?app=service&act=shouyi&p=service',
                    'name'  => 'shouyi',
                    'icon'  => 'shouyi',
                );
            }
            $menu['im_service']['submenu']['im_service']=array(
                'text'  => Lang::get('im_service'),
                'url'   => 'index.php?app=service&act=service&p=service&tuoguan=1',
                'name'  => 'service',
                'icon'  => 'service',
            );

            $menu['im_service']['submenu']['shangpinguanli']=array(
                'text'  => Lang::get('my_goods'),
                'url'   => 'index.php?app=service&act=my_goods&p=service',
                'name'  => 'my_goods',
                'icon'  => 'ico8',
            );

            $menu['im_service']['submenu']['peisong']=array(
                'text'  => Lang::get('peisong'),
                'url'   => 'index.php?app=service&act=peisong&p=service',
                'name'  => 'peisong',
                'icon'  => 'ico14',
            );

            $menu['im_service']['submenu']['tuangou']=array(
                'text'  => Lang::get('tuangou'),
                'url'   => 'index.php?app=service&act=tuangou&p=service',
                'name'  => 'tuangou',
                'icon'  => 'ico22',
            );

            $menu['im_service']['submenu']['orders']=array(
                'text'  => Lang::get('orders'),
                'url'   => 'index.php?app=service&act=orders&step=1&p=service',
                'name'  => 'orders',
                'icon'  => 'ico10',
            );
            /*if($validata["member_type"]!="01" && $validata["member_type"]!="04"){
                $menu['im_service']['submenu']['kaidan']=array(
                    'text'  => Lang::get('kaidan'),
                    'url'   => 'index.php?app=service&act=kaidan&step=1&p=service',
                    'name'  => 'kaidan',
                    'icon'  => 'kaidan',
                );
            }*/
            if($validata["member_type"]=="02"){
                //服务中心子账号管理
                $menu['im_service']['submenu']['zizhanghu']=array(
                    'text'  => Lang::get('zizhanghu'),
                    'url'   => 'index.php?app=service&act=zizhanghu&p=service',
                    'name'  => 'zizhanghu',
                    'icon'  => 'ico4',
                );
            }
            $menu['im_service']['submenu']['zxkf']  = array(
                'text'  => Lang::get('zxkf'),
                'url'   => 'index.php?app=service&act=zxkf&p=service',
                'name'  => 'zxkf',
                'icon'  => 'zxkf',
            );
            $menu['im_service']['submenu']['spzx']  = array(
                'text'  => Lang::get('spzx'),
                'url'   => 'index.php?app=service&act=spzx&p=service',
                'name'  => 'spzx',
                'icon'  => 'ico18',
            );
            if($validata["member_type"]=="02"){
                $menu['im_service']['submenu']['aqzx']  = array(
                    'text'  => Lang::get('aqzx'),
                    'url'   => 'index.php?app=service&act=aqzx&p=service',
                    'name'  => 'aqzx',
                    'icon'  => 'ico18',
                );
            }
            $menu['im_service']['submenu']['bank']  = array(
                'text'  => Lang::get('bank'),
                'url'   => 'index.php?app=member&act=bank&p=service',
                'name'  => 'bank',
                'icon'  => 'bank',
            );
        }

        return $menu;
    }
}

/**
 *    店铺管理子系统基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class StoreadminbaseApp extends MemberbaseApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user','check_user2')))
        {
            if (!IS_AJAX)
            {
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {

                $this->json_error('login_please');
                return;
            }
        }

        //
        @session_start();
        $_SESSION["p"]="seller";


        if(!$_GET["app"]=="service")
        {
                $referer = $_SERVER['HTTP_REFERER'];
                if (strpos($referer, 'act=login') === false)
                {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                    $ret_text = 'go_back';
                }
                else
                {
                    $ret_url = SITE_URL . '/weixin/index.php';
                    $ret_text = 'back_index';
                }

                /* 检查是否是店铺管理员 */
                if (!$this->visitor->get('manage_store'))
                {
                    /* 您不是店铺管理员 */
                    $this->show_warning(
                        'not_storeadmin',
                        'apply_now', 'index.php?app=apply',
                        $ret_text, $ret_url
                    );

                    return;
                }

                /* 检查是否被授权 */
                $privileges = $this->_get_privileges();
                if (!$this->visitor->i_can('do_action', $privileges))
                {
                    $this->show_warning('no_permission', $ret_text, $ret_url);

                    return;
                }

                /* 检查店铺开启状态 */
                $state = $this->visitor->get('state');
                if ($state == 0)
                {
                    $this->show_warning('apply_not_agree', $ret_text, $ret_url);

                    return;
                }
                elseif ($state == 2)
                {
                    $this->show_warning('store_is_closed', $ret_text, $ret_url);

                    return;
                }

                /* 检查附加功能 */
                if (!$this->_check_add_functions())
                {
                    $this->show_warning('not_support_function', $ret_text, $ret_url);
                    return;
                }
        }


        parent::_run_action();
    }
    function _get_privileges()
    {
        $store_id = $this->visitor->get('manage_store');
        $privs = $this->visitor->get('s');

        if (empty($privs))
        {
            return '';
        }

        foreach ($privs as $key => $admin_store)
        {
            if ($admin_store['store_id'] == $store_id)
            {
                return $admin_store['privs'];
            }
        }
    }

    /* 获取当前店铺所使用的主题 */
    function _get_theme()
    {
        $model_store =& m('store');
        $store_info  = $model_store->get($this->visitor->get('manage_store'));
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($curr_template_name, $curr_style_name) = explode('|', $theme);
        return array(
            'template_name' => $curr_template_name,
            'style_name'    => $curr_style_name,
        );
    }

    function _check_add_functions()
    {
        $apps_functions = array( // app与function对应关系
            'seller_groupbuy' => 'groupbuy',
            'coupon' => 'coupon',
        );
        if (isset($apps_functions[APP]))
        {
            $store_mod =& m('store');
            $settings = $store_mod->get_settings($this->_store_id);
            $add_functions = isset($settings['functions']) ? $settings['functions'] : ''; // 附加功能
            if (!in_array($apps_functions[APP], explode(',', $add_functions)))
            {
                return false;
            }
        }
        return true;
    }
}

/**
 *    店铺控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class StorebaseApp extends FrontendApp
{
    var $_store_id;

    /**
     * 设置店铺id
     *
     * @param int $store_id
     */
    function set_store($store_id)
    {
        $this->_store_id = intval($store_id);

        /* 有了store id后对视图进行二次配置 */
        $this->_init_view();
        $this->_config_view();
    }

    function _run_action()
    {
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        parent::_run_action();
    }

    //热门搜索
    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    function _config_view()
    {
        parent::_config_view();

        $this->_view->template_dir = ROOT_PATH . "/weixin/templates/";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mall/";
        $this->_view->res_base     = STATIC_URL . "/weixin/templates/style/";
    }

    /**
     * 取得店铺信息
     */
    function get_store_data()
    {
        $cache_server =& cache_server();
        $key = 'function_get_store_data_' . $this->_store_id;
        $store = $cache_server->get($key);
        if ($store === false)
        {
            $store = $this->_get_store_info();
            if (empty($store))
            {
                $this->show_warning('the_store_not_exist');
                exit;
            }
            if ($store['state'] == 2)
            {
                $this->show_warning('the_store_is_closed');
                exit;
            }
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store_mod =& m('store');
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);
            if($store['region_name']!=null) {
                $store['region_name'] = str_replace('中国','',$store['region_name']);
            }
            empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
            $store['store_owner'] = $this->_get_store_owner();
            $store['store_navs']  = $this->_get_store_nav();
            $goods_mod =& m('goods');
            $store['goods_count'] = $goods_mod->get_count_of_store($this->_store_id);
            $store['store_gcates']= $this->_get_store_gcategory();
            $store['sgrade'] = $this->_get_store_grade('grade_name');
            $functions = $this->_get_store_grade('functions');
            $store['functions'] = array();
            if ($functions)
            {
                $functions = explode(',', $functions);
                foreach ($functions as $k => $v)
                {
                    $store['functions'][$v] = $v;
                }
            }
            $cache_server->set($key, $store, 1800);
        }

        return $store;
    }

    /* 取得店铺信息 */
    function _get_store_info()
    {
        if (!$this->_store_id)
        {
            /* 未设置前返回空 */
            return array();
        }
        static $store_info = null;
        if ($store_info === null)
        {
            $store_mod  =& m('store');
            //$store_info = $store_mod->get_info($this->_store_id);
            $store_info = $store_mod->get(array(
                'conditions' => 'dropState=1 and store_id ='.$this->_store_id,
            ));
        }

        return $store_info;
    }

    /* 取得店主信息 */
    function _get_store_owner()
    {
        $user_mod =& m('member');
        $user = $user_mod->get($this->_store_id);

        return $user;
    }

    /* 取得店铺导航 */
    function _get_store_nav()
    {
        $article_mod =& m('article');
        return $article_mod->find(array(
            'conditions' => "store_id = '{$this->_store_id}' AND cate_id = '" . STORE_NAV . "' AND if_show = 1",
            'order' => 'sort_order',
            'fields' => 'title',
        ));
    }
    /*  取的店铺等级   */

    function _get_store_grade($field)
    {
        $store_info = $store_info = $this->_get_store_info();
        $sgrade_mod =& m('sgrade');
        $result = $sgrade_mod->get_info($store_info['sgrade']);
        return $result[$field];
    }
    /* 取得店铺分类 */
    function _get_store_gcategory()
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $gcategory_mod->get_list(-1, true);
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

    /**
     *    获取当前店铺所设定的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);

        return $template_name;
    }

    /**
     *    获取当前店铺所设定的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);

        return $style_name;
    }
}


/* 实现消息基础类接口 */
class MessageBase extends MallbaseApp {};

/* 实现模块基础类接口 */
class BaseModule  extends FrontendApp {};

/* 消息处理器 */
require(ROOT_PATH . '/eccore/controller/message.base.php');

?>
