<?php
namespace Behavior;
/**
 * 系统行为扩展：模板内容输出替换
 */
class ContentReplaceBehavior {

    public function run(&$content) {
        $content = $this->templateContentReplace($content);
    }

    /**
     * 模板内容替换
     * @param string $content 模板内容
     * @return string
     */
    protected function templateContentReplace($content) {
        // 系统默认的模板变量替换
        $replace =  array(
            '__ROOT__'       => __ROOT__,               // 当前网站地址
            '__APP__'        => __APP__,                // 当前应用地址
            '__MODULE__'     => __MODULE__,             // 当前模块地址
            '__ACTION__'     => __ACTION__,             // 当前操作地址
            '__SELF__'       => __SELF__,               // 当前页面地址
            '__CONTROLLER__' => __CONTROLLER__,         // 当前控制器地址
            '__URL__'        => __CONTROLLER__,         // 当前控制器地址
            '__PUBLIC__'     => __ROOT__ . '/Public',   // 站点公共目录
        );
        // 允许用户自定义模板的字符串替换
        if (is_array(C('TMPL_PARSE_STRING')))
            $replace = array_merge($replace, C('TMPL_PARSE_STRING'));
        return str_replace(array_keys($replace), array_values($replace), $content);
    }
}