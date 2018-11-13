<?php
/**
 * 用户评论表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;


class WxColumn extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getTypeTextAttr($value, $data)
    {

        switch ($data['type']){
            case 1:
                return '视频课';

            default;
                return '专家';
        }
    }

    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){

            case 0:
                return '下线';

            default;
                return '上线';
        }
    }
    public function expert()
    {
        return $this->hasOne('ContentExpert');
    }

    public function course()
    {
        return $this->hasOne('ContentCourse');
    }


}