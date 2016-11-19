<?php
/**
 * myphp  系统公共函数库（非必须）
 * check  系统验证核心函数
 * check  为“空间名”，与当前函数库文件名相同，F()就是根据它来引入函数库文件的
 * F()函数都是根据“空间名”来引入函数库文件的，一般空间名函数为第一个函数，为了能让其被F()函数找到，那就必须有这个空间名函数，即使这个空间名函数没有任何操作。
 * @category myphp
 * @package  check
 * @author   xiak <811800545@qq.com>
 * Update time：2014-7-20 17:27:45
 */


/**
 * @ 当前命名空间函数，也是验证函数 如果sql安全出问题了，就找这儿了
 * @ 太强大了，自动验证，自动过滤，自动填充轻松搞定，但如果一个很容易的数据要验证过滤就可以不必加载此函数了，手动写就好了，当然这只是出于按需加载的考虑，一般地要验证过滤三个以上的数据时加载才最有可能加载此文件，当然这只是建议而已，为了苛刻的按需加载而已。
 * @ 安全验证及过滤，复杂的直接写函数，分为：函数验证，正则验证，直接强制类型转换三中验证方式。
 * @ 其实大多规则仅一个正则就能搞定，但还是分类多种情况，因为要尽量不启动正则验证，当然这只是出于性能的考虑
 * @ mixed $v[2]是否强制验证，默认是（false：验证假则直接退出函数，后面的也没必要验证了，否则验证假时将这个缺省值给它用）
 * @param   array  $data     		$data[0]:要验证的数据，$data[1]:错误列表
 * @return 	array  $_data/false 	验证成功后是返回的数据，或验证失败返回布尔值假
 */
