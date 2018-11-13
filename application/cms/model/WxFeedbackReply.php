<?php
/**
 * 反馈回复列表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxFeedbackReply extends model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getTypeTextAttr($value, $data)
    {

        switch ($data['type']){

            case 1:
                return '用户';

            default;
                return '系统';
        }
    }

}