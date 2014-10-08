<?php

/*在线客服 */
class ZxkfModel extends BaseModel
{
    var $table  = 'chat_message';
    var $prikey = 'id';
    var $_name  = 'chat_message';

    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {
        return parent::add($data, $compatible);
    }


}

?>