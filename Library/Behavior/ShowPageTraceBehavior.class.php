<?php
namespace Behavior;
use Core\Log;
/**
 * 系统行为扩展：页面Trace显示输出
 * Update time：2015-11-8 16:36:02
 */
class ShowPageTraceBehavior {
    protected $tracePageTabs =  array('BASE' => '基本','FILE' => '文件','INFO' => '流程','ERROR' => '错误','DEBUG' => '调试','SQL' => 'SQL','CONFING' => '配置','CONSTANT' => '常量', 'GET' => 'GET', 'POST' => 'POST', 'COOKIE' => 'Cookie', 'SESSION' => 'SESSION', 'GLOBALS' => 'GLOBALS',);

    // 行为扩展的执行入口必须是run
    public function run(&$params) {
        if(!IS_AJAX && !IS_CLI && C('SHOW_PAGE_TRACE')) {
            echo $this->showTrace();
        }
    }


    /**
     * 显示页面Trace信息
     * @access private
     */
    private function showTrace() {
        $debug = trace();
        $trace = array();
        $tabs  = array_merge(C('TRACE_PAGE_TABS', null, $this->tracePageTabs), array('OTHER' => 'Other', 'TRACE' => 'Trace', 'SF' => '关于'));
        $sid = session_id();
        empty($sid) or $sid = session_name() . '=' . $sid;
        $files     = get_included_files();
        $file_list = array();
        foreach ($files as $key=>$file){
            $file_list[] = $file.' ( '.number_format(filesize($file)/1024,2).' KB )';
        }
        $cookie = '';
        foreach ($_COOKIE as $key => $value){
            $cookie .= $key . '=' . $value . ';';
        }
        $base = array(
            '请求信息'  =>  date('Y-m-d H:i:s', NOW_TIME).' '. $_SERVER['SERVER_PROTOCOL'].' '.REQUEST_METHOD.': '.APP_DOMAIN.__SELF__,
            '应用信息'  =>  '当前入口：'.(defined('MODULE_ALIAS')?MODULE_ALIAS:MODULE_NAME).'/'.(defined('CONTROLLER_ALIAS')?:CONTROLLER_NAME).'/'.(defined('ACTION_ALIAS')?:ACTION_NAME).' 实际入口：'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME.' 应用名称：'. APP_NAME .' 应用模式：'. APP_MODE .' 应用状态：'. (APP_STATUS ? : '无') . ' 调试模式：'. (APP_DEBUG ? 'true' : 'false') . ' 数据调试：'. (C('DB_DEBUG') ? 'true' : 'false') . ' URL模式：' . C('URL_MODEL'),
            '模板信息'  =>  '访问设备：'.(defined('DEVICE')?DEVICE:'未知').' 主模板：'.(defined('TPL_FILE')?TPL_FILE:'无').' 当前主题：'. (defined('THEME_NAME')?THEME_NAME:'无'),
            '运行时间'  =>  $this->showTime(),
            '吞吐速率'  =>  number_format(1/G('beginTime','viewEndTime'),2).'req/s',
            '内存开销'  =>  MEMORY_LIMIT_ON?number_format((memory_get_usage() - $GLOBALS['sf_startUseMems'])/1024,2).' kb':'不支持',
            '查询信息'  =>  'insert: ' . N('db_insert') . ' delete: ' . N('db_delete') . ' update: ' . N('db_update') . ' select: ' . N('db_select') . ' executeTime: (' . N('db_insert_executeTime') . 's, ' . N('db_delete_executeTime') . 's, ' . N('db_update_executeTime') . 's, ' . N('db_select_executeTime') . 's)',
            '缓存信息'  =>  ' gets: ' . N('cache_read') . ' writes: ' . N('cache_write'),
            '文件加载'  =>  count(get_included_files()),
            '配置加载'  =>  count(C()),
            '会话信息'  =>  !empty($sid) ? $sid : '无',
            'Cookies'    =>  $cookie ? : '无',
            '主机信息'  =>  PHP_OS . ' ' . $_SERVER['SERVER_SOFTWARE']. ' OUTPUT_BUFFERING: ' . OUTPUT_BUFFERING,
            'PHP 版本'  =>  PHP_VERSION,
            '系统版本'  =>  SF_VERSION,
            '代理信息'  =>  $_SERVER['HTTP_USER_AGENT'],
            '嘻嘻嘻嘻'  =>  'baby I love you forever :) baby 我永远都乖着呢',
            );
        $traceFile  =  COMMON_PATH.'Conf/trace.php';
        if(is_file($traceFile)) {
            $base = array_merge($base, include $traceFile);
        }

        $const = get_defined_constants(true);
        $const = $const['user'];
        foreach ($const as $key => $value) {
            if ($value === true) $const[$key] = 'true';
            elseif ($value === false) $const[$key] = 'false';
            elseif ($value === null) $const[$key] = 'null';
            elseif ($value === '') $const[$key] = "''";
        }
        $confing = C();
        foreach ($confing as $key => $value) {
            if ($value === true) $confing[$key] = 'true';
            elseif ($value === false) $confing[$key] = 'false';
            elseif ($value === null) $confing[$key] = 'null';
            elseif ($value === '') $confing[$key] = "''";
        }
        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS' && $key != '_POST' && $key != '_GET' && $key != '_COOKIE' && $key != '_FILES' && $key != '_REQUEST' && $key != '_SERVER')
                $g[$key] = $value;
        }
        $debug['CONSTANT'] = $const;
        $debug['CONFING'] = $confing;
        $debug['GET'] = $_GET;
        $debug['POST'] = $_POST;
        $debug['COOKIE'] = $_COOKIE;
        $debug['SESSION'] = $_SESSION;
        $debug['GLOBALS'] = $g;
        $debug['BASE'] = $base;
        $debug['FILE'] = $file_list;
        $debug['OTHER'] = array_diff_key($debug, $tabs);
        ob_start();
        debug_print_backtrace();
        $debug['TRACE'] = explode("\n", preg_replace('/#\d+/', '<strong style="color:red;border: 1px red solid;padding:2px;">' . '\0' . '</strong>', trim(ob_get_clean())));
        $debug['SF']  = <<<EOF

