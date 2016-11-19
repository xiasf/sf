<?php
/**
 * SF 系统公共函数库（基础必须）
 * @category SF
 * @package  Common
 * @author   xiak <811800545@qq.com> 爱生活，爱简单！
 * Update time：2016-2-16 17:35:12
 */

/**
 * 根据错误代码返回错误信息
 * @param int $code 开始标签
 * @return mixed
 */
function FriendlyErrorType($code) {
    // 这些级别将停止 E_ERROR E_PARSE E_CORE_ERROR E_COMPILE_ERROR E_USER_ERROR
    // 自定义捕捉不到的级别 E_ERROR E_PARSE E_CORE_ERROR E_CORE_WARNING E_COMPILE_ERROR E_COMPILE_WARNING E_STRICT
    // 能捕捉到的级别 E_WARNING E_NOTICE E_USER_ERROR E_USER_WARNING E_USER_NOTICE E_RECOVERABLE_ERROR E_DEPRECATED E_USER_DEPRECATED
    switch ($code) {
        case E_ERROR:                       
            $error = array('E_ERROR', '致命错误');                   // 1 致命的运行错误。错误无法恢复，暂停执行脚本。
            break;
        case E_WARNING:                     
            $error = array('E_WARNING', '警告');                     // 2 运行时警告(非致命性错误)。非致命的运行错误，脚本执行不会停止。
            break;
        case E_PARSE:                       
            $error = array('E_PARSE', '解析错误');                   // 4 编译时解析错误。解析错误只由分析器产生。
            break;
        case E_NOTICE:                      
            $error = array('E_NOTICE', '提醒');                      // 8 运行时提醒(这些经常是你代码中的bug引起的，也可能是有意的行为造成的。)
            break;
        case E_CORE_ERROR:                 
            $error = array('E_CORE_ERROR', 'php启动致命错误');        // 16 PHP启动时初始化过程中的致命错误。
            break;
        case E_CORE_WARNING:                
            $error = array('E_CORE_WARNING', 'php启动警告');          // 32 PHP启动时初始化过程中的警告(非致命性错)。
            break;
        case E_COMPILE_ERROR:               
            $error = array('E_COMPILE_ERROR', '编译致命错误');        // 64 编译时致命性错。这就像由Zend脚本引擎生成了一个E_ERROR。
            break;
        case E_COMPILE_WARNING:             
            $error = array('E_COMPILE_WARNING', '编译时警告');        // 128 编译时警告(非致命性错)。这就像由Zend脚本引擎生成了一个E_WARNING警告。
            break;
        case E_USER_ERROR:                  
            $error = array('E_USER_ERROR', '自定义错误');             // 256 用户自定义的错误消息。这就像由使用PHP函数trigger_error（程序员设置E_ERROR）
            break;
        case E_USER_WARNING:                
            $error = array('E_USER_WARNING', '自定义警告');           // 512 用户自定义的警告消息。这就像由使用PHP函数trigger_error（程序员设定的一个E_WARNING警告）
            break;
        case E_USER_NOTICE:                 
            $error = array('E_USER_NOTICE', '自定义提醒');            // 1024 用户自定义的提醒消息。这就像一个由使用PHP函数trigger_error（程序员一个E_NOTICE集）
            break;
        case E_STRICT:                      
            $error = array('E_STRICT', '编码标准警告');               // 2048 编码标准化警告。允许PHP建议如何修改代码以确保最佳的互操作性向前兼容性。
            break;
        case E_RECOVERABLE_ERROR:           
            $error = array('E_RECOVERABLE_ERROR', '致命错误');        // 4096 能被自定义错误处理捕捉到的致命错误。这就像一个E_ERROR，但可以通过用户定义的处理捕获（又见set_error_handler（））
            break;
        case E_DEPRECATED:                  
            $error = array('E_DEPRECATED', '通知');                   // 8192  运行时通知。启用后将会对在未来版本中可能无法正常工作的代码给出警告。 since PHP 5.3.0
            break;
        case E_USER_DEPRECATED:             
            $error = array('E_USER_DEPRECATED', '用户通知信息');       // 16384 用户产少的警告信息。 类似 E_DEPRECATED, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的。 since PHP 5.3.0
            break;
        case E_ALL:                         
            $error = array('E_ALL', 'E_ALL');                          // 8191,30719,6143 所有的错误和警告(不包括 E_STRICT) (E_STRICT will be part of E_ALL as of PHP 6.0)
            break;
        default:                            
            $error = array('E_NOLL', '未知错误');                      // 未知错误
    }
    return $error;
}


