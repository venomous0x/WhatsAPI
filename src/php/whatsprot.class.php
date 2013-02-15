<?php
require 'protocol.class.php';
require 'func.php';
require 'rc4.php';

class WhatsProt
{
    // The user phone number including the country code without '+' or '00'.
    protected $_phoneNumber;
    // The IMEI/MAC adress.
    protected $_imei;
    // The user password.
    protected $_password;
    // The user name.
    protected $_name;

    // The hostname of the whatsapp server.
    protected $_whatsAppHost = 'c.whatsapp.net';
    // The hostnames used to login/send messages.
    protected $_whatsAppServer = 's.whatsapp.net';
    protected $_whatsAppGroupServer = "g.us";
    // The device name.
    protected $_device = 'iPhone';
    // The WhatsApp version.
    protected $_whatsAppVer = '2.8.7';
    // The port of the whatsapp server.
    protected $_port = 5222;
    // The timeout for the connection with the Whatsapp servers.
    protected $_timeout = array('sec' => 2, 'usec' => 0);
    // A list of bytes for incomplete messages.
    protected $_incomplete_message = '';

    // The request code host.
    protected $_whatsAppReqHost = 'v.whatsapp.net/v2/code';
    // The register code host.
    protected $_whatsAppRegHost = 'v.whatsapp.net/v2/register';
    // The check credentials host.
    protected $_whatsAppCheHost = 'v.whatsapp.net/v2/exist';
    // User agent and token used in reques/registration code.
    protected $_whatsAppUserAgent = 'WhatsApp/2.3.53 S40Version/14.26 Device/Nokia302';
    protected $_whatsAppToken = 'PdA2DJyKoUrwLw1Bg6EIhzh502dF9noR9uFCllGk1354754753509';

    // The upload host.
    protected $_whatsAppUploadHost = 'https://mms.whatsapp.net/client/iphone/upload.php';

    // Describes the connection status with the whatsapp server.
    protected $_disconnectedStatus = 'disconnected';
    protected $_connectedStatus = 'connected';
    // Holds the login status.
    protected $_loginStatus;
    // The AccountInfo object.
    protected $_accountinfo;

    // Queue for received messages.
    protected $_messageQueue = array();
    // Queue for outgoing messages.
    protected $_outQueue = array();
    // Id to the last message sent.
    protected $_lastId = FALSE;
    // Message counter for auto-id.
    protected $_msgCounter = 1;
    // A socket to connect to the whatsapp network.
    protected $_socket;
    // An instance of the BinaryTreeNodeWriter class.
    protected $_writer;
    // An instance of the BinaryTreeNodeReader class.
    protected $_reader;

    // Instances of the KeyStream class.
    protected $_inputKey;
    protected $_outputKey;

    // Determines wether debug mode is on or off.
    protected $_debug;

    protected $_newmsgBind = FALSE;

    /**
     * Default class constructor.
     *
     * @param $Number
     *   The user phone number including the country code without '+' or '00'.
     * @param $imei
     *   The IMEI/MAC adress.
     * @param $Nickname
     *   The user name.
     * @param $debug
     *   Debug on or off, false by default.
     */
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

    /**
     * Add stream features.
     *
     * @return ProtocolNode
     *   Return itself.
     */
    protected function addFeatures()
    {
        $child = new ProtocolNode("receipt_acks", NULL, NULL, "");
        $parent = new ProtocolNode("stream:features", NULL, array($child), "");

        return $parent;
    }

    /**
     * Add the authenication nodes.
     *
     * @return ProtocolNode
     *   Return itself.
     */
    protected function addAuth()
    {
        $authHash = array();
        $authHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $authHash["mechanism"] = "WAUTH-1";
        $authHash["user"] = $this->_phoneNumber;
        $node = new ProtocolNode("auth", $authHash, NULL, "");

        return $node;
    }

    /**
     * Encrypt the password.
     *
     * @return string
     *   Return the encrypt password.
     */
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

