<?php
namespace Core;
/**
 * SF 应用程序类 执行应用过程管理
 * Update time：2015-11-5 16:42:53
 */
class App {

    /**
     * 应用程序初始化
     * @access private
     * @return void
     */
    private static function init() {
        // 定义当前请求的系统常量
        define('NOW_TIME', $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
        define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
        define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);

        define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')])) ? true : false);

        // URL调度
        Dispatcher::dispatch();

        if (C('REQUEST_VARS_FILTER')) {
            // 全局安全过滤
            array_walk_recursive($_GET, 'think_filter');
            array_walk_recursive($_POST, 'think_filter');
            array_walk_recursive($_REQUEST, 'think_filter');
        }

        // URL调度结束标签
        Hook::listen('url_dispatch');

        defined('DEVICE') or define('DEVICE', C('DEFAULT_DEVICE'));
    }


    /**
     * 执行应用程序
     * @access private
     * @return void
     */
    private static function exec() {

        if (!preg_match('/^[A-Za-z]\w*$/', CONTROLLER_NAME)) {

            $module = false;

        } else {
            //创建控制器实例
            $module = self::controller(CONTROLLER_NAME);
        }

        if (!$module) {
            $module = self::controller('Empty');
            if (!$module) {
                if (APP_DEBUG)
                    E(L('_CONTROLLER_NOT_EXIST_') . ':' . CONTROLLER_NAME);
                else {
                    header('Location: ' . __MODULE__);
                    exit;
                }
            }
        }

        $action = ACTION_NAME . C('ACTION_SUFFIX');

        try {
            self::invokeAction($module, $action);
        } catch (\ReflectionException $e) {
            // 方法调用发生异常后 引导到__call方法处理
            $method = new \ReflectionMethod($module, '__call');
            $method->invokeArgs($module, array($action, ''));
        }
    }


    /**
     * 用于实例化访问控制器
     * @access private
     * @param string $name 控制器名
     * @return Think\Controller|false
     */
    public static function controller($name) {
        $class = MODULE_NAME . '\\' . C('DEFAULT_C_LAYER') . '\\'.parse_name($name, 1) . C('DEFAULT_C_LAYER');
        if (class_exists($class))
            return new $class();
        else
            return false;
    }


    /**
     * 执行操作
     * @access private
     * @param object $module  控制器实例
     * @param string $action  操作名
     * @return void
     */
    public static function invokeAction($module, $action) {
        if (!preg_match('/^[A-Za-z]\w*$/', $action)) {
            // 非法操作
            throw new \ReflectionException();
        }
        //执行当前操作
        $method = new \ReflectionMethod($module, $action);
        if ($method->isPublic() && !$method->isStatic()) {
            $class = new \ReflectionClass($module);

            // 前置操作
            if ($class->hasMethod('_before_' . $action)) {
                $before = $class->getMethod('_before_' . $action);
                if ($before->isPublic()) {
                    $before->invoke($module);
                }
            }

            // URL参数绑定检测
            if ($method->getNumberOfParameters() > 0 && C('URL_PARAMS_BIND')) {
                switch (REQUEST_METHOD) {
                    case 'POST':
                        $vars = array_merge($_GET, $_POST);
                        break;
                    case 'PUT':
                        parse_str(file_get_contents('php://input'), $vars);
                        break;
                    default:
                        $vars = $_GET;
                }
                $args           = array();
                $params         = $method->getParameters();
                $paramsBindType = C('URL_PARAMS_BIND_TYPE');
                foreach ($params as $param) {
                    $name = $param->getName();
                    if (0 == $paramsBindType && isset($vars[$name])) {
                        $args[] = $vars[$name];
                    } elseif (1 == $paramsBindType && !empty($vars)) {
                        $args[] = array_shift($vars);
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        E($action.L('_ACTION_').L('_PARAM_ERROR_') . ':' . $name);
                    }
                }
                unset($paramsBindType);

                // 开启绑定参数过滤机制
                if (C('URL_PARAMS_SAFE')) {
                    $filters = C('URL_PARAMS_FILTER') ?: C('DEFAULT_FILTER');
                    if ($filters) {
                        $filters = explode(',', $filters);
                        foreach ($filters as $filter) {
                            $args = array_map_recursive($filter, $args); // 参数过滤
                        }
                    }
                }
                array_walk_recursive($args, 'think_filter');

                $method->invokeArgs($module, $args);
            } else {
                $method->invoke($module);
            }

            // 后置操作
            if ($class->hasMethod('_after_' . $action)) {
                $after = $class->getMethod('_after_' . $action);
                if ($after->isPublic()) {
                    $after->invoke($module);
                }
            }

        } else {
            // 操作方法不是Public 抛出异常
            throw new \ReflectionException();
        }
    }


    /**
     * 运行应用实例 入口文件使用的快捷方法
     * @access public
     * @return void
     */
    public static function run() {
        // 应用初始化标签
        Hook::listen('app_init');
        App::init();
        // 应用开始标签
        Hook::listen('app_begin');
        // Session初始化
        if (!IS_CLI) {
            session(C('SESSION_OPTIONS'));
        }
        // 记录应用初始化时间
        G('initTime');
        App::exec();
        // 应用结束标签
        Hook::listen('app_end');
    }


    public static function logo() {
        if (is_file(C('SF_LOGO')))
            return base64_encode(file_get_contents(C('SF_LOGO')));
    }
}