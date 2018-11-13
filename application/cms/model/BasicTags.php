<?php
/**
 * 标签
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class BasicTags extends Model
{
    protected $insert = ['level'];

    public function setLevelAttr($value,$data)
    {
        return empty($data['parent_id'])? 0: 1;
    }

    public function getList()
    {
        $list = BasicTags::order('tag_id')->select();
        $list = collection($list)->toArray();
        $tag_list = array();
        // 获取第一级菜单
        foreach ($list as $k=>$v){
            if($v['level'] == 0){
                $tag_list[$v['tag_id']] = $v;
                unset($list[$k]);
            }
        }
        // 二级菜单
        foreach ($list as $k=>$v){
            if($v['level'] == 1 && isset($tag_list[$v['parent_id']])){
                $tag_list[$v['parent_id']]['children'][] = $v;
            }
        }
        return $tag_list;
    }

    public function getRecommendTextAttr($value, $data)
    {
        switch ($data['recommend']){
            case 0:
                return '正常';

            default;
                return '推荐';
        }
    }
}
