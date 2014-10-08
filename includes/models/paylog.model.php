<?php

/* 营业额 log */
class paylogModel extends BaseModel
{
    var $table  = 'pay_log';
    var $prikey = 'log_id';
    var $_name  = 'paylog';

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