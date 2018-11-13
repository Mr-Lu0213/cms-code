<?php
/**
 * 资讯列表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class ContentArticleDetail extends Model
{
    //content获取器
    public function getContentAttr($value)
    {
        //解码
        return htmlspecialchars_decode($value);
    }

    //修改器
    public function setContentAttr($value)
    {
        //转码
        return htmlspecialchars($value);
    }


}