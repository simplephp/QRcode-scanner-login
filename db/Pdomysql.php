<?php
/*
 * 采用单例模式
 *
 */
class Pdomysql {
	
	private static $_instance;
	private static $DB = null;
	private static $stmt = null;
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
	
	//禁止construct
	private function __construct(){}
	//禁止clone
	private function __clone(){}
	
	//获取对象,同时初始化DB 对象
	public static function getInstance() {
		if(!(self::$DB instanceof self)) {
			self::$DB = self::connect();
		}
		if(self::$_instance instanceof self) {
			return self::$_instance;
		}
		self::$_instance = new self;
		return self::$_instance;
	}
	/**
	 * 链接数据库
	 */
	public static function connect() {
		try {
			return new \PDO(self::$config['dbtype'].':host='.self::$config['dbhost'].';port='.self::$config['dbport'].';dbname='.self::$config['databases'], self::$config['dbuser'], self::$config['dbpass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.self::$config['charset'].''));
		} catch (\PDOException $e) {
			die("Connect Error Infomation:" . $e->getMessage());
		}
	}
	/**
	* 获取要操作的数据
	* 返回:合併后的SQL語句
	* 类型:字串
	*/
	private function getCode($table, $args) {
		$code = '';
		if (is_array ($args)) {
			foreach ($args as $k => $v ) {
				if ($v == '') {
					continue;
				}
			$code .= "`$k`='$v',";
			}
		}
		$code = substr($code, 0, - 1);
		return $code;
	}
	/**
	* execute 	执行 INSERT\UPDATE\DELETE
	* retun 	执行語句影响行数
	*/
	public static function execute($sql) {
		self::getPDOError($sql);
		return self::$DB->exec($sql);
	}
	/**
	* 修改数据
	* 返回:記录数
	* 类型:数字
	* 參数:$db->update($table,array('title'=>'Zxsv'),array('id'=>'1'),$where
	* ='id=3');
	*/
	public function update($table, $args, $where) {
		$code = self::getCode ( $table, $args );
		$sql = "UPDATE `$table` SET ";
		$sql .= $code;
		$sql .= " Where $where";
		return self::execute ( $sql );
	}
  
	/**
	* 作用:刪除数据
	* 返回:表內記录
	* 类型:数组
	* 參数:$db->delete($table,$condition = null,$where ='id=3')
	*/
	public static function delete($table, $where) {
		$sql = "DELETE FROM `$table` Where $where";
		return self::execute($sql);
	}	
	/**
	* getLastId 	获取最后一次 insert ID
	* retun void
	*/
	public static function getLastId() {
		return self::$DB->lastInsertId();
	}

	/**
	* optimizeTable 	优化表
	* @param string $table 	表名称
	*/
	public static function optimizeTable($table) {
		$sql = 'OPTIMIZE TABLE '.$table.'';
		self::execute($sql);
	}
	/**
	* 作用:获取單行数据
	* 返回:表內第一条記录
	* 类型:数组
	* 參数:$db->fetOne($table,$condition = null,$field = '*',$where ='')
	*/
	public function fetOne($table, $field = '*', $where = false) {
		$sql = "SELECT {$field} FROM `{$table}`";
		$sql .= ($where) ? " WHERE $where" : '';
		return self::_fetch ($sql, $type = '0');
	}
	/**
	* 作用:获取所有数据
	* 返回:表內記录
	* 类型:二維数组
	* 參数:$db->fetAll('$table',$condition = '',$field = '*',$orderby = '',$limit
	* = '',$where='')
	*/
	public function fetAll($table, $field = '*', $orderby = false, $where = false) {
		$sql = "SELECT {$field} FROM `{$table}`";
		$sql .= ($where) ? " WHERE $where" : '';
		$sql .= ($orderby) ? " ORDER BY $orderby" : '';
		return self::_fetch ( $sql, $type = '1' );
	}
	/**
	* 执行具体SQL操作
	* 返回:运行結果
	* 类型:数组
	*/
	private static function _fetch($sql, $type) {
		$result = array();
		self::$stmt = self::$DB->query($sql);
		self::getPDOError($sql);
		self::$stmt->setFetchMode(\PDO::FETCH_ASSOC);
		switch ($type) {
			case '0' :
			$result = self::$stmt->fetch();
		break;
			case '1' :
			$result = self::$stmt->fetchAll();
		break;
			case '2' :
			$result = self::$stmt->rowCount();
		break;
		}
		self::$stmt = null;
		return $result;
	}

	/**
	* setDebugMode 设置是否开启debug
	* @param bool $mode 	ture or false
	*/
	public static function setDebugMode($mode = true) {
		return ($mode == true) ? self::$debug = true : self::$debug = false;
	}

	/**
	* getPDOError 捕获PDO错误信息
	* @param string $sql 	mysql语句
	* @param string $sqlid 	ID
	* @param string $sql 	SQL语句
	* return string
	*/
	private static function getPDOError($sql) {
		self::$config['debug'] ? self::errorfile($sql) : '';
		if (self::$DB->errorCode() != '00000') {
			$info = (self::$stmt) ? self::$stmt->errorInfo() : self::$DB->errorInfo();
			echo (self::sqlError('mySQL Query Error', $info[2], $sql));
			exit ();
		}
	}
	
	private function getSTMTError($sql) {
		self::$config['debug'] ? self::errorfile ($sql) : '';
			if (self::$stmt->errorCode() != '00000') {
				$info = (self::$stmt) ? self::$stmt->errorInfo() : self::$DB->errorInfo();
				echo(self::sqlError('mySQL Query Error', $info [2], $sql));
				exit();
		}
	}

	/**
	* errorfile 写入日志
	* @param string $sql 	mysql语句
	* @param string $sqlid 	ID
	* @param string $sql 	SQL语句
	* return string
	*/
	private static function errorfile($sql) {
		$errorfile = './dberrorlog.php';
		$sql = str_replace(
			array(
				"\n",
				"\r",
				"\t",
				"  ",
				"  ",
				"  "
			),
			array(
				" ",
				" ",
				" ",
				" ",
				" ",
				" "
				), $sql);
		if (!file_exists($errorfile)) {
			$fp = file_put_contents ( $errorfile, "<?PHP exit('Access Denied'); ?>\n" . $sql );
		} else {
			$fp = file_put_contents ( $errorfile, "\n" . $sql, FILE_APPEND );
		}
	}

	/**
	* sqlError 运行错误信息
	* @param string $message 错误消息
	* @param string $sqlid 	ID
	* @param string $sql 	SQL语句
	* return string
	*/
	private static function sqlError($message = '', $sqlid = '', $sql = '') {
		$errorInfo = '';
		if($message) {
			$errorInfo .=  $message;
		}
		if($sqlid){
			$errorInfo .= 'SQLID: ' . $sqlid ;
		}
		if($sql) {
			$errorInfo .= 'ErrorSQL: ' . $sql;
		}
		throw new \Exception($errorInfo);
	}
	//关闭数据库
	public function close() {
		if(!self::$config['pconnect']) {
			self::$DB = null;
		}
	}	
	public function __destruct() {
		self::close();
	}	
}