<?php

$lv = json_encode(array(0 => '谢谢惠顾', 1 => '大奖池一等奖' , 2 => '大奖池二等奖' , 3 => '大奖池三等奖',
                       4 => '大奖池四等奖', 5 => '大奖池五等奖' , 6 => '大奖池六等奖' , 7 => '大奖池七等奖',
                       8 => '大奖池八等奖', 9 => '大奖池久等奖' , 11 => '小奖池一等奖' , 12 => '小奖池二等奖',
                       13 => '小奖池三等奖', 14 => '小奖池四等奖' , 15 => '小奖池五等奖' , 16 => '小奖池六等奖',
                       17 => '小奖池七等奖', 18 => '小奖池八等奖' , 19 => '小奖池久等奖'));
define('LEVEL',$lv);

class Prize2App extends BackendApp
{
   function index(){
        $level = json_decode(LEVEL);
        $this->assign('level',$level);
        $date = $_POST['date'];
        $page = $this->_get_page();
        $prize2_model = & m('prize2');
        if(!empty($date)){
            $condition = 'choujiang_date =\'' . $date . '\' ';
            $sql = "select * from {$prize2_model -> table} where ";
            $sql .=  $condition ;
            $sql .=  " order by create_time desc limit ";
            $sql .= $page['limit'];
        }else{
            $sql = "select * from {$prize2_model->table} order by create_time desc limit ";
            $sql = $sql . $page['limit'];
        }
        $sqlCount = "select count(*) from {$prize2_model->table}";
        $prize2_model->_updateLastQueryCount($sqlCount);
        $prize2 = $prize2_model -> db ->getAll($sql);
        $page['item_count'] = $prize2_model->getCount();
        $this->_format_page($page);
        $this->assign('prize2',$prize2);
        $this->assign('page_info',$page);
        $this->display('prize2.index.html');
   }

    function add(){
        $level = json_decode(LEVEL);
        $this->assign('level',$level);
        $this->display('prize2.add.html');
    }

    function create(){
        $level = json_decode(LEVEL);
        $this->assign('level',$level);
        $jibie = $_POST['jibie'];
        $jine = $_POST['jine'];
        $choujiang_date = $_POST['choujiang_date'];
        $count = $_POST['count'];
        $sum_jine = $_POST['sum_jine'];
        $sum_jine2 = $_POST['sum_jine2'];
        $pool_count = $_POST['pool_count'];
        $this->assign('jibie',$jibie);
        $this->assign('jine',$jine);
        $this->assign('choujiang_date',$choujiang_date);
        $this->assign('count',$count);
        $this->assign('sum_jine',$sum_jine);
        $this->assign('sum_jine2',$sum_jine2);
        $this->assign('pool_count',$pool_count);
        if($jibie == null){
            $this->assign('error1','请输入级别');
            $this->display('prize2.add.html');
            return;
        }
        if($jine == null){
            $this->assign('error2','请输入金额');
            $this->display('prize2.add.html');
            return;
        }
        if($choujiang_date == null){
            $this->assign('error3','请输入抽奖时间');
            $this->display('prize2.add.html');
            return;
        }
        if($count == null){
            $this->assign('error4','请输入奖品数量');
            $this->display('prize2.add.html');
            return;
        }
        if($sum_jine == null){
            $this->assign('error5','请输入该级别总奖金');
            $this->display('prize2.add.html');
            return;
        }
        if($sum_jine2 == null){
            $this->assign('error7','请输入该级别奖池总奖金');
            $this->display('prize2.add.html');
            return;
        }
        if($pool_count == null){
            $this->assign('error8','请输入池总数量');
            $this->display('prize2.add.html');
            return;
        }
        $prize2_model = & m('prize2');
        $prize2 = $prize2_model->find(array(
              'conditions' =>  'jibie = ' . $jibie . ' and choujiang_date = \'' . $choujiang_date . '\''
            ));
        if(!empty($prize2)){
            $this->assign('error6','该级别抽奖时间以存在');
            $this->display('prize2.add.html');
            return;
        }
        $data = array(
            'jibie' => $jibie,
            'jine' => $jine,
            'choujiang_date' => $choujiang_date,
            'count' => $count,
            'sum_jine' => $sum_jine,
            'create_time' => date('Y-m-d H:i:s',time()),
            'sum_jine2' => $sum_jine2,
            'pool_count' => $pool_count
        );
        $prize2_model -> add($data);
        $this->show_message('add_ok',
            'back_list',    'index.php?app=prize2',
            'continue_add', 'index.php?app=prize2&amp;act=add'
        );
    }

    function edit(){
        $level = json_decode(LEVEL);
        $this->assign('level',$level);
        $jibie = $_GET['jibie'];
        $choujiang_date = $_GET['choujiang_date'];
        $prize2_model = & m('prize2');
        $prize2 = $prize2_model -> find(array(
            'conditions' =>  'jibie = ' . $jibie . ' and choujiang_date = \'' . $choujiang_date . '\''
        ));
        $this->assign('prize',$prize2);
        $this->display('prize2.edit.html');
    }

    function save(){
        $level = json_decode(LEVEL);
        $this->assign('level',$level);
        $jibie = $_POST['jibie'];
        $jine = $_POST['jine'];
        $choujiang_date = $_POST['choujiang_date'];
        $count = $_POST['count'];
        $sum_jine = $_POST['sum_jine'];
        $sum_jine2 = $_POST['sum_jine2'];
        $pool_count = $_POST['pool_count'];
        $prize2_model = & m('prize2');
        if($jibie == null || $count == null || $sum_jine == null || $sum_jine2 == null || $pool_count == null){
            $prize2 = $prize2_model -> find(array(
                'conditions' =>  'jibie = ' . $jibie . ' and choujiang_date = \'' . $choujiang_date . '\''
            ));
            $this->assign('prize',$prize2);
            $this->assign('error6','请不要输入空的数据');
            $this->display('prize2.edit.html');
            return;
        }
        $data = array(
                'jibie' => $jibie,
                'jine' => $jine,
                'choujiang_date' => $choujiang_date,
                'count' => $count,
                'sum_jine' => $sum_jine,
                'sum_jine2' => $sum_jine2,
                'pool_count' => $pool_count
        );
        $prize2_model -> edit(' jibie ='.$jibie.' and choujiang_date = \''.$choujiang_date.'\'',$data);
        $this->show_message('edit_ok',
                'back_list',    'index.php?app=prize2'
        );
    }

    function drop(){
           $jibie = $_GET['jibie'];
           $choujiang_date = $_GET['choujiang_date'];
           $prize2_model = & m('prize2');
           $prize2 = $prize2_model->find(array(
            'conditions' =>  'jibie = ' . $jibie . ' and choujiang_date = \'' . $choujiang_date . '\''
            ));
           if($prize2 != null){
              $sql = "delete from {$prize2_model->table} where jibie = " . $jibie . " and choujiang_date = '" . $choujiang_date . "'";
              $prize2_model -> db -> query($sql);
           }
           $this->show_message('drop_ok');
    }
}

?>