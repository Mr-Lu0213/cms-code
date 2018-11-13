<?php
/**
 * 权限人员表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use app\cms\controller\Permission;
use think\Model;

class PermissionPerson extends Model{

    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';


    public function getStatusTextAttr($value, $data)
    {

        switch ($data['status']){
            case 0:
                return '禁用';

            default:
                return '启用';

        }
    }

    /**
     * 获取角色
     */
    public function getRolesAttr($value, $data)
    {
        $roles = PermissionPersonRelation::alias('pr')->join('permission_role r', 'pr.role_id = r.role_id')
            ->where(['pr.pid' => $data['pid']])->column('r.role_name');
        return implode(', ', $roles);
    }

}