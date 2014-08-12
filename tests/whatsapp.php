#!/usr/bin/php
<?php
require '../src/whatsprot.class.php';

function fgets_u($pStdn)
{
    $pArr = array($pStdn);

    if (false === ($num_changed_streams = stream_select($pArr, $write = NULL, $except = NULL, 0))) {
        print("\$ 001 Socket Error : UNABLE TO WATCH STDIN.\n");

        return FALSE;
    } elseif ($num_changed_streams > 0) {
        return trim(fgets($pStdn, 1024));
    }
    return null;
}

$nickname = "WhatsAPI Test";

// #### DO NOT ADD YOUR INFO AND THEN COMMIT THIS FILE! ####
$sender = 	""; // Mobile number with country code (but without + or 00)
$imei = 	""; // MAC Address for iOS IMEI for other platform (Android/etc)
$password =     ""; // Password you received from WhatsApp

if ($argc < 2) {
    echo "USAGE: ".$_SERVER['argv'][0]." [-l] [-s <phone> <message>] [-i <phone>] [-set <status>]\n";
    echo "\tphone: full number including country code, without '+' or '00'\n";
    echo "\t-s: send message\n";
    echo "\t-l: listen for new messages\n";
    echo "\t-i: interactive conversation with <phone>\n";
    echo "\t-set: Set Status to <status>\n";
    exit(1);
}

$dst=$_SERVER['argv'][2];
$msg = "";
for ($i=3; $i<$argc; $i++) {
    $msg .= $_SERVER['argv'][$i]." ";
}

echo "[] Logging in as '$nickname' ($sender)\n";
$wa = new WhatsProt($sender, $imei, $nickname, TRUE);

$wa->connect();
$wa->loginWithPassword($password);

if ($_SERVER['argv'][1] == "-i") {
    echo "\n[] Interactive conversation with $dst:\n";
    stream_set_timeout(STDIN,1);
    while (TRUE) {
        while($wa->pollMessage());
        $buff = $wa->getMessages();
        if (!empty($buff)) {
            print_r($buff);
        }
        $line = fgets_u(STDIN);
        if ($line != "") {
            if (strrchr($line, " ")) {
                // needs PHP >= 5.3.0
                $command = trim(strstr($line, ' ', TRUE));
            } else {
                $command = $line;
            }
            switch ($command) {
                case "/query":
                    $dst = trim(strstr($line, ' ', FALSE));
                    echo "[] Interactive conversation with $dst:\n";
                    break;
                case "/lastseen":
                    echo "[] Request last seen $dst: ";
                    $wa->sendGetRequestLastSeen($dst);
                    break;
                default:
                    echo "[] Send message to $dst: $line\n";
                    $wa->sendMessage($dst , $line);
                    break;
            }
        }
    }
    exit(0);
}

if ($_SERVER['argv'][1] == "-l") {
    echo "\n[] Listen mode:\n";
    while (TRUE) {
        $wa->pollMessage();
        $data = $wa->getMessages();
        if(!empty($data)) print_r($data);
        sleep(1);
    }
    exit(0);
}

if ($_SERVER['argv'][1] == "-set") {
    echo "\n[] Setting status:\n";
    $wa->sendStatusUpdate($_SERVER['argv'][2]);
    exit(0);
}

echo "\n[] Request last seen $dst: ";
$wa->sendGetRequestLastSeen($dst);

echo "\n[] Send message to $dst: $msg\n";
$wa->sendMessage($dst , $msg);
echo "\n";
