<?php
/**
 * 周计划内容表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxWeekItemContent extends Model
{
    protected $autoWriteTimestamp = false;

    public function setContentAttr($value, $data)
    {
        return htmlspecialchars($data['content']);
    }

    public function getContentAttr($value)
    {

        return htmlspecialchars_decode($value);
    }

}