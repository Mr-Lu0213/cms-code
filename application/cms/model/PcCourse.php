<?php
/**
 * 专家展示
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class PcCourse extends Model
{

    public function getPriceAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }

    public function setPriceAttr($value)
    {
        return $value;
    }

    public function getPhotoAttr($value)
    {
        return empty($value)? '': Config::get('aliyun_oss.res_url').$value;
    }

       public function course()
    {
        return $this->hasOne('ContentCourse', 'course_id', 'course_id');
    }

}