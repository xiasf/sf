<?php
/**
 * SF 默认的配置文件（系统约定）
 * Update time：2016-2-18 08:46:22
 */

defined('APP_NAME') or exit;

return array(

    /* 模块相关设置 */
    'MODULE_ALLOW_LIST'     =>  array(),                                            // 允许访问的模块
    'MODULE_DENY_LIST'      =>  array('Data', 'Common'),                            // 禁止访问的模块
    'DEFAULT_MODULE'        =>  'Home',                                             // 默认模块
    'URL_MODULE_MAP'        =>  array('sf' => 'home_', 'root' => 'admin_'),         // 模块映射
    'DEFAULT_MODULE'        =>  'Home',                                             // 默认模块
    'VAR_MODULE'            =>  'm',                                                // 默认模块获取变量
    'MULTI_MODULE'          => true,


    /* 控制器相关设置 */
    'DEFAULT_C_LAYER'       =>  'Controller',                                       // 默认的控制器层名称
    'DEFAULT_CONTROLLER'    =>  'Index',                                            // 默认控制器名称
    'VAR_CONTROLLER'        =>  'c',                                                // 默认控制器获取变量
    'URL_CONTROLLER_MAP'    =>  array('a_' => 'b', 'c_' => 'd'),                    // 控制器映射


    /* 操作相关设置 */
    'DEFAULT_ACTION'        =>  'index',                                            // 默认操作名称
    'VAR_ACTION'            =>  'a',                                                // 默认操作获取变量
    'ACTION_SUFFIX'         =>  '',                                                 // 操作方法后缀
    'URL_ACTION_MAP'        =>  array(                                              // 操作映射
                                    'index_' => array(
                                                    'index' => array('a', 'c=1&d=2'),
                                                    'inde_' => 'b',
                                                ),
                                ),


    /* URL相关设置 */
    'URL_CASE_INSENSITIVE'  =>  true,               // 默true 表示URL不区分大小写 false则表示区分大小写
    'URL_MODEL'             =>  2,                  // 0 普通模式 1 PATHINFO模式 2 REWRITE模式 3 兼容模式
    'VAR_PATHINFO'          =>  's',                // 兼容模式PATHINFO获取变量
    'URL_PATHINFO_DEPR'     =>  '/',                // PATHINFO模式下，各参数之间的分割符号
    'URL_PATHINFO_FETCH'    =>  'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL',// 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
    'URL_REQUEST_URI'       =>  'REQUEST_URI',      // 获取当前页面地址的系统变量 默认为REQUEST_URI
    'URL_HTML_SUFFIX'       =>  'html|shtml',       // URL允许的伪静态后缀设置
    'URL_DENY_SUFFIX'       =>  'ico|png|gif|jpg',  // URL禁止访问的后缀设置
    'URL_PARAMS_BIND'       =>  true,               // URL变量绑定到Action方法参数
    'URL_PARAMS_BIND_TYPE'  =>  0,                  // URL变量绑定的类型 0 按变量名绑定 1 按变量顺序绑定
    'URL_PARAMS_FILTER'     =>  false,              // URL变量绑定过滤
    'URL_PARAMS_FILTER_TYPE'=>  '',                 // URL变量绑定过滤方法 如果为空 调用DEFAULT_FILTER


    /* 路由相关设置 */
    'URL_ROUTER_ON'         =>  false,              // 是否开启URL路由
    'URL_ROUTE_RULES'       =>  array(),            // 默认路由规则 针对模块
    'URL_MAP_RULES'         =>  array(),            // URL映射定义规则


    /* 域名相关设置 */
    'APP_SUB_DOMAIN_DEPLOY' =>  1,        // 是否开启子域名部署
    'APP_DOMAIN_SUFFIX'     =>  '',       // 域名后缀为二级后缀 *.com.cn *.net.cn 之类的域名后缀必须设置，否则不是必须
    'APP_SUB_DOMAIN_RULES'  =>  array(    // 子域名部署规则
                                    '127.0.0.1:8082'     => 'Home/Index_',                      // IP + 端口 部署
                                    'admin.domain1.com'  => 'Admin',                            // 完整域名部署
                                    'admin'              => 'Admin',                            // 子域名部署
                                    'admin'              => array('Admin', 'var1=1&var2=2'),    // 传入参数
                                    'test.admin'         => 'Admin/Test',                       // 三级域名 + 绑定控制器
                                    '*'                  => array('Test', 'var1=1&domain2=*'),  // 泛域名(_GET['domain2']：$domain2 当前二级域名名称)
                                    '*.user'             => array('User', 'status=1&domain3=*'),// 三级泛域名
                                ),


    /* session相关设置 */
    'SESSION_AUTO_START'     => true,           // 是否自动开启Session
    'SESSION_OPTIONS'        => array(),        // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'           => '',             // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'         => '',             // session 前缀
    //'VAR_SESSION_ID'      =>  'session_id',   //sessionID的提交变量


    /* cookie相关设置 */
    'COOKIE_EXPIRE'         =>  0,              // Cookie有效期
    'COOKIE_DOMAIN'         =>  '',             // Cookie有效域名
    'COOKIE_PATH'           =>  '/',            // Cookie路径
    'COOKIE_PREFIX'         =>  '',             // Cookie前缀 避免冲突
    'COOKIE_SECURE'         =>  false,          // Cookie安全传输
    'COOKIE_HTTPONLY'       =>  '',             // Cookie httponly设置


    /* 数据库相关设置 */
    'DB_TYPE'               =>    'mysql', 	    // 数据库类型
	'DB_HOST'               =>    '127.0.0.1',  // 服务器地址
	'DB_USER'               =>    'root', 		// 用户名
	'DB_PWD'                =>    'root', 		// 密码
	'DB_PORT'               =>    '3306', 		// 端口
    'DB_NAME'               =>    '5el',        // 数据库名
	'DB_PREFIX'             =>    'tb_', 		// 数据库表前缀
    'DB_CHARSET'            =>    'utf8',       // 数据库编码默认采用utf8
    'DB_PARAMS'             =>    array(), 	    // 数据库连接参数    
    'DB_DEBUG'  		    =>    false,	 	// 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>    true,     	// 启用字段缓存
    'DB_DEPLOY_TYPE'        =>    0, 			// 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>    false,    	// 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>    1, 			// 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>    '', 			// 指定从服务器序号


    /* 日志设置 */
    'LOG_TYPE'              =>  'File',                     // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'ERROR,DEBUG,INFO,SQL',     // 系统默认允许记录的日志类别
    'LOG_FILE_SIZE'         =>  2097152,                    // 日志文件大小限制（单位：byte）


    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  0,                  // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,              // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,              // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  '',                 // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'File',             // 数据缓存类型
    'DATA_CACHE_PATH'       =>  TEMP_PATH,          // 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  false,              // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,                  // 子目录缓存级别


    /* 模板引擎相关设置 */
    'DEFAULT_CHARSET'       =>  'utf-8',                // 默认输出编码
    'TMPL_CONTENT_TYPE'     =>  'text/html',            // 默认模板输出类型
    'HTTP_CACHE_CONTROL'    =>  'private',              // 网页缓存控制
    'TMPL_DETECT_THEME'     =>  false,                  // 自动侦测模板主题
    'TMPL_ENGINE_TYPE'      =>  'SF',                   // 默认模板引擎
    'TMPL_DENY_FUNC_LIST'   =>  'exit',                 // 模板引擎禁用函数
    'TMPL_DENY_PHP'         =>  true,                   // 默认模板引擎是否禁用PHP原生代码
    'TMPL_L_DELIM'          =>  '{',                    // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}',                    // 模板引擎普通标签结束标记
    'TMPL_VAR_IDENTIFY'     =>  'array',                // 模板变量识别。留空自动判断,参数为'obj'则表示对象
    'TMPL_STRIP_SPACE'      =>  true,                   // 是否去除模板文件里面的html空格与换行
    'TPL_CACHE_ON'          =>  true,                   // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_TIME'       =>  0,                      // 模板缓存有效期 0 为永久，(以数字为值，单位:秒)
    'DEFAULT_TMPL'          =>  'templates',            // 默认模板文件夹名称 TMPL
    'DEFAULT_CACHE_TMPL'    =>  'templates_cache',      // 默认模板缓存文件夹名称
    'NO_DEFAULT_CACHE_TMPL' =>  'no_templates_cache',   // 默认无模板缓存文件夹名称
    'DEFAULT_DEVICE'        =>  'pc',                   // 默认设备
    'DEFAULT_THEME'         =>  'default',              // 默认模板主题名称
    'VAR_DEVICE'            =>  'device',               // 默认设备获取变量
    'VAR_THEME'             =>  'theme',                // 默认主题切换变量
    'THEME_LIST'            =>  'default,default1',     // 允许切换的主题列表
    'TMPL_LOAD_DEFAULTTHEME'=>  true,                   // 是否允许使用差异化主题模板
    'TMPL_TEMPLATE_SUFFIX'  =>  '.html',                // 默认模板文件后缀
    'TMPL_CACHE_PREFIX'     =>  '',                     // 模板缓存前缀标识，可以动态改变
    'TMPL_CACHE_SUFFIX'     =>  '.tpl.php',             // 默认模板缓存后缀
    'TMPL_FILE_DEPR'        =>  '/',                    // 模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符


    /* 模板布局设置 */
    'LAYOUT_ON'             =>  false,              // 是否启用布局 默认为不启用
    'DEFAULT_L_LAYER'       =>  'Layout',           // 布局层名称 默认为layout
    'LAYOUT_NAME'           =>  'layout',           // 当前布局名称 默认为layout
    'TMPL_LAYOUT_ITEM'      =>  '{__CONTENT__}',    // 布局模板的内容替换标识


    /* 标签库相关设置 */
    'TAGLIB_BEGIN'          =>  '<',                // 标签库标签开始标记
    'TAGLIB_END'            =>  '>',                // 标签库标签结束标记
    'TAGLIB_LOAD'           =>  true,               // 是否使用内置标签库之外的其它标签库，默认自动检测
    'TAGLIB_BUILD_IN'       =>  'Cx',               // 默认内置标签库名称(标签使用不必指定标签库名称)
    'TAGLIB_PRE_LOAD'       =>  '',                 // 需要额外加载的标签库(须指定标签库名称)，多个以逗号分隔


    /* 文件上传相关设置 */
    'FILE_UPLOAD_TYPE'      =>  'Local',            // 默认文件上传方式


    /* 数据安全、加密相关设置 */
    'DATA_CRYPT_TYPE'       =>  'Think',                                // 默认数据加密方式
    'DATA_CRYPT_KEY'        =>  md5('Shen Fang I Love You Forever'),    // 默认数据加密盐值


    /* 系统异常错误设置 */
    'ERROR_PAGE'            =>  '',                                     // 错误定向页面
    'TMPL_EXCEPTION_FILE'   =>  SF_PATH . 'Tpl/exception.tpl',          // 错误页面模板文件
    'ERROR_MESSAGE'         =>  '糟了个糕，系统开小差了',               // 关闭调试时显示的 错误信息
    'ERROR_FILE'            =>  'baby 都是我不好',                      // 关闭调试时显示的 错误文件
    'ERROR_LINE'            =>  '1314520',                              // 关闭调试时显示的 错误行号
    'ERROR_TYPE'            =>  '逗你玩呢',                             // 关闭调试时显示的 错误类型
    'ERROR_CODE'            =>  '520',                                  // 关闭调试时显示的 错误代码
    'ERROR_TRACE'           =>  '傻瓜，你这傻瓜，不能吃的果子。',       // 关闭调试时显示的 错误跟踪
    'ERROR_BUFFERING_CLEAN' =>  '就不告诉你，我不想说的，你问一百遍我也不会说，我想说的你不问我也会告诉你。',                       // 关闭调试时显示的 buffering clean


    /* 系统调试相关设置 */
    'TMPL_TRACE_FILE'       =>  SF_PATH . 'Tpl/page_trace.tpl',   // TRACE模板文件
    'SHOW_PAGE_TRACE'       =>  false,                            // 是否打开TRACE
    'TRACE_MAX_RECORD'      =>  5200,                             // 每个级别的信息 最大记录数
    'TRACE_PAGE_TABS'       =>  array('BASE'        =>  '基本',   // 默认TRACE选项卡
                                      'FILE'        =>  '文件',
                                      'INFO'        =>  '流程',
                                      'ERROR'       =>  '错误',
                                      'DEBUG'       =>  '调试',
                                      'SQL'         =>  'SQL',
                                      'CONFING'     =>  '配置',
                                      'CONSTANT'    =>  '常量',
                                      'GET'         =>  'GET',
                                      'POST'        =>  'POST',
                                      'COOKIE'      =>  'Cookie',
                                      'SESSION'     =>  'SESSION',
                                      'GLOBALS'     =>  'GLOBALS',
                                    ),
    'PAGE_TRACE_SAVE'       =>  '',


    /* 杂项设置 */
    'SF_LOGO'               =>  SF_PATH . 'Tpl/sf.ico',             // 系统logo
    'DEFAULT_LANG'          =>  'zh-cn',                            // 默认语言
    'DEFAULT_TIMEZONE'      =>  'PRC',                              // 默认时区
    'DEFAULT_M_LAYER'       =>  'Model',                            // 默认的模型层名称
    'TMPL_ACTION_ERROR'     =>  SF_PATH . 'Tpl/dispatch_jump_no.tpl',  // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  SF_PATH . 'Tpl/dispatch_jump_ok.tpl',  // 默认成功跳转对应的模板文件
    'DEFAULT_AJAX_RETURN'   =>  'JSON',                             // 默认AJAX 数据返回格式,可选JSON XML ...
    'DEFAULT_JSONP_HANDLER' =>  'jsonpReturn',                      // 默认JSONP格式返回的处理方法
    'DEFAULT_FILTER'        =>  'htmlspecialchars',                 // 默认参数过滤方法 用于I函数...
    'VAR_AJAX_SUBMIT'       =>  'ajax',                             // 默认的AJAX提交变量
    'VAR_JSONP_HANDLER'     =>  'callback',                         // 默认的回调函数名
);