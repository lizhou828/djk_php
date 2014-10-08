<?php

class ReadMemcacheApp extends StorebaseApp
{

    function __construct()
    {
        $this->ReadMemcacheApp();
    }

    function ReadMemcacheApp()
    {
        parent::__construct();
    }

    function get_comment($id)
    {

        include_once(ROOT_PATH . '/includes/memcached-client.php');
        // 选项设置
        $options = array(
            'servers' => array(SERVERS),
            'debug' => false, //是否打开 debug
            'compress_threshold' => 10240, //超过多少字节的数据时进行压缩
            'persistant' => false //是否使用持久连接
        );
        // 创建 memcached 对象实例
        $mc = new memcached($options);
        // 设置此脚本使用的唯一标识符
        $str = $mc->get("comments");
        //取商品分类
        $goods_mod = &m("goods");
        $gcategory_mod = &m("gcategory");
        $goods = $goods_mod->get('goods_id ='.$id);
        $pid =  $goods['cate_id_1'];
        $cateConf = Conf::get("coments_category_id");
        $cate_id ;
        foreach ($cateConf as $key => $cc) {
            if ($pid == $key) {
                $cate_id = $cc;
            }
        }
        $comments = array();
        if ($str) {
            $cate_comments = explode("--------", $str);
            foreach ($cate_comments as $k => $cc) {
                $strToArray = explode("\n", $cc);
                if (count($strToArray) > 0) {
                    foreach ($strToArray as $key => $g) {
                        $gc = explode("#", $g);
                        $kc = $k."-" . $key;
                        $a = mt_rand(1,1000);
                        if ($gc[0] && $k == $cate_id && $a < $key) {
                            $comments[$kc] ['buyer_name'] = $gc[0];
                            $comments[$kc] ['comment'] = $gc[1];
                            $comments[$kc] ['quntity'] = $gc[2];
                            $comments[$kc] ['evaluation_time'] = $this->randomDate("2013-10-08 00:00:00","2014-03-28 00:00:00");
                            $comments[$kc] ['evaluation'] = 3;
                        }

                    }
                }
            }
        }
//

        return $this->array_sort($comments,'evaluation_time');;
    }

    function randomDate($begintime, $endtime=""){
        $begin = strtotime($begintime);
        $end = $endtime == "" ? mktime() : strtotime($endtime);
        $timestamp = rand($begin, $end);
        return  $timestamp;
    }

    function array_sort($arr,$keys,$type='dsc'){
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'dsc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
}

?>
