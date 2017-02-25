<?php
require __DIR__ . "/WebSocketClient.php";
$host = '127.0.0.1';
$prot = 9502;

$token = isset($_POST['token']) ? htmlspecialchars($_POST['token']) : '';
$username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';

$client = new WebSocketClient($host, $prot);
$client->connect();
$data = [
    'method' => 'asyncLogin',
    'token' => $token,
    'username' => $username
];

$client->send(json_encode($data));
$recvData = "";
$tmp = $client->recv();
if (empty($tmp))
{
    break;
}
$recvData .= $tmp;
echo $recvData . "size:" . strlen($recvData) . PHP_EOL;

echo PHP_EOL . "======" . PHP_EOL;
echo 'finish' . PHP_EOL;