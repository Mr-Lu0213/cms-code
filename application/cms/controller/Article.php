<?php
namespace app\cms\controller;


use app\cms\model\BasicContentClass;
use app\cms\model\ContentArticle;
use app\cms\model\BasicTags;
use think\Db;
use think\Log;
use think\Request;
use think\Session;
use think\Url;

class Article extends Basic
{
    /**
     * 资讯列表
     * @return \think\response\View
     */
    public function article()
    {
//        $article = new ContentArticle();
//        $list  = $article->order('created_time desc')->select();
//
//        $this->assign('list', $list);
        return view();
    }

    /**
     * ajax 分页数据源
     * @param Request $request
     */
    public function ajaxData(Request $request)
    {
        $where = [];
        $start = $request->param('start', 0);
        $length= $request->param('length', 10);
        $draw  = $request->param('draw');
        $search = $request->param('search.value');
        if($search){
            $where['title'] = ['like', '%'.$search.'%'];
        }

        // 条件过滤后记录数 必要
        $recordsFiltered = ContentArticle::where($where)->count();
        // 表的总记录数 必要
        $recordsTotal = ContentArticle::count();
        // 条件查询记录
        $list = ContentArticle::where($where)
            ->field('article_id, source_type, title, photo, status, author, created_time, class_id')
            ->order('article_id desc')
            ->limit($start, $length)->select();
        foreach ($list as $k=>$v){
            $list[$k]['photo_url'] = $v['photo_url'];
            $list[$k]['source_text'] = $v['source_text'];
            $list[$k]['status_text'] = $v['status_text'];
            $article_id = $v['article_id'];
            $url = url('Article/articleedit').'?id='.$article_id;
            $list[$k]['action'] = "<a href='{$url}'>编辑&nbsp;&nbsp;</a>";
            if($v['status'] == 1){
                $list[$k]['action'] .= "<a href='#' onclick='statusArt($article_id, 0)'>下线&nbsp;&nbsp;</a>";
            }else{
                $list[$k]['action'] .= "<a href='#' onclick='statusArt($article_id, 1)'>上线&nbsp;&nbsp;</a>";
            }
            $list[$k]['img'] = "<img src='{$v['photo_url']}?x-oss-process=image/resize,h_80' height=40>";
            $list[$k]['class_name'] = $v->dict->class_name;
        }

        $result = [
            "draw" => intval($draw),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $list
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 资讯添加
     */
    public function articleadd(Request $request)
    {
        $article_id = $request->param('article_id', 0);
        $tags       = empty($_REQUEST['tags']) ?[]: $_REQUEST['tags'];
        $class_id  = $request->param('class_name',0);

        //判断页面是否提交
        $data = [
            'type' => $request->param('type',1),
            'title' => $request->param('title'),
            'tags' => $tags,
            'class_id' => $class_id,
            'photo' => $request->param('photo','?x-oss-process=image/resize,h_80'),
            'status' => $request->param('status'),
            'source_type' => $request->param('source_type'),
            'recommend' => $request->param('recommend'),
            'summary'=>$request->param('summary'),
            'author' => $request->param('author'),
            'custom_look_num' => $request->param('custom_look_num',0),
        ];

        $article = new ContentArticle();
        $res = ContentArticle::where(['title'=>$data['title']])->value('article_id');
        if($res && (empty($article_id) || $article_id != $res)){
            responseData(0,'标题重复');
        }

        if($data['custom_look_num']<0){
            responseData(0,'浏览次数不能为负数');

        }
        if($article_id){
            $data['article_id']=$article_id;
            $article->save($data,['article_id' =>$article_id]);
            $article->detail->content=$request->param('content');
            $article->detail->save();
        }else{
            $article->save($data);
            $article->detail()->save(['content'=>$request->param('content')]);
        }


        responseData(1,'成功');
    }

    /**
     * 资讯编辑
     */
    public function articleEdit(Request $request)
    {
        $source_type = [
            [
                'value'  => 1,
                'name'   => '原创',
                'checked' => ''
            ],
            [
                'value'  => 2,
                'name'   => '转载',
                'checked' => 'checked'
            ],
        ];
        $article_id = $request->param('id', 0);
        $tag_list = BasicTags::select();
        $dict_list = BasicContentClass::where(['class_level'=>1])->select()->toArray();

        $act      = '新增';
        if($article_id){
            $info = ContentArticle::get($article_id);
            $act  = '编辑';
            if($info['tags']){

                foreach ($tag_list as $k=>$v){
                    if(in_array($v['name'], $info['tags'])){
                        $tag_list[$k]['selected'] = 'selected';
                    }
                }
            }
            if($info['class_id']){

                foreach ($dict_list as $k=>$v){
                   //dump($info);die;
                    if($v['class_id']==$info['class_id']){
                        $dict_list[$k]['selected'] = 'selected';
                    }
                }
            }

                foreach ($source_type as $k=>$v){
                    if($v['value'] == $info['source_type']){
                        $source_type[$k]['checked'] = 'checked';
                    }else{
                        $source_type[$k]['checked'] = '';
                    }
               }

        }
        $this->assign('source_type' ,$source_type);
        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('tag_list', $tag_list);
        $this->assign('dict_list', $dict_list);

        return view();
    }

    /**
     * 状态
     */
    public function articleStatus(Request $request)
    {
        $id      = $request->param('id', 0);
        $status  = $request->param('status', 0);
        if($id !== 0 && ContentArticle::update(['article_id'=>$id, 'status'=>$status])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

    public function articleRecommend(Request $request)
    {
        $id      = $request->param('id', 0);
        $recommend  = $request->param('recommend', 0);
        if($id !== 0 && ContentArticle::update(['article_id'=>$id, 'recommend'=>$recommend])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

}


