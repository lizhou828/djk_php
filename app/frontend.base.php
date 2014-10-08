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
        if( $info && $info['user_id'] > 0 && $info['spreader_type'] ==1 && $info['shop_name'] !=null  && $info['shop_name'] != "" ){
            $vshop_mod =& m('vshop');
            $vshop = $vshop_mod->get("user_id=".$info['user_id']." and status=1");
            $this->assign('vshop',$vshop);
        }

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

        $this->assign('center',$article['center']);
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
                $ret_url ='/index.php';
            }
        }
        if(!$_POST){
            $this->assign('ret_url', rawurlencode($ret_url));
        }



        if(!ecm_getcookie("sendKey") || ecm_getcookie("sendKey") ==""){
            ecm_setcookie("sendKey",rand(10000000,99999999),time() + 864000);
        }

        $this->assign('sendKey', $this->visitor->get('user_id')?$this->visitor->get('user_id'):-ecm_getcookie("sendKey")); //商城公告

        $this->assign('IF_DUANXING', IF_DUANXING);
        $this->assign('TO_PLCHOUJIANG_URL', TO_PLCHOUJIANG_URL);
        parent::display($tpl);
    }

    /*根据类型获取文章*/
    function _get_news($acc){
        $acategory_mod =& m('acategory');
        $article_mod =& m('article');
        $data = $article_mod->find(array(
            'conditions' => 'cate_id=' . $acategory_mod->get_ACC($acc) . ' AND if_show = 1 and dropState=1',
            'order' => 'sort_order ASC, add_time DESC',
            'fields' => 'article_id, title, add_time',
            'limit' => 7,
        ));
        return $data;
    }

    function login()
    {


        if ($this->visitor->has_login)
        {
            //$this->show_warning('has_login');
            header("Location: ".SITE_URL . '/index.php');
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
                if (isset($_SERVER['HTTP_REFERER']))
                {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                }
                else
                {
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            /* 防止登陆成功后跳转到登陆、退出的页面 */
            $ret_url = strtolower($ret_url);
            if (str_replace(array('act=login', 'act=logout','act=register_success'), '', $ret_url) != $ret_url)
            {
                $ret_url = SITE_URL . '/index.php';
            }

            if (Conf::get('captcha_status.login'))
            {
                $this->assign('captcha', 1);
            }
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_login'));
            $this->_config_seo('title', Lang::get('user_login') . ' - ' . Conf::get('site_title'));

            $login_name = base64_decode(ecm_getcookie("login_name"));

            if($login_name!=""){
                $this->assign('login_name', $login_name);
            }

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
                ecm_setcookie("login_name",base64_encode($_POST["user_name"]),time() + 9999999);
            }

            if (!$user_id)
            {
                /* 未通过验证，提示错误信息 */
                //$this->show_warning($ms->user->get_error());
                //print_r($ms->user->get_error());
                //header("Location: index.php?app=member&act=login&synlogout=1&msg=auth_failed_byStatus");
                $_POST["errorMsg"]="auth_failed_byStatus";
                $this->assign('postData', $_POST);
                $this->display('login.html');
                return;
            }
            else
            {
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
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            /* 防止登陆成功后跳转到登陆、退出的页面 */
            $ret_url = strtolower($ret_url);
            if (str_replace(array('act=login', 'act=logout',), '', $ret_url) != $ret_url){
                $ret_url = SITE_URL . '/index.php';
            }

            header("Location: ".$ret_url);

            /*exit;
            $this->show_message(
                Lang::get('login_successed') . $synlogin,
                'back_before_login', rawurldecode($_POST['ret_url']),
                'enter_member_center', 'index.php?app=member'
            );*/

        }
    }

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
                echo "<script type=\"text/javascript\">window.parent.location.href='".$url."';</script>";
            }
            echo "<script type=\"text/javascript\">window.parent.js_success('" . $dialog_id ."');</script>";
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
            echo "<script type=\"text/javascript\">window.parent.js_fail('" . $errors . "');</script>";
        }
    }

    function logout()
    {
        $this->visitor->logout();

        /* 跳转到登录页，执行同步退出操作 */
        header("Location: index.php?app=member&act=login&synlogout=1");
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
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id,member_type,member.region_id,member.region_name,member.nick_name,spreader_type,nick_name,shop_name',
        ));


        /* 店铺ID */
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];

        /* 保证基础数据整洁 */
        //unset($user_info['store_id']);
        //$mod_store =& m('store');

        $user_info["nice_name"] = $user_info["nick_name"]?$user_info["nick_name"]:$user_info["user_name"];
        if (substr(strtoupper($user_info["user_name"]),0,5) == "DJKKF") {
            $kf_data["djkkf001"] = "丹顶鹤";
            $kf_data["djkkf002"] = "鹦鹉";
            $kf_data["djkkf003"] = "信鸽";
            $kf_data["djkkf004"] = "信天翁";
            $kf_data["djkkf005"] = "白鹭";
            $kf_data["djkkf006"] = "杜鹃";
            $kf_data["djkkf007"] = "猫头鹰";
            $kf_data["djkkf008"] = "喜鹊";
            $kf_data["djkkf009"] = "孔雀";
            $kf_data["djkkf010"] = "鸳鸯";
            $user_info["nick_name"]=$kf_data[$user_info["user_name"]];
        }

        /* 分派身份 */
        $this->visitor->assign($user_info);



        /* 更新用户登录信息 */
        $mod_user->edit("user_id = '{$user_id}'", "last_login = '" . gmtime()  . "', last_ip = '" . real_ip() . "', logins = logins + 1");

        /* 更新购物车中的数据 */
        $mod_cart =& m('cart');
        $mod_cart->edit("(user_id = '{$user_id}' OR session_id = '" . SESS_ID . "') AND store_id <> '{$my_store}'", array(
            'user_id'    => $user_id,
            'session_id' => SESS_ID,
        ))  ;

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
            'url'   => SITE_URL . '/index.php',
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

        ecm_setcookie("login_name", '', time());
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

    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && in_array(APP, array('apply')) && !in_array(ACT, array('check_name','drop_image2')))
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
        $this->assign('shouye_footer',$this->get_guanggao('shouye_footer'));
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        include_once(ROOT_PATH . '/includes/HttpClient.class.php');

        parent::_run_action();
    }
