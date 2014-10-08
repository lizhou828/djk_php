<?php

/* 属性 attribute */
class AttributeModel extends BaseModel
{
    var $table  = 'attribute';
    var $prikey = 'attr_id';
    var $_name  = 'attribute';
    

    /*
     * 判断名称是否唯一
     */
    function unique($attr_name, $cate_id )
    {
        $conditions = "attr_name = '" . $attr_name . "' and dropState=1 AND category_id =".$cate_id;

        //dump($conditions);
        return count($this->find(array('conditions' => $conditions)));
    }

//    function

    function get_byids($conditions){
        $sql = "select category_id,attr_id,attr_name,def_value from {$this->table} where category_id in (".$conditions.") and is_search =1 and dropState=1";
        $res = $this->getAll($sql);
       return $res;
    }
}

?>