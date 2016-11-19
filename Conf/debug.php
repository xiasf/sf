<?php
/**
 * myphp 默认的调试模式配置文件
 * Update time：2015-3-7 13:50:34
 */
defined('APP_NAME') or exit;				// 拦截非法访问
return  array(
    'SHOW_PAGE_TRACE'      => true,       	// 是否打开TRACE
    'TPL_CACHE_ON'         => false, 		// 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_STRIP_SPACE'     => true, 		// 是否去除模板文件里面的html空格与换行

    'DB_DEBUG'  		   => true,	 		// 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'      => false,     	// 启用字段缓存
);