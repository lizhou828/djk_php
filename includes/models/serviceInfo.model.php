<?php

/* 会员 member */
class ServiceInfoModel extends BaseModel
{
    var $table  = 'service_info';
    var $prikey = 'id';
    var $_name  = 'serviceInfo';

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