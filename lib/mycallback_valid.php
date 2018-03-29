<?php
	/*
	* 为应用开启回调模式时需要调用此脚本进行服务器真实性验证
	*/
	 
	require_once dirname(__FILE__)."/msgcrypt.php";
	require_once dirname(__FILE__)."/helper.php";
		
	$AgentId = 1000003;
	//$AgentId = 1000004;
	//$AgentId = 1000005;
	//$AgentId = "txl";
    	
	//读取配置
	$appConfigs = loadConfig();
	$config = getConfigByAgentId($AgentId);

	$token  = $config->Token;    
	$corpId = $appConfigs->CorpId;
	$encodingAesKey = $config->EncodingAESKey;

	/*
	* 企业开启回调模式时，企业微信后台会向验证url发送一个get请求
	* 此逻辑需要先开通回调模式并将代码部署到服务器后进行验证
	*/

	//$sVerifyMsgSig = urldecode($_GET["msg_signature"]);
	//$sVerifyTimeStamp = urldecode($_GET["timestamp"]);
	//$sVerifyNonce = urldecode($_GET["nonce"]);
	//$sVerifyEchoStr = urldecode($_GET["echostr"]);
	//urlencode/urldecode时会对加号做转义为空格

	$sVerifyMsgSig = rawurldecode($_GET["msg_signature"]);
	$sVerifyTimeStamp = rawurldecode($_GET["timestamp"]);
	$sVerifyNonce = rawurldecode($_GET["nonce"]);
	$sVerifyEchoStr = rawurldecode($_GET["echostr"]);

	// 需要返回的明文
	$sEchoStr = "";
	$wxcpt = new MsgCrypt($token, $encodingAesKey, $corpId);
	$errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

	if($errCode == 0)
	{
		// 验证URL成功，将sEchoStr返回
		ob_clean();//清除缓存
		print($sEchoStr);
		exit;
	} 
	else
	{
		print("ERR:".$errCode."\n\n");
		exit;
	}
?>
