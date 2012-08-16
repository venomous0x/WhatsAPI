<?php
require "whatsprot.class.php";
$w = new WhatsProt("31631782112", md5(strrev("359599040911883")), "John Doe");
$w->Connect();
$w->Login();
?>

