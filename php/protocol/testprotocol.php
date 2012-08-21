<?php
require "whatsprot.class.php";
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
        print($m->NodeString("") . "\n");
    }
}
?>

