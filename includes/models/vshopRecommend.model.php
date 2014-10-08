<?php

/* 余额 log */
class vshopRecommendModel extends BaseModel
{
    var $table  = 'vshop_recom_goods';
    var $prikey = 'id';
    var $_name  = 'vshop_recomGoods';
    var $_relation = array(
        'has_goods' => array(
            'model'         => 'goods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent' => true
        )
    );

    /**
     * 根据sql，获取推荐的商品
     * @param $sql
     * @param $current  当前页
     * @param $perPage  每页取多少条记录
     * @author liz
     */
    function goodsList($sql,$current,$perPage){
        $default_image = true;
        $goods_list=array();
        if($sql == null || $sql == ""){
            echo "illegal sql sentences !";
            return;
        }
        $startResult = ($current-1)*$perPage;
        $sql .=" limit ".$startResult.", ".$perPage;
        $res = $this->db->query($sql);
        while ($row = $this->db->fetchRow($res)){
            $default_image && empty($row['default_image']) && $row['default_image'] = Conf::get('default_goods_image');
            $goods_list[] = $row;
        }
        return $goods_list;
    }
}

?>