<?php
/**
 * 周计划报名管理
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxWeekPlanJoin extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getTypeTextAttr($value, $data)
    {

        switch ($data['type']){
            case 1:
                return '支付';

            default;
                return '邀请好友';
        }
    }

    public function getStatusTextAttr($value, $data)
    {
        switch ($data['status']){
            case 1:
                return '报名中';

            default;
                return '报名成功';
        }
    }
}