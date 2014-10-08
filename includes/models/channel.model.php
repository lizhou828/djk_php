<?php

/* 渠道表 */
class ChannelModel extends BaseModel
{
    var $table  = 'channel';
    var $prikey = 'channel_id';
    var $_name  = 'channel';

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