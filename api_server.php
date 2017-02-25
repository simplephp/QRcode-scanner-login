<?php
$serv = new swoole_websocket_server("0.0.0.0", 9502);
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//这里监听了一个UDP端口用来 监听客服端 token 请求的
$serv->addlistener('0.0.0.0', 9503, SWOOLE_SOCK_UDP);
$serv->on('Open', function($server, $req) {
    $server->push($req->fd, responseJson(1,"success", ['method' => 'connection','status' => 1]));      
});

$serv->on('Message', function($server, $frame) use($redis){
    $rev_data = json_decode($frame->data,true);
    $method = isset($rev_data['method']) ? $rev_data['method'] : '';
    switch($method) {
        case 'join':
            $token = isset($rev_data['token']) ? $rev_data['token'] : '';
            $async_login_data = [
                'token' => $token,
                'fd' => $frame->fd
            ];
            $redis->hset("async_login_data",$token, json_encode($async_login_data));
            $server->push($frame->fd,responseJson(1,"success", ['method' => 'join','status' => 1]));
            break;
        default:
            break;
    }
});

/* 自定义处理
$process = new swoole_process(function($process) use ($redis, $serv) {
    while (true) {
        $data = $process->read();
        $data = json_decode($data,true);

    }
});

$serv->addProcess($process);
*/

$serv->on('packet', function ($serv, $data_string, $addr) use($redis){
    // UDP 请求 Response $server->sendto('220.181.57.216', 9502, "hello world");
    $data = json_decode($data_string, true);
    if(empty($data['method']) && !isset($data['method'])) return;
    if(empty($data['username']) && !isset($data['username'])) return;
    if(empty($data['token']) && !isset($data['token'])) return;
    //$process->write($data_string);
    $async_data = $redis->hget("async_login_data", $data['token']);
    if($async_data) {
        $async_data = json_decode($async_data, true);
        $serv->push($async_data['fd'], responseJson(1,"success", ['method' => 'verify','username' => $data['username'],'fd' => $async_data['fd'],'status' => 1]));    
    }
});

$serv->on('Close', function($server, $fd) {
    // 这儿应该要delete fd 前面 hash table key 为 token ,设计有问题。
    echo "connection close: ".$fd;
});

function responseJson($status = 1, $message = '', $data = array()) {
    $data = [
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
    return json_encode($data);
}
$serv->start();
