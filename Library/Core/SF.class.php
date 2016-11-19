<?php
namespace Core;
/**
 * SF 引导类
 * Update time：2015-11-5 11:46:42
 */
class SF {

    // 类映射
    private static $_map      = array();

    // 实例化对象
    private static $_instance = array();


    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    static public function start() {
        spl_autoload_register('Core\SF::autoload');
        register_shutdown_function('Core\SF::fatalError');
        set_error_handler('Core\SF::appError');
        set_exception_handler('Core\SF::appException');

        Storage::connect(STORAGE_TYPE);

        $mode = include is_file(CONF_PATH.'core.php') ? CONF_PATH . 'core.php' : MODE_PATH . APP_MODE . '.php';

        foreach ($mode['core'] as $file) {
            if (is_file($file)) {
            include $file;
            }
        }

        C('LOG_PATH', LOG_PATH . 'Common/');

        foreach ($mode['config'] as $key => $file){
            is_numeric($key) ? C(include $file) : C($key, include $file);
        }

        if (isset($mode['alias'])) {
            self::addMap(is_array($mode['alias']) ? $mode['alias'] : include $mode['alias']);
        }

        if(isset($mode['hook'])) {
            Hook::import(is_array($mode['hook']) ? $mode['hook'] : include $mode['hook']);
        }

        unset($mode);

        if (is_file(CONF_PATH . 'hook.php'))
            Hook::import(include CONF_PATH . 'hook.php');

        if (is_file(CONF_PATH . 'alias.php'))
            self::addMap(include CONF_PATH . 'alias.php');


        if (is_file(CONF_PATH . 'config_' . APP_MODE . '.php'))
            C(include CONF_PATH .'config_' . APP_MODE . '.php');

        if (APP_DEBUG && is_file(SF_PATH . 'Conf/debug.php'))
            C(include SF_PATH . 'Conf/debug.php');

        if (APP_DEBUG && is_file(CONF_PATH . 'debug.php'))
            C(include CONF_PATH . 'debug.php');

        if (APP_STATUS && is_file(CONF_PATH . APP_STATUS . '.php'))
            C(include CONF_PATH . APP_STATUS . '.php');


        self::load_ext_file(COMMON_PATH);


        if (is_file(SF_PATH . 'Lang/' . strtolower(C('DEFAULT_LANG')) . '.php'))
            L(include SF_PATH . 'Lang/' . strtolower(C('DEFAULT_LANG')) . '.php');

        if (is_file(LANG_PATH . strtolower(C('DEFAULT_LANG')) . '.php'))
            L(include LANG_PATH . strtolower(C('DEFAULT_LANG')) . '.php');

        date_default_timezone_set(C('DEFAULT_TIMEZONE'));

        G('loadTime');

        App::run();
    }


    /**
     * 加载动态扩展文件
     * @var string $path 文件路径
     * @return void
     */
    static public function load_ext_file($path) {
        // 加载自定义外部文件
        if($files = C('LOAD_EXT_FILE')) {
            $files =  explode(',', $files);
            foreach ($files as $file){
                $file = $path . 'Common/' . $file . '.php';
                if(is_file($file)) include $file;
            }
        }
        // 加载自定义的动态配置文件
        if($configs = C('LOAD_EXT_CONFIG')) {
            if(is_string($configs)) $configs =  explode(',', $configs);
            foreach ($configs as $key => $config){
                $file = is_file($config) ? $config : $path . 'Conf/' . $config . '.php';
                if(is_file($file)) {
                    is_numeric($key) ? C(include $file) : C($key, include $file);
                }
            }
        }
    }


    // 注册classmap
    static public function addMap($class, $map = '') {
        if (is_array($class)) {
            self::$_map = array_merge(self::$_map, $class);
        } else {
            self::$_map[$class] = $map;
        }        
    }


    // 获取classmap
    static public function getMap($class = '') {
        if ('' === $class){
            return self::$_map;
        } elseif (isset(self::$_map[$class])) {
            return self::$_map[$class];
        } else {
            return null;
        }
    }


