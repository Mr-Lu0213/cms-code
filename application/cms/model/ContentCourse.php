<?php
/**
 *
 * Created by: zhang
 * Date Time : 2018/5/22 15:04
 */

namespace app\cms\model;


use think\Config;
use think\Model;
use think\Session;

class ContentCourse extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';
    protected $auto = ['charge'];

    public function setChargeAttr($value, $data)
    {
        return empty($data['price'])? 0: 1;
    }

    public function getStatusTextAttr($value, $data)
    {
        return empty($data['status'])?
            '<span class="label label-danger">已下线</span>':
            '<span class="label label-success">已上线</span>';
    }

    public function getRecommendTextAttr($value, $data)
    {
        return empty($data['recommend'])?
            '<span class="label label-warning">未推荐</span>':
            '<span class="label label-success">已推荐</span>';
    }

    public function getTypeTextAttr($value, $data)
    {
        switch ($data['type']){
            case 1:
                return '视频';
            case 2:
                return '音频';
            default:
                return '未知';
        }
    }

    public function getOperateTextAttr($value, $data)
    {
        switch ($data['operate_type']){
            case 1:
                return '普通课';
            case 2:
                return '系列课';
            default:
                return '未知';
        }
    }

    public function getPhotoAttr($value)
    {
        return empty($value)? '': Config::get('aliyun_oss.res_url').$value;
    }

    public function getRawPhotoAttr($value, $data)
    {
        return $data['photo'];
    }

    public function getPriceAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }

    public function setPriceAttr($value)
    {
        return $value * 100;
    }


    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function setContentAttr($value)
    {
        return htmlspecialchars($value);
    }

    public function getTagsAttr($value){
        return empty($value)?array():array_filter(explode(',', trim($value,',')));
    }

    public function setTagsAttr($value){
        if(empty($value) || !is_array($value)){
            return '';
        }
        return ','.implode(',', $value).',';
    }
    public function dict()
    {
        return $this->hasOne('BasicContentClass', 'class_id', 'class_id');
    }

    /**
     * 课程章节
     * @return $this
     */
    public function maters()
    {
        return $this->hasMany('ContentCourseMaterial', 'course_id', 'course_id')
            ->field("ma_id mater_id, ma_title title, trial, sort");
    }

    /**
     * 课程详情
     * @return \think\model\relation\HasOne
     */
    public function detail()
    {
        return $this->hasOne('ContentCourseDetail', 'course_id', 'course_id');
    }

    public function commentNum()
    {
        return $this->hasMany('ContentComment', 'target_id', 'course_id')
            ->where(['type'=>1, 'status'=>1])->count();
    }

    public function orderCourse()
    {
        return $this->hasOne('Order', 'service_id', 'course_id');
    }

    public function expert()
    {
        return $this->hasOne('ContentExpert', 'expert_id', 'expert_id');
    }

}