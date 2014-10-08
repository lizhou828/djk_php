<?php

return array (
  'SHOP_DOMAIN' => 'http://testshop.dajike.com',
  'SITE_URL' => 'http://192.168.0.155',
  'STATIC_URL'=>'http://192.168.0.155',
  'IMG_URL'=>'http://192.168.0.155',
  'DB_CONFIG' => 'mysql://ecm_user:123456@192.168.0.130:3306/ecm',
  'DB_PREFIX' => 'ecm_',
  'LANG' => 'sc-utf-8',
  'COOKIE_DOMAIN' => '',
  'COOKIE_PATH' => '/',
  'ECM_KEY' => 'e653e04692897e9f6a6a60e34b5e8dbe2',
  'MALL_SITE_ID' => 'EMNGOCA5d642QSci2',
  'ENABLED_GZIP' => 0,
  'DEBUG_MODE' => 0,
  'CACHE_SERVER' => 'default',
  'MEMBER_TYPE' => 'default',
  'ENABLED_SUBDOMAIN' => 0,
  'SUBDOMAIN_SUFFIX' => '',
  'SESSION_TYPE' => 'mysql',
  'SESSION_MEMCACHED' => 'localhost:11211',
  'CACHE_MEMCACHED'  => 'localhost:11211',
  'TO_JIESUAN_URL'   => 'http://localhost:8080/dajike-account/account.action',                 //结算URL
  'TO_CHOUJIANG_URL' => 'http://localhost:8080/dajike-account/choujiang',                      //抽奖URL
  'TO_PLCHOUJIANG_URL' => 'http://test.dajike.com/dajike-account/plChoujiang/choujiang.action',
  'NOT_TO_PLCHOUJIANG_URL' => 'http://www.dajike.com/index.php?app=article&act=view&article_id=120',   //抽奖URL
  'TO_PAY_URL'		=> 'http://192.168.0.104:8080/dajike-account/list.action',		          //支付URL
  'TO_CHONGZHI_URL'  => 'http://192.168.0.104:8080/dajike-account/list_bank.action',		  //充值URL
  'TO_QIANGZHU_URL'  => 'http://192.168.0.104:8080/dajike-account/list_qz.action',		      //抢注支付URL
  'PAY_ORDER_BY_MOBILE'=>'http://test1.dajike.com',           //手机微商城：支付宝手机支付

  'JAVA_LOCATION'       =>  '192.168.0.145:8080',      //php调java 通用UEL
  'TO_PHP_JAVA_URL'     => '/dajike-account/finance/list.action',      //php调java 通用UEL
  'SERVICE_SHOUYI'      => '/dajike-account/finance/server_gains.action',                                              //php调java获得服务中心收益
  'CHAT_DOMAIN'=>'http://192.168.0.139:8080',
  'CHAT_CTX'=>'/dajike-chat',
  'CHAT_URL'=>'/chat/chat_iframe.action',
  'IS_CHAT'=>'OFF', //是否开启聊天
  'DIS_PATH'=>'',    //如果 指定了 DIS_PATH 变量，则上传的图片 地址 由  原来的  ROOT_PATH/up_dir        变成   DIS_PATH/up_dir   DIS_PATH 可以是非当前项目的路径    D://Data/......
  'FAIL_TIME'=>120,   //发送邮箱，短信 验证码过期时间
  'IS_MYREWRITE' =>'OFF',
  'QIANGZHU_FAIL_TIME'=>36000,    //服务中心抢注有效时间 精确到秒   36000秒=10小时
  'CART_IS_COOKIE' => 'ON', //购物车用cookie存放

  'EMAIL_HOST'=>'smtp.163.com',          //主机
  'EMAIL_PORT'=>'25',                      //端口 一般为25
  'EMAIL_TRUENAME'=>'dajike01@163.com', //真实的地址
  'EMAIL_USERNAME'=>'dajike01',          //SMTP认证的帐号
  'EMAIL_PASSWORD'=>'456572490',         //密码
  'EMAIL_FROM'=>'dajike01@163.com',     //显示给用户的发件人
  'EMAIL_TIME'=>180,                       //超时时间


    'CHANAL1'           => 'yes',           //是否切换到短信通道1，    默认是no 否， 确认是  yes
    'MSM1_USERID'       => 'mandi',       //短信通道1    短信接口主账号
    'MSM1_PASSWORD'     => '123123',       //短信通道1  短信接口密码
    'MSM1_ACCOUNT'      => '45',         //短信通道1     短信通道ID
    'MSM1_URL'      => '118.145.30.165',         //短信通道1     url
    'MSM1_PORT'      => '60001',         //短信通道1     端口

    'MSM_USERID'       => '961268',       //默认短信通道  短信接口主账号
    'MSM_PASSWORD'     => '1NUEHZ',       //默认短信通道  短信接口密码
    'MSM_ACCOUNT'      => 'admin',         //默认短信通道 短信接口子账号

    'REG_MAX'           =>400,                //最大注册个数
    'IF_DUANXING'      =>1,                   //是否开启短信功能   0 否，1是
	'IF_KEFU'          =>1                    //是否由客服来处理买家咨询商品的聊天 0,否，  1是
);

?>