    /**
     * 基于命名空间的类库自动加载（跨任意应用模块如此简单）
     * @param string $class 对象类名
     * @return void
     */
    public static function autoload($class) {
        if (isset(self::$_map[$class])) {
            include self::$_map[$class];
        } elseif (false !== strpos($class, '\\')) {
            $name = strstr($class, '\\', true);
            if (in_array($name, array('Core', 'Behavior', 'Org', 'Vendor'))) { 
                $path = LIB_PATH;
            } else {
              if (strpos($name, 'app_') === 0) {
                  $class = substr($class, strpos($class, '\\') + 1);
                  $path = PATH . substr($name, 4) . '/';             // 项目目录/应用目录
              } elseif (strpos($name, 'www') === 0) {
                  $class = substr($class, 4);
                  $path = ORG;                                       // 项目公共目录
              } else {
                  $namespace = C('AUTOLOAD_NAMESPACE');
                  $path = isset($namespace[$name]) ? dirname($namespace[$name]) . '/' : APP_PATH;
                  unset($namespace);
                }
            }
            $filename = $path . str_replace('\\', '/', $class) . '.class.php';
            if (is_file($filename))
                include $filename;
            else
                self::trace('SF autoload no file : '.$filename);
        }
    }


    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    static public function instance($class, $method = '') {
        $identify = $class . $method;
        if (!isset(self::$_instance[$identify])) {
            if (class_exists($class)) {
                $o = new $class();
                // if (!empty($method) && method_exists($o, $method))
                //     self::$_instance[$identify] = call_user_func(array(&$o, $method));
                // else
                    self::$_instance[$identify] = $o;
            }
            else
              E(L('_CLASS_NOT_EXIST_') . ':' . $class);
        }
        return self::$_instance[$identify];
    }


    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error            = array();
        $error['message'] = $e->getMessage();
        $error['code']    = $e->getCode();
        $trace            = $e->getTrace();

        if ('E' == $trace[0]['function']) {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        } else {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }
        $error['type']  = '抛出异常';
        $error['by']    = 'SF Exception';
        $error['trace'] = $e->getTraceAsString();

        $errorStr = 'type: SF Exception(抛出异常); code: ' . $error['code'] . '; message: ' . $error['message'] . '; file: ' . $error['file'] . '; line: ' . $error['line'];

        self::trace($errorStr, 'ERROR', true);

