<?php
/**
 * 微课堂内容表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxColumnItem extends Model
{

    // 自动维护创建时间和更新时间
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';


    public function getContentTypeTextAttr($value, $data)
    {
        switch ($data['content_type']){

            case 1:
                return '课程';

            default;
                return '专家';
        }
    }

    public function getStatusTextAttr($value ,$data)
    {
        switch ($data['status']){
            case 1:
                return '上线';
            case 3:
                return '上线';
            default;
                return '下线';
        }
    }
    public function expert()
    {
        return $this->hasOne('ContentExpert','content_id','expert_id');
    }

    public function course()
    {
        return $this->hasOne('ContentCourse','content_id','course_id');
    }

    public function column()
    {
        return $this->hasOne('WxColumn','column_id','column_id');
    }

}
