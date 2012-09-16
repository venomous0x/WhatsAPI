<?php

function isShort($str){
	$len = strlen($str);
	if($len < 256)$res = true;
	else $res =  false;
	return $res;
}

function strlen_wa($str){
	$len = strlen($str);
	if($len >= 256)$len = $len&0xFF00 >> 8;
	return $len;
}

function _hex($int){
    return (strlen(sprintf("%X", $int))%2==0) ? sprintf("%X", $int) : sprintf("0%X", $int);
}

function random_uuid(){
	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}

function strtohex($str){
	$hex = '';
	for ($i=0; $i < strlen($str); $i++)$hex .= "\x".dechex(ord($str[$i]));
	return $hex;
}

function startsWith($haystack, $needle , $pos=0){
    $length = strlen($needle);
    return (substr($haystack, $pos, $length) === $needle);
}

function endsWith($haystack, $needle){
    $length = strlen($needle);
    $start  = $length * -1; 
    return (substr($haystack, $start) === $needle);
}

function createIcon($file)
{
    $outfile = "thumb.jpg";
    $cmd = "convert $file -resize 100x100 $outfile";
    system($cmd);
    $fp = fopen($outfile, "r");
    $contents = fread($fp, filesize($outfile));
    fclose($fp);
    $b64 = base64_encode($contents);
    $outfile .= "b64";
    $fp = fopen($outfile, "w");
    fwrite($fp, $b64);
    fclose($fp);
}

?>
