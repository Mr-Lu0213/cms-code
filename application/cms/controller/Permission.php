<?php
namespace app\cms\controller;


use app\cms\model\PermissionMenu;
use app\cms\model\PermissionMenuRelation;
use app\cms\model\PermissionPerson;
use app\cms\model\PermissionPersonRelation;
use app\cms\model\PermissionRole;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Permission extends Basic
{
    /**
     * 菜单列表
     * @return \think\response\View
     */
    public function menu()
    {
        $menu_list = (new PermissionMenu())->getList();
        $this->assign('list', $menu_list);
        return view();
    }

    /**
     * 菜单
     * @param Request $request
     */
    public function menuEdit(Request $request)
    {
        $menu_id       = $request->param('id', 0);
        $menu          = array();
        if($menu_id){
            $menu      = PermissionMenu::find($menu_id);
            $parent_id = $menu->parent_id;
            $act       = '编辑';
        }else{
            $parent_id = $request->param('parent_id', 0);
        }

        $parent_list = PermissionMenu::where(['level'=>1])->column('name, seq', 'menu_id');
        foreach ($parent_list as $k=>$v) {
            if($parent_id == $v['menu_id']){
                $parent_list[$k]['active'] = 1;
            }else{
                $parent_list[$k]['active'] = 0;
            }
        }

        $this->assign('parent_list', $parent_list);
        $this->assign('info', $menu);
        $this->assign('act', $act);

        return view();
    }

    /**
     * 菜单保存
     */

    public function menuSave(Request $request)
    {
        $menu_id           = $request->param('menu_id', 0);
        $data['name']      = $request->param('name');
        $data['url']       = $request->param('url');
        $data['icon']      = $request->param('icon');
        $data['parent_id'] = $request->param('parent_id', 0);
        $menu = new PermissionMenu();
        if($menu_id){
            $menu->save($data, ['menu_id'=>$menu_id]);
        }else{
            $menu->data($data)->save();
        }
        $this->releaseMenu();
        responseData(1,'成功');
    }

    /**
     * 菜单删除
     */
    public function menuDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if(PermissionMenu::destroy($id)){
            $this->releaseMenu();
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    /**
     * 角色列表
     */
    public function role()
    {
        $User = new PermissionMenu();
        $count=$User->count();
        $page_num =empty($_REQUEST['page_num'])? "10" : $_REQUEST['role_name'];
        $list = PermissionRole::all();
        $this->assign('list', $list);

        return view();
    }

    /**
     * 编辑页面
     * @param Request $request
     * @return \think\response\View
     */
    public function roleEdit(Request $request)
    {
        $id = $request->param('id', 0);
        $info = array();
        $r_m = array();
        $act = '新增';
        $menu_list = (new PermissionMenu())->getList();
        if ($id) {
            $info = PermissionRole::find($id);
            $act = '编辑';
            $r_m = (new PermissionMenuRelation())->hasMenus($id);
            foreach ($menu_list as $k => $v) {
                if (in_array($v['menu_id'], $r_m)) {
                    $menu_list[$k]['checked'] = 'checked';
                }
                foreach ($v['children'] as $f => $b) {
                    if (in_array($b['menu_id'], $r_m)) {
                        $menu_list[$k]['children'][$f]['checked'] = 'checked';
                    }
                }
            }
        }
        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('r_m', $r_m);
        $this->assign('menu_list', $menu_list);

        return view();
    }

    /**
     * 角色保存
     */
    public function roleSave(Request $request)
    {
        $id         = $request->param('role_id', 0);
        $menu_check = $_REQUEST['menu_check'];
        $data = [
            'role_name' => $request->param('role_name'),
            'description' => $request->param('description'),
        ];

        Db::startTrans();
        try{
            $role = new PermissionRole();
            if($id){
                $role->save($data, ['role_id'=>$id]);
            }else{
                $role->data($data)->save();
                $id = $role->role_id;
            }
            (new PermissionMenuRelation())->updateRel($id, $menu_check);
            Db::commit();
            responseData(1,'成功');
        }catch (\Exception $e){
            Db::rollback();
            Log::error(__FUNCTION__.' '.$e->getMessage());
            responseData(0,'失败');
        }

    }

    /**
     * 角色删除
     */
    public function roleDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if(PermissionRole::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'删除失败');
        }
    }

    /**
     * 管理员
     */
    public function person()
    {
        $list = PermissionPerson::order('pid desc')->select();
        $this->assign('list', $list);
        return view();
    }

    /**
     * 管理员编辑页
     */
    public function personEdit(Request $request)
    {
        $role_list = (new PermissionRole())->roleList();
        $id       = $request->param('id', 0);
        $info     = array();
        $roles    = array();
        $act      = '新增';

        if($id){
            $info = PermissionPerson::find($id);
            $roles = PermissionPersonRelation::where(['pid'=>$id])->column('role_id');
            $act  = '编辑';
        }
        foreach ($role_list as $k=>$v){
            if(in_array($v['role_id'], $roles)){
                $role_list[$k]['checked'] = 'checked';
            }
        }

        $this->assign('act', $act);
        $this->assign('info', $info);
        $this->assign('role_list', $role_list);

        return view();
    }

    /**
     * 管理员保存
     */
    public function personSave(Request $request)
    {
        $id         = $request->param('id', 0);
        $roles      = $_REQUEST['roles'];
        $data = [
            'name'     => $request->param('person_name'),
            'account'  => $request->param('person_account'),
            'password' => $request->param('person_password'),
        ];
        Db::startTrans();
        try{
            $person = new PermissionPerson();
            if($id){
                unset($data['account']);
                if(empty($data['password'])){
                    unset($data['password']);
                }else{
                    $data['password'] = md5($data['password']);
                }

                $person->save($data, ['pid'=>$id]);
            }else{
                if(empty($data['password'])){
                    responseData(0,'密码不能为空');
                }
                $data['password'] = md5($data['password']);
                $person->data($data)->save();
                $id = $person->pid;
            }
            (new PermissionPersonRelation())->updateRole($id, $roles);
            Db::commit();
            responseData(1,'成功');
        }catch (\Exception $e){
            Db::rollback();
            Log::error(__FUNCTION__.' '.$e->getMessage());
            responseData(0,'失败');
        }
    }

    /**
     * 管理员删除
     */
    public function personDel(Request $request)
    {
        $id  = $request->param('id', 0);
        if($id !== 1 && PermissionPerson::destroy($id)){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }

    /**
     * 状态
     */
    public function personStatus(Request $request)
    {
        $id      = $request->param('id', 0);
        $status  = $request->param('status', 0);
        if($id !== 0 && PermissionPerson::update(['pid'=>$id, 'status'=>$status])){
            responseData(1,'成功');
        }else{
            responseData(0,'失败');
        }
    }
}
