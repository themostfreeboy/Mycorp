<?php
	function jxlencode($in)
	{
		$public_key = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA25FvCCCUFqxfDOktl9a8
xL3/u1exmFz/KVJ3IjjO3ygj9OBFuUvYSsDOrUECEFOoLajoTjSkBx0gw477MtDp
t6sTY1G1NHUe6nXEjTQ9QK5lmyZW8cbq2Cz5pcoVdoK8od9xtqzm0rvYvQsSaN/M
kdSjHNAR7dycKNupDpv6UI82RqCR1LqiYxPJ4+tz6lCxMRyA3vvPi/HStfw9La5c
qanjZ9CuL5RD3ObnAC8IKrwQRCqpGrAS+6NG89Cxs2pZz0cQWwfA/TjveLU4DK5/
BUHn/Sp13dBXtX6rua/hnllrvjj+12pYIAUk0Oafamd9+kKt8z5HuQ5Yu+b3yqXn
fQIDAQAB
-----END PUBLIC KEY-----
';
		$pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
		openssl_public_encrypt($in,$out,$pu_key);//公钥加密
		$out = base64_encode($out);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		return $out;
	}
?>