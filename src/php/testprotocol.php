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