    /**
     * Sets the bind of th new message.
     */
    public function setNewMessageBind($bind)
    {
        $this->_newmsgBind = $bind;
    }

    /**
     * Add message to the outgoing queue.
     */
    public function addOutQueue($node)
    {
        $this->_outQueue[] = $node;
    }

    /**
     * Add the auth response to protocoltreenode.
     *
     * @return ProtocolNode
     *   Return itself.
     */
    protected function addAuthResponse()
    {
        $resp = $this->authenticate();
        $respHash = array();
        $respHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $node = new ProtocolNode("response", $respHash, NULL, $resp);

        return $node;
    }

    /**
     * Send data to the whatsapp server.
     */
    protected function sendData($data)
    {
        socket_send($this->_socket, $data, strlen($data), 0);
    }

    /**
     * Send node to the whatsapp server.
     */
    protected function sendNode($node)
    {
        $this->DebugPrint($node->NodeString("tx  ") . "\n");
        $this->sendData($this->_writer->write($node));
    }

    /**
     * Read 1024 bytes from the whatsapp server.
     */
    protected function readData()
    {
        $buff = '';
        $ret = socket_read($this->_socket, 1024);
        if ($ret) {
            $buff = $this->_incomplete_message . $ret;
            $this->_incomplete_message = '';
        }

        return $buff;
    }

    /**
     * Process the challenge.
     *
     * @param $node
     *   The node that contains the challenge.
     */
    protected function processChallenge($node)
    {
        $this->challengeData = $node->_data;
    }

    /**
     * Tell the server we recieved the message.
     *
     * @param $msg
     *   The ProtocolTreeNode that contains the message.
     */
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

    /**
     * Process inbound data.
     *
     * @param $Data
     *   The data to process.
     */
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

    /**
     * Send the next message.
     */
    public function sendNext()
    {
        if (count($this->_outQueue) > 0) {
            $msgnode = array_shift($this->_outQueue);
            $msgnode->refreshTimes();
            $this->_lastId = $msgnode->getAttribute('id');
            $this->sendNode($msgnode);
        } else {
            $this->_lastId = FALSE;
        }
    }

    /**
     * Send the composing message.
     *
     * @param $msg
     *   The ProtocolTreeNode that contains the message.
     */
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

