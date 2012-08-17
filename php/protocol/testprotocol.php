<?php
require "whatsprot.class.php";
$w = new WhatsProt("***********", md5(strrev("***************")), "John Doe");
$w->Connect();
$w->Login();
?>

