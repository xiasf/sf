<?php
/**
 * myphp  系统公共函数库（非必须）
 * fen    系统提供的分页函数
 * fen    为“空间名”，与当前函数库文件名相同，F()就是根据它来引入函数库文件的
 * F()函数都是根据“空间名”来引入函数库文件的，一般空间名函数为第一个函数，为了能让其被F()函数找到，那就必须有这个空间名函数，即使这个空间名函数没有任何操作。
 * @category myphp
 * @package  fen
 * @author   xiak <811800545@qq.com>
 * Update time：2014-8-24 12:11:12
 */


/**
 * 分页条链接部分，供显示分页条方法调用，返回分页条<a>部分。
 * @param int  $pagecount       总页数
 * @param int  $nowpage         当前页次
 * @param int  $pagesize        每页最大显示条数
 * @param bloo $m_              头是否显示第一页
 * @param bloo $m               尾是否显示最后一页
 * @param int  $k               分页条最大宽度
 * @return string               链接串
 *┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *┃     @说明1：目前分页条最大宽度定位10，即只要总页数满足$k的话，那么每次都会循环10次来生成l<a> ,至于两
 *┃------------------------------------------------------------------------------------------------------------
 *┃边有可能出现的附加，也就是说全部<a>的数量为：($k||10||11|12),还有一个重要的问题是附加不能出现逻辑性的
 *┃------------------------------------------------------------------------------------------------------------
 *┃错误，为了方便区分，我们将所有附加的<a>定义特别样式，"1····"用"id=h"，"····$pagecount"用"id=w"。
 *┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *┃    “档”的概念就是前进或后退，也就是点击<a>会改变分页条的形态，比如1<=$nowpage<=5和$pagecount-4<=$nowpage<
 *┃------------------------------------------------------------------------------------------------------------
 *┃=$pagecount时就不会有任何形态的改变，当6<=$nowpage<$pagecount-4时我们总将$nowpage
 *┃------------------------------------------------------------------------------------------------------------
 *┃（当前点击的）定在第6次循环，这才是”两党合一“的精髓。
 *┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */
