<?php
/**
 *
 * Created by: zhang
 * Date Time : 2018/6/4 11:52
 */

namespace app\cms\controller;

use app\cms\model\ContentArticle;
use app\cms\model\ContentCourse;
use app\cms\model\ContentExpert;
use app\cms\model\WxAudioColumn;
use app\cms\model\WxBanner as BannerModel;
use think\Db;
use think\Log;
use think\Request;

class Banner extends Basic
{
    // 内容类型
    public $link_type = [
        1 => [
            'value' => 1,
            'name'  => '视频',
            'checked'=>'',
        ],
        2 => [
            'value' => 2,
            'name'  => '资讯',
            'checked'=>'',
        ],
        3 => [
            'value' => 3,
            'name'  => '专家',
            'checked'=>'',
        ],
        4 => [
            'value' => 4,
            'name'  => '外部链接',
            'checked'=>'',
        ],
        5 => [
            'value' => 5,
            'name'  => '音频',
            'checked'=>'',
        ],
    ];

    // 位置
    public $type_list = [
        1 => [
            'value'  => 1,
            'name'   => '首页',
            'checked'=>'',
        ],
        2 => [
            'value'  => 2,
            'name'   => '微课堂',
            'checked'=>'',
        ],
        3 => [
            'value'  => 3,
            'name'   => '首页推荐',
            'checked'=>'',
        ],
        4 => [
            'value' => 4,
            'name'  => '音频',
            'checked'=>'',
        ],
    ];

    // 状态
    public $status_list = [
        [
            'value'  => 1,
            'name'   => '下线',
            'active' => 0
        ],
        [
            'value'  => 2,
            'name'   => '上线',
            'active' => 0
        ],
        [
            'value'  => 3,
            'name'   => '置顶',
            'active' => 0
        ],
    ];

    // 推荐
    public $recommend_list = [
        [
            'value'  => 1,
            'name'   => '取消置顶',
            'active' => 0
        ],
        [
            'value'  => 2,
            'name'   => '置顶',
            'active' => 0
        ]
    ];

    /**
     * 列表
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request)
    {
        $where = [];
        if(!empty($request->param('status'))){
            $status          = $request->param('status') - 1;
            $where['status'] = $status;
            if(isset($this->status_list[$status])){
                $this->status_list[$status]['active'] = 1;
            }
        }

        $list = BannerModel::where($where)
                ->order('status desc, recommend desc, id desc')->select();
        $this->assign('list', $list);
        $this->assign('status_list', $this->status_list);
        return view();
    }

    /**
     * 编辑页
     * @param Request $request
     * @return \think\response\View
     */
    public function edit(Request $request)
    {
        $act      = '新增';
        $info     = [];
        $id       = $request->param('id');
        $articles = ContentArticle::where(['status'=>1])->field('article_id, title')->select();
        $courses  = ContentCourse::where(['status'=>1, 'operate_type'=>1,'type'=>1])->field('course_id, title')->select();
        $experts  = ContentExpert::where(['status'=>3, 'operate_type'=>2])->field('expert_id, name')->select();
        $audios  =  ContentCourse::where(['status'=>1, 'type'=>2])->field('course_id, title')->select()->toArray();

        if($id){
            $info = BannerModel::get($id);
            $act  = '编辑';
            if(isset($this->link_type[$info['link_type']])){
                $this->link_type[$info['link_type']]['checked'] = 'checked';
            }else{
                $this->link_type[1]['checked'] = 'checked';
            }

            if(isset($this->type_list[$info['type']])){
                $this->type_list[$info['type']]['checked'] = 'checked';
            }else{
                $this->type_list[1]['checked'] = 'checked';
            }

            $url = $info->getData('link_url');
            if($info['link_type'] == 1){
                foreach ($courses as $k=>$v){
                    if($v['course_id'] == $url){
                        $courses[$k]['selected'] = 'selected';
                    }
                }
            }else if($info['link_type'] == 2){
                foreach ($articles as $k=>$v){
                    if($v['article_id'] == $url){
                        $articles[$k]['selected'] = 'selected';
                    }
                }
            }else if($info['link_type'] == 3){
                foreach ($experts as $k=>$v){
                    if($v['expert_id'] == $url){
                        $experts[$k]['selected'] = 'selected';
                    }
                }
            }
            else if($info['link_type'] == 5){
                foreach ($audios as $k=>$v){
                    if($v['course_id'] == $url){
                        $audios[$k]['selected'] = 'selected';
                    }
                }
            }
        }else{
            $this->link_type[1]['checked'] = 'checked';
            $this->type_list[1]['checked'] = 'checked';
        }

        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('link_type', $this->link_type);
        $this->assign('type_list', $this->type_list);
        $this->assign('articles', $articles);
        $this->assign('courses', $courses);
        $this->assign('experts', $experts);
        $this->assign('audios', $audios);

        return view();
    }

    /**
     * 保存
     * @param Request $request
     */
    public function save(Request $request)
    {
        $id         = $request->param('id', 0);
        $data = [
            'type'        => $request->param('type'),
            'link_type'   => $request->param('link_type'),
            'description' => $request->param('description'),
            'photo'       => $request->param('photo'),
            'status'      => 1
        ];
        switch ($data['link_type']){
            case 1:
                $data['link_url'] = $request->param('course_id');
                break;

            case 2:
                $data['link_url'] = $request->param('article_id');
                break;

            case 3:
                $data['link_url'] = $request->param('expert_id');
                break;

            case 4:
                $data['link_url'] = $request->param('link_url');
                break;

            case 5:
                $data['link_url'] = $request->param('course_id');
                break;


        }
        if(empty($data['link_url'])){
            responseData(0, '请选择链接内容或填写外部链接');
        }

        try{
            $model = new BannerModel();
            if($id){
                $model->save($data, ['id'=>$id]);
            }else{
                $model->save($data);
            }
            responseData(1,'成功');
        }catch (\Exception $e){
            Log::error(__FUNCTION__.' '.$e->getMessage());
            responseData(0,'失败');
        }
    }

    /**
     * 删除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $id  = $request->param('id', 0);
        if(BannerModel::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    /**
     * 更改状态
     * @param Request $request
     */
    public function status(Request $request)
    {
        $where['id'] = $request->param('id', 0);
        $status  = $request->param('status', 0);
        $type    = $request->param('type', 0);
        if($type == 2){
            $where['recommend'] = $status;
        }else{
            $where['status'] = $status;
        }
        if(BannerModel::update($where)){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }
}