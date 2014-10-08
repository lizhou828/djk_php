<?php

class DatacacheApp extends MallbaseApp
{
    var $_pd_category_mod;
    var $_gcategory_mod;
    var $_attribute_mod;
    var $_category_brand_mod;
    var $_brand_mod;
    /**
     * 构造函数
     */
    function __construct()
    {
        $this->DatacacheApp();

    }
    function DatacacheApp()
    {
        parent::__construct();

        $this->_attribute_mod =& m('attribute');
        $this->_gcategory_mod =& bm('gcategory', array('_store_id' => 0));
        $this->_category_brand_mod = & m('categorybrand');
        $this->_pd_category_mod = & m('pdcategory');
        $this->_brand_mod = & m('brand',array('dropState'=>1));

    }

    function index() {
        $this->assign('pd_category_json',$this->cache_json_category());
        $this->assign('category_brand_json',$this->cache_json_brand());
        $this->assign('category_attrs_json',$this->cache_json_attrs());
        $store_mode  = & m('store');
        $store = $store_mode->get_info($this->visitor->get('user_id'));
        $this->assign('store',$store);
        if (substr(strtoupper($this->visitor->get("user_name")),0,9) == "DJKHANGUO") {
            $this->assign('hanguo','hanguo');
        }
        $this->display('datacache.form.html');
    }

    /**
     * 分类,属性Json
     */
    function cache_json_attrs()
    {
       header('Content-type: text/json');
       $sql_attrs = "SELECT GROUP_CONCAT(g.cate_id SEPARATOR '-') AS cat_ids,g.parent_id ,ab.attr_id, ab.attr_name,ab.def_value FROM {$this->_attribute_mod->table} ab
	                 LEFT JOIN {$this->_gcategory_mod->table} g ON g.cate_id = ab.category_id
	                 WHERE g.dropState=1 AND g.if_show=1 AND g.store_id=0 AND ab.is_search=1 AND ab.dropState=1
	                 GROUP BY ab.attr_id ";
       $cate_ids = $this->_category_brand_mod->getAll($sql_attrs);
       $data = json_encode($cate_ids);
       return $data;
    }

    /**
     * 频道，分类
     */
    function cache_json_category()
    {
        header('Content-type: text/json');
        $sql_pd = "SELECT GROUP_CONCAT(p.pd_id SEPARATOR '-' ) AS pds, c.cate_id, c.cate_name, c.parent_id,c.changshang_ticheng,c.dailishang_ticheng,c.lingshoushang_ticheng,c.shitidian_ticheng FROM {$this->_gcategory_mod->table} c
                   LEFT JOIN {$this->_pd_category_mod->table} p ON p.category_id = c.cate_id
                   where c.dropState =1 and c.store_id=0 and c.if_show =1 GROUP BY c.cate_id";
        $pd_ids = $this->_pd_category_mod->getAll($sql_pd);
        $data = json_encode($pd_ids);
        return $data;
    }
    //取分类品牌Json
    function cache_json_brand() {
        header('Content-type: text/json');
        $sql_brand = "SELECT GROUP_CONCAT(cb.category_id SEPARATOR '-') AS cat_ids, b.brand_id, b.brand_name FROM {$this->_brand_mod->table} b
	                  LEFT JOIN {$this->_category_brand_mod->table} cb ON b.brand_id = cb.brand_id  WHERE b.dropState=1 AND b.if_show=1  GROUP BY b.brand_id ";
        $brands = $this->_category_brand_mod->getAll($sql_brand);
        $data = json_encode($brands);
        return $data;
    }
}

?>
