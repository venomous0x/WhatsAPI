<?php

/**
 * Media uploader class
 */
class WhatsMediaUploader
{
    protected static function sendData($host, $POST, $HEAD, $filepath, $mediafile, $TAIL)
    {
        $sock = fsockopen("ssl://" . $host, 443);

        fwrite($sock, $POST);
        fwrite($sock, $HEAD);

        //write file data
        $buf = 1024;
        $totalread = 0;
        $fp = fopen($filepath, "r");
        while ($totalread < $mediafile['filesize']) {
            $buff = fread($fp, $buf);
            fwrite($sock, $buff, $buf);
            $totalread += $buf;
        }
        //echo $TAIL;
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

        list($header, $body) = preg_split("/\R\R/", $data, 2);

        $json = json_decode($body);
        if (!is_null($json)) {
            return $json;
        }
        return false;
    }

    public static function pushFile($uploadResponseNode, $messageContainer, $mediafile, $selfJID)
    {
        //get vars
        $url = $uploadResponseNode->getChild("media")->getAttribute("url");
        $filepath = $messageContainer["filePath"];
        $to = $messageContainer["to"];
        return self::getPostString($filepath, $url, $mediafile, $to, $selfJID);
    }

    protected static function getPostString($filepath, $url, $mediafile, $to, $from)
    {
        $host = parse_url($url, PHP_URL_HOST);

        //filename to md5 digest
        $cryptoname = md5($filepath) . "." . $mediafile['fileextension'];
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
        $hBAOS .= "Content-Type: " . $mediafile['filemimetype'] . "\r\n\r\n";

        $fBAOS = "\r\n--" . $boundary . "--\r\n";

        $contentlength += strlen($hBAOS);
        $contentlength += strlen($fBAOS);
        $contentlength += $mediafile['filesize'];

        $POST = "POST " . $url . "\r\n";
        $POST .= "Content-Type: multipart/form-data; boundary=" . $boundary . "\r\n";
        $POST .= "Host: " . $host . "\r\n";
        $POST .= "User-Agent: WhatsApp/2.3.53 S40Version/14.26 Device/Nokia302\r\n";
        $POST .= "Content-Length: " . $contentlength . "\r\n\r\n";

        return self::sendData($host, $POST, $hBAOS, $filepath, $mediafile, $fBAOS);
    }

}

?>