    /**
     * Connect to the WhatsApp network.
     */
    public function Connect()
    {
        $Socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($Socket, $this->_whatsAppHost, $this->_port);
        $this->_socket = $Socket;
        socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, $this->_timeout);
    }

    /**
     * Logs us in to the server.
     */
    public function Login()
    {
        $credentials = $this->checkCredentials();
        if ($credentials->status == 'ok') {
            $this->_password = $credentials->pw;
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

    /**
     * Pull from the socket, and place incoming messages in the message queue.
     */
    public function PollMessages()
    {
        $this->processInboundData($this->readData());
    }

    /**
     * Drain the message queue for application processing.
     *
     * @return array
     *   Return the message queue list.
     */
    public function GetMessages()
    {
        $ret = $this->_messageQueue;
        $this->_messageQueue = array();

        return $ret;
    }

    /**
     * Wait for message delivery notification.
     */
    public function WaitforReceipt()
    {
        $received = FALSE;
        do {
            $this->PollMessages();
            $msgs = $this->GetMessages();
            foreach ($msgs as $m) {
                // Process inbound messages.
                if ($m->_tag == "message") {
                    // @todo: Check if get _attributeHash: "retry" and notice.
                    if ($m->getChild('received') != NULL) {
                        $received = TRUE;
                    }
                }
                //print($m->NodeString("") . "\n");
            }
        } while (!$received);
    }

    /**
     * Send presence status.
     *
     * @param $type
     *   The presence status.
     */
    public function SendPresence($type = "available")
    {
        $presence = array();
        $presence['type'] = $type;
        $presence['name'] = $this->_name;
        $node = new ProtocolNode("presence", $presence, NULL, "");
        $this->sendNode($node);
    }

    /**
     * Send node to the servers.
     *
     * @param $to
     *   The reciepient to send.
     * @param $node
     *   The node that contains the message.
     */
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

        $msgid = time() . '-' . $this->_msgCounter;
        $whatsAppServer = $this->_whatsAppServer;
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

    /**
     * Send a text message to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $txt
     *   The text message.
     */
    public function Message($to, $txt)
    {
        $bodyNode = new ProtocolNode("body", NULL, NULL, $txt);
        $this->SendMessageNode($to, $bodyNode);
    }

    /**
     * Send a image to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $file
     *   The url/path to the image.
     */
    public function MessageImage($to, $file)
    {
        if ($image = file_get_contents($file)) {
            $fileName = basename($file);
            if (!preg_match("/https:\/\/[a-z0-9]+\.whatsapp.net\//i", $file)) {
                $uri = "/tmp/" . md5(time()) . $fileName;
                $tmpFile = file_put_contents($uri, $image);
                $url = $this->uploadFile($uri);
                unlink($uri);
            } else {
                $url = $file;
            }

            $mediaAttribs = array();
            $mediaAttribs["xmlns"] = "urn:xmpp:whatsapp:mms";
            $mediaAttribs["type"] = "image";
            $mediaAttribs["url"] = $url;
            $mediaAttribs["file"] = $fileName;
            $mediaAttribs["size"] = strlen($image);

            $icon = createIcon($image);

            $mediaNode = new ProtocolNode("media", $mediaAttribs, NULL, $icon);
            $this->SendMessageNode($to, $mediaNode);
        } else {
            throw new Exception('A problem has occurred trying to get the image.');
        }
    }

    /**
     * Send a location to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $long
     *   The logitude to send.
     * @param $lat
     *   The latitude to send.
     */
    public function Location($to, $long, $lat)
    {
        $whatsAppServer = $this->_whatsAppServer;
        if (strpos($to, "-") !== FALSE) {
            $whatsAppServer = $this->_whatsAppGroupServer;
        }

        $mediaHash = array();
        $mediaHash['type'] = "location";
        $mediaHash['longitude'] = $long;
        $mediaHash['latitude'] = $lat;
        $mediaHash['xmlns'] = "urn:xmpp:whatsapp:mms";
        $mediaNode = new ProtocolNode("media", $mediaHash, NULL, NULL);

        $msgid = time() . '-' . $this->_msgCounter;
        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $msgid;
        $messageHash["author"] = $this->_phoneNumber . "@" . $this->_whatsAppServer;
        $this->_msgCounter++;

        $messsageNode = new ProtocolNode("message", $messageHash, array($mediaNode), "");
        $this->sendNode($messsageNode);
    }

    /**
     * Upload file to WhatsApp servers.
     *
     * @param $file
     *   The uri of the file.
     *
     * @return string
     *   Return the remote url.
     */
    public function uploadFile($file)
    {
        $data['file'] = "@" . $file;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
        curl_setopt($ch, CURLOPT_URL, $this->_whatsAppUploadHost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        $response = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($response);
        $url = strip_tags($xml->dict->string[3]->asXML());

        if (!empty($url)) {
            return $url;
        } else {
            return FALSE;
        }
    }

    /**
     * Update de user status.
     *
     * @param $text
     *   The text message status to send.
     */
    public function sendStatusUpdate($txt)
    {
        $bodyNode = new ProtocolNode("body", NULL, NULL, $txt);
        $serverNode = new ProtocolNode("server", NULL, NULL, "");
        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");

        $msgid = time() . '-' . $this->_msgCounter;
        $messageHash = array();
        $messageHash["to"] = 's.us';
        $messageHash["type"] = "chat";
        $messageHash["id"] = $msgid;
        $this->_msgCounter++;

        $messsageNode = new ProtocolNode("message", $messageHash, array($xNode, $bodyNode), "");
        $this->sendNode($messsageNode);
    }

    /**
     * Send a pong to the whatsapp server.
     *
     * @param $msgid
     *   The id of the message.
     */
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

    /**
     * Send the nick name to the whatsapp server.
     */
    public function sendNickname()
    {
        $messageHash = array();
        $messageHash["name"] = $this->_name;
        $messsageNode = new ProtocolNode("presence", $messageHash, NULL, "");
        $this->sendNode($messsageNode);
    }

    /**
     * Request to retrieve the last online string.
     *
     * @param $to
     *   The reciepient to get the last seen.
     */
    public function RequestLastSeen($to)
    {

        $whatsAppServer = $this->_whatsAppServer;

        $queryHash = array();
        $queryHash['xmlns'] = "jabber:iq:last";
        $queryNode = new ProtocolNode("query", $queryHash, NULL, NULL);

        $msgid = time() . '-' . $this->_msgCounter;
        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "get";
        $messageHash["id"] = $msgid;
        $messageHash["from"] = $this->_phoneNumber . "@" . $this->_whatsAppServer;
        $this->_msgCounter++;

        $messsageNode = new ProtocolNode("iq", $messageHash, array($queryNode), "");
        $this->sendNode($messsageNode);
    }

    /**
     * Request a registration code from WhatsApp.
     *
     * @param $method
     *   Accepts only 'sms' or 'voice' as a value.
     * @param $countryCody
     *   ISO Country Code, 2 Digit.
     * @param $langCode
     *   ISO 639-1 Language Code: two-letter codes.
     *
     * @return object
     *   An object with server response.
     *   - status: Status of the request (sent/fail).
     *   - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
     *   - length: Registration code lenght.
     *   - method: Used method.
     *   - retry_after: Waiting time before requesting a new code.
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
            'id' => $this->_imei,
            'token' => $token,
            'c' => 'cookie',
        );

        $rest = $this->getResponse($host, $query);

        if ($rest->status != 'sent') {
            throw new Exception('There was a problem trying to request the code.');
        } else {
            return $rest;
        }
    }

    /*
     * Register account on WhatsApp using the provided code.
     *
     * @param integer $code
     *   Numeric code value provided on requestCode().
     *
     * @return object
     *   An object with server response.
     *   - status: Account status.
     *   - login: Phone number with country code.
     *   - pw: Account password.
     *   - type: Type of account.
     *   - expiration: Expiration date in UNIX TimeStamp.
     *   - kind: Kind of account.
     *   - price: Formated price of account.
     *   - cost: Decimal amount of account.
     *   - currency: Currency price of account.
     *   - price_expiration: Price expiration in UNIX TimeStamp.
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
            'id' => $this->_imei,
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
     *
     * WARNING: WhatsApp now changes your password everytime you use this.
     * Make sure you update your config file if the output informs about
     * a password change.
     *
     * @return object
     *   An object with server response.
     *   - status: Account status.
     *   - login: Phone number with country code.
     *   - pw: Account password.
     *   - type: Type of account.
     *   - expiration: Expiration date in UNIX TimeStamp.
     *   - kind: Kind of account.
     *   - price: Formated price of account.
     *   - cost: Decimal amount of account.
     *   - currency: Currency price of account.
     *   - price_expiration: Price expiration in UNIX TimeStamp.
     */
    public function checkCredentials()
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The prived phone number is not valid.');
        }

        // Build the url.
        $host = 'https://' . $this->_whatsAppCheHost;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'id' => $this->_imei,
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
     *
     * @return array
     *   An associative array with country code and phone number.
     *   - cc: The detected country code.
     *   - phone: The phone number.
     *   Return FALSE if country code is not found.
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


    /**
     * Print a message to the debug console.
     *
     * @param $debugMsg
     *   The debug message.
     */
    protected function DebugPrint($debugMsg)
    {
        if ($this->_debug) {
            print($debugMsg);
        }
    }
}
