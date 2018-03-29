<?php
	
	require_once dirname(__FILE__)."/helper.php";	
	require_once dirname(__FILE__)."/msgcrypt.php";
	require_once dirname(__FILE__)."/app_api.php";
	require_once dirname(__FILE__)."/myxmltoarray.php";
	require_once dirname(__FILE__)."/mystate.php";
	
	function getMsg($in_agentid)
	{
		//读取config文件里面的配置
		$appConfigs = loadConfig();
		$config = getConfigByAgentId($in_agentid);

		$token = $config->Token;
		$encodingAesKey = $config->EncodingAESKey;	
		$corpId = $appConfigs->CorpId;
		
		$sReqMsgSig = $_GET["msg_signature"];	
		$sReqTimeStamp = $_GET["timestamp"];	
		$sReqNonce = $_GET["nonce"];	
		$sReqData = file_get_contents("php://input");		

		$sMsg = "";//解析之后的明文
		$wxcpt = new MsgCrypt($token,$encodingAesKey,$corpId);
		$errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);

		if ($errCode == 0)
		{
			//解密成功，sMsg即为xml格式的明文
			 			
			$xml = new DOMDocument();
			$xml->loadXML($sMsg);
			$out_array=array();
			
			$ToUserName = $xml->getElementsByTagName("ToUserName")->item(0)->nodeValue;//企业微信CorpID
			$FromUserName = $xml->getElementsByTagName("FromUserName")->item(0)->nodeValue;//成员UserID
			$CreateTime = $xml->getElementsByTagName("CreateTime")->item(0)->nodeValue;//成息创建时间（整型）
			$MsgType = $xml->getElementsByTagName("MsgType")->item(0)->nodeValue;//消息类型
			$MsgId = $xml->getElementsByTagName("MsgId")->item(0)->nodeValue;//消息id，64位整型
			$AgentID = $xml->getElementsByTagName("AgentID")->item(0)->nodeValue;//企业应用的id，整型。可在应用的设置页面查看		
						
			if($MsgType=="text")//文本消息
			{
				$out_array["ToUserName"]=$ToUserName;
				$out_array["FromUserName"]=$FromUserName;
				$out_array["CreateTime"]=$CreateTime;
				$out_array["MsgType"]=$MsgType;
				$out_array["MsgId"]=$MsgId;
				$out_array["AgentID"]=$AgentID;
				$Content = $xml->getElementsByTagName("Content")->item(0)->nodeValue;//文本消息内容
				$out_array["Content"]=$Content;
			}
			else if($MsgType=="image")//图片消息
			{
				$out_array["ToUserName"]=$ToUserName;
				$out_array["FromUserName"]=$FromUserName;
				$out_array["CreateTime"]=$CreateTime;
				$out_array["MsgType"]=$MsgType;
				$out_array["MsgId"]=$MsgId;
				$out_array["AgentID"]=$AgentID;
				$PicUrl = $xml->getElementsByTagName("PicUrl")->item(0)->nodeValue;//图片链接
				$MediaId = $xml->getElementsByTagName("MediaId")->item(0)->nodeValue;//图片媒体文件id，可以调用获取媒体文件接口拉取
				$out_array["PicUrl"]=$PicUrl;
				$out_array["MediaId"]=$MediaId;
			}
			else if($MsgType=="voice")//语音消息
			{
				$out_array["ToUserName"]=$ToUserName;
				$out_array["FromUserName"]=$FromUserName;
				$out_array["CreateTime"]=$CreateTime;
				$out_array["MsgType"]=$MsgType;
				$out_array["MsgId"]=$MsgId;
				$out_array["AgentID"]=$AgentID;
				$MediaId = $xml->getElementsByTagName("MediaId")->item(0)->nodeValue;//语音媒体文件id，可以调用获取媒体文件接口拉取数据
				$Format = $xml->getElementsByTagName("Format")->item(0)->nodeValue;//语音格式，如amr，speex等
				$out_array["MediaId"]=$MediaId;
				$out_array["Format"]=$Format;
			}
			else if($MsgType=="video")//视频消息
			{
				$out_array["ToUserName"]=$ToUserName;
				$out_array["FromUserName"]=$FromUserName;
				$out_array["CreateTime"]=$CreateTime;
				$out_array["MsgType"]=$MsgType;
				$out_array["MsgId"]=$MsgId;
				$out_array["AgentID"]=$AgentID;
				$MediaId = $xml->getElementsByTagName("MediaId")->item(0)->nodeValue;//视频媒体文件id，可以调用获取媒体文件接口拉取数据
				$ThumbMediaId = $xml->getElementsByTagName("ThumbMediaId")->item(0)->nodeValue;//视频消息缩略图的媒体id，可以调用获取媒体文件接口拉取数据
				$out_array["MediaId"]=$MediaId;
				$out_array["ThumbMediaId"]=$ThumbMediaId;
			}
			else if($MsgType=="location")//位置消息
			{
				$out_array["ToUserName"]=$ToUserName;
				$out_array["FromUserName"]=$FromUserName;
				$out_array["CreateTime"]=$CreateTime;
				$out_array["MsgType"]=$MsgType;
				$out_array["MsgId"]=$MsgId;
				$out_array["AgentID"]=$AgentID;
				$Location_X = $xml->getElementsByTagName("Location_X")->item(0)->nodeValue;//地理位置纬度
				$Location_Y = $xml->getElementsByTagName("Location_Y")->item(0)->nodeValue;//地理位置经度
				$Scale = $xml->getElementsByTagName("Scale")->item(0)->nodeValue;//地图缩放大小
				$Label = $xml->getElementsByTagName("Label")->item(0)->nodeValue;//地理位置信息
				$out_array["Location_X"]=$Location_X;
				$out_array["Location_Y"]=$Location_Y;
				$out_array["Scale"]=$Scale;
				$out_array["Label"]=$Label;
			}
			else if($MsgType=="link")//链接消息
			{
				$out_array["ToUserName"]=$ToUserName;
				$out_array["FromUserName"]=$FromUserName;
				$out_array["CreateTime"]=$CreateTime;
				$out_array["MsgType"]=$MsgType;
				$out_array["MsgId"]=$MsgId;
				$out_array["AgentID"]=$AgentID;
				$Title = $xml->getElementsByTagName("Title")->item(0)->nodeValue;//标题
				$Description = $xml->getElementsByTagName("Description")->item(0)->nodeValue;//描述
				$PicUrl = $xml->getElementsByTagName("PicUrl")->item(0)->nodeValue;//封面缩略图的url
				$out_array["Title"]=$Title;
				$out_array["Description"]=$Description;
				$out_array["PicUrl"]=$PicUrl;
			}
			else if($MsgType=="event")//事件消息
			{
				$xml_array=xmlToArray($xml);
				$out_array=$xml_array["xml"];
			}
			else//其他
			{
				exit;//暂不处理
			}
			return $out_array;
		} 
		else
		{
			print("ERR:".$errCode."\n\n");	
			return NULL;			
		}
	}
	
	function pushMsgToJXL($in_array)
	{
		$touser = "qy012673c6c909d8002ade9104ce";//成员ID列表（消息接收者，多个接收者用‘|’分隔，最多支持1000个）。特殊情况：指定为@all，则向该企业应用的全部成员发送(非必需)
		//$toparty = "";//部门ID列表，多个接收者用‘|’分隔，最多支持100个。当touser为@all时忽略本参数(非必需)
		//$totag = "";//标签ID列表，多个接收者用‘|’分隔，最多支持100个。当touser为@all时忽略本参数(非必需)
		$agentid = 1000003;
		$FromUserName=$in_array["FromUserName"];
		$FromAgentID=$in_array["AgentID"];
		$MsgType=$in_array["MsgType"];
		$MsgType_str=$MsgType;
		if($FromUserName=="qy012673c6c909d8002ade9104ce")
		{
			$FromUserName="jxl";
		}
		else if($FromUserName=="dcy")
		{
			$FromUserName="dcy";
		}
		if($FromAgentID==1000003)
		{
			$FromAgentID="JXL应用";
		}
		else if($FromAgentID==1000004)
		{
			$FromAgentID="瑜宝宝应用";
		}
		else if($FromAgentID==1000005)
		{
			$FromAgentID="对话瑜宝宝";
		}
		if($MsgType=="text")
		{
			$MsgType_str="文本消息";
		}
		else if($MsgType=="image")
		{
			$MsgType_str="图片消息";
		}
		else if($MsgType=="voice")
		{
			$MsgType_str="语音消息";
		}
		else if($MsgType=="video")
		{
			$MsgType_str="视频消息";
		}
		else if($MsgType=="location")
		{
			$MsgType_str="位置消息";
		}
		else if($MsgType=="link")
		{
			$MsgType_str="链接消息";
		}
		else if($MsgType=="event")
		{
			$MsgType_str="事件消息";
		}
		date_default_timezone_set("Asia/Shanghai");//设置默认时区
		
		if($MsgType=="text")//文本消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string."\n具体消息内容如下:\n".$in_array["Content"];
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
		}
		else if($MsgType=="image")//图片消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid,//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string."\n具体消息内容如下:";
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"image",
 			"agentid"=>$agentid,
			"image"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="voice")//语音消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string."\n具体消息内容如下:";
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"voice",
 			"agentid"=>$agentid,
			"voice"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="video")//视频消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string."\n具体消息内容如下:";
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"video",
 			"agentid"=>$agentid,
			"video"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="location")//位置消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string."\n具体消息内容如下:\n地理位置纬度:".$in_array["Location_X"]."\n地理位置经度:".$in_array["Location_Y"]."\n地图缩放大小:".$in_array["Scale"]."\n地理位置信息:".$in_array["Label"];
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
		}
		else if($MsgType=="link")//链接消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string."\n具体消息内容如下:\n标题:".$in_array["Title"]."\n描述:".$in_array["Description"]."\n封面缩略图的url:".$in_array["PicUrl"];
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
		}
		else if($MsgType=="event")//事件消息
		{
			$msg_header = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid//企业应用的id，整型。可在应用的设置页面查看
 			);
			$arrayheader_string=print_r($in_array,true);
			$content_str="收到后台消息:\n消息来自于用户:".$FromUserName."\n消息来自于应用:".$FromAgentID."\n消息的创建时间为:".date("Y年m月d日H:i:s",strtotime(in_array["CreateTime"])+3600)."\n消息类型为:".$MsgType_str."\n消息头部信息如下:\n".$arrayheader_string;
			$Event=$in_array["Event"];//事件类型
			if($Event=="enter_agent")//进入应用事件
			{
				$content_str=$content_str."\n具体事件类型:进入应用事件";
			}
			else if($Event=="LOCATION")//上报地理位置事件
			{
				$content_str=$content_str."\n具体事件类型:上报地理位置事件\n地理位置纬度:".$in_array["Latitude"]."\n地理位置经度:".$in_array["Longitude"]."\n地理位置精度:".$in_array["Precision"];
			}
			else if($Event=="batch_job_result")//异步任务完成事件
			{
				$content_str=$content_str."\n具体事件类型:异步任务完成事件";
				$JobType=$in_array["JobType"];//操作类型
				if($JobType=="sync_user")//增量更新成员
				{
					$content_str=$content_str."\n具体操作类型:增量更新成员";
				}
				else if($JobType=="replace_user")//全量覆盖成员
				{
					$content_str=$content_str."\n具体操作类型:全量覆盖成员";
				}
				else if($JobType=="invite_user")//邀请成员关注
				{
					$content_str=$content_str."\n具体操作类型:邀请成员关注";
				}
				else if($JobType=="replace_party")//全量覆盖部门
				{
					$content_str=$content_str."\n具体操作类型:全量覆盖部门";
				}
				$content_str=$content_str."\n异步任务id:".$in_array["BatchJob"]["JobId"]."\n返回码:".$in_array["BatchJob"]["ErrCode"]."\n对返回码的文本描述内容:".$in_array["BatchJob"]["ErrMsg"];
			}
			else if($Event=="change_contact")//通讯录变更事件
			{
				$content_str=$content_str."\n具体事件类型:通讯录变更事件";
				$ChangeType=$in_array["ChangeType"];//通讯录变更子事件类型
				if($ChangeType=="create_user")//新增成员事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:新增成员事件";
					$content_str=$content_str."\n具体新增成员内容为:\n成员UserID:".$in_array["UserID"]."\n成员名称:".$in_array["Name"]."\n成员部门列表:".$in_array["Department"]."\n手机号码:".$in_array["Mobile"]."\n职位信息:".$in_array["Position"];
					if($in_array["Gender"]==1)
					{
						$content_str=$content_str."\n性别:男";
					}
					else if($in_array["Gender"]==2)
					{
						$content_str=$content_str."\n性别:女";
					}
					$content_str=$content_str."\n邮箱:".$in_array["Email"];
					if($in_array["Status"]==1)
					{
						$content_str=$content_str."\n邮箱激活状态:已激活";
					}
					else if($in_array["Status"]==2)
					{
						$content_str=$content_str."\n邮箱激活状态:已禁用";
					}
					$content_str=$content_str."\n头像url:".$in_array["Avatar"]."\n英文名:".$in_array["EnglishName"];
					if($in_array["IsLeader"]==0)
					{
						$content_str=$content_str."\n是否为上级:普通成员";
					}
					else if($in_array["IsLeader"]==1)
					{
						$content_str=$content_str."\n是否为上级:上级";
					}
					$content_str=$content_str."\n座机:".$in_array["Telephone"];
					if(isset($in_array["ExtAttr"]))
					{
						$content_str=$content_str."\n扩展属性:";
						for($index=0;$index<count($in_array["ExtAttr"]["Item"]);$index++)
						{
							$content_str=$content_str."\n第".($index+1)."项:\n扩展属性名称:".$in_array["ExtAttr"]["Item"][$index]["Name"]."\n扩展属性值:".$in_array["ExtAttr"]["Item"][$index]["Value"];
						}
					}
				}
				else if($ChangeType=="update_user")//更新成员事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:更新成员事件";
					$content_str=$content_str."\n具体更新成员内容为:\n变更信息的成员UserID:".$in_array["UserID"]."\n新的UserID:".$in_array["NewUserID"]."\n成员名称:".$in_array["Name"]."\n成员部门列表:".$in_array["Department"]."\n手机号码:".$in_array["Mobile"]."\n职位信息:".$in_array["Position"];
					if($in_array["Gender"]==1)
					{
						$content_str=$content_str."\n性别:男";
					}
					else if($in_array["Gender"]==2)
					{
						$content_str=$content_str."\n性别:女";
					}
					$content_str=$content_str."\n邮箱:".$in_array["Email"];
					if($in_array["Status"]==1)
					{
						$content_str=$content_str."\n邮箱激活状态:已激活";
					}
					else if($in_array["Status"]==2)
					{
						$content_str=$content_str."\n邮箱激活状态:已禁用";
					}
					$content_str=$content_str."\n头像url:".$in_array["Avatar"]."\n英文名:".$in_array["EnglishName"];
					if($in_array["IsLeader"]==0)
					{
						$content_str=$content_str."\n是否为上级:普通成员";
					}
					else if($in_array["IsLeader"]==1)
					{
						$content_str=$content_str."\n是否为上级:上级";
					}
					$content_str=$content_str."\n座机:".$in_array["Telephone"];
					if(isset($in_array["ExtAttr"]))
					{
						$content_str=$content_str."\n扩展属性:";
						for($index=0;$index<count($in_array["ExtAttr"]["Item"]);$index++)
						{
							$content_str=$content_str."\n第".($index+1)."项:\n扩展属性名称:".$in_array["ExtAttr"]["Item"][$index]["Name"]."\n扩展属性值:".$in_array["ExtAttr"]["Item"][$index]["Value"];
						}
					}
				}
				else if($ChangeType=="delete_user")//删除成员事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:删除成员事件";
					$content_str=$content_str."\n具体删除成员内容为:\n变更信息的成员UserID:".$in_array["UserID"];
				}
				else if($ChangeType=="create_party")//新增部门事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:新增部门事件";
					$content_str=$content_str."\n具体新增部门内容为:\n部门ID:".$in_array["Id"]."\n部门名称:".$in_array["Name"]."\n父部门id:".$in_array["ParentId"]."\n部门排序:".$in_array["Order"];
				}
				else if($ChangeType=="update_party")//更新部门事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:更新部门事件";
					$content_str=$content_str."\n具体更新部门内容为:\n部门ID:".$in_array["Id"]."\n部门名称:".$in_array["Name"]."\n父部门id:".$in_array["ParentId"];
				}
				else if($ChangeType=="delete_party")//删除部门事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:删除部门事件";
					$content_str=$content_str."\n具体删除部门内容为:\n部门ID:".$in_array["Id"];
				}
				else if($ChangeType=="update_tag")//标签成员变更事件
				{
					$content_str=$content_str."\n具体通讯录变更子事件类型:标签成员变更事件";
					$content_str=$content_str."\n具体标签成员变更内容为:\n标签ID:".$in_array["TagId"];
					if(isset($in_array["AddUserItems"]))
					{
						$content_str=$content_str."\n标签中新增的成员userid列表为:".$in_array["AddUserItems"];
					}
					if(isset($in_array["DelUserItems"]))
					{
						$content_str=$content_str."\n标签中删除的成员userid列表为:".$in_array["DelUserItems"];
					}
					if(isset($in_array["AddPartyItems"]))
					{
						$content_str=$content_str."\n标签中新增的部门id列表为:".$in_array["AddPartyItems"];
					}
					if(isset($in_array["DelPartyItems"]))
					{
						$content_str=$content_str."\n标签中删除的部门id列表为:".$in_array["DelPartyItems"];
					}
				}
			}
			else if($Event=="click")//菜单事件
			{
				$content_str=$content_str."\n具体事件类型:菜单事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"];
			}
			else if($Event=="view")//点击菜单跳转链接的事件
			{
				$content_str=$content_str."\n具体事件类型:点击菜单跳转链接的事件\n事件KEY值(设置的跳转URL):".$in_array["EventKey"];
			}
			else if($Event=="scancode_push")//扫码推事件
			{
				$content_str=$content_str."\n具体事件类型:扫码推事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"]."\n扫描类型:".$in_array["ScanCodeInfo"]["ScanType"]."\n扫描结果:".$in_array["ScanCodeInfo"]["ScanResult"];
			}
			else if($Event=="scancode_waitmsg")//扫码推事件且弹出“消息接收中”提示框的事件
			{
				$content_str=$content_str."\n具体事件类型:扫码推事件且弹出“消息接收中”提示框的事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"]."\n扫描类型:".$in_array["ScanCodeInfo"]["ScanType"]."\n扫描结果:".$in_array["ScanCodeInfo"]["ScanResult"];
			}
			else if($Event=="pic_sysphoto")//弹出系统拍照发图的事件
			{
				$content_str=$content_str."\n具体事件类型:弹出系统拍照发图的事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"]."\n发送的图片数量:".$in_array["SendPicsInfo"]["Count"];
			}
			else if($Event=="pic_photo_or_album")//弹出拍照或者相册发图的事件
			{
				$content_str=$content_str."\n具体事件类型:弹出拍照或者相册发图的事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"]."\n发送的图片数量:".$in_array["SendPicsInfo"]["Count"];
			}
			else if($Event=="pic_weixin")//弹出微信相册发图器的事件
			{
				$content_str=$content_str."\n具体事件类型:弹出微信相册发图器的事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"]."\n发送的图片数量:".$in_array["SendPicsInfo"]["Count"];
			}
			else if($Event=="location_select")//弹出地理位置选择器的事件
			{
				$content_str=$content_str."\n具体事件类型:弹出地理位置选择器的事件\n事件KEY值(与自定义菜单接口中KEY值对应):".$in_array["EventKey"]."X坐标信息:".$in_array["SendLocationInfo"]["Location_X"]."\nY坐标信息:".$in_array["SendLocationInfo"]["Location_Y"]."\n精度:".$in_array["SendLocationInfo"]["Scale"]."\n地理位置的字符串信息:".$in_array["SendLocationInfo"]["Label"]."\nPOI的名字:".$in_array["SendLocationInfo"]["Poiname"];
			}
			$msg_header["text"]=array("content"=>$content_str);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg_header));//发送信息头部信息
		}
		else//其他
		{
			exit;//暂不处理
		}
	}
	
	function pushMsgToDialog($in_array)
	{
		$touser = "qy012673c6c909d8002ade9104ce";//成员ID列表（消息接收者，多个接收者用‘|’分隔，最多支持1000个）。特殊情况：指定为@all，则向该企业应用的全部成员发送(非必需)
		$agentid = 1000005;
		$FromUserName=$in_array["FromUserName"];
		$FromAgentID=$in_array["AgentID"];
		$MsgType=$in_array["MsgType"];
		if($FromUserName!="dcy" || $FromAgentID!=1000004)
		{
			exit;//不是要处理的消息，直接退出
		}
		
		if($MsgType=="text")//文本消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid,
			"text"=>array(
				"content"=>$in_array["Content"]
				)
 			);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="image")//图片消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"image",
 			"agentid"=>$agentid,
			"image"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="voice")//语音消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"voice",
 			"agentid"=>$agentid,
			"voice"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="video")//视频消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"video",
 			"agentid"=>$agentid,
			"video"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="location")//位置消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid,
			"text"=>array(
				"content"=>"位置消息:\n地理位置纬度:".$in_array["Location_X"]."\n地理位置经度:".$in_array["Location_Y"]."\n地图缩放大小:".$in_array["Scale"]."\n地理位置信息:".$in_array["Label"]
				)
 			);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="link")//链接消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid,
			"text"=>array(
				"content"=>"链接消息:\n标题:".$in_array["Title"]."\n描述:".$in_array["Description"]."\n封面缩略图的url:".$in_array["PicUrl"]
				)
 			);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg));//发送信息
		}
		else//其他
		{
			exit;//暂不处理
		}
	}
	
	function pushMsgToDCY($in_array)
	{
		$touser = "dcy";//成员ID列表（消息接收者，多个接收者用‘|’分隔，最多支持1000个）。特殊情况：指定为@all，则向该企业应用的全部成员发送(非必需)
		$agentid = 1000004;
		$FromUserName=$in_array["FromUserName"];
		$FromAgentID=$in_array["AgentID"];
		$MsgType=$in_array["MsgType"];
		if($FromUserName!="qy012673c6c909d8002ade9104ce" || $FromAgentID!=1000005)
		{
			exit;//不是要处理的消息，直接退出
		}
		$state = new jxlstate();
		$state_code=$state->checkstate();
		if(($state_code==0 || $state_code==-1) && $MsgType!="event")
		{
			exit;//未开启发送状态或发送状态已过期或非事件消息
		}
		
		if($MsgType=="text")//文本消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"text",
 			"agentid"=>$agentid,
			"text"=>array(
				"content"=>$in_array["Content"]
				)
 			);
			$api_header = new APP_API($agentid);
			var_dump($api_header->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="image")//图片消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"image",
 			"agentid"=>$agentid,
			"image"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="voice")//语音消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"voice",
 			"agentid"=>$agentid,
			"voice"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="video")//视频消息
		{
			$msg = array(
 			"touser"=>$touser,
 			"msgtype"=>"video",
 			"agentid"=>$agentid,
			"video"=>array(
				"media_id"=>$in_array["MediaId"]
				)
 			);
			$api = new APP_API($agentid);
			var_dump($api->sendMsgToUser($msg));//发送信息
		}
		else if($MsgType=="event")//事件消息(事件消息较特殊，不发给dcy，获取数据库内的信息，加工处理后回传给自己)
		{
			$Event=$in_array["Event"];//事件类型
			if($Event=="click")//菜单事件
			{
				$EventKey=$in_array["EventKey"];//事件KEY值，与自定义菜单接口中KEY值对应
				$content_str="";
				if($EventKey=="query")//查询状态
				{
					$state_code=$state->checkstate();
					if($state_code==0)
					{
						$content_str="状态:关闭";
					}
					else if($state_code==-1)
					{
						$content_str="状态:开启状态已过期，目前状态关闭";
						$state_code=$state->state_off();
					}
					else
					{
						$content_str="状态:开启，有效期到".date("Y年m月d日H:i:s",$state_code);
					}
				}
				else if($EventKey=="open")//开启
				{
					$state_code=$state->state_on();
					if($state_code==true)
					{
						$content_str="开启成功";
					}
					else
					{
						$content_str="开启失败";
					}
				}
				else if($EventKey=="close")//关闭
				{
					$state_code=$state->state_off();
					if($state_code==true)
					{
						$content_str="关闭成功";
					}
					else
					{
						$content_str="关闭失败";
					}
				}
				$msg = array(
						"touser"=>"qy012673c6c909d8002ade9104ce",
						"msgtype"=>"text",
						"agentid"=>1000005
				);
				$msg["text"]=array("content"=>$content_str);
				$api_header = new APP_API(1000005);
				var_dump($api_header->sendMsgToUser($msg));//发送信息
			}
			else//其他
			{
				exit;//暂不处理
			}
		}
		else//其他
		{
			exit;//暂不处理
		}
	}
?>

