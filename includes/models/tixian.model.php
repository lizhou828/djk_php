<?php

/* 提现 log */
class tixianModel extends BaseModel
{
    var $table  = 'tixian';
    var $prikey = 'id';
    var $_name  = 'tixian';

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