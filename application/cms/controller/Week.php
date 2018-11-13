<?php
namespace app\cms\controller;



use app\cms\model\ContentArticle;
use app\cms\model\ContentCourse;
use app\cms\model\ContentExpert;
use app\cms\model\WxWeekItemContent;
use app\cms\model\WxWeekPlan;
use app\cms\model\WxWeekPlanInvites;
use app\cms\model\WxWeekPlanItem;
use app\cms\model\WxWeekPlanJoin;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Week extends Basic
{
    /**
     * 周计划
     * @return \think\response\View
     */
    public function week()
    {
        $week = new WxWeekPlan();
        $plan_id = array();
        $plan_list = $week->order('plan_id desc')->select();
        $now_time = date('Y-m-d', time());
        //var_dump($plan_list);die;
        $this->assign('list', $plan_list);
        $this->assign('now_time', $now_time);

        return view();
    }


    /**
     * 周计划添加
     */
    public function weekAdd(Request $request)
    {
        $plan_id = $request->param('plan_id', 0);
        $time = explode('-', $request->param('start_date'));
        $plan = new WxWeekPlan();
        $plan->checkDate($time, $plan_id);    // 验证日期是否可用
        //判断页面是否提交
        $data = [
            'title' => $request->param('title'),
            'periods' => $request->param('periods'),
            'qrcode' => $request->param('qrcode'),
            'qrcode_title' => $request->param('qrcode_title'),
            'price' => $request->param('price'),
            'origin_price' => $request->param('origin_price'),
            'invites_num' => $request->param('invites_num'),
            'start_date' => date("Y-m-d", strtotime($time[0])),
            'end_date' => date("Y-m-d", strtotime($time[1])),
            'content' => $request->param('content'),
        ];

        try {
            if ($plan_id) {
                $plan->save($data, ['plan_id' => $plan_id]);
            } else {
                $plan->save($data);
            }
            responseData(1, '成功');
        } catch (\Exception $e) {
            if (strstr($e->getMessage(), 'SQLSTATE[23000]')) {
                responseData(0, '期数不能重复');
            } else {
                responseData(0, $e->getMessage());
            }
        }

    }


    /**
     * 周计划编辑
     */
    public function weekEdit(Request $request)
    {
        $plan_id = $request->param('id', 0);
        $week_info = array();
        $info = array();
        $act = '新增';
        if ($plan_id) {
            $info = WxWeekPlan::find($plan_id);
            $week_info = (new WxWeekPlan())->find($request->param('id'));
            $act = '编辑';
        }
        $periods = WxWeekPlan::max('periods') + 1;
        $info['start_date'] = date('m/d/Y', strtotime($info['start_date']));
        $info['end_date'] = date('m/d/Y', strtotime($info['end_date']));
        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('week_info', $week_info);
        $this->assign('periods', $periods);

        return view();
    }

    /**
     * 周计划删除
     */
    public function weekDel(Request $request)
    {
        $id = $request->param('id', 0);
        if ($id !== 1 && WxWeekPlan::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    /**
     * 周计划内容
     */
    public function edit(Request $request)
    {
        $week = new WxWeekPlanItem();
        $list = $week->where(['plan_id' => $request->param('id')])->select();
        $week_info = (new WxWeekPlan())->find($request->param('id'));
        $week_map = $week_info->week_map();

        foreach ($list as $v) {
            if (isset($week_map[$v->week_num])) {
                $week_map[$v->week_num]['title'] = $v->week_day_title;
            }
        }

        $this->assign('list', $list);
        $this->assign('now_time', date('Y-m-d', time()));
        $this->assign('week_info', $week_info);
        $this->assign('week_map', $week_map);

        return view();
    }

    /**
     * 计划内容保存
     */
    public function editAdd(Request $request)
    {
        $data = [

            'plan_id' => $request->param('plan_id'),
            'week_num' => $request->param('week_num'),
            'content_type' => $request->param('content_type'),
            'content_title' => $request->param('content_title'),
        ];
        $item = new WxWeekPlanItem();
        if($request->param('act') != '编辑'){
            if ($data['content_type'] == 2 && empty($request->param('content'))) {
                responseData(0, '内容不能为空');
            } else if ($item->where($data)->find()) {
                responseData(0, '标题重复');
            }
        }
        $data['week_day_title'] = $request->param('week_day_title');
        $data['points'] = $request->param('points');
        $list = $item->where(['plan_id' => $data['plan_id']])->select();

            foreach ($list as $v) {
                if ($v->week_num == $data['week_num'] &&
                    !empty($v->week_day_title) && $v->week_day_title != $data['week_day_title']
                ) {
                    responseData(0, '每日小标题必须一致');
                }
            }

        Db::startTrans();
        try {
            if($request->param('item_id')){
                $item = (new WxWeekPlanItem())->find($request->param('item_id'));
                $item->save($data);
                if ($data['content_type'] == 2) {
                    // 添加关联内容
                    $item->content->save(['content' => $request->param('content')]);
                }
            }else{
                $item->save($data);

                if ($data['content_type'] == 2) {
                    // 添加关联内容
                    $item->content()->save(['content' => $request->param('content')]);
                }
            }


            Db::commit();
            responseData(1, '成功');
        } catch (\Exception $e) {
            Db::rollback();
            responseData(0, '失败',[$e->getMessage()]);
        }

    }

    /**
     * 周计划内容编辑页
     */
    public function editEdit(Request $request)
    {
        $item_id = $request->param('id', 0);
        $plan_id = $request->param('plan_id');
        $week_info = (new WxWeekPlan())->find($plan_id);
        $week_map = $week_info->week_map();
        $info = array();
        $act = '新增';
        if ($item_id) {
            $info = WxWeekPlanItem::find($item_id);
            $act = '编辑';
        }
        $content = WxWeekItemContent::get(['item_id' => $item_id]);
        $this->assign('week_info',$week_info);
        $this->assign('plan_id',$plan_id);
        $this->assign('content',$content);
        $this->assign('week_map', $week_map);
        $this->assign('act', $act);
        $this->assign('info', $info);
        return view();
    }

    /**
     * 周计划内容删除
     */
    public function editDel(Request $request)
    {
        $item_id = $request->param('id', 0);
        if (WxWeekPlanItem::destroy($item_id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '删除失败');
        }
    }

    /**
     * 周计划报名管理
     */
    public function join()
    {
        $join = new WxWeekPlanJoin();
        $join_list = $join->join('wx_member', 'wx_week_plan_join.member_id = wx_member.member_id', 'LEFT')
            ->join('wx_week_plan', 'wx_week_plan_join.plan_id = wx_week_plan.plan_id', 'LEFT')
            ->field('wx_week_plan_join.*,wx_member.nick_name,wx_member.phone,wx_week_plan.title')
            ->select();

        $this->assign('list', $join_list);

        return view();
    }


    public function weekStatus(Request $request)
    {
        $id      = $request->param('id', 0);
        $status  = $request->param('status', 0);
        if($id !== 0 && WxWeekPlanJoin::update(['join_id'=>$id, 'status'=>$status])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }
}


