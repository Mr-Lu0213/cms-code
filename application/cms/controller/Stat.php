<?php
namespace app\cms\controller;


use app\cms\model\StatWxPage;
use app\cms\model\StatWxLogin;
use app\cms\model\WxOrder;

use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Stat extends Basic
{
    /**
     * 数据概况
     * @return \think\response\View
     */
    public function normal()
    {
        $stat = new StatWxPage();
        $everyday = date('Y-m-d',time());
        $time = strtotime($everyday);
        $list = $stat->where(['page_type'=>1])->where('created_time', '>=', $time)->count();
        $yesterday_time = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday = $stat->where(['page_type'=>1])
                   ->where("created_time >= $yesterday_time AND created_time <= $time")->count();

        $login = new StatWxLogin();
        $everyday = date('Y-m-d',time());
        $time = strtotime($everyday);
        $login_list = $login->where('created_time', '>=', $time)->group('member_id')->count();
        $yesterday_time = strtotime(date("Y-m-d",strtotime("-1 day")));
        $login_yesterday = $login->where("created_time >= $yesterday_time AND created_time <= $time")->count();

        $order = new WxOrder();
        $time = date('Y-m-d',time());
        $order_list = $order->where(['status'=>2])->where('created_time', '>=', $time)->count();
        $yesterday_time = date("Y-m-d",strtotime("-1 day"));
        $order_yesterday = $order->where("created_time >= $yesterday_time AND created_time <= $time")->count();


        $monery_list = $order->where(['status'=>2])->where('created_time', '>=', $time)->sum('money');
        $monery_yesterday = $order->where(['status'=>2])
                          ->where("created_time >= $yesterday_time AND created_time <= $time")->sum('money');
        $monery_list = sprintf("%0.2f", $monery_list/100);
        $monery_yesterday = sprintf("%0.2f", $monery_yesterday/100);

        $total_list = $stat->where(['page_type'=>1])->count();
        $total_login = $stat->group('member_id')->count();
        $total = ceil($total_list/$total_login);

        $times_stat = $stat->where(['page_type'=>1])->sum('visit_time');
        $times = $times_stat/$total_login.'s';


        $volume_list = $order->field('service_id')->where(['type'=>4,'status'=>2])
            ->group('service_id')->limit(10)->select();

        $this->assign('list', $list);
        $this->assign('yesterday', $yesterday);
        $this->assign('login_list', $login_list);
        $this->assign('login_yesterday', $login_yesterday);
        $this->assign('order_list', $order_list);
        $this->assign('order_yesterday', $order_yesterday);
        $this->assign('monery_list', $monery_list);
        $this->assign('monery_yesterday', $monery_yesterday);
        $this->assign('total', $total);
        $this->assign('times', $times);
        $this->assign('volume_list', $volume_list);

        return view();
    }

}


