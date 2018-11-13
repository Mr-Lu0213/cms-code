<?php

namespace app\cms\controller;


use app\cms\model\WxFeedback;
use app\cms\model\WxFeedbackReply;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Feedback extends Basic
{
    /**
     * 意见反馈表
     * @return \think\response\View
     */
    public function feedback()
    {
        $feedback = new WxFeedback();
        $feedback_list = $feedback->order('id desc')->select();
        foreach ($feedback_list as $key => $value) {
            $feedback_list[$key]['nick_name'] = $feedback->member()->where('member_id='.$value['member_id'])
                                                ->value('nick_name');
        }

        $this->assign('list', $feedback_list);

        return view();
    }


    public function feeddetail(Request $request)
    {
        $id = $request->param('id');
        $feeddetail_info= (new WxFeedback())->find($request->param('id'));
        $feedback = new WxFeedback();
        $feedback_reply = new WxFeedbackReply();

        $feedback_list = $feedback->where('id='.$id)->find();
        $feedback_list->sys_unread = 0;
        $feedback_list->save();
        $reply_list = $feedback_reply->where('feed_id ='.$id)->select();
        $this->assign('feedback_list', $feedback_list);
        $this->assign('reply_list', $reply_list);
        $this->assign('feeddetail_info', $feeddetail_info);

        return view();
    }

    public function feedreplyadd(Request $request)
    {
        $feed_id = $request->param('feed_id');
        $content = $request->param('content');
        $feedback = new WxFeedback();
        $feedback_reply = new WxFeedbackReply();
        $res = $feedback_reply->where(['content' => $content])->select();
        if ($res[0]['content'] == $content) {
            responseData(0, '回复重复');
        }
        if (empty($feed_id) || empty($content)) {
            responseData(0, '参数错误');
        } else {
            $data['feed_id'] = $feed_id;
            $data['content'] = $content;
            $data['type'] = 2;
            $data['pid'] = session('admin.pid');
            $data['created_time'] = date("Y-m-d H:i:s");
            $feedback_reply->insert($data);
            $feedback_list = $feedback->where('id='.$feed_id)->find();
            $feedback_list->member_unread = 1;
            $feedback_list->save();
            $msg = "<div class='list_div'>
                         <p>【{$data['created_time']}&nbsp;系统：】&nbsp;{$data['content']}</p >
                </div>";
            responseData(1, $msg);
        }
    }

}


