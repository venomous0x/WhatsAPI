<?php
require_once 'func.php';

function generateRequestToken($country, $phone) {
	$waString = "UxYPUgMKRMKDEMKCwprCjcKMRjohaSlXQQ==";
	$noMediaHash = "AAGpM5zvDnFyrsmemfAETcw/kPWMRcCoW96rBU2pphtEOCWNVhSp8QX6";
	$waPrefix = "Y29tLndoYXRzYXBw";
	$signature = "MIIDMjCCAvCgAwIBAgIETCU2pDALBgcqhkjOOAQDBQAwfDELMAkGA1UEBhMCVVMxEzARBgNVBAgTCkNhbGlmb3JuaWExFDASBgNVBAcTC1NhbnRhIENsYXJhMRYwFAYDVQQKEw1XaGF0c0FwcCBJbmMuMRQwEgYDVQQLEwtFbmdpbmVlcmluZzEUMBIGA1UEAxMLQnJpYW4gQWN0b24wHhcNMTAwNjI1MjMwNzE2WhcNNDQwMjE1MjMwNzE2WjB8MQswCQYDVQQGEwJVUzETMBEGA1UECBMKQ2FsaWZvcm5pYTEUMBIGA1UEBxMLU2FudGEgQ2xhcmExFjAUBgNVBAoTDVdoYXRzQXBwIEluYy4xFDASBgNVBAsTC0VuZ2luZWVyaW5nMRQwEgYDVQQDEwtCcmlhbiBBY3RvbjCCAbgwggEsBgcqhkjOOAQBMIIBHwKBgQD9f1OBHXUSKVLfSpwu7OTn9hG3UjzvRADDHj+AtlEmaUVdQCJR+1k9jVj6v8X1ujD2y5tVbNeBO4AdNG/yZmC3a5lQpaSfn+gEexAiwk+7qdf+t8Yb+DtX58aophUPBPuD9tPFHsMCNVQTWhaRMvZ1864rYdcq7/IiAxmd0UgBxwIVAJdgUI8VIwvMspK5gqLrhAvwWBz1AoGBAPfhoIXWmz3ey7yrXDa4V7l5lK+7+jrqgvlXTAs9B4JnUVlXjrrUWU/mcQcQgYC0SRZxI+hMKBYTt88JMozIpuE8FnqLVHyNKOCjrh4rs6Z1kW6jfwv6ITVi8ftiegEkO8yk8b6oUZCJqIPf4VrlnwaSi2ZegHtVJWQBTDv+z0kqA4GFAAKBgQDRGYtLgWh7zyRtQainJfCpiaUbzjJuhMgo4fVWZIvXHaSHBU1t5w//S0lDK2hiqkj8KpMWGywVov9eZxZy37V26dEqr/c2m5qZ0E+ynSu7sqUD7kGx/zeIcGT0H+KAVgkGNQCo5Uc0koLRWYHNtYoIvt5R3X6YZylbPftF/8ayWTALBgcqhkjOOAQDBQADLwAwLAIUAKYCp0d6z4QQdyN74JDfQ2WCyi8CFDUM4CaNB+ceVXdKtOrNTQcc0e+t";
	$classesMd5 = "30CnAF22oY+2PUD5pcJGqw==";
	$k = "PkTwKSZqUfAUyR0rPQ8hYJ0wNsQQ3dW1+3SCnyTXIfEAxxS75FwkDf47wNv/c8pP3p0GXKR6OOQmhyERwx74fw1RYSU10I4r1gyBVDbRJ40pidjM41G1I1oN";
	$KEY = "The piano has been drinking";

	//TODO: This phone prefix split XXX-ZZZZZ... is ok for +34 numbers, but needs to be checked
	//      for other countries
	$phone1 = substr($phone, 0, 3);
	$phone2 = substr($phone, 3);

	// This AES secret is not really needed right now
	$id = base64_decode($waString) . $country . $phone2;
	$salt = substr(base64_decode($noMediaHash),2,4);
	$key = pbkdf2('sha1', $id, $salt, 16, 16, true);
	$iv = substr(base64_decode($noMediaHash),6,16);
	$data = substr(base64_decode($noMediaHash),22);
	$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'nofb', '');
	mcrypt_generic_init($td, $key, $iv);
	$aes_secret = mcrypt_generic($td, $data);
	mcrypt_module_close($td);


	// We xor this file because I don't want to have a copyrighted png 
	// on my repository
	$f =  file_get_contents("magic.dat");
	$count = 0;
	for ($i=0; $i < strlen($f); $i++) {
        	$f[$i] = $f[$i] ^ $KEY[$count++];
        	if ($count == strlen($KEY) -1) {
                	$count = 0;
        	}
	}

	$d = base64_decode($waPrefix) . $f;
	$key2 = pbkdf2('sha1', $d, base64_decode($k), 128, 80, true);

	$data = base64_decode($signature) . base64_decode($classesMd5) . $phone;

	$opad = str_repeat(chr(0x5C), 64);
	$ipad = str_repeat(chr(0x36), 64);
	for ($i = 0; $i < 64; $i++) {
		$opad[$i] = $opad[$i] ^ $key2[$i];
		$ipad[$i] = $ipad[$i] ^ $key2[$i];
	}

	$output = hash("sha1", $opad . hash("sha1", $ipad . $data, true), true);
    
	return base64_encode($output);
}