//热门搜索
    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
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
     * 根据区域、详细地址，生成经纬度，以数组的形式返回
     * @param $regionName
     * @param $address
     * @return array(latitude,longitude);
     * @author liz
     */
    function get_latitude_longitude_by_address($address,$regionName){
        if($address == "" || $regionName == ""){
            return array();
        }
        $url = "http://api.map.baidu.com/geocoder?address=%s&output=xml&key=lcCMY1xxIFG2mUh5TOG8iwTa&city=%s";
        $fullUrl = sprintf($url,$address,$regionName);
        $xml= $this->post($fullUrl);
        $resultXML = simplexml_load_string($xml);
        $status = $resultXML->status;
        $result = $resultXML->result;
        $lat = $result->location->lat;
        $lng = $result->location->lng;
        $array = array("status"=>strval($status),"latitude"=>strval($lat),"longitude"=>strval($lng));
        return $array;
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
        $gcategories = array();
        if($pid == -1) {
            $arr = $gcategory_mod->get_list(0);
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
        }else {
        $cate_ids= $_pdgcategory_mod->_getCategoryByPdId($pid);
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
        }
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        $data = $tree->getArrayList(0);
        $cache_server->set($key, $data, 3600);
        return $data;
    }



    function _config_view()
    {
        parent::_config_view();

        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();
        $this->_view->template_dir = ROOT_PATH . "/themes/mall/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mall/{$template_name}";
        $this->_view->res_base     = STATIC_URL . "/themes/mall/{$template_name}/styles/{$style_name}";
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
    function _run_action(){
        if(
        ($_GET["app"]=="service" && ($_GET["act"]=="register_service" ||  $_GET["act"]=="serachService" ||  $_GET["act"]=="queryByqiangzhu")
            ||($_GET["app"] == "pos" && (!$_GET["act"] || $_GET["act"] =="index" || $_GET["act"] =="bind"))
            || ($_GET["app"]=="jkxd_portal")
        )
        ){
            //跳过登入验证
        }else
        {
            /* 只有登录的用户才可访问 */
            if (!$this->visitor->has_login && $_GET["app"] !="swfupload"
                && !in_array(ACT, array('login', 'register', 'check_user','check_invi_code','check_user2','cj','qq_login','check_user_or_phone_mob','check_pwd','view_remote','view_iframe','uploadedfile','drop_image2'))
                && !( $_GET["app"] == "tuiguang" && ( $_GET["act"] == "view" || $_GET["act"] == "product"  || $_GET["act"] == "help" || $_GET["act"] == "reg_store"))){
                if (!IS_AJAX){
                    header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
                    return;
                }
                else{
                    $this->json_error('login_please');
                    return;
                }
            }
            @session_start();
            if($_GET["p"]){
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

    //公用的获取用户信息api
    function user_api($user_id,$region_id,$type){
        $params["type"] = ($type == "") ? "query" : "query_server";;
        $params["userId"] =  ($user_id == "") ? $this->visitor->get('user_id') : $user_id;

        $user = $this->visitor->get();
        $queryType = 1;
        if($user["member_type"] == 2 || $user["member_type"] ==3 ){
            $queryType = 3;
        }else if($user["member_type"] == 4 ){
            $queryType = 4;
        }

        $params["tId"] ="0";
        $params["orderId"] ="";
        $params["userName"] ="";
        $params["channelCode"] ="";
        $params["channelName"] ="";
        $params["channelCard"] ="";
        $params["jine"] ="0";
        $params["regionId"] =( $region_id == "") ? 0 : $region_id;
        $Client = new HttpClient(JAVA_LOCATION);
        $url = "http://".JAVA_LOCATION.TO_PHP_JAVA_URL;
        $pageContents = HttpClient::quickPost($url, $params);

//        $this->pr($pageContents);exit;
        return $pageContents;
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
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id ,member_type,member.region_name,member.region_id,member.nick_name',
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
                    'url'   => TO_PLCHOUJIANG_URL,
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
                'pos_bank' => array(
                    'text'  => "pos机刷卡查询",
                    'url'   => 'index.php?app=member&act=pos_view&p=userInfo',
                    'name'  => 'pos_bank',
                    'icon'  => 'pos_bank',
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
                /*'my_question' =>array(
                    'text'  => Lang::get('my_question'),
                    'url'   => 'index.php?app=my_question&p=buyer',
                    'name'  => 'my_question',
                    'icon'  => 'ico17',

                ),*/
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
            /*$menu['im_seller']['submenu']['groupbuy_manage'] = array(
                'text'  => Lang::get('groupbuy_manage'),
                'url'   => 'index.php?app=seller_groupbuy&p=seller',
                'name'  => 'groupbuy_manage',
                'icon'  => 'ico22',
            );*/
            /*$menu['im_seller']['submenu']['my_qa'] = array(
                'text'  => Lang::get('my_qa'),
                'url'   => 'index.php?app=my_qa&p=seller',
                'name'  => 'my_qa',
                'icon'  => 'ico18',
            );*/
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
            $menu['im_seller']['submenu']['daifu_order'] = array(
                'text'  => "代付订单",
                'url'   => 'index.php?app=seller_order&p=daifu',
                'name'  => 'daifu_order',
                'icon'  => 'ico12',
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
//            $menu['im_seller']['submenu']['yushou_manage'] = array(
//                'text'  => Lang::get('yushou_manage'),
//                'url'   => 'index.php?app=seller_yushou&p=seller&p=seller',
//                'name'  => 'yushou_manage',
//                'icon'  => 'ico22',
//            );
//            $menu['im_seller']['submenu']['temai_manage'] = array(
//                'text'  => Lang::get('temai_manage'),
//                'url'   => 'index.php?app=seller_temai&p=seller&p=seller',
//                'name'  => 'temai_manage',
//                'icon'  => 'ico22',
//            );
        }

        $menu['im_tuiguang'] = array(
            'name'  => 'im_tuiguang',
            'text'  => Lang::get('im_tuiguang'),
            'id'    =>'tuiguang',
            'submenu'   => array(
                'tuiguang'  => array(
                    'text'  => Lang::get('tuiguang'),
                    'url'   => 'index.php?app=tuiguang&act=daima&p=tuiguang',
                    'name'  => 'daima',
                    'icon'  => 'tuiguang',
                ),
                'tuiguang_member'  => array(
                    'text'  => Lang::get('tuiguang_member'),
                    'url'   => 'index.php?app=tuiguang&act=tuiguang_member&p=tuiguang',
                    'name'  => 'tuiguang_member',
                    'icon'  => 'tuiguang_member',
                ),
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
                    'url'   => 'index.php?app=member&type=6&act=showYueLog',
                    'name'  => 'shouyi',
                    'icon'  => 'shouyi',
                ),

            ));
         if( $validata["spreader_type"]==1 && $validata['shop_name'] != null && $validata['shop_name'] != ""){
             //集客小店
             $menu['jkxd'] = array(
                 'name'  => 'my_jkxd',
                 'text'  => Lang::get('my_jkxd'),
                 'id'    =>'my_jkxd',
                 'submenu'   => array(
                     'jkxd_index'  => array(
                         'text'  => "集客小店信息",
                         'url'   => 'index.php?app=jkxd',
                         'name'  => 'jkxd_index',
                         'icon'  => 'jkxd_index',
                     ),
                     'jkxd_store'  => array(
                         'text'  => Lang::get('jkxd_store'),
                         'url'   => 'index.php?app=jkxd&act=jkxd_store',
                         'name'  => 'jkxd_store',
                         'icon'  => 'jkxd_store',
                     ),
                     'jkxd_order'  => array(
                         'text'  => Lang::get('jkxd_order'),
                         'url'   => 'index.php?app=jkxd&act=jkxd_order',
                         'name'  => 'jkxd_order',
                         'icon'  => 'jkxd_order',
                     ),
                     'my_yongjin'  => array(
                         'text'  => Lang::get('my_yongjin'),
                         'url'   => 'index.php?app=jkxd&act=my_yongjing',
                         'name'  => 'my_yongjing',
                         'icon'  => 'my_yongjing',
                     ),
                     'yjtg'  => array(
                         'text'  => '一键分享',
                         'url'   => 'index.php?app=jkxd&act=yjtg',
                         'name'  => 'yjtg',
                         'icon'  => 'yjtg',
                     ),

                 ));
         }

     }

        if ($validata["member_type"]=="02" || $validata["member_type"]=="03" || $validata["member_type"]=="04"){

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
            if($this->visitor->get('user_name') == "djk11001"){                 //公共帐号，可以修改所有的店铺资料
                $menu['im_service']['submenu']['im_service']=array(
                    'text'  => Lang::get('im_service'),
                    'url'   => 'index.php?app=service&act=service&p=service',
                    'name'  => 'service',
                    'icon'  => 'service',
                );
                $menu['im_service']['submenu']['order_manage']=array(
                    'text'  => Lang::get('order_manage'),
                    'url'   => 'index.php?app=service&act=orders&p=service',
                    'name'  => 'order_manage',
                    'icon'  => 'service',
                );
            }elseif($this->visitor->get('user_name') == "djk11002"){           //公共帐号，可以帮所有的店铺上传资料
                $menu['im_service']['submenu']['im_service']=array(
                    'text'  => Lang::get('my_goods'),
                    'url'   => 'index.php?app=service&act=my_goods&p=service',
                    'name'  => 'service',
                    'icon'  => 'service',
                );

            }elseif($this->visitor->get('user_name') == "djk11003"){           //公共帐号，可以帮所有的店铺上传资料
                $menu['im_service']['submenu']['im_service']=array(
                    'text'  => Lang::get('my_goods'),
                    'url'   => 'index.php?app=service&act=my_goods&li=shenhe&p=service&type=tuoguan',
                    'name'  => 'service',
                    'icon'  => 'service',
                );

            }
            else{
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
                        'url'   => 'index.php?app=member&p=userinfo&act=showYueLog&type=6&o=service',
                        'name'  => 'shouyi',
                        'icon'  => 'shouyi',
                    );
                }
                $menu['im_service']['submenu']['im_service']=array(
                    'text'  => Lang::get('im_service'),
                    'url'   => 'index.php?app=service&act=service&p=service',
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

                /*$menu['im_service']['submenu']['tuangou']=array(
                    'text'  => Lang::get('tuangou'),
                    'url'   => 'index.php?app=service&act=tuangou&p=service',
                    'name'  => 'tuangou',
                    'icon'  => 'ico22',
                );*/

                $menu['im_service']['submenu']['orders']=array(
                    'text'  => Lang::get('orders'),
                    'url'   => 'index.php?app=service&act=orders&p=service',
                    'name'  => 'orders',
                    'icon'  => 'ico10',
                );
                if($validata["member_type"]=="04"){
                    $menu['im_service']['submenu']['vshop_goods_manage']=array(
                        'text'  => "精品店商品管理",
                        'url'   => 'index.php?app=service&act=vshop_goods_manage',
                        'name'  => 'vshop_goods_manage',
                        'icon'  => 'ico10',
                    );
                }

                if($validata["member_type"]=="02"){
                    $menu['im_service']['submenu']['orders_pos']=array(
                        'text'  => "pos机消费查询",
                        'url'   => 'index.php?app=service&act=orders_pos&p=service',
                        'name'  => 'orders_pos',
                        'icon'  => 'ico10',
                    );
                }
                //非韩国馆的服务中心才显示本辖区订单
                /*$count = count(explode("djkhanguo",$validata["user_name"]));
                if($count == 1 ){
                    $menu['im_service']['submenu']['benqu_orders']=array(
                        'text'  =>'本辖区订单',
                        'url'   => 'index.php?app=service&act=benqu_orders&p=service',
                        'name'  => 'benqu_orders',
                        'icon'  => 'benqu_orders',
                    );
                }*/

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
//                $menu['im_service']['submenu']['zxkf']  = array(
//                    'text'  => Lang::get('zxkf'),
//                    'url'   => 'index.php?app=service&act=zxkf&p=service',
//                    'name'  => 'zxkf',
//                    'icon'  => 'zxkf',
//                );
                /*$menu['im_service']['submenu']['spzx']  = array(
                    'text'  => Lang::get('spzx'),
                    'url'   => 'index.php?app=service&act=spzx&p=service',
                    'name'  => 'spzx',
                    'icon'  => 'ico18',
                );*/
                if($validata["member_type"]=="02"){
                    $menu['im_service']['submenu']['aqzx']  = array(
                        'text'  => Lang::get('aqzx'),
                        'url'   => 'index.php?app=service&act=aqzx&p=service',
                        'name'  => 'aqzx',
                        'icon'  => 'ico18',
                    );
                }
                if($validata["member_type"] != "04"){
                    $menu['im_service']['submenu']['bank']  = array(
                        'text'  => Lang::get('bank'),
                        'url'   => 'index.php?app=member&act=bank&p=service',
                        'name'  => 'bank',
                        'icon'  => 'bank',
                    );
                }
                if($validata["member_type"] == "02" || $validata["member_type"] == "03" ){
                    $menu['vshop'] = array(
                        'name'  => 'vshop',
                        'text'  => "精品集客小店",
                        'id'    => 'vshop',
                        'submenu'=>array(
                            'vshop'  => array(
                                'text'  => "精品店管理",
                                'url'   => 'index.php?app=service&act=vshop_all',
                                'name'  => 'vshop_all',
                                'icon'  => 'vshop_all',
                            ),
                            'vshop_order'  => array(
                                'text'  => "精品店订单管理",
                                'url'   => 'index.php?app=service&act=vshop_order',
                                'name'  => 'vshop_order',
                                'icon'  => 'vshop_order',
                            ),
                            'vshop_yongjin'  => array(
                                'text'  => "精品店佣金管理",
                                'url'   => 'index.php?app=service&act=vshop_yongjin',
                                'name'  => 'vshop_yongjin',
                                'icon'  => 'vshop_yongjin',
                            ),
                            'vshop_goods'  => array(
                                'text'  => "商品推荐",
                                'url'   => 'index.php?app=service&act=vshop_goods',
                                'name'  => 'vshop_goods',
                                'icon'  => 'vshop_goods',
                            ),
                        )
                    );
                }

                if($validata["member_type"]=="02"){
//                    $menu['im_service']['submenu']['qianyue']  = array(
//                        'text'  => '签约业务员申请',
//                        'url'   => 'index.php?app=service&act=qianyue&p=service',
//                        'name'  => 'qianyue',
//                        'icon'  => 'ico18',
//                    );

                    $menu['im_service']['submenu']['bind_user']  = array(
                        'text'  => "绑定推荐人账号",
                        'url'   => 'index.php?app=service&act=bind_user&p=service',
                        'name'  => 'bind_user',
                        'icon'  => 'bind_user',
                    );
                }
            }
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
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user','check_user2','view_remote','view_iframe','uploadedfile','drop_image2')) && $_GET["app"] !="swfupload")
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
                    $ret_url = SITE_URL . '/index.php';
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
        $this->assign('shouye_footer',$this->get_guanggao('shouye_footer'));
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        parent::_run_action();
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
    //热门搜索
    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    function _config_view()
    {
        parent::_config_view();
        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

        $this->_view->template_dir = ROOT_PATH . "/themes/store/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/store/{$template_name}";
        $this->_view->res_base     = STATIC_URL . "/themes/store/{$template_name}/styles/{$style_name}";
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
