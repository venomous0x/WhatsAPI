<?php
require_once("whatsprot.class.php");
/**
 * Created by JetBrains PhpStorm.
 * User: Max
 * Date: 6-1-14
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 *
 * Usage:
 * $username = "";
 * 
 * $password = "";
 *
 * $contacts = array("", "", ""); // or read them from a file
 *  
 * $wbs = new WaBulkSender($username, $password);
 * $wbs->Login();
 * $wbs->SyncContacts($contacts);
 * $wbs->SendBulk($contacts, "bulk message");
 * or
 * $wbs->SendBroadcast($contacts, "Broadcast Message");
 */

class WaBulkSender
{
    protected $username;
    protected $password;
    protected $nickname;
    /** @var @var $wa WhatsProt */
    protected $wa;
    protected static $sendLock = false;

    /**
     * @param string $username
     * @param string $password
     * @param string $nickname
     * @param bool $debug
     */
    public function __construct($username, $password, $nickname = "WhatsApp", $debug = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->wa = new WhatsProt($username, null, $nickname, $debug);
        $this->bindEvents();
    }

    protected function bindEvents()
    {
        $this->wa->eventManager()->bind("onLoginFailed", "WaBulkSender::event_onLoginFailed");
        $this->wa->eventManager()->bind("onLogin", "WaBulkSender::event_onLogin");
        $this->wa->eventManager()->bind("onConnect", "WaBulkSender::event_onConnect");
        $this->wa->eventManager()->bind("onMessageReceivedServer", "WaBulkSender::event_onMessageReceivedServer");
    }

    public function Login()
    {
        echo "Connecting... ";
        $this->wa->connect();
        echo "Logging in... ";
        $this->wa->loginWithPassword($this->password);
        $this->wa->sendClientConfig();
        $this->wa->sendGetServerProperties();
        $this->wa->pollMessages();
        echo "Ready for work!<br />";
    }

    /**
     * @param string $number
     * @param string $reason
     */
    public static function event_onLoginFailed($number, $reason)
    {
        die("login failed for $number; Reason: $reason");
    }

    /**
     * @param string $number
     */
    public static function event_onLogin($number)
    {
        echo "logged in as $number<br />";
    }

    /**
     * @param string $number
     * @param $socket
     */
    public static function event_onConnect($number, $socket)
    {
        echo "connected to WhatsApp<br />";
    }

    public static function event_onMessageReceivedServer($mynumber, $from, $id, $type)
    {
        if($from != "broadcast")
        {
            //unlock
            echo "$type with id $id from $mynumber to $from received by server<br />";
            static::$sendLock = false;
        }
    }

    /**
     * @param string[] $contacts
     */
    public function SyncContacts($contacts)
    {
		$this->wa->sendSync($contacts);
		echo "Synced " . count($contacts) . " contacts<br />";
    }

    /**
     * @param string[] $targets
     * @param string $message
     */
    public function SendBroadcast($targets, $message)
    {
        if(count($targets) > 25)
        {
            echo "Error: too many broadcast targets (" . count($targets) . ")<br />";
            return;
        }
        echo "Sending broadcast... ";
        $this->wa->sendBroadcastMessage($targets, $message);
        $this->wa->pollMessages();
        echo "done!<br />";
    }

    /**
     * @param string[] $targets
     * @param $message
     */
    public function SendBulk($targets, $message)
    {
        echo "Sending " . count($targets) . " bulk messages...<br />";
        foreach($targets as $target)
        {
            $this->wa->sendPresenceSubscription($target);
            $this->wa->pollMessages();
            $this->wa->sendMessageComposing($target);
            sleep(1);
            $this->wa->pollMessages();
            $this->wa->sendMessagePaused($target);
            static::$sendLock = true;
            echo "Sending message from " . $this->username . " to $target... ";
            $this->wa->sendMessage($target, $message);
            while(static::$sendLock)
            {
                //wait for server receipt
                sleep(1);
            }
        }
        echo "Finished sending bulk<br />";
    }
}
