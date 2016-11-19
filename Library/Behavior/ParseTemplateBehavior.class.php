<?php
namespace Behavior;
use Core\Storage;
use Core\SF;
/**
 * 系统行为扩展：模板解析
 * Update time：2015-10-18 20:32:59
 */
class ParseTemplateBehavior {

    /**
     * 视图解析
     * @param array $data  array('var', 'file', 'content', 'prefix')
     * @return viod
     */
    public function run(&$data) {
        $engine  = strtolower(C('TMPL_ENGINE_TYPE'));
        $content = !empty($data['content']) ? $data['content'] : $data['file'];
        $prefix  = !empty($data['prefix'])  ? $data['prefix']  : C('TMPL_CACHE_PREFIX');

        if ('sf' == $engine) {
            if (C('TPL_CACHE_ON') && ((!empty($data['content']) && $this->checkContentCache($data['content'], $prefix)) || $this->checkCache($data['file'], $prefix))) {
                Storage::load($this->getCacheFile($content, $prefix), $data['var']);
            } else {
                $tpl = SF::instance('Core\\Template');
                $tpl->fetch($content, $data['var'], $prefix);
            }

        } else {
            // 调用第三方模板引擎解析和输出
            if (strpos($engine, '\\')) {
                $class = $engine;
            } else {
                $class = 'Core\\Template\\Driver\\' . ucwords($engine);
            }
            if (class_exists($class)) {
                $tpl = new $class;
                $tpl->fetch($content, $data['var']);
            } else {
                E(L('_NOT_SUPPORT_') . ':' . $class);
            }
        }
    }


    /**
     * 获取模板缓存文件名
     * @param string $templateFile  模板文件或内容
     * @param string $prefix        模板缓存前缀
     * @return boolean
     */
    private function getCacheFile($templateFile, $prefix) {
        static $tplCacheFile = array();
        $key = md5($templateFile . $prefix);
        if (!isset($tplCacheFile[$key])) {
            if (is_file($templateFile)) {
                $_tplFile = str_replace(TMPL_PATH, '', $templateFile);
                if ($_tplFile != $templateFile)
                    $tplCacheFile[$key] = (defined('TMPL_CACHE_PATH') ? TMPL_CACHE_PATH : CACHE_PATH . C('DEFAULT_CACHE_TMPL') . '/') . pathinfo($_tplFile, PATHINFO_DIRNAME) . '/' . $prefix . pathinfo($_tplFile, PATHINFO_FILENAME) . C('TMPL_CACHE_SUFFIX');
                else
                    $tplCacheFile[$key] = (defined('TMPL_CACHE_PATH') ? TMPL_CACHE_PATH : CACHE_PATH . C('DEFAULT_CACHE_TMPL') . '/') . $prefix . pathinfo($_tplFile, PATHINFO_FILENAME) . C('TMPL_CACHE_SUFFIX');
            } else {
                $tplCacheFile[$key] = CACHE_PATH . C('NO_DEFAULT_CACHE_TMPL') . '/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . $prefix . md5($templateFile) . C('TMPL_CACHE_SUFFIX');
            }
        }
        return $tplCacheFile[$key];
    }


    /**
     * 检测模板缓存是否有效
     * @param string $templateFile  模板文件名
     * @param string $prefix        模板缓存前缀
     * @return boolean
     */
    protected function checkCache($templateFile, $prefix = '') {
        if (!Storage::has($tmplCacheFile = $this->getCacheFile($templateFile, $prefix))) {
            return false;
        } elseif (filemtime($templateFile) > Storage::get($tmplCacheFile,'mtime')) {
            return false;
        } elseif (C('TMPL_CACHE_TIME') != 0 && time() > (Storage::get($tmplCacheFile,'mtime') + C('TMPL_CACHE_TIME'))) {
            return false;
        }
        if (C('LAYOUT_ON')) {
            $layoutFile = THEME_PATH . C('LAYOUT_NAME') . C('TMPL_TEMPLATE_SUFFIX');
            if (filemtime($layoutFile) > Storage::get($tmplCacheFile, 'mtime')) {
                return false;
            }
        }
        return true;
    }


    /**
     * 检测数据内容缓存是否存在
     * @param string $tmplContent  模板内容
     * @param string $prefix       模板缓存前缀
     * @return boolean
     */
    protected function checkContentCache($tmplContent, $prefix = '') {
        if (Storage::has($this->getCacheFile($tmplContent, $prefix))) {
            return true;
        } else {
            return false;
        }
    }
}
