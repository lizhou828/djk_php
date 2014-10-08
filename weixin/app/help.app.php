<?php


define('WEI_ABOUT', 'm_about');
define('WEI_GUIDE', 'm_guide');
define('WEI_PAY', 'm_pay');
define('WEI_ASALE', 'm_Asale');
/**
 *    默认控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class HelpApp extends MallbaseApp
{
    function index()
    {
        $acategory_mod =& m('acategory');
        $article_mod =& m('article');
        $sql = "SELECT * FROM ecm_acategory where code = '" . WEI_ABOUT . "' or code = '" . WEI_GUIDE .
               "' or code = '" . WEI_PAY . "' or code = '" . WEI_ASALE . "' and dropState = 1";
        $acategory = $acategory_mod -> db ->getAll($sql);
        if(count($acategory) > 0){
            foreach($acategory as $key=>$value){
                $article = $article_mod -> find("cate_id = ".$value['cate_id'] . " and dropState = 1");
                $count = count($article) - 1;
                $acategory[$key]['article'] = $article;
                $acategory[$key]['count'] = $count;
            }
        }
        $this -> assign('acategory',$acategory);
        $this -> display("help.list.html");
    }

    function show(){
        $article_mod =& m('article');
        $article_id = $_GET['article_id'];
        if($article_id != ""){
            $article = $article_mod -> get($article_id);
        }
        $this -> assign('article',$article);
        $this -> display("help.show.html");
    }

}
?>
