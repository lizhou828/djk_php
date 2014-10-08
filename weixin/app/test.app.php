<?php

class TestApp extends MallbaseApp {
    function index(){
        echo $_SESSION["yanzhengma"];echo "<br/>";
        setcookie("t_id", '', time(), "/weixin");
        setcookie("t_id", $_GET['u_id'], time() + 60*60*24, "/");
    }


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
//        $ip = $this -> getip();
        $ip="220.168.55.6";
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
        echo $city;
    }

    function test(){
        $this -> display("test1.html");
    }

    function test1(){
         $this -> display("test.html");
    }


}