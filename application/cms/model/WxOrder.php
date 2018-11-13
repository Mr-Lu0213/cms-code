<?php
/**
 * 订单管理
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxOrder extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getTypeTextAttr($value, $data)
    {

        switch ($data['type']){
            case 1:
                return '订阅课程';

            case 2:
                return '订阅专家';

            case 3:
                return '订阅周计划';

            default;
                return '';
        }
    }

    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){
            case 1:
                return '待支付';

            case 2:
                return '支付成功';

            default;
                return '已过期';
        }
    }

    public function getPayTextAttr($value, $data)
    {

        switch ($data['pay_type']) {

            case 1:
                return '微信';

            default;
                return '';
        }
    }
    public function getMoneyAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }

    public function setMoneyAttr($value)
    {
        return $value * 100;
    }

    public function getOriginalAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }
    public function setOriginalAttr($value)
    {
        return $value * 100;
    }

    public function getBalanceAttr($value)
    {
        return sprintf("%0.2f", $value/100);
    }
    public function setBalanceAttr($value)
    {
        return $value * 100;
    }


    public function expert()
    {
        return $this->hasOne('ContentExpert', 'service_id', 'expert_id');
    }

    public function course()
    {
        return $this->hasOne('ContentCourse', 'service_id', 'course_id');
    }

    public function plan()
    {
        return $this->hasOne('WxWeekPlan', 'service_id', 'plan_id');
    }

    public function member()
    {
        return $this->hasOne('WxMember', 'member_id', 'member_id');
    }

}