<?php


class ZqjApp extends MallbaseApp
{


    function index() {
        $this->_config_seo(array(
            'title' => '中秋大促',
        ));

        $this->display('zqj.html');
    }
}

?>
