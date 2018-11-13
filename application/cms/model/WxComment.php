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


class WxComment extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getTypeTextAttr($value, $data)
    {
        switch ($data['content_type']){

            case 1:
                return '视频课';
            case 2:
                return '资讯';
            case 3:
                return '专家';
            default;
                return '';
        }
    }

    public function getStatusTextAttr($value, $data)
    {
        switch ($data['status']){

            case 1:
                return '有效';

            default;
                return '无效';
        }
    }

    public function expert()
    {
        return $this->hasOne('ContentExpert', 'content_id', 'expert_id');
    }

    public function course()
    {
        return $this->hasOne('ContentCourse', 'content_id', 'course_id');
    }

    public function member()
    {
        return $this->hasOne('WxMember', 'member_id', 'member_id');
    }

    public function article()
    {
        return $this->hasOne('ContentArticle', 'content_id', 'article_id');
    }
}