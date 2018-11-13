<?php
/**
 * 菜单关系表
 * Created by: zhang
 * Date Time : 2018/5/31 15:03
 */
namespace app\cms\model;

use think\Model;
use think\Session;

class PermissionMenuRelation extends Model{

    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function menu()
    {
        return $this->hasOne('PermissionMenu', 'menu_id', 'menu_id');
    }

    public function getMenuList()
    {
        $user  = Session::get('user');
        $roles = PermissionPersonRelation::where(['pid'=>$user['pid']])->column('role_id');
        $where = [
            'role_id'=> ['in', $roles]
        ];
        $menu  = $this->alias('pr')
                ->join('permission_menu pm', 'pm.menu_id = pr.menu_id')
                ->where($where)->field('pm.*')->group('pm.menu_id')
                ->order('pm.seq asc, pm.menu_id asc')->select();
        $menu = collection($menu)->toArray();

        $menu_list = array();
        // 获取第一级菜单
        foreach ($menu as $k=>$v){
            if($v['level'] == 1){
                $menu_list[$v['menu_id']] = $v;
                unset($menu[$k]);
            }
        }
        // 二级菜单
        foreach ($menu as $k=>$v){
            if($v['level'] == 2 && isset($menu_list[$v['parent_id']])){
                $menu_list[$v['parent_id']]['children'][] = $v;
                unset($menu[$k]);
            }
        }
        Session::set('menu', $menu_list);
        return $menu_list;
    }

    public function updateRel($role_id, $menus)
    {
        $menus = array_unique($menus);
        $this->where(['role_id'=>$role_id])->delete();
        if(empty($menus)){
            return true;
        }
        $menu = new PermissionMenu();
        $dataAll = [];
        $parent  = [];
        foreach ($menus as $v){
            $dataAll[] = [
                'role_id' => $role_id,
                'menu_id' => $v
            ];
            $parent_id = $menu->where(['menu_id'=>$v])->value('parent_id');
            if($parent_id && !in_array($parent_id, $menus)){
                $parent[] = $parent_id;
            }
        }

        $parent = array_unique($parent);
        if($parent){
            foreach ($parent as $v){
                $dataAll[] = [
                    'role_id' => $role_id,
                    'menu_id' => $v
                ];
            }
        }

        $this->saveAll($dataAll);
    }

    public function hasMenus($role_id)
    {
        return $this->where(['role_id'=>$role_id])->column('menu_id');
    }
}