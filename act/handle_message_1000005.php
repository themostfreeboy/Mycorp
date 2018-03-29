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
		//require_once dirname(__FILE__)."/../lib/mycallback_valid.php";//仅仅在微信后台配置API接收消息验证时使用，配置完成后此行代码需注释掉
		require_once dirname(__FILE__)."/../lib/mymessage.php";
		$agentid = 1000005;
		$out_array=getMsg($agentid);
		if($out_array!=NULL)
		{
			pushMsgToJXL($out_array);
			pushMsgToDCY($out_array);
		}
	?>
</body>
</html>