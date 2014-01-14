#!/usr/bin/php
<?php

// This file is part of a tutorial on the blog of Philipp C. Heckel, July 2013
// http://blog.philippheckel.com/2013/07/07/send-whatsapp-messages-via-php-script-using-whatsapi/

require_once('whatsapp_whatsapi_config.php');

$destinationPhone = '491231234567';

$w = new WhatsProt($userPhone, $userIdentity, $userName, $debug);
$w->Connect();
$w->LoginWithPassword($password);
$w->Message($destinationPhone, $argv[1]);

?>
