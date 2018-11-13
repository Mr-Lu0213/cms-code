<?php
// 微信相关配置文件
use think\Env;
return [
    'app_id'        => Env::get('wx_app_id'),                    // 应用id
    'app_secret'    => Env::get('wx_app_secret'),      // 应用密钥
    'mch_id'        => Env::get('wx_mch_id'),
    'key'           => Env::get('wx_key'),
    'notify_url'    => Env::get('wx_notify_url'),

    // 模板ID
    'temple_list' => [

    ],

];