function check($data) {
	$_data = array();							// 初始化

	/**
	 * 原型：
	 * v[0] 我们传进来的原值
	 * v[1] 我们的验证规则如，函数验证，正则验证等（/^\W+$/：自定义的正则，qq/：使用系统的正则；int:qq/这是过滤级别）
	 * v[2] 是否强制验证，如果有值，那么如果验证不通过时可以用这个（缺省）值代替，默认强制验证
	 * 对于我们返回的数组内容，我们没有为它们安排“键值”，没这个必要，直接用索引数组是最有效率的
	 * 如要进行多项验证就写多个规则就可以了的（暂时只能能这样了，多个验证规则分割法是先较复杂，以后再想好的办法）
	 */

	$value = $data[0];							// 待验证值列表
	$error = $data[1];							// 验证失败，“唯一返回时”的错误提示列表，如果没有设置错误提示，那么将返回布尔值假
	$i = 0;
	foreach ($value as $v) {

		$v[1] = trim($v[1]);	// 防止我们写规则时而疏忽造成错误

		// if (stripos($v[1], '<>') !== false) {
		// 	$vv = explode('<>', $v[1]);		// 现在它包含多个规则了
		// } 2014-7-24 22:38:21


		if (stripos($v[1], ':') === 1) {
			list($k, $v[1]) = explode(':', $v[1]);
		} else {
			$k = 1;				// 默认只进行',",\转义过滤
		}

		if ($v[1]) {			// 当有验证规则时（注意验证并不会对原值造成任何影响，每次只能验证一个规则）

			$e = trim(current($error));
			if (empty($e))
				$error[$i] = ' error未通过验证：“未设置出错信息”';		// 空串和空格和0都算未设置
			unset($e);

			$m = $v[2];			// 得到是否强制，是否给你机会
			unset($v[2]);

			if (!isset($m))
				$m = false;		// 默认强制验证，不会给你机会

			/*----------验证初始化完毕----------*/


			/*------------验证规则------------*/

			if (strpos($v[1], '(') === 0) {									// 确定是函数
				$f = substr($v[1], 1, -1);
				function_exists($f) or E('<b>no fun from check:</b> ('.$f . ') line:'.__LINE__);

				if (!call_user_func($f, $v[0]))			// 函数直接验证，不合法直接返回假
				if ($m === false) return '#'.$v[0].'# '.current($error);	// 返回错误提示，如果没有设置错误提示，那么将返回布尔值假（返回布尔假没有用的，此处被隐式的转换为空串了，没有意义的，所以现在对于未设置的出错信息我们会有提示的。）
				else $v[0] = $m;
				unset($f);	// 用完立即释放变量
			}

			elseif (strpos($v[1], '/') !== false) {						// 正则验证
				$reg = (strpos($v[1], '/') === 0) ? $v[1] : REG(substr($v[1], 0, -1));			// 取得正则模式表达式
				if (!preg_match($reg, $v[0]))							// 验证失败
				if ($m === false) return '#'.$v[0].'# '.current($error);
				else $v[0] = $m;										// 验证失败的一次机会
				unset($reg);											// 及时释放资源
			}

			elseif (strpos($v[1], '{') === 0) {							// 字符长度验证
				$s = substr($v[1], 1, -1);
		        $c = mb_strlen($v[0],'utf-8');

		        list($l, $r) = explode(',', $s);

				if ($r) {
					 if ($l > $c || $c > $r) {
	 					 	if ($m === false) return '#'.$v[0].'# '.current($error);
							else $v[0] = $m;
					 }
				} else{
					if ($c != $l)
						if ($m === false) return '#'.$v[0].'# '.current($error);
						else $v[0] = $m;
				}
			}

			elseif (strpos($v[1], '|') === 0) {							// 可能值验证
				if (!in_array($v[0], explode('|', trim($v[1], '|'))))	// 验证失败
				if ($m === false) return '#'.$v[0].'# '.current($error);
				else $v[0] = $m;										// 验证失败的一次机会
			}

			elseif (strpos($v[1], '!') === 0) {							// 排斥值验证

				$cv = explode('!', trim($v[1], '!'));
				if (is_array($cv))
				foreach ($cv as $value) {
					if (stripos($v[0], $value) !== false)
						if ($m === false) return '#'.$v[0].'# (不能通过排斥验证) ' .current($error);
						else $v[0] = $m;										// 验证失败的一次机会
				}
			}

			else {								// 直接强制转换
				switch ($v[1]) {
					case 'int':
						$v[0] = (int) $v[0];	// 转换为整形
						break;
					case 'float':
						$v[0] = (float) $v[0];	// 转换为浮点型
						break;
					case 'string':
						$v[0] = (string) $v[0];	// 转换为字符串
						break;
					case 'bool':
						$v[0] = (bool) $v[0];	// 转换为布尔类型
						break;
					default:
						$v[0] = (int) $v[0];	// 保险起见，对于这种强制转换为整形比较安全
						break;
				}
			}

		}	// 如果没有$v[1]（验证规则），那么说明：我只是来过滤一下而已哈


		$se = $v[0];							// 得到信息

		if ($k == 0) {							// 不过滤
			$_data[$i++] = $se;					// 取得信息
			unset($value, $m, $se, $v);			// 及时释放资源
			array_shift($error);				// 这一组提示出栈
			continue;							// 到此为止，进入下次循环
		}


		// 转义过滤在最后一步，在格式验证的后面，这很重要，确保转义不会影响格式验证

		// 过滤等级为1啊（验证函数一般用于与有sql使用的时，所以这个基本的过滤是必须的）
		$se = addslashes($se);	// 这一步是必须的转义',",\,请确保post/get/cookie过来的是原始纯值，不然重复过滤没有意义
		
		// $se = addcslashes($se, '/,-,*,;,`,^');	// mysql所有特殊字符的过滤（只要搞定单引号的问题，这个应该就没问题了）
		// 注意%和_确实算mysql的特殊字符，但这仅在模糊检索时有效，如不想要这样的效果，那就在做搜索功能时手动转义控制吧，不然名称中带有%，_的是不能被检索到的。

		if ($k == 2)
			$se = htmlspecialchars($se, ENT_NOQUOTES, 'UTF-8');	// 过滤等级为2（单，双引号上面已经编码了，这里就不出处理了）

		if ($k == 3) {	// 过滤等级为3
			$se = strip_tags($se);
			$se = htmlspecialchars($se, ENT_NOQUOTES, 'UTF-8');
		}

		$_data[$i++] = $se;					// 得到信息

		// 遍历是将原数组复制一份了的哈，所以现在直接干掉原数组
		unset($value, $m, $se, $v);			// 及时释放资源
		array_shift($error);				// 这一组提示出栈（指针向前移一位）
	}

	return $_data;			// 最终返回
}


/**
 * 系统提供的常用正则函数，如果有自定义的那就请自行定义并且自行先加载好，注意不要与系统的函数重名
 * 如果自定义函数不是什么非常特殊的那就建议移步这里做系统的吧，多为系统做贡献嘛
 */
function REG($name) {
	$list = array(
		    'require'=> '/.+/',
            'email' => '/^\w+([-+.] \w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'qq' => '/^[1-9][0-9]{4,}$/',
            'shouji' => '/^1[3|4|5|8][0-9]\d{8}$/',
            'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/^\d+$/',
            'zip' => '/^[1-9]\d{5}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
            'z' => "/^[\x{4e00}-\x{9fa5}]+$/u",					// 完全中文
            'z2' => "/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u",		// 中文数字和字母和下划线
            'z3' => "/^[\x{4e00}-\x{9fa5}A-Za-z0-9_ ]+$/u",		// 中文数字和字母和下划线允许空格
		 );
	if (isset($list[$name]))
		 return $list[$name];	// 当存在则返回
	else
		E('<b>no REG check:</b> '.$name . ' line:'.__LINE__);	// 防止系统正则错误（此为开发人员错误）
}


/**
 * 系统提供的常用验证函数，如果有自定义的那就请自行定义并且自行先加载好，注意不要与系统的函数重名
 * 如果自定义函数不是什么非常特殊的那就建议移步这里做系统的吧，多多为系统做贡献嘛
 */
function user______($data) {
	if ($data == 'xiak') return true;
	else return false;
}

function z______($data) {
	if ($data == '夏凯') return true;
	else return false;
}