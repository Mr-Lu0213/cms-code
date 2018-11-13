<?php
namespace app\cms\controller;


use OSS\Core\OssException;
use think\Config;
use think\Log;
use think\Request;

class Index extends Basic
{
    public function index()
    {
        return view();
    }

    /**
     * 保存图片
     * @return \think\Response
     */
    public function uploadImg()
    {
        if(!empty($upload = uploadFile())){
            $data['photo'] = $upload[0] ;
            responseData(1, '成功', $data);
        }else{
            responseData(0, '失败');
        }
    }

    /**
     * 音视频上传
     */
    public function uploadVideo(Request $request)
    {
        if($request->has('chunks')){
            $this->chunkTemp();
            responseData(1, '临时文件保存成功');
        }else if ($request->has('merge')){
            $file = $this->chunkMerge();
            if($file){
                $data['url'] = multiupload($file);
                $data['oss_url'] = Config::get('aliyun_oss.res_url').$data['url'];
                responseData(1, '文件转存成功', $data);
            }
        }else{
            responseData(0, '失败');
        }

    }

    /**
     * 临时保存
     * @return bool
     */
    public function chunkTemp()
    {
        $md5       = request()->param('md5');
        $file      = request()->file('file');
        if($file){
            $path      = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'temp' . DS  . $md5;
            $save_name = request()->param('chunk');
            $info = $file->move($path, $save_name);
            if($info){
                return true;
            }else{
                Log::error(__FUNCTION__.' '.$file->getError());
            }
        }else{
            Log::error(__FUNCTION__.' '.json_encode([$file, $_FILES]));
        }
    }

    /**
     * 合并文件
     * @return string
     */
    public function chunkMerge()
    {
        $md5       = request()->param('md5');
        $filename  = $md5.'-'.request()->param('fileName');
        $dir       = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'temp' . DS . $md5;
        $save_file = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'temp' . DS . $filename;

        // 获取分片文件内容
        $block_info = scandir($dir);

        // 除去无用文件
        foreach ($block_info as $key => $block) {
            if ($block == '.' || $block == '..') unset($block_info[$key]);
        }
        natsort($block_info);

        if (!file_exists($save_file)) fopen($save_file, "w");

        // 开始写入
        $out = @fopen($save_file, "wb");

        // 增加文件锁
        if (flock($out, LOCK_EX)) {
            foreach ($block_info as $b) {
                // 读取文件
                if (!$in = @fopen($dir.'/'.$b, "rb")) {
                    break;
                }

                // 写入文件
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }

                @fclose($in);
                @unlink($dir.'/'.$b);
            }
            flock($out, LOCK_UN);
        }
        @fclose($out);
        @rmdir($dir);
        return $save_file;

    }



}
