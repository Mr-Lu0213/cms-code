<?php
namespace app\cms\controller;


use app\cms\model\WxSystemMsg;

use think\Db;
use think\Log;
use think\Request;
use think\Session;

class System extends Basic
{
    /**
     * 小秘书列表
     * @return \think\response\View
     */
    public function system()
    {
        $system = new WxSystemMsg();
        $map['status'] = 1;
        $id = array();
        $system_list = $system->where($map)->order('id desc')->select();
        $this->assign('list', $system_list);
        return view();
    }


    /**
     * 小秘书添加
     */
    public function systemadd(Request $request)
    {
        $id = $request->param('id', 0);
        //判断页面是否提交
        $data = [
            'content' => $request->param('content'),
        ];

        $system = new WxSystemMsg();
        if($id){
            $system->save($data,['id' =>$id]);
        }else{
            $system->save($data);
        }
        responseData(1,'成功');
    }

    /**
     * 小秘书编辑
     */
    public function systemEdit(Request $request)
    {
        $id = $request->param('id', 0);
        $info     = array();
        $act      = '新增';
        if($id){
            $info = WxSystemMsg::get($id);
            $act  = '编辑';
        }
        $this->assign('act', $act);
        $this->assign('info', $info);

        return view();
    }

    /**
     * 状态
     */
    public function systemStatus(Request $request)
    {
        $id      = $request->param('id', 0);
        $status  = $request->param('status', 0);
        if($id !== 0 && WxSystemMsg::update(['id'=>$id, 'status'=>$status])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

}


