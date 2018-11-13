<?php
//配置文件
return [
    //资源目录
    'view_replace_str' =>  [
        '__PUBLIC__' => ROOT_PATH.'/public',
        '__STATIC__' => ROOT_PATH.'/public/static',
        '__CSS__'    => ROOT_PATH.'/public/static/css',
        '__JS__'     => ROOT_PATH.'/public/static/js',
    ],

    'app_host' => '',   // url根域名

    //模板
    'template'  =>  [
        'layout_on'     =>  true,
        'layout_name'   =>  'layout/default',
        'layout_item'   =>  '{__REPLACE__}'
    ],

    //'exception_handle'       => '\\app\\cms\\exception\\Http',

    'system_name' => '3.0管理系统',
    'system_abbr' => 'CMS',

    'session'    => [
        'prefix'         => 'cms',
        'type'           => '',
        'auto_start'     => true,
    ],
];