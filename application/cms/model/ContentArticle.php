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

class ContentArticle extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function detail()
    {
        return $this->hasOne('ContentArticleDetail','article_id','article_id');
    }


    public function getSourceTextAttr($value, $data)
    {

        switch ($data['source_type']){
            case 1:
                return '原创';

            default;
                return '转载';
        }
    }

    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){

            case 1:
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

    public function getPhotoUrlAttr($value,$data)
    {
        return empty($data['photo'])? '': Config::get('aliyun_oss.res_url').$data['photo'];
    }

    public function dict()
    {
        return $this->hasOne('BasicContentClass', 'class_id', 'class_id');
    }

}