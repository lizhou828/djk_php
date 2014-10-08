<?php

/* 商品属性 goodsattr */
class GoodsattrModel extends BaseModel
{
    var $table  = 'goods_attr';
    var $prikey = 'gattr_id';
    var $_name  = 'goodsattr';

    var $_relation  = array(
        // 一个商品属性只能属于一个商品
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goodsattr',
        ),
    );
    /*
    * 判断商品属性是否唯一
    */
    function unique($goods_id, $attr_id)
    {
        //dump($conditions);
        $sql = "select * from {$this->table} where goods_id=".$goods_id." and attr_id =".$attr_id;
        $rows = $this->getRow($sql);
        if ($rows>0) {
            return true;
        } else{
            return  false;
        }

    }

    /**
     * 获取商品属性
     */
     function  get_bygoodsid($goods_id,$cateIds){
         $sql = "SELECT ga.attr_id,ga.attr_value,ga.attr_name FROM ".  DB_PREFIX ."goods_attr AS ga ".
                 "LEFT JOIN ". DB_PREFIX ."attribute AS ab ON ga.attr_id = ab.attr_id".
                 " WHERE ab.category_id IN (".$cateIds.") AND ga.goods_id =".$goods_id;
         $res = $this->getAll($sql);
         return $res;
     }

   // 获取一级分类的商品属性
    function get_goods_cateid($cateid) {

        $sql = "SELECT	ab.attr_name,ab.def_value,ab.attr_id FROM ".DB_PREFIX."attribute AS ab  WHERE   ab.category_id =".$cateid." and ab.dropState=1 and ab.is_search=1";

        $res = $this->getAll($sql);
        return $res;
    }

    /*
    function edit($conditons,$data) {

        return parent::edit($conditons, $data);
    }*/

    function edit($conditons,$data) {
        $sql = "update {$this->table} set attr_name = '".$data['attr_name']."',attr_value ='".$data['attr_value'].
            "',sort_order=".$data['sort_order']." where goods_id =".$data['goods_id']." and attr_id =".$data['attr_id'];
         $this->db->query($sql);
    }
}

?>