/**
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function G($start, $end = '', $dec = 6) {
    static $time = array();
    static $mem = array();
    if (is_float($end)) {
        $time[$start] = $end;
    } elseif (!empty($end)) {
        if (!isset($time[$end])) $time[$end] = microtime(true);
        if (MEMORY_LIMIT_ON && $dec === 'm')
            return number_format(((isset($mem[$end]) ?: memory_get_usage()) -$mem[$start]) / 1024);
        else
            return !$dec ? ($time[$end] - $time[$start]) : number_format(($time[$end] - $time[$start]), $dec);

    } else {
        $time[$start] = microtime(true);
        if (MEMORY_LIMIT_ON) $mem[$start] = memory_get_usage();
    }
    return null;
}


/**
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @param boolean $save 是否保存结果
 * @return mixed
 */
function N($key, $step = 0, $save = false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save) ? S('N_' . $key) : 0;
    }
    if (empty($step)) {
        return $_num[$key];
    } else {
        $_num[$key] = $_num[$key] + (float) $step;
    }
    if (false !== $save) {
        S('N_'.$key,$_num[$key], $save);
    }
    return null;
}


/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @throws Think\Exception
 * @return void
 */
function E($msg = '', $code=0) {
    throw new Core\Exception($msg, $code);
}


/**
 * 给个目录，帮你删除里面的所有文件，不会删除目录的
 * @param  string  $dir 相对的或绝对路径
 * @return 	void
 */
function delfile($dir) {
	if (strrpos(substr($dir, -1), '/') === 0)
		$dir = substr($dir, 0, -1);
	if (!file_exists($dir))
		return ;
	if ($dir_handle = opendir($dir)) {
		while ($filename = readdir($dir_handle)) {
			if ($filename != '.' && $filename != '..') {
				$subfile = $dir . '/' . $filename;
				if (is_dir($subfile)) {
					delfile($subfile);		// 如果是目录则递归调用
				} elseif (is_file($subfile)) {
					unlink($subfile);		// 删除文件
				}
			}
		}
		closedir($dir_handle);				// 关闭目录资源
	}
}


/**
 * 获取和设置语言定义(不区分大小写)
 * @param string|array $name 语言变量
 * @param mixed $value 语言值或者变量
 * @return mixed
 */
function L($name=null, $value=null) {
    static $_lang = array();
    // 空参数返回所有定义
    if (empty($name))
        return $_lang;
    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name)) {
        $name   =   strtoupper($name);
        if (is_null($value)){
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        }elseif(is_array($value)){
            // 支持变量
            $replace = array_keys($value);
            foreach($replace as &$v){
                $v = '{$'.$v.'}';
            }
            return str_replace($replace,$value,isset($_lang[$name]) ? $_lang[$name] : $name);        
        }
        $_lang[$name] = $value; // 语言定义
        return null;
    }
    // 批量定义
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return null;
}


/**
 * 获取和设置配置参数，支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value       配置值
 * @param mixed $default     默认值
 * @return mixed
 */
function C($name = null, $value = null, $default = null) {
    static $_config = array();

    if (empty($name)) return $_config;	// 无参数时获取所有

    if (is_string($name)) {

        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            else
	            $_config[$name] = $value;
            return null;
        }

        $name = explode('.', $name);	// 二维数组设置和获取支持
        $name[0]   =  strtoupper($name[0]);

        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        else
	        $_config[$name[0]][$name[1]] = $value;
        return null;
    }

    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return null;
    }

    return null; // 避免非法参数
}


/**
 * 非常酷的"重定向运行"函数，默认重定向到当前模块，当前控制器，默认操作。（不带参数，只负责跳到任意操作）
 * 它太强大了，几乎当你想要使用智能重定向的时候你都能想到它。（为了解决php函数传参的不爽，为了更自由，我们做了一点小小工作）
 * @param  string $m 模块 		默认为当前的
 * @param  string $c 控制器 	默认为当前的
 * @param  string $a 操作 		默认为index
 * @return 	void
 */
