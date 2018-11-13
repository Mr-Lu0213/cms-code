<?php
/**
 * 小秘书列表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxSystemMsg extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = false;
    protected $autoWriteTimestamp = 'datetime';

    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){

            case 1:
                return '未删除';

            default;
                return '已删除';
        }
    }
}