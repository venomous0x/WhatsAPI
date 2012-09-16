<?php
require "protocol.class.php";
require "func.php";
class WhatsProt 
{
    protected $_phoneNumber;
    protected $_imei;
    protected $_name;

    protected $_whatsAppHost = "bin-short.whatsapp.net";
    protected $_whatsAppServer = "s.whatsapp.net";
    protected $_whatsAppRealm = "s.whatsapp.net";
    protected $_whatsAppDigest = "xmpp/s.whatsapp.net";
    protected $_device = "iPhone";
    protected $_whatsAppVer = "2.8.2";
    protected $_port = 5222;
    protected $_timeout = array("sec" => 2, "usec" => 0);
    protected $_incomplete_message = "";

    protected $_disconnectedStatus = "disconnected";
    protected $_connectedStatus = "connected";
    protected $_loginStatus;
    protected $_accountinfo;

    protected $_messageQueue = array();

    protected $_socket;
    protected $_writer;
    protected $_reader;

    protected $_debug;
	
    function __construct($Number, $imei, $Nickname, $debug = false)
    {
        $this->_debug = $debug;
        $dict = getDictionary();
        $this->_writer = new BinTreeNodeWriter($dict);
        $this->_reader = new BinTreeNodeReader($dict);
        $this->_phoneNumber = $Number;
        $this->_imei = $imei;
        $this->_name = $Nickname;
        $this->_loginStatus = $this->_disconnectedStatus;
    }
    
    protected function addFeatures()
    {
        $child = new ProtocolNode("receipt_acks", NULL, NULL, "");
        $parent = new ProtocolNode("stream:features", NULL, array($child), "");
        return $parent;
    }

    protected function addAuth()
    {
        $authHash = array();
        $authHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $authHash["mechanism"] = "DIGEST-MD5-1";
        $node = new ProtocolNode("auth", $authHash, NULL, "");
        return $node;
    }
    
    public function encryptPassword()
    {
    	if(stripos($this->_imei, ":") !== false){
    		$this->_imei = strtoupper($this->_imei);
    		return md5($this->_imei.$this->_imei);
    	}
        else {
        	return md5(strrev($this->_imei));
        }
    }

    protected function authenticate($nonce)
    {
        $NC = "00000001";
        $qop = "auth";
        $cnonce = random_uuid();
        $data1 = $this->_phoneNumber;
        $data1 .= ":";
        $data1 .= $this->_whatsAppServer;
        $data1 .= ":";
        $data1 .= $this->EncryptPassword();

        $data2 = pack('H32', md5($data1));
        $data2 .= ":";
        $data2 .= $nonce;
        $data2 .= ":";
        $data2 .= $cnonce;

        $data3 = "AUTHENTICATE:";
        $data3 .= $this->_whatsAppDigest;

        $data4 = md5($data2);
        $data4 .= ":";
        $data4 .= $nonce;
        $data4 .= ":";
        $data4 .= $NC;
        $data4 .= ":";
        $data4 .= $cnonce;
        $data4 .= ":";
        $data4 .= $qop;
        $data4 .= ":";
        $data4 .= md5($data3);

        $data5 = md5($data4);
		$response = sprintf('username="%s",realm="%s",nonce="%s",cnonce="%s",nc=%s,qop=%s,digest-uri="%s",response=%s,charset=utf-8', 
            $this->_phoneNumber, 
            $this->_whatsAppRealm, 
            $nonce, 
            $cnonce, 
            $NC, 
            $qop, 
            $this->_whatsAppDigest, 
            $data5);
        return $response;
    }

    protected function addAuthResponse()
    {
        $resp = $this->authenticate($this->challengeArray["nonce"]);
        $respHash = array();
        $respHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $node = new ProtocolNode("response", $respHash, NULL, base64_encode($resp));
        return $node;
    }

	protected function sendData($data)
    {
		socket_send( $this->_socket, $data, strlen($data), 0 );
	}	
    
    protected function sendNode($node)
    {
        $this->DebugPrint($node->NodeString("tx  ") . "\n");
        $this->sendData($this->_writer->write($node));
    }

    protected function readData()
    {
        $buff = "";
        $ret = socket_read( $this->_socket, 1024 );
        if ($ret)
        {
            $buff = $this->_incomplete_message . $ret;
            $this->_incomplete_message = "";
        }
        return $buff;
    }
    
    protected function processChallenge($node)
    {
        $challenge = base64_decode($node->_data);
        $challengeStrs = explode(",", $challenge);
        $this->challengeArray = array();
        foreach ($challengeStrs as $c)
        {
            $d = explode("=", $c);
            $this->challengeArray[$d[0]] = str_replace("\"", "", $d[1]);
        }
    }
    
