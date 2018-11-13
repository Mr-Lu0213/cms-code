<?php
/**
 * 异常处理
 * Created by: zhang
 * Date Time : 2018/4/13 11:31
 */
namespace app\cms\exception;

use Exception;
use think\exception\Handle;
use think\Log;
use think\Request;

class Http extends Handle
{
    public function render(Exception $e)
    {
        Log::error($e->getMessage());
        if(Request::instance()->isAjax()){
            responseData(0, '请求出错了', [$e->getMessage()]);
        }
    }
}