<?php
namespace app\cms\controller;


use app\cms\model\BasicContentClass;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Dict extends Basic
{
    /**
     * 内容列表
     * @return \think\response\View
     */
    public function dict()
    {
        $dict = new BasicContentClass();
        $class_id = array();
        $list = $dict->order('seq asc,class_id desc')->select();
        $this->assign('list',$list);
        return view();
    }

    /**
     * 内容添加
     * @param Request $request
     */
    public function dictAdd(Request $request)
    {

        $class_id = $request->param('class_id', 0);
        //判断页面是否提交
        $data = [
            'class_name' => $request->param('class_name'),
            'parent_id' => $request->param('parent_id',0)
        ];

        $dict = new BasicContentClass();
        if(!empty($dict->where(['class_name' => $data['class_name']])->find())){
            responseData(0,'内容重复');
        }
        if($class_id){
            $dict->save($data,['class_id' =>$class_id]);
        }else{
            $name_array = explode(',', str_replace('，', ',', trim($data['class_name'])));
            $dataList = [];
            foreach ($name_array as $v){
                $dataList[] = [
                    'class_name' => $v,
                    'parent_id' => $request->param('parent_id',0),
                    'class_level' => 1
                ];
            }
            $dict->saveAll($dataList);
        }
        responseData(1,'成功');
    }

    /**
     * 标签编辑
     */
    public function dictEdit(Request $request)
    {
        $class_id       = $request->param('id', 0);
        $info          = array();
        $act           = '新增';
        if($class_id){
            $info      = BasicContentClass::find($class_id);
            $parent_id = $info->parent_id;
            $act       = '编辑';
        }else{
            $parent_id = $request->param('parent_id', 0);
        }

        if($parent_id == 0 && $class_id == 0){
            $readonly = true;
        }
        if($request->param('is_update')){
            $readonly = true;
        }
        $parent_list = BasicContentClass::field('class_name,class_id')->select();
        foreach ($parent_list as $k=>$v) {
            if($parent_id == $v['class_id']){
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


    /**
     * 标签保存
     */
    public function dictSave(Request $request)
    {
        $class_id           = $request->param('class_id', 0);
        $data['class_name']      = $request->param('class_name');
        $data['parent_id'] = $request->param('parent_id', 0);
        $menu = new BasicContentClass();
        if($class_id){
            $menu->save($data, ['class_id'=>$class_id]);
        }else{
            $menu->data($data)->save();
        }
        responseData(1,'成功');
    }

    public function dictDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if(BasicContentClass::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    public function dictSeq(Request $request)
    {
        $id = $request->param('id', 0);
        $seq = $request->param('seq', 0);
        if($seq < 0){
            responseData(0,'排序值不可小于0');
        }
        if ($id !== 0 && BasicContentClass::update(['class_id' => $id, 'seq' => $seq])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

}
