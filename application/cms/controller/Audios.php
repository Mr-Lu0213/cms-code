<?php
namespace app\cms\controller;


use app\cms\model\ContentCourse;
use app\cms\model\ContentCourseMaterial;
use app\cms\model\WxAudioRecommend;

use think\Db;
use think\Log;
use think\Request;
use think\Session;


class Audios extends Basic
{
    /**
     * 推荐音频列表
     * @return \think\response\View
     */
    public function audios()
    {
        $audios = new WxAudioRecommend();
        $id = array();
        $where = [

        ];
        $audios_list = $audios->with([
            'course'=>function($query) use($where){
                $query->where($where);
            }])->select();

        $this->assign('audios_list', $audios_list);
        return view();
    }

    /**
     * 音频添加
     */
    public function audiosadd(Request $request)
    {
        $course = new ContentCourseMaterial();
        $ma_id = (new WxAudioRecommend())->column('ma_id');
        $where = [
            'ma_id' => ['not in', $ma_id],
            'trial'=>1
        ];

        $audios_list = $course->hasWhere(
            'course',[
                'type'=>2,
                'status' =>1,

            ]

        )->with(['course' => function($query){
            $query->field('course_id, charge');
        }])->where($where)->field('ma_id,ma_title,ma_url,trial')->select();

        $audios_list = $audios_list->toArray();
        foreach ($audios_list as $k=>$v){
            if($v['course']['charge'] == 1 && $v['trial'] == 0){
                unset($audios_list[$k]);
            }
        }
        $this->assign('audios_list', $audios_list);

        return view();
    }

    /**
     * 音频编辑
     */
    public function audiosEdit(Request $request)
    {

        $id = $request->param('id', 0);

        $info = array();
        $act = '新增';
        if ($id) {
            $info = WxAudioRecommend::get($id);
            $audios_info = (new WxAudioRecommend())->find($request->param('id'));
            $act = '编辑';

            $this->assign('act', $act);
            $this->assign('info', $info);
            $this->assign('audios_info', $audios_info);

            return view();


        }
    }

    public function audiosDel(Request $request)
    {
        $id = $request->param('id', 0);
        if (WxAudioRecommend::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '删除失败');
        }
    }

    public function Save(Request $request)
    {
        $id = $request->param('id', 0);
        $data = ['ma_id' => $id];
        $audios = new WxAudioRecommend();

        $res = $audios->save($data);
        if($res){
            responseData(1,'成功');
        }
    }
}