function fen($pagecount, $nowpage, $pagesize, $m_, $m, $k = 10) {
    $str = '';      //初始化
    //生成上一页，如果存在的话
    if ($nowpage > 1)
        $shang = '<a href='.fen_url(URL(array('page' => ($nowpage-1), 'size' => $pagesize))).' id="shang">上一页</a>&nbsp;';
    if ($nowpage < $pagecount)
        $xia = '<a href='.fen_url(URL(array('page' => ($nowpage+1), 'size' => $pagesize))).' id="xia">下一页</a>&nbsp;';
    $str .= $shang;         //拼接上一页

    /**
     * 当总页数少于或等于分页条最大宽度时【第1种情况】
     */
    if ($pagecount <= $k) {
        for ($i = 0; $i < $pagecount; $i++){
            if($nowpage == $i+1)
                $str .= '<a href='.fen_url(URL(array('page' => ($i+1), 'size' => $pagesize))).' id="now">'.($i+1).'</a>&nbsp;';
            else
            $str .= '<a href='.fen_url(URL(array('page' => ($i+1), 'size' => $pagesize))).'>'.($i+1).'</a>&nbsp;';
        }
        return $str.$xia;       //返回完整拼接
    }

    /**
     * 当前页次在[1~5]之间时才会出现[····$pagecount]
     *【第2种情况】【一个····最大附加】
     */
    if (1 <= $nowpage && $nowpage <= 5) {
        for ($i = 0; $i < 10; $i++) {        //循环10次不会改变
            if($nowpage == $i+1)
                $str .= '<a href='.fen_url(URL(array('page' => ($i+1), 'size' => $pagesize))).' id="now">'.($i+1).'</a>&nbsp;';
            else
            $str .= '<a href='.fen_url(URL(array('page' => ($i+1), 'size' => $pagesize))).'>'.($i+1).'</a>&nbsp;';
        }
        $w_ = ($m || (($i+5) > $pagecount)) ? $pagecount : ($i+5);  //确定尾部，支持是否显示最大页数
        $str .= '<a href='.fen_url(URL(array('page' => $w_, 'size' => $pagesize))).$pagesize.' id=w>'.'····'.$w_.'</a>&nbsp;';
        return $str.$xia;       //返回完整拼接
    }

    /**
     * 当前页次在[(pagecount-4) ~ $pagecount]之间时才会出现[1····]
     *【第3种情况】【一个最小1···附加】
     */
    if (($pagecount - 4) <= $nowpage && $nowpage <= $pagecount) {
        $start = $pagecount - 9;        //循环生成<a>的起点

            $h_ = $m_ ? 1 : ($start - 4);  //确定头，支持是否显示的是第一页
            $str .= '<a href='.fen_url(URL(array('page' => $h_, 'size' => $pagesize))).' id=h>'.$h_.'····</a>&nbsp;';

        for ($i = 0; $i <10; $i++) {         //循环10次不会改变
            if($nowpage == $start+$i)
                $str .= '<a href='.fen_url(URL(array('page' => ($start+$i), 'size' => $pagesize))).' id="now">'.($start+$i).'</a>&nbsp;';
            else
            $str .= '<a href='.fen_url(URL(array('page' => ($start+$i), 'size' => $pagesize))).'>'.($start+$i).'</a>&nbsp;';
        }
     
        return $str.$xia;       //返回完整拼接
    }

    /**
     * 当前页次在[6 ~ ($pagecount-4)]之间时才会出现[1····]和[····$pagecount](6,7例外，没有····不然会有逻辑错误)
     *【第4种情况】【两个附加(1···)和(····$pagecount)】
     * 例外说明：此时也可能附加1，而不给····这是为了防止出现逻辑性的错误
     */
    if (6 <= $nowpage && $nowpage <= ($pagecount-4)) {
        $start = $nowpage - 5;          //循环生成<a>的起点
        if ($nowpage == 7)              //循环起点为2
            $str .= '<a href='.fen_url(URL(array('page' => 1, 'size' => $pagesize))).' id="h">1</a>&nbsp;';
        elseif ($nowpage == 6) {
            $str .= '<a href='.fen_url(URL(array('page' => 1, 'size' => $pagesize))).' id="h">1</a>&nbsp;';
            $start = 2;     //让它从2开始，防止出现逻辑性的错误
        }
        else {
            $h_ = $m_ ? 1 : (($start > 4) ? ($start - 4) : 1);  //确定头，支持是否显示的是第一页
            $str .= '<a href='.fen_url(URL(array('page' => $h_, 'size' => $pagesize))).' id=h>'.$h_.'····</a>&nbsp;';
        }
        for ($i = 0; $i <10; $i++) {         //循环10次不会改变
            if($nowpage == $start+$i)
                $str .= '<a href='.fen_url(URL(array('page' => ($start+$i), 'size' => $pagesize))).' id="now">'.($start+$i).'</a>&nbsp;';
            else
            $str .= '<a href='.fen_url(URL(array('page' => ($start+$i), 'size' => $pagesize))).'>'.($start+$i).'</a>&nbsp;';
        }
        if ($nowpage == ($pagecount - 5))
        $str .= '<a href='.fen_url(URL(array('page' => $pagecount, 'size' => $pagesize))).' id=w>'.$pagecount.'</a>&nbsp;';
        else {
            $w_ = ($m || (($start+$i+4) > $pagecount)) ? $pagecount : ($start+$i+4);  //确定尾部，支持是否显示最大页数
            $str .= '<a href='.fen_url(URL(array('page' => $w_, 'size' => $pagesize))).' id=w>'.'····'.$w_.'</a>&nbsp;';
        }
        return $str.$xia;       //返回完整拼接
    }
}


