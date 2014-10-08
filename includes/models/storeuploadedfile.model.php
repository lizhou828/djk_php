<?php

/* 我的银行卡 */
class StoreuploadedfileModel extends BaseModel
{
    var $table  = 'store_image';
    var $prikey = 'file_id';
    var $_name  = 'storeuploadedfile';

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

    /*
* 获得店铺默认照片
* **/
    function get_store_img($id)
    {
        $sql = "SELECT  file_path FROM ".DB_PREFIX."store_image WHERE store_id =".$id." AND dropState=1 ORDER BY if_default DESC LIMIT 1";
        $res = $this->db->getCol($sql);
        return $res;

    }
}

?>