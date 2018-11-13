<?php
/**
 * Created by qizhao
 * Date: 2018/7/3
 * Time: 17:16
 */

namespace app\cms\model;


use think\Model;

class WxCoupon extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';
    protected $insert = ['coupon_code', 'name'];

    public function getTypeTextAttr($value, $data)
    {
        switch ($data['type']){
            case 1:
                return '满减券';

            default;
                return '代金券';
        }
    }

    public function setCouponCodeAttr($value)
    {
        return empty($value)? getRandomString(8): $value;
    }

    public function setNameAttr($value, $data)
    {
        if(!empty($value)){
            return $value;
        }
        if($data['type'] == 1){
            return  "满{$data['reach_amount']}减{$data['amount']}券";
        }elseif ($data['type'] == 2){
            return "{$data['amount']}元代金券";
        }
    }

    public function getSceneTextAttr($value, $data)
    {
        $map = [1=>'用户注册', 2=>'营销活动', 3=>'定向优惠券', 4=>'实体券', 5=>'营销批开'];
        return empty($map[$data['scene']])? '': $map[$data['scene']];
    }

    public function getTargetTextAttr($value, $data)
    {
        $map = [0=>'全部', 1=>'课程', 2=>'专家', 3=>'周计划', 9=>'指定商品'];
        return empty($map[$data['target']])? '': $map[$data['target']];
    }

    public function getStatusTextAttr($value, $data)
    {
        $now = date('Y-m-d H:i:s');
        if($now > $data['end_date']){
            return "<span class='label label-success'>发放结束</span>";
        }else if($now < $data['start_date']){
            return "<span class='label label-warning'>等待发放</span>";
        }else{
            return "<span class='label label-primary'>发放中</span>";
        }
    }

    public function getStatusAttr($value, $data)
    {
        $now = date('Y-m-d H:i:s');
        if($now > $data['end_date']){
            return 3;   // 结束
        }else if($now < $data['start_date']){
            return 1;   // 等待
        }else{
            return 2;   // 进行
        }
    }

}