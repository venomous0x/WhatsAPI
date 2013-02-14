<?php
require 'whatsprot.class.php';
# phone number, IMEI, and name, the IMEI.
$options = getopt("d::", array("debug::"));
$debug = (array_key_exists("debug", $options) || array_key_exists("d", $options)) ? TRUE : FALSE;
# Target phone number
$target = "**********";

$w = new WhatsProt("************", "********", "John Doe", true);
$w->Connect();
# Now Login function sends Nickname and (Available) Presence
$w->Login();
# Implemented out queue messages and auto msgid
$w->Message($target, "1");
$w->Message($target, "2");
$w->Message($target, "3");
$w->Message($target, "4");
$w->Message($target, "5");
# You can create a ProcessNode class (or whatever name you want) that has a process($node) function
# and pass it through setNewMessageBind, that way everytime the class receives a text message it will run
# the process function to it.
$pn = new ProcessNode($w,$target);
$w->setNewMessageBind($pn);

while (1) {
    $w->PollMessages();
    $msgs = $w->GetMessages();
    foreach ($msgs as $m) {
        # process inbound messages
        //print($m->NodeString("") . "\n");
    }
}

class ProcessNode
{
    protected $_wp = false;
    protected $_target = false;
    public function __construct($wp,$target)
    {
        $this->_wp = $wp;
        $this->_target = $target;
    }
    public function process($node)
    {
        # Example of process function, you have to guess a number (psss it's 5)
        # If you guess it right you get a gift
        $text = $node->getChild('body');
        $text = $text->_data;
        if ($text && ($text == "5" || trim($text)=="5")) {
            $iconfile = "../../tests/Gift.jpgb64";
            $fp = fopen($iconfile, "r");
            $icon = fread($fp, filesize($iconfile));
            fclose($fp);
            $this->_wp->MessageImage($this->_target, "https://mms604.whatsapp.net/d11/26/09/8/5/85a13e7812a5e7ad1f8071319d9d1b43.jpg", "hero.jpg", 84712, $icon);
            $this->_wp->Message($this->_target, "¡Congratulations you guessed the right number!");
        } else {
            $this->_wp->Message($this->_target, "¡I'm sorry, try again!");
        }
    }

}
