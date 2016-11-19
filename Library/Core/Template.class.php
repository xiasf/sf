<?php
namespace Core;
/**
 * SF内置模板引擎类
 * Update time：2016-1-10 23:18:18
 */
class  Template {

    protected $tagLib       = array();
    protected $templateFile = '';
    public    $tVar         = array();
    public    $config       = array();
    private   $literal      = array();

    /**
     * 构造函数
     */
    public function __construct() {
        $this->config['cache_path']      = CACHE_PATH . C('DEFAULT_CACHE_TMPL') . '/';
        $this->config['template_suffix'] = C('TMPL_TEMPLATE_SUFFIX');
        $this->config['cache_suffix']    = C('TMPL_CACHE_SUFFIX');
        $this->config['tmpl_cache']      = C('TMPL_CACHE_ON');
        $this->config['cache_time']      = C('TMPL_CACHE_TIME');
        $this->config['taglib_begin']    = $this->stripPreg(C('TAGLIB_BEGIN'));
        $this->config['taglib_end']      = $this->stripPreg(C('TAGLIB_END'));
        $this->config['tmpl_begin']      = $this->stripPreg(C('TMPL_L_DELIM'));
        $this->config['tmpl_end']        = $this->stripPreg(C('TMPL_R_DELIM'));
        // $this->config['default_tmpl']    = C('TEMPLATE_NAME');
        $this->config['layout_default_layer']     = C('DEFAULT_L_LAYER');
        $this->config['layout_item']     = C('TMPL_LAYOUT_ITEM');
    }


    private function stripPreg($str) {
        return str_replace(
                        array('{','}','(',')','|','[',']','-','+','*','.','^','?'),
                        array('\{','\}','\(','\)','\|','\[','\]','\-','\+','\*','\.','\^','\?'),
                        $str);
    }