function H($m = MODULE_NAME, $c = CONTROLLER_NAME, $a = 'index') {
	/**
	 * 只能用这种办法解决传参问题了（例如我们只想传a，但前面两个又不想写，那就写''代表默认的吧，当然了这只是我们想偷懒的一个办法而已）（当然你也可以说把M和C放到后面不就行了吗，但是我们想要M,C,A这种结构性）
	 * php的传参就是有这点不爽，没有办法让我们只传最想要的参数，达不到我们理想的效果，所以只有自己动手做了
	 * 解决php实参与形参传递时的不自由，没点想象空间，阻抗……？
	 */

	// 如果发现第一个参数是'no-ob'那么它还还有这个禁止客户端缓存的功能哦，禁止客户端缓存在某些时候有大作用哦
	if ($m == 'no-ob') {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pramga: no-cache");
		return;
	}

	if ($m == 'utf-8') {
		return;
	}

	if ($m == '401') {
		header('HTTP/1.1 401 Unauthorized');
		header('status: 401 Unauthorized');
		exit;
	}

	if ($m == '404') {
		header('HTTP/1.1 404 Not Found');
		header("status: 404 Not Found");
		exit;
	}

	if ($m == '') $m = MODULE_NAME;
	if ($c == '') $c = CONTROLLER_NAME;
	$href = U($m . '/' . $c . '/' . $a);
	header('HTTP/1.1 301 Moved Permanently');
	header("status: 301 Moved Permanently");
	header("Location: $href");
	exit;
}


/**
 * 模板编译，压缩（我们的压缩始终能都能同时压缩php html js css）
 * 我们的口号是：将压缩进行到底！不放过一个多余的空格，珍惜每一个字节的资源。
 * 不论怎样我们总是先考虑安全，性能，其实是使用最人性化的设计……
 * @param   string   $content		待处理的串
 * @return 	string 	 $content		处理好的串
 */
function HTML_TPL($content) {
	// 模板编译文件是一个php文件，所以处理它跟处理php文件差不多
	$content = preg_replace('/(;|,|:\s+|p|\(|\)|\{|\}|\s+)\s*(\/\/).*/', '\1', $content);			// 去除单行//，模板中请不要使用#注释

	$content = preg_replace('/(\r|\n|\t)/', ' ', $content);											// 去除所有的回车or换行字符和tab

	/*为减少不必要的麻烦所以先合并连续的块注释*/
	$content = preg_replace('/(\*)+\/\s*\/(\*)+/', '*', $content);

	/*去掉单行或多行注释，这里有个规则，此类注释里面不能出现 （不敢写啊，自己看）目前暂时这样，想好了在解决这个问题。 已解决看见没，后面加了个?号，表示非贪婪匹配哈*/
	$content = preg_replace('/(;|,|:\s+|p|\(|\)|\{|\}|\s+)\s*\/(\*)+.*?(\*)+\//', '\1', $content);

	// 再来一个彻底去除所有非程序语句的不必要的空格，没有比这个更吊的了。（html标签部分主要是在这里压缩了）(  .xiao_list:hover   .xiao_x这是给css开点特权——css可以通过三个或以上的空格来避免被干掉啊，现已优化得更智能，可以分辨出运算符了，不需要这么古怪的规则了)
	// 这一切都是按照我们规范书写代码来设计的，事实上我们的代码就是严格遵循规范的
	// 括号可能有点问题比如背景中，但是浏览器似乎能容许这个小错误或者不规范吧，所以这个问题不大，我们只兼容会出问题的部分就可以了
	$content = preg_replace('/\s*(\(|\)| ;| \{| \}|<|>|,|!|\?|:|\.=|\+|-=|\*|\/|%=|=|&|\|)\s*/', '\1', $content);
	$content = preg_replace('/\s*(-|\.|%)\s+/', '\1', $content);			// 为了能适配css只能单独作出这种情况的适配了（.是为了类选择器，-是为了css定位，%是为了css值，当然随着情况的复杂性的提高，这种适配可能会有不断的改变和更新的）（括号好像问题不大）（括号在ie里面有问题，所以为了统一调整，我们规定，所有css有带括号属性时，括号必须是写在最后的，这样就能避免这个问题了，但并不是所有都是这样的，对于背景图片没有这个问题，而边框RGB颜色则可能有这个问题）
	// 还有一个问题，我们网页压缩了，那么js可能和之前的也有不同了（节点问题），这个需要注意下，不用再为这个兼容性担心了，压缩的代码没有这个问题

	$content = preg_replace('/[ ]{2,}/', ' ', $content);					// 最后过滤唯一剩下两个及以上的基本空白符-空格

	$content = preg_replace('/<!--.*?-->/', '', $content);				// 去除html注释

	return $content;
}


/**
 * 获取客户端IP地址（从tp那儿copy过来的一个函数）
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown',$arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type = 0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {return strtoupper($match[1]);}, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}


/**
 * 解析资源地址并导入类库文件
 * 例如 module/controller addon://module/behavior
 * @param string $name 资源地址 格式：[扩展://][模块/]资源名
 * @param string $layer 分层名称
 * @param integer $level 控制器层次
 * @return string
 */
