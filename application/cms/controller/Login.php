<?php
/**
 * 登录
 * Created by: zhang
 * Date Time : 2017/12/12 11:16
 */
namespace app\cms\controller;

use app\cms\model\PermissionMenuRelation;
use app\cms\model\PermissionPerson;
use app\cms\model\PermissionPersonRelation;
use think\Controller;
use think\Request;
use think\Session;

class Login extends Controller
{

    /**
     * 登录页面
     * @return View
     */
    public function index()
    {
        return view();
    }

    /**
     * 登陆
     * Login constructor.
     * @param Request $request
     */
    public function login(Request $request)
    {
        $where = [
            'account' => trim($request->param('username', '')),
            'password' => md5(trim($request->param('pwd', ''))),
        ];
        $user = PermissionPerson::where($where)->find();
        if(!empty($user)){
            if($user['status'] == 1){
                Session::set('user', $user);
                (new PermissionMenuRelation())->getMenuList();
                $this->redirect('Index/index');
            }else{
                $this->error('账号已被禁用');
            }
        }else{
            $this->error('登录失败');
        }

    }

    /**
     * 登出
     */
    public function loginOut()
    {
        Session::clear('cms');
        $this->redirect('Login/index');
    }


}