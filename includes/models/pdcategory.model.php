<?php

/* 频道分类关联控制 */
class PdcategoryModel extends BaseModel
{
    var $table  = 'pd_category';
    var $prikey = 'id';
    var $_name  = 'pd_category';
    var $_relation  = array(

    );
    /* 获取pid */
    function _getpdIds($id)
    {
        $pdids = array();
        $sql="SELECT pd_id from {$this->table} where category_id='$id'";
        $res = $this->db->query($sql);
        while ($row = $this->db->fetchRow($res)) {
            $val=$row['pd_id'];
            $pdids["$val"]= $row['pd_id'];
        }
        return $pdids;
    }

   /*根据频道ID获取分类ID*/
    function _getCategoryByPdId($pid)
    {
        $cate_ids = array();
        $sql="SELECT category_id from {$this->table} where pd_id='$pid' order by sort_id asc";
        $res = $this->db->query($sql);
        while ($row = $this->db->fetchRow($res)) {
            $cate_ids[$row['category_id']]= $row['category_id'];
        }
        return $cate_ids;
    }
   /*
    * 根据分类ID删除频道分类关联关系
    * * */
    function deleteBycateId($id) {
        $sql = "delete from {$this->table} where category_id='${id}'";
        $res = $this->db->query($sql);

        return $res;
    }

}

/* 商品分类业务模型 */
class PdcategoryBModel extends PdcategoryModel{
    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {

        return parent::add($data, $compatible);
    }

    function edit($conditions, $edit_data)
    {
        $this->clear_cache();

        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        return parent::drop($conditions, $fields);
    }


}

?>