function parse_res_name($name, $layer, $level = 1) {
    if (strpos($name, '://')) {
        // 指定扩展资源
        list($extend, $name) = explode('://', $name);
    } else {
        $extend = '';
    }
    if (strpos($name, '/') && substr_count($name, '/') >= $level) {
        // 指定模块
        list($module, $name) = explode('/', $name, 2);
    } else {
        $module = defined('MODULE_NAME') ? MODULE_NAME : '';
    }
    $array = explode('/', $name);

    $class = $module . '\\' . $layer;
    foreach ($array as $name) {
        $class .= '\\' . parse_name($name, 1);
    }
    // 导入资源类库
    if ($extend) {
        // 扩展资源
        $class = $extend . '\\' . $class;
    }
    return $class . $layer;
}


/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (is_file($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}


/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($class, $baseUrl = '', $ext=EXT) {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut     = explode('/', $class);
    if (empty($baseUrl)) {
        if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
            //加载当前模块的类库
            $baseUrl = MODULE_PATH;
            $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
        }elseif (is_dir(LIB_PATH.$class_strut[0])) {
            // 系统类库包和第三方类库包
            $baseUrl = LIB_PATH;
        }else { // 加载其他模块的类库
            $baseUrl = APP_PATH;
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl    .= '/';
    $classfile       = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)) {
        // 如果类不存在 则导入类库文件
        return require_cache($classfile);
    }
}


/**
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 * @param string $name 函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return void
 */
function load($name, $baseUrl='', $ext='.php') {
    $name = str_replace(array('.', '#'), array('/', '.'), $name);
    if (empty($baseUrl)) {
        if (0 === strpos($name, '@/')) {
            //加载当前项目函数库
            $baseUrl    = COMMON_PATH.'Common/';
            $name       = substr($name, 2);
        } else {
            //加载ThinkPHP 系统函数库
            $baseUrl    = EXTEND_PATH . 'Function/';
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl       .= '/';
    require_cache($baseUrl . $name . $ext);
}


/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
 * @param string $class 类库
 * @param string $baseUrl 基础目录
 * @param string $ext 类库后缀
 * @return boolean
 */
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl))
        $baseUrl = VENDOR_PATH;
    return import($class, $baseUrl, $ext);
}


/**
 * 实例化模型类 格式 [资源://][模块/]模型
 * @param string $name 资源地址
 * @param string $layer 模型层名称
 * @return Core\Model
 */
function D($name = '', $layer = '') {
    if (empty($name))
        return new Core\Model;
    static $_model = array();
    $layer         = $layer ? : C('DEFAULT_M_LAYER');
    if (!isset($_model[$name . $layer])) {
    	$class = parse_res_name($name, $layer);
	    if (class_exists($class)) {
	        $_model[$name . $layer] = new $class(basename($name));
	    } elseif (false === strpos($name, '/')) {
	        $class = '\\Common\\' . $layer . '\\' . $name . $layer;
	        $_model[$name . $layer] = class_exists($class) ? new $class($name) : new Core\Model($name);
	    } else {
	    	E('D方法实例化没找到模型类' . $class);
	    }
    }
    return $_model[$name . $layer];
}


/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Core\Model
 */
function M($name = '', $tablePrefix = '', $connection = '') {
    static $_model = array();
    if (strpos($name, ':'))
        list($class, $name) = explode(':', $name);
    else
        $class = 'Core\\Model';
    $guid = (is_array($connection) ? implode('', $connection) : $connection) . $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name, $tablePrefix, $connection);
    return $_model[$guid];
}


/**
 * 添加和获取页面Trace记录
 * @param string $value 信息
 * @param string $level 日志类型
 * @param boolean $record 是否强制记录日志
 * @return void|array
 */
function trace($value = '', $level = 'DEBUG', $record = false) {
    return Core\SF::trace($value, $level, $record);
}


// 不区分大小写的in_array实现
function in_array_case($value,$array){
    return in_array(strtolower($value),array_map('strtolower',$array));
}


/**
 * 用于实例化访问控制器
 * @param string $name 控制器名
 * @param string $path 控制器命名空间（路径）
 * @return Think\Controller|false
 */
