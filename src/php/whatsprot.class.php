<?php
require 'protocol.class.php';
require 'WhatsAppEvent.php';
require 'func.php';
require 'rc4.php';

class WhatsProt
{
    /**
     * Constant declarations.
     */
    // The hostname of the whatsapp server.
    const _whatsAppHost = 'c.whatsapp.net';
    // The hostnames used to login/send messages.
    const _whatsAppServer = 's.whatsapp.net';
    const _whatsAppGroupServer = 'g.us';
    // The device name.
    const _device = 'iPhone';
    // The WhatsApp version.
    const _whatsAppVer = '2.8.7';
    // The port of the whatsapp server.
    const _port = 5222;
    // The timeout for the connection with the Whatsapp servers.
    const _timeoutSec = 2;
    const _timeoutUsec = 0;
    // The request code host.
    const _whatsAppReqHost = 'v.whatsapp.net/v2/code';
    // The register code host.
    const _whatsAppRegHost = 'v.whatsapp.net/v2/register';
    // The check credentials host.
    const _whatsAppCheHost = 'v.whatsapp.net/v2/exist';
    // User agent and token used in reques/registration code.
    const _whatsAppUserAgent = 'WhatsApp/2.3.53 S40Version/14.26 Device/Nokia302';
    const _whatsAppToken = 'PdA2DJyKoUrwLw1Bg6EIhzh502dF9noR9uFCllGk1354754753509';

    // The upload host.
    const _whatsAppUploadHost = 'https://mms.whatsapp.net/client/iphone/upload.php';

    // Describes the connection status with the whatsapp server.
    const _disconnectedStatus = 'disconnected';
    const _connectedStatus = 'connected';

    /**
     * Property declarations.
     */
    // The user phone number including the country code without '+' or '00'.
    protected $_phoneNumber;
    // The IMEI/MAC adress.
    protected $_identity;
    // The user password.
    protected $_password;
    // The user name.
    protected $_name;

