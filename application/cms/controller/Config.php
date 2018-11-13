<?php
namespace app\cms\controller;


use app\cms\model\WxConfig;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Config extends Basic
{
    /**
     * 自定义配置列表
     * @return \think\response\View
     */
    public function config()
    {
        $config = new WxConfig();
        $id = array();
        $list = $config->order('status desc')->select();
        $this->assign('list', $list);
        return view();
    }


    /**
     * 自定义配置添加
     */
    public function configadd(Request $request)
    {
        $id = $request->param('id', 0);
        //判断页面是否提交
        $data = [
            'name' => $request->param('name'),
            'key' => $request->param('key'),
            'value' => $request->param('value'),
        ];

        $config = new WxConfig();
        if($id){
            $config->save($data,['id' =>$id]);
        }else{
            $config->save($data);
        }

        responseData(1,'成功');
    }

    /**
     * 自定义配置编辑
     */
    public function configEdit(Request $request)
    {
        $id = $request->param('id', 0);
        $config_info = (new WxConfig())->find($request->param('id'));
        $info     = array();
        $act      = '新增';
        if($id){
            $info = WxConfig::find($id);
            $act  = '编辑';
        }
        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('config_info', $config_info);

        return view();
    }
}


