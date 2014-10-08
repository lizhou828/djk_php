<?php

/**
 *    商品属性管理控制器
 *
 *    @author    Hyber
 *    @usage    none
 */
class AttributeApp extends BackendApp
{

    var $_gcategory_mod;
    var $_attribute_mod;

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->AttributeApp();

    }
    function AttributeApp()
    {
        parent::__construct();

        $this->_attribute_mod =& m('attribute');
        $this->_gcategory_mod = & m('gcategory');

    }

    /*
     * 默认列表页
     */
    function index() {
        $attributes = $this->_attribute_mod->find(array(
                   "conditions" => "1=1 and dropState=1 and category_id =".$_GET['cate_id'],
                ));
        $this->assign("gcategory",$this->_gcategory_mod->get($_GET['cate_id']));
        $this->assign("attributes",$attributes);
        $this->display("attribute.form.html");
    }

    /**
     * 新增
     */
    function add() {

        if(IS_POST)  {
            $attr_names= $_POST['attr_name'];
            $def =  $_POST['def_value'];
            $def_value_pl = preg_replace('/\s{2,}/', ',',$def);
            foreach ($attr_names as $ar=>$v) {
                $data = array(
                    'category_id'=>$_POST['cate_id'],
                    'attr_name'=>$v,
                    'input_mode'=>$_POST['input_mode'],
                    'def_value'=>$def_value_pl[$ar],
                    'is_search'=>$_POST["is_search_{$ar}"],
                );
                if($this->_attribute_mod->unique($v,$data['category_id'])==0) {
                    $this->_attribute_mod->add($data);
                } else {
                    $this->show_message("属性名称已存在");
                    return;
                }
            }

        $attributes = $this->_attribute_mod->find(array(
            "conditions" => "1=1 and dropState=1 and category_id =".$_POST['cate_id'],
        ));
        $this->assign("gcategory",$this->_gcategory_mod->get($_POST['cate_id']));
        $this->assign("attributes",$attributes);
        $this->display("attribute.form.html");
       }
    }

    //编辑
    function edit() {
        if (!IS_POST) {
            $id = $_GET['attr_id'];
            $cate_id = $_GET['cate_id'];
            $attr =  $this->_attribute_mod->get($id);

            $values = explode(',',$attr['def_value']);
            $str = null;
            foreach ($values as $val) {
                if ($str == null) {
                    $str = $val;
                } else {
                    $str.="\n".$val;
                }
            }
            $attr['def_value'] = $str;
            $attributes = $this->_attribute_mod->find(array(
                "conditions" => "1=1 and dropState=1 and category_id =".$cate_id,
            ));

            $this->assign("attribute",$attr);
            $this->assign("gcategory",$this->_gcategory_mod->get($cate_id));
            $this->assign("attributes",$attributes);
        } else {
            $data = array();
            $def_value_pl = preg_replace('/\s{2,}/', ',',$_POST['def_value']);
            $data["attr_name"] = $_POST['attr_name'][0];
            $data["def_value"] = trim($def_value_pl[0]);
            $data["is_search"] = $_POST['is_search_0'];
            $data["category_id"] = $_POST['cate_id'];

             $this->_attribute_mod->edit($_POST['attr_id'],$data);

            $attributes = $this->_attribute_mod->find(array(
                "conditions" => "1=1 and dropState=1 and category_id =".$_POST['cate_id'],
            ));
            $this->assign("gcategory",$this->_gcategory_mod->get($_POST['cate_id']));
            $this->assign("attributes",$attributes);
        }
        $this->display("attribute.form.html");
    }


    /**
     * 删除
     */
    function drop(){
        $id = $_GET['attr_id'];
        $cate_id = $_GET['cate_id'];
        $this->_attribute_mod->edit($id,array('dropState'=>0));
        $attributes = $this->_attribute_mod->find(array(
            "conditions" => "1=1 and dropState=1 and category_id =".$cate_id,
        ));
        $this->assign("gcategory",$this->_gcategory_mod->get($cate_id));
        $this->assign("attributes",$attributes);
        $this->display("attribute.form.html");
    }
}

?>