function controller($name,$path=''){
    $layer  =   C('DEFAULT_C_LAYER');
    $class  =   ( $path ? basename(ADDON_PATH).'\\'.$path : MODULE_NAME ).'\\'.$layer;
    $array  =   explode('/',$name);
    foreach($array as $name){
        $class  .=   '\\'.parse_name($name, 1);
    }
    $class .=   $layer;
    if(class_exists($class)) {
        return new $class();
    }else {
        return false;
    }
}


function think_filter(&$value) {
	// TODO 其他安全过滤

	// 过滤查询特殊字符
    if(preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i',$value)){
        $value .= ' ';
    }
}


/**
 * 获取模版文件 格式 资源://模块@主题/控制器/操作
 * @param string $template 模版资源地址
 * @param string $layer 视图层（目录）名称
 * @return string
 */
function T($template = '', $layer = '') {
    (false === strpos($template, '://')) && $template = 'http://' . str_replace(':', '/', trim($template, '/'));

    $info   = parse_url(trim($template, '/'));
    $extend = $info['scheme'];
    $module = isset($info['user']) ? $info['user'] : MODULE_NAME;
    $file   = $info['host'] . (isset($info['path']) ? $info['path'] : '');
    $layer  = $layer ? $layer : C('DEFAULT_TMPL');

    if ($auto = C('AUTOLOAD_NAMESPACE') && isset($auto[$extend])) {
        $baseUrl = $auto[$extend] . $module . '/' . $layer . '/';
    } else {
		defined('TMPL_PATH') or define('TMPL_PATH', COMMON_PATH . $layer . '/');
        $baseUrl = TMPL_PATH . DEVICE . '/' . $module . '/';
    }

    $theme = substr_count($file, '/') < 2 ? (defined('THEME_NAME') ? THEME_NAME : C('DEFAULT_THEME')) : '';

    $depr = C('TMPL_FILE_DEPR');
    if ('' == $file) {
        $file = CONTROLLER_NAME . $depr . ACTION_NAME;
    } elseif (false === strpos($file, '/')) {
        $file = CONTROLLER_NAME . $depr . $file;
    } elseif ('/' != $depr) {
        $file = substr_count($file, '/') > 1 ? substr_replace($file, $depr, strrpos($file, '/'), 1) : str_replace('/', $depr, $file);
    }

    return $baseUrl . ($theme ? $theme . '/' : '') . $file . C('TMPL_TEMPLATE_SUFFIX');
}


/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value='', $path=DATA_PATH) {
    static $_cache  =   array();
    $filename       =   $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            if(false !== strpos($name,'*')){
                return false; // TODO 
            }else{
                unset($_cache[$name]);
                return Core\Storage::unlink($filename,'F');
            }
        } else {
            Core\Storage::put($filename,serialize($value),'F');
            // 缓存数据
            $_cache[$name]  =   $value;
            return null;
        }
    }
    // 获取缓存数据
    if (isset($_cache[$name]))
        return $_cache[$name];
    if (Core\Storage::has($filename,'F')){
        $value      =   unserialize(Core\Storage::read($filename,'F'));
        $_cache[$name]  =   $value;
    } else {
        $value          =   false;
    }
    return $value;
}


/**
 * 获取输入参数
 * @param string $name   变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter  参数过滤方法
 * @param mixed $datas   要获取的额外数据源
 * @return mixed
 */
