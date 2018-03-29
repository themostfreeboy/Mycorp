<!DOCTYPE HTML>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="zh-CN" />
    <title>JXL消息处理文件</title>
</head>
<body>
	<?php
		require_once dirname(__FILE__)."/../lib/myencode.php";//rsa2048加密+base64编码
		function testSendRSAMessageToJXL($data)
		{
			$url = "https://daily.vicp.io/mycorp/act/sendmessage.php";
			$send_name = rawurlencode(MD5("jxl"));
			$send_content = rawurlencode(jxlencode($data));
			$post_data = array(
				"name" => $send_name,
				"content" => $send_content
			);
			$postdata = http_build_query($post_data);
			$options = array(
				"http" => array(
				"method" => "POST",
				"header" => "Content-type:application/x-www-form-urlencoded",
				"content" => $postdata,
				"timeout" => 15 * 60 //超时时间(单位:s)
				)
			);
			$context = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
		}
		$getdata = $_GET["data"];
		testSendRSAMessageToJXL($getdata);
	?>
</body>
</html>