/**
 * 分页条2，同分页条1一样的功能，只不过主题皮肤，形式不一样而已，类似的还可以有fen……n，当然也可以写应用自定义的，但不鼓励这样的，要多多为系统做作贡献嘛，但如果在这一个文件中写太多的fen就会违背我们的按需加载的高性能追求，所以那时可以创建多个分页文件fen1.php，fen2.php……（不论何时，安全和性能都是myphp要优先考虑的）
 * @param int $pagecount   总页数
 * @param int $nowpage     当前页数
 * @param int $pagesize    每页显示最大数
 */
function fen2($pagecount, $nowpage, $pagesize) {
    $str = '';
	if (($nowpage < $pagecount) && ($nowpage > 1)) { 
		$str .= "<a href=".fen_url(URL(array('page' => 1, 'size' => $pagesize)))." id=h>首页</a>";
		$str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		$str .= "<a href=".fen_url(URL(array('page' => ($nowpage-1), 'size' => $pagesize)))." id=shang>上一页</a>";
		$str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		$str .= "<a href=".fen_url(URL(array('page' => ($nowpage+1), 'size' => $pagesize)))." id=xia>下一页</a>";
		$str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		$str .= "<a href=".fen_url(URL(array('page' => $pagecount, 'size' => $pagesize)))." id=w>尾页</a>";
		$str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
	} elseif($pagecount == 1) {
			$str = '';
		} elseif ($nowpage == $pagecount) {
    		$str .= "<a href=".fen_url(URL(array('page' => 1, 'size' => $pagesize)))." id=h>首页</a>";
    		$str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
    		$str .= "<a href=".fen_url(URL(array('page' => ($nowpage-1), 'size' => $pagesize)))." id=shang>上一页</a>";
    		$str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		} elseif ($nowpage == 1) {
                $str .= "<a href=".fen_url(URL(array('page' => ($nowpage+1), 'size' => $pagesize)))." id=xia>下一页</a>";
                $str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $str .= "<a href=".fen_url(URL(array('page' => $pagecount, 'size' => $pagesize)))." id=w>尾页</a>";
                $str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        }
	return $str;
}


/**
 * 之前的问题：目前分页存在严重问题，无论哪种情况，分页连接都不支持带参数进来，这可能要我们想一个办法来解决这个问题
 * 已经想办法解决，搜索类的分页（带url参数类的分页）都要用get方式就好了，为了解决get提交时的问题就需要使用隐藏域来解决路由了，因为get提交方式会直接忽略路由的
 *
 * ----------------------------------------
 *
 * 为分页工作做最后一步，为其加上原本的url参数（如，一些特殊类型的参数type等，还有get搜索的参数）不然只有分页参数部分的url没有任何意义
 * 带参数的分页只能这样get方式，路由和其它被get忽略的参数请写在 hidden 隐藏域中
 * @param  str  $href    不带参数的分页绝对url
 * @return str           最终的带参数的分页绝对url
 */
function fen_url($href) {
    // 最后为了帮忙解决分页问题，看有无$_SERVER['QUERY_STRING']，请注意带参数的分页必须使用get方式，详情见分页函数说明;（这部分还是不适合在URL()函数中，所以现在移到这里来了）
    if ($_SERVER['QUERY_STRING']) {
        $list = array();

        $a = explode('&', $_SERVER['QUERY_STRING']);    // 先把每一对参数分开
        foreach ($a as $value) {
            list($k, $v) = explode('=', $value);        // 先把每一对参数分开
            $list[$k] = $v;                             // 这样就得到了每一对参数值对
        }

        if (array_key_exists('m', $list)) unset($list['m']);
        if (array_key_exists('c', $list)) unset($list['c']);
        if (array_key_exists('a', $list)) unset($list['a']);

        if (array_key_exists('page', $list)) unset($list['page']);
        if (array_key_exists('size', $list)) unset($list['size']);

        if ($list)
        return $href .= '&' . urldecode(http_build_query($list));  // 得到最终的绝对url（这里url解码了一次，是因为get或者浏览器本省就会url编码一次的，http_build_query也会进行url编码，所以这里是为了防止出现重复编码）
    }
    return $href;
}