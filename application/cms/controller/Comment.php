<?php
namespace app\cms\controller;


use app\cms\model\ContentExpert;
use app\cms\model\PermissionMenu;
use app\cms\model\PermissionMenuRelation;
use app\cms\model\PermissionPerson;
use app\cms\model\PermissionPersonRelation;
use app\cms\model\PermissionRole;
use app\cms\model\WxComment;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Comment extends Basic
{
    /**
     * 用户评论列表
     * @return \think\response\View
     */
    public function comment()
    {
        $comment = new WxComment();
        $map['status'] = 1;
        $list = $comment->where($map)->select();
        foreach ($list as $key => $value) {
            $list[$key]['nick_name'] = $comment->member()->where('member_id='.$value['member_id'])
                                         ->value('nick_name');
            if ($value['content_type'] == 1) {
                $list[$key]['relevance'] = $comment->course()->where('course_id='.$value['content_id'])
                                        ->value('title');
            } elseif ($value['content_type'] == 3) {
                $list[$key]['relevance'] = $comment->expert()->where('expert_id='.$value['content_id'])
                                         ->value('name');
            } else {
                $list[$key]['relevance'] = $comment->article()->where('article_id='.$value['content_id'])
                                        ->value('title');
            }
        }


        foreach ($list as $key => $value) {
            foreach ($value['relevance'] as $item => $val) {
                foreach ($val as $k => $v) {
                    $value['relevance'] = $v;
                }
            }
        }

        $this->assign('list', $list);
        return view();
    }

    public function commentDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if(WxComment::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

}


