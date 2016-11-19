<?php
namespace Core;
/**
 * Dispatcher类 完成URL解析、路由和调度
 * Update time：2015-11-22 02:11:43
 */
class Dispatcher {

    /**
     * 域名部署
     * @access private
     * @return void
     */
    static private function domain_deployment() {
        define('APP_DOMAIN', $_SERVER['HTTP_HOST']);
        if (C('APP_SUB_DOMAIN_DEPLOY')) {
            $rules = C('APP_SUB_DOMAIN_RULES');
            if (isset($rules[APP_DOMAIN])) {
                $rule = $rules[APP_DOMAIN];
            } else {
                if (strpos(C('APP_DOMAIN_SUFFIX'),'.')) { // com.cn net.cn 
                    $domain = array_slice(explode('.', APP_DOMAIN), 0, -3);
                } else {
                    $domain = array_slice(explode('.', APP_DOMAIN), 0, -2);                    
                }
                if (!empty($domain)) {
                    $subDomain = implode('.', $domain);
                    define('SUB_DOMAIN', $subDomain); // 当前完整子域名
                    $domain2 = array_pop($domain);
                    if ($domain) {
                        $domain3 = array_pop($domain);
                    }
                    // 往下可以支持更多级子域名，目前只支持三级
                    unset($domain);
                    if (isset($rules[$subDomain])) { // 子域名
                        $rule = $rules[$subDomain];
                    } elseif (isset($rules['*.' . $domain2]) && !empty($domain3)) { // 泛三级域名
                        $rule = $rules['*.' . $domain2];
                        $panDomain = $domain3;
                    } elseif (isset($rules['*']) && !empty($domain2) && 'www' != $domain2 ) { // 泛二级域名
                        $rule      = $rules['*'];
                        $panDomain = $domain2;
                    }
                }                
            }

            if (!empty($rule)) {
                if (is_array($rule)) {
                    list($rule, $vars) = $rule;
                }
                $array = explode('/', $rule);
                defined('BIND_MODULE') or define('BIND_MODULE', array_shift($array));

                if (!empty($array)) {
                    $controller = array_shift($array);
                    if ($controller) {
                        defined('BIND_CONTROLLER') or define('BIND_CONTROLLER', $controller);
                    }
                }

                if (isset($vars)) {
                    parse_str($vars, $parms);
                    if (isset($panDomain)) {
                        $pos = array_search('*', $parms);
                        if(false !== $pos) {
                            $parms[$pos] = $panDomain;
                        }
                    }
                    $_GET = array_merge($_GET, $parms);
                }
            }
        }
    }


    /**
     * 获取当前实际模块名
     * @access private
     * @return string
     */
    static private function getModule($var) {
        $module = strtolower(!empty($_GET[$var]) ? $_GET[$var] : C('DEFAULT_MODULE'));
        unset($_GET[$var]);
        if ($maps = C('URL_MODULE_MAP')) {
            if (isset($maps[$module])) {
                define('MODULE_ALIAS', ucfirst($module));
                return ucfirst($maps[$module]);
            } elseif (array_search($module, $maps)) {
                return '';
            }
        }
        return strip_tags(ucfirst($module));
    }


    /**
     * 获得实际的控制器名称
     * @access private
     * @return string
     */
    private static function getController($var, $urlCase) {
        $controller = (!empty($_GET[$var]) ? $_GET[$var] : C('DEFAULT_CONTROLLER'));
        unset($_GET[$var]);
        if ($maps = C('URL_CONTROLLER_MAP')) {
            if (isset($maps[strtolower($controller)])) {
                define('CONTROLLER_ALIAS', strtolower($controller));
                return ucfirst($maps[CONTROLLER_ALIAS]);
            } elseif (array_search(strtolower($controller), $maps)) {
                return '';
            }
        }
        if ($urlCase) {
            $controller = parse_name($controller, 1);
        }
        return strip_tags(ucfirst($controller));
    }


