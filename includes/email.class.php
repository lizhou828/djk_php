<?php
class SMTP
{
var $host; //主机
var $port = 25; //端口 一般为25
var $user; //SMTP认证的帐号
var $pass; //认证密码
var $debug = false; //是否显示和服务器会话信息？
var $conn;
var $result_str; //结果
var $in; //客户机发送的命令
var $from; //收件人收到邮件显示的源信箱
var $email; //真实的地址
var $to; //目标信箱
var $subject; //主题
var $body; //内容
var $error;
var $charset = 'utf-8'; //字符集
var $All;
function SMTP($array)
{
$this->host = $array['host'];
$this->port = $array['port'];
$this->email= $array['trueemail'];
$this->from = $array['from'];
$this->user = base64_encode($array['username']);
$this->pass = base64_encode($array['password']);
$this->debug = $array['debug'];
$this->socket = @socket_create (AF_INET, SOCK_STREAM, SOL_TCP);

if($this->socket){
$this->result_str = "创建SOCKET:".socket_strerror(socket_last_error());
$this->debug_show($this->result_str);
}
else
die("初始化失败，请检查您的网络连接和参数");

$this->conn = socket_connect($this->socket,$this->host,$this->port);
if($this->conn){
$this->result_str = "创建SOCKET连接:".socket_strerror(socket_last_error());
$this->debug_show($this->result_str);
}
else
die("初始化失败，请检查您的网络连接和参数");

$this->result_str = "服务器应答：<font color=#cc0000>".socket_read ($this->socket, 1024)."</font>";
$this->debug_show($this->result_str);


}

function debug_show($str)
{
if($this->debug)
{
echo $str."<p>\r\n";
}
}

function setmail($to,$subject,$body){
$this->to = $to;
$this->subject = iconv("utf-8","gbk",$subject);
$this->body = $body;

$All ="Content-type:text/html;charset={$this->charset}\r\n"; //邮件的编码方式可以根据自己的需要改
$All .= "From:".$this->from."\r\n";
$All .= "To:".$this->to."\r\n";
$All .= "Subject:".$this->subject."\r\n\r\n";
$All .= $this->body;
$this->All = $All;
}
/**
* 发送邮件部分
* 接收邮箱数组
*/
function send($toarray,$subject,$body)
{
//以下是和服务器会话
$this->in = "EHLO HELO\r\n";
$this->docommand();

$this->in = "AUTH LOGIN\r\n";
$this->docommand();

$this->in = $this->user."\r\n";
$this->docommand();

$this->in = $this->pass."\r\n";
$this->docommand();

foreach( $toarray as $to ) {
$this -> setmail($to,$subject,$body);

$this->in = "RSET\r\n";
$this->docommand();

$this->in = "MAIL FROM:<".$this->email.">\r\n";
$this->docommand();

$this->in = "RCPT TO:<".$this->to.">\r\n";
$this->docommand();

$this->in = "DATA\r\n";
$this->docommand();

$this->in = $this->All."\r\n.\r\n";
$this->docommand();
}

$this->in = "QUIT\r\n";
$this->docommand();

//结束，关闭连接
}
function docommand()
{
socket_write ($this->socket, $this->in, strlen ($this->in));
$this->debug_show("Client Action：".$this->in);
$this->result_str = "Server：<font color=#cc0000>".socket_read ($this->socket, 1024)."</font>";
$this->debug_show($this->result_str);
}
}
/*
$mailconfig['host'] = "smtp.163.com"; //主机
$mailconfig['port'] = "25"; //端口 一般为25
$mailconfig['trueemail'] = "dajike01@163.com"; //真实的地址
$mailconfig['username'] = "dajike01"; //SMTP认证的帐号
$mailconfig['password'] = "456572490"; //改成自己的
$mailconfig['debug'] = false; //是否显示和服务器会话信息？
$mailconfig['from'] = "dajike01@163.com"; //显示给用户的发件人
set_time_limit(180);
$smtp = new SMTP($mailconfig);
$GLOBALS["smtp"]=$smtp;

$title = "系统出错";
$content = "测试啊http://www.baidu.com<script>alert('aaa');</script>";
$smtp->send(array("17706753@qq.com"),$title,$content);
*/

?>