    public function get($name) {
        if (isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }


    public function set($name, $value) {
        $this->tVar[$name] = $value;
    }


    /**
     * 加载模板
     * @param string $templateFile  模板文件或内容
     * @param array  $templateVar   模板变量
     * @param string $prefix        模板缓存前缀
     * @return void
     */
    public function fetch($templateFile, $templateVar, $prefix = '') {
        $this->tVar        = $templateVar;
        $templateCacheFile = $this->loadTemplate($templateFile, $prefix);
        Storage::load($templateCacheFile, $this->tVar, null, 'tpl');
    }


    /**
     * 加载主模板并缓存
     * @param string $templateFile 模板文件或内容
     * @param string $prefix 模板缓存前缀
     * @return string
     */
    public function loadTemplate($templateFile, $prefix = '') {

        list($tmplCacheFile, $tmplContent) = $this->getCacheFile($templateFile, $prefix);

        if (C('LAYOUT_ON')) {
            if (false !== strpos($tmplContent, '{__NOLAYOUT__}')) {
                $tmplContent = str_replace('{__NOLAYOUT__}', '', $tmplContent);
            } else {
                $layoutFile = THEME_PATH . $this->config['layout_default_layer'] . '/' . C('LAYOUT_NAME') . $this->config['template_suffix'];

                is_file($layoutFile) or E(L('_LAYOUT_NOT_EXIST_') . ':' . $layoutFile);

                $tmplContent = str_replace($this->config['layout_item'], $tmplContent, file_get_contents($layoutFile));
            }
        }

        $tmplContent = $this->compiler($tmplContent);

        Storage::put($tmplCacheFile, trim($tmplContent), 'tpl');

        return $tmplCacheFile;
    }


    /**
     * 获取主模板缓存文件名和内容
     * @param string $templateFile  模板文件或内容
     * @param string $prefix        模板缓存前缀
     * @return boolean
     */
    private function getCacheFile($templateFile, $prefix) {
        static $tplCacheFile = array();
        $key = md5($templateFile . $prefix);
        if (!isset($tplCacheFile[$key])) {
            if (is_file($templateFile)) {
                $this->templateFile = $templateFile;
                $tmplContent = file_get_contents($templateFile);
                $_tplFile = str_replace(TMPL_PATH, '', $templateFile);
                if ($_tplFile != $templateFile)
                $tmplCacheFile = (defined('TMPL_CACHE_PATH') ? TMPL_CACHE_PATH : $this->config['cache_path']) . pathinfo($_tplFile, PATHINFO_DIRNAME) . '/' . $prefix . pathinfo($_tplFile, PATHINFO_FILENAME) . $this->config['cache_suffix'];
                else
                    $tmplCacheFile = (defined('TMPL_CACHE_PATH') ? TMPL_CACHE_PATH : CACHE_PATH . C('DEFAULT_CACHE_TMPL') . '/') . $prefix . pathinfo($_tplFile, PATHINFO_FILENAME) . C('TMPL_CACHE_SUFFIX');
                unset($_tplFile);

            } else {
                $tmplContent = $templateFile;
                $tmplCacheFile = CACHE_PATH . C('NO_DEFAULT_CACHE_TMPL') . '/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . $prefix . md5($tmplContent) . $this->config['cache_suffix'];
            }
            $tplCacheFile[$key] = array($tmplCacheFile , $tmplContent);
        }
        return $tplCacheFile[$key];
    }


    /**
     * 编译模板文件内容
     * @param  mixed   $tmplContent 模板内容
     * @return string
     */
    protected function compiler($tmplContent) {
        //模板解析
        $tmplContent = $this->parse($tmplContent);

        // 添加安全代码
        $tmplContent = "<?php if (!defined('APP_NAME')) exit;?>\r\n" . $tmplContent;

        // 优化生成的php代码
        $tmplContent = str_replace('?><?php', '', $tmplContent);

        // 模版编译过滤标签
        Hook::listen('template_filter', $tmplContent);

        return $tmplContent . "\r\n" . '<!-- Update: ' . date('Y-m-d H:i:s' , NOW_TIME) . ' -->' . "\r\n" . '<!-- Shen Fang I Love You Forever -->' . "\r\n";
    }


    /**
     * 模板解析
     * @param string $content   要解析的内容
     * @return string
     */
    public function parse($content) {
        if (empty($content)) return '';

        $content = $this->parseInclude($content);

        $content = $this->parsePhp($content);

        C('TAGLIB_LOAD') && $this->getIncludeTagLib($content);
        C('TAGLIB_PRE_LOAD') && $this->tagLib = array_merge($this->tagLib, explode(',', C('TAGLIB_PRE_LOAD')));
        C('TAGLIB_BUILD_IN') && $this->tagLib = array_merge($this->tagLib, explode(',' , C('TAGLIB_BUILD_IN')));
        $this->tagLib = array_unique($this->tagLib);
        if (!empty($this->tagLib)) {
            foreach($this->tagLib as $tagLibName) {
                $this->parseTagLib($tagLibName, $content);
            }
        }

        $content = preg_replace_callback('/' . $this->config['tmpl_begin'] . '([^\d\w\s' . $this->config['tmpl_begin'] . $this->config['tmpl_end'] . '].+?)' . $this->config['tmpl_end'] . '/is', array($this, 'parseTag'), $content);

        $content = preg_replace_callback('/\/I LOVE Shen Fang(\d+)\//is', array($this, 'restoreLiteral'), $content);

        C('TMPL_STRIP_SPACE') && $content = HTML_TPL($content);

        return $content;
    }


    /**
     * 模板基本处理
     * @param string $content   要处理的内容
     * @return string
     */
    private function base($content) {
        if (empty($content)) return '';
        $content = preg_replace('/<!--(.*?)-->/s', '', $content);
        return $content;
    }


    /**
     * 替换页面中的literal标签
     * @param string $content  模板内容
     * @return string
     */
    private function parseLiteral($content) {
        if(is_array($content)) $content = $content[1];
        if (trim($content) == '')  return '';
        $i                 = count($this->literal);
        $this->literal[$i] = $content;
        return '/I LOVE Shen Fang' . $i . '/';
    }


    /**
     * 还原被替换的literal标签
     * @param string $i  literal标签序号
     * @return string
     */
    private function restoreLiteral($i) {
        if(is_array($i)) $i = $i[1];
        $parseStr = $this->literal[$i];
        unset($this->literal[$i]);
        return $parseStr;
    }


    // 解析模板中的布局标签
    protected function parseLayout($content) {
        $find = preg_match('/' . $this->config['taglib_begin'] . 'layout\s+(.+?)\s*?\/' . $this->config['taglib_end'] . '/is',$content,$matches);
        if ($find) {
            $content = str_replace($matches[0], '', $content);
            $array   = $this->parseXmlAttrs($matches[1]);
            if (!C('LAYOUT_ON') || C('LAYOUT_NAME') != $array['name'] ) {
                $layoutFile = THEME_PATH . $this->config['layout_default_layer'] . '/' . $array['name'] . $this->config['template_suffix'];
                is_file($layoutFile) or E(L('_LAYOUT_NOT_EXIST_') . ':' . $layoutFile);
                $replace = isset($array['replace']) ? $array['replace'] : $this->config['layout_item'];
                $content = str_replace($replace, $content, file_get_contents($layoutFile));
            }
        } else {
            $content = str_replace('{__NOLAYOUT__}', '', $content);
        }
        return $content;
    }


    // 解析模板中的include标签
    protected function parseInclude($content) {
        $content = $this->base($content);

        $content = preg_replace_callback('/' . $this->config['taglib_begin'] . 'sf' . $this->config['taglib_end'] . '(.*?)' . $this->config['taglib_begin'] . '\/sf' . $this->config['taglib_end'] . '/is', array($this, 'parseLiteral'), $content);

        $this->setTplVar($content);
        $content = $this->parseLayout($content);
        $find    = preg_match_all('/'.$this->config['taglib_begin'] . 'include\s+(.+?)\s*?\/' . $this->config['taglib_end'].'/is',$content,$matches);
        while (--$find >= 0) {
            $include = $matches[1][$find];
            $array   = $this->parseXmlAttrs($include);
            if (!empty($array['file'])) {
                $file = $array['file'];
                unset($array['file']);
                $content = str_replace($matches[0][$find], $this->parseIncludeItem($file, $array), $content);
            } else
                E('您没有指定需引入的模板文件名称');
        }
        return $content;
    }


    /**
     * 加载公共模板并缓存 和当前模板在同一路径，否则使用相对路径
     * @param string $tmplPublicName  公共模板文件名
     * @param array $vars  要传递的变量列表
     * @return string
     */
    private function parseIncludeItem($tmplPublicName, $vars = array()){
        // 分析模板文件名并读取内容
        $parseStr = $this->parseTemplateName($tmplPublicName);
        // 替换变量
        foreach ($vars as $key => $val) {
            $parseStr = str_replace(C('TMPL_L_DELIM') . '$' . $key . C('TMPL_R_DELIM'), $val, $parseStr);
        }
        // 再次对包含文件进行模板分析
        return $this->parseInclude($parseStr);
    }


    /**
     * 分析加载的模板文件并读取内容 支持多个模板文件读取
     * @param string $tmplPublicName  模板文件名
     * @return string
     */
    private function parseTemplateName($templateName) {
        $parseStr = '';
        $array    =  explode(',', $templateName);
        foreach ($array as $templateName) {
            (substr($templateName, 0, 1) == '$') && $templateName = $this->get(substr($templateName, 1));
            empty($templateName) && E('您没有指定需引入的模板文件名称');
            if(false === strpos($templateName, $this->config['template_suffix'])) {
                $templateName = T($templateName);
            }
            is_file($templateName) or E(L('_TEMPLATE_NOT_EXIST_') . ':' . $templateName);
            $parseStr .= file_get_contents($templateName);
        }
        return $parseStr;
    }


    // 检查PHP语法
    protected function parsePhp($content) {
        if (ini_get('short_open_tag')) {
            // 开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识 
            $content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\';?>'."\n", $content);
        }
        // 模板禁用php语法
        if (C('TMPL_DENY_PHP') && false !== strpos($content, '<?php')) {
            E(L('_NOT_ALLOW_PHP_'));
        }
        return $content;
    }


