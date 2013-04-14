<?php
set_time_limit(60);//1 minute
session_start();
session_write_close();
//Calling session_write_close a lot because of session locks.
//Calling session_start will lock the session for writing,
//and any other process (like ajax call) will have to wait
//until it is unlocked, either by finishing page execution
//or by calling session_write_close. This causes a problem
//here because this page has a very long lifetime (1 minute).

$time = $_SESSION["running"];

function onProfilePicture($from, $type, $data)
{
    if($type == "preview")
    {
        $filename = "../pictures/preview_" . $from . ".jpg";
    }
    else
    {
        $filename = "../pictures/" . $from . ".jpg";
    }
    $fp = @fopen($filename, "w");
    if($fp)
    {
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
    if($running != $time)
    {
        //index.php refreshed by user
        die();
    }
    session_write_close();
    return true;//continue running
}

require_once '../whatsprot.class.php';
$target = @$_POST["target"];
$username = "************";
$password = "******************************";
$w = new WhatsProt($username, 0, "WhatsApi AJAX Demo", true);
$w->Connect();
$w->LoginWithPassword($password);

$initial = @$_POST["initial"];
if($initial == "true" && $target != null)
{
    //request contact picture only on first call
    $w->GetProfilePicture($target);
    //finally starting to use the event manager!
    $w->eventManager()->bind("onProfilePicture", "onProfilePicture");
}
//subscribe contact status
//$w->SendPresenceSubscription($target);
//TODO: presense handling (online/offline/typing/last seen)

while(running($time))
{
    $w->PollMessages();
    
    running($time);//check again if timestamp has been updated

    //check for outbound messages to send:
    session_start();
    $outbound = $_SESSION["outbound"];
    $_SESSION["outbound"] = array();
    session_write_close();
    if(count($outbound) > 0)
    {
        foreach($outbound as $message)
        {
            //send messages
            $w->Message($message["target"], $message["body"]);
            $w->PollMessages();
        }
    }
    
    //check for received messages:
    $messages = $w->GetMessages();
    if(count($messages) > 0)
    {
        session_start();
        $inbound = $_SESSION["inbound"];
        $_SESSION["inbound"] = array();//lock
        foreach($messages as $message)
        {
            $data = $message->getChild("body")->_data;
            if($data != null && $data != '')
            {
                $inbound[] = $data;
            }
        }
        $_SESSION["inbound"] = $inbound;
        session_write_close();
    }
}