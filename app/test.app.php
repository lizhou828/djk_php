<?php

    $str1=strtotime("2014-04-02");
    $str2=strtotime("2014-04-12");
    echo date($str1);
    echo date($str2)."<br/>";
    if($str2 > $str1){
        echo "true<br/>";
    }

    if( preg_match("/^1[3|4|5|8][0-9]\d{8}$/",13412431233) == 1 ){
        echo "匹配";
    }else{
        echo "不匹配";
    }

    echo "(1-0.16)*0.01=". (1-0.16)*0.01;

    echo "<hr/>";

    $str= "a计算 中英文 混合 1234 字符串的 长度 abcd";
    var_dump(explode(" " ,$str)) ;
    echo "<hr/>";
        echo strpos($str,"7");
    echo "<hr/>";
    $num1=37.5;
    $num2=89.3;
    echo "1-37.5/89.3=". round(1-37.5/89.3,4);





?>