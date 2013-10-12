<?php
set_time_limit(60); //1 minute
session_start();
session_write_close();
//Calling session_write_close a lot because of session locks.
//Calling session_start will lock the session for writing,
//and any other process (like ajax call) will have to wait
//until it is unlocked, either by finishing page execution
//or by calling session_write_close. This causes a problem
//here because this page has a very long lifetime (1 minute).

$time = $_SESSION["running"];
function onGetProfilePicture($from, $target, $type, $data)
{
    if ($type == "preview") {
        $filename = "../pictures/preview_" . $target . ".jpg";
    } else {
        $filename = "../pictures/" . $target . ".jpg";
    }
    $fp = @fopen($filename, "w");
    if ($fp) {
        fwrite($fp, $data);
        fclose($fp);
    }
    session_start();
    $_SESSION["profilepic"] = $filename;
    session_write_close();
}

function running($time)
{
    //Compare initial timestamp in session
    //and current timestamp in session. This
    //timestamp is updated each time index.php
    //is called (page is refreshed). This will
    //kill the old socket.php processes.
    session_start();
    $running = $_SESSION["running"];
    if ($running != $time) {
        //index.php refreshed by user
        die();
    }
    session_write_close();

    return true; //continue running
}

function onGetImage($mynumber, $from, $id, $type, $t, $name, $size, $url, $file, $mimetype, $filehash, $width, $height, $preview)
{
    //save thumbnail
    $previewuri = "../media/thumb_" . $file;
    $fp = @fopen($previewuri, "w");
    if ($fp) {
        fwrite($fp, $preview);
        fclose($fp);
    }

    //download and save original
    $data = file_get_contents($url);
    $fulluri = "../media/" . $file;
    $fp = @fopen($fulluri, "w");
    if ($fp) {
        fwrite($fp, $data);
        fclose($fp);
    }

    //format message
    $msg = "<a href='$fulluri' target='_blank'><img src='$previewuri' /></a>";

    //insert message
    session_start();
    $in = $_SESSION["inbound"];
    $in[] = $msg;
    $_SESSION["inbound"] = $in;
    session_write_close();
}

require_once '../whatsprot.class.php';
$target = @$_POST["target"];
$username = "************";
$password = "******************************";
$w = new WhatsProt($username, 0, "WhatsApi AJAX Demo", true);

$w->eventManager()->bind("onGetImage", "onGetImage");
$w->eventManager()->bind("onGetProfilePicture", "onGetProfilePicture");

$w->connect();
$w->loginWithPassword($password);

$initial = @$_POST["initial"];
if ($initial == "true" && $target != null) {
    //request contact picture only on first call
    $w->sendGetProfilePicture($target);
}

//subscribe contact status
$w->SendPresenceSubscription($target);
//TODO: presense handling (online/offline/typing/last seen)

while (running($time)) {
    $w->pollMessages();

    running($time); //check again if timestamp has been updated
    //check for outbound messages to send:
    session_start();
    $outbound = $_SESSION["outbound"];
    $_SESSION["outbound"] = array();
    session_write_close();
    if (count($outbound) > 0) {
        foreach ($outbound as $message) {
            //send messages
            $w->sendMessage($message["target"], $message["body"]);
            $w->pollMessages();
        }
    }

    //check for received messages:
    $messages = $w->getMessages();
    if (count($messages) > 0) {
        session_start();
        $inbound = $_SESSION["inbound"];
        $_SESSION["inbound"] = array(); //lock
        foreach ($messages as $message) {
            $data = @$message->getChild("body")->getData();
            if ($data != null && $data != '') {
                $inbound[] = $data;
            }
        }
        $_SESSION["inbound"] = $inbound;
        session_write_close();
    }
}
