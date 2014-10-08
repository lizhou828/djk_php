<?php

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

$menu_data = array
(
    'mall_setting' => array
    (
        'default'     => 'default|all',//后台登录
        'setting'     => 'setting|all',//网站设置
        'region'       => 'region|all',//地区设置
        'payment'    => 'payment|all',//支付方式
        'theme'     => 'theme|all',//主题设置
        'mailtemplate'   => 'mailtemplate|all',//邮件模板
        'template'  => 'template|all',//模板编辑
    ),
    'goods_admin' => array
    (
        'gcategory'    => 'gcategory|all',//分类管理
        'brand' => 'brand|all',//品牌管理
        'goods'    => 'goods|all',//商品管理
        'recommend'    => 'recommend|all',//推荐类型
    ),
    'store_admin' => array
    (
        'sgrade'    => 'sgrade|all',//店铺等级
        'scategory'     => 'scategory|all',//店铺分类
        'store'   => 'store|all',//店铺管理
        'store_index'   => 'store|index',//店铺列表
        'store_edit'   => 'store|edit',//店铺编辑
        'store_view'   => 'store|view',//查看
        'store_drop'   => 'store|drop',//店铺删除
        'member_bank'   => 'member_bank|all',//银行卡管理
    ),
    'member' => array
    (
        'user'  => 'user|all',//会员管理
        'admin' => 'admin|all',//管理员管理
        'notice' => 'notice|all',//会员通知
    ),
    'fwzx' => array
    (
        'service_manage'  => 'service|all',//会员管理
        'caigou_manage' => 'caigou|all',//管理员管理
        'shenhefwzx' => 'shenhefwzx|all',//会员通知
    ),
    'order' => array
    (
        'order'   =>   'order|index' ,//订单列表
        'order_view'   => 'order|view',//订单查看
        'jyjl'   => 'order|record',//交易记录
        'koushuizh'   => 'order|zfye',//扣税账户余额
        'unkoushuizh'   => 'order|zfye',//非扣税账户余额
        'totalzh'   => 'order|zhtotal',//账户余额汇总
        'fwzxjy'   => 'order|fwzx',//账户余额汇总
        'store_ranking'   => 'order|store_ranking',//全国商家业绩
        'fwzx_list'   => 'order|fwzx_list',//服务中心列表
        'fwzx_shouyi'   => 'order|fwzx_shouyi',//服务中心列表
        'fwzx_detail'   => 'order|fwzx_detail',//服务中心详细
        'fwzx_ajax'   => 'order|ajax_store_record',//商家订单明细
        'fwzx_tixian'   => 'order|tixian',//代服务中心提现
    ),
    'website' => array
    (
        'acategory'    => 'acategory|all',//文章分类
        'article'      => array('article' => 'article|all', 'upload' => array('comupload' => 'comupload|all', 'swfupload' => 'swfupload|all')),//文章管理
        'partner'      => 'partner|all',//合作伙伴
        'navigation'   => 'navigation|all',//页面导航
        'db'           => 'db|all',//数据库
        'groupbuy'     => 'groupbuy|all',//团购
        'consulting'   => 'consulting|all',//咨询
        'share_link'   => 'share|all',//分享管理
        'msm_record'   => 'msm|all',//短信发送记录
    ),
    'external' => array
    (
        'plugin' => 'plugin|all',//插件管理
        'module'   => 'module|all',//模块管理
        'widget'   => 'widget|all',//挂件管理
    ),
    'clear_cache' =>array
    (
        'clear_cache' => 'clear_cache|all',//清空缓存
    ),
    'tixian_manager' =>array
    (
        'tixian' => 'tixian|all',//清空缓存
    ),
    'qianyue_manager' =>array
    (
        'qianyue' => 'qianyue|all',//清空缓存
    ),
    'guanggao_manager' =>array
    (
        'guanggao' => 'guanggao|all',//清空缓存
    ),
    'prize_manager' =>array
    (
        'prize1' => 'prize1|all',//清空缓存
        'prize2' => 'prize2|all',//清空缓存
    ),
    'pos_manager' =>array
    (
        'apply' => 'pos|apply',
        'record' => 'pos|record'
    ),
);
?>