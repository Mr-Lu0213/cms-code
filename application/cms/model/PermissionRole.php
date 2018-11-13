<?php
/**
 * 角色表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Model;

class PermissionRole extends Model{

    protected $createTime = 'created_time';
    protected $updateTime = false;
    protected $autoWriteTimestamp = 'datetime';
    protected $resultSetType = 'collection';

    public function menus()
    {
        return $this->hasMany('PermissionMenuRelation', 'role_id', 'role_id')->field('menu_id');
    }

    public function roleList()
    {
        return $this->order('role_id desc')->where('role_id != 1')->field('role_id, role_name')->select()->toArray();
    }

}