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

class WxWeekPlanItem extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getWeekTextAttr($value, $data)
    {
        return "第{$data['week_num']}天";
    }

    public function getContentTextAttr($value, $data)
    {

        switch ($data['content_type']){
            case 1:
                return '课程';

            case 2:
                return '资讯';

            default;
                return '专家';
        }
    }

    /**
     * 关联内容
     */
    public function content()
    {
        return $this->hasOne('WxWeekItemContent', 'item_id', 'item_id');
    }
}