<?php

/* 团购活动 groupbuy */
class YushouModel extends BaseModel
{
    var $table  = 'yushou';
    var $alias  = 'ys';
    var $prikey = 'id';
    var $_name  = 'yushou';
    var $_relation  = array(
        // 一个团购活动属于一个商品
        'belong_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_yushou',
        ),
        // 一个团购活动属于一个店铺
        'belong_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_yushou',
        ),
    );



    //查询团购商品所属分类
    function get_cate_id($type) {
        $sql = "SELECT DISTINCT gd.cate_id_1 FROM ".DB_PREFIX."yushou as ys,".DB_PREFIX."goods as gd
        WHERE ys.goods_id = gd.goods_id and ys.state=1 and ys.type=".$type;
       return $this->db->getCol($sql);

    }
}
?>
