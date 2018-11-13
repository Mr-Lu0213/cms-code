<?php
/**
 *
 * Created by: zhang
 * Date Time : 2018/5/22 15:04
 */

namespace app\cms\model;


use think\Config;
use think\Model;

class WxBanner extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getPhotoAttr($value)
    {
        return empty($value)? '': Config::get('aliyun_oss.res_url').$value;
    }

    public function getRawPhotoAttr($value, $data)
    {
        return $data['photo'];
    }

    public function getStatusTextAttr($value, $data)
    {
        $map = [
            0=>'<span class="label label-danger">已下线</span>',
            1=>'<span class="label label-success">已上线</span>'
        ];
        return empty($map[$data['status']])? '<span class="label label-warning">未知</span>': $map[$data['status']];
    }

    public function getRecommendTextAttr($value, $data)
    {
        $map = [
            0=>'<span class="label label-danger">未置顶</span>',
            1=>'<span class="label label-success">已置顶</span>'
        ];
        return empty($map[$data['recommend']])? '<span class="label label-warning">未知</span>': $map[$data['recommend']];
    }

    public function getTypeTextAttr($value, $data)
    {
        $map = [1=>'首页', 2=>'微课堂', 3=>'首页推荐',4=>'音频'];
        return empty($map[$data['type']])? '未知': $map[$data['type']];
    }

    public function getLinkTypeTextAttr($value, $data)
    {
        $map = [1=>'视频', 2=>'资讯', 3=>'专家', 4=>'外部链接',5=>'音频'];
        return empty($map[$data['link_type']])? '未知': $map[$data['link_type']];
    }

    public function getUrlAttr($value, $data)
    {
        $h5_uri = Config::get('api.h5_uri');
        switch ($data['type']){
            case 1:
                return $h5_uri.'courseDetails?courseId='.$value;

            case 2:
                return $h5_uri.'newsDetails?newsId='.$value;

            default:
                return $value;
        }
    }


}