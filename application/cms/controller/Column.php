<?php

namespace app\cms\controller;


use app\cms\model\BasicTags;
use app\cms\model\WxColumn;
use app\cms\model\ContentCourse;
use app\cms\model\ContentExpert;

use app\cms\model\WxColumnItem;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Column extends Basic
{
    /**
     * 微课堂列表
     * @return \think\response\View
     */
    public function column(Request $request)
    {

        $column = new WxColumn();
        $column_list = $column->order('seq asc,column_id desc')->select();
        $this->assign('list', $column_list);
        return view();
    }

    /**
     * 微课堂添加
     */
    public function columnadd(Request $request)
    {
        $column_id = $request->param('column_id', 0);
        //判断页面是否提交
        $data = [
            'type' => $request->param('type'),
            'title' => $request->param('title'),
            'seq' => $request->param('seq'),

        ];

        $column = new WxColumn();

        if ($column_id) {
            $column->save($data, ['column_id' => $column_id]);
        } else {
            $column->save($data);
        }

        responseData(1, '成功');
    }

    /**
     * 微课堂编辑
     */
    public function columnEdit(Request $request)
    {
        $column_type = [
            [
                'value'  => 1,
                'name'   => '视频课',
                'checked' => 'checked'
            ],
            [
                'value'  => 2,
                'name'   => '专家',
                'checked' => ''
            ],
        ];
        $column_id = $request->param('id', 0);
        $info = array();
        $act = '新增';
        if ($column_id) {
            $info = WxColumn::get($column_id);
            $act = '编辑';
            foreach ($column_type as $k=>$v){
                if($v['value'] == $info['type']){
                    $column_type[$k]['checked'] = 'checked';
                }else{
                    $column_type[$k]['checked'] = '';
                }
            }
        }

        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('column_type' ,$column_type);

        return view();
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');
        $column = new WxColumn();
        $columnItem = new WxColumnItem();
        $column_type = $column->where('column_id =' . $id)->field('type')->select()->toArray();
        $column_item_list = $columnItem->where(['column_id' => $id])->order('id desc')->select();

        if ($column_type[0]['type'] == 1) {
            $data['name'] = '标题';
            $data['type'] = 1;
            $data['column_id'] = $id;
            $course = new ContentCourse();
            $list = $course->where(['operate_type' => 1,'type' => 1, 'status' => 1])->field('course_id id,title')->select();
            foreach ($column_item_list as $key => $value) {
                $column_item_list[$key]['title'] = $columnItem->column()->where('column_id =' . $value['column_id'])
                    ->value('title');
                $column_item_list[$key]['content'] = $columnItem->course()->where('course_id =' . $value['content_id'])
                    ->value('title');
                $column_item_list[$key]['status'] = $columnItem->course()->where(['course_id' => $value['content_id']])
                    ->value('status');
            }
        } else{$data['name'] = '名字';
            $data['type'] = 2;
            $data['column_id'] = $id;
            $expert = new ContentExpert();
            $list = $expert->where(['operate_type'=>2,'status' => 3])->field('expert_id id,name title')->select();
            foreach ($column_item_list as $key => $value) {
                $column_item_list[$key]['title'] = $columnItem->column()->where('column_id =' . $value['column_id'])
                    ->value('title');
                $column_item_list[$key]['content'] = $columnItem->expert()->where('expert_id =' . $value['content_id'])
                    ->value('name');
                $column_item_list[$key]['status'] = $columnItem->expert()->where(['expert_id' => $value['content_id']])
                    ->value('status');
            }
        }

        $this->assign('column_item_list',$column_item_list);
        $this->assign('data',$data);
        $this->assign('list', $list);
        $this->assign('column_type', $column_type);

        return view();
    }

    public function editadd(Request $request)
    {
        $data['content_id'] = $request->param('content_id');
        $data['content_type'] = $request->param('content_type');
        $data['column_id'] = $request->param('column_id');
        $columnItem = new WxColumnItem();
        $res = WxColumnItem::get($data);
        if($res){
            responseData(0,'重复');
        }
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
        if(WxColumnItem::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    /**
     * 状态
     */
    public function columnStatus(Request $request)
    {
        $id = $request->param('id', 0);
        $status = $request->param('status', 0);
        if ($id !== 0 && WxColumn::update(['column_id' => $id, 'status' => $status])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    public function columnSeq(Request $request)
    {
        $id = $request->param('id', 0);
        $seq = $request->param('seq', 0);
        if($seq < 0){
            responseData(0,'排序值不可小于0');
        }
        if ($id !== 0 && WxColumn::update(['column_id' => $id, 'seq' => $seq])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

}


