<?php
/**
 * cms模块控制器基类
 * Created by: zhang
 * Date Time : 2017/12/11 14:55
 */
namespace app\cms\controller;

use app\cms\model\PermissionMenuRelation;
use think\Controller;
use think\Response;
use think\Session;
use think\Url;
use think\Request;
use think\Config;
use think\Validate;

class Basic extends Controller
{
    public $USERID;  // 账号ID
    public $page_skip;
    public $page_size;

    /**
     * 构造函数
     * Basic constructor.
     */
    public function _initialize()
    {
        // 检查登陆
        $this->isLogin();

        // 检查菜单
        $request = Request::instance();
        $this->menuList($request->controller(), $request->action());

        $this->httpValidate($request->param());
    }

    /**
     * 检测是否登陆
     * @return bool
     */
    public function isLogin()
    {
        if($user = $this->getUser()){
            $this->USERID = $user['pid'];
        }else{
            $this->redirect('Login/loginOut');
        }

    }

    /**
     * 获取菜单列表
     * @param $controller 控制器名
     * @param $action 方法名
     */
    public function menuList($controller , $action)
    {
        $controller = strtolower($controller);
        $action     = strtolower($action);
        $menuList   = $this->getMenuList();
        $active     = 0;
        $temp       = [];

        foreach ($menuList as $k=>$v){
            $menuList[$k]['active'] = 0;
            // 过滤空菜单
            if(empty($v['children'])){
                continue;
            }
            foreach ($v['children'] as $f=>$b){
                if(isset($b['active']) && $b['active'] == 1){
                    $temp = [$k, $f];
                }

                $url = explode('/', trim($b['url'], '/'));
                $menuList[$k]['children'][$f]['active'] = 0;

                if($active == 0){
                    // 判断是否当前菜单
                    if(isset($url[0]) && strtolower($url[0]) === $controller ){
                        if(isset($url[1]) && strtolower($url[1]) === $action){
                            $menuList[$k]['children'][$f]['active'] = 1;
                            $menuList[$k]['active'] = 1;
                            $active = 1;
                        }
                    }
                }
                // 生成链接
                $menuList[$k]['children'][$f]['link_url'] = Url::build($b['url']);
            }
        }

        // 当前操作不在菜单列表 保持原有的选中效果
        if ($active == 0 && !empty($temp)){
            $k = $temp[0];
            $f = $temp[1];
            $menuList[$k]['children'][$f]['active'] = 1;
            $menuList[$k]['active'] = 1;
        }

        Session::set('menu', $menuList);
        $this->assign('nav_menu_list', $menuList);

    }


    // 获取当前用户信息
    public function getUser()
    {
        return Session::get('user');
    }

    public function getMenuList()
    {
        if(Session::get('menu')){
            return Session::get('menu');
        }else{
            return (new PermissionMenuRelation())->getMenuList();
        }
    }

    public function releaseMenu()
    {
        Session::delete('menu');
    }

    /**
     * 参数验证
     */
    public function httpValidate($param)
    {
        $controller = strtolower($this->request->controller());
        $action     = strtolower($this->request->action());
        $c_data     = Config::get('cms_validate.'.$controller);
        if(!empty($c_data[$action]) && $rule = $c_data[$action]){
            $validate = new Validate($rule);
            $result   = $validate->check($param);
            if(!$result){
                responseData(0, $validate->getError());
            }
        }
    }

}