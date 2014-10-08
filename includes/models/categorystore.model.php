<?php

/* 收货地址 address */
class CategorystoreModel extends BaseModel
{
    var $table  = 'category_store';
    var $prikey = 'cate_id';
    var $_name  = 'category_store';

    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {
        return parent::add($data, $compatible);
    }

}

?>