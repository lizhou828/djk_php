<?php


class KxjApp extends MallbaseApp
{


    function index() {
        $num = '1000';
        $this->_config_seo(array(
            'title' => '欢乐开学季',
        ));
        $recom_mod =& m('recommend');

        $list = $recom_mod->get_recommended_goods(Conf::get("kxj-tuijian"), $num, true, 0);
        $this->assign('goods_list',$list);
        $this->display('kxj.html');
    }
}

?>
