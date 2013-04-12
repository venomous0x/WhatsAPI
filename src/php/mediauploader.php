<?php
/**
 * Media uploader class
 */
class WhatsMediaUploader
{
    protected static function sendData($host, $POST, $HEAD, $filepath, $TAIL)
    {
        $sock = fsockopen("ssl://" . $host, 443);
        
        fwrite($sock, $POST);
        fwrite($sock, $HEAD);
        
        //write file data
        $buf = 1024;
        $totalread = 0;
        $filesize = filesize($filepath);
        $fp = fopen($filepath, "r");
        while($totalread < $filesize)
        {
            $buff = fread($fp, $buf);
            fwrite($sock, $buff, $buf);
            $totalread += $buf;
        }
        echo $TAIL;
        fwrite($sock, $TAIL);
        sleep(1);
        
        $data = fgets($sock, 8192);
        $data .= fgets($sock, 8192);
        $data .= fgets($sock, 8192);
        $data .= fgets($sock, 8192);
        $data .= fgets($sock, 8192);
        $data .= fgets($sock, 8192);
        $data .= fgets($sock, 8192);
        fclose($sock);
        $lines = explode("\n", $data);
        foreach($lines as $line)
        {
            if(stristr($line, "{"))
            {
                $json = json_decode($line);
                return $json;
            }
        }
        return false;
    }
    
    public static function pushFile($uploadResponseNode, $messageContainer, $selfJID)
    {
        //get vars
        $url = $uploadResponseNode->getChild("media")->getAttribute("url");
        $messageNode = $messageContainer["messageNode"];
        $filepath = $messageContainer["filePath"];
        $to = $messageNode->getAttribute("to");
        return self::getPostString($filepath, $url, $to, $selfJID);
    }
    
    protected static function getPostString($filepath, $url, $to, $from)
    {
        $host = str_replace("https://", "", $url);
        $host = explode("/", $host);
        print_r($host);
        $host = $host[0];
        
        $filetype = mime_content_type($filepath);
        $filesize = filesize($filepath);
        
        //filename to md5 digest
        $cryptoname = md5($filepath) . "." . pathinfo($filepath, PATHINFO_EXTENSION);
        
        $boundary = "zzXXzzYYzzXXzzQQ";
        $contentlength = 0;
        
        $hBAOS = "--" . $boundary . "\r\n";
        $hBAOS .= "Content-Disposition: form-data; name=\"to\"\r\n\r\n";
        $hBAOS .= $to . "\r\n";
        $hBAOS .= "--" . $boundary . "\r\n";
        $hBAOS .= "Content-Disposition: form-data; name=\"from\"\r\n\r\n";
        $hBAOS .= $from . "\r\n";
        $hBAOS .= "--" . $boundary . "\r\n";
        $hBAOS .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . $cryptoname . "\"\r\n";
        $hBAOS .= "Content-Type: " . $filetype . "\r\n\r\n";
        
        $fBAOS = "\r\n--" . $boundary . "--\r\n";
        
        $contentlength += strlen($hBAOS);
        $contentlength += strlen($fBAOS);
        $contentlength += $filesize;
        
        $POST = "POST " . $url . "\r\n";
        $POST .= "Content-Type: multipart/form-data; boundary=" . $boundary . "\r\n";
        $POST .= "Host: " . $host . "\r\n";
        $POST .= "User-Agent: WhatsApp/2.3.53 S40Version/14.26 Device/Nokia302\r\n";
        $POST .= "Content-Length: " . $contentlength . "\r\n\r\n";
        
        return self::sendData($host, $POST, $hBAOS, $filepath, $fBAOS);
    }
}
?>
