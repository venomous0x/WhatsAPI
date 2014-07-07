<?php
require_once '../src/whatsprot.class.php';
//Change the time zone if you are in a different country/time zone
date_default_timezone_set('Europe/Madrid');

echo "####################################\n";
echo "#                                  #\n";
echo "#           WA CLI CLIENT          #\n";
echo "#                                  #\n";
echo "####################################\n\n";
echo "====================================\n";

////////////////CONFIGURATION///////////////////////
////////////////////////////////////////////////////
$username = "";                      
$password = "";
$identity = "";     
$nickname = ""; 
$debug = false;                           
/////////////////////////////////////////////////////
if ($_SERVER['argv'][1] == null) {
    echo "USAGE: php ".$_SERVER['argv'][0]." <numero> \n\nEj: php client.php 34123456789\n\n";
    exit(1);
}
$target = $_SERVER['argv'][1];
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

// This function will print when the user goes online/offline
// It is required to send Presence Subscription First
function onPresenceReceived($username, $from, $type)
{
	$dFrom = str_replace(array("@s.whatsapp.net","@g.us"), "", $from);
		if($type == "available")
    		echo "<$dFrom is online>\n\n";
    	else
    		echo "<$dFrom is offline>\n\n";
}

echo "[] Logging in as '$nickname' ($username)\n";
$w = new WhatsProt($username, $identity, $nickname, false);

$w->eventManager()->bind("onPresence", "onPresenceReceived");

$w->connect(); // Connect to WA
$w->loginWithPassword($password); // Logging in with passwwd
echo "[*]Conectado a WhatsApp\n\n";
$w->sendClientConfig(); // Send client config to server
$w->sendGetServerProperties(); // receive server properties
$sync = array($target);
$w->sendSync($sync); // Send sync with target
$w->pollMessages(); // queue messages
$w->sendPresenceSubscription($target); // subscribe to target

$pn = new ProcessNode($w, $target);
$w->setNewMessageBind($pn);

    while (1) {
    $w->pollMessages();
    $msgs = $w->getMessages();
    foreach ($msgs as $m) {
        # process inbound messages
        //print($m->NodeString("") . "\n");
    }
        $line = fgets_u(STDIN);
        if ($line != "") {
            if (strrchr($line, " ")) {
                $command = trim(strstr($line, ' ', TRUE));
            } else {
                $command = $line;
            }
            switch ($command) {
                case "/query":
                    $dst = trim(strstr($line, ' ', FALSE));
                    echo "[] Interactive conversation with $contact:\n";
                    break;
                case "/lastseen":
                    echo "[] Last seen time of $target: ";
                    $w->sendGetRequestLastSeen($target);
                    break;
                default:
                    $w->sendMessage($target , $line);
                    break;
            }
        }
}

class ProcessNode
{
    protected $wp = false;
    protected $target = false;

    public function __construct($wp, $target)
    {
        $this->wp = $wp;
        $this->target = $target;
    }

    public function process($node)
    {
        $text = $node->getChild('body');
        $text = $text->getData(); // get text message
        $notify = $node->getAttribute("notify"); // get the name of target

		echo "\n- ".$notify.": ".$text."    ".date('H:i')."\n"; // print in screen

	}
}  