    /**
     * 获得实际的操作名称
     * @access private
     * @return string
     */
    private static function getAction($var, $urlCase) {
        $action = !empty($_POST[$var]) ? $_POST[$var] : (!empty($_GET[$var]) ? $_GET[$var] : C('DEFAULT_ACTION'));
        unset($_POST[$var], $_GET[$var]);
        if ($maps = C('URL_ACTION_MAP')) {
            if (isset($maps[strtolower(CONTROLLER_NAME)])) {
                $maps = $maps[strtolower(CONTROLLER_NAME)];
                if (isset($maps[strtolower($action)])) {
                    define('ACTION_ALIAS', strtolower($action));
                    if (is_array($maps[ACTION_ALIAS])) {
                        parse_str($maps[ACTION_ALIAS][1], $vars);
                        $_GET = array_merge($_GET, $vars);
                        return $maps[ACTION_ALIAS][0];
                    } else {
                        return $maps[ACTION_ALIAS];
                    }
                }
                foreach ($maps as $value) {
                    if (is_array($value)) {
                        if ($value[1] == strtolower($action)) return '';
                    } else {
                        if ($value == strtolower($action)) return '';
                    }
                }
            }
        }
        return strip_tags($urlCase ? strtolower($action) : $action);
    }


    /**
     * URL映射到控制器
     * @access public
     * @return void
     */
    static public function dispatch() {

        self::domain_deployment();

        $varPath       = C('VAR_PATHINFO');
        // $varAddon      = C('VAR_ADDON');
        $varModule     = C('VAR_MODULE');
        $varController = C('VAR_CONTROLLER');
        $varAction     = C('VAR_ACTION');
        $urlCase       = C('URL_CASE_INSENSITIVE');
        $depr          = C('URL_PATHINFO_DEPR');

        define('MODULE_PATHINFO_DEPR', $depr);
        define('__SELF__', strip_tags($_SERVER[C('URL_REQUEST_URI')]));

        if (isset($_GET[$varPath])) {
            $_SERVER['PATH_INFO'] = $_GET[$varPath];
            unset($_GET[$varPath]);
        } elseif (IS_CLI) {
            $_SERVER['PATH_INFO'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        }

        if (!isset($_SERVER['PATH_INFO'])) {
            $types = explode(',', C('URL_PATHINFO_FETCH'));
            foreach ($types as $type) {
                if (0 === strpos($type, ':')) {// 支持函数判断
                    $_SERVER['PATH_INFO'] = call_user_func(substr($type, 1));
                    break;
                } elseif (!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                    break;
                }
            }
        }

        if (empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = '';
            define('__INFO__', '');
            define('__EXT__', '');
        } else {
            define('__INFO__', trim($_SERVER['PATH_INFO'], '/'));
            define('__EXT__', strtolower(pathinfo($_SERVER['PATH_INFO'], PATHINFO_EXTENSION)));

            if (__EXT__ && C('URL_HTML_SUFFIX') && !preg_match('/\.(' . trim(C('URL_HTML_SUFFIX'), '.') . ')$/i', $_SERVER['PATH_INFO'])) {
                E('不被允许的后缀:' . __EXT__);
            }

            if (__EXT__ && C('URL_DENY_SUFFIX') && preg_match('/\.(' . trim(C('URL_DENY_SUFFIX'), '.') . ')$/i', $_SERVER['PATH_INFO'])) {
                E('禁止访问的后缀:' . __EXT__);
            }

            $_SERVER['PATH_INFO'] = __INFO__;

            // 去除URL后缀
            $_SERVER['PATH_INFO'] = preg_replace('/\.' . __EXT__ . '$/i', '', $_SERVER['PATH_INFO']);
            
            if (!defined('BIND_MODULE') && (!C('URL_ROUTER_ON') || !Route::check()) && $_SERVER['PATH_INFO']) {
                $paths                = explode($depr, $_SERVER['PATH_INFO'], 2);
                $_GET[$varModule]     = $paths[0];
                $_SERVER['PATH_INFO'] = isset($paths[1]) ? $paths[1] : '';
            }
        }

        define('MODULE_NAME', defined('BIND_MODULE') ? BIND_MODULE : self::getModule($varModule));

        $allowList = C('MODULE_ALLOW_LIST');
        if (MODULE_NAME && (empty($allowList) || (is_array($allowList) && in_array_case(MODULE_NAME, $allowList))) && !in_array_case(MODULE_NAME, C('MODULE_DENY_LIST')) && is_dir(APP_PATH . MODULE_NAME)) {
            unset($allowList);

            define('MODULE_PATH', APP_PATH . MODULE_NAME . '/');
            C('CACHE_PATH', CACHE_PATH . MODULE_NAME . '/');
            C('LOG_PATH', LOG_PATH . MODULE_NAME . '/');

            // 模块检测
            Hook::listen('module_check');

            //加载系统模块默认文件

            if (is_file(MODULE_PATH . 'Conf/config.php'))
                C (include MODULE_PATH . 'Conf/config.php');

            if ('common' != APP_MODE && is_file(MODULE_PATH . 'Conf/config_' . APP_MODE . '.php'))
                C(include MODULE_PATH . 'Conf/config_' . APP_MODE . '.php');

            if (APP_STATUS && is_file(MODULE_PATH . 'Conf/' . APP_STATUS . '.php'))
                C(include MODULE_PATH . 'Conf/' . APP_STATUS . '.php');

            if (APP_DEBUG && is_file(MODULE_PATH . 'Conf/debug.php'))
                C (include MODULE_PATH . 'Conf/debug.php');

            if (is_file(MODULE_PATH . 'Conf/alias.php'))
                Think::addMap(include MODULE_PATH . 'Conf/alias.php');

            if (is_file(MODULE_PATH . 'Conf/hook.php'))
                Hook::import(include MODULE_PATH . 'Conf/hook.php');

            if (is_file(MODULE_PATH . 'Lang' . strtolower(C('DEFAULT_LANG')) . '.php'))
                L(include MODULE_PATH . 'Lang' . strtolower(C('DEFAULT_LANG')) . '.php');

            if (is_file(MODULE_PATH . 'Common/function.php'))
                include MODULE_PATH . 'Common/function.php';

            SF::load_ext_file(COMMON_PATH);
        } else {
            unset($allowList);
            if (APP_DEBUG) {
                E(L('_MODULE_NOT_EXIST_') . ':' . MODULE_NAME);
            } else {
                header('Location: ' . _PHP_FILE_);
                exit;
            }
        }

        if (!defined('__APP__')) {
            $urlMode = C('URL_MODEL');
            if (URL_COMPAT == $urlMode) {
                define('PHP_FILE', _PHP_FILE_ . '?' . $varPath . '=');
            } elseif (URL_REWRITE == $urlMode) {
                $url = dirname(_PHP_FILE_);
                if ('/' == $url || '\\' == $url) {
                    $url = '';
                }
                define('PHP_FILE', $url);
                unset($url);
            } else {
                define('PHP_FILE', _PHP_FILE_);
            }
            define('__APP__', strip_tags(PHP_FILE));
        }

        $moduleName = defined('MODULE_ALIAS') ? MODULE_ALIAS : MODULE_NAME;
        define('__MODULE__', defined('BIND_MODULE') ? __APP__ : __APP__ . '/' . ($urlCase ? strtolower($moduleName) : $moduleName));
        unset($moduleName);

        if ('' != $_SERVER['PATH_INFO'] && (!C('URL_ROUTER_ON') || !Route::check())) {

            Hook::listen('path_info');

            $paths = explode($depr, trim($_SERVER['PATH_INFO'], $depr));

            if (!defined('BIND_CONTROLLER')) {
                $_GET[$varController] = array_shift($paths);
            }

            if (!defined('BIND_ACTION')) {
                $_GET[$varAction] = array_shift($paths);
            }

            $var = array();
            if (1 == C('URL_PARAMS_BIND_TYPE')) {
                $var = $paths;
            } else {
                preg_replace_callback('/(\w+)\/([^\/]+)/',
                    function ($match) use (&$var) {
                        $var[$match[1]] = strip_tags($match[2]);
                    }, implode('/', $paths)
                );
            }
            $_GET = array_merge($var, $_GET);
        }

        define('CONTROLLER_NAME', defined('BIND_CONTROLLER') ? BIND_CONTROLLER : self::getController($varController, $urlCase));
        define('ACTION_NAME', defined('BIND_ACTION') ? BIND_ACTION : self::getAction($varAction, $urlCase));

        $controllerName = defined('CONTROLLER_ALIAS') ? CONTROLLER_ALIAS : CONTROLLER_NAME;
        define('__CONTROLLER__', __MODULE__ . $depr . (defined('BIND_CONTROLLER') ? '' : ($urlCase ? parse_name($controllerName) : $controllerName)));
        unset($controllerName);

        define('__ACTION__', __CONTROLLER__ . $depr . (defined('ACTION_ALIAS') ? ACTION_ALIAS : ACTION_NAME));

        $_REQUEST = array_merge($_POST, $_GET, $_COOKIE);
    }
}