    protected function sendMessageReceived($msg)
    {
        $requestNode = $msg->getChild("request");
        if ($requestNode != null)
        {
            $xmlnsAttrib = $requestNode->getAttribute("xmlns");
            if (strcmp($xmlnsAttrib, "urn:xmpp:receipts") == 0)
            {
                $recievedHash = array();
                $recievedHash["xmlns"] = "urn:xmpp:receipts";
                $receivedNode = new ProtocolNode("received", $recievedHash, null, "");

                $messageHash = array();
                $messageHash["to"] = $msg->getAttribute("from");
                $messageHash["type"] = "chat";
                $messageHash["id"] = $msg->getAttribute("id");
                $messageNode = new ProtocolNode("message", $messageHash, array($receivedNode), "");
                $this->sendNode($messageNode);
            }
        }
    }

    protected function processInboundData($data)
    {
        try
        {
            $node = $this->_reader->nextTree($data);
            while ($node != null)
            {
                $this->DebugPrint($node->NodeString("rx  ") . "\n");
                if (strcmp($node->_tag, "challenge") == 0)
                {
                    $this->processChallenge($node);
                }
                else if (strcmp($node->_tag, "success") == 0)
                {
                    $this->_loginStatus = $this->_connectedStatus;
                    $this->_accountinfo = array('status'=>$node->getAttribute('status'),'kind'=>$node->getAttribute('kind'),'creation'=>$node->getAttribute('creation'),'expiration'=>$node->getAttribute('expiration'));
                }
                if (strcmp($node->_tag, "message") == 0)
                {
                    array_push($this->_messageQueue, $node);
                    $this->sendMessageReceived($node);
                }
                if (strcmp($node->_tag, "iq") == 0 AND strcmp($node->_attributeHash['type'], "get") == 0 AND strcmp($node->_children[0]->_tag, "ping") == 0)
                {
                    $this->Pong($node->_attributeHash['id']);
                }

                $node = $this->_reader->nextTree();
            }
        }
        catch (IncompleteMessageException $e)
        {
            $this->_incomplete_message = $e->getInput();
        }
    }

    public function accountInfo(){
    	if(is_array($this->_accountinfo)){
    		print_r($this->_accountinfo);
    	}
    	else{
    		echo "No information available";
    	}
    }
    
    public function Connect(){ 
        $Socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
        socket_connect( $Socket, $this->_whatsAppHost, $this->_port );
        $this->_socket = $Socket;
        socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, $this->_timeout);
    }

    public function Login()
    {
        $resource = "$this->_device-$this->_whatsAppVer-$this->_port";
        $data = $this->_writer->StartStream($this->_whatsAppServer, $resource);
        $feat = $this->addFeatures();
        $auth = $this->addAuth();
        $this->sendData($data);
        $this->sendNode($feat);
        $this->sendNode($auth);

        $this->processInboundData($this->readData());
        $data = $this->addAuthResponse();
        $this->sendNode($data);
        $cnt = 0;
        do
        {
            $this->processInboundData($this->readData());
        } while (($cnt++ < 100) && (strcmp($this->_loginStatus, $this->_disconnectedStatus) == 0));
    }

    # Pull from the socket, and place incoming messages in the message queue
    public function PollMessages()
    {
        $this->processInboundData($this->readData());
    }
    
    # Drain the message queue for application processing
    public function GetMessages()
    {
        $ret = $this->_messageQueue;
        $this->_messageQueue = array();
        return $ret;
    }

    protected function SendMessageNode($msgid, $to, $node)
    {
        $serverNode = new ProtocolNode("server", null, null, "");

        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");

        $messageHash = array();
        $messageHash["to"] = $to . "@" . $this->_whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $msgid;
        $messsageNode = new ProtocolNode("message", $messageHash, array($xNode, $node), "");
        $this->sendNode($messsageNode);
    }

    public function Message($msgid, $to, $txt)
    {
        $bodyNode = new ProtocolNode("body", null, null, $txt);
        $this->SendMessageNode($msgid, $to, $bodyNode);
    }

    public function MessageImage($msgid, $to, $url, $file, $size, $icon)
    {
        $mediaAttribs = array();
        $mediaAttribs["xmlns"] = "urn:xmpp:whatsapp:mms";
        $mediaAttribs["type"] = "image";
        $mediaAttribs["url"] = $url;
        $mediaAttribs["file"] = $file;
        $mediaAttribs["size"] = $size;

        $mediaNode = new ProtocolNode("media", $mediaAttribs, null, $icon);
        $this->SendMessageNode($msgid, $to, $mediaNode);
    }
    
    public function Pong($msgid)
    {
        $whatsAppServer = $this->_whatsAppServer;

        $messageHash = array();
        $messageHash["to"] = $whatsAppServer;
        $messageHash["id"] = $msgid;
        $messageHash["type"] = "result";
       
       	$messsageNode = new ProtocolNode("iq", $messageHash, null, "");
	$this->sendNode($messsageNode);
    }

    protected function DebugPrint($debugMsg)
    {
        if ($this->_debug)
        {
            print($debugMsg);
        }
    }
    /**
	 * TODO
     */
    public function RequestLastSeen($var){
    	return null;
    }
}

?>
