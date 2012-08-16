<?php
require "protocol.class.php";
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

    protected $_socket;
    protected $_writer;
    protected $_reader;
	
    function __construct($Number, $Password, $Nickname)
    {
        $dict = getDictionary();
        $this->_writer = new BinTreeNodeWriter($dict);
        $this->_reader = new BinTreeNodeReader($dict);
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
	protected function send($data){
		socket_send( $this->_socket, $data, strlen($data), 0 );
	}	

    protected function read()
    {
        $buff = $this->_incomplete_message . socket_read( $this->_socket, 1024 );
        $this->_incomplete_message = "";
        return $buff;
    }
    
    protected function processInboundData($data)
    {
        printhexstr($data, "data");
        $node = $this->_reader->nextTree($data);
        print($node->NodeString("") . "\n");
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
        $this->processInboundData($this->read($data));
        printhexstr($data, "data");
    }
}

?>
