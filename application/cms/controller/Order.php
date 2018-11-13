<?php

namespace app\cms\controller;

use app\cms\model\WxMember;
use app\cms\model\WxOrder;
use app\cms\model\WxWeekOrder;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Order extends Basic
{
    /**
     * 订单列表
     * @return \think\response\View
     */
    public function order()
    {
//        $order = new WxOrder();
//        $list = $order->order('status desc')->select();
//
//        foreach ($list as $key => $value) {
//            $list[$key]['nick_name'] = $order->member()->where('member_id='.$value['member_id'])
//                                        ->value('nick_name');
//            $list[$key]['phone'] = $order->member()->where('member_id=' . $value['member_id'])
//                                        ->value('phone');
//            if ($value['type'] == 1) {
//                $list[$key]['relevance'] = $order->course()->where('course_id='.$value['service_id'])
//                                         ->value('title');
//            } elseif ($value['type'] == 2) {
//                $list[$key]['relevance'] = $order->expert()->where('expert_id='.$value['service_id'])
//                                         ->value('name');
//            } else {
//                $list[$key]['relevance'] = $order->plan()->where('plan_id='.$value['service_id'])
//                                         ->value('title');
//            }
//        }
//
//        foreach ($list as $key => $value) {
//            foreach ($value['relevance'] as $item => $val) {
//                foreach ($val as $k => $v) {
//                    $value['relevance'] = $v;
//                }
//            }
//        }
//
//        $this->assign('list',$list);

        return view();
    }

    /**
     * ajax 分页数据源
     * @param Request $request
     */
    public function ajaxData(Request $request)
    {
        $where = [];
        $start = $request->param('start', 0);
        $length= $request->param('length', 10);
        $draw  = $request->param('draw');
        $search = $request->param('search.value');
        if($search){
            $where['phone'] = ['like', '%'.$search.'%'];
        }

        $query = WxOrder::hasWhere('member',$where)
                ->with(['member' => function ($query) {
                $query->field('member_id,phone,nick_name');
            }]);


        // 条件过滤后记录数 必要
        $recordsFiltered = $query->count();
        // 表的总记录数 必要
        $recordsTotal = WxOrder::count();
        // 条件查询记录
        $list =$query->order('order_id desc')->limit($start, $length)->select();


        $order = new WxOrder();
        foreach ($list as $key=>$value){
            if ($value['type'] == 1) {
                $list[$key]['relevance'] = $order->course()->where('course_id='.$value['service_id'])
                                         ->value('title');
            } elseif ($value['type'] == 2) {
                $list[$key]['relevance'] = $order->expert()->where('expert_id='.$value['service_id'])
                                         ->value('name');
            } else {
                $list[$key]['relevance'] = $order->plan()->where('plan_id='.$value['service_id'])
                                         ->value('title');
            }
            $list[$key]['phone'] = $value['member']['phone'];
            $list[$key]['nick_name'] = $value['member']['nick_name'];
            $list[$key]['type_text'] = $value['type_text'];
            $list[$key]['pay_text'] = $value['pay_text'];
            $list[$key]['status_text'] = $value['status_text'];

        }

        $result = [
            "draw" => intval($draw),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $list,
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }


}