        self::halt($error);
    }


    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     * 不能扑捉到的级别 E_ERROR E_PARSE E_CORE_ERROR E_CORE_WARNING E_COMPILE_ERROR E_COMPILE_WARNING E_STRICT（实际上能扑捉到，只是扑捉不到在调用 set_error_handler()函数所在文件中产生的大多数 E_STRICT）
     * 能扑捉到的级别 E_WARNING E_NOTICE E_USER_ERROR E_USER_WARNING E_USER_NOTICE E_RECOVERABLE_ERROR E_DEPRECATED E_USER_DEPRECATED
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
        $errorInfo = FriendlyErrorType($errno);
        $errorStr  = 'SF appError type: ' . $errorInfo[0] . '(' . $errorInfo[1] . "); code: $errno; message: $errstr; file: " . $errfile . "; line: $errline";

        self::trace($errorStr, 'ERROR', true);

        if (E_USER_ERROR == $errno) {
            $e = array(
                'message' => $errstr,
                'file'    => $errfile,
                'line'    => $errline,
                'code'    => $errno,
                'type'    => $errorInfo[0] . '(' . $errorInfo[1] . ')',
                'by'      => 'SF appError',
              );
            self::halt($e);
        }
    }


    /**
     * 致命错误捕获
     * @return void
     */
    static public function fatalError() {
        if ($e = error_get_last()) {
            $errorInfo = FriendlyErrorType($e['type']);
            $errorStr  = 'SF fatalError type: ' . $errorInfo[0] . '(' . $errorInfo[1] . '); code: ' . $e['type'] . '; message: ' . $e['message'] . '; file: ' . $e['file'] . '; line: ' . $e['line'];
            self::trace($errorStr, 'ERROR', true);
            switch($e['type']) {
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:
                $e['code'] = $e['type'];
                $e['type'] = $errorInfo[0] . '(' . $errorInfo[1] . ')';
                $e['by']   = 'SF fatalError';
                Log::save();
                self::halt($e);
                break;
            }
        }
        Log::save();
    }


    /**
     * 错误输出
     * @param array $e 错误
     * @return void
     */
    static public function halt($e) {

        $buffering_clean = ob_get_clean();

        if (APP_DEBUG || IS_CLI) {

            if (!is_array($e_ = $e)) {
                $trace        = debug_backtrace();
                $e            = array();
                $e['message'] = $e_;
                $e['file']    = $trace[0]['file'];
                $e['line']    = $trace[0]['line'];
                unset($trace);
            }
            unset($e_);

            $buffering_clean && $e['buffering_clean'] = $buffering_clean;
            unset($buffering_clean);

            (!isset($e['message']) || $e['message'] == '') && $e['message'] = '未知';
            isset($e['file']) or $e['file'] = '未知';
            isset($e['line']) or $e['line'] = '未知';
            isset($e['type']) or $e['type'] = '未知';
            isset($e['code']) or $e['code'] = '未知';
            isset($e['by'])   or $e['by']   = '未知';

            if (!isset($e['trace'])) {
                ob_start();
                debug_print_backtrace();
                $e['trace'] = ob_get_clean();
            }

            if (IS_CLI)
              exit(iconv('UTF-8','gbk',
                        'message: '. $e['message']) . PHP_EOL .
                        'FILE: '   . $e['file']     . '(' . $e['line'].')' . PHP_EOL .
                        'type: '   . $errorInfo[0]  . '(' . $errorInfo[1] . ')'.PHP_EOL .
                        'code: '   . $e['type']     . PHP_EOL .
                        'by: '     . $e['by']       . PHP_EOL .
                        'trace: '  . $e['trace']    . 
                        ($e['buffering_clean'] ? 'buffering_clean: ' . $e['buffering_clean'] : '')
                  );

        } else {
            $error_page       = C('ERROR_PAGE');
            if (!empty($error_page)) {
                redirect($error_page);
            } else {

                if ($buffering_clean)
                    $e['buffering_clean'] = C('ERROR_BUFFERING_CLEAN') ? : '未知';
                unset($buffering_clean);

                $e['message'] = C('ERROR_MESSAGE') ? : '未知';
                $e['file']    = C('ERROR_FILE') ?    : '未知';
                $e['line']    = C('ERROR_LINE') ?    : '未知';
                $e['type']    = C('ERROR_TYPE') ?    : '未知';
                $e['code']    = C('ERROR_CODE') ?    : '未知';
                $e['trace']   = C('ERROR_TRACE') ?   : '未知';
                $e['by']      = 'SF';
            }
        }
        include C('TMPL_EXCEPTION_FILE');
        exit;
    }


    /**
     * 添加和获取页面Trace记录
     * @param mixed  $value 变量
     * @param string $level 日志级别(或者页面Trace的选项卡)
     * @param boolean $record 是否记录日志
     * @return void|array
     */
    static public function trace($value = '', $level = 'DEBUG', $record = false) {
        static $_trace = array();
        if ('' === $value) {
            return $_trace;
        } else {
            $info = print_r($value, true);
            $level = strtoupper($level);

            // 日志可选 页面记录必须 我们认为 AJAX请求 没有show trace 强制记录 时必须记录日志 
            if(APP_DEBUG || (defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE')  || $record)
                Log::record($info,$level,$record);

            if(!isset($_trace[$level]) || (C('TRACE_MAX_RECORD') && (count($_trace[$level]) > C('TRACE_MAX_RECORD'))))
                $_trace[$level] = array();

            $_trace[$level][] = $info;

            if ($level == 'ERROR' && $record === true) {
              // 错误处理钩子
              Hook::listen('app_error', $info);
            }

        }
    }
}