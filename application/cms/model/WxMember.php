<?php
/**
 * 用户管理
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxMember extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getSexTextAttr($value, $data)
    {

        switch ($data['sex']){
            case 1:
                return '男';

            case 2:
                return '女';

            default;
                return '未知';
        }
    }

    public function getSourceTextAttr($value, $data)
    {

        switch ($data['source']){
            case 0:
                return '内部账号';

            case 1:
                return '微信';

            default;
                return '未知';
        }
    }

    public function getPhotoUrlAttr($value,$data)
    {
        return empty($data['photo'])? '': '<img width=40 src="'.Config::get('aliyun_oss.res_url').$data['photo'].'">';
    }

    public function getBalanceAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }

    public function setBalanceAttr($value)
    {
        return $value * 100;
    }

}
