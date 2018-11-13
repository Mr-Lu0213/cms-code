<?php
/**
 * 人员关系表
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Model;

class PermissionPersonRelation extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    public function role()
    {
        return $this->hasOne('PermissionRole', 'role_id', 'role_id');
    }

    public function updateRole($pid, $roles)
    {
        $roles = array_unique($roles);
        $this->where(['pid'=>$pid])->delete();
        if(empty($roles)){
            return true;
        }
        $dataAll = [];
        foreach ($roles as $v){
            $dataAll[] = [
                'pid' => $pid,
                'role_id' => $v
            ];
        }
        $this->saveAll($dataAll);
    }

}