<?php
namespace app\common\controller;

use think\Config;
use think\Log;
use think\Url;
use Wchat\Wchat;

class Message
{
    protected $_Data;               // 数据
    protected $wchat;               // 微信实例

    public    $hostUri;             // 项目URI
    public    $temple_list;         // 模板列表
    public    $templeId;            // 模板ID
    public    $detailUrl;           // 消息详情链接
    public    $openId;              // openid
    public    $dataFirst;           // 模板数据
    public    $dataKeyword1 = '';   // 模板数据
    public    $dataKeyword2 = '';   // 模板数据
    public    $dataKeyword3 = '';   // 模板数据
    public    $dataKeyword4 = '';   // 模板数据
    public    $dataKeyword5 = '';   // 模板数据
    public    $dataRemark   = '';   // 模板数据

    public function __construct($data)
    {
        $this->_Data = $data;
        $this->wchat = new Wchat();
        $this->wchat->setAppid(Config::get('wx.app_id'));
        $this->wchat->setSecret(Config::get('wx.app_secret'));
        $this->hostUri = Config::get('smp.host_uri');
        $this->temple_list = Config::get('wx.temple_list');
    }

    /**
     * 发送消息 入口
     * @param $type 1、订购消息
     * @return bool
     */
    public function run($type = 0)
    {
        try{
            if(empty($this->templeId = $this->temple_list[$type])){
                return false;
            }
            switch ($type){
                case 1:
                    $this->detailUrl = $this->hostUri.Url::build('/web/job/read', ['id'=>$this->_Data->job_id, 'onlyRead'=>1]);
                    $this->orderMsg();
                    break;

            }

            return true;
        }catch (\Exception $e){
            Log::write("发送消息异常-{$type}-{$e->getMessage()}", 'error');
        }

    }

    //
    public function orderMsg()
    {
        $this->dataFirst = $this->_Data->title;
        $this->dataKeyword1 = '';
        $this->dataKeyword2 = '';
        $this->dataKeyword3 = $this->_Data->body;
        $this->dataKeyword4 = $this->_Data->create_date;

        //$this->send();

    }


    // 执行发送
    private function send()
    {
        $this->wchat->TemplateMessageSend($this->openId
            , $this->templeId
            , $this->detailUrl
            , $this->dataFirst
            , $this->dataKeyword1
            , $this->dataKeyword2
            , $this->dataKeyword3
            , $this->dataKeyword4
            , $this->dataRemark);
    }



}
