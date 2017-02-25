<?php
/**
 * memcache 扩展操作memcache缓存核心类库
 * 1.set 设置缓存
 * 2.get 获取缓存
 * 3.setKeyToValue 使用处理浏览量延迟更新，定时刷数据到数据表中非常有用
 * 缓存流量量，可按时间 更新 或设置按次数更新
 * @Version 1.0
 */
namespace Db;

class Memcache{
    public static $instance = NULL;
    public static $linkHandle = array();
    private $conf;
    
    // 哈希hashNum 粒度
    const hashNum = 100;
    // 哈希hashKeyPre key 前缀
    const hashKeyPre = 'views_delay_update_';
 
    /**
     * 初始化化，实例memcache
     * @param type $host
     * @param type $port
     */
    public function __construct($configs){
        $this->conf = $configs;
    }
    
    /**
     * Get a instance of MyRedisClient
     *
     * @param $configs
     * @return object
     */
    static function getInstance($configs){
        if (!self::$instance) {
            self::$instance = new self($configs);
        }
        return self::$instance;
    }
    /**
     * //设置Memcache set
     * 将key和value对应。如果key已经存在了，它会被覆盖，而不管它是什么类型。
     * 
     * @param type $key 键值
     * @param type $value 保存value
     * @param type $expire 超时时间
     */
    public function set($key, $value, $expire = 0) {
        $mc = $this->getMemcache($key);
        if(!is_object($mc)) {
            return false;
        }
        //MEMCACHE_COMPRESSED 设置压缩
        return $mc->set($key,$value, MEMCACHE_COMPRESSED, $expire);
    }
    /**
     * 删除一个Memcache key/value
     * 
     * @param type $key
     * @param type $expire
     * @return type
     */
    public function del($key, $expire = 0) {
        $mc = $this->getMemcache($key);
        if(!is_object($mc)) {
            return false;
        }
        return $mc->delete($key, $expire);
    }
    /**
     * 成功时返回 TRUE， 或者在失败时返回 FALSE. 
     */
    public function flush() {
        //仅清除所有，主库，会自动同步到从库
        foreach (self::$linkHandle['master'] as $var){
            $var->flush();
        }  
        return true;
    }
    /**
     * 返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回FALSE。 
     * 因为GET只处理string类型的values。
     * @param $key
     */
    public function get($key){
        $mc = $this->getMemcache($key, 'slave');
        if(!is_object($mc)) {
            return false;
        }
        return $mc->get($key);
    }
    
    /**
     * //使用到全局函数 mcServer 此函数初始化本类 
     * 
     * @param type $key
     * @param type $value
     * @param type $expire
     */
    public static function setMkey($key, $value, $expire = 0) {
        //$newKey = self::key;
        return mcServer($key, $value, $expire); 
    }
    /**
     * //使用到全局函数 mcServer 此函数初始化本类 
     * 
     * @param type $key
     * @return type
     */
    public static function getMkey($key) {
        return mcServer($key);
    }
    /**
     * 获得memcache Resources
     * 
     * @param $key   mem 存的key/或随机值
     * @param string $tag   master/slave
     */
    public function getMemcache($key = null, $tag = 'master'){
        //关闭 memcache 缓存
        if(MEMCACHE_TF == false) {
            return false;
        }
        if(!empty(self::$linkHandle[$tag])){
            return self::$linkHandle[$tag];
        }

        $key = empty($key) ? uniqid() : $key;
        $mcArr  = $this->conf[$tag];
        //获得相应主机的数组下标
        $arrIndex = $this->getHostByHash($key,count($this->conf[$tag])); 

        $obj = new Memcache;
        $obj->connect($mcArr[$arrIndex]['host'],$mcArr[$arrIndex]['port']);
        self::$linkHandle[$tag] = $obj;

        return $obj;
    }
    
    //获取Memcache版本信息
    public function getVersion() {
        return $this->getMemcache($key,'slave')->getVersion();
    }
    /**
     * 随机取出主机
     * @param $key      $变量key值
     * @param $n        主机数
     * @return string
     */
    private function getHostByHash($key,$n){
        if($n < 2) {
            return 0;
        }
        $id = crc32($key) >> 16 & 0x7FFFFFFF;
        return intval(fmod($id, $n));
    }
    
    /**
     * 关闭连接
     * pconnect 连接是无法关闭的
     *  
     * @param int $flag 关闭选择 0:关闭 Master 1:关闭 Slave 2:关闭所有
     * @return boolean
     */
    public function close($flag = 2){
        switch($flag){
            // 关闭 Master
            case 0:
                foreach (self::$linkHandle['master'] as $var){
                    $var->close();
                }
            break;
            // 关闭 Slave
            case 1:
                foreach (self::$linkHandle['slave'] as $var){
                    $var->close();
                }
            break;
            // 关闭所有
            case 2:
                $this->close(0);
                $this->close(1);
            break;
        }
        return true;
    }
    
    /**
     * 哈希 散列到队列中，根据key 获取hash 值
     * PS 负载均衡可以考虑驶入如下函数
     * @param type $key
     * @return int hashNum 对应的key值
     */
    public static function hash32($key) {
        $id = crc32($key) >> 16 & 0x7FFFFFFF;
        return intval(fmod($id, self::hashNum));
    }
    
    /**
     * 浏览量延迟更新，塞到mem hash散列Key value中，再调用定时任务 或者事件触发更新到数据库
     * @param type $key
     * @return boolean
     */
    public static function setKeyToValue($key) {
        //获取散列 num 
        $num = self::hash32($key);
        $hashKey = self::hashKeyPre . $num;
        //获取当前缓存的memcache值
        $getArr = self::getMkey($hashKey);
        $keyArr = empty($getArr) ? array() : $getArr;
        // key 不存在数组中，入栈,设置memcache值
        if(!in_array($key, $keyArr)){
            array_push($keyArr, $key);
            self::setMkey($hashKey, $keyArr);
            return true;
        } 
        return false;
    }
}
