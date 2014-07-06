<?php
function decode($hex)
{
    $hexarr = str_split($hex, 2);
    $str = null;
    foreach ($hexarr as $k => $v) {
        $str .= "  " . getToken(hexdec($v));
    }

    return $str;
}

function str2hex($string)
{
    $hexstr = unpack('H*', $string);

    return array_shift($hexstr);
}

function hex2str($hexstr)
{
    $hexstr = str_replace(' ', '', $hexstr);
    $hexstr = str_replace('\x', '', $hexstr);
    $retstr = pack('H*', $hexstr);

    return $retstr;
}

function printhexstr($data, $name)
{
    $data = str2hex($data);
    $len = strlen($data);
    print("Len: $len - $name\n");
    for ($i = 0; $i < $len; $i += 2) {
        if ((($i - 1) % 32) == 31) {
            print("\n");
        }
        printf(" %s%s", $data[$i], $data[$i + 1]);
    }

    print("\n");
}
