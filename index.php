<!DOCTYPE HTML>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="zh-CN" />
    <title>JXL1</title>
</head>
<body>
	<?php
		require_once dirname(__FILE__)."/lib/myencode.php";//rsa2048加密+base64编码
		require_once dirname(__FILE__)."/lib/mydecode.php";//base64解码+rsa2048解密
		require_once dirname(__FILE__)."/lib/myuser.php";//数据库中查询或添加用户
		
		$appid = "ww3899a4cc8b13d23a";
		$redirect_uri = "https://daily.vicp.io/mycorp/act/oauth_1000003.php";//回调地址
		$response_type = "code";//固定项
		$scope = "snsapi_base";//可以为"snsapi_base"或者"snsapi_userinfo"或者"snsapi_privateinfo"
		$agentid = "1000003";
		$state = MD5("jxl");//回调后的传入参数，可用于传参，也可用于校检
		$header_url = "location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=".$response_type."&scope=".$scope."&agentid=".$agentid."&state=".$state."#wechat_redirect";
				
		if(isset($_COOKIE["userid"]))//检查是否存在cookie，若存在cookie
		{
			date_default_timezone_set("Asia/Shanghai");//设置默认时区
			$cookie_data = jxldecode($_COOKIE["userid"]);
			if(strlen($cookie_data)!=46)//MD5:32+date:14
			{
				header($header_url);
				exit;
			}
			$cookie_expire = 3600;//cookie有效时间(单位:s)
			$user_id_md5 = substr($cookie_data,0,32);
			$cookie_time = substr($cookie_data,32,14);
			$distance = strtotime($cookie_time)-strtotime(date("YmdHis",time()));
			if($distance>$cookie_expire||$distance<=0)//cookie过期
			{
				header($header_url);
				exit;
			}
			if(checkuser($user_id_md5)==true)//数据库中已包含此userid
			{
				$testdata="helloworld";
				print("testdata=".$testdata."</br>");
				$after_code=jxlencode($testdata);
				print("after_code=".$after_code."</br>");
				$after_decode=jxldecode($after_code);
				print("after_decode=".$after_decode."</br>");
				//TODO
				exit;
			}
			else//数据库中尚未包含此userid
			{
				header($header_url);
				exit;
			}
		}
		else//不存在cookie
		{
			header($header_url);
			exit;
		}
	?>
</body>
</html>