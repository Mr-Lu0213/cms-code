<?php
/**
 * 浏览量
 * Created by: zhang
 * Date Time : 2018/4/13 15:03
 */
namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;
use think\Session;

class StatWxPage extends Model{
    protected $createTime = 'created_time';

    public function article(){

        return $this->hasOne('ContentArticle', 'article_id', 'article_id');
    }
}