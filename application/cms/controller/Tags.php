<?php
namespace app\cms\controller;


use app\cms\model\BasicTags;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Tags extends Basic
{
    /**
     * 标签列表
     * @return \think\response\View
     */
    public function tags()
    {
        $tag = new BasicTags();
        $tag_id = array();
        $list = $tag->getList();
        $this->assign('list',$list);
        return view();
    }

    /**
     * 标签添加
     * @param Request $request
     */
    public function tagsAdd(Request $request)
    {

        $tag_id = $request->param('tag_id', 0);
        //判断页面是否提交
        $data = [
            'name' => $request->param('name'),
            'parent_id' => $request->param('parent_id',0),
        ];

        $tag = new BasicTags();
        if(!empty($tag->where(['name' => $data['name']])->find())){
            responseData(0,'标签重复');
        }
        if($tag_id){
            $tag->save($data,['tag_id' =>$tag_id]);
        }else{
            $test = explode(',', str_replace('，', ',', trim($data['name'])));
            $dataList = [];
            foreach ($test as $v){
                $dataList[] = [
                    'name' => $v,
                    'parent_id' => $request->param('parent_id',0),

                ];

            }

            $tag->saveAll($dataList);

        }
        responseData(1,'成功');
    }

    /**
     * 标签编辑
     */
    public function tagsEdit(Request $request)
    {
        $tag_id       = $request->param('id', 0);
        $info          = array();
        $act           = '新增';
        if($tag_id){
            $info      = BasicTags::find($tag_id);
            $parent_id = $info->parent_id;
            $act       = '编辑';
        }else{
            $parent_id = $request->param('parent_id', 0);
        }

        if($parent_id == 0 && $tag_id == 0){
            $readonly = true;
        }
        if($request->param('is_update')){
            $readonly = true;
        }
        $parent_list = BasicTags::where(['level'=>0])->field('name,tag_id')->select();
        foreach ($parent_list as $k=>$v) {
            if($parent_id == $v['tag_id']){
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
    public function TagsSave(Request $request)
    {
        $tag_id           = $request->param('tag_id', 0);
        $data['name']      = $request->param('name');
        $data['parent_id'] = $request->param('parent_id', 0);
        $menu = new BasicTags();
        if($tag_id){
            $menu->save($data, ['tag_id'=>$tag_id]);
        }else{
            $menu->data($data)->save();
        }
        responseData(1,'成功');
    }

    public function TagsRecommend(Request $request)
    {
        $id      = $request->param('id', 0);
        $recommend  = $request->param('recommend', 0);
        if($id !== 0 && BasicTags::update(['tag_id'=>$id, 'recommend'=>$recommend])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

        public function tagsDel(Request $request)
    {
        $id  = $request->param('id', 0);
        $ids = BasicTags::where('parent_id',$id)->column('tag_id');
        $ids[] = $id;
        if(BasicTags::destroy($ids)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

}
