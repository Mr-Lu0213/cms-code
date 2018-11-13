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

class WxFeedback extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){

            case 0:
                return '已读';

            default;
                return '未读';
        }
    }

    public function getSysunreadTextAttr($value, $data)
    {

        switch ($data['sys_unread']){

            case 0:
                return '已读';

            default;
                return '未读';
        }
    }

    public function member()
    {
        return $this->hasOne('WxMember', 'member_id', 'member_id');
    }
}