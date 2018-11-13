<?php
/**
 * Created by qizhao
 * Date: 2018/7/3
 * Time: 16:49
 */

namespace app\cms\controller;


use app\cms\model\ContentCourse;
use app\cms\model\ContentExpert;
use app\cms\model\WxCoupon;
use app\cms\model\WxCouponDetail;
use app\cms\model\WxCouponItem;
use think\Log;
use think\Request;

class Coupon extends Basic
{
    /**
     * 列表页
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request)
    {
        $list = WxCoupon::order('coupon_id desc')->select();
        $this->assign('list', $list);
        return view();
    }

    /**
     * 新增/编辑 页面
     * @param Request $request
     * @return \think\response\View     */
    public function edit(Request $request)
    {
        $act        = '新增';
        $info       = [];
        $type_map   = [1=>'满减', 2=>'代金券'];
        $scene_map  = [1=>'用户注册', 2=>'营销活动', 3=>'定向优惠券'];
        $target_map = [0=>'全部', 1=>'课程', 2=>'专家', 3=>'周计划', 9=>'指定商品'];
        $courses    = ContentCourse::where(['status'=>1, 'operate_type'=>1])
                    ->field('course_id, title')->select();
        $experts    = ContentExpert::where(['status'=>1, 'operate_type'=>2])
                    ->field('expert_id, name')->select();

        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('type_map', $type_map);
        $this->assign('scene_map', $scene_map);
        $this->assign('target_map', $target_map);
        $this->assign('courses', $courses);
        $this->assign('experts', $experts);

        return view();
    }

    /**
     * 保存
     * @param Request $request
     */
    public function save(Request $request)
    {
        $courses       = empty($_REQUEST['courses']) ?[]: $_REQUEST['courses'];
        $experts       = empty($_REQUEST['experts']) ?[]: $_REQUEST['experts'];
        $data = [
            'type'        => $request->param('type'),
            'scene'       => $request->param('scene'),
            'target'      => $request->param('target'),
            'name'        => $request->param('name'),
            'amount'      => $request->param('amount'),
            'reach_amount'=> $request->param('reach_amount', 0),
            'duration'    => $request->param('duration'),
        ];

        if($data['type'] == 1){
            if(empty($data['reach_amount'])){
                responseData(0, '满减达标金额不能为空');
            }else if($data['reach_amount'] < $data['amount']){
                responseData(0, '满减达标金额不能小于优惠金额');
            }
        }
        if($data['duration'] < 1){
            responseData(0, '有效期不在允许范围内');
        }
        if($data['target'] == 3 && empty($courses) && empty($packages)){
            responseData(0, '请选择可用产品');
        }

        $coupon_date = explode('-', $request->param('coupon_date'));
        if(empty($coupon_date[1])){
            responseData(0, '发放日期不能为空');
        }
        $data['start_date'] = date('Y-m-d', strtotime($coupon_date[0]));
        $data['end_date']   = date('Y-m-d', strtotime($coupon_date[1]));

        try{
            $model = new WxCoupon();
            $model->data($data)->save();
            if($model->target == 9){
                $itemList = [];
                foreach ($courses as $v){
                    $itemList[] = [
                        'coupon_id' => $model->coupon_id,
                        'type'      => 1,
                        'item_id'   => $v
                    ];
                }
                foreach ($experts as $v){
                    $itemList[] = [
                        'coupon_id' => $model->coupon_id,
                        'type'      => 2,
                        'item_id'   => $v
                    ];
                }
                (new WxCouponItem())->saveAll($itemList);
            }
            responseData(1,'成功');
        }catch (\Exception $e){
            Log::error(__FUNCTION__.' '.$e->getMessage());
            responseData(0,'失败');
        }
    }

    /**
     * 删除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $id  = $request->param('id', 0);
        if(WxCoupon::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    /**
     * 导入批开数据
     * @param Request $request
     * @return mixed
     */
    public function importdata(Request $request)
    {
        $coupon_id = $request->param('id', 0);
        if(!$coupon_id){
            responseData(0, '参数错误');
        }

        // 保存文件到本地
        $file = $request->file('item');
        $info = $file->validate(['size'=>30000000,'ext'=>'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 读取xls文件内容
            $data = read_excel(ROOT_PATH.'public/uploads/' . $info->getSaveName());
            if(!empty($data)){
                $result = WxCouponDetail::importData($data, $coupon_id);
                if(false === $result){
                    responseData(0, '导入失败');
                }else{
                    $count = count($data);
                    responseData($result['success']?1:0, "总计 {$count} 条，成功导入 {$result['success']} 条");
                }

            }else{
                responseData(0, '数据为空');
            }

        }else{
            // 上传失败获取错误信息
            responseData(0,$file->getError());
        }
    }

}