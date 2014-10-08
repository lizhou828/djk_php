<?php

$lv = json_encode(array(0 => '谢谢惠顾', 1 => '大奖池一等奖' , 2 => '大奖池二等奖' , 3 => '大奖池三等奖',
    4 => '大奖池四等奖', 5 => '大奖池五等奖' , 6 => '大奖池六等奖' , 7 => '大奖池七等奖',
    8 => '大奖池八等奖', 9 => '大奖池久等奖' , 11 => '小奖池一等奖' , 12 => '小奖池二等奖',
    13 => '小奖池三等奖', 14 => '小奖池四等奖' , 15 => '小奖池五等奖' , 16 => '小奖池六等奖',
    17 => '小奖池七等奖', 18 => '小奖池八等奖' , 19 => '小奖池久等奖'));
define('LEVEL',$lv);

class Prize1App extends BackendApp
{
   function index(){
        $level = json_decode(LEVEL);
        $this->assign('level',$level);
        $prize1_model = & m('prize1');
        $page = $this->_get_page();
        $date = $_POST['date'];
        if(!empty($date)){
            $condition = 'choujiang_date =\'' . $date . '\' ';
            $sql = "select * from {$prize1_model->table} where " . $condition . " order by create_time desc limit " . $page['limit'];
        }else{
            $sql = "select * from {$prize1_model->table} order by create_time desc limit " . $page['limit'];
        }
        $prize1 = $prize1_model -> db ->getAll($sql);
        $sqlcount = "select count(*) from {$prize1_model->table}";
        $prize1_model->_updateLastQueryCount($sqlcount);
        $page['item_count'] = $prize1_model->getCount();
        $this->_format_page($page);
        $this->assign('prize1',$prize1);
        $this->assign('page_info',$page);
        $this->display('prize1.index.html');
   }
}

?>