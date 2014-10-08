<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PHP在线生成二维码-www.dede007.com</title>
    <SCRIPT LANGUAGE=JavaScript>
        function post(){
            if(document.getElementById('content').value==''){alert('内容不能为空!');document.getElementById('content').focus();return false;}
            if(ckregdatapost()==false){return false;}
        }
    </SCRIPT>
</head>
<body>
<style>
    body{text-align:center;background:#f5f5f5;line-height:22px;font-size:14px;color:#888;}
    .newcrop{margin:10;}
    .er{width:700px;margin:0px auto;border:1px solid gray}
    span{background:#645;color:#fff}
    body form {text-align:center;padding: 3px 6px 3px 6px;}
    input.txt{color: #00008B;background-color: #ADD8E6;border: 1px inset #00008B;width: 200px;}
    input.btn {color: #00008B;background-color: #ADD8E6;border: 1px outset #00008B;padding: 2px 4px 2px 4px;}
    input.smallInput{border:1 solid black;FONT-SIZE: 9pt; FONT-STYLE: normal; FONT-VARIANT: normal; FONT-WEIGHT: normal; HEIGHT: 18px; LINE-HEIGHT: normal}
</style>
<body class="newcrop"><div class="er">
    <?php
    $content=$_POST['content'];
    $width=$_POST['width']?$_POST['width']:300;
    $height=$_POST['height']?$_POST['height']:300;
    if($content){
        echo "你输入的文字是: <span>$content </span><BR />";
        echo "你选择的宽度是: <span>$width </span><BR />";
        echo "你选择的高度是: <span>$height </span><BR /> ";
        echo "生成的二维码图像是：<BR /> ";
        $wen = urlencode($content);
        echo "<img id=qrcode_img src=https://chart.googleapis.com/chart?cht=qr&chld=H&chs={$width}x{$height}&chl={$wen} /><br />
   图片地址:<a href='https://chart.googleapis.com/chart?cht=qr&chld=H&chs={$width}x{$height}&chl={$wen}' target='_blank'>https://chart.googleapis.com/chart?cht=qr&chld=H&chs={$width}x{$height}&chl={$wen}</a><br />
复制发给你的朋友。 <a href='javascript:history.go(-1);'>返回上一页</a>";
    }else{
        ?>
        <form action="index.php?app=two" method="post" onsubmit="return post();">
            <h1>
                二维码生成工具</h1>
            <p>width:<select name="width">
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300" Selected>300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                height: <select name="height">
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300" Selected>300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select></p>
            输入网址或者文字:<br />
            <textarea rows="5" cols="30" name="content" id="content" ></textarea>
            <br /><br />
            <input type="submit" value="生成图片"  /> &nbsp;&nbsp;&nbsp;<INPUT TYPE="RESET" VALUE="重新填写">
            <br />
        </form><?php }?>
    <div></body></html>