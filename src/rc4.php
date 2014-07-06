<?php

class rc4
{
    private $s;
    private $i;
    private $j;

    public function __construct($key, $drop)
    {
        $this->s = range(0, 255);
        for ($i = 0, $j = 0; $i < 256; $i++) {
            $k = ord($key{$i % strlen($key)});
            $j = ($j + $k + $this->s[$i]) & 255;
            $this->swap($i, $j);
        }

        $this->i = 0;
        $this->j = 0;
        $this->cipher(range(0, $drop), 0, $drop);
    }

    public function cipher($data, $offset, $length)
    {
        $out = $data;
        for ($n = $length; $n > 0; $n--) {
            $this->i = ($this->i + 1) & 0xff;
            $this->j = ($this->j + $this->s[$this->i]) & 0xff;
            $this->swap($this->i, $this->j);
            $d = ord($data{$offset});
            $out[$offset] = chr($d ^ $this->s[($this->s[$this->i] + $this->s[$this->j]) & 0xff]);
            $offset++;
        }

        return $out;
    }

    protected function swap($i, $j)
    {
        $c = $this->s[$i];
        $this->s[$i] = $this->s[$j];
        $this->s[$j] = $c;
    }

}

// DEPRECATED: WAUTH-1
//class KeyStream
//{
//    private $rc4;
//    private $key;
//
//    public function __construct($key)
//    {
//        $this->rc4 = new RC4($key, 256);
//        $this->key = $key;
//    }
//
//    public function encode($data, $offset, $length, $append = true)
//    {
//        $d = $this->rc4->cipher($data, $offset, $length);
//        $h = substr(hash_hmac('sha1', $d, $this->key, true), 0, 4);
//        if ($append)
//            return $d . $h;
//        else
//            return $h . $d;
//    }
//
//    public function decode($data, $offset, $length)
//    {
//        /* TODO: Hash check */
//
//        return $this->rc4->cipher($data, $offset + 4, $length - 4);
//    }
//
//}
