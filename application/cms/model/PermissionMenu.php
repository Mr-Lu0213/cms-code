<?php
/**
 * 功能菜单表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Log;
use think\Model;
use think\Session;

class PermissionMenu extends Model{

    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';
    protected $insert = ['level'];
    protected $update = ['level'];

    protected static function init()
    {
        // 超级管理员自动绑定所有菜单
        PermissionMenu::event('after_insert', function ($menu) {
            try{
                $relation = new PermissionMenuRelation();
                $relation->data([
                    'role_id' => 1,
                    'menu_id' => $menu->menu_id
                ])->save();
                Session::delete('menu');
            }catch (\Exception $e){
                Log::error($e->getMessage());
            }
        });

        // 删除菜单关联
        PermissionMenu::event('after_delete', function ($menu) {
            try{
                PermissionMenuRelation::destroy(['menu_id' => $menu->menu_id, 'role_id'=>['neq'=>1]]);
                Session::delete('menu');
            }catch (\Exception $e){
                Log::error($e->getMessage());
            }
        });
    }

    public function setLevelAttr($value,$data)
    {
        return empty($data['parent_id'])? 1: 2;
    }

    public function getList()
    {
        $list = PermissionMenu::order('menu_id')->select();
        $list = collection($list)->toArray();
        $menu_list = array();
        // 获取第一级菜单
        foreach ($list as $k=>$v){
            if($v['level'] == 1){
                $menu_list[$v['menu_id']] = $v;
                unset($list[$k]);
            }
        }
        // 二级菜单
        foreach ($list as $k=>$v){
            if($v['level'] == 2 && isset($menu_list[$v['parent_id']])){
                $menu_list[$v['parent_id']]['children'][] = $v;
            }
        }
        return $menu_list;
    }

}