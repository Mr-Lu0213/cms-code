<?php
// 应用公共文件
use think\Config;
use OSS\OssClient;
use OSS\Request;
use think\Response;
use think\Log;

/**
 * 实例化一个oss上传类
 * @return OssClient
 */
function create_oss_client()
{
    $accessKeyId = config::get('aliyun_oss.key_id');
    $accessKeySecret = config::get('aliyun_oss.key_secret');
    $endpoint = config::get('aliyun_oss.end_point');
    return new OssClient($accessKeyId, $accessKeySecret, $endpoint);
}

/**
 * OSS 图片文件上传
 * @param string $process 图片处理参数
 * @return array 已上传文件路径
 */
function uploadFile($file = '', $upload_dir = ''){
    $oss = create_oss_client();
    $oss_bucket = config::get('aliyun_oss.bucket');
    $info = array();
    $upload_dir ? $upload_dir : $upload_dir = Config::get("common.upload_dir");
    $file? $files = [$file]: $files = $_FILES;
    foreach ($files as $k=>$v){
        $file = $v;

        //设置文件上传类型
        $allowExts = array('jpg','gif','png','jpeg','mp3');
        $pathinfo = pathinfo($file["name"]);
        if(empty($file["name"])){
            continue;
        }
        if(!in_array(strtolower($pathinfo['extension']), $allowExts)){
            throw new Exception("文件类型只允许 'jpg','gif','png','jpeg','mp3'");
        }

        // 检测文件大小
        $maxSize = 30000000;
        if ($maxSize < $file["size"]) {
            throw new Exception("文件过大");
        }

        $filename = $file["tmp_name"];
        $pinfo = pathinfo($file["name"]);
        $ftype = @$pinfo['extension'];
        $destination = $upload_dir.date("Ymd")."/" . date("YmdHis") . rand(100, 999) . "." . $ftype;
        try{
            $oss->uploadFile($oss_bucket, $destination, $filename);
            $info[] = $destination;
        }catch (\OSS\Core\OssException $e){
            Log::write(__FUNCTION__." OssException : ".$e->getMessage(), "ERROR");
        }catch (\Exception $e){
            Log::write(__FUNCTION__." : ".$e->getMessage(), "ERROR");
        }
    }
    return $info;
}

/**
 * OSS 内容上传为文件
 * @param string $content   内存中的内容
 * @param string $upload_dir    保存的文件地址
 * @return string
 */
function putObject($content = '', $filetype='png', $upload_dir = ''){
    if(!$content){
        return '';
    }
    $oss = create_oss_client();
    $oss_bucket = config::get('aliyun_oss.bucket');
    $upload_dir ? $upload_dir : $upload_dir = Config::get("common.upload_dir");
    $destination = $upload_dir.date("Ymd")."/" . date("YmdHis") . rand(100, 999).'.'.$filetype;
    try{
        $oss->putObject($oss_bucket, $destination, $content);
        return $destination;
    }catch (\OSS\Core\OssException $e){
        Log::write(__FUNCTION__." OssException : ".$e->getMessage(), "ERROR");
    }catch (\Exception $e){
        Log::write(__FUNCTION__." : ".$e->getMessage(), "ERROR");
    }

}


/**
 * 分片上传
 * @param $file
 * @return bool|string
 */
function multiupload($file)
{
    $pathinfo   = pathinfo($file);
    $ossClient  = create_oss_client();
    $bucket     = config::get('aliyun_oss.bucket');
    $upload_dir = Config::get("common.upload_dir");
    $ftype      = $pathinfo['extension'];
    $object     = $upload_dir.date("Ymd")."/" . date("YmdHis") . rand(100, 999) . "." . $ftype;
    try{
        $ossClient->multiuploadFile($bucket, $object, $file);
        unlink($file);  // 删除本地文件
        return $object;
    } catch(\OSS\Core\OssException $e) {
        Log::error(__FUNCTION__.' '.$e->getMessage());
        return false;
    }
}



/**
 * 发送短信验证码
 * @param integer $code   验证码/随机密码
 * @param integer $phone    手机号
 * @return boolean
 */
function sendMsg($code, $phone){
    $sms = new \Aliyun\sms\Sms(
        Config::get('aliyun_oss.key_id'),
        Config::get('aliyun_oss.key_secret')
    );
    $response = $sms->sendSms(
        "家长学堂", // 短信签名
        "SMS_105120058", // 短信模板编号
        $phone, // 短信接收者
        Array(  // 短信模板中字段的值
            "code"=>$code,
            "product"=>"家长学堂"
        )
    );
    date_default_timezone_set('PRC');
    Log::write(__FUNCTION__.' '.json_encode([$phone,$code,$response], JSON_UNESCAPED_UNICODE), 'info');
    if($response->Code == 'OK'){
        return true;
    }else{
        return false;
    }


}

/**
 * 验证手机号码格式
 * @param $phone
 * @return bool
 */
function checkPhone($phone)
{
    if(preg_match("/^1[34578]{1}\d{9}$/",$phone)){
        return true;
    }else{
        return false;
    }
}

/**
 * 生成手机验证码
 * @return int
 */
function randomCaptcha()
{
    return rand(99999, 999999);
}

/**
 * 获取当前日期
 * @return false|string
 */
function getNow()
{
    return date('Y-m-d H:i:s');
}


/**
 * 读取excel文件内容
 * @param $filename
 * @param string $encode
 * @return array
 */
function readExcel($filename, $encode='utf-8')
{
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($filename);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow = $objWorksheet->getHighestRow();
    $highestColumn = $objWorksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $excelData = array();
    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }
    return $excelData;
}

function exportExcel($expTitle, $expCellName, $expTableData){
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
    $fileName = $expTitle;
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);

    $objPHPExcel = new PHPExcel();
    $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

    /*            $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle); */

    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(28);
    //写入表头
    $i=0;
    foreach ($expCellName as $k=>$v){
        $col = $cellName[$i].'1';
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col, "  ".$v."  ");
        $objPHPExcel->getActiveSheet()->getStyle($col)->getFont()->setName('宋体') //字体
        ->setSize(11) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($col)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setAutoSize(true);
        $i++;
    }

    // Miscellaneous glyphs, UTF-8
    $j = 0;
    foreach ($expTableData as $vv){
        $i=0;
        foreach ($expCellName as $k=>$v){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$i].($j+2), $vv[$k]);
            $i++;
        }
        $j++;
    }

    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

/**
 * 解析url参数
 * @param $str
 * @return array
 */
function parse_url_param($str)
{
    $data = array();
    $arr=array();
    $p=array();
    $arr=explode('?', $str);
    $p = explode('&', $arr[1]);
    foreach ($p as $val) {
        $tmp = explode('=', $val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}


/**
 * ajax响应
 * @param $code
 * @param string $msg
 * @param array $data
 */
function responseData($code=0, $msg='', $data=[])
{
    $result = [
        'code' => $code,
        'msg'  => $msg,
        'data' => $data
    ];
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    die;
}

/**
 * 获取随机字符串
 * @param $len
 * @param null $chars
 * @return string
 */
function getRandomString($len, $chars=null)
{
    if (is_null($chars)){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
}

/**
 * 读取excel文件内容
 * @param $filename
 * @param $shifttitle   是否去除第一行记录
 * @return array
 */
function read_excel($filename, $shifttitle=true)
{
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    if($shifttitle){
        array_shift($sheetData);
    }
    return $sheetData;
}