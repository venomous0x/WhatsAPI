<?php
require_once 'protocol.class.php';
require_once 'WhatsAppEvent.php';
require_once 'func.php';
require_once 'rc4.php';
require_once 'mediauploader.php';

class WhatsProt
{
    /**
     * Constant declarations.
     */
    const CONNECTED_STATUS = 'connected';                   // Describes the connection status with the WhatsApp server.
    const DISCONNECTED_STATUS = 'disconnected';             // Describes the connection status with the WhatsApp server.
    const PORT = 5222;                                      // The port of the WhatsApp server.
    const TIMEOUT_SEC = 2;                                  // The timeout for the connection with the WhatsApp servers.
    const TIMEOUT_USEC = 0;                                 //
    const WHATSAPP_CHE_HOST = 'v.whatsapp.net/v2/exist';    // The check credentials host.
    const WHATSAPP_GROUP_SERVER = 'g.us';                   // The Group server hostname
    const WHATSAPP_HOST = 'c.whatsapp.net';                 // The hostname of the WhatsApp server.
    const WHATSAPP_REG_HOST = 'v.whatsapp.net/v2/register'; // The register code host.
    const WHATSAPP_REQ_HOST = 'v.whatsapp.net/v2/code';     // The request code host.
    const WHATSAPP_SERVER = 's.whatsapp.net';               // The hostname used to login/send messages.
    const WHATSAPP_UPLOAD_HOST = 'https://mms.whatsapp.net/client/iphone/upload.php'; // The upload host.
    const WHATSAPP_DEVICE = 'Android';                      // The device name.
    const WHATSAPP_VER = '2.10.750';                        // The WhatsApp version.
    const WHATSAPP_TOKEN = '30820332308202f0a00302010202044c2536a4300b06072a8648ce3804030500307c310b3009060355040613025553311330110603550408130a43616c69666f726e6961311430120603550407130b53616e746120436c61726131163014060355040a130d576861747341707020496e632e31143012060355040b130b456e67696e656572696e67311430120603550403130b427269616e204163746f6e301e170d3130303632353233303731365a170d3434303231353233303731365a307c310b3009060355040613025553311330110603550408130a43616c69666f726e6961311430120603550407130b53616e746120436c61726131163014060355040a130d576861747341707020496e632e31143012060355040b130b456e67696e656572696e67311430120603550403130b427269616e204163746f6e308201b83082012c06072a8648ce3804013082011f02818100fd7f53811d75122952df4a9c2eece4e7f611b7523cef4400c31e3f80b6512669455d402251fb593d8d58fabfc5f5ba30f6cb9b556cd7813b801d346ff26660b76b9950a5a49f9fe8047b1022c24fbba9d7feb7c61bf83b57e7c6a8a6150f04fb83f6d3c51ec3023554135a169132f675f3ae2b61d72aeff22203199dd14801c70215009760508f15230bccb292b982a2eb840bf0581cf502818100f7e1a085d69b3ddecbbcab5c36b857b97994afbbfa3aea82f9574c0b3d0782675159578ebad4594fe67107108180b449167123e84c281613b7cf09328cc8a6e13c167a8b547c8d28e0a3ae1e2bb3a675916ea37f0bfa213562f1fb627a01243bcca4f1bea8519089a883dfe15ae59f06928b665e807b552564014c3bfecf492a0381850002818100d1198b4b81687bcf246d41a8a725f0a989a51bce326e84c828e1f556648bd71da487054d6de70fff4b49432b6862aa48fc2a93161b2c15a2ff5e671672dfb576e9d12aaff7369b9a99d04fb29d2bbbb2a503ee41b1ff37887064f41fe2805609063500a8e547349282d15981cdb58a08bede51dd7e9867295b3dfb45ffc6b259300b06072a8648ce3804030500032f00302c021400a602a7477acf841077237be090df436582ca2f0214350ce0268d07e71e55774ab4eacd4d071cd1efad022e923a364bfacff3a80de3f950b1e0'; // Token used in request/registration code.
    const WHATSAPP_USER_AGENT = 'WhatsApp/2.10.750 Android/4.2.1 Device/GalaxyS3';  // User agent used in request/registration code.

    /**
     * Property declarations.
     */
    protected $accountInfo;             // The AccountInfo object.
    protected $challengeData;           //
    protected $debug;                   // Determines whether debug mode is on or off.
    protected $event;                   // An instance of the WhatsAppEvent class.
    protected $groupList = array();     // An array with all the groups a user belongs in.
    protected $identity;                // The IMEI/MAC address.
    protected $incompleteMessage = '';  // A list of bytes for incomplete messages.
    protected $inputKey;                // Instances of the KeyStream class.
    protected $outputKey;               // Instances of the KeyStream class.
    protected $lastGroupId = false;     // Id to the last group id created.
    protected $lastId = false;          // Id to the last message sent.
    protected $loginStatus;             // Holds the login status.
    protected $mediaFileInfo = array(); // Media File Information
    protected $mediaQueue = array();    // Queue for media message nodes
    protected $messageCounter = 1;      // Message counter for auto-id.
    protected $messageQueue = array();  // Queue for received messages.
    protected $name;                    // The user name.
    protected $newMsgBind = false;      //
    protected $outQueue = array();      // Queue for outgoing messages.
    protected $password;                // The user password.
    protected $phoneNumber;             // The user phone number including the country code without '+' or '00'.
    protected $reader;                  // An instance of the BinaryTreeNodeReader class.
    protected $serverReceivedId;        // Confirm that the *server* has received your command.
    protected $socket;                  // A socket to connect to the WhatsApp network.
    protected $writer;                  // An instance of the BinaryTreeNodeWriter class.

    /**
     * Default class constructor.
     *
     * @param string $number
     *   The user phone number including the country code without '+' or '00'.
     * @param string $identity
     *   The IMEI/MAC address or Recovery Token.
     * @param string $nickname
     *   The user name.
     * @param $debug
     *   Debug on or off, false by default.
     */
    public function __construct($number, $identity, $nickname, $debug = false)
    {
        $dict = getDictionary();
        $this->writer = new BinTreeNodeWriter($dict);
        $this->reader = new BinTreeNodeReader($dict);
        $this->debug = $debug;
        $this->phoneNumber = $number;
        if (strlen($identity) < 32) {
            //compute md5 identity hash
            $this->identity = $this->getIdentity($identity);
        } else {
            //use provided identity hash
            $this->identity = $identity;
        }
        $this->name = $nickname;
        $this->loginStatus = static::DISCONNECTED_STATUS;
    }

    /**
     * Add message to the outgoing queue.
     */
    public function addMsgOutQueue($node)
    {
        $this->outQueue[] = $node;
    }

