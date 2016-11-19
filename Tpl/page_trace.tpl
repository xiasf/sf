<!-- sf_page_trace start -->
<style>div,ul,li {padding: 0;margin: 0;}#think_page_trace {position: fixed;bottom:0;right:0;font-size:14px;width:100%;z-index: 999999;color: #000;text-align:left;font-family:'微软雅黑';}#sf_page_trace_tab {opacity: .9;display: none;background:white;margin:0;}#sf_page_trace_tab_on {height: auto;padding: 5px;border-bottom: 1px solid #000;border-top: 1px solid red;font-size: 15px;background: linear-gradient(to bottom,#585858 0,#1D1D1D 100%);}#sf_page_trace_tab_on span {text-shadow: #000000 0 1px 0;position: relative;color:#000;padding-right:15px;height:30px;line-height: 30px;display:inline-block;margin-right:6px;cursor: pointer;font-weight:700}#sf_page_trace_tab_on span:after {content: '';width: 1px;height: 30px;background-color: #888888;position: absolute;top: 0;right: 3px;background: linear-gradient(to bottom,rgba(113, 113, 113, 0.97) 0,#989898 100%);box-shadow: rgba(0, 0, 0, 0.88) 0px 0px 2px 1px;opacity: .5;}#sf_page_trace_tab_on span:nth-child(1){margin-left: 6px;}#sf_page_trace_tab_on span:nth-last-child(1):after{display: none;}#sf_page_trace_tab_content {box-shadow: 1px 4px 5px #999 inset;overflow:auto;padding: 0; line-height: 24px}#sf_page_trace_tab_content div {display: none;}#sf_page_trace_tab_content ul li {border-bottom:1px solid #E4E4E4;font-size:13px;padding:5px 12px}#sf_page_trace_close {box-shadow: rgba(0, 0, 0, 0.88) 0px 0px 2px 1px;background-color: rgb(105, 12, 12);display: block;height: 15px;width: 15px;position: absolute;top: 13px;right: 12px;cursor: pointer;color: red;line-height: 11px;border: 1px red solid;text-align: center;}#sf_page_trace_open {z-index: 999999;height:46px;float:right;text-align: right;overflow:hidden;position:fixed;bottom:48%;right:0;color:#000;line-height:30px;cursor:default;box-shadow:rgba(0, 0, 0, 0.2) 0px 0px 2px 1px;background:hsla(0, 100%, 100%, 0.2);padding: 3px 0px 3px 3px;border-radius: 0px;}#sf_page_trace_open div {background:hsl(350, 100%, 59%);color:#FFF;font-family: '微软雅黑';padding:0 8px;float:right;line-height:46px;font-size:13px;}</style>
<div id="think_page_trace">
<div id="sf_page_trace_tab">
<div id="sf_page_trace_tab_on">
	<?php foreach($trace as $key => $value){ ?>
    <span><?php echo $key. (is_array($value) ? (count($value) ? '('.count($value).')' : '') : ''); ?></span>
    <?php } ?>
</div>
<div id="sf_page_trace_tab_content">
		<?php foreach($trace as $key => $info) { ?>
    <div>
    <ul>
	<?php
	if(is_array($info)){
		foreach ($info as $k => $val) {
			if ($key == 'Trace')
				echo '<li style="font-size:14px;">' . (is_numeric($k) ? '' : $k.' : ') . $val .'</li>';
			else
				echo '<li>' . (is_numeric($k) ? '' : $k.' : ') . htmlentities(print_r($val, true),ENT_COMPAT,'utf-8') .'</li>';
	    }
	} else
		echo '<li style="border:0;font-size:14px;padding:3px 12px">' . $info .'</li>';
    ?>
    </ul>
    </div>
    <?php } ?>
</div>
</div>
<div id="sf_page_trace_close">x</div>
</div>
<div id="sf_page_trace_open"><div><?php echo G('beginTime','viewEndTime').'s ';?></div><img width="46" title="Shen Fang I love you forever" src='data:image/png;base64,<?php echo \Core\App::logo();?>' />
</div>
<script type="text/javascript">
	(function(){
		var tab_tit  = document.getElementById('sf_page_trace_tab_on').getElementsByTagName('span');
		var tab_cont = document.getElementById('sf_page_trace_tab_content').getElementsByTagName('div');
		var open     = document.getElementById('sf_page_trace_open');
		var close    = document.getElementById('sf_page_trace_close');
		var trace    = document.getElementById('sf_page_trace_tab');
		sf_page_trace_tab_content.style.height = (document.documentElement.clientHeight * 0.48 + 10) + 'px';
		var cookie   = document.cookie.match(/sf_show_page_trace=(\d-\d+)/);
		var history  = (cookie && typeof cookie[1] != 'undefined' && cookie[1].split('-')) || [0,0];
		open.onclick = function(){
			trace.style.display = 'block';
			this.style.display = 'none';
			close.parentNode.style.display = 'block';
			history[0] = 1;
			document.cookie = 'sf_show_page_trace='+history.join('-')
		}
		close.onclick = function(){
			trace.style.display = 'none';
			this.parentNode.style.display = 'none';
			open.style.display = 'block';
			history[0] = 0;
			document.cookie = 'sf_show_page_trace='+history.join('-');
		}
		for (var i = 0; i < tab_tit.length; i++) {
			tab_tit[i].onclick = (function(i) {
				return function() {
					for (var j = 0; j < tab_cont.length; j++) {
						tab_cont[j].style.display = 'none';
						tab_tit[j].style.color = '#999';
					}
					tab_cont[i].style.display = 'block';
					tab_tit[i].style.color = '#fff';
					history[1] = i;
					document.cookie = 'sf_show_page_trace='+history.join('-');
				}
			})(i)
		}
		parseInt(history[0]) && open.click();
		(tab_tit[history[1]] || tab_tit[0]).click();
	})();
</script>
<!-- sf_page_trace end -->