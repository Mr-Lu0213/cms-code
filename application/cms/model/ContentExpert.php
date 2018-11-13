<?php
/**
 * 专家列表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class ContentExpert extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getTypeTextAttr($value, $data)
    {

        switch ($data['type']){
            case 1:
                return '咨询';

            case 2:
                return '讲师';

            case 3:
                return '全能';

            default;
                return '';
        }
    }

    public function getSexTextAttr($value, $data)
    {

        switch ($data['sex']){
            case 1:
                return '男';

            default;
                return '女';
        }
    }

    public function getOperateTextAttr($value, $data)
    {

        switch ($data['operate_type']){
            case 1:
                return '普通专家';

            default;
                return '系列课专家';
        }
    }
    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){

            case 3:
                return '上线';

            default;
                return '下线';
        }
    }

    public function getRecommendTextAttr($value, $data)
    {
        switch ($data['recommend']){
            case 0:
                return '正常';

            default;
                return '推荐';
        }
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

    public function getPriceAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }

    public function setPriceAttr($value)
    {
        return $value * 100;
    }

    public function getPhotoUrlAttr($value,$data)
    {
        return empty($data['photo'])? '': Config::get('aliyun_oss.res_url').$data['photo'];
    }

    public function getBannerUrlAttr($value,$data)
    {
        return empty($data['banner'])? '': Config::get('aliyun_oss.res_url').$data['banner'];
    }

}