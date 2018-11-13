<?php

namespace app\cms\controller;


use app\cms\model\BasicTags;
use app\cms\model\ContentCourseMaterial;
use app\cms\model\WxAudioColumn;
use app\cms\model\ContentCourse;
use app\cms\model\ContentExpert;

use app\cms\model\WxAudioColumnItem;
use app\cms\model\WxAudioRecommend;
use app\cms\model\WxColumnItem;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Audio extends Basic
{
    /**
     * 音频列表
     * @return \think\response\View
     */
    public function audio(Request $request)
    {

        $audio = new WxAudioColumn();
        $audio_id = array();

        $list = $audio->getList();
        $this->assign('list',$list);

        return view();
    }

    /**
     * 音频添加
     */
    public function audioadd(Request $request)
    {
        $column_id = $request->param('column_id', 0);
        //判断页面是否提交
        $data = [
            'icon' => $request->param('icon_url'),
            'name' => $request->param('name'),
            'parent_id' => $request->param('parent_id',0),
        ];
        $column = new WxAudioColumn();

        if($column_id){
            $column->save($data,['column_id' =>$column_id]);
        }else{
            $test = explode(',', str_replace('，', ',', trim($data['name'])));
            $dataList = [];
            foreach ($test as $v){
                $dataList[] = [
                    'name' => $v,
                    'icon' => $request->param('icon_url'),
                    'parent_id' => $request->param('parent_id',0)
                ];
            }

            $column->saveAll($dataList);
        }
        responseData(1, '成功');
    }


    /**
     * 音频编辑
     */
    public function audioEdit(Request $request)
    {
        $column_id       = $request->param('id', 0);
        $info          = array();
        $act           = '新增';
        if($column_id){
            $info      = WxAudioColumn::find($column_id);
            $parent_id = $info->parent_id;
            $act       = '编辑';
        }else{
            $parent_id = $request->param('parent_id', 0);
        }

        if($parent_id == 0 && $column_id == 0){
            $readonly = true;
        }
        if($request->param('is_update')){
            $readonly = true;
        }
        $parent_list = WxAudioColumn::where(['level'=>0])->field('name,icon,column_id')->select();
        foreach ($parent_list as $k=>$v) {
            if($parent_id == $v['column_id']){
                $parent_list[$k]['active'] = 1;
            }else{
                $parent_list[$k]['active'] = 0;
            }
        }
        $this->assign('readonly' ,$readonly);
        $this->assign('parent_list', $parent_list);
        $this->assign('info', $info);
        $this->assign('act', $act);

        return view();
    }

    public function audios(Request $request)
    {
        $column_id       = $request->param('id', 0);
        $info          = array();
        $act           = '新增';
        if($column_id){
            $info      = WxAudioColumn::find($column_id);
            $parent_id = $info->parent_id;
            $act       = '编辑';
        }else{
            $parent_id = $request->param('parent_id', 0);
        }

        if($parent_id == 0 && $column_id == 0){
            $readonly = true;
        }
        if($request->param('is_update')){
            $readonly = true;
        }
        $parent_list = WxAudioColumn::where(['level'=>0])->field('name,icon,column_id')->select();

        foreach ($parent_list as $k=>$v) {
            if($parent_id == $v['column_id']){
                $parent_list[$k]['active'] = 1;
            }else{
                $parent_list[$k]['active'] = 0;
            }
        }
        $this->assign('readonly' ,$readonly);
        $this->assign('parent_list', $parent_list);
        $this->assign('info', $info);
        $this->assign('act', $act);
        return view();
    }

    public function audioDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if(WxAudioColumn::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    public function edit(Request $request)
    {
        $list_id=array();
        $id = $request->param('id',0);
        $audio_info = (new WxAudioColumn())->find($request->param('id'));
        $columnItem = new WxAudioColumnItem();
        $column_item_list = $columnItem->where(['column_id' => $id])->order('seq asc,column_id desc')->select()->toArray();
        foreach($column_item_list as $value){
            array_push($list_id,$value['content_id']);
        }
        $data['name'] = '名字';
        $data['column_id'] = $id;
        $course = new ContentCourse();
        $where=[
            'type' => 2,
            'status' => 1,
            'course_id' => ['not in',$list_id]
        ];

        $list = $course->where($where)->field('course_id id,title')->select()->toArray();

        foreach ($column_item_list as $key => $value) {
            $column_item_list[$key]['name'] = $columnItem->audio()->where('column_id =' . $value['column_id'])
                    ->value('name');
            $column_item_list[$key]['title'] = $columnItem->course()->where('course_id =' . $value['content_id'])
                    ->value('title');
            $column_item_list[$key]['status'] = $columnItem->course()->where('course_id =' . $value['content_id'])
                ->value('status');
        }
        $this->assign('column_item_list',$column_item_list);
        $this->assign('data',$data);
        $this->assign('list', $list);
        $this->assign('audio_info', $audio_info);

        return view();
    }

    public function editadd(Request $request)
    {
        $data['content_id'] = $request->param('content_id');
        $data['column_id'] = $request->param('column_id');
        $columnItem = new WxAudioColumnItem();
        $res = WxAudioColumnItem::get($data);

        try{
            $columnItem->save($data);
            responseData(1, '成功');
        }catch (\Exception $e){
            responseData(0, '失败');
        }
    }

    public function editDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if(WxAudioColumnItem::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    /**
     * 状态
     */
    public function audioStatus(Request $request)
    {
        $id = $request->param('id', 0);
        $status = $request->param('status', 0);
        if ($id !== 0 && WxAudioColumn::update(['column_id' => $id, 'status' => $status])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    public function audioSeq(Request $request)
    {
        $id = $request->param('id', 0);
        $seq = $request->param('seq', 0);
        if($seq < 0){
            responseData(0,'排序值不可小于0');
        }
        if ($id !== 0 && WxAudioColumn::update(['column_id' => $id, 'seq' => $seq])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    public function audioRecommend(Request $request)
    {
        $id      = $request->param('id', 0);
        $recommend  = $request->param('recommend', 0);
        if($id !== 0 && WxAudioColumn::update(['column_id'=>$id, 'recommend'=>$recommend])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }


}