    // A list of bytes for incomplete messages.
    protected $_incomplete_message = '';

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
    // Id to the last grouip id created.
    protected $_lastGroupId = FALSE;
    // Message counter for auto-id.
    protected $_msgCounter = 1;
    // A socket to connect to the whatsapp network.
    protected $_socket;
    // An instance of the BinaryTreeNodeWriter class.
    protected $_writer;
    // An instance of the BinaryTreeNodeReader class.
    protected $_reader;
    // An instance of the WhatsAppEvent class.
    protected $event;

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
    public function __construct($Number, $identity, $Nickname, $debug = FALSE)
    {
        $this->_debug = $debug;
        $dict = getDictionary();
        $this->_writer = new BinTreeNodeWriter($dict);
        $this->_reader = new BinTreeNodeReader($dict);
        $this->_phoneNumber = $Number;
        $this->_identity = $identity;
        $this->_name = $Nickname;
        $this->_loginStatus = WhatsProt::_disconnectedStatus;
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

        $whatsAppServer = WhatsProt::_whatsAppServer;
        if (strpos($to, "-") !== FALSE) {
            $whatsAppServer = WhatsProt::_whatsAppGroupServer;
        }
        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->msgId();
        $messageHash["t"] = time();

        $messsageNode = new ProtocolNode("message", $messageHash, array($xNode, $notnode, $reqnode, $node), "");
        if (!$this->_lastId) {
            $this->_lastId = $messageHash["id"];
            $this->sendNode($messsageNode);
        } else {
            $this->_outQueue[] = $messsageNode;
        }
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
        } else {
            $error = socket_strerror(socket_last_error($this->_socket));
            $this->eventManager()->fire('onClose', array($this->_phoneNumber, $error));
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
     * Tell the server we received the message.
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
            $this->eventManager()->fire('onSendMessageReceived', array($this->_phoneNumber, $messageHash["t"], $msg->getAttribute("from")));
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
                    $this->_loginStatus = WhatsProt::_connectedStatus;
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
                    if ($node->getChild('composing') != NULL) {
                        $this->eventManager()->fire('onUserComposing', array(
                            $this->_phoneNumber,
                            $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t']
                        ));
                    }
                    if ($node->getChild('paused') != NULL) {
                        $this->eventManager()->fire('onUserPaused', array(
                            $this->_phoneNumber,
                            $node->_attributeHash['from'],
                            $node->_attributeHash['id'],
                            $node->_attributeHash['type'],
                            $node->_attributeHash['t']
                        ));
                    }
                    if ($node->getChild('notify') != NULL && $node->_children[0]->getAttribute('name') != '' && $node->getChild('body') != NULL) {
                        $this->eventManager()->fire('onGetMessage', array(
                            $this->_phoneNumber,
                            $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                            $node->_children[0]->getAttribute('name'),
                            $node->_children[2]->_data
                        ));
                    }
                    if ($node->getChild('notify') != NULL && $node->_children[0]->getAttribute('name') != '' && $node->getChild('media') != NULL) {
                        if ($node->_children[2]->getAttribute('type') == 'image') {
                            $this->eventManager()->fire('onGetImage', array(
                                $this->_phoneNumber,
                                $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                                $node->_children[0]->getAttribute('name'),
                                $node->_children[2]->getAttribute('size'),
                                $node->_children[2]->getAttribute('url'),
                                $node->_children[2]->getAttribute('file'),
                                $node->_children[2]->getAttribute('mimetype'),
                                $node->_children[2]->getAttribute('filehash'),
                                $node->_children[2]->getAttribute('width'),
                                $node->_children[2]->getAttribute('height'),
                                $node->_children[2]->_data
                            ));
                        } elseif ($node->_children[2]->getAttribute('type') == 'video') {
                            $this->eventManager()->fire('onGetVideo', array(
                                $this->_phoneNumber,
                                $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                                $node->_children[0]->getAttribute('name'),
                                $node->_children[2]->getAttribute('url'),
                                $node->_children[2]->getAttribute('file'),
                                $node->_children[2]->getAttribute('size'),
                                $node->_children[2]->getAttribute('mimetype'),
                                $node->_children[2]->getAttribute('filehash'),
                                $node->_children[2]->getAttribute('duration'),
                                $node->_children[2]->getAttribute('vcodec'),
                                $node->_children[2]->getAttribute('acodec'),
                                $node->_children[2]->_data
                            ));
                        } elseif ($node->_children[2]->getAttribute('type') == 'audio') {
                            $this->eventManager()->fire('onGetAudio', array(
                                $this->_phoneNumber,
                                $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                                $node->_children[0]->getAttribute('name'),
                                $node->_children[2]->getAttribute('size'),
                                $node->_children[2]->getAttribute('url'),
                                $node->_children[2]->getAttribute('file'),
                                $node->_children[2]->getAttribute('mimetype'),
                                $node->_children[2]->getAttribute('filehash'),
                                $node->_children[2]->getAttribute('duration'),
                                $node->_children[2]->getAttribute('acodec'),
                            ));
                        } elseif ($node->_children[2]->getAttribute('type') == 'vcard') {
                            $this->eventManager()->fire('onGetvCard', array(
                                $this->_phoneNumber,
                                $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                                $node->_children[0]->getAttribute('name'),
                                $node->_children[2]->_children[0]->getAttribute('name'),
                                $node->_children[2]->_children[0]->_data
                            ));
                        } elseif ($node->_children[2]->getAttribute('type') == 'location' && !isset($node->_children[2]->_attributeHash['url'])) {
                            $this->eventManager()->fire('onGetLocation', array(
                                $this->_phoneNumber,
                                $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                                $node->_children[0]->getAttribute('name'),
                                $node->_children[2]->getAttribute('longitude'),
                                $node->_children[2]->getAttribute('latitude'),
                                $node->_children[2]->_data
                            ));
                        } elseif ($node->_children[2]->getAttribute('type') == 'location' && isset($node->_children[2]->_attributeHash['url'])) {
                            $this->eventManager()->fire('onGetPlace', array(
                                $this->_phoneNumber,
                                $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t'],
                                $node->_children[0]->getAttribute('name'),
                                $node->_children[2]->getAttribute('name'),
                                $node->_children[2]->getAttribute('longitude'),
                                $node->_children[2]->getAttribute('latitude'),
                                $node->_children[2]->getAttribute('url'),
                                $node->_children[2]->_data
                            ));
                        }
                    }
                    if ($node->getChild('x') != NULL) {
                        $this->eventManager()->fire('onMessageReceivedServer', array(
                            $this->_phoneNumber,
                            $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t']
                        ));
                    }
                    if ($node->getChild('received') != NULL) {
                        $this->eventManager()->fire('onMessageReceivedClient', array(
                            $this->_phoneNumber,
                            $node->_attributeHash['from'], $node->_attributeHash['id'], $node->_attributeHash['type'], $node->_attributeHash['t']
                        ));
                    }
                    if (strcmp($node->_attributeHash['type'], "subject") == 0) {print_r($node);
                        $this->eventManager()->fire('onGetGroupSubject', array(
                            $this->_phoneNumber,
                            reset(explode('@', $node->_attributeHash['from'])), $node->_attributeHash['t'], reset(explode('@', $node->_attributeHash['author'])),
                            $node->_children[0]->getAttribute('name'),
                            $node->_children[2]->_data,
                        ));
                    }
                }
                if (strcmp($node->_tag, "presence") == 0 && strncmp($node->_attributeHash['from'], $this->_phoneNumber, strlen($this->_phoneNumber)) != 0 && strpos($node->_attributeHash['from'], "-") !== FALSE && isset($node->_attributeHash['type'])) {
                    $this->eventManager()->fire('onGetPresence', array(
                        $this->_phoneNumber,
                        $node->_attributeHash['from'], $node->_attributeHash['type']
                    ));
                }
                if (strcmp($node->_tag, "presence") == 0 && strncmp($node->_attributeHash['from'], $this->_phoneNumber, strlen($this->_phoneNumber)) != 0 && strpos($node->_attributeHash['from'], "-") !== FALSE && isset($node->_attributeHash['type'])) {
                    $groupId = reset(explode('@', $node->_attributeHash['from']));
                    if (isset($node->_attributeHash['add'])) {
                        $this->eventManager()->fire('onAddParticipantGroup', array(
                            $this->_phoneNumber,
                            $groupId, reset(explode('@', $node->_attributeHash['add']))
                        ));
                    } elseif (isset($node->_attributeHash['remove'])) {
                        $this->eventManager()->fire('onRemoveParticipantGroup', array(
                            $this->_phoneNumber,
                            $groupId, reset(explode('@', $node->_attributeHash['remove'])), reset(explode('@', $node->_attributeHash['author']))
                        ));
                    }
                }
                if (strcmp($node->_tag, "iq") == 0 && strcmp($node->_attributeHash['type'], "get") == 0 && strcmp($node->_children[0]->_tag, "ping") == 0) {
                    $this->eventManager()->fire('onPing', array($this->_phoneNumber, $node->_attributeHash['id']));
                    $this->Pong($node->_attributeHash['id']);
                }
                if (strcmp($node->_tag, "iq") == 0 && strcmp($node->_attributeHash['type'], "result") == 0 && strcmp($node->_children[0]->_tag, "query") == 0) {
                    array_push($this->_messageQueue, $node);
                }
                if (strcmp($node->_tag, "iq") == 0 && strcmp($node->_attributeHash['type'], "result") == 0 && strcmp($node->_children[0]->_tag, "group") == 0) {
                    $this->_lastGroupId = $node->_children[0]->_attributeHash['id'];
                    $this->eventManager()->fire('onCreateGroupChat', array(
                        $this->_phoneNumber,
                        $node->_children[0]->_attributeHash['id']
                    ));
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
     * Connect to the WhatsApp network.
     */
    public function Connect()
    {
        $Socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($Socket, WhatsProt::_whatsAppHost, WhatsProt::_port);
        $this->_socket = $Socket;
        socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => WhatsProt::_timeoutSec, 'usec' => WhatsProt::_timeoutUsec));
        $this->eventManager()->fire('onConnect', array($this->_phoneNumber, $this->_socket));
    }

    /**
     * Disconnect to the WhatsApp network.
     */
    public function Disconnect()
    {
        socket_close($this->_socket);
        $this->eventManager()->fire('onDisconnect', array($this->_phoneNumber, $this->_socket));
    }

    /**
     * Logs us in to the server.
     */
    public function Login()
    {
        $this->_accountinfo = (array) $this->checkCredentials();
        if ($this->_accountinfo['status'] == 'ok') {
            $this->_password = $this->_accountinfo['pw'];
        }
        $resource = WhatsProt::_device . '-' . WhatsProt::_whatsAppVer . '-' . WhatsProt::_port;
        $data = $this->_writer->StartStream(WhatsProt::_whatsAppServer, $resource);
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
        } while (($cnt++ < 100) && (strcmp($this->_loginStatus, WhatsProt::_disconnectedStatus) == 0));
        $this->eventManager()->fire('onLogin', array($this->_phoneNumber));
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
                    if ($m->getChild('received') != NULL && !isset($m->_attributeHas['retry'])) {
                        $received = TRUE;
                    } elseif ($m->getChild('received') != NULL && isset($m->_attributeHas['retry'])) {
                        throw new Exception('There was a problem trying to send the message, please retry.');
                    }
                }
                //print($m->NodeString("") . "\n");
            }
        } while (!$received);
    }


    /**
     * Wait for group notification.
     */
    public function WaitforGroupId()
    {
        $this->_lastGroupId = FALSE;
        do {
            $this->PollMessages();
            $msgs = $this->GetMessages();
        } while (!$this->_lastGroupId);
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
        $this->eventManager()->fire('onSendPresence', array($this->_phoneNumber, $presence['type'], $presence['name']));
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

        $messageHash = array();
        $messageHash["to"] = 's.us';
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->msgId();

        $messsageNode = new ProtocolNode("message", $messageHash, array($xNode, $bodyNode), "");
        $this->sendNode($messsageNode);
        $this->eventManager()->fire('onSendStatusUpdate', array($this->_phoneNumber, $txt));
    }

    /**
     * Send the composing message status. When typing a message.
     *
     * @param $to
     *   The reciepient to send.
     */
    public function sendComposingMessage($to)
    {
        $comphash = array();
        $comphash['xmlns'] = 'http://jabber.org/protocol/chatstates';
        $compose = new ProtocolNode("composing", $comphash, NULL, "");

        $whatsAppServer = WhatsProt::_whatsAppServer;
        if (strpos($to, "-") !== FALSE) {
            $whatsAppServer = WhatsProt::_whatsAppGroupServer;
        }

        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->msgId();
        $messageHash["t"] = time();

        $messageNode = new ProtocolNode("message", $messageHash, array($compose), "");
        $this->sendNode($messageNode);
    }

    /**
     * Send the composing message status. When make a pause typing a message.
     *
     * @param $to
     *   The reciepient to send.
     */
    public function sendPausedMessage($to)
    {
        $comphash = array();
        $comphash['xmlns'] = 'http://jabber.org/protocol/chatstates';
        $compose = new ProtocolNode("paused", $comphash, NULL, "");

        $whatsAppServer = WhatsProt::_whatsAppServer;
        if (strpos($to, "-") !== FALSE) {
            $whatsAppServer = WhatsProt::_whatsAppGroupServer;
        }

        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->msgId();
        $messageHash["t"] = time();

        $messageNode = new ProtocolNode("message", $messageHash, array($compose), "");
        $this->sendNode($messageNode);
    }

    /**
     * Create a group chat.
     *
     * @param string $subject
     *   The reciepient to send.
     * @param array $participants
     *   An array with the participans.
     *
     * @return string
     *   The group ID.
     */
    public function createGroupChat($subject, $participants)
    {
        $groupHash = array();
        $groupHash["xmlns"] = "w:g";
        $groupHash["action"] = "create";
        $groupHash["subject"] = $subject;
        $group = new ProtocolNode("group", $groupHash, NULL, "");

        $setHash = array();
        $setHash["id"] = $this->msgId();
        $setHash["type"] = "set";
        $setHash["to"] = WhatsProt::_whatsAppGroupServer;
        $groupNode = new ProtocolNode("iq", $setHash, array($group), "");

        $this->sendNode($groupNode);
        $this->WaitforGroupId();
        $groupId = $this->_lastGroupId;
        $this->addGroupParticipants($groupId, $participants);
        return $groupId;
    }

    /**
     * Add participants to a group.
     *
     * @param string $groupId
     *   The group ID.
     * @param array $participants
     *   An array with the participans.
     */
    public function addGroupParticipants($groupId, $participants)
    {
        $this->SendActionGroupParticipants($groupId, $participants, 'add');
    }

    /**
     * Remove participants from a group.
     *
     * @param string $groupId
     *   The group ID.
     * @param array $participants
     *   An array with the participans.
     */
    public function removeGroupParticipants($groupId, $participants)
    {
        $this->SendActionGroupParticipants($groupId, $participants, 'remove');
    }

    /**
     * Sent anctio to participants of a group.
     *
     * @param string $groupId
     *   The group ID.
     * @param array $participants
     *   An array with the participans.
     * @param string $tag
     *   The tag action.
     */
    protected function sendActionGroupParticipants($groupId, $participants, $tag)
    {
        $Participants = array();
        foreach($participants as $participant) {
            $Participants[] = new ProtocolNode("participant", array("jid" => $participant . '@' . WhatsProt::_whatsAppServer), NULL, "");
        }

        $childHash = array();
        $childHash["xmlns"] = "w:g";
        $child = new ProtocolNode($tag, $childHash, $Participants, "");

        $setHash = array();
        $setHash["id"] = $this->msgId();
        $setHash["type"] = "set";
        $setHash["to"] = $groupId . '@' . WhatsProt::_whatsAppGroupServer;

        $node = new ProtocolNode("iq", $setHash, array($child), "");

        $this->sendNode($node);
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
     *   The url/uri to the image.
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
     * Send a video to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $file
     *   The url/uri to the MP4/MOV video.
     */
    public function MessageVideo($to, $file)
    {
        $extension         = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $allowedExtensions = array('mp4', 'mov');
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Unsupported video format.');
        } elseif ($image = file_get_contents($file)) {
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
            $mediaAttribs["type"] = "video";
            $mediaAttribs["url"] = $url;
            $mediaAttribs["file"] = $fileName;
            $mediaAttribs["size"] = strlen($image);

            $icon = createVideoIcon($image);

            $mediaNode = new ProtocolNode("media", $mediaAttribs, NULL, $icon);
            $this->SendMessageNode($to, $mediaNode);
        } else {
            throw new Exception('A problem has occurred trying to get the video.');
        }
    }

    /**
     * Send a audio to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $file
     *   The url/uri to the 3GP/CAF audio.
     */
    public function MessageAudio($to, $file)
    {
        $extension         = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $allowedExtensions = array('3gp', 'caf');
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Unsupported audio format.');
        } elseif ($image = file_get_contents($file)) {
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
            $mediaAttribs["type"] = "audio";
            $mediaAttribs["url"] = $url;
            $mediaAttribs["file"] = $fileName;
            $mediaAttribs["size"] = strlen($image);

            $mediaNode = new ProtocolNode("media", $mediaAttribs, NULL, "");
            $this->SendMessageNode($to, $mediaNode);
        } else {
            throw new Exception('A problem has occurred trying to get the audio.');
        }
    }

    /**
     * Send a vCard to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $name
     *   The contact name.
     * @param $vCard
     *   The contact vCard to send.
     */
    public function vCard($to, $name, $vCard)
    {
        $vCardAttribs = array();
        $vCardAttribs['name'] = $name;
        $vCardNode = new ProtocolNode("vcard", $vCardAttribs, NULL, $vCard);

        $mediaAttribs = array();
        $mediaAttribs["xmlns"] = "urn:xmpp:whatsapp:mms";
        $mediaAttribs["type"] = "vcard";
        $mediaAttribs["encoding"] = "text";

        $mediaNode = new ProtocolNode("media", $mediaAttribs, array($vCardNode), "");
        $this->SendMessageNode($to, $mediaNode);
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
        $mediaHash = array();
        $mediaHash['xmlns'] = "urn:xmpp:whatsapp:mms";
        $mediaHash['type'] = "location";
        $mediaHash['latitude'] = $lat;
        $mediaHash['longitude'] = $long;

        $mediaNode = new ProtocolNode("media", $mediaHash, NULL, NULL);
        $this->SendMessageNode($to, $mediaNode);
    }

    /**
     * Send a location to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $url
     *   The google maps place url.
     * @param $long
     *   The logitude to send.
     * @param $lat
     *   The latitude to send.
     * @param $name
     *   The google maps place name.
     * @param $image
     *   The google maps place image.
     *
     * @see: https://maps.google.com/maps/place?cid=1421139585205719654
     * @todo: Add support for only pass as argument the place id.
     */
    public function Place($to, $url, $long, $lat, $name, $image)
    {
        $mediaHash = array();
        $mediaHash['xmlns'] = "urn:xmpp:whatsapp:mms";
        $mediaHash['type'] = "location";
        $mediaHash['url'] = $url;
        $mediaHash['latitude'] = $lat;
        $mediaHash['longitude'] = $long;

        if ($image = file_get_contents($file))
        {
            $icon = createVideoIcon($image);
        } else {
            $icon = giftThumbnail();
        }

        $mediaNode = new ProtocolNode("media", $mediaHash, NULL, $icon);
        $this->SendMessageNode($to, $mediaNode);
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
        curl_setopt($ch, CURLOPT_URL, WhatsProt::_whatsAppUploadHost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        $response = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($response);
        $url = strip_tags($xml->dict->string[3]->asXML());

        if (!empty($url)) {
            $this->eventManager()->fire('onUploadFile', array($this->_phoneNumber, basename($file), $url));
            return $url;
        } else {
            $this->eventManager()->fire('onFailedUploadFile', array($this->_phoneNumber, basename($file)));
            return FALSE;
        }
    }

    /**
     * Request to retrieve the last online string.
     *
     * @param $to
     *   The reciepient to get the last seen.
     */
    public function RequestLastSeen($to)
    {

        $whatsAppServer = WhatsProt::_whatsAppServer;

        $queryHash = array();
        $queryHash['xmlns'] = "jabber:iq:last";
        $queryNode = new ProtocolNode("query", $queryHash, NULL, NULL);

        $messageHash = array();
        $messageHash["to"] = $to . "@" . $whatsAppServer;
        $messageHash["type"] = "get";
        $messageHash["id"] = $this->msgId();
        $messageHash["from"] = $this->_phoneNumber . "@" . WhatsProt::_whatsAppServer;

        $messsageNode = new ProtocolNode("iq", $messageHash, array($queryNode), "");
        $this->sendNode($messsageNode);
        $this->eventManager()->fire('onRequestLastSeen', array($this->_phoneNumber, $messageHash["id"], $to));
    }

    /**
     * Send a pong to the whatsapp server.
     *
     * @param $msgid
     *   The id of the message.
     */
    public function Pong($msgid)
    {
        $whatsAppServer = WhatsProt::_whatsAppServer;

        $messageHash = array();
        $messageHash["to"] = $whatsAppServer;
        $messageHash["id"] = $msgid;
        $messageHash["type"] = "result";

        $messsageNode = new ProtocolNode("iq", $messageHash, NULL, "");
        $this->sendNode($messsageNode);
        $this->eventManager()->fire('onPong', array($this->_phoneNumber, $msgid));
    }

    /**
     * Control msg id.
     *
     * @return string
     *   A message id string.
     */
    protected function msgId()
    {
        $msgid = time() . '-' . $this->_msgCounter;
        $this->_msgCounter++;

        return $msgid;
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
     *   - length: Registration code lenght.
     *   - method: Used method.
     *   - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
     *   - param: The missing_param/bad_param.
     *   - retry_after: Waiting time before requesting a new code.
     */
    public function requestCode($method = 'sms', $countryCode = 'US', $langCode = 'en')
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The prived phone number is not valid.');
            return FALSE;
        }

        // Build the token.
        $token = md5(WhatsProt::_whatsAppToken . $phone['phone']);

        // Build the url.
        $host = 'https://' . WhatsProt::_whatsAppReqHost;
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

        $response = $this->getResponse($host, $query);

        if ($response->status != 'sent') {
            $this->eventManager()->fire('onFailedRequestCode', array($this->_phoneNumber, $method, $response->reason, $response->reason == 'too_recent' ? $response->reason : $response->param));
            throw new Exception('There was a problem trying to request the code.');
        } else {
            $this->eventManager()->fire('onRequestCode', array($this->_phoneNumber, $method, $response->length));
        }
        return $response;
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
            return FALSE;
        }

        // Build the url.
        $host = 'https://' . WhatsProt::_whatsAppRegHost;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'id' => $this->_identity,
            'code' => $code,
            'c' => 'cookie',
        );

        $response = $this->getResponse($host, $query);


        if ($response->status != 'ok') {
            $this->eventManager()->fire('onFailedRegisterCode', array($this->_phoneNumber, $response->status, $response->reason, $response->retry_after));
            throw new Exception('An error occurred registering the registration code from WhatsApp.');
        } else {
            $this->eventManager()->fire('onRegisterCode', array(
                $this->_phoneNumber,
                $response->login,
                $response->pw,
                $response->type,
                $response->expiration,
                $response->kind,
                $response->price,
                $response->cost,
                $response->currency,
                $response->price_expiration
            ));
        }
        return $response;
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
            return FALSE;
        }

        // Build the url.
        $host = 'https://' . WhatsProt::_whatsAppCheHost;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'udid' => $this->_identity,
            'c' => 'cookie',
        );

        $response = $this->getResponse($host, $query);

        if ($response->status != 'ok') {
            $this->eventManager()->fire('onBadCredentials', array($this->_phoneNumber, $response->status, $response->reason));
            throw new Exception('There was a problem trying to request the code.');
        } else {
            $this->eventManager()->fire('onGoodCredentials', array(
                $this->_phoneNumber,
                $response->login,
                $response->pw,
                $response->type,
                $response->expiration,
                $response->kind,
                $response->price,
                $response->cost,
                $response->currency,
                $response->price_expiration
            ));
        }
        return $response;
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
        curl_setopt($ch, CURLOPT_USERAGENT, WhatsProt::_whatsAppUserAgent);
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
     *   - country: The detected country name.
     *   - cc: The detected country code.
     *   - phone: The phone number.
     *   Return FALSE if country code is not found.
     */
    protected function dissectPhone()
    {
        if (($handle = fopen('countries.csv', 'rb')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000)) !== FALSE) {
                if (strpos($this->_phoneNumber, $data[1]) === 0) {
                    // Return the first appearance.
                    fclose($handle);

                    $phone = array(
                        'country' => $data[0],
                        'cc' => $data[1],
                        'phone' => substr($this->_phoneNumber, strlen($data[1]), strlen($this->_phoneNumber)),
                    );

                    $this->eventManager()->fire('onDissectPhone', array_merge(array($this->_phoneNumber), $phone));

                    return $phone;
                }
            }
            fclose($handle);
        }

        $this->eventManager()->fire('onFailedDissectPhone', array($this->_phoneNumber));
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

    /**
     * Gets a new micro event dispatcher.
     */
    public function eventManager()
    {
        if (!is_object($this->event)) {
            $this->event = new WhatsAppEvent();
        }

        return $this->event;
    }
}
