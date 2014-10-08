<?php

define('THUMB_QUALITY', 85);
define('INDEX_TOP_WIDTH',180);
define('INDEX_TOP_HEIGHT',190);

define('INDEX_CENTER_WIDTH',160);
define('INDEX_CENTER_HEIGHT',160);

define('INDEX_LEFT_WIDTH',80);
define('INDEX_LEFT_HEIGHT',80);

define('GOOD_BIG_WIDTH',310);
define('GOOD_BIG_HEIGHT',310);

define('GOOD_MIDDLE_WIDTH',312);
define('GOOD_MIDDLE_HEIGHT',189);

define('NEW_GOOD_SMALL_WIDTH',100);
define('NEW_GOOD_SMALL_HEIGHT',100);

define('NEW_GOOD_MIDDLE_WIDTH',220);
define('NEW_GOOD_MIDDLE_HEIGHT',220);

define('NEW_GOOD_BIG_WIDTH',350);
define('NEW_GOOD_BIG_HEIGHT',350);

define('NEWS_GOOD_MIDDLE_WIDTH',280);
define('NEWS_GOOD_MIDDLE_HEIGHT',170);

define('BDSH_INDEX_GOOD_WIDTH',220);
define('BDSH_INDEX_GOOD_HEIGHT',120);


define('BDSH_GOOD_DETAIL_WIDTH',340);
define('BDSH_GOOD_DETAIL_HEIGHT',240);

define('BDSH_GOOD_LIST_WIDTH',150);
define('BDSH_GOOD_LIST_HEIGHT',90);

define('BDSH_GOOD_DETAIL1_WIDTH',280);
define('BDSH_GOOD_DETAIL1_HEIGHT',170);

define('BDSH_GOOD_LIST1_WIDTH',220);
define('BDSH_GOOD_LIST1_HEIGHT',220);
class ImgApp extends MallbaseApp
{
    function index() {
        import('image.func');
        import('uploader.lib');
        /*$sizes = array(BDSH_INDEX_GOOD_WIDTH . 'X' . BDSH_INDEX_GOOD_HEIGHT,
            BDSH_GOOD_DETAIL_WIDTH . 'X' . BDSH_GOOD_DETAIL_HEIGHT,
            BDSH_GOOD_LIST_WIDTH . 'X' .BDSH_GOOD_LIST_HEIGHT,
            BDSH_GOOD_DETAIL1_WIDTH .'X' . BDSH_GOOD_DETAIL1_HEIGHT,
            BDSH_GOOD_LIST1_WIDTH .'X' . BDSH_GOOD_LIST1_HEIGHT,
            INDEX_TOP_WIDTH . 'X' . INDEX_TOP_HEIGHT, INDEX_CENTER_WIDTH . 'X' . INDEX_CENTER_HEIGHT,
            INDEX_LEFT_WIDTH . 'X' .INDEX_LEFT_HEIGHT, GOOD_BIG_WIDTH .'X' . GOOD_BIG_HEIGHT,GOOD_MIDDLE_WIDTH.'X'.GOOD_MIDDLE_HEIGHT,
            NEW_GOOD_BIG_WIDTH . 'X' . NEW_GOOD_BIG_HEIGHT,
            NEW_GOOD_MIDDLE_WIDTH . 'X' . NEW_GOOD_MIDDLE_HEIGHT,
            NEW_GOOD_SMALL_WIDTH. 'X' .NEW_GOOD_SMALL_HEIGHT,
            NEWS_GOOD_MIDDLE_WIDTH. 'X'.NEWS_GOOD_MIDDLE_HEIGHT
        );*/


        $sizes = array(
            BDSH_GOOD_DETAIL_WIDTH . 'X' . BDSH_GOOD_DETAIL_HEIGHT
        );



        for ($idx = 30; $idx < 32; $idx++) {
            $f_name = "";
            $basedir="/dis/data/files/2013/12";
            $f_name = $idx + "";
            if ($idx < 11) $f_name = "0".$f_name;
            $basedir = $basedir . $f_name;
            echo $basedir."<br/>";
            if ($dh = opendir($basedir)) {
                while (($file = readdir($dh)) !== false) {

                    if ($file != '.' && $file != '..' && $file != '.svn' && $file != 'files' && $file != 'styles'){
                        if (!is_dir($basedir."/".$file)) {

                            if (count(explode('X', $file)) < 2 && count(explode("small", $file)) < 2) {
                                //echo "$basedir/$file<br/>";
                                $urls = explode('.', "$basedir/$file");
                                for($i = 0; $i < count($sizes); $i++){
                                    $path = $urls[0] . '_' . $sizes[$i] . '.' . $urls[1];
                                    //echo $path . "<br/>";
                                    $size = explode('X', $sizes[$i]);
                                    if(!file_exists($path)){
                                        make_thumb("$basedir/$file", $path, $size[0], $size[1], THUMB_QUALITY);
                                        print_r("<font color='red'>" . $path . "</font>" . "<br/>");
                                        print_r("<br>");
                                    }
                                }
                            }


                        }else{
                            /*$dirname = $basedir."/".$file . "<br/>";
                            echo $dirname;*/

                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
?>
