<?php

/* 提成 */
class TichengDetailModel extends BaseModel
{
    var $table  = 'ticheng_detail';
    var $alias  = 't';
    var $prikey = 'order_id';
    var $_name  = 'tichengDetail';


    function getOne($sql)
    {
        return parent::getOne($sql);
    }
}
?>
