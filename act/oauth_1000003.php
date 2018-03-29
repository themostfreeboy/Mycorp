<!DOCTYPE HTML>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="zh-CN" />
    <title>JXL2</title>
</head>
<body>
	<?php
		require_once dirname(__FILE__)."/../lib/access_token.php";//获取token
		require_once dirname(__FILE__)."/../lib/myencode.php";//rsa2048加密+base64编码
		require_once dirname(__FILE__)."/../lib/mydecode.php";//base64解码+rsa2048解密
		require_once dirname(__FILE__)."/../lib/myuser.php";//数据库中查询或添加用户
		
		$code = $_GET["code"];//用户微信授权码
		$state = $_GET["state"];//传入的参数，此处用于校检
		
		if(empty($code)||empty($state)||$state!=MD5("jxl"))
		{
			print("非法访问</br>");
			exit;
		}
		
		$agentid = 1000003;
		$hr_ins  = new AccessToken($agentid);
		$corpid = getConfigByAgentId($agentid)->corpId;
		$access_token = $hr_ins->getAccessToken();
		$get_user_info_url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$access_token."&code=".$code."&agentid=".$agentid;
		
		$user_info = json_decode(file_get_contents($get_user_info_url));
		if(isset($user_info->errcode)&&($user_info->errcode!=0))
		{
			if($user_info->errcode==40029)//用户微信授权码code被重复使用或已过期(5分钟)
			{
				print('获取用户信息失败，第二次网页跳转请选择屏幕下方的"访问原网页"，不要直接点"继续访问"，<a href="https://daily.vicp.io/mycorp/">请重试</a>。');
				exit;
			}
			print("<h1>错误码：</h1>".$user_info->errcode."</br>");
			print("<h2>错误信息：</h2>".$user_info->errmsg."</br>");
			print("获取用户信息失败</br>");
			exit;
		}
		$user_id = $user_info->UserId;//当用户为企业成员时，UserId对应于成员的UserID
		if(empty($user_id))
		{
			$user_id = $user_info->OpenId;//当用户为非企业成员授权时，返回内容不存在UserId，而是OpenId，OpenId是非企业成员的标识，对当前企业唯一。
		}
		if(empty($user_id))
		{
			print("获取用户信息失败</br>");
			exit;
		}
		//$device_id = $user_info->DeviceId;//手机设备号(由企业微信在安装时随机生成，删除重装会改变，升级不受影响)
		
		$user_id_md5 = MD5($user_id);
		
		if(checkuser($user_id_md5)==true)//数据库中已包含此userid
		{
			date_default_timezone_set("Asia/Shanghai");//设置默认时区
			$cookie_expire = 3600;//cookie有效时间(单位:s)
			$cookie_time = date("YmdHis",time()+$cookie_expire);
			$cookie_data = $user_id_md5."".$cookie_time;
			setcookie("userid",jxlencode($cookie_data),time()+$cookie_expire,"/","daily.vicp.io",true);//设置cookie，可以在cookie有效时间内避免下次访问的微信授权验证
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
			adduser($user_id_md5);
			print("数据库中尚未包含此用户，此用户无权限访问</br>");
			exit;
		}
	?>
</body>
</html>