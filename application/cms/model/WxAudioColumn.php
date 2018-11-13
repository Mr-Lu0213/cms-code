<?php
/**
 * 音频表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;


class WxAudioColumn extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    protected $insert = ['level'];

    public function setLevelAttr($value,$data)
    {
        return empty($data['parent_id'])? 0: 1;
    }

    public function getList()
    {
        $list = WxAudioColumn::order('seq asc,column_id desc')->select();

        $tag_list = array();
        // 获取第一级菜单
        foreach ($list as $k=>$v){

            $v['icon_url']=$v->icon_url;
            //dump($v);die;
            if($v['level'] == 0){
                $tag_list[$v['column_id']] =$v;
                unset($list[$k]);
            }
        }
        $tag_list = collection($tag_list)->toArray();
        // 二级菜单
        foreach ($list as $k=>$v){
            if($v['level'] == 1 && isset($tag_list[$v['parent_id']])){
                $tag_list[$v['parent_id']]['children'][] = $v;
            }
        }
        return $tag_list;
    }

    public function getIconUrlAttr($value,$data)
    {
        return empty($data['icon'])? '': Config::get('aliyun_oss.res_url').$data['icon'];
    }

    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){

            case 1:
                return '上线';

            default;
                return '下线';
        }
    }

    public function getRecommendTextAttr($value, $data)
    {
        switch ($data['recommend']){
            case 1:
                return '正常';

            default;
                return '推荐';
        }
    }


}