<?php
/**
 *
 * Created by: zhang
 * Date Time : 2018/5/22 15:04
 */

namespace app\cms\model;

use think\Config;
use think\Log;
use think\Model;

class ContentCourseMaterial extends Model
{
    protected $createTime = 'created_time';
    protected $updateTime = 'updated_time';
    protected $autoWriteTimestamp = 'datetime';

    protected static function init()
    {
        // 添加
        ContentCourseMaterial::event('after_insert', function ($data) {
            try{
                ContentCourse::where(['course_id' => $data['course_id']])->setInc('mat_num');
                if(!empty($data['ma_time'])){
                    ContentCourse::where(['course_id' => $data['course_id']])->setInc('course_time', $data['ma_time']);
                }

            }catch (\Exception $e){
                Log::error($e->getMessage());
            }
        });

        // 更新
        ContentCourseMaterial::event('after_update', function ($data) {
            try{
                if(!empty($data['ma_time'])){
                    ContentCourse::where(['course_id' => $data['course_id']])->setInc('course_time', $data['ma_time']);
                }
            }catch (\Exception $e){
                Log::error($e->getMessage());
            }
        });

        // 删除
        ContentCourseMaterial::event('after_delete', function ($data) {
            try{
                ContentCourse::where(['course_id' => $data['course_id']])->setDec('mat_num');
                if(!empty($data['ma_time'])){
                    ContentCourse::where(['course_id' => $data['course_id']])->setDec('course_time', $data['ma_time']);
                }
            }catch (\Exception $e){
                Log::error($e->getMessage());
            }
        });
    }

    public function getMaUrlAttr($value)
    {
        return empty($value)? '': Config::get('aliyun_oss.res_url').$value;
    }

    public function course()
    {
        return $this->hasOne('ContentCourse', 'course_id', 'course_id');
    }
}