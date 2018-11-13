<?php
/**
 * 周计划
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class WxWeekPlan extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function getQrcodeUrlAttr($value, $data)
    {
        return empty($data['qrcode']) ? '' : Config::get('aliyun_oss.res_url') . $data['qrcode'];
    }

    public function getPriceAttr($value)
    {
        return sprintf("%0.2f", $value / 100);
    }

    public function setPriceAttr($value)
    {
        return $value * 100;
    }

    public function getOriginpriceAttr($value)
    {
        return sprintf("%0.2f", $value / 100);
    }

    public function setOriginpriceAttr($value)
    {
        return $value * 100;
    }

    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function setContentAttr($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * 验证日期是否可用
     * @param $date 日期数组
     */
    public function checkDate($date, $plan_id)
    {
        $starttime = strtotime($date[0]);
        $endtime   = strtotime($date[1]);

        // 必须在同一周
        /*if(date('W', $starttime) != date('W', $endtime)){
            responseData(0, '开始和结束日期必须在同一周');
        }*/

        // 必须是周一至周五
        if(date('w', $starttime) != 1 || date('w', $endtime) > 5 || date('w', $endtime) < 1){
            responseData(0, '周计划执行时间必须在周一至周五');
        }

        // 计划时间不能重叠
        if($this->where([
            'start_date' => ['elt', date('Y-m-d', $starttime)],
            'end_date'   => ['gt', date('Y-m-d', $starttime)],
            'plan_id'    => ['neq', $plan_id]
        ])->find()){
            responseData(0, '该时间段内已有其他计划，请勿重复添加');
        }

    }

    /**
     * 计划执行长度（天）
     */
    public function getWeekDateAttr($value, $data)
    {
        return (strtotime($data['end_date']) - strtotime($data['start_date'])) / 86400 + 1;
    }

    public function week_map()
    {
        $map = [];
        for($i=1; $i<=$this->week_date; $i++)
        {
            $map[$i] = ['name'=>"第{$i}天", 'title'=>''];
        }
        return $map;
    }
}

