var ws = {};
var client_id = 0;
// 定义自己的 websocket 地址
var config = {
    'server' : 'ws://192.168.1.131:9502',
    'flash_websocket' : true
}

$(document).ready(function () {
    //使用原生WebSocket
    if (window.WebSocket || window.MozWebSocket)
    {
        ws = new WebSocket(config.server);
    }
    //使用flash websocket
    else if (config.flash_websocket)
    {
        WEB_SOCKET_SWF_LOCATION = "./flash-websocket/WebSocketMain.swf";
        $.getScript("./flash-websocket/swfobject.js", function () {
            $.getScript("./flash-websocket/web_socket.js", function () {
                ws = new WebSocket(config.server);
            });
        });
    }
    //使用http xhr长轮循
    else
    {
        ws = new Comet(config.server);
    }
    listenEvent();
});

function listenEvent() {
    /**
     * 连接建立时触发
     */
    ws.onopen = function (e) {
        $("#status").html('正在连接swoole');
    };

    ws.onmessage = function (e) {
        var message = eval('('+ e.data +')');
        var method = message.data.method;
        if (method == 'connection')
        {
            if(message.data.status == 1) 
            {
                $("#status").html('连接swoole成功...');
                var msg = '{"method":"join","token":"'+ token +'"}';//'method':'join','token':'" + token + "'};
                ws.send(msg);

            }
        }
        else if (method == 'join')
        {
            if(message.data.status == 1) 
            {
               $("#status").html('等待验证...'); 
            }
        }
        else if (method == 'verify')
        {
            $("#status").html('校对用户完成.用户'+message.data.username+'已经登录'); 
        }
    };

    /**
     * 连接关闭事件
     */
    ws.onclose = function (e) {

    };

    /**
     * 异常事件
     */
    ws.onerror = function (e) {
        
    };
}