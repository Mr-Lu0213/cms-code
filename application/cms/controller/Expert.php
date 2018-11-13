<?php
namespace app\cms\controller;


use app\cms\model\ContentExpert;
use app\cms\model\ContentExpertApply;
use app\cms\model\BasicTags;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Expert extends Basic
{
    /**
     * 专家列表
     * @return \think\response\View
     */
    public function expert()
    {
        $expert = new ContentExpert();
        $expert_id = array();
        $expert_list = $expert->order('recommend desc')->select();
        $this->assign('list', $expert_list);
        return view();
    }

    /**
     * 专家添加
     */
    public function expertadd(Request $request)
    {

        $expert_id = $request->param('expert_id', 0);
        $tags       = empty($_REQUEST['tags']) ?[]: $_REQUEST['tags'];
        //判断页面是否提交
        $data = [
            'type' => $request->param('type'),
            'operate_type' => $request->param('operate_type'),
            'name' => $request->param('name'),
            'phone' => $request->param('phone'),
            'sex' => $request->param('sex'),
            'photo' => $request->param('photo'),
            'banner' => $request->param('banner'),
            'company' => $request->param('company'),
            'back' => $request->param('back'),
            'result' => $request->param('result'),
            'skill' => $request->param('skill'),
            'class_name' => $request->param('class_name'),
            'price' => $request->param('price'),
            'tags' => $tags,
            'custom_order_num' => $request->param('custom_order_num',0),
            'experience' => $request->param('experience'),
        ];

        $expert = new ContentExpert();
        if($data['custom_order_num']<0){
            responseData(0,'自定义订购人数不能为负数');

        }
        if($expert_id){
            $expert->save($data,['expert_id' =>$expert_id]);
        }else{
            $expert->save($data);
        }
                responseData(1,'成功');
    }

    /**
     * 专家编辑
     */
    public function expertEdit(Request $request)
    {

        $operate_type = [
            [
                'value' => 1,
                'name' => '普通专家',
                'checked' => 'checked'
            ],
            [
                'value' => 2,
                'name' => '系列课专家',
                'checked' => ''
            ],
        ];

        $sex_type = [
            [
                'value' => 1,
                'name' => '男',
                'checked' => 'checked'
            ],
            [
                'value' => 2,
                'name' => '女',
                'checked' => ''
            ],
        ];
        $expert_id = $request->param('id', 0);
        $tag_list = BasicTags::where(['level' => 1])->select();
        $info = array();
        $act = '新增';
        if ($expert_id) {
            $info = ContentExpert::get($expert_id);
            $expert_info = (new ContentExpert())->find($request->param('id'));
            $act = '编辑';
            if ($info['tags']) {
                foreach ($tag_list as $k => $v) {
                    if (in_array($v['name'], $info['tags'])) {
                        $tag_list[$k]['selected'] = 'selected';
                    }
                }
            }
                foreach ($operate_type as $k => $v) {
                    if ($v['value'] == $info['operate_type']) {
                        $operate_type[$k]['checked'] = 'checked';
                    } else {
                        $operate_type[$k]['checked'] = '';
                    }
                }
                foreach ($sex_type as $k => $v) {
                    if ($v['value'] == $info['sex']) {
                        $sex_type[$k]['checked'] = 'checked';
                    } else {
                        $sex_type[$k]['checked'] = '';
                  }
            }
        }


        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('tag_list', $tag_list);
        $this->assign('expert_info', $expert_info);
        $this->assign('operate_type', $operate_type);
        $this->assign('sex_type', $sex_type);
        return view();

    }


    /**
     * 状态
     */
    public function expertStatus(Request $request)
    {
        $id      = $request->param('id', 0);
        $status  = $request->param('status', 0);
        if($id !== 0 && ContentExpert::update(['expert_id'=>$id, 'status'=>$status])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

    public function expertRecommend(Request $request)
    {
        $id      = $request->param('id', 0);
        $recommend  = $request->param('recommend', 0);
        if($id !== 0 && ContentExpert::update(['expert_id'=>$id, 'recommend'=>$recommend])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

    /**
     * 专家申请列表
     */
    public function apply()
    {
        $list = ContentExpertApply::order('id desc')->select();
        $this->assign('list', $list);
        return view();
    }

    //申请状态
    public function applyStatus(Request $request)
    {
        $id      = $request->param('id', 0);
        $status  = $request->param('status', 0);
        if($id !== 0 && ContentExpertApply::update(['id'=>$id, 'status'=>$status])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

}


