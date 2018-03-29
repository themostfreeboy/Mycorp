<?php
	function checkuser($user_id_md5)
	{
		$conn = new mysqli("localhost", "jxltest", "Np7DXmxaXF", "jxltest");
		$conn->query("set names utf8;");
		$result = $conn->query("select userid from userinfo where userid='$user_id_md5' limit 1;");
		$data=array();
		while($tmp=mysqli_fetch_assoc($result))
		{
			$data[]=$tmp;
		}
		if(count($data)==0)//数据库中尚未包含此userid
		{
			return false;
		}
		else//数据库中已包含此userid
		{
			return true;
		}
	}
	function adduser($user_id_md5)
	{
		if(checkuser==false)//数据库中尚未包含此userid
		{
			$conn = new mysqli("localhost", "jxltest", "Np7DXmxaXF", "jxltest");
			$conn->query("set names utf8;");
			$result = $conn->query("INSERT INTO userinfo (userid) VALUES ('$user_id_md5');");
			return $result;
		}
		else//数据库中已包含此userid
		{
			return false;
		}
	}
?>