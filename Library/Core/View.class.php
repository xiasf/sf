<?php
namespace Core;
/**
 * SF 视图类
 * Update time：2015-10-16 23:10:11
 */
class View {
    /**
     * 模板输出变量
     */ 
    protected $tVar  = array();

    /**
     * 模板主题
     */
    protected $theme = '';


    /**
     * 模板变量赋值
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name, $value = '') {
        if (is_array($name)) {
            $this->tVar = array_merge($this->tVar, $name);
        } else {
            $this->tVar[$name] = $value;
        }
    }


    /**
     * 取得模板变量的值
     * @param string $name
     * @return mixed
     */
    public function get($name = '') {
        if ('' === $name) {
            return $this->tVar;
        }
        return isset($this->tVar[$name]) ? $this->tVar[$name] : false;
    }


    /**
     * 加载模板和页面输出 可以返回输出内容
     * @param string $templateFile  模板文件规则
     * @param string $charset       模板输出字符集
     * @param string $contentType   输出类型
     * @param string $content       模板输出内容
     * @param string $prefix        模板缓存前缀
     * @return mixed
     */
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        G('viewStartTime');

        // 视图开始标签
        Hook::listen('view_begin', $templateFile);

        // 解析并获取模板内容
        $content = $this->fetch($templateFile, $content, $prefix);

        // 输出模板内容
        $this->render($content, $charset, $contentType);

        // 视图结束标签
        Hook::listen('view_end');
    }


    /**
     * 输出内容文本可以包括Html
     * @param string $content       输出内容
     * @param string $charset       模板输出字符集
     * @param string $contentType   输出类型
     * @return mixed
     */
    private function render($content, $charset = '', $contentType = '') {
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        header('Content-Type:' . $contentType . '; charset=' . $charset);
        header('Cache-control:' . C('HTTP_CACHE_CONTROL'));
        echo $content;
    }


    /**
     * 解析和获取模板内容
     * @param string $templateFile  模板文件规则
     * @param string $content       模板输出内容
     * @param string $prefix        模板缓存前缀
     * @return string
     */
    public function fetch($templateFile = '', $content = '', $prefix = '') {
        if (empty($content)) {
            $templateFile = $this->parseTemplate($templateFile);
            is_file($templateFile) or E(L('_TEMPLATE_NOT_EXIST_') . ':' . $templateFile);
            defined('TPL_FILE') or define('TPL_FILE', $templateFile);
        } else {
            defined('THEME_PATH') or define('THEME_PATH', $this->getThemePath());
        }

        ob_start();
        ob_implicit_flush(0);

        if ('php' == strtolower(C('TMPL_ENGINE_TYPE'))) {

            extract($this->tVar, EXTR_OVERWRITE);
            !empty($content) ? eval('?>' . $content) : include $templateFile;

        } else {

            $params = array('var' => $this->tVar, 'file' => $templateFile, 'content' => $content, 'prefix' => $prefix);
            Hook::listen('view_parse', $params);    // 视图解析标签

        }

        $content = ob_get_clean();

        // 内容过滤标签
        Hook::listen('view_filter', $content);

        return $content;
    }


    /**
     * 自动定位模板文件
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplate($template = '') {
        if (is_file($template)) {
            return $template;
        }
        $depr     = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);

        $module = MODULE_NAME;
        if (strpos($template, '@')) {
            list($module, $template) = explode('@', $template);
        }

        if('' == $template) {
            $template = CONTROLLER_NAME . $depr . ACTION_NAME;
        } elseif (false === strpos($template, $depr)) {
            $template = CONTROLLER_NAME . $depr . $template;
        }

        defined('THEME_PATH') or define('THEME_PATH', $this->getThemePath($module));

        $file = THEME_PATH . $template . C('TMPL_TEMPLATE_SUFFIX');

        if (C('TMPL_LOAD_DEFAULTTHEME') && THEME_NAME != C('DEFAULT_THEME') && !is_file($file)) {
            $file = dirname(THEME_PATH) . '/' . C('DEFAULT_THEME') . '/' . $template . C('TMPL_TEMPLATE_SUFFIX');
        }

        return $file;
    }


    /**
     * 获取当前的主题模板路径
     * @param  string $module 模块名
     * @return string
     */
    protected function getThemePath($module = MODULE_NAME) {
        $theme = $this->getTheme();
        if (!defined('TMPL_PATH')) {
            $tplPath = is_dir(COMMON_PATH . C('DEFAULT_TMPL')) ? (COMMON_PATH . C('DEFAULT_TMPL')) : (COMMON_PATH . C('DEFAULT_TMPL') . '/' . C('DEFAULT_DEVICE'));
            define('TMPL_PATH', $tplPath . '/');
        }
        return TMPL_PATH . DEVICE . '/' . $module . '/' . ($theme ? $theme . '/' : '');
    }


    /**
     * 设置当前输出的模板主题
     * @param  mixed $theme 主题名称
     * @return View
     */
    public function theme($theme) {
        $this->theme = $theme;
        return $this;
    }


    /**
     * 获取当前的模板主题
     * @return string
     */
    private function getTheme() {
        if($this->theme) { // 指定模板主题
            $theme = $this->theme;
        } else {
            $theme =  C('DEFAULT_THEME');
            if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
                $t = C('VAR_THEME');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                } elseif (cookie('sf_theme')) {
                    $theme = cookie('sf_theme');
                }
                if(!in_array($theme, explode(',', C('THEME_LIST')))){
                    $theme =  C('DEFAULT_THEME');
                }
                cookie('sf_theme' ,$theme, 864000);
            }
        }
        defined('THEME_NAME') or define('THEME_NAME', $theme);                  // 当前模板主题名称
        return $theme ? $theme : '';
    }
}