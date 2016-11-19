<?php
/**
 * SF 系统入口文件
 * @category   myphp
 * @package    Common
 * @author     xiak <811800545@qq.com> 爱生活，爱简单！
 * Update time：2015-10-31 16:03:00	芳芳：改变也是件美好的事情！
 */

$GLOBALS['sf_beginTime'] = microtime(true);
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if (MEMORY_LIMIT_ON) $GLOBALS['sf_startUseMems'] = memory_get_usage();

header('X-Powered-By: SF PHP Framework by xiak 811800545@qq.com 15997152146  Shen Fang I love you forever :)');

// 系统基本常量定义
const SF_VERSION   = '5.2.0';

// url model
const URL_COMMON   = 0; 	// 普通模式
const URL_PATHINFO = 1;		// PATHINFO模式
const URL_REWRITE  = 2;		// REWRITE模式
const URL_COMPAT   = 3;		// 兼容模式

define('IS_CGI', (0 === strpos(PHP_SAPI, 'cgi') || false !== strpos(PHP_SAPI, 'fcgi')) ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

if (!IS_CLI) {
    if (!defined('_PHP_FILE_')) {
        if (IS_CGI) {	// CGI/FASTCGI模式下
            $_temp = explode('.php', $_SERVER['PHP_SELF']);
            define('_PHP_FILE_', rtrim(str_replace($_SERVER['HTTP_HOST'], '', $_temp[0].'.php'), '/'));
            unset($_temp);
        } else {
            define('_PHP_FILE_', rtrim($_SERVER['SCRIPT_NAME'], '/'));
        }
    }

    if (!defined('__ROOT__')) {
        $_root = rtrim(dirname(_PHP_FILE_), '/');
        define('__ROOT__', (($_root == '/' || $_root == '\\') ? '' : $_root));
        unset($_root);
    }
    defined('__PUBLIC__') or define('__PUBLIC__', __ROOT__ . '/Public');
}

define('OUTPUT_BUFFERING', ini_get('output_buffering'));

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    ini_set('magic_quotes_runtime', 0);
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
} else {
    define('MAGIC_QUOTES_GPC', false);
}

if (function_exists('saeAutoLoader')) {
    defined('APP_MODE') or define('APP_MODE', 'sae');
    defined('STORAGE_TYPE') or define('STORAGE_TYPE', 'Sae');
} else {
    defined('APP_MODE') or define('APP_MODE', 'common');
    defined('STORAGE_TYPE') or define('STORAGE_TYPE', 'File');
}


defined('APP_DEBUG')   or define('APP_DEBUG', false);
defined('APP_STATUS')  or define('APP_STATUS', '');

// 应用目录常量定义

defined('PATH')        or define('PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/');

defined('ORG_PATH')    or define('ORG_PATH', PATH . 'ORG/');

defined('APP_NAME')    or define('APP_NAME', 'Application');

defined('APP_PATH')    or define('APP_PATH', PATH . APP_NAME . '/');

defined('COMMON_PATH') or define('COMMON_PATH', APP_PATH . 'Common/');

defined('CONF_PATH')   or define('CONF_PATH', COMMON_PATH . 'Conf/');

defined('LANG_PATH')   or define('LANG_PATH', COMMON_PATH . 'Lang/');

defined('DATA_PATH')   or define('DATA_PATH', APP_PATH . 'Data/');

defined('LOG_PATH')    or define('LOG_PATH', DATA_PATH . 'Logs/');

defined('TEMP_PATH')   or define('TEMP_PATH', DATA_PATH . 'Temp/');

defined('CACHE_PATH')  or define('CACHE_PATH', DATA_PATH . 'Cache/');


// 系统目录常量定义

defined('SF_PATH') 		 or define('SF_PATH', str_replace('\\', '/', __DIR__) . '/');

defined('LIB_PATH') 	 or define('LIB_PATH', SF_PATH . 'Library/');

defined('CORE_PATH') 	 or define('CORE_PATH', LIB_PATH . 'Core/');

defined('BEHAVIOR_PATH') or define('BEHAVIOR_PATH', LIB_PATH . 'Behavior/');

defined('VENDOR_PATH') 	 or define('VENDOR_PATH', LIB_PATH . 'Vendor/');

defined('MODE_PATH') 	 or define('MODE_PATH', SF_PATH . 'Mode/');

require CORE_PATH.'SF.class.php';

Core\SF::start();