<?php
require 'protocol.class.php';
require 'func.php';
require 'rc4.php';

class WhatsProt
{
    protected $_phoneNumber;
    protected $_imei;
    protected $_password;
    protected $_name;

    protected $_whatsAppHost = 'c.whatsapp.net';
    protected $_whatsAppServer = 's.whatsapp.net';
    protected $_whatsAppGroupServer = "g.us";
    protected $_device = 'iPhone';
    protected $_whatsAppVer = '2.8.7';
    protected $_port = 5222;
    protected $_timeout = array('sec' => 2, 'usec' => 0);
    protected $_incomplete_message = '';

    protected $_whatsAppReqHost = 'v.whatsapp.net/v2/code';
    protected $_whatsAppRegHost = 'v.whatsapp.net/v2/register';
    protected $_whatsAppCheHost = 'v.whatsapp.net/v2/exist';

    protected $_whatsAppUserAgent = 'WhatsApp/2.3.53 S40Version/14.26 Device/Nokia302';
    protected $_whatsAppToken = 'PdA2DJyKoUrwLw1Bg6EIhzh502dF9noR9uFCllGk1354754753509';

    protected $_disconnectedStatus = 'disconnected';
    protected $_connectedStatus = 'connected';
    protected $_loginStatus;
    protected $_accountinfo;

    protected $_messageQueue = array();
    protected $_outQueue = array();
    protected $_lastId = FALSE;
    protected $_msgCounter = 1;
    protected $_socket;
    protected $_writer;
    protected $_reader;

    protected $_inputKey;
    protected $_outputKey;

    protected $_debug;

    protected $_newmsgBind = FALSE;

    public function __construct($Number, $imei, $Nickname, $debug = FALSE)
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
        $authHash["mechanism"] = "WAUTH-1";
        $authHash["user"] = $this->_phoneNumber;
        $node = new ProtocolNode("auth", $authHash, NULL, "");