function I($name, $default = '', $filter = null, $datas = null) {
    static $_PUT = null;
    if (strpos($name, '/')) {
        list($name, $type) = explode('/', $name, 2);
    }
    if (strpos($name, '.')) {
        list($method, $name) = explode('.', $name, 2);
    } else {
        $method = 'get';
    }
    switch (strtolower($method)) {
        case 'get':
            $input = &$_GET;
            break;
        case 'post':
            $input = &$_POST;
            break;
        case 'put':
            if (is_null($_PUT)) {
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input = $_PUT;
            break;
        case 'sf_auto':
            switch ($_SERVER['REQUEST_METHOD']) {
            	case 'GET':
                    $input = $_GET;
                    break;
                case 'POST':
                    $input = $_POST;
                    break;
                case 'PUT':
                    if (is_null($_PUT)) {
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input = $_PUT;
                    break;
                default:
                    $input = $_GET;
            }
            break;
        case 'path':
            $input = array();
            if (!empty($_SERVER['PATH_INFO'])) {
                $depr  = C('URL_PATHINFO_DEPR');
                $input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
            }
            break;
        case 'request':
            $input = &$_REQUEST;
            break;
        case 'session':
            $input = &$_SESSION;
            break;
        case 'cookie':
            $input = &$_COOKIE;
            break;
        case 'server':
            $input = &$_SERVER;
            break;
        case 'globals':
            $input = &$GLOBALS;
            break;
        case 'data':
            $input = &$datas;
            break;
        default:
            return null;
    }
    if ('' == $name) {
        // 获取全部变量
        $data    = $input;
        $filters = isset($filter) ? $filter : C('DEFAULT_FILTER');
        if ($filters) {
            if (is_string($filters)) {
                $filters = explode(',', $filters);
            }
            foreach ($filters as $filter) {
                $data = arrayMapRecursive($filter, $data); // 参数过滤
            }
        }
    } elseif (isset($input[$name])) {
        $data    = $input[$name];

        if (MAGIC_QUOTES_GPC && ($input == $_GET || $input == $_POST))
        	$data = is_array($data) ? array_map('stripslashes', $data) : stripslashes($data);

        $filters = isset($filter) ? $filter : C('DEFAULT_FILTER');
        if ($filters) {
            if (is_string($filters)) {
                if (0 === strpos($filters, '/')) {
                    if (1 !== preg_match($filters, (string) $data)) {
                        return isset($default) ? $default : null;
                    }
                } else {
                    $filters = explode(',', $filters);
                }
            } elseif (is_int($filters)) {
                $filters = array($filters);
            }

            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    if (function_exists($filter)) {
                        $data = is_array($data) ? arrayMapRecursive($filter, $data) : $filter($data); // 参数过滤
                    } else {
                        $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                        if (false === $data) {
                            return isset($default) ? $default : null;
                        }
                    }
                }
            }
        }
        if (!empty($type)) {
            switch (strtolower($type)) {
                case 'a':
                    $data = (array) $data;
                    break;
                case 'd':
                    $data = (int) $data;
                    break;
                case 'f':
                    $data = (float) $data;
                    break;
                case 'b':
                    $data = (boolean) $data;
                    break;
                case 's':
                default:
                    $data = (string) $data;
            }
        }
    } else {
        $data = isset($default) ? $default : null;
    }
    is_array($data) && array_walk_recursive($data, 'think_filter');
    return $data;
}


function arrayMapRecursive($filter, $data) {
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val) ? arrayMapRecursive($filter, $val) : call_user_func($filter, $val);
    }
    return $result;
}


/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}


