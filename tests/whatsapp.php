#!/usr/bin/php
<?php
require '../src/php/whatsprot.class.php';

function fgets_u($pStdn)
{
    $pArr = array($pStdn);

    if (false === ($num_changed_streams = stream_select($pArr, $write = NULL, $except = NULL, 0))) {
        print("\$ 001 Socket Error : UNABLE TO WATCH STDIN.\n");

        return FALSE;
    } elseif ($num_changed_streams > 0) {
        return trim(fgets($pStdn, 1024));
    }
}

$nickname = "WhatsAPI Test";
$sender = 	"491734158233"; // Mobile number with country code (but without + or 00)
$imei = 	"70:DE:E2:69:26:AC"; // MAC Address for iOS IMEI for other platform (Android/etc)

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

$wa->Connect();
$wa->Login();

if ($_SERVER['argv'][1] == "-i") {
    echo "\n[] Interactive conversation with $dst:\n";
    stream_set_timeout(STDIN,1);
    while (TRUE) {
        $wa->PollMessages();
        $buff = $wa->GetMessages();
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
                case "/accountinfo":
                    echo "[] Account Info: ";
                    $wa->accountInfo();
                    break;
                case "/lastseen":
                    echo "[] Request last seen $dst: ";
                    $wa->RequestLastSeen(time()."-1", "$dst");
                    break;
                default:
                    echo "[] Send message to $dst: $line\n";
                    $wa->Message(time()."-1", $dst , $line);
                    break;
            }
        }
    }
    exit(0);
}

if ($_SERVER['argv'][1] == "-l") {
    echo "\n[] Listen mode:\n";
    while (TRUE) {
        $wa->PollMessages();
        $data = $wa->GetMessages();
        if(!empty($data)) print_r($data);
        sleep(1);
    }
    exit(0);
}

if ($_SERVER['argv'][1] == "-set") {
    echo "\n[] Setting status:\n";
    $wa->sendStatusUpdate(1,$_SERVER['argv'][2]);
    exit(0);
}

echo "\n[] Request last seen $dst: ";
$wa->RequestLastSeen(time()."-1", $dst);

echo "\n[] Send message to $dst: $msg\n";
$wa->Message(time()."-1", $dst , $msg);
echo "\n";

?>
