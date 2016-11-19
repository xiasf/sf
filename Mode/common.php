<?php     
/**
 * SF 引导类
 * Update time：2015-10-1 16:37:53
 */

return array(

    // 核心文件
    'core'      =>  array(
        SF_PATH         . 'Common/functions.php',
        COMMON_PATH     . 'Common/function.php',
        CORE_PATH       . 'Hook.class.php',
        CORE_PATH       . 'App.class.php',
        CORE_PATH       . 'Dispatcher.class.php',
        // CORE_PATH       . 'Log.class.php',
        // CORE_PATH       . 'Route.class.php',
        // CORE_PATH       . 'Controller.class.php',
        // CORE_PATH       . 'View.class.php',
        // BEHAVIOR_PATH   . 'BuildLiteBehavior.class.php',
        // BEHAVIOR_PATH   . 'ParseTemplateBehavior.class.php',
        // BEHAVIOR_PATH   . 'ContentReplaceBehavior.class.php',
    ),

    // 配置文件
    'config'    =>  array(
        SF_PATH   . 'Conf/config.php',   // 系统惯例配置
        CONF_PATH . 'config.php',      // 应用公共配置
    ),

    // 别名定义
    'alias'     =>  array(
        'Core\Log'               => CORE_PATH . 'Log.class.php',
        'Core\Log\Driver\File'   => CORE_PATH . 'Log/Driver/File.class.php',
        'Core\Exception'         => CORE_PATH . 'Exception.class.php',
        'Core\Model'             => CORE_PATH . 'Model.class.php',
        'Core\Db'                => CORE_PATH . 'Db.class.php',
        'Core\Template'          => CORE_PATH . 'Template.class.php',
        'Core\Cache'             => CORE_PATH . 'Cache.class.php',
        'Core\Cache\Driver\File' => CORE_PATH . 'Cache/Driver/File.class.php',
        'Core\Storage'           => CORE_PATH . 'Storage.class.php',
    ),

    // 行为扩展定义
    'hook'  =>  array(
        'app_init'     =>  array(
            'Behavior\BuildLiteBehavior', // 生成运行Lite文件
        ),

        'app_begin'     =>  array(
            'Behavior\ReadHtmlCacheBehavior', // 读取静态缓存
        ),

        'app_end'       =>  array(
            'Behavior\ShowPageTraceBehavior', // 页面Trace显示
        ),

        'url_dispatch'  => array(
            'Behavior\DeviceCheckBehavior',     // 访问设备检测
            ),

        'view_begin'    => array(
        ),

        'view_end'      => array(
        ),

        'view_parse'    =>  array(
            'Behavior\ParseTemplateBehavior', // 模板解析 支持PHP、内置模板引擎和第三方模板引擎
        ),

        'template_filter'=> array(
            'Behavior\ContentReplaceBehavior', // 模板输出替换
        ),

        'view_filter'   =>  array(
            'Behavior\WriteHtmlCacheBehavior', // 写入静态缓存
        ),
    ),
);