/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url = '', $vars = '', $suffix = true, $domain = false) {
    // 解析URL
    $info = parse_url($url);
    $url  = !empty($info['path']) ? $info['path'] : ACTION_NAME;
    if (isset($info['fragment'])) {
        // 解析锚点
        $anchor = $info['fragment'];
        if (false !== strpos($anchor, '?')) {
            // 解析参数
            list($anchor, $info['query']) = explode('?', $anchor, 2);
        }
        if (false !== strpos($anchor, '@')) {
            // 解析域名
            list($anchor, $host) = explode('@', $anchor, 2);
        }
    } elseif (false !== strpos($url, '@')) {
        // 解析域名
        list($url, $host) = explode('@', $info['path'], 2);
    }
    // 解析子域名
    if (isset($host)) {
        $domain = $host . (strpos($host, '.') ? '' : strstr($_SERVER['HTTP_HOST'], '.'));
    } elseif (true === $domain) {
        $domain = $_SERVER['HTTP_HOST'];
        if (C('APP_SUB_DOMAIN_DEPLOY')) {
            // 开启子域名部署
            $domain = 'localhost' == $domain ? 'localhost' : 'www' . strstr($_SERVER['HTTP_HOST'], '.');
            // '子域名'=>array('模块[/控制器]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                $rule = is_array($rule) ? $rule[0] : $rule;
                if (false === strpos($key, '*') && 0 === strpos($url, $rule)) {
                    $domain = $key . strstr($domain, '.'); // 生成对应子域名
                    $url    = substr_replace($url, '', 0, strlen($rule));
                    break;
                }
            }
        }
    }

    // 解析参数
    if (is_string($vars)) {
        // aaa=1&bbb=2 转换成数组
        parse_str($vars, $vars);
    } elseif (!is_array($vars)) {
        $vars = array();
    }
    if (isset($info['query'])) {
        // 解析地址里面参数 合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }

    // if (DEVICE == 'wap')
         // $vars = array_merge($vars, array('device' => 'wap'));

    // URL组装
    $depr    = C('URL_PATHINFO_DEPR');
    $urlCase = C('URL_CASE_INSENSITIVE');
    if ($url) {
        if (0 === strpos($url, '/')) {
        // 定义路由
            $route = true;
            $url   = substr($url, 1);
            if ('/' != $depr) {
                $url = str_replace('/', $depr, $url);
            }
        } else {
            if ('/' != $depr) {
                // 安全替换
                $url = str_replace('/', $depr, $url);
            }
            // 解析模块、控制器和操作
            $url                 = trim($url, $depr);
            $path                = explode($depr, $url);
            $var                 = array();
            $varModule           = C('VAR_MODULE');
            $varController       = C('VAR_CONTROLLER');
            $varAction           = C('VAR_ACTION');
            $var[$varAction]     = !empty($path) ? array_pop($path) : ACTION_NAME;
            $var[$varController] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;
            if ($maps = C('URL_ACTION_MAP')) {
                if (isset($maps[strtolower($var[$varController])])) {
                    $maps = $maps[strtolower($var[$varController])];
                    if ($action = array_search(strtolower($var[$varAction]), $maps)) {
                        $var[$varAction] = $action;
                    }
                }
            }
            if ($maps = C('URL_CONTROLLER_MAP')) {
                if ($controller = array_search(strtolower($var[$varController]), $maps)) {
                    $var[$varController] = $controller;
                }
            }
            if ($urlCase) {
                $var[$varController] = parse_name($var[$varController]);
            }
            $module = '';

            if (!empty($path)) {
                $var[$varModule] = implode($depr, $path);
            } else {
                if (C('MULTI_MODULE')) {
                    if (MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')) {
                        $var[$varModule] = MODULE_NAME;
                    }
                }
            }
            if ($maps = C('URL_MODULE_MAP')) {
                if ($_module = array_search(strtolower($var[$varModule]), $maps)) {
                    $var[$varModule] = $_module;
                }
            }
            if (isset($var[$varModule])) {
                $module = $var[$varModule];
                unset($var[$varModule]);
            }

        }
    }

    if (C('URL_MODEL') == 0) {
        // 普通模式URL转换
        $url = __APP__ . '?' . C('VAR_MODULE') . "={$module}&" . http_build_query(array_reverse($var));
        if ($urlCase) {
            $url = strtolower($url);
        }
        if (!empty($vars)) {
            $vars = http_build_query($vars);
            $url .= '&' . $vars;
        }
    } else {
        // PATHINFO模式或者兼容URL模式
        if (isset($route)) {
            $url = __APP__ . '/' . rtrim($url, $depr);
        } else {
            $module = (defined('BIND_MODULE') && BIND_MODULE == $module) ? '' : $module;
            $url    = __APP__ . '/' . ($module ? $module . MODULE_PATHINFO_DEPR : '') . implode($depr, array_reverse($var));
        }
        if ($urlCase) {
            $url = strtolower($url);
        }
        if (!empty($vars)) {
            // 添加参数
            foreach ($vars as $var => $val) {
                if ('' !== trim($val)) {
                    $url .= $depr . $var . $depr . urlencode($val);
                }

            }
        }
        if ($suffix) {
            $suffix = true === $suffix ? C('URL_HTML_SUFFIX') : $suffix;
            if ($pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
            if ($suffix && '/' != substr($url, -1)) {
                $url .= '.' . ltrim($suffix, '.');
            }
        }
    }
    if (isset($anchor)) {
        $url .= '#' . $anchor;
    }
    if ($domain) {
        $url = (is_ssl() ? 'https://' : 'http://') . $domain . $url;
    }
    return $url;
}


/**
 * 实例化多层控制器 格式：[资源://][模块/]控制器
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @param integer $level 控制器层次
 * @return Think\Controller|false
 */
function A($name, $layer = '', $level = 0) {
    static $_action = array();
    $layer          = $layer ?: C('DEFAULT_C_LAYER');
    $level          = $level ?: (C('DEFAULT_C_LAYER') == $layer ? C('CONTROLLER_LEVEL') : 1);
    if (isset($_action[$name . $layer])) {
        return $_action[$name . $layer];
    }

    $class = parse_res_name($name, $layer, $level);
    if (class_exists($class)) {
        $action                  = new $class();
        $_action[$name . $layer] = $action;
        return $action;
    } else {
        return false;
    }
}


/**
 * 远程调用控制器的操作方法 URL 参数格式 [资源://][模块/]控制器/操作
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url, $vars = array(), $layer = '') {
    $info   = pathinfo($url);
    $action = $info['basename'];
    $module = $info['dirname'];
    $class  = A($module, $layer);
    if ($class) {
        if (is_string($vars)) {
            parse_str($vars, $vars);
        }
        return call_user_func_array(array(&$class, $action . C('ACTION_SUFFIX')), $vars);
    } else {
        return false;
    }
}


/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传入的参数
 * @return void
 */
function W($name, $data = array()) {
    return R($name, $data, 'Widget');
}


/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}


