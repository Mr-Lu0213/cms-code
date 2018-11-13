<?php
//阿里云oss配置文件
use think\Env;
return [
    'key_id'        => Env::get('oss_id'),
    'key_secret'    => Env::get('oss_key'),
    'end_point'     => Env::get('oss_point'),
    'bucket'        => Env::get('oss_bucket'),
    'res_url'       => Env::get('oss_res_url'),
];