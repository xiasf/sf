<!DOCTYPE html">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<link rel="icon" href='/thinkphp/ThinkPHP/Tpl/errorRedDot.ico' type="image/x-icon"/>
<title>系统出错了</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
/*html{ overflow-y: scroll; }*/
body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 12px; }
img{ border: 0; }
.error{ padding: 22px; }
.face{ font-size: 30px; font-weight: normal; margin-bottom: 22px; }
.face .err_type {color: #fff;/*border: 2px #FFF solid;*/border-radius: 17px;padding: 11px;font-size: 24px;-webkit-box-shadow: 0 2px 6px -2px rgba(0, 0, 0, 1),inset 0 1px 2px 0 rgba(255,255,255,0.5);background: linear-gradient(to bottom,#FF4747 0,#961414 100%);/*text-shadow: 2px 2px 15px white;*/position: relative;top: -4px; }
.face_div { height: 20px;border-top: 1px #ECECEC solid;/*background: linear-gradient(to bottom,#F0F0F0 0,#FFFFFF 100%);*/ }
h1 { font-size: 32px; line-height: 48px; }
.error .content { padding-top: 12px; }
.error .info { margin-bottom: 12px; }
.error .info .title { /*margin-bottom: 5px;*/ padding: 8px;border-bottom: 1px #ddd solid; }
.error .info .title h3 { color: #000; font-weight: 700; font-size: 14px; }
.error .info .text { border-radius: 5px;font-weight: bold;line-height: 24px;margin: 0 0 25px 0;/*background: rgb(250, 250, 250);*/border: 1px #CECECE solid;/*padding: 8px;*/color: #000;-webkit-box-shadow: 0 2px 6px -4px rgba(0, 0, 0, 0.69),inset 0 1px 2px 0 rgba(230, 230, 230, 1);-webkit-box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2); }
.error .info .text p { padding: 8px; white-space: pre-wrap;}
.error .info .text span { color: red; }
.error .trace .text { color: rgb(121, 121, 121); font-weight: 100; }

.copyright{ padding: 12px 48px; color: #999; }
.copyright a{ color: #000; text-decoration: none; }
</style>
</head>
<body>
<div class="error">
	<p class="face" title="糟了个糕，暂时遇到了点问题，请不要担心，下面的错误信息将会协助你解决问题。">:(  系统发生 <span class="err_type"><?php echo $e['type']?></span> 错误 <span style="color:#E7E7E7">BY <?php echo $e['by']?></span></p>
	<div class="face_div"></div>
	<div class="content">
		<div class="info">
			
			<?php
				if(isset($e['buffering_clean'])) {
			?>
				<div class="text">
					<div class="title" title="缓冲内容是“SF错误显示程序极力保留下来的”的在错误显示之前所要输出的内容"><h3>缓冲内容</h3></div>
					<p style="color: rgb(121, 121, 121);font-weight: 100;"><?php echo $e['buffering_clean'];?></p>
				</div>
			<?php
				}
			?>

			<div class="text">
				<div class="title"><h3>错误信息</h3></div>
				<p><span><?php echo htmlspecialchars($e['message'], ENT_NOQUOTES, 'UTF-8');?></span></p>
			</div>

			<div class="text">
				<div class="title"><h3>错误类型</h3></div>
				<p>type: <span><?php echo $e['type'] ;?></span> &#12288;code: <span><?php echo $e['code'];?></span></p>
			</div>

			<div class="text">
				<div class="title"><h3>错误位置</h3></div>
				<p>FILE: <span><?php echo $e['file'] ;?></span> &#12288;LINE: <span><?php echo $e['line'];?></span></p>
			</div>

		</div>

		<div class="info trace">
			<div class="text">
				<div class="title"><h3>页面跟踪</h3></div>
				<p><?php echo htmlspecialchars($e['trace'], ENT_NOQUOTES, 'UTF-8');?></p>
			</div>
		</div>

		<div class="info trace">
			<div class="text">
				<div class="title"><h3>嘻嘻嘻嘻</h3></div>
				<p>无论是什么时候都得正面面对不是吗，我们已经记录好了这份错误报告，感谢您的反馈，祝您生活愉快！&#12288;<?php echo date('Y-m-d H:i:s');?></p>
			</div>
		</div>
	</div>
	<div class="face_div"></div>
	<div>
		<p>X-Powered By：SF PHP Framework by xiak&#12288;Shen Fang I love you forever :)&#12288;芳芳：改变也是件美好的事&#12288;baby 我永远都乖着呢</p>
	</div>
</div>
</body>
</html>