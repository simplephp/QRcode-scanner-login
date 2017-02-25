>**Android 客户端扫描网页端二维码实现登录**


----------

> 主要涉及技术和类库

 1.php  phpqrcode 类库 PHP生成二维码
 2.php  swoole 扩展
 3.php  redis 扩展
 4.js  websocket 客户端
 5.android  zxing 类库扫描二维码
 6.android okhttp 网络类库
 7.mysql 储存数据
 注意：最终实现为 DEMO 版本，切勿拿到生产环境上使用。
 
 

----------


> 文件列表介绍
> 

 1.QRScanner.apk android扫描二维码客户端(小米2S上测试了，哈哈其他不知道兼容不，毕竟不是写android的)
 2.api.php android 请求接口处理，并且转发 UDP 到 api_server.php
 3.api_server.php  服务端处理
 4.qrlogin.sql 数据库配置文件
 
 

> 基础配置
	> 

 1.数据库连接配置
	 ./db/Pdomysql.clss
	
		```
		private static $config = array(
			'dbtype' => 'mysql',
			'dbhost' => '127.0.0.1',
			'dbuser' => 'root',
			'dbpass' => '123456',
			'databases' => 'demo',
			'dbport' => '3306',
			'charset' => 'UTF8',
			'pconnect' => false,
			'debug' => true,
		);
		数据库根据自己的环境配置，这垃圾代码，大家将就吧
	```
  2.文件权限
     ./temp 读写权限（linux）

  3.修改index.php 的token 地址配置
  
		```
		 $tokenURL = 'http://your_ip/qrlogin/api.php?token='.$uuid;
		```
  4.配置 api_server.php swoole_websocket_server、Redis、UDP监听地址和端口
		
		``` 
		$serv = new swoole_websocket_server("0.0.0.0", 9502);
		$redis = new Redis();
		$redis->connect('127.0.0.1', 6379);

		//这里监听了一个UDP端口用来 监听客服端 token 请求的
		$serv->addlistener('0.0.0.0', 9503, SWOOLE_SOCK_UDP);
		```
        根据自己的实际环境做修改
		

> 基本实现原理（没有啥技术含量）
	> 

 1.浏览网页(index.php)，生成 Token(UUID也可以这么说)，保存到 MySql 数据库或者缓存也可以，有Redis
 嘛，websocket 连接到 swoole，等待推送信息
 2.客户端扫描二维码里面的Token,Android 客户端(用户登录状态)携带用户名和Token或者其他标识
 请求api.php做校验更新数据库,api.php 发送 UDP 请求到 api_server.php
 3.api_server.php 接受到 UDP 请求处理后将登录信息推送到网页客户端
 

> 写到最后
	> 

 实现原理简单，有安全问题，实现的一种思路，不在个位看官面前班门弄斧，学海无涯，so 写代码去吧
