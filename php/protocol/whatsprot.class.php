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

    protected function addAuthResponse()
    {

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
        printhexstr($data, "data");
        $node = $this->_reader->nextTree($data);
        while ($node != null)
        {
            print($node->NodeString("") . "\n");
            if (strcmp($node->_tag, "challenge") == 0)
            {
                $this->processChallenge($node);
            }
            $node = $this->_reader->nextTree();
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
        $input = "\x00\x05\xf8\x03\x01\x38\x8a\x00\x08\xf8\x02\x96\xf8\x01\xf8\x01\x7e\x00\x5f\xf8\x04\x1a\xbd\xa7\xfc\x58\x62\x6d\x39\x75\x59\x32\x55\x39\x49\x6a\x63\x31\x4e\x7a\x4d\x78\x4f\x44\x45\x34\x4d\x44\x45\x33\x4e\x79\x49\x73\x63\x57\x39\x77\x50\x53\x4a\x68\x64\x58\x52\x6f\x49\x69\x78\x6a\x61\x47\x46\x79\x63\x32\x56\x30\x50\x58\x56\x30\x5a\x69\x30\x34\x4c\x47\x46\x73\x5a\x32\x39\x79\x61\x58\x52\x6f\x62\x54\x31\x74\x5a\x44\x55\x74\x63\x32\x56\x7a\x63\x77\x3d\x3d";

        $this->processInboundData($input);
        #$this->processInboundData($this->read($data));
    }
}

?>
