<?php
namespace Core;
/**
 * SF 控制器基类
 * Update time：2015-10-16 22:59:28
 */
abstract class Controller {

    /**
     * 视图实例对象
     */    
    protected $view   = null;

    /**
     * 控制器参数
     */
    protected $config = array();


   /**
     * 架构函数 取得模板对象实例
     */
    public function __construct() {
        Hook::listen('action_begin', $this->config);

        $this->view = SF::instance('Core\View');

        method_exists($this,'_initialize') && $this->_initialize();
    }


    /**
     * 模板显示 调用内置的模板引擎显示方法
     * @param string $templateFile  模板文件规则
     * @param string $charset       输出编码
     * @param string $contentType   输出类型
     * @param string $content       输出内容
     * @param string $prefix        模板缓存前缀
     * @return void
     */
    protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        $this->view->display($templateFile, $charset, $contentType, $content, $prefix);
    }


    /**
     * 输出内容文本可以包括Html 并支持内容解析
     * @param string $content       输出内容
     * @param string $charset       模板输出字符集
     * @param string $contentType   输出类型
     * @param string $prefix        模板缓存前缀
     * @return mixed
     */
    protected function show($content, $charset = '', $contentType = '', $prefix = '') {
        $this->view->display('', $charset, $contentType, $content, $prefix);
    }


    /**
     * 获取输出页面内容
     * @param string $templateFile  模板文件规则
     * @param string $content       模板输出内容
     * @param string $prefix        模板缓存前缀
     * @return string
     */
    protected function fetch($templateFile = '', $content = '', $prefix = '') {
        return $this->view->fetch($templateFile , $content , $prefix);
    }


    /**
     * 创建静态页面
     * @param string $htmlfile      生成的静态文件名称
     * @param string $htmlpath      生成的静态文件路径
     * @param string $templateFile  模板文件规则
     * @return string
     */
    protected function buildHtml($htmlfile = '', $htmlpath = '', $templateFile = '') {
        $content  = $this->fetch($templateFile);
        $htmlpath = !empty($htmlpath) ? $htmlpath : HTML_PATH;
        $htmlfile = $htmlpath . $htmlfile . C('HTML_FILE_SUFFIX');
        Storage::put($htmlfile,$content,'html');
        return $content;
    }


    /**
     * 模板主题设置
     * @param string $theme 模版主题
     * @return Action
     */
    protected function theme($theme) {
        $this->view->theme($theme);
        return $this;
    }


    /**
     * 模板变量赋值
     * @param mixed $name   要显示的模板变量
     * @param mixed $value  变量的值
     * @return Action
     */
    protected function assign($name, $value = '') {
        $this->view->assign($name, $value);
        return $this;
    }

    public function __set($name, $value) {
        $this->assign($name, $value);
    }


    /**
     * 取得模板显示变量的值
     * @param string $name 模板显示变量
     * @return mixed
     */
    public function get($name = '') {
        return $this->view->get($name);      
    }


    public function __get($name) {
        return $this->get($name);
    }


    /**
     * 检测模板变量的值
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return $this->get($name);
    }


    /**
     * 魔术方法 有不存在的操作的时候执行
     * @param string  $method   方法名
     * @param array   $args     参数
     * @return mixed
     */
    public function __call($method, $args) {
        if (0 === strcasecmp($method, ACTION_NAME . C('ACTION_SUFFIX'))) {
            if (method_exists($this, '_empty')) {
                $this->_empty($method, $args);
            } elseif (is_file($this->view->parseTemplate())) {
                $this->display();
            } else {
                if (APP_DEBUG) {
                    E(L('_ERROR_ACTION_') . ':' . ACTION_NAME);
                } else {
                    header('Location: ' . __CONTROLLER__);
                    exit;
                }
            }
        } else {
            E(__CLASS__ . ':' . $method . L('_METHOD_NOT_EXIST_'));
        }
    }


    /**
     * Ajax方式返回数据到客户端
     * @param mixed   $data         要返回的数据
     * @param String  $type         AJAX返回数据格式
     * @param int     $json_option  传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data , $type = '', $json_option = 0) {
        if (empty($type)) $type = C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)) {
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $json_option));
            case 'XML':
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data, $json_option).');');  
            case 'EVAL':
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);            
            default:
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return', $data);
        }
    }


    /**
     * Action跳转(URL重定向）   支持指定模块和延时跳转
     * @param string    $url    跳转的URL表达式
     * @param array     $params 其它URL参数
     * @param integer   $delay  延时跳转的时间 单位为秒
     * @param string    $msg    跳转提示信息
     * @return void
     */
    protected function redirect($url, $params = array(), $delay = 0, $msg = '') {
        $url = U($url, $params);
        redirect($url, $delay, $msg);
    }


    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }


    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->assign('waitSecond',$ajax);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // 状态
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            $this->assign('message',$message);// 提示信息
            // 成功操作后默认停留1秒
            if(!isset($this->waitSecond))    $this->assign('waitSecond','1');
            // 默认操作成功自动返回操作前页面
            if(!isset($this->jumpUrl)) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// 提示信息
            //发生错误时候默认停留3秒
            if(!isset($this->waitSecond))    $this->assign('waitSecond','3');
            // 默认发生错误的话自动返回上页
            if(!isset($this->jumpUrl)) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // 中止执行  避免出错后继续执行
            exit ;
        }
    }


   /**
     * 析构方法
     */
    public function __destruct() {
        Hook::listen('action_end');
    }
}