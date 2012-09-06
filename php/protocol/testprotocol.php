<?php
require "whatsprot.class.php";
# phone number, IMEI, and name, the IMEI is reversed 
# and hashed in whatsprot.class.php so just put your 
# IMEI here as it is!
$w = new WhatsProt("***********", "***************", "John Doe");
$w->Connect();
$w->Login();
$w->Message(time() . "-1", "***********", "yurp");
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

