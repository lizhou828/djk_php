<?php


class JifenApp extends MallbaseApp
{
    function __construct()
    {

        $this->JifenApp();
    }

    function JifenApp()
    {

        parent::__construct();
        include_once(ROOT_PATH . '/app/member.app.php');
    }
    function index()
    {
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo('title', Lang::get('jifen'));
        if ($this->visitor->has_login)
        {
           $this->assign("jifen",$this->_jifen_info());
        }
        $data = $this->get_goods_list(40);
        $this->assign("page_info",$data['page']);
        $this->assign('goods_list',$data['goods_list']);
        $this->display('jifen.index.html');
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }

    function _jifen_info() {
       $member= new memberApp();
       $use =json_decode($member->user_api('','',''));
        $data['jifen'] = $use->jifen;
        $user = $this->visitor->get();
        $data['user_name'] = $user['nick_name'];
        return $data;
    }

    /*热销品*/
    function _get_hot_goods($cate_ids)
    {
        $goods_mod = &m('goods');

        $goods_list = $goods_mod->get_list(array(
            'conditions' => "if_show = 1 AND closed = 0  AND s.state =" . STORE_OPEN,
            'scate_ids' => $cate_ids,
            'order' => 'sales DESC',
            'join' => 'has_goodsstatistics, belongs_to_store',
            'limit' => 4,  false, true
        ));
        foreach ($goods_list as $key=>$goods) {
            if (strpos($goods['default_image'],'/')===false) {
                $goods_list[$key]['default_image']=  Conf::get('default_goods_image');
            }
        }
      return $goods_list;
    }


    /*商品模块展示*/
    function get_goods_list($page_size = 16)
    {
        $goods_mod = & m('goods');
        $userpriv_mod = & m('userpriv');
        $goods_list = null;

        $data = null;
//        $sql = "select g.* from {$goods_mod->table} g ,{$userpriv_mod->table} up where g.store_id = up.user_id and g.if_show=1 and g.closed=0 and g.dropState=1 ";
//        $goods_list=  $goods_mod->db->getAll($sql);

        $page   =   $this->_get_page($page_size);
        $goods_list =  $goods_mod->find(array(
                   'conditions' => " if_show=1 and closed=0 and dropState=1 and if_jifen>0",
                   'order' => ' add_time desc',
                   'count' => true,
                   'limit' => $page['limit']
                   ));
        $data['goods_list'] = $goods_list;
        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $data['page'] = $page;
        return $data;
    }
}

?>
