<?php
namespace app\cms\controller;


use app\cms\model\ContentCourse;
use app\cms\model\PcCourse;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class PcCourses extends Basic
{
    /**
     * 精品课程列表
     * @return \think\response\View
     */
    public function pccourse()
    {
        $pccourse = new PcCourse();
        $id = array();
        $where = [
            'status' => 1,
            'operate_type' => 1,

        ];
        $pccourse_list = $pccourse->with([
            'course'=>function($query) use($where){
                $query->where($where);
            }
        ])->select();

        $this->assign('pccourse_list', $pccourse_list);
        return view();
    }

    /**
     * 课程添加
     */
    public function pccourseadd(Request $request)
    {
        $course = new ContentCourse();
        $pccourse = (new PcCourse())->select();
        $course_id = '';
        foreach ($pccourse as $key => $value) {
            $course_id .= ','.$value['course_id'];
        }
        $course_id = trim($course_id, ',');
        $where = [
            'status' => 1,
            'operate_type' => 1,
            'type' => 1,
            'course_id' => ['not in', $course_id]
        ];
        $course_list = $course->where($where)->field('course_id,title,photo,order_num')->select();
        $this->assign('course_list', $course_list);

        return view();
    }

    /**
     * 课程编辑
     */
    public function pccourseEdit(Request $request)
    {

        $id = $request->param('id', 0);

        $info = array();
        $act = '新增';
        if ($id) {
            $info = PcCourses::get($id);
            $pccourse_info = (new PcCourses())->find($request->param('id'));
            $act = '编辑';

            $this->assign('act', $act);
            $this->assign('info', $info);
            $this->assign('pccourse_info', $pccourse_info);

            return view();

        }
    }

    public function pccourseDel(Request $request)
    {
        $id = $request->param('id', 0);
        if (PcCourse::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '删除失败');
        }
    }

    public function Save(Request $request)
    {
        $id = $request->param('id', 0);
        $data = ['course_id' => $id];
        $pccourse = new PcCourse();
        $num = $pccourse->count();
        if ($num >= 8) {
            responseData(0, '添加失败，最多上传8个课程');
        }
        $res = $pccourse->save($data);
        if($res){
            responseData(1,'成功');
        }

    }
}