<?php

/* 收货地址 address */
class CategorybrandModel extends BaseModel
{
    var $table  = 'category_brand';
    var $prikey = 'id';
    var $_name  = 'category_brand';

    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {
        return parent::add($data, $compatible);
    }

}

?>