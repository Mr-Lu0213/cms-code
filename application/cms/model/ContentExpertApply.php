<?php
/**
 * 专家申请
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class ContentExpertApply extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getPhotoUrlAttr($value,$data)
    {
        return empty($data['photo'])? '': Config::get('aliyun_oss.res_url').$data['photo'];
    }

    public function getStatusTextAttr($value, $data)
    {
        if($data['status'] == 1){
            return '未处理';
        }else{
            return '已处理';
        }
    }

    /**
     * 关联用户
     * @return \think\model\relation\HasOne
     */
    public function member()
    {
        return $this->hasOne('WxMember', 'member_id', 'member_id');
    }

}