    /**
     * 分析XML属性
     * @param string $attr  XML属性字符串
     * @return array
     */
    private function parseXmlAttrs($attr) {
        ($xml = simplexml_load_string('<tpl><tag ' . $attr . ' /></tpl>')) or E(L('_XML_TAG_ERROR_') . ':' . $attr);
        $xml  = get_object_vars($xml->tag->attributes());
        return isset($xml['@attributes']) ? array_change_key_case($xml['@attributes']) : array();
    }


    public function setTplVar(&$content) {
        $find = preg_match_all('/' . $this->config['taglib_begin'] . 'set\s+(.+?)\s*?\/' . $this->config['taglib_end'] . '/is',$content, $matches);
        while (--$find >= 0) {
            $content = str_replace($matches[0][$find], '', $content);
            $array   = $this->parseXmlAttrs($matches[1][$find]);
            if (!empty($array))
                $this->tVar = array_merge($this->tVar, $array);
        }
    }


    /**
     * 搜索模板页面中包含的TagLib库
     * @param string $content  模板内容
     * @return void
     */
    public function getIncludeTagLib(&$content) {
        $find = preg_match_all('/' . $this->config['taglib_begin'] . 'taglib\s+(.+?)\s*?\/' . $this->config['taglib_end'] . '/is',$content, $matches);
        while (--$find >= 0) {
            $content = str_replace($matches[0][$find], '', $content);
            $array   = $this->parseXmlAttrs($matches[1][$find]);
            if (!empty($array['name']))
                $this->tagLib = array_merge($this->tagLib, explode(',', $array['name']));
            else
                E('您没有指定需引入的标签库名称');
        }
    }


