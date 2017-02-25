<?php
session_start();

require_once('./db/Pdomysql.php');
$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
$PNG_WEB_DIR = 'temp/';
if (!file_exists($PNG_TEMP_DIR)){
    mkdir($PNG_TEMP_DIR); 
}
include dirname(__FILE__).DIRECTORY_SEPARATOR.'phpqrcode'.DIRECTORY_SEPARATOR.'qrlib.php';

$filename = $PNG_TEMP_DIR.'test.png';

// uuid 生成切勿拿到生产环境里使用,虽然重复几率小，30秒将重新更新下session，至于你保存到数据啥滴、都可以
$uuid = '';
if(isset($_SESSION['buildqr']) and isset($_SESSION['buildqr']['timestamp']) and (time() - $_SESSION['buildqr']['timestamp']) < 30) {
    $uuid = $_SESSION['buildqr']['uuid'];
} else {
    $uuid = uuid();
    // write to db
    $db = Pdomysql::getInstance();
    $sql = 'insert into `qrlogin`(uname,token)value("","'.$uuid.'")';
    $status = $db->execute($sql);
    if($status) {
      $_SESSION['buildqr'] = [
            'uuid' => $uuid,
            'timestamp' => time(),
        ];       
    }
}
$tokenURL = 'http://192.168.1.111/qrlogin/api.php?token='.$uuid;
$filename = $PNG_TEMP_DIR.'test'.md5($tokenURL.'|L|10').'.png';
$imgSRC = $PNG_WEB_DIR.basename($filename);
QRcode::png($tokenURL, $filename, 'L', '10', 2);

echo
<<<QR
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>swoole+websocket+redis</title>
            <script type="text/javascript">
            var token = "$uuid";
            </script>
            <script src="./js/jquery.min.js"></script>
            <script src="./js/login.js"></script> 
        </head>
        <body>
            <div id="status"></div>
            <img src="$imgSRC" />
        </body>
    </html>
QR;


function uuid($namespace = '') {
    static $guid = '';
    $uid = uniqid("", true);
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid =   
            substr($hash,  0,  8) . 
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12);
    return $guid;
}