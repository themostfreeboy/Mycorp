<?php
	class jxlstate
	{
		public $conn;
		
		function __construct()
		{
			date_default_timezone_set("Asia/Shanghai");//设置默认时区
			$this->$conn = new mysqli("localhost", "mycorp", "wWEri2BKXd", "mycorp");
			$this->$conn->query("set names utf8;");
		}
		
		public function checkstate()
		{
			$result = $this->$conn->query("SELECT time FROM statetable WHERE name='state' limit 1;");
			$data=array();
			while($tmp=mysqli_fetch_assoc($result))
			{
				$data[]=$tmp;
			}
			if(count($data)==0)//数据库中未查到此数据
			{
				return 0;
			}
			else//数据库中查到此数据
			{
				$get_time=$data[0]["time"];
				$current_time=time();
				if($get_time==0)
				{
					return 0;//关闭
				}
				else if($get_time>0 && $current_time<$get_time)
				{
					return $get_time;//开启
				}
				else if($get_time>0 && $current_time>$get_time)
				{
					return -1;//已过期
				}
				return 0;
			}
		}
		
		public function state_on()
		{
			$expire = 3600;//有效时间(单位:s)
			$time = time()+$expire;
			$result = $this->$conn->query("UPDATE statetable SET time=$time WHERE name='state';");
			return $result;
		}
		
		public function state_off()
		{
			$result = $this->$conn->query("UPDATE statetable SET time=0 WHERE name='state';");
			return $result;
		}
	}
?>