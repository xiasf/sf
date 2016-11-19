<?php
namespace Core;
/**
 * 日志处理类
 */
class Log {

    const ERROR = 'ERROR';      // 错误
    const INFO  = 'INFO';       // 信息
    const DEBUG = 'DEBUG';      // 调试
    const SQL   = 'SQL';        // SQL

    // 日志信息
    static protected $log     =  array();

    // 日志存储
    static protected $storage =   null;

    // 日志初始化
    static public function init($config = array()){
        $type   =   isset($config['type'])?$config['type']:'File';
        $class  =   strpos($type,'\\')? $type: 'Core\\Log\\Driver\\'. ucfirst(strtolower($type));           
        unset($config['type']);
        self::$storage = new $class($config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    static function record($message,$level=self::ERROR,$record=false) {
        if($record || false !== strpos(C('LOG_LEVEL'),$level)) {
            self::$log[] =   "{$level}: {$message}\r\n";
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function save($type='',$destination='') {
        if(empty(self::$log)) return ;

        if(empty($destination))
            $destination = C('LOG_PATH').date('y_m_d').'.log';
        if(!self::$storage){
            $type = $type?:C('LOG_TYPE');
            $class  =   'Core\\Log\\Driver\\'. ucfirst($type);
            self::$storage = new $class();            
        }
        $message    =   implode('',self::$log);
        self::$storage->write($message,$destination);
        // 保存后清空日志缓存
        self::$log = array();
    }

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function write($message,$level=self::ERROR,$type='',$destination='') {
        if(!self::$storage){
            $type = $type?:C('LOG_TYPE');
            $class  =   'Core\\Log\\Driver\\'. ucfirst($type);
            self::$storage = new $class();            
        }
        if(empty($destination))
            $destination = C('LOG_PATH').date('y_m_d').'.log';        
        self::$storage->write("{$level}: {$message}\r\n", $destination);
    }
}