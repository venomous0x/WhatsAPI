<?php
/**
 * Created by JetBrains PhpStorm.
 * User: max
 * Date: 29-1-14
 * Time: 11:55
 * To change this template use File | Settings | File Templates.
 */
require_once("rc4.php");
require_once("func.php");

class KeyStream {
    public static $AuthMethod = "WAUTH-2";
    const DROP = 768;
    private $rc4;
    private $seq;
    private $macKey;

    public function __construct($key, $macKey)
    {
        $this->rc4 = new rc4($key, self::DROP);
        $this->macKey = $macKey;
    }

    public static function GenerateKeys($password, $nonce)
    {
        $array = array(
            "key",//placeholders
            "key",
            "key",
            "key"
        );
        $array2 = array(1, 2, 3, 4);
        $nonce .= '0';
        for($j = 0; $j < count($array); $j++)
        {
            $nonce[(strlen($nonce) - 1)] = $array2[$j];
            $foo = wa_pbkdf2("sha1", $password, $nonce, 2, 20, true);
            $array[$j] = $foo;
        }
        return $array;
    }

    public function DecodeMessage($buffer, $macOffset, $offset, $length)
    {
        $mac = $this->computeMac($buffer, $offset);
        //validate mac
        for($i = 0; $i < 4; $i++)
        {
            $foo = ord($buffer[$macOffset + $i]);
            $bar = ord($mac[$i]);
            if($foo !== $bar)
            {
                throw new Exception("MAC mismatch: $foo != $bar");
            }
        }
        $this->rc4->cipher($buffer, $offset, $length);
    }

    public function EncodeMessage($buffer, $offset, $length)
    {
        $data = $this->rc4->cipher($buffer, $offset, $length);
        $mac = $this->computeMac($buffer, $offset);
        return $mac . $data;
    }

    private function computeMac($buffer, $offset)
    {
        $hmac = hash_init("sha1", HASH_HMAC, $this->macKey);
        hash_update($hmac, substr($buffer, $offset));
        $array = chr($this->seq >> 24)
            . chr($this->seq >> 16)
            . chr($this->seq >> 8)
            . chr($this->seq);
        hash_update($hmac, $array);
        $this->seq++;
        return hash_final($hmac, true);
    }
}