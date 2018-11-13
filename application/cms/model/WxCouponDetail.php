<?php
/**
 * Created by qizhao
 * Date: 2018/7/3
 * Time: 17:16
 */

namespace app\cms\model;


use think\Log;
use think\Model;

class WxCouponDetail extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 批量发放
     * @param $data
     * @param $coupon_id
     * @return array|bool
     */
    public static function importData($data, $coupon_id)
    {
        if(!$coupon = (new WxCoupon())->where(['coupon_id'=>$coupon_id, 'scene'=>3])->find()){
            return false;
        }
        $memberModel = new WxMember();
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', time() + 86400 * $coupon->duration);
        $result = [
            'success' => 0,
            'error' => 0
        ];
        foreach ($data as $v){
            try{
                $phone = trim($v[0]);
                if(!$phone){
                    continue;
                }
                $member_id = $memberModel->where(['phone'=>$phone])->value('member_id');
                $data = [
                    'coupon_id' => $coupon_id,
                    'member_id' => $member_id,
                    'start_time'=> $start_time,
                    'end_time'  => $end_time
                ];
                self::create($data);
                $result['success'] ++;

            }catch (\Exception $e){
                $result['error'] ++;
                Log::error('优惠券导入出错-'.$e->getMessage());
            }
        }
        return $result;
    }

}