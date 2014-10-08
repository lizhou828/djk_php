<?php

return array(
    'dashboard' => array(
        'text'      => Lang::get('dashboard'),
        'subtext'   => Lang::get('offen_used'),
        'default'   => 'welcome',
        'children'  => array(
            'welcome'   => array(
                'text'  => Lang::get('welcome_page'),
                'url'   => 'index.php?act=welcome',
            ),
            /*'aboutus'   => array(
                'text'  => Lang::get('aboutus_page'),
                'url'   => 'index.php?act=aboutus',
            ),*/
            'base_setting'  => array(
                'parent'=> 'setting',
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
            ),
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'parent'=> 'user',
                'url'   => 'index.php?app=user',
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'parent'=> 'store',
                'url'   => 'index.php?app=store',
            ),
            'goods_manage'  => array(
                'text'  => Lang::get('goods_manage'),
                'parent'=> 'goods',
                'url'   => 'index.php?app=goods',
            ),
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'parent'=> 'trade',
                'url'   => 'index.php?app=order'
            ),
        ),
    ),
    // 设置
    'setting'   => array(
        'text'      => Lang::get('setting'),
        'default'   => 'base_setting',
        'children'  => array(
            'base_setting'  => array(
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
            ),
            'region' => array(
                'text'  => Lang::get('region'),
                'url'   => 'index.php?app=region',
            ),
			'country' => array(
                'text'  => '国家设置',
                'url'   => 'index.php?app=country',
            ),
           /* 'payment' => array(
                'text'  => Lang::get('payment'),
                'url'   => 'index.php?app=payment',
            ),
            /*'theme' => array(
                'text'  => Lang::get('theme'),
                'url'   => 'index.php?app=theme',
            ),
            'template' => array(
                'text'  => Lang::get('template'),
                'url'   => 'index.php?app=template',
            ),*/
            'mailtemplate' => array(
                'text'  => Lang::get('noticetemplate'),
                'url'   => 'index.php?app=mailtemplate',
            ),
        ),
    ),
    // 商品
    'goods' => array(
        'text'      => Lang::get('goods'),
        'default'   => 'goods_manage',
        'children'  => array(
            'gcategory' => array(
                'text'  => Lang::get('gcategory'),
                'url'   => 'index.php?app=gcategory',
            ),
            'brand' => array(
                'text'  => Lang::get('brand'),
                'url'   => 'index.php?app=brand',
            ),
            'goods_manage' => array(
                'text'  => Lang::get('goods_manage'),
                'url'   => 'index.php?app=goods',
            ),
            'recommend_type' => array(
                'text'  => LANG::get('recommend_type'),
                'url'   => 'index.php?app=recommend'
            ),

        ),
    ),
    // 店铺
    'store'     => array(
        'text'      => Lang::get('store'),
        'default'   => 'store_manage',
        'children'  => array(
            /*'sgrade' => array(
                'text'  => Lang::get('sgrade'),
                'url'   => 'index.php?app=sgrade',
            ),*/
            'scategory' => array(
                'text'  => Lang::get('scategory'),
                'url'   => 'index.php?app=scategory',
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'url'   => 'index.php?app=store',
            ),
            'member_bank'  => array(
                'text'  => Lang::get('member_bank'),
                'url'   => 'index.php?app=memberbank',
            ),
        ),
    ),
    // 会员
    'user' => array(
        'text'      => Lang::get('user'),
        'default'   => 'user_manage',
        'children'  => array(
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'url'   => 'index.php?app=user&type=01',
            ),
            'admin_manage' => array(
                'text' => Lang::get('admin_manage'),
                 'url'   => 'index.php?app=admin',
             ),
             'user_notice' => array(
                'text' => Lang::get('user_notice'),
                'url'  => 'index.php?app=notice',
             ),
        ),
    ),
    //服务中心
    'service' => array(
        'text'      => Lang::get('service'),
        'default'   => 'service_manage',
        'children'  => array(
            'service' => array(
                'text'  => Lang::get('service_manage'),
                'url'   => 'index.php?app=service&type=02',
            ),
            'caigou' => array(
                'text'  => Lang::get('caigou'),
                'url'   => 'index.php?app=caigou&type=04',
            ),
            'shenhefwzx' => array(
                'text'  => Lang::get('shenhefwzx'),
                'url'   => 'index.php?app=shenhefwzx&act=shenhefwzx',
            ),
        ),
    ),
    // 交易
    'trade' => array(
        'text'      => Lang::get('trade'),
        'default'   => 'order_manage',
        'children'  => array(
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'url'   => 'index.php?app=order'
            ),
            'jymx' => array(
                'text'  => Lang::get('jyjl'),
                'url'   => 'index.php?app=order&act=record'
            ),
            'kszhmx' => array(
                'text'  => Lang::get('koushuizh'),
                'url'   => 'index.php?app=order&act=zfye&type=1'
            ),
            'ukszhmx' => array(
                'text'  => Lang::get('unkoushuizh'),
                'url'   => 'index.php?app=order&act=zfye&type=0'
            ),
            'zhhz' => array(
                'text'  => Lang::get('totalzh'),
                'url'   => 'index.php?app=order&act=zhtotal'
            ),
            'fwzx' => array(
                'text'  => Lang::get('fwzxjy'),
                'url'   => 'index.php?app=order&act=fwzx'
            ),
            'store_ranking' => array(
                'text'  => Lang::get('store_ranking'),
                'url'   => 'index.php?app=order&act=store_ranking'
            ),
            'fwzx_shouyi' => array(
                'text'  => Lang::get('fwzx_shouyi'),
                'url'   => 'index.php?app=order&act=fwzx_list'
            ),
        ),
    ),

    // 提现
    'tixian' => array(
        'text'      => Lang::get('tixian'),
        'default'   => 'view_tixian',
        'children'  => array(
            'view_tixian' => array(
                'text'  => Lang::get('view_tixian'),
                'url'   => 'index.php?app=tixian'
            ),
        ),
    ),

    'qianyue' => array(
        'text'      => '签约联盟',
        'default'   => 'qianyue_index',
        'children'  => array(
            'qianyue_index' => array(
                'text'  => '首页',
                'url'   => 'index.php?app=qianyue'
            )
        ),
    ),

    // 网站
    'website' => array(
        'text'      => Lang::get('website'),
        'default'   => 'acategory',
        'children'  => array(
            'acategory' => array(
                'text'  => Lang::get('acategory'),
                'url'   => 'index.php?app=acategory',
            ),
            'article' => array(
                'text'  => Lang::get('article'),
                'url'   => 'index.php?app=article',
            ),
            'partner' => array(
                'text'  => Lang::get('partner'),
                'url'   => 'index.php?app=partner',
            ),
            'navigation' => array(
                'text'  => Lang::get('navigation'),
                'url'   => 'index.php?app=navigation',
            ),
            'db' => array(
                'text'  => Lang::get('db'),
                'url'   => 'index.php?app=db&amp;act=backup',
            ),
            'groupbuy' => array(
                'text' => Lang::get('groupbuy'),
                'url'  => 'index.php?app=groupbuy',
            ),
            'consulting' => array(
                'text'  =>  LANG::get('consulting'),
                'url'   => 'index.php?app=consulting',
            ),
            'share_link' => array(
                'text'  =>  LANG::get('share_link'),
                'url'   => 'index.php?app=share',
            ),
            'msm_record' => array(
                'text'  =>  LANG::get('msm_record'),
                'url'   => 'index.php?app=msm',
            ),
        ),
    ),
    // 扩展
    'extend' => array(
        'text'      => Lang::get('extend'),
        'default'   => 'plugin',
        'children'  => array(
            'plugin' => array(
                'text'  => Lang::get('plugin'),
                'url'   => 'index.php?app=plugin',
            ),
            'module' => array(
                'text'  => Lang::get('module'),
                'url'   => 'index.php?app=module&act=manage',
            ),
            'widget' => array(
                'text'  => Lang::get('widget'),
                'url'   => 'index.php?app=widget',
            ),
        ),
    ),
    //奖品
    'prize' => array(
        'text'      => Lang::get('prize'),
        'default'   => 'prize1',
        'children'  => array(
            'prize1' => array(
                'text'  => Lang::get('prize1'),
                'url'   => 'index.php?app=prize1',
            ),
            'prize2' => array(
                'text'  => Lang::get('prize2'),
                'url'   => 'index.php?app=prize2',
            ),
        ),
    ),

    //广告
    'guanggao' => array(
        'text'      => "广告管理",
        'default'   => 'guanggao',
        'children'  => array(
            'guanggao' => array(
                'text'  => '广告管理',
                'url'   => 'index.php?app=guanggao',
            )
        ),
    ),

    //广告
    'pos' => array(
        'text'      => "pos机",
        'default'   => 'apply',
        'children'  => array(
            'apply' => array(
                'text'  => 'pos机申请',
                'url'   => 'index.php?app=pos&act=apply',
            ),
            'record' => array(
                'text'  => 'pos机消费',
                'url'   => 'index.php?app=pos&act=record',
            )
        ),
    ),
);

?>