    /**
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
     *   - price: Formatted price of account.
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
        $host = 'https://' . static::WHATSAPP_CHE_HOST;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'id' => $this->identity,
            'c' => 'cookie',
        );

        $response = $this->getResponse($host, $query);

        if ($response->status != 'ok') {
            $this->eventManager()->fire('onBadCredentials', array($this->phoneNumber, $response->status, $response->reason));
            if ($this->debug) {
                print_r($query);
                print_r($response);
            }
            throw new Exception('There was a problem trying to request the code.');
        } else {
            $this->eventManager()->fire('onGoodCredentials', array(
                $this->phoneNumber,
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

    /**
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
     *   - price: Formatted price of account.
     *   - cost: Decimal amount of account.
     *   - currency: Currency price of account.
     *   - price_expiration: Price expiration in UNIX TimeStamp.
     */
    public function codeRegister($code)
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The prived phone number is not valid.');
        }

        // Build the url.
        $host = 'https://' . static::WHATSAPP_REG_HOST;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'id' => $this->identity,
            'code' => $code,
            'c' => 'cookie',
        );

        $response = $this->getResponse($host, $query);


        if ($response->status != 'ok') {
            $this->eventManager()->fire('onFailedRegisterCode', array($this->phoneNumber, $response->status, $response->reason, $response->retry_after));
            if ($this->debug) {
                print_r($query);
                print_r($response);
            }
            throw new Exception('An error occurred registering the registration code from WhatsApp.');
        } else {
            $this->eventManager()->fire('onRegisterCode', array(
                $this->phoneNumber,
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

    /**
     * Request a registration code from WhatsApp.
     *
     * @param string $method
     *   Accepts only 'sms' or 'voice' as a value.
     * @param string $countryCody
     *   ISO Country Code, 2 Digit.
     * @param string $langCode
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
    public function codeRequest($method = 'sms', $countryCode = false, $langCode = false)
    {
        if (!$phone = $this->dissectPhone()) {
            throw new Exception('The provided phone number is not valid.');
        }

        if ($countryCode === false && $phone['ISO3166'] != '') {
            $countryCode = $phone['ISO3166'];
        }
        if ($countryCode === false) {
            $countryCode = 'US';
        }
        if ($langCode === false && $phone['ISO639'] != '') {
            $langCode = $phone['ISO639'];
        }
        if ($langCode === false) {
            $langCode = 'en';
        }

        // Build the token.
        $token = md5(static::WHATSAPP_TOKEN . $phone['phone']);

        // Build the url.
        $host = 'https://' . static::WHATSAPP_REQ_HOST;
        $query = array(
            'cc' => $phone['cc'],
            'in' => $phone['phone'],
            'lc' => $countryCode,
            'lg' => $langCode,
            'mcc' => '000',
            'mnc' => '000',
            'method' => $method,
            'id' => $this->identity,
            'token' => $token,
            'c' => 'cookie',
        );

        if ($this->debug) {
            print_r($query);
        }

        $response = $this->getResponse($host, $query);

        if ($this->debug) {
            print_r($response);
        }

        if ($response->status != 'sent') {
            if (isset($response->reason) && $response->reason == "too_recent") {
                $this->eventManager()->fire('onFailedRequestCodeTooRecent', array($this->phoneNumber, $method, $response->reason, $response->retry_after));
                $minutes = round($response->retry_after / 60);
                throw new Exception("Code already sent. Retry after $minutes minutes.");
            } else {
                $this->eventManager()->fire('onFailedRequestCode', array($this->phoneNumber, $method, $response->reason, $response->param));
                throw new Exception('There was a problem trying to request the code.');
            }
        } else {
            $this->eventManager()->fire('onRequestCode', array($this->phoneNumber, $method, $response->length));
        }

        return $response;
    }

    /**
     * Connect to the WhatsApp network.
     */
    public function connect()
    {
        $Socket = fsockopen(static::WHATSAPP_HOST, static::PORT);
        stream_set_timeout($Socket, static::TIMEOUT_SEC, static::TIMEOUT_USEC);
        $this->socket = $Socket;
        $this->eventManager()->fire('onConnect', array($this->phoneNumber, $this->socket));
    }

    /**
     * Disconnect to the WhatsApp network.
     */
    public function disconnect()
    {
        fclose($this->socket);
        $this->eventManager()->fire('onDisconnect', array($this->phoneNumber, $this->socket));
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

    /**
     * Drain the message queue for application processing.
     *
     * @return array
     *   Return the message queue list.
     */
    public function getMessages()
    {
        $ret = $this->messageQueue;
        $this->messageQueue = array();

        return $ret;
    }

    /**
     * Logs us in to the server.
     */
    public function login($profileSubscribe = false)
    {
        $this->accountInfo = (array) $this->checkCredentials();
        if ($this->accountInfo['status'] == 'ok') {
            if ($this->debug) {
                echo "New password received: " . $this->accountInfo['pw'] . "\r\n";
            }
            $this->password = $this->accountInfo['pw'];
        }
        $this->doLogin($profileSubscribe);
    }

    public function loginWithPassword($password, $profileSubscribe = false)
    {
        $this->password = $password;
        $this->doLogin($profileSubscribe);
    }

    /**
     * Pull from the socket, and place incoming messages in the message queue.
     */
    public function pollMessages()
    {
        $this->processInboundData($this->readData());
    }

    /**
     * Send the active status. User will show up as "Online" (as long as socket is connected).
     */
    public function sendActiveStatus()
    {
        $messageNode = new ProtocolNode("presence", array("type" => "active"), null, "");
        $this->sendNode($messageNode);
    }

    public function sendClearDirty($categories)
    {
        $catnodes = array();
        foreach ($categories as $category) {
            $catnode = new ProtocolNode("category", array("name" => $category), null, null);
            $catnodes[] = $catnode;
        }
        $clean = new ProtocolNode("clean", array("xmlns" => "urn:xmpp:whatsapp:dirty"), $catnodes, null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("cleardirty"),
            "type" => "set",
            "to" => "s.whatsapp.net"
                ), array($clean), null);
        $this->sendNode($node);
    }

    public function sendGetClientConfig()
    {
        $child = new ProtocolNode("config", array("xmlns" => "urn:xmpp:whatsapp:push"), null, null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("sendconfig"),
            "type" => "get",
            "to" => static::WHATSAPP_SERVER
                ), array($child), null);
        $this->sendNode($node);
    }

    public function sendGetGroups()
    {
        $this->sendGetGroupsFiltered("participating");
    }

    public function sendGetGroupsInfo($gjid)
    {
        $child = new ProtocolNode("query", array("xmlns" => "w:g"), null, null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("getgroupinfo"),
            "type" => "get",
            "to" => $this->getJID($gjid)
                ), array($child), null);
        $this->sendNode($node);
    }

    public function sendGetGroupsOwning()
    {
        $this->sendGetGroupsFiltered("owning");
    }

    public function sendGetGroupsParticipants($gjid)
    {
        $child = new ProtocolNode("list", array(
            "xmlns" => "w:g"
                ), null, null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("getparticipants"),
            "type" => "get",
            "to" => $this->getJID($gjid)
                ), array($child), null);
        $this->sendNode($node);
    }

    public function sendGetPrivacyBlockedList()
    {
        $child = new ProtocolNode("list", array(
            "name" => "default"
                ), null, null);
        $child2 = new ProtocolNode("query", array(
            "xmlns" => "jabber:iq:privacy"
                ), array($child), null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("getprivacy"),
            "type" => "get"
                ), array($child2), null);
        $this->sendNode($node);
    }

    /**
     * Get profile picture of user
     *
     * @param $number
     *  Number or JID
     *
     * @param bool $large
     *  Request large picture
     */
    public function sendGetProfilePicture($number, $large = false)
    {
        $hash = array();
        $hash["xmlns"] = "w:profile:picture";
        $hash["type"] = "image";
        if (!$large) {
            $hash["type"] = "preview";
        }
        $picture = new ProtocolNode("picture", $hash, null, null);

        $hash = array();
        $hash["id"] = $this->createMsgId("getpicture");
        $hash["type"] = "get";
        $hash["to"] = $this->getJID($number);
        $node = new ProtocolNode("iq", $hash, array($picture), null);
        $this->sendNode($node);
        $this->waitForServer($hash["id"]);
    }

    /**
     * Request to retrieve the last online string.
     *
     * @param $to
     *   The reciepient to get the last seen.
     */
    public function sendGetRequestLastSeen($to)
    {
        $queryHash = array();
        $queryHash['xmlns'] = "jabber:iq:last";
        $queryNode = new ProtocolNode("query", $queryHash, null, null);

        $messageHash = array();
        $messageHash["to"] = $this->getJID($to);
        $messageHash["type"] = "get";
        $messageHash["id"] = $this->createMsgId("lastseen");
        $messageHash["from"] = $this->getJID($this->phoneNumber);

        $messageNode = new ProtocolNode("iq", $messageHash, array($queryNode), "");
        $this->sendNode($messageNode);
        $this->waitForServer($messageHash["id"]);
        $this->eventManager()->fire('onRequestLastSeen', array($this->phoneNumber, $messageHash["id"], $to));
    }

    public function sendGetServerProperties()
    {
        $child = new ProtocolNode("props", array(
            "xmlns" => "w"
                ), null, null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("getproperties"),
            "type" => "get",
            "to" => "s.whatsapp.net"
                ), array($child), null);
        $this->sendNode($node);
    }

    public function sendGetStatus($jid)
    {
        $parts = explode("@", $jid);
        $to = $parts[0] . "@s.us";
        $child = new ProtocolNode("action", array("type" => "get"), null, null);
        $node = new ProtocolNode("message", array(
            "to" => $to,
            "type" => "action",
            "id" => $this->createMsgId("message")
                ), array($child), null);
        $this->sendNode($node);
    }

    /**
     * Create a group chat.
     *
     * @param string $subject
     *   The reciepient to send.
     * @param array $participants
     *   An array with the participants.
     *
     * @return string
     *   The group ID.
     */
    public function sendGroupsChatCreate($subject, $participants = array())
    {
        $groupHash = array();
        $groupHash["xmlns"] = "w:g";
        $groupHash["action"] = "create";
        $groupHash["subject"] = $subject;
        $group = new ProtocolNode("group", $groupHash, null, "");

        $setHash = array();
        $setHash["id"] = $this->createMsgId("creategroup");
        $setHash["type"] = "set";
        $setHash["to"] = static::WHATSAPP_GROUP_SERVER;
        $groupNode = new ProtocolNode("iq", $setHash, array($group), "");

        $this->sendNode($groupNode);
        $this->waitForGroupId();
        $groupId = $this->lastGroupId;

        if (count($participants) > 0) {
            $this->sendGroupsParticipantsAdd($groupId, $participants);
        }

        return $groupId;
    }

    public function sendGroupsChatEnd($gjid)
    {
        $gjid = $this->getJID($gjid);
        $hash = array();
        $hash["xmlns"] = "w:g";
        $hash["action"] = "delete";
        $child = new ProtocolNode("group", $hash, null, null);

        $hash = array();
        $hash["id"] = $this->createMsgId("endgroup");
        $hash["type"] = "set";
        $hash["to"] = $gjid;
        $node = new ProtocolNode("iq", $hash, array($child), null);
        $this->sendNode($node);
    }

    public function sendGroupsLeave($gjids)
    {
        if (!is_array($gjids)) {
            $gjids = array($gjids);
        }
        $nodes = array();
        foreach ($gjids as $gjid) {
            $nodes[] = new ProtocolNode("group", array("id" => $gjid), null, null);
        }
        $leave = new ProtocolNode("leave", array("xmlns" => "w:g"), $nodes, null);
        $hash = array();
        $hash["id"] = $this->createMsgId("leavegroups");
        $hash["to"] = "g.us";
        $hash["type"] = "set";
        $node = new ProtocolNode("iq", $hash, array($leave), null);
        $this->sendNode($node);
    }

    /**
     * Add participants to a group.
     *
     * @param string $groupId
     *   The group ID.
     * @param array $participants
     *   An array with the participants.
     */
    public function sendGroupsParticipantsAdd($groupId, $participants)
    {
        $this->sendGroupsChangeParticipants($groupId, $participants, 'add');
    }

    /**
     * Remove participants from a group.
     *
     * @param string $groupId
     *   The group ID.
     * @param array $participants
     *   An array with the participants.
     */
    public function sendGroupsParticipantsRemove($groupId, $participants)
    {
        $this->sendGroupsChangeParticipants($groupId, $participants, 'remove');
    }

    /**
     * Send a location to the user/group.
     * Receiver will see larger google map
     * thumbnail of Lat/Long but NO
     * name/url for location.
     * @param $to
     *   The receipient to send.
     * @param $long
     *   The longitude to send.
     * @param $lat
     *   The latitude to send.
     */
    public function sendLocation($to, $long, $lat)
    {
        $mediaHash = array();
        $mediaHash['xmlns'] = "urn:xmpp:whatsapp:mms";
        $mediaHash['type'] = "location";
        $mediaHash['latitude'] = $lat;
        $mediaHash['longitude'] = $long;

        $mediaNode = new ProtocolNode("media", $mediaHash, null, null);
        $this->sendMessageNode($to, $mediaNode);
    }

    /**
     * Send a text message to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $txt
     *   The text message.
     */
    public function sendMessage($to, $txt)
    {
        $txt = $this->parseMessageForEmojis($txt);
        $bodyNode = new ProtocolNode("body", null, null, $txt);
        $this->sendMessageNode($to, $bodyNode);
    }

    /**
     * Send a audio to the user/group.
     *
     * @param $to
     *   The reciepient to send.
     * @param $filepath
     *   The url/uri to the 3GP/CAF audio.
     * @param  bool $storeURLmedia
     * @return bool
     */
    public function sendMessageAudio($to, $filepath, $storeURLmedia = false)
    {
        if ($this->getMediaFile($filepath, 1024 * 1024 * 10) == true) {
            $allowedExtensions = array('3gp', 'caf', 'wav', 'mp3', 'wma', 'ogg', 'aif', 'aac', 'm4a');
            if (in_array($this->mediaFileInfo['fileextension'], $allowedExtensions)) {
                $b64hash = base64_encode(hash_file("sha256", $this->mediaFileInfo['filepath'], true));
                //request upload
                $this->sendRequestFileUpload($b64hash, "audio", $this->mediaFileInfo['filesize'], $this->mediaFileInfo['filepath'], $to);
                $this->processTempMediaFile($storeURLmedia);
                return true;
            } else {
                //Not allowed file type.
                $this->processTempMediaFile($storeURLmedia);
                return false;
            }
        } else {
            //Didn't get media file details.
            return false;
        }
    }

    public function sendMessageBroadcast($targets, $message)
    {
        if (!is_array($targets)) {
            $targets = array($targets);
        }
        $bodyNode = new ProtocolNode("body", null, null, $message);

        $serverNode = new ProtocolNode("server", null, null, "");
        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");

        $toNodes = array();
        foreach ($targets as $target) {
            $jid = $this->getJID($target);
            $hash = array("jid" => $jid);
            $toNode = new ProtocolNode("to", $hash, null, null);
            $toNodes[] = $toNode;
        }

        $broadcastNode = new ProtocolNode("broadcast", null, $toNodes, null);

        $messageHash = array();
        $messageHash["to"] = "broadcast";
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->createMsgId("broadcast");

        $messageNode = new ProtocolNode("message", $messageHash, array($broadcastNode, $xNode, $bodyNode), null);
        if (!$this->lastId) {
            $this->lastId = $messageHash["id"];
            $this->sendNode($messageNode);
            //listen for response
            $this->waitForServer($messageHash["id"]);
        } else {
            $this->outQueue[] = $messageNode;
        }
    }

    /**
     * Send the composing message status. When typing a message.
     *
     * @param $to
     *   The reciepient to send.
     */
    public function sendMessageComposing($to)
    {
        $comphash = array();
        $comphash['xmlns'] = 'http://jabber.org/protocol/chatstates';
        $compose = new ProtocolNode("composing", $comphash, null, "");

        $messageHash = array();
        $messageHash["to"] = $this->getJID($to);
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->createMsgId("composing");
        $messageHash["t"] = time();

        $messageNode = new ProtocolNode("message", $messageHash, array($compose), "");
        $this->sendNode($messageNode);
    }

    /**
     * Send an image file to group/user
     *
     * @param  string $to
     *  recepient
     * @param  string $filepath
     *  path to local image file
     * @param  bool $storeURLmedia
     * @return bool
     */
    public function sendMessageImage($to, $filepath, $storeURLmedia = false)
    {
        if ($this->getMediaFile($filepath, 1024 * 1024 * 5) == true) {
            $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');
            if (in_array($this->mediaFileInfo['fileextension'], $allowedExtensions)) {
                $b64hash = base64_encode(hash_file("sha256", $this->mediaFileInfo['filepath'], true));
                //request upload
                $this->sendRequestFileUpload($b64hash, "image", $this->mediaFileInfo['filesize'], $this->mediaFileInfo['filepath'], $to);
                $this->processTempMediaFile($storeURLmedia);
                return true;
            } else {
                //Not allowed file type.
                $this->processTempMediaFile($storeURLmedia);
                return false;
            }
        } else {
            //Didn't get media file details.
            return false;
        }
    }

    /**
     * Send the composing message status. When make a pause typing a message.
     *
     * @param $to
     *   The reciepient to send.
     */
    public function sendMessagePaused($to)
    {
        $comphash = array();
        $comphash['xmlns'] = 'http://jabber.org/protocol/chatstates';
        $compose = new ProtocolNode("paused", $comphash, null, "");

        $messageHash = array();
        $messageHash["to"] = $this->getJID($to);
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->createMsgId("paused");
        $messageHash["t"] = time();

        $messageNode = new ProtocolNode("message", $messageHash, array($compose), "");
        $this->sendNode($messageNode);
    }

    /**
     * Send a video to the user/group.
     *
     * @param  string $to
     *   The reciepient to send.
     * @param  string $filepath
     *   The url/uri to the MP4/MOV video.
     * @param  bool $storeURLmedia
     * @return bool
     */
    public function sendMessageVideo($to, $filepath, $storeURLmedia = false)
    {
        if ($this->getMediaFile($filepath, 1024 * 1024 * 20) == true) {
            $allowedExtensions = array('mp4', 'mov', 'avi');
            if (in_array($this->mediaFileInfo['fileextension'], $allowedExtensions)) {
                $b64hash = base64_encode(hash_file("sha256", $this->mediaFileInfo['filepath'], true));
                //request upload
                $this->sendRequestFileUpload($b64hash, "video", $this->mediaFileInfo['filesize'], $this->mediaFileInfo['filepath'], $to);
                $this->processTempMediaFile($storeURLmedia);
                return true;
            } else {
                //Not allowed file type.
                $this->processTempMediaFile($storeURLmedia);
                return false;
            }
        } else {
            //Didn't get media file details.
            return false;
        }
    }

    /**
     * Send the next message.
     */
    public function sendNextMessage()
    {
        if (count($this->outQueue) > 0) {
            $msgnode = array_shift($this->outQueue);
            $msgnode->refreshTimes();
            $this->lastId = $msgnode->getAttribute('id');
            $this->sendNode($msgnode);
        } else {
            $this->lastId = false;
        }
    }

    /**
     * Send the offline status. User will show up as "Offline".
     */
    public function sendOfflineStatus()
    {
        $messageNode = new ProtocolNode("presence", array("type" => "unavailable"), null, "");
        $this->sendNode($messageNode);
    }

    /**
     * Send a location to the user/group.
     * Allows for custom name and URL to
     * location to be set by user.
     *
     * @param $to
     *   The receipient to send.
     * @param $url
     *   The google maps place url.
     * @param $long
     *   The longitude to send.
     * @param $lat
     *   The latitude to send.
     * @param $name
     *   The google maps place name.
     *
     * @see: https://maps.google.com/maps/place?cid=1421139585205719654
     * @todo: Add support for only pass as argument the place id.
     */
    public function sendPlace($to, $long, $lat, $name, $url = null)
    {
        $mediaHash = array();
        $mediaHash['xmlns'] = "urn:xmpp:whatsapp:mms";
        $mediaHash['type'] = "location";
        $mediaHash['url'] = $url;
        $mediaHash['latitude'] = $lat;
        $mediaHash['longitude'] = $long;
        $mediaHash['name'] = $name;

        $mediaNode = new ProtocolNode("media", $mediaHash, null, null);
        $this->sendMessageNode($to, $mediaNode);
    }

    /**
     * Send a pong to the whatsapp server.
     *
     * @param $msgid
     *   The id of the message.
     */
    public function sendPong($msgid)
    {
        $whatsAppServer = static::WHATSAPP_SERVER;

        $messageHash = array();
        $messageHash["to"] = $whatsAppServer;
        $messageHash["id"] = $msgid;
        $messageHash["type"] = "result";

        $messageNode = new ProtocolNode("iq", $messageHash, null, "");
        $this->sendNode($messageNode);
        $this->eventManager()->fire('onPong', array($this->phoneNumber, $msgid));
    }

    /**
     * Send presence status.
     *
     * @param $type
     *   The presence status.
     */
    public function sendPresence($type = "available")
    {
        $presence = array();
        $presence['type'] = $type;
        $presence['name'] = $this->name;
        $node = new ProtocolNode("presence", $presence, null, "");
        $this->sendNode($node);
        $this->eventManager()->fire('onSendPresence', array($this->phoneNumber, $presence['type'], @$presence['name']));
    }

    /**
     * Send presence subscription, automatically receive presence updates as long as the socket is open.
     *
     * @param $to
     *   Phone number.
     */
    public function sendPresenceSubscription($to)
    {
        $node = new ProtocolNode("presence", array("type" => "subscribe", "to" => $this->getJID($to)), null, "");
        $this->sendNode($node);
    }

    public function sendSetGroupPicture($gjid, $path)
    {
        $this->sendSetPicture($gjid, $path);
    }

    public function sendSetPrivacyBlockedList($blockedJids = array())
    {
        if (!is_array($blockedJids)) {
            $blockedJids = array($blockedJids);
        }
        $items = array();
        foreach ($blockedJids as $index => $jid) {
            $item = new ProtocolNode("item", array(
                "type" => "jid",
                "value" => $this->getJID($jid),
                "action" => "deny",
                "order" => $index + 1//WhatsApp stream crashes on zero index
                    ), null, null);
            $items[] = $item;
        }
        $child = new ProtocolNode("list", array("name" => "default"), $items, null);
        $child2 = new ProtocolNode("query", array("xmlns" => "jabber:iq:privacy"), array($child), null);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("setprivacy"),
            "type" => "set"
                ), array($child2), null);
        $this->sendNode($node);
    }

    public function sendSetProfilePicture($path)
    {
        $this->sendSetPicture($this->phoneNumber, $path);
    }

    public function sendSetRecoveryToken($token)
    {
        $child = new ProtocolNode("pin", array("xmlns" => "w:ch:p"), null, $token);
        $node = new ProtocolNode("iq", array(
            "id" => $this->createMsgId("settoken"),
            "type" => "set",
            "to" => "s.whatsapp.net"
                ), array($child), null);
        $this->sendNode($node);
    }

    /**
     * Update de user status.
     *
     * @param string $txt
     *   The text message status to send.
     */
    public function sendStatusUpdate($txt)
    {
        $bodyNode = new ProtocolNode("body", null, null, $txt);
        $serverNode = new ProtocolNode("server", null, null, "");
        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");

        $messageHash = array();
        $messageHash["to"] = 's.us';
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->createMsgId("sendstatus");

        $messageNode = new ProtocolNode("message", $messageHash, array($xNode, $bodyNode), "");
        $this->sendNode($messageNode);
        $this->eventManager()->fire('onSendStatusUpdate', array($this->phoneNumber, $txt));
        //listen for response
        $this->pollMessages();
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
    public function sendVcard($to, $name, $vCard)
    {
        $vCardAttribs = array();
        $vCardAttribs['name'] = $name;
        $vCardNode = new ProtocolNode("vcard", $vCardAttribs, null, $vCard);

        $mediaAttribs = array();
        $mediaAttribs["xmlns"] = "urn:xmpp:whatsapp:mms";
        $mediaAttribs["type"] = "vcard";
        $mediaAttribs["encoding"] = "text";

        $mediaNode = new ProtocolNode("media", $mediaAttribs, array($vCardNode), "");
        $this->sendMessageNode($to, $mediaNode);
    }

    /**
     * Sets the bind of the new message.
     */
    public function setNewMessageBind($bind)
    {
        $this->newMsgBind = $bind;
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_URL, static::WHATSAPP_UPLOAD_HOST);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($response);
        $url = strip_tags($xml->dict->string[3]->asXML());

        if (!empty($url)) {
            $this->eventManager()->fire('onUploadFile', array($this->phoneNumber, basename($file), $url));
            return $url;
        } else {
            $this->eventManager()->fire('onFailedUploadFile', array($this->phoneNumber, basename($file)));
            return false;
        }
    }

    /**
     * Wait for group notification.
     */
    public function waitForGroupId()
    {
        $this->lastGroupId = false;
        do {
            $this->pollMessages();
        } while (!$this->lastGroupId);
    }

    /**
     * Wait for message delivery notification.
     */
    public function waitForMessageReceipt()
    {
        $received = false;
        do {
            $this->pollMessages();
            $msgs = $this->getMessages();
            foreach ($msgs as $m) {
                // Process inbound messages.
                if ($m->tag == "message") {
                    if ($m->getChild('received') != null && !isset($m->_attributeHas['retry'])) {
                        $received = true;
                    } elseif ($m->getChild('received') != null && isset($m->_attributeHas['retry'])) {
                        throw new Exception('There was a problem trying to send the message, please retry.');
                    }
                }
                //print($m->NodeString("") . "\n");
            }
        } while (!$received);
    }

    /**
     * Wait for server to acknowledge *it* has received message.
     */
    public function waitForServer($id)
    {
        $time = time();
        $this->serverReceivedId = false;
        do {
            $this->pollMessages();
        } while ($this->serverReceivedId !== $id && time() - $time < 5);
    }

    protected function authenticate()
    {
        $key = pbkdf2('sha1', base64_decode($this->password), $this->challengeData, 16, 20, true);
        $this->inputKey = new KeyStream($key);
        $this->outputKey = new KeyStream($key);
        $array = $this->phoneNumber . $this->challengeData . time();
        $response = $this->outputKey->encode($array, 0, strlen($array), false);

        return $response;
    }

    /**
     * Add the authentication nodes.
     *
     * @return ProtocolNode
     *   Return itself.
     */
    protected function createAuthNode()
    {
        $authHash = array();
        $authHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $authHash["mechanism"] = "WAUTH-1";
        $authHash["user"] = $this->phoneNumber;
        $node = new ProtocolNode("auth", $authHash, null, "");

        return $node;
    }

    /**
     * Add the auth response to protocoltreenode.
     *
     * @return ProtocolNode
     *   Return itself.
     */
    protected function createAuthResponseNode()
    {
        $resp = $this->authenticate();
        $respHash = array();
        $respHash["xmlns"] = "urn:ietf:params:xml:ns:xmpp-sasl";
        $node = new ProtocolNode("response", $respHash, null, $resp);

        return $node;
    }

    /**
     * Add stream features.
     *
     * @return ProtocolNode
     *   Return itself.
     */
    protected function createFeaturesNode($profileSubscribe)
    {
        $nodes = array();
        $nodes[] = new ProtocolNode("receipt_acks", null, null, "");
        if ($profileSubscribe) {
            $nodes[] = new ProtocolNode("w:profile:picture", array("type" => "all"), null, '');
        }
        $parent = new ProtocolNode("stream:features", null, $nodes, "");

        return $parent;
    }

    /**
     * Control msg id.
     *
     * @param  string $prefix
     * @return string
     *   A message id string.
     */
    protected function createMsgId($prefix)
    {
        $msgid = "$prefix-" . time() . '-' . $this->messageCounter;
        $this->messageCounter++;

        return $msgid;
    }

    /**
     * Print a message to the debug console.
     *
     * @param $debugMsg
     *   The debug message.
     */
    protected function debugPrint($debugMsg)
    {
        if ($this->debug) {
            echo $debugMsg;
        }
    }

    /**
     * Dissect country code from phone number.
     *
     * @return array
     *   An associative array with country code and phone number.
     *   - country: The detected country name.
     *   - cc: The detected country code (phone prefix).
     *   - phone: The phone number.
     *   - ISO3166: 2-Letter country code
     *   - ISO639: 2-Letter language code
     *   Return false if country code is not found.
     */
    protected function dissectPhone()
    {
        if (($handle = fopen('countries.csv', 'rb')) !== false) {
            while (($data = fgetcsv($handle, 1000)) !== false) {
                if (strpos($this->phoneNumber, $data[1]) === 0) {
                    // Return the first appearance.
                    fclose($handle);

                    $phone = array(
                        'country' => $data[0],
                        'cc' => $data[1],
                        'phone' => substr($this->phoneNumber, strlen($data[1]), strlen($this->phoneNumber)),
                        'ISO3166' => @$data[3],
                        'ISO639' => @$data[4]
                    );

                    $this->eventManager()->fire('onDissectPhone', array_merge(array($this->phoneNumber), $phone));

                    return $phone;
                }
            }
            fclose($handle);
        }

        $this->eventManager()->fire('onFailedDissectPhone', array($this->phoneNumber));

        return false;
    }

    protected function doLogin($profileSubscribe)
    {
        $this->writer->resetKey();
        $this->reader->resetKey();
        $resource = static::WHATSAPP_DEVICE . '-' . static::WHATSAPP_VER . '-' . static::PORT;
        $data = $this->writer->StartStream(static::WHATSAPP_SERVER, $resource);
        $feat = $this->createFeaturesNode($profileSubscribe);
        $auth = $this->createAuthNode();
        $this->sendData($data);
        $this->sendNode($feat);
        $this->sendNode($auth);

        $this->processInboundData($this->readData());
        $data = $this->createAuthResponseNode();
        $this->sendNode($data);
        $this->reader->setKey($this->inputKey);
        $this->writer->setKey($this->outputKey);
        $cnt = 0;
        do {
            $this->processInboundData($this->readData());
        } while (($cnt++ < 100) && (strcmp($this->loginStatus, static::DISCONNECTED_STATUS) == 0));
        $this->eventManager()->fire('onLogin', array($this->phoneNumber));
        $this->sendPresence();
    }

    protected function getIdentity($identity)
    {
        return md5(strrev($identity));
    }

    /**
     * Process number/jid and turn it into a JID if necessary
     *
     * @param $number
     *  Number to process
     */
    protected function getJID($number)
    {
        if (!stristr($number, '@')) {
            //check if group message
            if (stristr($number, '-')) {
                //to group
                $number .= "@" . static::WHATSAPP_GROUP_SERVER;
            } else {
                //to normal user
                $number .= "@" . static::WHATSAPP_SERVER;
            }
        }

        return $number;
    }

    /**
     * Retrieves media file and info from either a URL or localpath
     * @param $filepath
     * The URL or path to the mediafile you wish to send
     * @param $maxsizebytes
     * The maximum size in bytes the media file can be. Default 1MB
     *
     * @return boolean Returns false if file information can not be obtained.
     */
    protected function getMediaFile($filepath, $maxsizebytes = 1048576)
    {
        if (filter_var($filepath, FILTER_VALIDATE_URL) !== false) {
            $this->mediaFileInfo = array();
            $this->mediaFileInfo['url'] = $filepath;

            //File is a URL. Create a curl connection but DON'T download the body content
            //because we want to see if file is too big.
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "$filepath");
            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_NOBODY, true);

            if (curl_exec($curl) === false) {
                return false;
            }

            //While we're here, get mime type and filesize and extension
            $info = curl_getinfo($curl);
            $this->mediaFileInfo['filesize'] = $info['download_content_length'];
            $this->mediaFileInfo['filemimetype'] = $info['content_type'];
            $this->mediaFileInfo['fileextension'] = pathinfo(parse_url($this->mediaFileInfo['url'], PHP_URL_PATH), PATHINFO_EXTENSION);

            //Only download file if it's not too big
            //TODO check what max file size whatsapp server accepts.
            if ($this->mediaFileInfo['filesize'] < $maxsizebytes) {
                //Create temp file in media folder. Media folder must be writable!
                $this->mediaFileInfo['filepath'] = tempnam(getcwd() . '/media', 'WHA');
                $fp = fopen($this->mediaFileInfo['filepath'], 'w');
                if ($fp) {
                    curl_setopt($curl, CURLOPT_NOBODY, false);
                    curl_setopt($curl, CURLOPT_BUFFERSIZE, 1024);
                    curl_setopt($curl, CURLOPT_FILE, $fp);
                    curl_exec($curl);
                    fclose($fp);
                } else {
                    unlink($this->mediaFileInfo['filepath']);
                    curl_close($curl);
                    return false;
                }
                //Success
                curl_close($curl);
                return true;
            } else {
                //File too big. Don't Download.
                curl_close($curl);
                return false;
            }
            //Close connection to test file headers.
            curl_close($curl);
        } else if (file_exists($filepath)) {
            //Local file
            $this->mediaFileInfo['filesize'] = filesize($filepath);
            if ($this->mediaFileInfo['filesize'] < $maxsizebytes) {
                $this->mediaFileInfo['filepath'] = $filepath;
                $this->mediaFileInfo['fileextension'] = pathinfo($filepath, PATHINFO_EXTENSION);
                //TODO
                //Get Mime type using finfo.
//                $finfo = new finfo_open(FILEINFO_MIME_TYPE);
//                $this->_mediafileinfo['filemimetype'] = finfo_file($finfo, $filepath);
//                finfo_close($finfo);
                //mime_content_type deprecated
                $this->mediaFileInfo['filemimetype'] = mime_content_type($filepath);
                return true;
            } else {
                //File too big
                return false;
            }
        }
        //Couldn't tell what file was, local or URL.
        return false;
    }

    protected function getResponse($host, $query)
    {
        // Build the url.
        $url = $host . '?';
        foreach ($query as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }
        $url = rtrim($url, '&');

        // Open connection.
        $ch = curl_init();

        // Configure the connection.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, static::WHATSAPP_USER_AGENT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/json'));
        // This makes CURL accept any peer!
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Get the response.
        $response = curl_exec($ch);

        // Close the connection.
        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Process the challenge.
     *
     * @param $node
     *   The node that contains the challenge.
     */
    protected function processChallenge($node)
    {
        $this->challengeData = $node->data;
    }

    /**
     * Process inbound data.
     *
     * @param string $data
     *   The data to process.
     */
    protected function processInboundData($data)
    {
        try {
            $node = $this->reader->nextTree($data);
            while ($node != null) {
                $this->debugPrint($node->nodeString("rx  ") . "\n");
                if (strcmp($node->tag, "challenge") == 0) {
                    $this->processChallenge($node);
                } elseif (strcmp($node->tag, "success") == 0) {
                    $this->loginStatus = static::CONNECTED_STATUS;
                }
                if (strcmp($node->tag, "message") == 0) {
                    array_push($this->messageQueue, $node);

                    //do not send received confirmation if sender is yourself
                    if (strpos($node->attributeHash['from'], $this->phoneNumber . '@' . static::WHATSAPP_SERVER) === false) {
                        $this->sendMessageReceived($node);
                    }

                    if ($node->hasChild('x') && $this->lastId == $node->getAttribute('id')) {
                        $this->sendNextMessage();
                    }
                    if ($this->newMsgBind && $node->getChild('body')) {
                        $this->newMsgBind->process($node);
                    }
                    if ($node->getChild('composing') != null) {
                        $this->eventManager()->fire('onUserComposing', array(
                            $this->phoneNumber,
                            $node->attributeHash['from'], $node->attributeHash['type'], $node->attributeHash['t']
                        ));
                    }
                    if ($node->getChild('paused') != null) {
                        $this->eventManager()->fire('onUserPaused', array(
                            $this->phoneNumber,
                            $node->attributeHash['from'],
                            $node->attributeHash['type'],
                            $node->attributeHash['t']
                        ));
                    }
                    if ($node->getChild('notify') != null && $node->children[0]->getAttribute('name') != '' && $node->getChild('body') != null) {
                        $this->eventManager()->fire('onGetMessage', array(
                            $this->phoneNumber,
                            $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                            $node->children[0]->getAttribute('name'),
                            $node->children[2]->data
                        ));
                    }
                    if ($node->getChild('notify') != null && $node->children[0]->getAttribute('name') != '' && $node->getChild('media') != null) {
                        if ($node->children[2]->getAttribute('type') == 'image') {
                            $this->eventManager()->fire('onGetImage', array(
                                $this->phoneNumber,
                                $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                                $node->children[0]->getAttribute('name'),
                                $node->children[2]->getAttribute('size'),
                                $node->children[2]->getAttribute('url'),
                                $node->children[2]->getAttribute('file'),
                                $node->children[2]->getAttribute('mimetype'),
                                $node->children[2]->getAttribute('filehash'),
                                $node->children[2]->getAttribute('width'),
                                $node->children[2]->getAttribute('height'),
                                $node->children[2]->data
                            ));
                        } elseif ($node->children[2]->getAttribute('type') == 'video') {
                            $this->eventManager()->fire('onGetVideo', array(
                                $this->phoneNumber,
                                $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                                $node->children[0]->getAttribute('name'),
                                $node->children[2]->getAttribute('url'),
                                $node->children[2]->getAttribute('file'),
                                $node->children[2]->getAttribute('size'),
                                $node->children[2]->getAttribute('mimetype'),
                                $node->children[2]->getAttribute('filehash'),
                                $node->children[2]->getAttribute('duration'),
                                $node->children[2]->getAttribute('vcodec'),
                                $node->children[2]->getAttribute('acodec'),
                                $node->children[2]->data
                            ));
                        } elseif ($node->children[2]->getAttribute('type') == 'audio') {
                            $this->eventManager()->fire('onGetAudio', array(
                                $this->phoneNumber,
                                $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                                $node->children[0]->getAttribute('name'),
                                $node->children[2]->getAttribute('size'),
                                $node->children[2]->getAttribute('url'),
                                $node->children[2]->getAttribute('file'),
                                $node->children[2]->getAttribute('mimetype'),
                                $node->children[2]->getAttribute('filehash'),
                                $node->children[2]->getAttribute('duration'),
                                $node->children[2]->getAttribute('acodec'),
                            ));
                        } elseif ($node->children[2]->getAttribute('type') == 'vcard') {
                            $this->eventManager()->fire('onGetvCard', array(
                                $this->phoneNumber,
                                $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                                $node->children[0]->getAttribute('name'),
                                $node->children[2]->children[0]->getAttribute('name'),
                                $node->children[2]->children[0]->data
                            ));
                        } elseif ($node->children[2]->getAttribute('type') == 'location' && !isset($node->children[2]->attributeHash['url'])) {
                            $this->eventManager()->fire('onGetLocation', array(
                                $this->phoneNumber,
                                $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                                $node->children[0]->getAttribute('name'),
                                $node->children[2]->getAttribute('longitude'),
                                $node->children[2]->getAttribute('latitude'),
                                $node->children[2]->data
                            ));
                        } elseif ($node->children[2]->getAttribute('type') == 'location' && isset($node->children[2]->attributeHash['url'])) {
                            $this->eventManager()->fire('onGetPlace', array(
                                $this->phoneNumber,
                                $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t'],
                                $node->children[0]->getAttribute('name'),
                                $node->children[2]->getAttribute('name'),
                                $node->children[2]->getAttribute('longitude'),
                                $node->children[2]->getAttribute('latitude'),
                                $node->children[2]->getAttribute('url'),
                                $node->children[2]->data
                            ));
                        }
                    }
                    if ($node->getChild('x') != null) {
                        $this->serverReceivedId = $node->attributeHash['id'];
                        $this->eventManager()->fire('onMessageReceivedServer', array(
                            $this->phoneNumber,
                            $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t']
                        ));
                    }
                    if ($node->getChild('received') != null) {
                        $this->eventManager()->fire('onMessageReceivedClient', array(
                            $this->phoneNumber,
                            $node->attributeHash['from'], $node->attributeHash['id'], $node->attributeHash['type'], $node->attributeHash['t']
                        ));
                    }
                    if (strcmp($node->attributeHash['type'], "subject") == 0) {
                        print_r($node);
                        $this->eventManager()->fire('onGetGroupSubject', array(
                            $this->phoneNumber,
                            reset(explode('@', $node->attributeHash['from'])), $node->attributeHash['t'], reset(explode('@', $node->attributeHash['author'])),
                            $node->children[0]->getAttribute('name'),
                            $node->children[2]->data,
                        ));
                    }
                }
                if ($node->tag == "presence" && $node->getAttribute("status") == "dirty") {
                    //clear dirty
                    $categories = array();
                    if (count($node->children) > 0)
                        foreach ($node->children as $child) {
                            if ($child->tag == "category") {
                                $categories[] = $child->getAttribute("name");
                            }
                        }
                    $this->sendClearDirty($categories);
                }
                if (strcmp($node->tag, "presence") == 0 && strncmp($node->attributeHash['from'], $this->phoneNumber, strlen($this->phoneNumber)) != 0 && strpos($node->attributeHash['from'], "-") == false && isset($node->attributeHash['type'])) {
                    $this->eventManager()->fire('onGetPresence', array(
                        $this->phoneNumber,
                        $node->attributeHash['from'], $node->attributeHash['type']
                    ));
                }
                if (strcmp($node->tag, "presence") == 0 && strncmp($node->attributeHash['from'], $this->phoneNumber, strlen($this->phoneNumber)) != 0 && strpos($node->attributeHash['from'], "-") !== false && isset($node->attributeHash['type'])) {
                    $groupId = reset(explode('@', $node->attributeHash['from']));
                    if (isset($node->attributeHash['add'])) {
                        $this->eventManager()->fire('onAddParticipantGroup', array(
                            $this->phoneNumber,
                            $groupId, reset(explode('@', $node->attributeHash['add']))
                        ));
                    } elseif (isset($node->attributeHash['remove'])) {
                        $this->eventManager()->fire('onRemoveParticipantGroup', array(
                            $this->phoneNumber,
                            $groupId, reset(explode('@', $node->attributeHash['remove'])), reset(explode('@', $node->attributeHash['author']))
                        ));
                    }
                }
                if (strcmp($node->tag, "iq") == 0 && strcmp($node->attributeHash['type'], "get") == 0 && strcmp($node->children[0]->tag, "ping") == 0) {
                    $this->eventManager()->fire('onPing', array($this->phoneNumber, $node->attributeHash['id']));
                    $this->sendPong($node->attributeHash['id']);
                }
                if (strcmp($node->tag, "iq") == 0 && strcmp($node->attributeHash['type'], "result") == 0) {
                    $this->serverReceivedId = $node->attributeHash['id'];
                    if ($node->children[0] != null && strcmp($node->children[0]->tag, "query") == 0) {
                        array_push($this->messageQueue, $node);
                    }
                    if ($node->children[0] != null && strcmp($node->children[0]->tag, "picture") == 0) {
                        $this->eventManager()->fire("onProfilePicture", array(
                            $node->getAttribute("from"),
                            $node->getChild("picture")->getAttribute("type"),
                            $node->getChild("picture")->data
                        ));
                    }
                    if ($node->children[0] != null && strcmp($node->children[0]->tag, "media") == 0) {
                        $this->processUploadResponse($node);
                    }
                    if ($node->children[0] != null && strcmp($node->children[0]->tag, "duplicate") == 0) {
                        $this->processUploadResponse($node);
                    }
                }
                if (strcmp($node->tag, "iq") == 0 && strcmp($node->attributeHash['type'], "result") == 0) {
                    if ($node->children[0] != null && strcmp($node->children[0]->tag, "group") == 0) {
                        if (isset($node->children[0]->attributeHash['owner'])) {
                            foreach ($node->children as $group) {
                                $this->groupList[] = array(
                                    'group_id' => $group->attributeHash['id'],
                                    'owner' => $group->attributeHash['owner'],
                                    'creation' => $group->attributeHash['creation'],
                                    'subject' => $group->attributeHash['subject'],
                                    's_t' => $group->attributeHash['s_t'],
                                    's_o' => $group->attributeHash['s_o'],
                                );
                            }
                            $this->eventManager()->fire('onGetGroupList', array(
                                $this->phoneNumber,
                                $this->groupList
                            ));
                            $this->serverReceivedId = $node->attributeHash['id'];
                        } else {
                            $this->lastGroupId = $node->children[0]->attributeHash['id'];
                            $this->eventManager()->fire('onCreateGroupChat', array(
                                $this->phoneNumber,
                                $node->children[0]->attributeHash['id']
                            ));
                        }
                    } else {
                        $this->serverReceivedId = $node->attributeHash['id'];
                    }
                }
                $node = $this->reader->nextTree();
            }
        } catch (IncompleteMessageException $e) {
            $this->incompleteMessage = $e->getInput();
        }
    }

    /**
     * Process and save media image
     *
     * @param $node
     * ProtocolNode containing media
     */
    protected function processMediaImage($node)
    {
        $media = $node->getChild("media");
        if ($media != null) {
            $filename = $media->getAttribute("file");
            $url = $media->getAttribute("url");

            //save thumbnail
            $data = $media->data;
            $fp = @fopen("media/thumb_" . $filename, "w");
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            }

            //download and save original
            $data = file_get_contents($url);
            $fp = @fopen("media/" . $filename, "w");
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            }
        }
    }

    /**
     * Processes received picture node
     *
     * @param $node
     *  ProtocolNode containing the picture
     */
    protected function processProfilePicture($node)
    {
        $pictureNode = $node->getChild("picture");

        if ($pictureNode != null) {
            $type = $pictureNode->getAttribute("type");
            $data = $pictureNode->data;
            if ($type == "preview") {
                $filename = "pictures/preview_" . $node->getAttribute("from") . ".jpg";
            } else {
                $filename = "pictures/" . $node->getAttribute("from") . ".jpg";
            }
            $fp = @fopen($filename, "w");
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            }
        }
    }

    /**
     * If the media file was originally from a URL, this function either deletes it
     * or renames it depending on the user option.
     * @param boolean $storeURLmedia Should the script save and rename any media files saved from
     * a URL or remove the temporary file?
     */
    protected function processTempMediaFile($storeURLmedia)
    {
        if (isset($this->mediaFileInfo['url'])) {
            if ($storeURLmedia) {
                if (is_file($this->mediaFileInfo['filepath'])) {
                    rename($this->mediaFileInfo['filepath'], $this->mediaFileInfo['filepath'] . $this->mediaFileInfo['fileextension']);
                }
            } else {
                if (is_file($this->mediaFileInfo['filepath'])) {
                    unlink($this->mediaFileInfo['filepath']);
                }
            }
        }
    }

    /**
     * Process media upload response
     *
     * @param $node
     *  Message node
     */
    protected function processUploadResponse($node)
    {
        $id = $node->getAttribute("id");
        $messageNode = @$this->mediaQueue[$id];
        if ($messageNode == null) {
            //message not found, can't send!
            return;
        }

        $duplicate = $node->getChild("duplicate");
        if ($duplicate != null) {
            //file already on whatsapp servers
            $url = $duplicate->getAttribute("url");
            $filesize = $duplicate->getAttribute("size");
            $mimetype = $duplicate->getAttribute("mimetype");
            $filehash = $duplicate->getAttribute("filehash");
            $filetype = $duplicate->getAttribute("type");
            $width = $duplicate->getAttribute("width");
            $height = $duplicate->getAttribute("height");
            $filename = array_pop(explode("/", $url));
        } else {
            //upload new file
            $json = WhatsMediaUploader::pushFile($node, $messageNode, $this->mediaFileInfo, $this->phoneNumber);

            if (!$json) {
                //failed upload
                return false;
            }

            $url = $json->url;
            $filesize = $json->size;
            $mimetype = $json->mimetype;
            $filehash = $json->filehash;
            $filetype = $json->type;
            $width = $json->width;
            $height = $json->height;
            $filename = $json->name;
        }

        $mediaAttribs = array();
        $mediaAttribs["xmlns"] = "urn:xmpp:whatsapp:mms";
        $mediaAttribs["type"] = $filetype;
        $mediaAttribs["url"] = $url;
        $mediaAttribs["file"] = $filename;
        $mediaAttribs["size"] = $filesize;

        $filepath = $this->mediaQueue[$id]['filePath'];
        $to = $this->mediaQueue[$id]['to'];

        switch ($filetype) {
            case "image":
                $icon = createIcon($filepath);
                break;
            case "video":
                $icon = videoThumbnail();
                break;
            default:
                $icon = '';
                break;
        }

        $mediaNode = new ProtocolNode("media", $mediaAttribs, null, $icon);
        $this->sendMessageNode($to, $mediaNode);
    }

    /**
     * Read 1024 bytes from the whatsapp server.
     */
    protected function readData()
    {
        $buff = '';
        $ret = @fread($this->socket, 1024);
        if ($ret) {
            $buff = $this->incompleteMessage . $ret;
            $this->incompleteMessage = '';
        } else {
            //fclose($this->_socket);
            //$error = "Read error, closing socket...";
            //$this->eventManager()->fire('onClose', array($this->_phoneNumber, $error));
            //Don't close socket since it could be a timeout
            //TODO: Check connection status on error
        }

        return $buff;
    }

    /**
     * Send data to the whatsapp server.
     */
    protected function sendData($data)
    {
        fwrite($this->socket, $data, strlen($data));
    }

    protected function sendGetGroupsFiltered($type)
    {
        $msgID = $this->createMsgId("getgroups");
        $child = new ProtocolNode("list", array(
            "xmlns" => "w:g",
            "type" => $type
                ), null, null);
        $node = new ProtocolNode("iq", array(
            "id" => $msgID,
            "type" => "get",
            "to" => "g.us"
                ), array($child), null);
        $this->sendNode($node);
        $this->waitForServer($msgID);
    }

    /**
     * Sent action to participants of a group.
     *
     * @param string $groupId
     *   The group ID.
     * @param array $participants
     *   An array with the participants.
     * @param string $tag
     *   The tag action.
     */
    protected function sendGroupsChangeParticipants($groupId, $participants, $tag)
    {
        $Participants = array();
        foreach ($participants as $participant) {
            $Participants[] = new ProtocolNode("participant", array("jid" => $this->getJID($participant)), null, "");
        }

        $childHash = array();
        $childHash["xmlns"] = "w:g";
        $child = new ProtocolNode($tag, $childHash, $Participants, "");

        $setHash = array();
        $setHash["id"] = $this->createMsgId("participants");
        $setHash["type"] = "set";
        $setHash["to"] = $this->getJID($groupId);

        $node = new ProtocolNode("iq", $setHash, array($child), "");

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
    protected function sendMessageNode($to, $node)
    {
        $serverNode = new ProtocolNode("server", null, null, "");
        $xHash = array();
        $xHash["xmlns"] = "jabber:x:event";
        $xNode = new ProtocolNode("x", $xHash, array($serverNode), "");
        $notify = array();
        $notify['xmlns'] = 'urn:xmpp:whatsapp';
        $notify['name'] = $this->name;
        $notnode = new ProtocolNode("notify", $notify, null, "");
        $request = array();
        $request['xmlns'] = "urn:xmpp:receipts";
        $reqnode = new ProtocolNode("request", $request, null, "");

        $messageHash = array();
        $messageHash["to"] = $this->getJID($to);
        $messageHash["type"] = "chat";
        $messageHash["id"] = $this->createMsgId("message");
        $messageHash["t"] = time();

        $messageNode = new ProtocolNode("message", $messageHash, array($xNode, $notnode, $reqnode, $node), "");
        if (!$this->lastId) {
            $this->lastId = $messageHash["id"];
            $this->sendNode($messageNode);
            //listen for response
            $this->waitForServer($messageHash["id"]);
        } else {
            $this->outQueue[] = $messageNode;
        }
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
        if ($requestNode != null || $receivedNode != null) {
            $receivedHash = array();
            $receivedHash["xmlns"] = "urn:xmpp:receipts";
            $receivedNode = new ProtocolNode("received", $receivedHash, null, "");

            $messageHash = array();
            $messageHash["to"] = $msg->getAttribute("from");
            $messageHash["type"] = "chat";
            $messageHash["id"] = $msg->getAttribute("id");
            $messageHash["t"] = time();
            $messageNode = new ProtocolNode("message", $messageHash, array($receivedNode), "");
            $this->sendNode($messageNode);
            $this->eventManager()->fire('onSendMessageReceived', array($this->phoneNumber, $messageHash["t"], $msg->getAttribute("from")));
        }
    }

    /**
     * Send node to the WhatsApp server.
     */
    protected function sendNode($node)
    {
        $this->debugPrint($node->nodeString("tx  ") . "\n");
        $this->sendData($this->writer->write($node));
    }

    /**
     * Send request to upload file
     *
     * @param $b64hash
     *  Base64 hash of file
     * @param $type
     *  File type
     * @param $size
     *  File size
     * @param $filepath
     *  Path to image file
     * @param $to
     *  Recepient
     */
    protected function sendRequestFileUpload($b64hash, $type, $size, $filepath, $to)
    {
        $hash = array();
        $hash["xmlns"] = "w:m";
        $hash["hash"] = $b64hash;
        $hash["type"] = $type;
        $hash["size"] = $size;
        $mediaNode = new ProtocolNode("media", $hash, null, null);

        $hash = array();
        $id = $this->createMsgId("upload");
        $hash["id"] = $id;
        $hash["to"] = static::WHATSAPP_SERVER;
        $hash["type"] = "set";
        $node = new ProtocolNode("iq", $hash, array($mediaNode), null);

        //add to queue
        $this->mediaQueue[$id] = array("messageNode" => $node, "filePath" => $filepath, "to" => $this->getJID($to));

        $this->sendNode($node);
        $this->waitForServer($hash["id"]);
    }

    /**
     * Set your profile picture
     *
     * @param string $jid
     * @param string $filepath
     *  Path to image file
     */
    protected function sendSetPicture($jid, $filepath)
    {
        preprocessProfilePicture($filepath);
        $fp = @fopen($filepath, "r");
        if ($fp) {
            $data = fread($fp, filesize($filepath));
            if ($data) {
                //this is where the fun starts
                $hash = array();
                $hash["xmlns"] = "w:profile:picture";
                $picture = new ProtocolNode("picture", $hash, null, $data);

                $icon = createIconGD($filepath, 96, true);
                $thumb = new ProtocolNode("picture", array("type" => "preview"), null, $icon);

                $hash = array();
                $hash["id"] = $this->createMsgId("setphoto");
                $hash["to"] = $this->getJID($jid);
                $hash["type"] = "set";
                $node = new ProtocolNode("iq", $hash, array($picture, $thumb), null);

                $this->sendNode($node);
            }
        }
    }

    /**
     * Parse the message text for emojis
     *
     * This will look for special strings in the message text
     * that need to be replaced with a unicode character to show
     * the corresponding emoji.
     *
     * Emojis should be entered in the message text either as the
     * correct unicode character directly, or if this isn't possible,
     * by putting a placeholder of ##unicodeNumber## in the message text.
     * eg:
     * ##1f604## this will show the smiling face
     * ##1f1ec_1f1e7## this will show the UK flag.
     *
     * Notice that if 2 unicode characters are required they should be joined
     * with an underscore.
     *
     *
     * @param string $txt
     * The message to be parsed for emoji code.
     *
     * @return string
     */
    private function parseMessageForEmojis($txt)
    {
        $matches = null;
        preg_match_all('/##(.*?)##/', $txt, $matches, PREG_SET_ORDER);
        if (is_array($matches)) {
            foreach ($matches as $emoji) {
                $txt = str_ireplace($emoji[0], $this->unichr((string) $emoji[1]), $txt);
            }
        }

        return $txt;
    }

    /**
     * Creates the correct unicode character from the unicode code point
     *
     * @param int $int
     * @return string
     */
    private function unichr($int)
    {
        $string = null;
        $multiChars = explode('_', $int);

        foreach ($multiChars as $char) {
            $string .= mb_convert_encoding('&#' . intval($char, 16) . ';', 'UTF-8', 'HTML-ENTITIES');
        }

        return $string;
    }

}
