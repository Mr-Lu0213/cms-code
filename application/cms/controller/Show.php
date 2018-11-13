<?php
namespace app\cms\controller;


use app\cms\model\PcShow;
use app\cms\model\BasicTags;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Show extends Basic
{
    /**
     * 轮播列表
     * @return \think\response\View
     */
    public function show()
    {
        $show = new PcShow();
        $show_id = array();
        $status = 1;
        $show_list  = $show->order('status desc')->select();
        $this->assign('list', $show_list);
          return view();

    }

    /**
     * 轮播添加
     */
    public function showadd(Request $request)
    {
        $id = $request->param('id', 0);

        //判断页面是否提交
        $data = [
            'type' => $request->param('type'),
            'status' => $request->param('status',0),
            'storage' => $request->param('storage'),

        ];

        $show = new PcShow();

        if ($id) {
           $show->save($data, ['id' => $id]);
        } else {
           $show->save($data);
        }
        responseData(1,'成功');
    }
    /**
     * 轮播图编辑
     */
    public function showEdit(Request $request)
    {
        $type = [
            [
                'value'  => 1,
                'name'   => '视频',
                'checked' => 'checked'
            ],
            [
                'value'  => 2,
                'name'   => '图片',
                'checked' => ''
            ],
        ];
        $id = $request->param('id', 0);
        $act      = '新增';
        if ($id) {
            $info = PcShow::find($id);
            $act = '编辑';
        }
        foreach ($type as $k=>$v){
            if($v['value'] == $info['type']){
                $source_type[$k]['checked'] = 'checked';
            }else{
                $source_type[$k]['checked'] = '';
            }
        }
        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('type', $type);

        return view();
    }

    /**
     * 删除
     */
    public function showDel(Request $request)
    {
        $id = $request->param('id', 0);
        if ($id !== 1 && PcShow::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    /**
     * 状态
     */
    public function showStatus(Request $request)
    {
        $id  = $request->param('id', 0);
        $res = (new PcShow())->where('id ='.$id)->update(['status' => 1]);
        $result = (new PcShow())->where('id !='.$id)->update(['status' => 0]);
        if($res && $result){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }

    }
}


