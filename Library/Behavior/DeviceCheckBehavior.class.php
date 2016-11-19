<?php
namespace Behavior;
/**
 * 设备检测 检测室是否为移动设备
 * Update time：2014-8-4 14:29:06
 */
class DeviceCheckBehavior {
    public function run(&$params) {
        if (empty($_SERVER['HTTP_USER_AGENT']) ) {
            define('DEVICE', C('DEFAULT_DEVICE'));
        } elseif (
            (isset($_GET[C('VAR_DEVICE')]) && $_GET[C('VAR_DEVICE')]  == 'wap')
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Wechat') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'Adr') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'MeeGo') !== false
            || stripos($_SERVER['HTTP_USER_AGENT'], 'PlayBook') !== false) {
            define('DEVICE', 'wap');
        } else {
            define('DEVICE', 'pc');
        }
    }
}