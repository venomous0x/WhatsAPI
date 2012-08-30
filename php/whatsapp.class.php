<?php
require "func.php";
require "decode.php";

 class WhatsApp {
	
	private $_server = 's.whatsapp.net';
	private $_host = 'bin-short.whatsapp.net';
	private $_Digest_Uri = 'xmpp/s.whatsapp.net';
	private $_Realm = 's.whatsapp.net';
	private $_Qop = 'auth';
	private $_contype = 'STREAM_CLIENT_PERSISTENT';
	private $_device = "iPhone";
	private $_whatsAppVer = "2.8.2";
	private $_port = 5222;
	private $_timeout = array("sec" => 2, "usec" => 0);
	/*
	Account Info
	*/
	private $_accinfo;
	private $_account_status;	// Active or not .. 
	private $_account_kind;	// paid or free .. 
	private $_account_creation ;	// Timestamp of creation date
	private $_account_expiration;	// Timestamp of expiration date

	private $_incomplete_message = "";

	function __construct($Number, $Password, $Nickname){
		$this->_number = $Number;
		$this->_password = $Password;
		$this->_nickname = $Nickname;
	}
	
	function _is_full_msg($str){
		$length = ord(substr($str,0,1));
 		$sl = strlen($str);
 		if (strlen($str) < $length){
			return false;
		}
		return true;
	}

	function _identify($str){
		$msg_identifier = "\x5D\x38\xFA\xFC";		
		$server_delivery_identifier = "\x8C";		
		$client_delivery_identifier = "\x7f\xbd\xad";
		$acc_info_iden = "\x99\xBD\xA7\x94";		
		$last_seen_ident = "\x48\x38\xFA\xFC";
		$last_seen_ident2 = "\x7B\xBD\x4C\x8B";

		if ($this->_is_full_msg($str) == false){
			return 'incomplete_msg';
		}
		else if(startsWith($str,$msg_identifier,3)){ 
			if(endsWith($str,$server_delivery_identifier)){
				return 'server_delivery_report';
			}
			else if(endsWith($str,$client_delivery_identifier)){
				return 'client_delivery_report';
			}
			else{
			return 'msg'; 
			}
		}
		else if(startsWith($str,$acc_info_iden,3)){
		return 'account_info';
		}
		else if(startsWith($str,$last_seen_ident,3) && strpos($str, $last_seen_ident2)){
		return 'last_seen';
		}
	}
	
	function Connect(){ 
		$Socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		socket_connect( $Socket, $this->_host, $this->_port );
		$this->_socket = $Socket;
		socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, $this->_timeout);
	}
	
	function send($data){
		socket_send( $this->_socket, $data, strlen($data), 0 );
	}	
	
	function read(){
		$buff = "";
		$timeout_sec = 1;
		$timeout_usec = 2000;
		# Dirty method for draining a socket without blocking for long periods of time.
		# Ideally reading the socket would be done in a separate thread, but this is php... 
		# so that aint going to happen.
		do
		{
			$read = array($this->_socket);
			$write  = NULL;
			$except = NULL;
			$num_changed_sockets = socket_select($read, $write, $except, $timeout_sec, $timeout_usec);
			$timeout_sec = 0;
			if ($num_changed_sockets > 0)
			{
				$buff .= $this->read_soc();
			}
		} while ($num_changed_sockets > 0);
		return $buff;
	}

	function read_soc(){
		$buff = $this->_incomplete_message . socket_read( $this->_socket, 1024 );
		$this->_incomplete_message = "";
		$resarray = explode("\x00", $buff);
		$removed  = array_shift($resarray);	
		$rescount = count($resarray);
		if ($rescount != 0){
			foreach($resarray as $k=>$v){
				$rcvd_type = $this->_identify($v);
				if ($rcvd_type == 'incomplete_msg'){
					$this->_incomplete_message = $v;
				}
				else if($rcvd_type == 'msg'){
					$msg = $this->parse_received_message($v);
					echo "[] Message from ".$msg['from_number']." '".$msg['sender_name']."': ".$msg['body_txt']."\n"; 
					//echo json_encode($msg); // Do something with the message here ?
				}
				else if ($rcvd_type == 'account_info'){
					$accinfo = $this->parse_account_info($v);
					$this->accinfo = $accinfo;
				}
				else if ($rcvd_type == 'last_seen'){
					$lastseen = $this->parse_last_seen($v);
					echo json_encode($lastseen); // They're stored in account variables too 
				}
			}
		unset($rcvd_type);
		}
	    return $buff;
	}
	
	function parse_received_message($msg){
		// RCVD MSG IN STRING 
		$length = substr($msg,0,1);
		$message['length'] = ord($length);		 // PACKET EXCLUDING 00 AND FIRST HEX SHOULD EQUAL THIS NUMBER
		$msg = substr($msg,2); 		// Remove Length & F8
		$message['sec_length'] = ord(substr($msg,0,1)); 		// Length of something i dont know excatly what  
		$msg = substr($msg,5); 		// Remove Second Length ( 1 HEX ) , Remove XML Chrs ( 4 HEX )
		$message['from_number_length'] = ord(substr($msg,0,1));
		$msg = substr($msg,1); 		// Remove Length
		$message['from_number'] = substr($msg,0,$message['from_number_length']);
		$msg = substr($msg,$message['from_number_length']);				// Remove NUMBER
		$msg = substr($msg,3); 		// Remove F8 & XML ( 2 HEX )
		$message['message_id_length'] = ord(substr($msg,0,1));
		$msg = substr($msg,1); 		// Remove Length
		$message['message_id'] = substr($msg,0,$message['message_id_length']);			
		$msg = substr($msg,$message['message_id_length']);			
		$msg = substr($msg,4); 		// Remove XML ( 4 HEX )
		$message['timestamp_length'] = ord(substr($msg,0,1));
		$msg = substr($msg,1); 		// Remove Length
		$message['timestamp'] = substr($msg,0,$message['timestamp_length']);
		$msg = substr($msg,$message['timestamp_length']);				// Remove Timestamp
		// Check for Retry header 
		if(substr($msg,0,1) == "\x88"){
			$msg = substr($msg,1);
			if ((substr($msg,0,1)) == "\xfc"){
				$msg = substr($msg,3); 		// Remove 4 byte Retry Length , i dont think i will need it
			} else {
				$msg = substr($msg,1); 		// Remove 2 byte Retry Length , i dont think i will need it
			}
		}
		$msg = substr($msg,9); 		// Remove XMPP XML and Name XML Headers 
		$message['sender_name_length'] = ord(substr($msg,0,1));
		$msg = substr($msg,1); 		// Remove Length
		$message['sender_name'] = substr($msg,0,$message['sender_name_length']);			
		$msg = substr($msg,$message['sender_name_length']);			 // Remove sender from msg
		$msg = substr($msg,9); 		// Remove body headers
		$message['body_txt_length'] = ord(substr($msg,0,1));
		$msg = substr($msg,1); 		// Remove Length
		$message['body_txt'] = substr($msg,0,$message['body_txt_length']);			
		$msg = substr($msg,$message['body_txt_length']); 		// Remove body txt
		$msg = substr($msg,9); 		// Remove XMPP XML and Name XML Headers 
		$message['time_length'] = ord(substr($msg,0,1));
		$msg = substr($msg,1); 		// Remove Length
		$message['time'] = substr($msg,0,$message['time_length']);			
		$msg = substr($msg,$message['time_length']);
		$this->MessageReceived($message['from_number'], $message['message_id']);
		return $message;
	}

	function parse_account_info($msg){
		$msg = substr($msg,3); 		// Remove Length,F8,second length
		$msg = substr($msg,4); 		// Remove Success XML
		// Next should be status
		$acst = substr($msg,0,1);	
		if($acst == "\x09"){
		$this->_account_status = 'active';
		} else {
		$this->_account_status = 'inactive';
		}
		$msg = substr($msg,2); 		// Remove status & KIND XML
		$actkind = substr($msg,0,1);
		if($actkind == "\x37"){
		$this->_account_kind = 'free';
		} else {
		$this->_account_kind = 'paid';
		}
		$msg = substr($msg,3); 		// Remove XML
		$creation_timstamp_len = ord(substr($msg,0,1)); // Should return 10 for the next few thousdands years
		$msg = substr($msg,1); 		// Remove Length
		$this->_account_creation = substr($msg,0,$creation_timstamp_len);	
		$msg = substr($msg,$creation_timstamp_len); 		// Remove Timestamp
		$msg = substr($msg,2); 		// Remove Expiration XML
		$expr_length = ord(substr($msg,0,1)); // Should also be 10
		$msg = substr($msg,1); 		// Remove Length
		$this->_account_expiration = substr($msg,0,$expr_length);	
		$x['status'] = $this->_account_status;
		$x['kind'] = $this->_account_kind;
		$x['creation'] = $this->_account_creation;
		$x['expiration'] = $this->_account_expiration;
		return $x;
	}
	
	function parse_last_seen($msg){
		$msg = substr($msg,7); 		// Remove Some XML DATA
		$moblen = ord(substr($msg,0,1)); 
		$msg = substr($msg,1); 		// Remove Length
		$lastseen['mobile'] = substr($msg,0,$moblen);	
		$msg = substr($msg,$moblen);
		$msg = substr($msg,16); 		// Remove Some More XML DATA
		$last_seen_len = ord(substr($msg,0,1)); 
		$msg = substr($msg,1); 		// Remove Length
		$lastseen['seconds_ago'] = substr($msg,0,$last_seen_len);	
		return $lastseen;
	}
	
	function status(){
		$status = socket_get_status($this->_socket);
		print_r($status);
	}
	
	function Login(){
		$Data = "WA"."\x00\x04\x00\x19\xf8\x05\x01\xa0\x8a\x84\xfc\x11"."$this->_device-$this->_whatsAppVer-$this->_port".
				"\x00\x08\xf8\x02\x96\xf8\x01\xf8\x01\x7e\x00\x07\xf8\x05\x0f\x5a\x2a\xbd\xa7";
		$this->send($Data);
		$Buffer = $this->read();
		$Response = base64_decode(substr( $Buffer, 26 ));
		$arrResp = explode( ",", $Response );
		$authData = array();
		foreach( $arrResp AS $Key => $Value ){
			$resData = explode( "=", $Value );
			$authData[$resData[0]] = str_replace( '"', '', $resData[1] );
		}
		$ResData = $this -> _authenticate( $authData['nonce'] );
		$Response = "\x01\x31\xf8\x04\x86\xbd\xa7\xfd\x00\x01\x28".base64_encode($ResData);
		$this->send($Response);
		$rBuffer = $this->read();
		$this->read();
		$name = $this->_nickname;
		$next = "\x00".chr(8+strlen($name))."\xf8\x05\x74\xa2\xa3\x61\xfc".chr(strlen($name)).$name.
				"\x00\x15\xf8\x06\x48\x43\x05\xa2\x3a\xf8\x01\xf8\x04\x7b\xbd\x4d\xf8\x01\xf8\x03\x55\x61\x24".
				"\x00\x12\xf8\x08\x48\x43\xfc\x01\x32\xa2\x3a\xa0\x8a\xf8\x01\xf8\x03\x1f\xbd\xb1";
		$stream = $this->send($next);
		$this->read();
	}
	
	public function _authenticate( $nonce,$_NC = '00000001'){
		$cnonce = random_uuid();		
		$a1 = sprintf('%s:%s:%s', $this ->_number, $this ->_server, $this ->_password);
		$a1 = pack('H32', md5($a1) ) . ':' . $nonce . ':' . $cnonce;
		$a2 = "AUTHENTICATE:" . $this->_Digest_Uri;
		$password = md5($a1) . ':' . $nonce . ':' . $_NC . ':' . $cnonce . ':' . $this->_Qop . ':' .md5($a2);
		$password = md5($password);
		$Response = sprintf('username="%s",realm="%s",nonce="%s",cnonce="%s",nc=%s,qop=%s,digest-uri="%s",response=%s,charset=utf-8',	$this -> _number, $this->_Realm, $nonce, $cnonce, $_NC, $this->_Qop, $this->_Digest_Uri, $password);
		return $Response;
	}

	function MessageReceived($to,$msgid){
		$to_length = chr(mb_strlen($to,"UTF-8"));
		$msgid_length = chr(mb_strlen($msgid));
		#$content = "\x00$msg_length";
		$content = "\xf8\x08\x5d\xa0\xfa\xfc$to_length";
		$content .= $to;
		$content .= "\x8a\xa2\x1b\x43\xfc$msgid_length";
		$content .= $msgid;
		$content .= "\xf8\x01\xf8\x03\x7f\xbd\xad";
		$total_length = hex2str(_hex(strlen($content)));
		$msg = "\x00$total_length";
		$msg .= $content;
		$this->send($msg);
	}

	public function Message($msgid,$to,$txt){
		$long_txt_bool = isShort($txt);
		$txt_length = hex2str(_hex(strlen($txt)));
		$to_length = chr(mb_strlen($to,"UTF-8"));
		$msgid_length = chr(mb_strlen($msgid));
		$content = "\xF8\x08\x5D\xA0\xFA\xFC$to_length";
		$content .= $to;
		$content .= "\x8A\xA2\x1B\x43\xFC$msgid_length";
		$content .= $msgid;
		$content .= "\xF8\x02\xF8\x04\xBA\xBD\x4F\xF8\x01\xF8\x01\x8C\xF8\x02\x16";
		if(!$long_txt_bool){
		$content .= "\xFD\x00$txt_length";
		} else { 
		$content .= "\xFC$txt_length";
		}
		$content .= $txt;
		$total_length = hex2str(_hex(strlen($content)));
		if(strlen($total_length) == '1'){		$total_length = "\x00$total_length";		}
		$msg ="";
		$msg .= "$total_length";
		$msg .= $content;
		//echo str2hex($msg);
		//printhexstr($msg, "msg");
		$stream = $this->send($msg);
		$this->read();
	}
	
	public function sendImage($msgid,$to,$path,$size,$link,$b64thumb){
		$thumb_length = hex2str(_hex(strlen($b64thumb)));
		$to_length = chr(mb_strlen($to,"UTF-8"));
		$msgid_length = chr(mb_strlen($msgid));
		$path_length = chr(mb_strlen($path));
		$size_length = chr(mb_strlen($size));		// in bytes
		$link_length = chr((strlen($link)));
		$content = "\xF8\x08\x5D\xA0\xFA\xFC$to_length";
		$content .= $to;
		$content .= "\x8A\xA2\x1B\x43\xFC$msgid_length";
		$content .= $msgid;
		$content .= "\xF8\x02\xF8\x04\xBA\xBD\x4F\xF8\x01\xF8\x01\x8C\xF8\x0C\x5C\xBD\xB0\xA2\x44\xFC\x04\x66\x69\x6C\x65\xFC$path_length";
		$content .= $path;
		$content .= "\xFC\x04\x73\x69\x7A\x65\xFC$size_length";
		$content .= $size;
		$content .= "\xA5\xFC$link_length";
		$content .= $link;
		$content .= "\xFD\x00$thumb_length";
		$content .= $b64thumb;
		$total_length = hex2str(_hex(strlen($content)));
		$msg ="";
		$msg .= "$total_length";
		$msg .= $content;
		//echo str2hex($msg);
		$stream = $this->send($msg);
		$this->read();
	}
	
	public function Subscribe($mobile){
		$mob_len = chr(mb_strlen($mobile,"UTF-8"));
		$content = "\xF8\x05\x74\xA2\x98\xA0\xFA\xFC$mob_len";
		$content .= "$mobile";
		$content .= "\x8A";
		$len = strlen($content);
		$total_length = chr($len);
		$request = "\x00";
		$request .= "$total_length";
		$request .= $content;
		$stream = $this->send($request);
		$this->read();
	}
	
	public function RequestLastSeen($mobile){
		$mob_len = chr(mb_strlen($mobile,"UTF-8"));
		$content = "\xF8\x08\x48\x43\xFC\x01\x37\xA2\x3A\xA0\xFA\xFC$mob_len";
		$content .= "$mobile";
		$content .= "\x8A\xF8\x01\xF8\x03\x7B\xBD\x4C";
		$len = strlen($content);
		$total_length = chr($len);
		$request = "\x00";
		$request .= "$total_length";
		$request .= $content;
		$stream = $this->send($request);
		$this->read();
	}
	
	public function accountInfo(){
		echo json_encode($this->accinfo);
	}
	
 }
 ?>
