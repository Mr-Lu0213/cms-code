<?php
namespace app\cms\controller;


use app\cms\model\ContentExpert;
use app\cms\model\PcExpert;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class PcExperts extends Basic
{
    /**
     * 专家展示列表
     * @return \think\response\View
     */
    public function pcexpert()
    {
        $pcexpert = new PcExpert();
        $id = array();
        $where = [
            'status' => 3,
            'operate_type' => 2,

        ];
        $pcexpert_list = $pcexpert->with([
            'expert'=>function($query) use($where){
                $query->where($where);
        }])->select();

        $this->assign('pcexpert_list', $pcexpert_list);
        return view();
    }

    /**
     * 专家添加
     */
    public function pcexpertadd(Request $request)
    {
        $expert = new ContentExpert();
        $pcexpert = (new PcExpert())->select();
        $expert_id = '';
        foreach ($pcexpert as $key => $value) {
            $expert_id .= ','.$value['expert_id'];
        }
        $expert_id = trim($expert_id, ',');
        $where = [
            'status' => 3,
            'operate_type' => 2,
            'expert_id' => ['not in', $expert_id]
        ];


        $expert_list = $expert->where($where)->field('expert_id,name,photo,price')->select();
        $this->assign('expert_list', $expert_list);

        return view();
    }

    /**
     * 专家编辑
     */
    public function pcexpertEdit(Request $request)
    {

        $id = $request->param('id', 0);

        $info = array();
        $act = '新增';
        if ($id) {
            $info = PcExpert::get($id);
            $pcexpert_info = (new PcExpert())->find($request->param('id'));
            $act = '编辑';

            $this->assign('act', $act);
            $this->assign('info', $info);
            $this->assign('pcexpert_info', $pcexpert_info);

            return view();

        }
    }

    public function pcexpertDel(Request $request)
    {
        $id = $request->param('id', 0);
        if (PcExpert::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '删除失败');
        }
    }

    public function Save(Request $request)
    {
        $id = $request->param('id', 0);
        $data = ['expert_id' => $id];
        $pcexpert = new PcExpert();
        $num = $pcexpert->count();
        if ($num >= 4) {
            responseData(0, '添加失败，最多4个专家');
        }
        $res = $pcexpert->save($data);
        if($res){
            responseData(1,'成功');
        }
    }
}