/**
 * session管理函数
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name = '', $value = '')
{
    $prefix = C('SESSION_PREFIX');
    if (is_array($name)) {
        // session初始化 在session_start 之前调用
        if (isset($name['prefix'])) {
            C('SESSION_PREFIX', $name['prefix']);
        }
        if (C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')])) {
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        } elseif (isset($name['id'])) {
            session_id($name['id']);
        }
        if ('common' == APP_MODE) {
            // 其它模式可能不支持
            ini_set('session.auto_start', 0);
        }
        if (isset($name['name'])) {
            session_name($name['name']);
        }
        if (isset($name['path'])) {
            session_save_path($name['path']);
        }
        if (isset($name['domain'])) {
            ini_set('session.cookie_domain', $name['domain']);
        }
        if (isset($name['expire'])) {
            ini_set('session.gc_maxlifetime', $name['expire']);
            ini_set('session.cookie_lifetime', $name['expire']);
        }
        if (isset($name['use_trans_sid'])) {
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        }
        if (isset($name['use_cookies'])) {
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        }
        if (isset($name['cache_limiter'])) {
            session_cache_limiter($name['cache_limiter']);
        }
        if (isset($name['cache_expire'])) {
            session_cache_expire($name['cache_expire']);
        }
        if (isset($name['type'])) {
            C('SESSION_TYPE', $name['type']);
        }
        if (C('SESSION_TYPE')) {
            // 读取session驱动
            $type   = C('SESSION_TYPE');
            $class  = strpos($type, '\\') ? $type : 'Think\\Session\\Driver\\' . ucwords(strtolower($type));
            $hander = new $class();
            session_set_save_handler(
                array(&$hander, "open"),
                array(&$hander, "close"),
                array(&$hander, "read"),
                array(&$hander, "write"),
                array(&$hander, "destroy"),
                array(&$hander, "gc"));
        }
        // 启动session
        if (C('SESSION_AUTO_START')) {
            session_start();
        }
    } elseif ('' === $value) {
        if ('' === $name) {
            // 获取全部的session
            return $prefix ? $_SESSION[$prefix] : $_SESSION;
        } elseif (0 === strpos($name, '[')) {
            // session 操作
            if ('[pause]' == $name) {
                // 暂停session
                session_write_close();
            } elseif ('[start]' == $name) {
                // 启动session
                session_start();
            } elseif ('[destroy]' == $name) {
                // 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) {
                // 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) {
            // 检查session
            $name = substr($name, 1);
            if (strpos($name, '.')) {
                // 支持数组
                list($name1, $name2) = explode('.', $name);
                return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
            } else {
                return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) {
            // 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) {
            // 获取session
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                return isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null;
            } else {
                return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                return isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
    } elseif (is_null($value)) {
        // 删除session
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    } else {
        // 设置session
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } else {
            if ($prefix) {
                $_SESSION[$prefix][$name] = $value;
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }
    return null;
}
/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $option cookie参数
 * @return mixed
 */
function cookie($name = '', $value = '', $option = null)
{
    // 默认设置
    $config = array(
        'prefix'   => C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire'   => C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'     => C('COOKIE_PATH'), // cookie 保存路径
        'domain'   => C('COOKIE_DOMAIN'), // cookie 有效域名
        'secure'   => C('COOKIE_SECURE'), //  cookie 启用安全传输
        'httponly' => C('COOKIE_HTTPONLY'), // httponly设置
    );
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option)) {
            $option = array('expire' => $option);
        } elseif (is_string($option)) {
            parse_str($option, $option);
        }
        $config = array_merge($config, array_change_key_case($option));
    }
    if (!empty($config['httponly'])) {
        ini_set("session.cookie_httponly", 1);
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE)) {
            return null;
        }
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {
            // 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return null;
    } elseif ('' === $name) {
        // 获取全部的cookie
        return $_COOKIE;
    }
    $name = $config['prefix'] . str_replace('.', '_', $name);
    if ('' === $value) {
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            if (0 === strpos($value, 'think:')) {
                $value = substr($value, 6);
                return array_map('urldecode', json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
            } else {
                return $value;
            }
        } else {
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if (is_array($value)) {
                $value = 'think:' . json_encode(array_map('urlencode', $value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            $_COOKIE[$name] = $value;
        }
    }
    return null;
}