<?php
namespace Core\Template;
/**
 * SF标签库TagLib解析基类
 * Update time：2015-10-24 20:12:28
 */
abstract class TagLib {

    /**
     * 标签库定义XML文件
     * @var string
     */
    protected $xml      = '';

    protected $tags     = array();  // 标签定义

    /**
     * 标签库名称
     * @var string
     */
    protected $tagLib   ='';

    /**
     * 标签库标签列表
     * @var string
     */
    protected $tagList  = array();

    /**
     * 标签库分析数组
     * @var string
     */
    protected $parse    = array();

    /**
     * 标签库是否有效
     * @var string
     */
    protected $valid    = false;

    /**
     * 当前模板对象
     * @var object
     */
    protected $tpl;

    protected $comparison = array(' nheq '=>' !== ', ' heq '=>' === ', ' neq '=>' != ', ' eq '=>' == ', ' egt '=>' >= ', ' gt '=>' > ', ' elt '=>' <= ', ' lt '=>' < ');


    /**
     * 架构函数
     */
    public function __construct() {
        $this->tagLib = get_class($this);
        $this->tpl    = \Core\SF::instance('Core\\Template');
    }


    /**
     * TagLib标签属性分析 返回标签属性数组
     * @param string $tagStr 标签内容
     * @return array
     */
    public function parseXmlAttr($attr, $tag) {
        $attr = str_replace('&', '___', $attr);
        $xml  = simplexml_load_string('<tpl><tag ' . $attr . ' /></tpl>') or E(L('_XML_TAG_ERROR_') . ':' . $attr);
        $xml  = get_object_vars($xml->tag->attributes());
        if (isset($xml['@attributes']) && $attr_info = array_change_key_case($xml['@attributes'])) {
            $tag  = strtolower($tag);
            if (!isset($this->tags[$tag])) { // 当前使用的可能是别名
                foreach($this->tags as $val) {
                    if (isset($val['alias']) && in_array($tag, explode(',', $val['alias']))) {
                        $item = $val;
                        break;
                    }
                }
            } else {
                $item = $this->tags[$tag];
            }
            if (isset($item['attr'])) {
                $attr = explode(',', $item['attr']);
            } else {
                $attr = array();
            }
            if (isset($item['must'])) {
                $must = explode(',', $item['must']);
            } else {
                $must = array();
            }
            $attrs = array();
            foreach($attr as $name) {
                if (isset($attr_info[$name])) {
                    $attrs[$name] = str_replace('___', '&', $attr_info[$name]);
                } elseif (false !== array_search($name, $must)) {
                    E($this->tagLib . ':' . $tag . ' ' . $name . '属性必须指定哦！');
                }
            }
            return $attrs;
        } else {
            !empty($this->tags[$tag]['must']) && E($this->tagLib . ':' . $tag . ' ' . $this->tags[$tag]['must'] . '属性必须指定哦！');
            return array();
        }
    }


    /**
     * 解析条件表达式 $name.a == "sf"
     * @param string $condition 表达式标签内容
     * @return string
     */
    public function parseCondition($condition) {
        if (empty($condition)) return '';
        $condition = str_ireplace(array_keys($this->comparison), array_values($this->comparison), $condition);
        switch(strtolower(C('TMPL_VAR_IDENTIFY'))) {
            case 'array':
                $condition = preg_replace_callback('/\$(\w+)\.((\w+\.?)+)\s/is', function ($matches) {$vars = explode('.', trim(trim($matches[2], '.')));$parseStr = '$'.$matches[1];foreach ($vars as $val) $parseStr .= '["' . $val . '"]';return $parseStr . ' ';}, $condition);
                break;
            case 'obj':
                $condition = preg_replace_callback('/\$(\w+)\.((\w+\.?)+)\s/is', function ($matches) {$vars = explode('.', trim(trim($matches[2], '.')));$parseStr = '$'.$matches[1];foreach ($vars as $val) $parseStr .= '->' . $val;return $parseStr . ' ';}, $condition);
                break;
            default:
                $condition = preg_replace_callback('/\$(\w+)\.((\w+\.?)+)\s/is', function ($matches) {
                    $vars = explode('.', trim(trim($matches[2], '.')));
                    $arr_str = $obj_str = '$' . $matches[1];
                    foreach ($vars as $val)
                        $arr_str .= '["' . $val . '"]';
                    foreach ($vars as $val)
                        $obj_str .= '->' . $val;
                    return ('(is_array($' . $matches[1] . ')?' . $arr_str . ':' . $obj_str) . ') ';
                }, $condition);
        }
        return $condition;
    }


    /**
     * 自动识别构建变量
     * @param string $name 变量描述
     * @return string
     */
    public function autoBuildVar($varStr) {
        if (empty($varStr)) return '';
        if (strpos($varStr, '.')) {
            $vars = explode('.', $varStr);
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
        } elseif (defined($varStr)) {
            $parseStr = $varStr;
        } else {
            $parseStr = '$' . $varStr;
        }
        return $parseStr;
    }


    // 获取标签定义
    public function getTags() {
        return $this->tags;
    }
}