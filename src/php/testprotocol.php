<?php
require "whatsprot.class.php";
# phone number, IMEI, and name, the IMEI is reversed 
# and hashed in whatsprot.class.php so just put your 
# IMEI here as it is!
$options = getopt("d::", array("debug::"));
$debug = (array_key_exists("debug", $options) || array_key_exists("d", $options)) ? true : false;
$w = new WhatsProt("***********", "***************", "John Doe", $debug);
$w->Connect();
$w->Login();
$w->Message(time() . "-1", "***********", "yürp");
# To send an image, put the image on the internet somewhere and send it with the MessageImage function.
# if you want to be real fancy then take a scaled down version (100pixel) and base64_encode it, 
# and send that as the last parameter to the MessageImage function.
# I wrote a quick function (funcs.php->createIcon) to do all this for you.
# You can also just leave the last param empty to send no icon
# Obviously this needs better integration... but this is a start...
# Also thumb.jpgb64 is just for an example!
$iconfile = "tests/thumb.jpgb64";
$fp = fopen($iconfile, "r");
$icon = fread($fp, filesize($iconfile));
fclose($fp);

$w->MessageImage(time() . "-1", "***********", "https://lh3.googleusercontent.com/-vT0wjhrlTaQ/T_bwd4_PUYI/AAAAAAAABog/oKPZ6ssJqC0/s673/DSC02471.JPG", "DSC02471.jpg", 55508, $icon);

while(1)
{
    $w->PollMessages();
    $msgs = $w->GetMessages();
    foreach ($msgs as $m)
    {
        # process inbound messages
        #print($m->NodeString("") . "\n");
    }
}
?>

