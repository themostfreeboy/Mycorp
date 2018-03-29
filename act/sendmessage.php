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
		require_once dirname(__FILE__)."/../lib/helper.php";	
		require_once dirname(__FILE__)."/../lib/msgcrypt.php";
		require_once dirname(__FILE__)."/../lib/app_api.php";
		require_once dirname(__FILE__)."/../lib/mydecode.php";//base64解码+rsa2048解密
		function sendRSAMessageToJXL()
		{
			$touser = "qy012673c6c909d8002ade9104ce";
			$agentid = 1000003;
			date_default_timezone_set("Asia/Shanghai");//设置默认时区
			$get_name = rawurldecode($_POST["name"]);
			$get_content = jxldecode(rawurldecode($_POST["content"]));
			if(empty($get_name) || empty($get_content) || $get_name != MD5("jxl"))
			{
				print("非法访问!</br>");
				return;
			}
			$msg = array(
				"touser"=>$touser,
				"msgtype"=>"text",
				"agentid"=>$agentid
			);
			$content_str="时间:".date("Y年m月d日H:i:s",time())."\n具体内容如下:\n".$get_content;
			$msg["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			$api_header->sendMsgToUser($msg);
		}
		sendRSAMessageToJXL();
	?>
</body>
</html>