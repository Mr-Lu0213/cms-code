<?php
/**
 * 轮播图
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class PcShow extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = false;
    protected $autoWriteTimestamp = 'datetime';

    public function getStorageAttr($value)
    {
        return empty($value)? '': Config::get('aliyun_oss.res_url').$value;
    }

    public function getTypeTextAttr($value, $data)
    {

        switch ($data['type']){
            case 1:
                return '视频';

            default;
                return '图片';
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
}