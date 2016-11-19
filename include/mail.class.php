<?php
/**
 * 利用phpmailer的邮件发送类
 */
class mail {

	public function send($e, $u, $t, $c) {

		// 此不适合做入进程
		// require(MYPHP_INC . "PHPMailer/class.phpmailer.php");

		// $mail = new PHPMailer();

		$mail = X('*phpmailer/phpmailer');

		$mail = X('*phpmailer/phpmailer');

		$mail->IsSMTP();                            	// 启用SMTP
		$mail->Host = "smtp.126.com";               	// SMTP服务器
		$mail->SMTPAuth = true;                     	// 开启SMTP认证
		$mail->Username = "vip17fanba@126.com";     	// SMTP用户名
		$mail->Password = "qq5752020193126";        	// SMTP密码

		$mail->From = "vip17fanba@126.com";             // 发件人地址
		$mail->FromName = "5el.me";                     // 发件人
		$mail->AddReplyTo("login@5el.me", "5el.me");   	// 回复地址
		$mail->IsHTML(true);                    		// 是否HTML格式邮件
		$mail->CharSet = "utf-8";               		// 这里指定字符集！
		$mail->Encoding = "base64";
		$mail->WordWrap = 50;                                   //设置每行字符长度


		$mail->AddAddress($e, $u); //添加收件人
		// $mail->AddAddress("811800545@qq.com");

		/** 附件设置
		$mail->AddAttachment("/var/tmp/file.tar.gz");       // 添加附件
		$mail->AddAttachment("/tmp/image.jpg", "new.jpg");  // 添加附件,并指定名称
		*/


		$mail->Subject = $t;        //邮件主题
		$mail->Body    = $c;        //邮件内容
		$mail->AltBody = "";    	//邮件正文不支持HTML的备用显示

		if(!$mail->Send())
			return false;
		else
			return true;

		// if(!$mail->Send())
		// {
		//    echo "Message could not be sent. <p>";
		//    echo "Mailer Error: " . $mail->ErrorInfo;
		//    exit;
		// }
		// echo "Message has been sent";
	}
}