    /**
     * 获取完整TagLib命名空间和TagLib名
     * @param string $tagLibName      规则
     * @return string
     */
    private function getTagLibName($tagLibName) {
        (substr($tagLibName, 0, 1) == '$') && $tagLibName = $this->get(substr($tagLibName, 1));
        if (strpos($tagLibName, '\\')) {
            $className  = $tagLibName;
            $tagLibName = substr($tagLibName, strrpos($tagLibName,'\\') + 1);
        } else {
            $className = 'Core\\Template\\TagLib\\'.ucwords($tagLibName);            
        }
        return array($tagLibName, $className);
    }


    /**
     * TagLib库解析
     * @param string $tagLibName 要解析的标签库
     * @param string $content 要解析的模板内容
     * @param boolean $hide 是否隐藏标签库前缀
     * @return string
     */
    public function parseTagLib($tagLibName, &$content) {
        empty($tagLibName) && E('请指定标签库名称');

        list($tagLibName, $className) = $this->getTagLibName($tagLibName);
        class_exists($className) or E(L('_TAGLIB_NOT_EXIST_') . ':' . $className);

        $begin = $this->config['taglib_begin'];
        $end   = $this->config['taglib_end'];

        $TagLibObj = \Core\SF::instance($className);
        $that = $this;
        foreach ($TagLibObj->getTags() as $name => $val) {
            method_exists($TagLibObj, '_' . $name) or E($className . ':' . $name . '主解析方法必须被定义');
            $tags   = array($name);
            isset($val['alias']) && $tags = array_merge($tags, explode(',', $val['alias']));
            $level    = isset($val['level']) ? $val['level'] : 1;
            $closeTag = isset($val['close']) ? $val['close'] : true;

            if (false !== strpos(C('TAGLIB_BUILD_IN'), $tagLibName))
                $tagPrefix = '';
            else
                $tagPrefix = $tagLibName . ':';

            foreach ($tags as $tag) {

                $parseTag = $tagPrefix . $tag;

                method_exists($TagLibObj, '_' . $tag) or $tag = $name;

                if (!$closeTag) {
                    $patterns = '/' . $begin . $parseTag . '\s*([^' . $end . ']*)\/' . $end . '/is';
                    $content = preg_replace_callback($patterns, function($matches) use($TagLibObj, $tag, $that) {
                        return $that->parseXmlTag($TagLibObj, $tag, $matches[1]);
                    }, $content);
                } else {
                    $patterns = '/' . $begin . $parseTag . '\s*([^' . $end . ']*)' . $end . '(.*?)' . $begin . '\/' . $parseTag . $end . '/is';
                    for ($i = 0; $i < $level; $i++) {
                        $content = preg_replace_callback($patterns, function($matches) use($TagLibObj, $tag, $that) {
                            return $that->parseXmlTag($TagLibObj, $tag, $matches[1], $matches[2]);
                        }, $content);
                    }
                }
            }
        }
    }


