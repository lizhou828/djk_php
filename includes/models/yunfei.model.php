<?php

/* 运费 member */
class YunfeiModel extends BaseModel
{
    var $table  = 'yunfei';
    var $prikey = 'id';
    var $_name  = 'yunfei';

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