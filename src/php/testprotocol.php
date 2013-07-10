<?php
require_once 'whatsprot.class.php';
# phone number, IMEI, and name, the IMEI.
$options = getopt("d::", array("debug::"));
$debug = (array_key_exists("debug", $options) || array_key_exists("d", $options)) ? true : false;

$username = "**your phone number**";
$identity = "**your IMEI code**";
$password = "**server generated whatsapp password**";
$nickname = "**your nickname**";
$target = "**contact's phone number**";

$w = new WhatsProt($username, $identity, $nickname, $debug);
$w->connect();
# Now LoginWithPassword function sends Nickname and (Available) Presence
$w->loginWithPassword($password);

//retrieve large profile picture/ output is in /src/php/pictures/
$w->sendGetProfilePicture($target, true);

//update your profile picture
$w->sendSetProfilePicture("demo/venom.jpg");

//send picture
$w->sendMessageImage($target, "demo/x3.jpg");

# Implemented out queue messages and auto msgid
$w->sendMessage($target, "Sent from WhatsApi at " . $time());

# You can create a ProcessNode class (or whatever name you want) that has a process($node) function
# and pass it through setNewMessageBind, that way everytime the class receives a text message it will run
# the process function to it.
$pn = new ProcessNode($w, $target);
$w->setNewMessageBind($pn);

while (1) {
    $w->pollMessages();
    $msgs = $w->getMessages();
    foreach ($msgs as $m) {
        # process inbound messages
        //print($m->NodeString("") . "\n");
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
        # Example of process function, you have to guess a number (psss it's 5)
        # If you guess it right you get a gift
        $text = $node->getChild('body');
        $text = $text->data;
        if ($text && ($text == "5" || trim($text) == "5")) {
            $iconfile = "../../tests/Gift.jpgb64";
            $fp = fopen($iconfile, "r");
            $icon = fread($fp, filesize($iconfile));
            fclose($fp);
            $this->wp->sendMessageImage($this->target, "https://mms604.whatsapp.net/d11/26/09/8/5/85a13e7812a5e7ad1f8071319d9d1b43.jpg", "hero.jpg", 84712, $icon);
            $this->wp->sendMessage($this->target, "¡Congratulations you guessed the right number!");
        } else {
            $this->wp->sendMessage($this->target, "¡I'm sorry, try again!");
        }
    }

}
