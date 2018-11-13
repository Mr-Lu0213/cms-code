<?php
/**
 * 音频内容表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxAudioColumnItem extends Model
{

    // 自动维护创建时间和更新时间
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';


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
    public function getIconUrlAttr($value,$data)
    {
        return empty($data['icon'])? '': Config::get('aliyun_oss.res_url').$data['icon'];
    }

    public function course()
    {
        return $this->hasOne('ContentCourse','content_id','course_id');
    }

    public function audio()
    {
        return $this->hasOne('WxAudioColumn','column_id','column_id');
    }
    public function courses()
    {
        return $this->hasOne('ContentCourseMaterial', 'ma_id', 'ma_id');
    }
}
