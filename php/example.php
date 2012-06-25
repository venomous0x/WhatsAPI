<?php
require "whatsapp.class.php";
 
// DEMO OF USAGE
$wa = new WhatsApp("full number without + or 00", "password", "John Doe");
$wa->Connect();
$wa->Login();
 
 
// SEND FILE TEST 
// sendImage($msgid,$to,$path,$size,$link,$b64thumb);
$link = "http://onetoanother.com/images/comingsoon.jpg";
$thumb = file_get_contents("demo/x3.jpg");
$b64thumb = base64_encode($thumb);
$wa->sendImage(time()."-1","973xxxxxx","ccf7b9444a511639efd998260b712253.jpg","9999",$link,$b64thumb);
////// END SEND FILE TEST
 
// SEND AN EMOJI TEST
$str = "tgas [emo]EE808A[/emo] wtwet [emo]EE808C[/emo]";
function parsEmo($matches){
	return hex2str($matches[1]);
}
$bbcode = preg_replace_callback("#\[emo\](.+)\[\/emo\]#iUs","parsEmo",$str);
$wa->Message(time()."-1","97366666666","$bbcode");
 //$wa->RequestLastSeen("9733110772"); 
?>
