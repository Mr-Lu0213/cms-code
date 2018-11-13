<?php
/**
 *
 * Created by: zhang
 * Date Time : 2018/6/4 11:52
 */

namespace app\cms\controller;


use app\cms\model\BasicTags;
use app\cms\model\BasicContentClass;
use app\cms\model\ContentCourse;
use app\cms\model\ContentCourseMaterial;
use app\cms\model\ContentExpert;
use think\Log;
use think\Request;

class Course extends Basic
{
    /**
     * 列表页
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request)
    {
        $status_list = [
            [
                'value' => 1,
                'name' => '下线',
                'active' => 0
            ],
            [
                'value' => 2,
                'name' => '上线',
                'active' => 0
            ],
        ];

        $where = [];
        if (!empty($request->param('status'))) {
            $status = $request->param('status') - 1;
            $where['status'] = $status;
            if (isset($status_list[$status])) {
                $status_list[$status]['active'] = 1;
            }
        }
        $list = ContentCourse::where($where)
            ->order('course_id desc')->select();


        $this->assign('list', $list);
        $this->assign('status_list', $status_list);

        return view();
    }

    /**
     * 新增/编辑 页面
     * @param Request $request
     * @return \think\response\View
     */
    public function edit(Request $request)
    {
        $operate_type = [
            [
                'value' => 1,
                'name' => '普通课程',
                'checked' => 'checked'
            ],
            [
                'value' => 2,
                'name' => '专家系列课',
                'checked' => ''
            ],
        ];
        $course_type = [
            [
                'value' => 1,
                'name' => '视频课程',
                'checked' => 'checked'
            ],
            [
                'value' => 2,
                'name' => '音频课程',
                'checked' => ''
            ],
        ];
        $act = '新增';
        $info = array();
        $id = $request->param('id');
        $id = $request->param('id', 0);
        $course_info = (new ContentCourse())->find($id);
        $tag_list = BasicTags::select();
        $dict_list = BasicContentClass::where(['class_level'=>1])->select();
        $expert_a = ContentExpert::where(['operate_type' => 1, 'status' => 3])->field('expert_id, name')->select();
        $expert_b = ContentExpert::where(['operate_type' => 2, 'status' => 3])->field('expert_id, name')->select();


        if ($id) {
            $info = ContentCourse::get($id);
            $course_info = (new ContentCourse())->find($request->param('id'));
            $act = '编辑';
            if ($info['tags']) {
                foreach ($tag_list as $k => $v) {
                    if (in_array($v['name'], $info['tags'])) {
                        $tag_list[$k]['selected'] = 'selected';
                    }
                }
            }

            if($info['class_id']){
            foreach ($dict_list as $k=>$v){
                if($v['class_id']==$info['class_id']){
                    $dict_list[$k]['selected'] = 'selected';
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
            foreach ($course_type as $k => $v) {
                if ($v['value'] == $info['type']) {
                    $course_type[$k]['checked'] = 'checked';
                } else {
                    $course_type[$k]['checked'] = '';
                }
            }


        }

        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('operate_type', $operate_type);
        $this->assign('course_type', $course_type);
        $this->assign('expert_a', $expert_a);
        $this->assign('expert_b', $expert_b);
        $this->assign('tag_list', $tag_list);
        $this->assign('dict_list', $dict_list);
        $this->assign('course_info', $course_info);

        return view();
    }

    /**
     * 保存
     * @param Request $request
     */
    public function save(Request $request)
    {
        $id = $request->param('id', 0);
        $tags = empty($_REQUEST['tags']) ? [] : $_REQUEST['tags'];
        $class_id  = $request->param('class_name',0);
        $data = [
            'tags' => $tags,
            'class_id' => $class_id,
            'type' => $request->param('type', 1),
            'operate_type' => $request->param('operate_type', 1),
            'title' => $request->param('title'),
            'summary' => $request->param('summary'),
            'photo' => $request->param('photo'),
            'price' => $request->param('price', 0),
            'custom_order_num' => $request->param('custom_order_num', 0),
            'custom_play_num' => $request->param('custom_play_num', 0),
        ];
        if ($data['price'] == 0) {
            $data['charge'] = 0;
        } else {
            $data['charge'] = 1;
        }


        try {
            if ($id) {
                $course = ContentCourse::find($id);
                if ($course['operate_type'] == 1) {
                    $data['expert_id'] = $request->param('expert_a', 0);
                } else {
                    $data['expert_id'] = $request->param('expert_b', 0);
                }
                if ($data['expert_id'] == 0) {
                    responseData(0, '请选择专家');
                }
                $data['expert_name'] = ContentExpert::where(['expert_id' => $data['expert_id']])->value('name');
                $course->isUpdate(true)->save($data);
                $course->detail->save([
                    'content' => $request->param('content'),
                    'results' => $request->param('results'),
                ]);
            } else {
                $course = new ContentCourse();
                if ($data['operate_type'] == 1) {
                    $data['expert_id'] = $request->param('expert_a', 0);
                } else {
                    $data['expert_id'] = $request->param('expert_b', 0);
                }

                if ($data['expert_id'] == 0) {
                    responseData(0, '请选择专家');
                }
                $data['expert_name'] = ContentExpert::where(['expert_id' => $data['expert_id']])->value('name');
                $data['status'] = 0;    // 新增默认状态为下线
                $course->save($data);
                $course->detail()->save([
                    'content' => $request->param('content'),
                    'results' => $request->param('results'),
                ]);
            }
            responseData(1, '成功');
        } catch (\Exception $e) {
            Log::error(__FUNCTION__ . ' ' . $e->getMessage());
            if (strstr($e->getMessage(), 'SQLSTATE[23000]')) {
                responseData(0, '标题重复');
            } else {
                responseData(0, '失败');
            }

        }
    }

    /**
     * 删除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $id = $request->param('id', 0);
        if (ContentCourse::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '删除失败');
        }
    }

    /**
     * 修改状态
     * @param Request $request
     */
    public function status(Request $request)
    {
        $id = $request->param('id', 0);
        $status = $request->param('status', 0);
        if (ContentCourse::update(['course_id' => $id, 'status' => $status])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    /**
     * 推荐
     * @param Request $request
     */
    public function recommend(Request $request)
    {
        $id = $request->param('id', 0);
        $status = $request->param('recommend', 0);
        if (ContentCourse::update(['course_id' => $id, 'recommend' => $status])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }

    /**
     * 课程章节
     * @param Request $request
     * @return \think\response\View
     */
    public function material(Request $request)
    {
        $ma_info = array();
        $act = '添加';
        $course_id = $request->param('id', 0);
        $ma_id = $request->param('ma_id', 0);
        $course = ContentCourse::where(['course_id' => $course_id])->field('course_id, title, operate_type, mat_num, type')->find();
        $mat_list = ContentCourseMaterial::where(['course_id' => $course_id])
            ->order('sort asc')->select();
        if ($ma_id) {
            $act = '编辑';
            $ma_info = ContentCourseMaterial::find($ma_id);
        }

        $this->assign('act', $act);
        $this->assign('ma_info', $ma_info);
        $this->assign('info', $course);
        $this->assign('mat_list', $mat_list);

        return view();
    }

    /**
     * 章节修改/添加
     */
    public function maSave(Request $request)
    {
        $course_id = $request->param('id');
        $ma_id = $request->param('ma_id', 0);
        $title = $request->param('ma_title');
        $url = $request->param('video_url');
        $trial = $request->param('trial', 0);
        $ma_time = $request->param('ma_time', 0);

        if (!$ma_id) {
            if (!$url) {
                responseData(0, '文件不能为空');
            }
            $course = ContentCourse::find($course_id);
            if ($course->operate_type == 2 && $course->mat_num > 0) {
                responseData(0, '系列课最多允许上传一个章节');
            }

            $material = new ContentCourseMaterial();
            $material->course_id = $course_id;
            $material->ma_title = $title;
            $material->ma_url = $url;
            $material->trial = $trial;
            $material->ma_time = $ma_time;
            $material->save();
        } else {
            $material = ContentCourseMaterial::find($ma_id);
            $material->ma_title = $title;
            $material->trial = $trial;
            if ($ma_time) {
                $material->ma_time = $ma_time;
            }
            if ($url) {
                $material->ma_url = $url;
            }
            $material->save();
        }
        responseData(1, '成功');
    }

    /**
     * 章节删除
     * @param Request $request
     */
    public function maDel(Request $request)
    {
        $id = $request->param('id', 0);
        if (ContentCourseMaterial::destroy($id)) {
            responseData(1, '成功');
        } else {
            responseData(0, '删除失败');
        }
    }

    public function courseSort(Request $request)
    {
        $id = $request->param('id', 0);
        $sort = $request->param('sort', 0);
        if ($sort < 0) {
            responseData(0, '排序值不可小于0');
        }
        if ($id !== 0 && ContentCourseMaterial::update(['ma_id' => $id, 'sort' => $sort])) {
            responseData(1, '成功');
        } else {
            responseData(0, '失败');
        }
    }


}

