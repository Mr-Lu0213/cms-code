<?php
/**
 * Created by qizhao
 * Date: 2018/6/25
 * Time: 17:10
 */

namespace app\cms\model;

use think\Model;

class ContentCourseDetail extends Model
{
    protected $autoWriteTimestamp = false;

    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function setContentAttr($value)
    {
        return htmlspecialchars($value);
    }
}