        <pre style="padding: 0;margin: 0;line-height: normal;font-family: 宋体;">

        baby SF 为你献上。
                                     _   _                      
           _______________          |*\_/*|________   
          |  ___________  |        ||_/-\_|______  |                惟爱你，你的声音是幸福的动力。 
          | |           | |        | |           | |                 
          | |   0   0   | |        | |   0   0   | |                惟爱你，想着你心里就多一片光明。
          | |     -     | |        | |     -     | |             
          | |   \___/   | |        | |   \___/   | |                惟爱你，不论一路经历再大暴风雨，我会永远坚定陪着你。
          | |___     ___| |        | |___________| |                
          |_____|\_/|_____|        |_______________|                
            _|__|/ \|_|_.............._|________|_                  我的小baby，baby，baby，baby永远不会忘记那一年那一月那一日幸福的开始。2014-10-19 18:00:00  
           / ********** \            / ********** \   
         /  ************  \        /  ************  \               我的baby。   
        --------------------      --------------------              

        遇见你是我今生最大的福气。你是我一首歌，一首永远都唱不完的歌。

        所有心愿为你实现。用尽所有力气不是为我，我什么都愿意。

        2015-11-21 16:44:53
        </pre>
EOF;

        foreach ($tabs as $name => $title) {
            $name          = strtoupper($name);
            $trace[$title] = isset($debug[$name]) ? $debug[$name] : array();
        }

        if($save = C('PAGE_TRACE_SAVE')) {
            // 日志保存要将Trace内容html反编码
            $array = array();
            if (is_string($save)) {
                if ($save == '*')
                    $array = $trace;
                elseif(isset($trace[$save]))
                    $array = $trace[$tabs[$save]];
            } elseif(is_array($save)) {
                foreach ($save as $tab){
                    if (isset($trace[$tabs[$tab]]))
                        $array[$tabs[$tab]] = $trace[$tabs[$tab]];
                }
            }
            $trace_str = '';
            foreach ($array as $key => $value) {
                $trace_str .= "[{$key}]\r\n";
                if(is_array($value)) {
                    foreach ($value as $k => $v){
                        $trace_str .= (!is_numeric($k) ? $k . ':' : '') . print_r($v, true) . "\r\n";
                    }
                } else {
                    $trace_str .= print_r($value, true) . "\r\n";
                }
                $trace_str .= "\r\n";
            }

            if ($trace_str)
                Log::write("\r\n" . $trace_str, 'DEBUG', $type = '', C('LOG_PATH') . date('y_m_d') . '_trace.log');
        }

        ob_start();
        include C('TMPL_TRACE_FILE');
        return ob_get_clean();
    }

    /**
     * 获取运行时间
     */
    private function showTime() {
        // 显示运行时间
        G('beginTime', $GLOBALS['sf_beginTime']);
        G('viewEndTime');
        return G('beginTime','viewEndTime').'s ( Load:'.G('beginTime','loadTime').'s Init:'.G('loadTime','initTime').'s Exec:'.G('initTime','viewStartTime').'s Template:'.G('viewStartTime','viewEndTime').'s )';
    }
}