    /**
     * 解析标签库的标签
     * @param object $TagLibObj    标签库对象实例
     * @param string $tag          标签名
     * @param string $attr         标签属性
     * @param string $content      标签内容
     * @return string
     */
    public function parseXmlTag($TagLibObj, $tag, $attr, $content = '') {
        $attrs = $TagLibObj->parseXmlAttr($attr, $tag);
        return call_user_func_array(array($TagLibObj, '_' . $tag), array($attrs, trim($content)));
    }


    /**
     * 模板标签解析
     * 格式： {TagName:args [|content] }
     * @param string $tagStr 标签内容
     * @return string
     */
    public function parseTag($tagStr) {
        $tagStr = $tagStr[1];
        $flag  = substr($tagStr, 0, 1);
        $flag2 = substr($tagStr, 1, 1);
        $name  = substr($tagStr, 1);
        if ('$' == $flag && '.' != $flag2 && '(' != $flag2) {
            return $this->parseVar($name);
        } elseif (':' == $flag) {
            return  '<?php echo ' . $name . ';?>';
        } elseif ('~' == $flag) {
            return  '<?php ' . $name . ';?>';
        }
        return C('TMPL_L_DELIM') . $tagStr . C('TMPL_R_DELIM');
    }


    /**
     * 模板变量解析,支持使用函数
     * 格式： {$varname|function1|function2=arg1,arg2}
     * @param string $varStr 变量数据
     * @return string
     */
    public function parseVar($varStr) {
        if (empty($varStr)) return '';
        static $_varParseList = array();
        $varStr = trim($varStr);
        if (!isset($_varParseList[$varStr])) {
            $varArray = explode('|', $varStr);
            $var = array_shift($varArray);
            if (false !== strpos($var, '.')) {
                $vars = explode('.', $var);
                $var  = array_shift($vars);
                switch (strtolower(C('TMPL_VAR_IDENTIFY'))) {
                    case 'array':
                        $parseStr = '$' . $var;
                        foreach ($vars as $val)
                            $parseStr .= '["' . $val . '"]';
                        break;
                    case 'obj':
                        $parseStr = '$' . $var;
                        foreach ($vars as $val)
                            $parseStr .= '->' . $val;
                        break;
                    default:
                        $arr_str = $obj_str = '$' . $var;
                        foreach ($vars as $val)
                            $arr_str .= '["' . $val . '"]';
                        foreach ($vars as $val)
                            $obj_str .= '->' . $val;
                        $parseStr = 'is_array($' . $var . ')?' . $arr_str . ':' . $obj_str;
                }
            } elseif (strpos($varStr, ':')) {
                $parseStr = '$' . str_replace(':', '->', $varStr);
            } else {
                $parseStr = '$' . $var;
            }
            (count($varArray) > 0 && !empty($varArray[0])) && $parseStr = $this->parseVarFunction($parseStr, $varArray);
            $_varParseList[$varStr] = '<?php echo ' . $parseStr . ';?>';
        }
        return $_varParseList[$varStr];
    }


    /**
     * 对模板变量使用函数
     * 格式 {$varname|function1|function2=arg1,arg2}
     * @param string $varname       变量名
     * @param array $fun_list       函数列表
     * @return string
     */
    public function parseVarFunction($varname, $fun_list) {
        $length = count($fun_list);
        for ($i = 0; $i < $length; $i++) {
            list($fun, $param) = explode('=', $fun_list[$i], 2);
            $fun = trim($fun);
            switch ($fun) {
                case 'default':
                    $varname = '(isset(' . $varname.')&&(' . $varname . '!==""))?(' . $varname . '):' . $param;
                    break;
                default:
                    if (false === stripos(C('TMPL_DENY_FUNC_LIST'), $fun)) {
                        if (isset($param)) {
                            if (strstr($param, '###')) {
                                $param   = str_replace('###', $varname, $param);
                                $varname = "$fun($param)";
                            } else {
                                $varname = "$fun($varname,$param)";
                            }
                        } elseif (!empty($fun)) {
                            $varname = "$fun($varname)";
                        }
                    } else {
                        E(L('_NOT_ALLOW_FUNCTION_') . ':' . $fun);
                    }
            }
        }
        return $varname;
    }
}