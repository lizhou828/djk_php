<?php

/* 我的银行卡 */
class MemberbankModel extends BaseModel
{
    var $table  = 'member_bank';
    var $prikey = 'id';
    var $_name  = 'memberbank';

    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {
        return parent::add($data, $compatible);
    }

    function edit($conditions, $edit_data)
    {
        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        return parent::drop($conditions, $fields);
    }

}

?>