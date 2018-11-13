<?php
/**
 * 音频展示
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxAudioRecommend extends Model
{

    public function getPhotoAttr($value)
    {
        return empty($value)? '': Config::get('aliyun_oss.res_url').$value;
    }


    public function course()
    {
        return $this->hasOne('ContentCourseMaterial', 'ma_id', 'ma_id');
    }

}