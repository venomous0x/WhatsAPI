<?php
require "protocol.class.php";
require "../func.php";
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

    protected $_socket;
    protected $_writer;
    protected $_reader;
	
    function __construct($Number, $imei, $Nickname)
    {
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
        return $this->_writer->write($parent);
    }

    protected function addAuth()
    {
        $authHash = array();
        $authHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $authHash["mechanism"] = "DIGEST-MD5-1";
        $node = new ProtocolNode("auth", $authHash, NULL, "");
        return $this->_writer->write($node);
    }
    
    protected function encryptPassword()
    {
        return md5(strrev($this->_imei));
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
        return $this->_writer->write($node);        
    }

	protected function send($data)
    {
		socket_send( $this->_socket, $data, strlen($data), 0 );
	}	

    protected function read()
    {
        $buff = $this->_incomplete_message . socket_read( $this->_socket, 1024 );
        $this->_incomplete_message = "";
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
    
    protected function processInboundData($data)
    {
        try
        {
            $node = $this->_reader->nextTree($data);
            while ($node != null)
            {
                print($node->NodeString("") . "\n");
                if (strcmp($node->_tag, "challenge") == 0)
                {
                    $this->processChallenge($node);
                }
                else if (strcmp($node->_tag, "success") == 0)
                {
                    $this->_loginStatus = $this->_connectedStatus;
                }
                $node = $this->_reader->nextTree();
            }
        }
        catch (IncompleteMessageException $e)
        {
            $this->_incomplete_message = $e->getInput();
            printf("IncompleteMessageException\n");
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
        $data .= $this->addFeatures();
        $data .= $this->addAuth();
        $this->send($data);

        $this->processInboundData($this->read());
        $data = $this->addAuthResponse();
        $this->send($data);
        $cnt = 0;
        do
        {
            $this->processInboundData($this->read());
        } while (($cnt++ < 100) && (strcmp($this->_loginStatus, $this->_disconnectedStatus) == 0));
    }

    public function PollMessages()
    {
        $this->processInboundData($this->read());
    }
}

?>
