<?php
/**
 *
 * Created by: zhang
 * Date Time : 2018/5/22 15:04
 */

namespace app\common\model;


use think\Config;
use think\Model;

class Member extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';
    protected $insert = ['ip_number'];

    public function getPhotoAttr($value)
    {
        return empty($value)? '': Config::get('api.res_url').$value;
    }

    public function getPhotoViewAttr($value, $data){
        $photo = empty($data['photo'])? '': Config::get('api.res_url').$data['photo'];
        return empty($photo)? '': '<img src="'.$photo.'" height="40">';
    }

    public function getBindPhoneAttr($value, $data)
    {
        return empty($data['phone'])? 0: 1;
    }

    public function setIpNumberAttr($value)
    {
        return request()->ip(1);
    }

    public function blueAccount()
    {
        return $this->hasOne('MemberBlueAccount', 'member_id', 'member_id')
            ->field('member_id, account');
    }

}