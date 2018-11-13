<?php
namespace app\cms\controller;


use app\cms\model\WxMember;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Member extends Basic
{
    /**
     * 用户管理
     * @return \think\response\View
     */
    public function member()
    {
        /*$member = new WxMember();
        $member_id = array();
        $member_list = $member->order('member_id desc')->select();
        $this->assign('list', $member_list);*/
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
            $where['nick_name|phone'] = ['like', '%'.$search.'%'];
        }

        // 条件过滤后记录数 必要
        $recordsFiltered = WxMember::where($where)->count();
        // 表的总记录数 必要
        $recordsTotal = WxMember::count();
        // 条件查询记录
        $list = WxMember::where($where)
            ->field('member_id, nick_name, photo, phone, balance, created_time, source, sex')
            ->order('member_id desc')
            ->limit($start, $length)->select();
        foreach ($list as $k=>$v){
            $list[$k]['photo_url'] = $v['photo_url'];
            $list[$k]['source_text'] = $v['source_text'];
            $list[$k]['sex_text'] = $v['sex_text'];
        }

        $result = [
            "draw" => intval($draw),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $list
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 用户添加
     */
    public function memberadd(Request $request)
    {
        $member_id = $request->param('member_id', 0);
        //判断页面是否提交
        $data = [
            'nick_name' => $request->param('nick_name'),
            'sex' => $request->param('sex'),
            'photo' => $request->param('photo'),
            'phone' => $request->param('phone'),
            'source' => $request->param('source'),
            'balance' => $request->param('balance'),
        ];

        $member = new WxMember();
        if($member_id){
            $member->save($data,['member_id' =>$member_id]);
        }else{
            $member->save($data);
        }
        responseData(1,'成功');
    }

    /**
     * 用户管理编辑
     */
    public function memberEdit(Request $request)
    {
        $member_id = $request->param('id', 0);
        $info = array();
        $act = '新增';
        if ($member_id) {
            $info = WxMember::find($member_id);
            $act = '编辑';
        }
        $this->assign('act', $act);
        $this->assign('info', $info);

        return view();
    }

}