        return $node;
    }

    public function encryptPassword()
    {
        return base64_decode($this->_password);
    }

    protected function authenticate()
    {
        $key = pbkdf2('sha1', $this->encryptPassword(), $this->challengeData, 16, 20, TRUE);
        $this->_inputKey = new KeyStream($key);
        $this->_outputKey = new KeyStream($key);
        $array = $this->_phoneNumber . $this->challengeData . time();
        $response = $this->_outputKey->encode($array, 0, strlen($array), FALSE);

        return $response;
    }

    public function setNewMessageBind($bind)
    {
        $this->_newmsgBind = $bind;
    }

    public function addOutQueue($node)
    {
        $this->_outQueue[] = $node;
    }

    protected function addAuthResponse()
    {
        $resp = $this->authenticate();
        $respHash = array();
        $respHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $node = new ProtocolNode("response", $respHash, NULL, $resp);

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
        $buff = '';
        $ret = socket_read( $this->_socket, 1024 );
        if ($ret) {
            $buff = $this->_incomplete_message . $ret;
            $this->_incomplete_message = '';
        }

        return $buff;
    }

    protected function processChallenge($node)
    {
        $this->challengeData = $node->_data;
    }

    protected function sendMessageReceived($msg)
    {
        $requestNode = $msg->getChild("request");
        $receivedNode = $msg->getChild("received");
        if ($requestNode != NULL || $receivedNode != NULL) {
            $recievedHash = array();
            $recievedHash["xmlns"] = "urn:xmpp:receipts";
            $receivedNode = new ProtocolNode("received", $recievedHash, NULL, "");

            $messageHash = array();
            $messageHash["to"] = $msg->getAttribute("from");
            $messageHash["type"] = "chat";
            $messageHash["id"] = $msg->getAttribute("id");
            $messageHash["t"] = time();
            $messageNode = new ProtocolNode("message", $messageHash, array($receivedNode), "");
            $this->sendNode($messageNode);
        }
    }

    protected function processInboundData($data)
    {
        try {
            $node = $this->_reader->nextTree($data);
            while ($node != NULL) {
                $this->DebugPrint($node->NodeString("rx  ") . "\n");
                if (strcmp($node->_tag, "challenge") == 0) {
                    $this->processChallenge($node);
                } elseif (strcmp($node->_tag, "success") == 0) {
                    $this->_loginStatus = $this->_connectedStatus;
                    $this->_accountinfo = array(
                        'status' => $node->getAttribute('status'),
                        'kind' => $node->getAttribute('kind'),
                        'creation' => $node->getAttribute('creation'),
                        'expiration' => $node->getAttribute('expiration'),
                    );
                }
                if (strcmp($node->_tag, "message") == 0) {
                    array_push($this->_messageQueue, $node);
                    $this->sendMessageReceived($node);
                    if ($node->hasChild('x') && $this->_lastId == $node->getAttribute('id')) {
                        $this->sendNext();
                    }
                    if ($this->_newmsgBind && $node->getChild('body')) {
                        $this->_newmsgBind->process($node);
                    }
                }
                if (strcmp($node->_tag, "iq") == 0 && strcmp($node->_attributeHash['type'], "get") == 0 && strcmp($node->_children[0]->_tag, "ping") == 0) {
                    $this->Pong($node->_attributeHash['id']);
                }
                if (strcmp($node->_tag, "iq") == 0 && strcmp($node->_attributeHash['type'], "result") == 0 && strcmp($node->_children[0]->_tag, "query") == 0) {
                    array_push($this->_messageQueue, $node);
                }
                $node = $this->_reader->nextTree();
            }
        } catch (IncompleteMessageException $e) {
            $this->_incomplete_message = $e->getInput();
        }
    }

    public function sendNext()
    {
        if (count($this->_outQueue)>0) {
            $msgnode = array_shift($this->_outQueue);
            $msgnode->refreshTimes();
            $this->_lastId = $msgnode->getAttribute('id');
            $this->sendNode($msgnode);
        } else {
            $this->_lastId = FALSE;
        }
    }

    public function sendComposing($msg)
    {
        $comphash = array();
        $comphash['xmlns'] = 'http://jabber.org/protocol/chatstates';
        $compose = new ProtocolNode("composing", $comphash, NULL, "");
        $messageHash = array();
        $messageHash["to"] = $msg->getAttribute("from");
        $messageHash["type"] = "chat";
        $messageHash["id"] = time().'-'.$this->_msgCounter;
        $messageHash["t"] = time();
        $this->_msgCounter++;
        $messageNode = new ProtocolNode("message", $messageHash, array($compose), "");
        $this->sendNode($messageNode);
    }

    public function accountInfo()
    {
        if (is_array($this->_accountinfo)) {
            print_r($this->_accountinfo);
        } else {
            echo "No information available";
        }
    }

    public function Connect()
    {
        $Socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($Socket, $this->_whatsAppHost, $this->_port);
        $this->_socket = $Socket;
        socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, $this->_timeout);
    }

    public function Login()
    {
        $credentials = $this->checkCredentials();
        if ($credentials->status == 'ok') {
            $this->_password($credentials->pw);
        }
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
        $this->_reader->setKey($this->_inputKey);
        $this->_writer->setKey($this->_outputKey);
        $cnt = 0;
        do {
            $this->processInboundData($this->readData());
        } while (($cnt++ < 100) && (strcmp($this->_loginStatus, $this->_disconnectedStatus) == 0));
        $this->sendNickname();
        $this->SendPresence();
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

    public function WaitforReceipt()
    {
        $received = FALSE;
        do {
            $this->PollMessages();
            $msgs = $this->GetMessages();
            foreach ($msgs as $m) {
                # process inbound messages
                if ($m->_tag == "message") {
                    if ($m->getChild('received') != NULL) {
                        $received = TRUE;
                    }
                }
                //print($m->NodeString("") . "\n");
            }
        } while (!$received);
        //echo "Received node!!\n";
    }

    public function SendPresence($type="available")
    {
        $presence = array();
        $presence['type'] = $type;
        $presence['name'] = $this->_name;
        $node = new ProtocolNode("presence", $presence, NULL, "");
        $this->sendNode($node);
    }

    protected function SendMessageNode($to, $node)
    {
        $serverNode = new ProtocolNode("server", NULL, NULL, "");
        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");
        $notify = array();
        $notify['xmlns'] = 'urn:xmpp:whatsapp';
        $notify['name'] = $this->_name;
        $notnode = new ProtocolNode("notify", $notify, NULL, "");
        $request = array();
        $request['xmlns'] = "urn:xmpp:receipts";
        $reqnode = new ProtocolNode("request", $request, NULL, "");
        $msgid = time().'-'.$this->_msgCounter;

	$whatsAppServer = $this->whatsAppServer;
        if (strpos($to, "-") !== FALSE) {
            $whatsAppServer = $this->_whatsAppGroupServer;
        }
        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $msgid;
        $messageHash["t"] = time();
        $this->_msgCounter++;
        $messsageNode = new ProtocolNode("message", $messageHash, array($xNode, $notnode,$reqnode,$node), "");
        if (!$this->_lastId) {
            $this->_lastId = $msgid;
            $this->sendNode($messsageNode);
        }else
            $this->_outQueue[] = $messsageNode;
    }

    public function Message($to, $txt)
    {
        $bodyNode = new ProtocolNode("body", NULL, NULL, $txt);
        $this->SendMessageNode($to, $bodyNode);
    }

    public function MessageImage($to, $url, $file, $size, $icon)
    {
        $mediaAttribs = array();
        $mediaAttribs["xmlns"] = "urn:xmpp:whatsapp:mms";
        $mediaAttribs["type"] = "image";
        $mediaAttribs["url"] = $url;
        $mediaAttribs["file"] = $file;
        $mediaAttribs["size"] = $size;

        $mediaNode = new ProtocolNode("media", $mediaAttribs, NULL, $icon);
        $this->SendMessageNode($to, $mediaNode);
    }

    public function Location($msgid, $to, $long, $lat)
    {
        $whatsAppServer = $this->_whatsAppServer;

        $mediaHash = array();
        $mediaHash['type'] = "location";
        $mediaHash['longitude'] = $long;
        $mediaHash['latitude'] = $lat;
        $mediaHash['xmlns'] = "urn:xmpp:whatsapp:mms";
        $mediaNode = new ProtocolNode("media", $mediaHash, NULL, NULL);

        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $msgid;
        $messageHash["author"] = $this->_phoneNumber . "@" . $this->_whatsAppServer;

        $messsageNode = new ProtocolNode("message", $messageHash, array($mediaNode), "");
        $this->sendNode($messsageNode);
    }

    public function sendStatusUpdate($msgid, $txt)
    {
        $bodyNode = new ProtocolNode("body", NULL, NULL, $txt);
        $serverNode = new ProtocolNode("server", NULL, NULL, "");
        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");
        $messageHash = array();
        $messageHash["to"] = 's.us';
        $messageHash["type"] = "chat";
        $messageHash["id"] = $msgid;
        $messsageNode = new ProtocolNode("message", $messageHash, array($xNode, $bodyNode), "");
        $this->sendNode($messsageNode);
    }

    public function Pong($msgid)
    {
        $whatsAppServer = $this->_whatsAppServer;

        $messageHash = array();
        $messageHash["to"] = $whatsAppServer;
        $messageHash["id"] = $msgid;
        $messageHash["type"] = "result";

        $messsageNode = new ProtocolNode("iq", $messageHash, NULL, "");
        $this->sendNode($messsageNode);
    }

    public function sendNickname()
    {
        $messageHash = array();
        $messageHash["name"] = $this->_name;
        $messsageNode = new ProtocolNode("presence", $messageHash, NULL, "");
        $this->sendNode($messsageNode);
    }

    protected function DebugPrint($debugMsg)
    {
        if ($this->_debug) {
            print($debugMsg);
        }
    }

    public function RequestLastSeen($msgid, $to)
    {

        $whatsAppServer = $this->_whatsAppServer;

        $queryHash = array();
        $queryHash['xmlns'] = "jabber:iq:last";
        $queryNode = new ProtocolNode("query", $queryHash, NULL, NULL);

        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "get";
        $messageHash["id"] = $msgid;
        $messageHash["from"] = $this->_phoneNumber . "@" . $this->_whatsAppServer;

        $messsageNode = new ProtocolNode("iq", $messageHash, array($queryNode), "");
        $this->sendNode($messsageNode);
    }

    /**
     * Request a registration code from WhatsApp.
     */
    public function requestCode($method = 'sms', $countryCody = 'US', $langCode = 'en')
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The prived phone number is not valid.');
        }

        // Build the token.
        $token = md5($this->_whatsAppToken . $phone['phone']);

        // Build the url.
        $host = 'https://' . $this->_whatsAppReqHost;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'lc' => $countryCode,
            'lg' => $langCode,
            'mcc' => '000',
            'mnc' => '000',
            'method' => $method,
            'id' => $this->_identity,
            'token' => $token,
            'c' => 'cookie',
        );

        $rest = $this->getResponse($host, $query);

        if ($rest->status != 'sent') {
            throw new Exception('There was a problem trying to request the code..');
        } else {
            return $rest;
        }
    }

    /*
     * Register account on WhatsApp using the provided code.
     */
    public function registerCode($code)
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The prived phone number is not valid.');
        }

        // Build the url.
        $host = 'https://' . $this->_whatsAppRegHost;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'id' => $this->_identity,
            'code' => $code,
            'c' => 'cookie',
        );

        $rest = $this->getResponse($host, $query);

        if ($rest->status != 'ok') {
            throw new Exception('An error occurred registering the registration code from WhatsApp.');
        }

        return $rest;
    }

    /*
     * Check if account credentials are valid.
     */
    public function checkCredentials()
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The prived phone number is not valid.');
        }

        // Build the url.
        $host = 'https://' . $this->whatsAppCheHost;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'id' => $this->_identity,
            'c' => 'cookie',
        );

        return $this->getResponse($host, $query);
    }

    protected function getResponse($host, $query)
    {
        // Build the url.
        $url = $host . '?';
        foreach ($query as $key => $value) {
          $url .= $key . '=' . $value . '&';
        }
        rtrim($url, '&');

        // Open connection.
        $ch = curl_init();

        // Configure the connection.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_whatsAppUserAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/json'));
        // This makes CURL accept any peer!
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // Get the response.
        $response = curl_exec($ch);

        // Close the connection.
        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Dissect country code from phone number.
     */
    public function dissectPhone()
    {
        if (($handle = fopen('countries.csv', 'rb')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000)) !== FALSE) {
                if (strpos($this->_phoneNumber, $data[1]) === 0) {
                    // Return the first appearance.
                    fclose($handle);

                    return array(
                        'cc' => $data[1],
                        'phone' => substr($this->_phoneNumber, strlen($data[1]), strlen($this->_phoneNumber)),
                    );
                }
            }
            fclose($handle);
        }

        return FALSE;
    }
}
