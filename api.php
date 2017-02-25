<?php
$method = isset($_POST['method']) ? htmlspecialchars($_POST['method']) : '';
require_once('./db/Pdomysql.php');
$db = Pdomysql::getInstance();

//swoole server IP and port
$ip = '192.168.1.131';
$port = '9503';
switch($method)
{
    case 'thridLogin':
            $token = isset($_POST['token']) ? htmlspecialchars($_POST['token']) : '';
            $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
            if(empty($token)) { echo 'token must be set.'; exit();}
            // write to db
            $sql = 'update `qrlogin` set uname="'.$username.'" where token="'.$token.'"';
            $status = $db->execute($sql);   
            $data = [
                'method' => 'verify',
                'username' => $username,
                'token'    => $token,
            ];
            // send to swoole server use UDP
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $msg = json_encode($data);
            $len = strlen($msg);
            socket_sendto($sock, $msg, $len, 0, $ip, $port);
            socket_close($sock);  
        break;
    case 'islogin':
            // ajax 轮询 可用(low 逼做法)
            $token = isset($_POST['token']) ? htmlspecialchars($_POST['token']) : '';
            $where = 'token="'.$token.'"';
            $result = $db->fetOne('qrlogin','uname',$where);
            if($result){
                if(empty($result['uname'])) {
                    echo 0;
                } else {
                    echo 1;
                }
            }
        break;
    default:
            echo 'The method is not allowed';
        break;
}