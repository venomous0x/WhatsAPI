<?php
/**
 *                  CONFIG SETTINGS
 * This script needs you to have a WHATSAPP password BEFORE you use it. If you
 * do NOT have your WHATSAPP password THIS SCRIPT IS OF NO USE.
 *
 * DO NOT LOOK FOR HELP WITH THIS SCRIPT IF YOU DO NOT HAVE YOUR WHATSAPP PASSWORD!!!
 *
 * Use the example below to add your own config settings.
 *
 * (Did I mention that you NEED to ALREADY KNOW your whatsapp's password?)
 *
 * Gmail Users:
 * If you have a GOOGLE email account AND you have *added mobile phone numbers* to your
 * gmail contacts this script can access those contacts and add them to the dropdown list
 * for you to select easily when you want to text someone.
 *
 * Numbers should be marked as MOBILE and should be in INTERNATIONAL format.
 *
 * Just to ABSOLUTELY CLEAR - by having gmail contacts listed in this script, does not imply that
 * that person has whatsapp installed. You need to know if they can accept whatsapp messages before
 * you try sending them messages.
 *
 * Most of my friends have whatsapp so I know I can select almost anyone from my google contacts.
 *
 * If you don't wish to have this feature, then do not add the email and email password line to your
 * config.
 *
 *
 * You can add as many config blocks as you want to. As long as each new user has a whatsapp password already....
 *
 * Enjoy.
 *
 */
//This is a aimple password to view this script. It is NOT the whatsapp password.
$config['webpassword'] = 'MakeUpPassword';

//Config Template
//$config['YourFirstName'] = array(
//    'id' => '<The Device Identity token. Obtained during registration with this API or using MissVenom (https://github.com/shirioko/MissVenom)to sniff from your phone.>',
//    'fromNumber' => '<Your Mobile Number eg: 44123456789>',
//    'nick' => '<A Nickname for your phone>',
//    'waPassword' => "<Your WhatsAPP password. Obtained during registration with this API or using Missvenom to sniff from your phone.>"
//
//OPTIONAL
//    'email' => 'testemail@gmail.com',
//    'emailPassword' => 'gmailpassword'
//
//);
//
//
//Example
//$config['Jonathan'] = array(
//    'id' => 'e807f1fcf82d132f9bb018ca6738a19f',
//    'fromNumber' => '441234567890',
//    'nick' => "Jonathan's iPhone",
//    'waPassword' => "EsdfsawS+/ffdskjsdhwebdgxbs=",
//    'email' => 'testemail@gmail.com',
//    'emailPassword' => 'gmailpassword'
//);

$config['YOURNAME'] = array(
    'id' => 'e807f1fcf82d132f9bb018ca6738a19f',
    'fromNumber' => '441234567890',
    'nick' => "YOURNICKNAME",
    'waPassword' => "EsdfsawS+/ffdskjsdhwebdgxbs=",
    'email' => 'testemail@gmail.com',
    'emailPassword' => 'gmailpassword'
);

/**
 *
 * NOTHING ELSE TO EDIT BELOW THIS LINE.
 *
 */
require 'whatsprot.class.php';

/**
 * For the future, other ways of getting contacts from various sources
 * can be used by implementing the Contacts Interface.
 *
 *
 * Data must be returned in an array that looks like follows:
 *
 * Array
 *
 * (
 *   [0] => Array
 *       (
 *           [name] => PersonName
 *           [id] => 1234567890
 *       )
 *
 *   [1] => Array
 *       (
 *           [name] => PersonName2
 *           [id] => 0987654321
 *       )
 * )
 */
interface Contacts
{
    public function getContacts(array $config, $user);
}

/**
 * Google Contacts implementation.
 */
class GoogleContacts implements Contacts
{
    private $config;
    private $curl;
    private $auth;
    private $headers;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function getContacts(array $config, $user)
    {
        if (!array_key_exists($user, $config)) {
            throw new Exception("No Credentials for the user requested are available.");
        } else {
            $this->config = $config;
            $contacts = $this->getGoogleContacts($user);
        }
        if (is_array($contacts)) {
            return $contacts;
        }

        return false;
    }

    private function getGoogleContacts($user)
    {
        if (!isset($this->config[$user]['email'])) {
            throw new Exception("Email address for $user was not supplied or set.");
        }

        try {
            $auth = $this->getAuthString($user);
            $this->setHeaders($auth);
            $groupid = $this->getGoogleGroupId('System Group: My Contacts');
            $contacts = $this->retreiveContacts($groupid);
        } catch (Exception $e) {
            throw $e;
        }

        return $contacts;
    }

    private function getAuthString($user)
    {
        // Construct an HTTP POST request
        $clientlogin_url = "https://www.google.com/accounts/ClientLogin";
        $clientlogin_post = array(
            "accountType" => "HOSTED_OR_GOOGLE",
            "Email" => $this->config[$user]['email'],
            "Passwd" => $this->config[$user]['emailPassword'],
            "service" => "cp",
            "source" => "whatsapp"
        );

        // Set some options (some for SHTTP)
        curl_setopt($this->curl, CURLOPT_URL, $clientlogin_url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $clientlogin_post);
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);

        // Execute
        $authresponse = curl_exec($this->curl);

        // Get the Auth string and save it
        $matches = null;
        $res = preg_match("/Auth=([a-z0-9_-]+)/i", $authresponse, $matches);
        if ($res == 1) {
            $this->auth = $matches[1];

            return $matches[1];
        }
        throw new Exception('Could not get Authentication code from google');
    }

    private function setHeaders($auth)
    {
        $this->headers = array(
            "Authorization: GoogleLogin auth=" . $auth,
            "GData-Version: 2.0",
        );
    }

    private function getHeaders()
    {
        return $this->headers;
    }

    private function getGoogleGroupId($groupname)
    {
        // Connect to Google and get a list of all contact groups.
        curl_setopt($this->curl, CURLOPT_URL, "https://www.google.com/m8/feeds/groups/default/full");
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $groupresponse = curl_exec($this->curl);

        //Load XML response
        $atom = simplexml_load_string($groupresponse);

        //Find the group id for the main contact group.
        foreach ($atom->entry as $entry) {
            if (stristr($entry->title, $groupname) !== false) {
                $contactgroup = $entry->id;
            }
        }

        if (isset($contactgroup)) {
            return $contactgroup;
        } else {
            return false;
        }
    }

    private function retreiveContacts($groupid)
    {
        curl_setopt($this->curl, CURLOPT_URL, "https://www.google.com/m8/feeds/contacts/default/full?max-results=2000&group=$groupid");
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $contactsresponse = curl_exec($this->curl);
        curl_close($this->curl);

        //Load XML response
        $contactsxml = simplexml_load_string($contactsresponse);
        $data = array();
        foreach ($contactsxml->entry as $entry) {
            $name = $entry->title;
            $gd = $entry->children('http://schemas.google.com/g/2005');
            foreach ($gd->phoneNumber as $p) {
                if ($p->attributes()->rel == "http://schemas.google.com/g/2005#mobile") {
                    $n = trim(preg_replace("/[\D+]*/", "", $p));
                    if (substr((string) $n, 0, 1) !== '0' && strlen($n) > 10) {
                        $data[] = array('name' => "$name ($n)", 'id' => $n);
                    }
                }
            }
        }
        usort($data, array($this, 'sortByName'));

        return($data);
    }

    public function sortByName($a, $b)
    {
        return strcasecmp($a['name'], $b['name']);
    }

}

/**
 * Start session so we don't always have to
 * log in.
 */
if (!isset($_SESSION)) {
    $cookieParams = session_get_cookie_params(); // Gets current cookies params.
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], false, true);
    session_name('wa_session'); // Sets the session name to the one set above.
    session_start(); // Start the php session
}

/**
 * Detect how the script was called. If it was POSTED too, we have
 * something to do, if it was just called via GET, we probably
 * only have to show the login page.
 */
$whatsapp = new Whatsapp($config);

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
        exit($whatsapp->showWebLoginForm());
    }
}
$whatsapp->process();

class Whatsapp
{
    private $config;
    private $from;
    private $number;
    private $id;
    private $nick;
    private $password;
    private $contacts = array();
    private $inputs;
    private $messages;
    private $wa;
    private $connected;
    private $waGroupList;

    public function __construct(array $config)
    {
        $this->config = $config;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            try {
                $this->inputs = $this->cleanPostInputs();

                if (isset($this->inputs['from'])) {
                    $this->from = $this->inputs['from'];

                    if (!array_key_exists($this->from, $this->config)) {
                        exit(json_encode(array(
                            "success" => false,
                            'type' => 'contacts',
                            "errormsg" => "No config settings for user $this->from could be found"
                        )));
                    } else {
                        $this->number = $this->config[$this->from]['fromNumber'];
                        $this->id = $this->config[$this->from]['id'];
                        $this->nick = $this->config[$this->from]['nick'];
                        $this->password = $this->config[$this->from]['waPassword'];

                        $this->wa = new WhatsProt($this->number, $this->id, $this->nick, false);
                        $this->wa->eventManager()->bind('onGetMessage', array($this, 'processReceivedMessage'));
                        $this->wa->eventManager()->bind('onConnect', array($this, 'connected'));
                        $this->wa->eventManager()->bind('onGetGroups', array($this, 'processGroupArray'));
                    }
                }
            } catch (Exception $e) {
                exit(json_encode(array(
                    "success" => false,
                    'type' => 'contacts',
                    "errormsg" => $e->getMessage()
                )));
            }
        }
    }

    /**
     * Sets flag when there is a connection with WhatsAPP servers.
     *
     * @return void
     */
    public function connected()
    {
        $this->connected = true;
    }

    /**
     * Clean and Filter the inputted Form values
     *
     * This function attempts to clean and filter input values from
     * the form in the $_POST array. As nothing is currently put into
     * a database etc, this is probably not required, but it should help
     * if someone wishes to extend this project later.
     *
     * @return array     array with values that have been filtered.
     * @throws Exception If no $_POST values submitted.
     */
    private function cleanPostInputs()
    {
        $args = array(
            'action' => FILTER_SANITIZE_STRING,
            'password' => FILTER_SANITIZE_STRING,
            'from' => FILTER_SANITIZE_STRING,
            'to' => array(
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_REQUIRE_ARRAY,
            ),
            'message' => FILTER_UNSAFE_RAW,
            'image' => FILTER_VALIDATE_URL,
            'audio' => FILTER_VALIDATE_URL,
            'video' => FILTER_VALIDATE_URL,
            'locationname' => FILTER_SANITIZE_STRING,
            'status' => FILTER_SANITIZE_STRING,
            'userlat' => FILTER_SANITIZE_STRING,
            'userlong' => FILTER_SANITIZE_STRING
        );

        $myinputs = filter_input_array(INPUT_POST, $args);
        if (!$myinputs) {
            throw Exception("Problem Filtering the inputs");
        }

        return $myinputs;
    }

    /**
     * Process the latest request.
     *
     * Decide what course of action to take with the latest
     * request/post to this script.
     *
     * @return void
     */
    public function process()
    {
        switch ($this->inputs['action']) {
            case 'login':
                $this->webLogin();
                break;
            case 'logout':
                $this->webLogout();
                exit($this->showWebLoginForm());
                break;
            case 'getContacts':
                $this->getContacts();
                break;
            case 'updateStatus':
                $this->updateStatus();
                break;
            case 'sendMessage':
                $this->sendMessage();
                break;
            case 'sendBroadcast':
                $this->sendBroadcast();
                break;
            default:
                if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
                    exit($this->showWebForm());
                }
                exit($this->showWebLoginForm());
                break;
        }
    }

    /**
     * Get Contacts from various sources to display in form.
     *
     * This method will allow you to add contacts from external
     * sources to add to the "to box" dropdown list on the form.
     * Currently this can include:
     *  - Whatsapp Groups for the current user
     *  - Google Contacts (if username/password was supplied in config)
     *
     * @return void
     *
     */
    private function getContacts()
    {
        try {
            //Get whatsapp's Groups this user belongs to.
            $this->waGroupList = array();
            $this->getGroupList();
            if (is_array($this->waGroupList)) {
                $this->contacts = array_merge($this->contacts, $this->waGroupList);
            }

            //Get contacts from google if gmail account configured.
            $googleContacts = array();
            if (isset($this->config[$this->from]['email'])) {

                $email = $this->config[$this->from]['email'];

                if (stripos($email, 'gmail') !== false || stripos($email, 'googlemail') !== false) {
                    $google = new GoogleContacts();
                    $googleContacts = $google->getContacts($this->config, $this->from);
                    if (is_array($googleContacts)) {
                        $this->contacts = array_merge($this->contacts, $googleContacts);
                    }
                }
            }

            if (isset($this->contacts)) {
                exit(json_encode(array(
                    "success" => true,
                    "type" => 'contacts',
                    "data" => $this->contacts,
                    "messages" => $this->messages
                )));
            }
        } catch (Exception $e) {
            exit(json_encode(array(
                "success" => false,
                'type' => 'contacts',
                "errormsg" => $e->getMessage()
            )));
        }
    }

    /**
     * Cleanly disconnect from Whatsapp.
     *
     * Ensure at end of script, if a connected had been made
     * to the whatsapp servers, that it is nicely terminated.
     *
     * @return void
     */
    public function __destruct()
    {
        if (isset($this->wa) && $this->connected) {
            $this->wa->disconnect();
        }
    }

    /**
     * Connect to Whatsapp.
     *
     * Create a connection to the whatsapp servers
     * using the supplied password.
     *
     * @return boolean
     */
    private function connectToWhatsApp()
    {
        if (isset($this->wa)) {
            $this->wa->connect();
            $this->wa->loginWithPassword($this->password);
            return true;
        }

        return false;
    }

    /**
     * Return all groups a user belongs too.
     *
     * Log into the whatsapp servers and return a list
     * of all the groups a user participates in.
     *
     * @return void
     */
    private function getGroupList()
    {
        $this->connectToWhatsApp();
        $this->wa->sendGetGroups();
    }

    /**
     * Process inbound text messages.
     *
     * If an inbound message is detected, this method will
     * store the details so that it can be shown to the user
     * at a suitable time.
     *
     * @param string $phone The number that is receiving the message
     * @param string $from  The number that is sending the message
     * @param string $id    The unique ID for the message
     * @param string $type  Type of inbound message
     * @param string $time  Y-m-d H:m:s formatted string
     * @param string $name  The Name of sender (nick)
     * @param string $data  The actual message
     *
     * @return void
     */
    public function processReceivedMessage($phone, $from, $id, $type, $time, $name, $data)
    {
        $matches = null;
        $time = date('Y-m-d H:i:s', $time);
        if (preg_match('/\d*/', $from, $matches)) {
            $from = $matches[0];
        }
        $this->messages[] = array('phone' => $phone, 'from' => $from, 'id' => $id, 'type' => $type, 'time' => $time, 'name' => $name, 'data' => $data);
    }

    /**
     * Process the event onGetGroupList and sets a formatted array of groups the user belongs to.
     *
     * @param  string        $phone      The phone number (jid ) of the user
     * @param  array         $groupArray Array with details of all groups user eitehr belongs to or owns.
     * @return array|boolean
     */
    public function processGroupArray($phone, $groupArray)
    {
        $formattedGroups = array();

        if (!empty($groupArray)) {
            foreach ($groupArray as $group) {
                $formattedGroups[] = array('name' => "GROUP: " . $group['subject'], 'id' => $group['group_id']);
            }

            $this->waGroupList = $formattedGroups;

            return true;
        }

        return false;
    }

    /**
     * Update a users Status
     *
     * @return void
     */
    private function updateStatus()
    {
        if (isset($this->inputs['status']) && trim($this->inputs['status']) !== '') {
            $this->connectToWhatsApp();
            $this->wa->sendStatusUpdate($this->inputs['status']);
            exit(json_encode(array(
                "success" => true,
                "data" => "<br />Your status was updated to - <b>{$this->inputs['status']}</b>",
                "messages" => $this->messages
            )));
        } else {
            exit(json_encode(array(
                "success" => false,
                "errormsg" => "There was no text in the submitted status box!"
            )));
        }
    }

    /**
     * Sends a message to a contact.
     *
     * Depending on the inputs sends a
     * message/video/image/location message to
     * a contact.
     *
     * @return void
     */
    private function sendMessage()
    {
        if (is_array($this->inputs['to'])) {
            $this->connectToWhatsApp();
            foreach ($this->inputs['to'] as $to) {
                if (trim($to) !== '') {
                    if (isset($this->inputs['message']) && trim($this->inputs['message'] !== '')) {
                        $this->wa->sendMessageComposing($to);
                        $this->wa->sendMessage($to, $this->inputs['message']);
                    }
                    if (isset($this->inputs['image']) && $this->inputs['image'] !== false) {
                        $this->wa->sendMessageImage($to, $this->inputs['image']);
                    }
                    if (isset($this->inputs['audio']) && $this->inputs['audio'] !== false) {
                        $this->wa->sendMessageAudio($to, $this->inputs['audio']);
                    }
                    if (isset($this->inputs['video']) && $this->inputs['video'] !== false) {
                        $this->wa->sendMessageVideo($to, $this->inputs['video']);
                    }
                    if (isset($this->inputs['locationname']) && trim($this->inputs['locationname'] !== '')) {
                        $this->wa->sendMessageLocation($to, $this->inputs['userlong'], $this->inputs['userlat'], $this->inputs['locationname'], null);
                    }
                } else {
                    exit(json_encode(array(
                        "success" => false,
                        "errormsg" => "A blank number was provided!",
                        "messages" => $this->messages
                    )));
                }
            }

            exit(json_encode(array(
                "success" => true,
                "data" => "Message Sent!",
                "messages" => $this->messages
            )));
        }
        exit(json_encode(array(
            "success" => false,
            "errormsg" => "Provided numbers to send message to were not in valid format."
        )));
    }

    /**
     * Sends a broadcast Message to a group of contacts.
     *
     * Currenly only sends a normal message to
     * a group of contacts.
     *
     * @return void
     */
    private function sendBroadcast()
    {
        if (isset($this->inputs['action']) && trim($this->inputs['action']) == 'sendBroadcast') {

            $this->connectToWhatsApp();
            if (isset($this->inputs['message']) && trim($this->inputs['message'] !== '')) {
                $this->wa->sendBroadcastMessage($this->inputs['to'], $this->inputs['message']);
            }
            if (isset($this->inputs['image']) && $this->inputs['image'] !== false) {
                $this->wa->sendBroadcastImage($this->inputs['to'], $this->inputs['image']);
            }
            if (isset($this->inputs['audio']) && $this->inputs['audio'] !== false) {
                $this->wa->sendBroadcastAudio($this->inputs['to'], $this->inputs['audio']);
            }
            if (isset($this->inputs['video']) && $this->inputs['video'] !== false) {
                $this->wa->sendBroadcastVideo($this->inputs['to'], $this->inputs['video']);
            }
            if (isset($this->inputs['locationname']) && trim($this->inputs['locationname'] !== '')) {
                $this->wa->sendBroadcastPlace($this->inputs['to'], $this->inputs['userlong'], $this->inputs['userlat'], $this->inputs['locationname'], null);
            }
            exit(json_encode(array(
                "success" => true,
                "data" => "Broadcast Message Sent!",
                "messages" => $this->messages
            )));
        }
    }

    /**
     * Process the web login page.
     *
     * @return void
     */
    private function webLogin()
    {
        if ($this->inputs['password'] == $this->config['webpassword']) {
            $_SESSION['logged_in'] = true;
            exit($this->showWebForm());
        } else {
            $error = "Sorry your password was incorrect.";
            exit($this->showWebLoginForm($error));
        }
    }

    /**
     * Logout of the webapp
     *
     * @return void
     */
    private function webLogout()
    {
        unset($_SESSION['logged_in']);
    }

    /**
     * Show Web app Login page
     *
     * @return void
     */
    public function showWebLoginForm($error = null)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Personal Whatsapp Login</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta name="description" content="">
                <meta name="author" content="">

                <!-- Styles -->
                <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap.min.css" rel="stylesheet">
                <style type="text/css">
                    body {
                        padding-top: 40px;
                        padding-bottom: 40px;
                        background-color: #f5f5f5;
                    }
                    h2 {
                        font-size: 24px;
                    }
                    .form-signin, .error {
                        max-width: 300px;
                        padding: 20px 40px;
                        margin: 10px auto;
                        background-color: #fff;
                        border: 1px solid #e5e5e5;
                        -webkit-border-radius: 5px;
                        -moz-border-radius: 5px;
                        border-radius: 5px;
                        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        box-shadow: 0 1px 2px rgba(0,0,0,.05);
                    }
                    .form-signin input[type="password"] {
                        font-size: 16px;
                        height: auto;
                        margin-bottom: 15px;
                        padding: 7px 9px;
                    }
                    .error {
                        background-color: pink;
                        padding: 10px;
                    }
                </style>
                <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-responsive.min.css" rel="stylesheet">

                <!-- Javascript -->
                <script type="text/javascript" src="//scottjehl.github.io/iOS-Orientationchange-Fix/ios-orientationchange-fix.js"></script>
            </head>

            <body>
                <div class="container">
                    <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <?php
                        if (isset($error)) {
                            echo "<div class='error'>Sorry the password was invalid. Please try again.</div>";
                        }
                        ?>
                        <input type="hidden" name="action" value="login">
                        <h2 class="form-signin-heading">Whatsapp! Login</h2>
                        <input type="password" name="password" class="input-block-level" placeholder="Password">
                        <div class="pagination-centered">
                            <button class="btn btn-large btn-primary" type="submit">Sign in</button>
                        </div>
                    </form>
                </div>
            </body>
        </html>
        <?php

        return ob_get_clean();
    }

    /**
     * Show main Web App.
     *
     * @return void
     */
    private function showWebForm()
    {
        ob_start();
        if (!isset($_SESSION)) {
            session_name('wa_session');
            session_start(); // Start the php session
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Personal Whatsapp</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta name="description" content="">
                <meta name="author" content="">

                <!-- Styles -->
                <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap.min.css" rel="stylesheet">
                <link href="chosen/chosen.css" rel="stylesheet">
                <link href="emoji/emojisprite.css" rel="stylesheet" type="text/css" />
                <style type="text/css">
                    body {
                        background-color: #f5f5f5;
                    }
                    .container{
                        width: 320px;
                    }
                    h2 {
                        font-size: 24px;
                        text-align: center;
                        margin:0;
                    }
                    #inboundMessage, #results {
                        padding-left: 5px;
                    }
                    .form-horizontal {
                        width: 300px;
                        padding: 10px 10px 10px 5px;
                        margin: 5px auto;
                        background-color: #fff;
                        border: 1px solid #e5e5e5;
                        border-radius: 5px;
                        -webkit-border-radius: 5px;
                        -moz-border-radius: 5px;
                        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        box-shadow: 0 1px 2px rgba(0,0,0,.05);
                    }
                    .form-horizontal .form-heading
                    {
                        margin-bottom: 10px;
                    }
                    .form-horizontal .control-label{
                        width: 45px;
                    }
                    .form-horizontal .controls{
                        margin-left: 55px;
                        width: 248px;
                    }
                    .form-horizontal .control-group{
                        margin-bottom: 5px;
                    }
                    .input-prepend input[id*='user']{
                        width: 70px;
                    }
                    .input-prepend input{
                        width:206px;
                    }
                    .nav-tabs {
                        border-bottom:transparent;
                    }
                    #faketextbox {
                        height: 90px;
                        display:block;
                        background-color: #FFF;
                        padding: 5px;
                        border: 1px solid #e5e5e5;
                        border-radius: 5px;
                        -webkit-border-radius: 5px;
                        -moz-border-radius: 5px;
                        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        box-shadow: 0 1px 2px rgba(0,0,0,.05);
                        font-size: 13px;
                        overflow-y: auto;
                        white-space: pre-wrap;
                    }
                    img.emoji {
                        content: "";
                        padding: 2px;
                        display:inline-block;
                        cursor: pointer;
                    }
                    img.emoji:hover{
                        background-color:lightskyblue;
                        -webkit-border-radius: 5px;
                    }
                    .nav-tabs>li>a {
                        padding: 0 6px;
                        line-height: 15px;
                        font-size: 10px;
                    }
                    .emojicontainer{
                        margin-left: 55px;
                        width: 247px;
                    }
                    #emojiTab.nav{
                        margin-bottom: 3px;
                    }
                    #emojiTab ul.dropdown-menu{
                        min-width:194px;
                        padding: 6px;
                    }

                    #map_canvas{
                        width: 247px;
                        height: 300px;
                        margin-bottom: 5px;
                    }

                    #pickLocation{
                        margin-left: 12px;
                    }
                    #mapContainer input{
                        width: 130px;
                        position: relative;
                        top: 30px;
                        left: 3px;
                        z-index: 5;
                        background-color: #fff;
                        padding: 2px;
                        border: 1px solid #999;
                    }

                    .chzn-container-single .chzn-single{
                        height:28px;
                        line-height: 28px;
                    }

                    .chzn-container-multi .chzn-choices li{
                        float:none;
                    }

                    .chzn-container-multi .chzn-choices .search-choice{
                        margin-right: 3px;
                    }
                    .chzn-container-multi .chzn-choices .search-field{
                        height: 28px;
                        line-height: 28px;
                    }
                    div#to_chzn a.error{
                        color: #b94a48;
                        border-color: #b94a48;
                    }

                    div#to_chzn a.success{
                        color: #468847;
                        border-color: #468847;
                    }

                    div.pac-container{
                        width: 242px !important;
                    }
                    #formcontrols button{
                        width: 48%;
                        height: 50px;
                    }

                    /* Large desktop */
                    @media (min-width: 1200px) {
                        #formcontrols button{height: 30px;}
                    }

                    /* Portrait tablet to landscape and desktop */
                    @media (min-width: 768px) and (max-width: 979px) {
                    }

                    /* Landscape phone to portrait tablet */
                    @media (max-width: 767px) {
                    }

                    /* Landscape phones and down */
                    @media (max-width: 480px) {
                    }
                </style>

                <!-- Javascript -->
                <script type="text/javascript" src="//code.jquery.com/jquery-1.8.3.min.js"></script>
                <script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
                <!-- Chosen "fork" originally from here due ability to add number not found in list: https://github.com/koenpunt/chosen  -->
                <script type="text/javascript" src="chosen/chosen.jquery.min.js"></script>
                <!-- Blockui fork from: http://www.malsup.com/jquery/block/-->
                <script type="text/javascript" src="//scottjehl.github.io/iOS-Orientationchange-Fix/ios-orientationchange-fix.js"></script>
                <script type="text/javascript" src="//bainternet-js-cdn.googlecode.com/svn/trunk/js/jQuery%20BlockUI%20Plugin/2.39/jquery.blockUI.js"></script>
                <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
                <script type="text/javascript" src="//cdn.jsdelivr.net/gmap3/5.0b/gmap3.min.js"></script>
                <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places" ></script>

                <script type="text/javascript">
                    $(document).ready(function() {

                        $(document).ajaxStop(function() {
                            $.unblockUI();
                        });

                        $(document).ajaxStart(function() {
                            $.blockUI();
                        });

                        $("#action").change(function() {
                            var val = $(this).val();
                            doAction(val);
                        });

                        createChosen();
                        $('#to').bind("change", function() {
                            $('#whatsappform').validate().element($(this));
                        });

                        $('#from').bind("change", function() {
                            doAction('getContacts');
                        });

                        function doAction(type)
                        {
                            switch (type) {
                                case 'sendMessage':
                                    $('#to').prop('multiple', false);
                                    $("#to").prop('disabled', false);
                                    createChosen();
                                    $("#to").closest('div.control-group').show();
                                    $('#emojiTab').show();
                                    $("#faketextbox").prop('disabled', false);
                                    $("#faketextbox").closest('div.control-group').show();
                                    $("#image").prop('disabled', false);
                                    $("#image").closest('div.control-group').show();
                                    $("#audio").prop('disabled', false);
                                    $("#audio").closest('div.control-group').show();
                                    $("#video").prop('disabled', false);
                                    $("#video").closest('div.control-group').show();
                                    $("#locationname").prop('disabled', false);
                                    $("#userlat").prop('disabled', false);
                                    $("#userlong").prop('disabled', false);
                                    $("#locationname").closest('div.control-group').show();
                                    $("#pickLocation").show();
                                    $("#status").prop('disabled', true);
                                    $("#status").closest('div.control-group').hide();
                                    break;

                                case 'updateStatus':
                                    $("#to").prop('disabled', true);
                                    $("#to").closest('div.control-group').hide();
                                    $('#emojiTab').hide();
                                    $("#faketextbox").prop('disabled', true);
                                    $("#faketextbox").closest('div.control-group').hide();
                                    $("#image").prop('disabled', true);
                                    $("#image").closest('div.control-group').hide();
                                    $("#audio").prop('disabled', true);
                                    $("#audio").closest('div.control-group').hide();
                                    $("#video").prop('disabled', true);
                                    $("#video").closest('div.control-group').hide();
                                    $("#locationname").prop('disabled', true);
                                    $("#userlat").prop('disabled', true);
                                    $("#userlong").prop('disabled', true);
                                    $("#locationname").closest('div.control-group').hide();
                                    $("#pickLocation").hide();
                                    $("#status").prop('disabled', false);
                                    $("#status").closest('div.control-group').show();
                                    break;

                                case 'sendBroadcast':
                                    $('#to').prop('multiple', true);
                                    $("#to").prop('disabled', false);
                                    createChosen();
                                    $("#to").closest('div.control-group').show();
                                    $('#emojiTab').show();
                                    $("#faketextbox").prop('disabled', false);
                                    $("#faketextbox").closest('div.control-group').show();
                                    $("#image").prop('disabled', false);
                                    $("#image").closest('div.control-group').show();
                                    $("#audio").prop('disabled', false);
                                    $("#audio").closest('div.control-group').show();
                                    $("#video").prop('disabled', false);
                                    $("#video").closest('div.control-group').show();
                                    $("#locationname").prop('disabled', false);
                                    $("#userlat").prop('disabled', false);
                                    $("#userlong").prop('disabled', false);
                                    $("#locationname").closest('div.control-group').show();
                                    $("#pickLocation").show();
                                    $("#status").prop('disabled', true);
                                    $("#status").closest('div.control-group').hide();
                                    break;

                                case 'getContacts':
                                    var fromuser = $("#from").val();
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                                        cache: false,
                                        data: {action: "getContacts", from: fromuser},
                                        dataType: "json",
                                        timeout: 15000,
                                        success: onSuccess,
                                        error: onError,
                                        complete: function(jqXHR, textStatus) {
                                            $.unblockUI();
                                        }
                                    });

                                    return false;

                                default:
                                    alert('Error. An action should have been specified.');

                                    return false;
                            }
                        }

                        function createChosen()
                        {
                            $('#to_chzn').remove();
                            $('#to').removeClass('chzn-done');
                            $("#to").chosen({
                                create_option: true,
                                persistent_create_option: true,
                                no_results_text: "Can't Find:",
                                create_option_text: 'Click to Add '
                            });
                            $('#to_chzn').css('width', '220px');
                        };

                        $("img").on('click', function() {
                            var txtToAdd = this.outerHTML;
                            $("#faketextbox").append(txtToAdd);
                        });

                        $('#mapContainer').hide();
                        $('#pickLocation').click(function() {
                            if ($('#mapContainer').is(':visible')) {
                                $('#mapContainer').hide();

                                return false;
                            } else {
                                $('#mapContainer').show();
                                createMap();

                                return false;
                            }
                        });

                        function createMap()
                        {
                            var map = new google.maps.Map(document.getElementById('map_canvas'), {
                                center: new google.maps.LatLng(53.4, -7.778),
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                zoom: 6,
                                panControl: false,
                                streetViewControl: true,
                                streetViewOptions: {
                                    position: google.maps.ControlPosition.LEFT_CENTER
                                },
                                mapTypeControl: true,
                                mapTypeControlOptions: {
                                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                                },
                                zoomControl: true,
                                zoomControlOptions: {
                                    style: google.maps.ZoomControlStyle.SMALL,
                                    position: google.maps.ControlPosition.LEFT_CENTER
                                }
                            });

                            var input = (document.getElementById('target'));
                            var searchBox = new google.maps.places.SearchBox(input);
                            var marker;

                            google.maps.event.addListener(searchBox, 'places_changed', function() {
                                var places = searchBox.getPlaces();
                                placeMarker(places[0].geometry.location);
                                map.setZoom(16);
                                map.setCenter(marker.getPosition());
                            });

                            function placeMarker(location)
                            {
                                if (marker) {
                                    marker.setPosition(location);
                                } else {
                                    marker = new google.maps.Marker({
                                        position: location,
                                        map: map
                                    });
                                }
                                updateLocation(location);
                            }

                            function updateLocation(event)
                            {
                                if ($('#target').val() !== '') {
                                    $('#locationname').val($('#target').val());
                                }
                                $('#userlat').val(event.lat().toFixed(5));
                                $('#userlong').val(event.lng().toFixed(6));
                            }

                            google.maps.event.addListener(map, 'click', function(event) {
                                placeMarker(event.latLng);

                                var geocoder = new google.maps.Geocoder();
                                geocoder.geocode({
                                    "latLng": event.latLng
                                }, function(results, status) {
                                    if (status === google.maps.GeocoderStatus.OK) {
                                        //                                        var lat = results[0].geometry.location.lat(),
                                        //                                                lng = results[0].geometry.location.lng();
                                        $("#locationname").val(results[0].formatted_address);
                                    }
                                });
                            });
                        }

                        //if form is submitted
                        $('#whatsappform').submit(function(e) {
                            if ($('#whatsappform').validate().form() === false) {
                                return false;
                            }

                            //Store original html to replace back in box later.
                            var original = $('#faketextbox').html();
                            //Scan html code for emojis and replace with text and special marker.
                            $('#faketextbox img').each(function(index) {
                                var emojiUnicode = this.outerHTML.match(/emoji-(.*?)"/)[1];
                                $(this).replaceWith('##' + emojiUnicode + '##');
                            });
                            //Replace all BR's with line breaks.
                            var message = $.trim($('#faketextbox').html().replace(/<br\s?\/?>/g, "\n"));
                            //Copy the corrected message text to our hidden input field to be serialised.
                            $('#message').val($('#faketextbox').html(message).text());
                            //Replace the corrected text with the original html so it shows properly on a browser.
                            $('#faketextbox').html(original);
                            //Continue with the form.
                            var formData = $("#whatsappform").serialize();

                            $.ajax({
                                type: "POST",
                                url: "<?php echo $_SERVER['PHP_SELF'] ?>",
                                cache: false,
                                data: formData,
                                dataType: "json",
                                timeout: 45000,
                                success: onSuccess,
                                error: onError,
                                //beforeSend: function(jqXHR, settings) {
                                //},
                                complete: function() {
                                    $.unblockUI();
                                }
                            });

                            return false;
                        });

                        $("#whatsappform").validate({
                            ignore: ":hidden:not(select)",
                            rules: {
                                from: {
                                    required: true
                                },
                                to: {
                                    required: true
                                },
                                status: {
                                    minlength: 4,
                                    required: true
                                },
                                password: {
                                    minlength: 2,
                                    required: true
                                }
                            },
                            errorPlacement: function(error, element) {
                                return true;
                            },
                            highlight: function(label) {
                                $(label).closest('.control-group').addClass('error');
                                if (label.id === 'to') {
                                    $('div#to_chzn a').addClass('error');
                                    $('div#to_chzn a').removeClass('success');
                                }
                            },
                            success: function(label) {
                                $("#" + label[0].htmlFor).closest('.control-group').addClass('success');
                                $("#" + label[0].htmlFor).closest('.control-group').removeClass('error');
                                if (label[0].htmlFor === 'to') {
                                    $('div#to_chzn a').addClass('success');
                                    $('div#to_chzn a').removeClass('error');
                                }
                            }

                        });

                        function onSuccess(data, textStatus, jqXHR)
                        {
                            switch (data.success) {

                                case false:
                                    newAlert('error', data.errormsg);
                                    break;

                                case true:
                                    if (data.type === 'contacts') {
                                        $("select#to").html('<option></option>');
                                        $.each(data.data, function(i, item) {
                                            $("select#to").append("<option value='" + data.data[i].id + "'>" + data.data[i].name + "</option>");
                                        });
                                        $("#to").trigger("liszt:updated");
                                    } else {
                                        newAlert('success', data.data);
                                    }
                                    if (data.messages !== null) {
                                        $.each(data.messages, function(i, item) {
                                            $("#inboundMessage").append("<div class='alert alert-block alert-info'>" + data.messages[i].time + ":<br /> " + data.messages[i].name + " (+" + data.messages[i].from + ")<br />" + data.messages[i].data + "</div>");
                                        });
                                    }
                                    break;

                                default:
                                    newAlert('success', 'The ajax call was successful but there was an error with the data returned: HTTP Status:' + jqXHR.status + "StatusText" + jqXHR.statusText);
                                    break;
                            }
                        }

                        function onError(request, type, errorThrown)
                        {
                            var message = "There was an error with the AJAX request.\n";
                            switch (type) {
                                case 'timeout':
                                    message += "The request timed out.";
                                    break;
                                case 'notmodified':
                                    message += "The request was not modified but was not retrieved from the cache.";
                                    break;
                                case 'parsererror':
                                    message += "XML/Json format is bad.";
                                    break;
                                default:
                                    message += "HTTP Error (" + request.status + " " + request.statusText + ")\n\n." + request.responseText + errorThrown;
                            }
                            $(".alert-error").remove();
                            newAlert('error', message);
                        }

                        function newAlert(type, message)
                        {
                            $("#results").append("<div class='alert alert-block alert-" + type + "' ><strong>" + type.charAt(0).toUpperCase() + type.substr(1) + ": </strong>" + message + "</div>");
                            $(".alert-success").delay(5000).fadeOut("slow", function() {
                                $(".alert-error").remove();
                                $(this).remove();
                            });
                        }

                        $('#logout').bind("click", function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                                data: {action: "logout"},
                                success: function(result) {
                                    window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>";
                                },
                                async: false
                            });
                        });
                    });
                </script>

            </head>

            <body>

                <div class="container">
                    <h2 class="form-heading">Whatsapp! Messenger</h2>
                    <form class="form-horizontal" id="whatsappform">
                        <input type="hidden" name='message' id='message'>
                        <div id="results"></div>
                        <div id="inboundMessage"></div>
                        <fieldset>
                            <div class="control-group">
                                <label class="control-label" for="action">Action</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-wrench"></i></span>
                                        <select id="action" name="action">
                                            <option value="sendMessage">Send a Message</option>
                                            <option value="updateStatus">Update Status</option>
                                            <option value="sendBroadcast">Send Broadcast</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="from">From</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-user"></i></span>
                                        <select id="from" name="from" placeholder="Choose who to send as...">
                                            <option value="">Choose Sender...</option> 
                                            <?php
                                            foreach (array_keys($this->config) as $key) {
                                                if ($key !== 'webpassword') {
                                                    echo "<option value='$key'>$key</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="to">To</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-user"></i></span>
                                        <select id="to" name="to[]" data-placeholder="Choose a person/group..">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="emojicontainer">
                                <ul id="emojiTab" class="nav nav-tabs">
                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">People</a>
                                        <ul class="dropdown-menu">
                                            <img class="emoji emoji-1F604"> <img class="emoji emoji-1F603"> <img class="emoji emoji-1F600"> <img class="emoji emoji-1F60A"> <img class="emoji emoji-263A"> <img class="emoji emoji-1F609"> <img class="emoji emoji-1F60D">
                                            <img class="emoji emoji-1F618"> <img class="emoji emoji-1F61A"> <img class="emoji emoji-1F617"> <img class="emoji emoji-1F619"> <img class="emoji emoji-1F61C"> <img class="emoji emoji-1F61D"> <img class="emoji emoji-1F61B">
                                            <img class="emoji emoji-1F633"> <img class="emoji emoji-1F601"> <img class="emoji emoji-1F614"> <img class="emoji emoji-1F60C"> <img class="emoji emoji-1F612"> <img class="emoji emoji-1F61E"> <img class="emoji emoji-1F623">
                                            <img class="emoji emoji-1F622"> <img class="emoji emoji-1F602"> <img class="emoji emoji-1F62D"> <img class="emoji emoji-1F62A"> <img class="emoji emoji-1F625"> <img class="emoji emoji-1F630"> <img class="emoji emoji-1F605">
                                            <img class="emoji emoji-1F613"> <img class="emoji emoji-1F629"> <img class="emoji emoji-1F62B"> <img class="emoji emoji-1F628"> <img class="emoji emoji-1F631"> <img class="emoji emoji-1F620"> <img class="emoji emoji-1F621">
                                            <img class="emoji emoji-1F624"> <img class="emoji emoji-1F616"> <img class="emoji emoji-1F606"> <img class="emoji emoji-1F60B"> <img class="emoji emoji-1F637"> <img class="emoji emoji-1F60E"> <img class="emoji emoji-1F634">
                                            <img class="emoji emoji-1F635"> <img class="emoji emoji-1F632"> <img class="emoji emoji-1F61F"> <img class="emoji emoji-1F626"> <img class="emoji emoji-1F627"> <img class="emoji emoji-1F608"> <img class="emoji emoji-1F47F">
                                            <img class="emoji emoji-1F62E"> <img class="emoji emoji-1F62C"> <img class="emoji emoji-1F610"> <img class="emoji emoji-1F615"> <img class="emoji emoji-1F62F"> <img class="emoji emoji-1F636"> <img class="emoji emoji-1F607">
                                            <img class="emoji emoji-1F60F"> <img class="emoji emoji-1F611"> <img class="emoji emoji-1F472"> <img class="emoji emoji-1F473"> <img class="emoji emoji-1F46E"> <img class="emoji emoji-1F477"> <img class="emoji emoji-1F482">
                                            <img class="emoji emoji-1F476"> <img class="emoji emoji-1F466"> <img class="emoji emoji-1F467"> <img class="emoji emoji-1F468"> <img class="emoji emoji-1F469"> <img class="emoji emoji-1F474"> <img class="emoji emoji-1F475">
                                            <img class="emoji emoji-1F471"> <img class="emoji emoji-1F47C"> <img class="emoji emoji-1F478"> <img class="emoji emoji-1F63A"> <img class="emoji emoji-1F638"> <img class="emoji emoji-1F63B"> <img class="emoji emoji-1F63D">
                                            <img class="emoji emoji-1F63C"> <img class="emoji emoji-1F640"> <img class="emoji emoji-1F63F"> <img class="emoji emoji-1F639"> <img class="emoji emoji-1F63E"> <img class="emoji emoji-1F479"> <img class="emoji emoji-1F47A">
                                            <img class="emoji emoji-1F648"> <img class="emoji emoji-1F649"> <img class="emoji emoji-1F64A"> <img class="emoji emoji-1F480"> <img class="emoji emoji-1F47D"> <img class="emoji emoji-1F4A9"> <img class="emoji emoji-1F525">
                                            <img class="emoji emoji-2728"> <img class="emoji emoji-1F31F"> <img class="emoji emoji-1F4AB"> <img class="emoji emoji-1F4A5"> <img class="emoji emoji-1F4A2"> <img class="emoji emoji-1F4A6"> <img class="emoji emoji-1F4A7">
                                            <img class="emoji emoji-1F4A4"> <img class="emoji emoji-1F4A8"> <img class="emoji emoji-1F442"> <img class="emoji emoji-1F440"> <img class="emoji emoji-1F443"> <img class="emoji emoji-1F445"> <img class="emoji emoji-1F444">
                                            <img class="emoji emoji-1F44D"> <img class="emoji emoji-1F44E"> <img class="emoji emoji-1F44C"> <img class="emoji emoji-1F44A"> <img class="emoji emoji-270A"> <img class="emoji emoji-270C"> <img class="emoji emoji-1F44B">
                                            <img class="emoji emoji-270B"> <img class="emoji emoji-1F450"> <img class="emoji emoji-1F446"> <img class="emoji emoji-1F447"> <img class="emoji emoji-1F449"> <img class="emoji emoji-1F448"> <img class="emoji emoji-1F64C">
                                            <img class="emoji emoji-1F64F"> <img class="emoji emoji-261D"> <img class="emoji emoji-1F44F"> <img class="emoji emoji-1F4AA"> <img class="emoji emoji-1F6B6"> <img class="emoji emoji-1F3C3"> <img class="emoji emoji-1F483">
                                            <img class="emoji emoji-1F46B"> <img class="emoji emoji-1F46A"> <img class="emoji emoji-1F46C"> <img class="emoji emoji-1F46D"> <img class="emoji emoji-1F48F"> <img class="emoji emoji-1F491"> <img class="emoji emoji-1F46F">
                                            <img class="emoji emoji-1F646"> <img class="emoji emoji-1F645"> <img class="emoji emoji-1F481"> <img class="emoji emoji-1F64B"> <img class="emoji emoji-1F486"> <img class="emoji emoji-1F487"> <img class="emoji emoji-1F485">
                                            <img class="emoji emoji-1F470"> <img class="emoji emoji-1F64E"> <img class="emoji emoji-1F64D"> <img class="emoji emoji-1F647"> <img class="emoji emoji-1F3A9"> <img class="emoji emoji-1F451"> <img class="emoji emoji-1F452">
                                            <img class="emoji emoji-1F45F"> <img class="emoji emoji-1F45E"> <img class="emoji emoji-1F461"> <img class="emoji emoji-1F460"> <img class="emoji emoji-1F462"> <img class="emoji emoji-1F455"> <img class="emoji emoji-1F454">
                                            <img class="emoji emoji-1F45A"> <img class="emoji emoji-1F457"> <img class="emoji emoji-1F3BD"> <img class="emoji emoji-1F456"> <img class="emoji emoji-1F458"> <img class="emoji emoji-1F459"> <img class="emoji emoji-1F4BC">
                                            <img class="emoji emoji-1F45C"> <img class="emoji emoji-1F45D"> <img class="emoji emoji-1F45B"> <img class="emoji emoji-1F453"> <img class="emoji emoji-1F380"> <img class="emoji emoji-1F302"> <img class="emoji emoji-1F484">
                                            <img class="emoji emoji-1F49B"> <img class="emoji emoji-1F499"> <img class="emoji emoji-1F49C"> <img class="emoji emoji-1F49A"> <img class="emoji emoji-2764"> <img class="emoji emoji-1F494"> <img class="emoji emoji-1F497">
                                            <img class="emoji emoji-1F493"> <img class="emoji emoji-1F495"> <img class="emoji emoji-1F496"> <img class="emoji emoji-1F49E"> <img class="emoji emoji-1F498"> <img class="emoji emoji-1F48C"> <img class="emoji emoji-1F48B">
                                            <img class="emoji emoji-1F48D"> <img class="emoji emoji-1F48E"> <img class="emoji emoji-1F464"> <img class="emoji emoji-1F465"> <img class="emoji emoji-1F4AC"> <img class="emoji emoji-1F463"> <img class="emoji emoji-1F4AD">
                                        </ul>
                                    </li>

                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Nature</a>
                                        <ul class="dropdown-menu">
                                            <img class="emoji emoji-1F436"> <img class="emoji emoji-1F43A"> <img class="emoji emoji-1F431"> <img class="emoji emoji-1F42D"> <img class="emoji emoji-1F439"> <img class="emoji emoji-1F430"> <img class="emoji emoji-1F438">
                                            <img class="emoji emoji-1F42F"> <img class="emoji emoji-1F428"> <img class="emoji emoji-1F43B"> <img class="emoji emoji-1F437"> <img class="emoji emoji-1F43D"> <img class="emoji emoji-1F42E"> <img class="emoji emoji-1F417">
                                            <img class="emoji emoji-1F435"> <img class="emoji emoji-1F412"> <img class="emoji emoji-1F434"> <img class="emoji emoji-1F411"> <img class="emoji emoji-1F418"> <img class="emoji emoji-1F43C"> <img class="emoji emoji-1F427">
                                            <img class="emoji emoji-1F426"> <img class="emoji emoji-1F424"> <img class="emoji emoji-1F425"> <img class="emoji emoji-1F423"> <img class="emoji emoji-1F414"> <img class="emoji emoji-1F40D"> <img class="emoji emoji-1F422">
                                            <img class="emoji emoji-1F41B"> <img class="emoji emoji-1F41D"> <img class="emoji emoji-1F41C"> <img class="emoji emoji-1F41E"> <img class="emoji emoji-1F40C"> <img class="emoji emoji-1F419"> <img class="emoji emoji-1F41A">
                                            <img class="emoji emoji-1F420"> <img class="emoji emoji-1F41F"> <img class="emoji emoji-1F42C"> <img class="emoji emoji-1F433"> <img class="emoji emoji-1F40B"> <img class="emoji emoji-1F404"> <img class="emoji emoji-1F40F">
                                            <img class="emoji emoji-1F400"> <img class="emoji emoji-1F403"> <img class="emoji emoji-1F405"> <img class="emoji emoji-1F407"> <img class="emoji emoji-1F409"> <img class="emoji emoji-1F40E"> <img class="emoji emoji-1F410">
                                            <img class="emoji emoji-1F413"> <img class="emoji emoji-1F415"> <img class="emoji emoji-1F416"> <img class="emoji emoji-1F401"> <img class="emoji emoji-1F402"> <img class="emoji emoji-1F432"> <img class="emoji emoji-1F421">
                                            <img class="emoji emoji-1F40A"> <img class="emoji emoji-1F42B"> <img class="emoji emoji-1F42A"> <img class="emoji emoji-1F406"> <img class="emoji emoji-1F408"> <img class="emoji emoji-1F429"> <img class="emoji emoji-1F43E">
                                            <img class="emoji emoji-1F490"> <img class="emoji emoji-1F338"> <img class="emoji emoji-1F337"> <img class="emoji emoji-1F340"> <img class="emoji emoji-1F339"> <img class="emoji emoji-1F33B"> <img class="emoji emoji-1F33A">
                                            <img class="emoji emoji-1F341"> <img class="emoji emoji-1F343"> <img class="emoji emoji-1F342"> <img class="emoji emoji-1F33F"> <img class="emoji emoji-1F33E"> <img class="emoji emoji-1F344"> <img class="emoji emoji-1F335">
                                            <img class="emoji emoji-1F334"> <img class="emoji emoji-1F332"> <img class="emoji emoji-1F333"> <img class="emoji emoji-1F330"> <img class="emoji emoji-1F331"> <img class="emoji emoji-1F33C"> <img class="emoji emoji-1F310">
                                            <img class="emoji emoji-1F31E"> <img class="emoji emoji-1F31D"> <img class="emoji emoji-1F31A"> <img class="emoji emoji-1F311"> <img class="emoji emoji-1F312"> <img class="emoji emoji-1F313"> <img class="emoji emoji-1F314">
                                            <img class="emoji emoji-1F315"> <img class="emoji emoji-1F316"> <img class="emoji emoji-1F317"> <img class="emoji emoji-1F318"> <img class="emoji emoji-1F31C"> <img class="emoji emoji-1F31B"> <img class="emoji emoji-1F319">
                                            <img class="emoji emoji-1F30D"> <img class="emoji emoji-1F30E"> <img class="emoji emoji-1F30F"> <img class="emoji emoji-1F30B"> <img class="emoji emoji-1F30C"> <img class="emoji emoji-1F320"> <img class="emoji emoji-2B50">
                                            <img class="emoji emoji-2600"> <img class="emoji emoji-26C5"> <img class="emoji emoji-2601"> <img class="emoji emoji-26A1"> <img class="emoji emoji-2614"> <img class="emoji emoji-2744"> <img class="emoji emoji-26C4">
                                            <img class="emoji emoji-1F300"> <img class="emoji emoji-1F301"> <img class="emoji emoji-1F308"> <img class="emoji emoji-1F30A">
                                        </ul>
                                    </li>

                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Objects</a>
                                        <ul class="dropdown-menu">
                                            <img class="emoji emoji-1F38D"> <img class="emoji emoji-1F49D"> <img class="emoji emoji-1F38E"> <img class="emoji emoji-1F392"> <img class="emoji emoji-1F393"> <img class="emoji emoji-1F38F"> <img class="emoji emoji-1F386">
                                            <img class="emoji emoji-1F387"> <img class="emoji emoji-1F390"> <img class="emoji emoji-1F391"> <img class="emoji emoji-1F383"> <img class="emoji emoji-1F47B"> <img class="emoji emoji-1F385"> <img class="emoji emoji-1F384">
                                            <img class="emoji emoji-1F381"> <img class="emoji emoji-1F38B"> <img class="emoji emoji-1F389"> <img class="emoji emoji-1F38A"> <img class="emoji emoji-1F388"> <img class="emoji emoji-1F38C"> <img class="emoji emoji-1F52E">
                                            <img class="emoji emoji-1F3A5"> <img class="emoji emoji-1F4F7"> <img class="emoji emoji-1F4F9"> <img class="emoji emoji-1F4FC"> <img class="emoji emoji-1F4BF"> <img class="emoji emoji-1F4C0"> <img class="emoji emoji-1F4BD">
                                            <img class="emoji emoji-1F4BE"> <img class="emoji emoji-1F4BB"> <img class="emoji emoji-1F4F1"> <img class="emoji emoji-260E"> <img class="emoji emoji-1F4DE"> <img class="emoji emoji-1F4DF"> <img class="emoji emoji-1F4E0">
                                            <img class="emoji emoji-1F4E1"> <img class="emoji emoji-1F4FA"> <img class="emoji emoji-1F4FB"> <img class="emoji emoji-1F50A"> <img class="emoji emoji-1F509"> <img class="emoji emoji-1F508"> <img class="emoji emoji-1F507">
                                            <img class="emoji emoji-1F514"> <img class="emoji emoji-1F515"> <img class="emoji emoji-1F4E2"> <img class="emoji emoji-1F4E3"> <img class="emoji emoji-23F3"> <img class="emoji emoji-231B"> <img class="emoji emoji-23F0">
                                            <img class="emoji emoji-231A"> <img class="emoji emoji-1F513"> <img class="emoji emoji-1F512"> <img class="emoji emoji-1F50F"> <img class="emoji emoji-1F510"> <img class="emoji emoji-1F511"> <img class="emoji emoji-1F50E">
                                            <img class="emoji emoji-1F4A1"> <img class="emoji emoji-1F526"> <img class="emoji emoji-1F506"> <img class="emoji emoji-1F505"> <img class="emoji emoji-1F50C"> <img class="emoji emoji-1F50B"> <img class="emoji emoji-1F50D">
                                            <img class="emoji emoji-1F6C1"> <img class="emoji emoji-1F6C0"> <img class="emoji emoji-1F6BF"> <img class="emoji emoji-1F6BD"> <img class="emoji emoji-1F527"> <img class="emoji emoji-1F529"> <img class="emoji emoji-1F528">
                                            <img class="emoji emoji-1F6AA"> <img class="emoji emoji-1F6AC"> <img class="emoji emoji-1F4A3"> <img class="emoji emoji-1F52B"> <img class="emoji emoji-1F52A"> <img class="emoji emoji-1F48A"> <img class="emoji emoji-1F489">
                                            <img class="emoji emoji-1F4B0"> <img class="emoji emoji-1F4B4"> <img class="emoji emoji-1F4B5"> <img class="emoji emoji-1F4B7"> <img class="emoji emoji-1F4B6"> <img class="emoji emoji-1F4B3"> <img class="emoji emoji-1F4B8">
                                            <img class="emoji emoji-1F4F2"> <img class="emoji emoji-1F4E7"> <img class="emoji emoji-1F4E5"> <img class="emoji emoji-1F4E4"> <img class="emoji emoji-2709"> <img class="emoji emoji-1F4E9"> <img class="emoji emoji-1F4E8">
                                            <img class="emoji emoji-1F4EF"> <img class="emoji emoji-1F4EB"> <img class="emoji emoji-1F4EA"> <img class="emoji emoji-1F4EC"> <img class="emoji emoji-1F4ED"> <img class="emoji emoji-1F4EE"> <img class="emoji emoji-1F4E6">
                                            <img class="emoji emoji-1F4DD"> <img class="emoji emoji-1F4C4"> <img class="emoji emoji-1F4C3"> <img class="emoji emoji-1F4D1"> <img class="emoji emoji-1F4CA"> <img class="emoji emoji-1F4C8"> <img class="emoji emoji-1F4C9">
                                            <img class="emoji emoji-1F4DC"> <img class="emoji emoji-1F4CB"> <img class="emoji emoji-1F4C5"> <img class="emoji emoji-1F4C6"> <img class="emoji emoji-1F4C7"> <img class="emoji emoji-1F4C1"> <img class="emoji emoji-1F4C2">
                                            <img class="emoji emoji-2702"> <img class="emoji emoji-1F4CC"> <img class="emoji emoji-1F4CE"> <img class="emoji emoji-2712"> <img class="emoji emoji-270F"> <img class="emoji emoji-1F4CF"> <img class="emoji emoji-1F4D0">
                                            <img class="emoji emoji-1F4D5"> <img class="emoji emoji-1F4D7"> <img class="emoji emoji-1F4D8"> <img class="emoji emoji-1F4D9"> <img class="emoji emoji-1F4D3"> <img class="emoji emoji-1F4D4"> <img class="emoji emoji-1F4D2">
                                            <img class="emoji emoji-1F4DA"> <img class="emoji emoji-1F4D6"> <img class="emoji emoji-1F516"> <img class="emoji emoji-1F4DB"> <img class="emoji emoji-1F52C"> <img class="emoji emoji-1F52D"> <img class="emoji emoji-1F4F0">
                                            <img class="emoji emoji-1F3A8"> <img class="emoji emoji-1F3AC"> <img class="emoji emoji-1F3A4"> <img class="emoji emoji-1F3A7"> <img class="emoji emoji-1F3BC"> <img class="emoji emoji-1F3B5"> <img class="emoji emoji-1F3B6">
                                            <img class="emoji emoji-1F3B9"> <img class="emoji emoji-1F3BB"> <img class="emoji emoji-1F3BA"> <img class="emoji emoji-1F3B7"> <img class="emoji emoji-1F3B8"> <img class="emoji emoji-1F47E"> <img class="emoji emoji-1F3AE">
                                            <img class="emoji emoji-1F0CF"> <img class="emoji emoji-1F3B4"> <img class="emoji emoji-1F004"> <img class="emoji emoji-1F3B2"> <img class="emoji emoji-1F3AF"> <img class="emoji emoji-1F3C8"> <img class="emoji emoji-1F3C0">
                                            <img class="emoji emoji-26BD"> <img class="emoji emoji-26BE"> <img class="emoji emoji-1F3BE"> <img class="emoji emoji-1F3B1"> <img class="emoji emoji-1F3C9"> <img class="emoji emoji-1F3B3"> <img class="emoji emoji-26F3">
                                            <img class="emoji emoji-1F6B5"> <img class="emoji emoji-1F6B4"> <img class="emoji emoji-1F3C1"> <img class="emoji emoji-1F3C7"> <img class="emoji emoji-1F3C6"> <img class="emoji emoji-1F3BF"> <img class="emoji emoji-1F3C2">
                                            <img class="emoji emoji-1F3CA"> <img class="emoji emoji-1F3C4"> <img class="emoji emoji-1F3A3"> <img class="emoji emoji-2615"> <img class="emoji emoji-1F375"> <img class="emoji emoji-1F376"> <img class="emoji emoji-1F37C">
                                            <img class="emoji emoji-1F37A"> <img class="emoji emoji-1F37B"> <img class="emoji emoji-1F378"> <img class="emoji emoji-1F379"> <img class="emoji emoji-1F377"> <img class="emoji emoji-1F374"> <img class="emoji emoji-1F355">
                                            <img class="emoji emoji-1F354"> <img class="emoji emoji-1F35F"> <img class="emoji emoji-1F357"> <img class="emoji emoji-1F356"> <img class="emoji emoji-1F35D"> <img class="emoji emoji-1F35B"> <img class="emoji emoji-1F364">
                                            <img class="emoji emoji-1F371"> <img class="emoji emoji-1F363"> <img class="emoji emoji-1F365"> <img class="emoji emoji-1F359"> <img class="emoji emoji-1F358"> <img class="emoji emoji-1F35A"> <img class="emoji emoji-1F35C">
                                            <img class="emoji emoji-1F372"> <img class="emoji emoji-1F362"> <img class="emoji emoji-1F361"> <img class="emoji emoji-1F373"> <img class="emoji emoji-1F35E"> <img class="emoji emoji-1F369"> <img class="emoji emoji-1F36E">
                                            <img class="emoji emoji-1F366"> <img class="emoji emoji-1F368"> <img class="emoji emoji-1F367"> <img class="emoji emoji-1F382"> <img class="emoji emoji-1F370"> <img class="emoji emoji-1F36A"> <img class="emoji emoji-1F36B">
                                            <img class="emoji emoji-1F36C"> <img class="emoji emoji-1F36D"> <img class="emoji emoji-1F36F"> <img class="emoji emoji-1F34E"> <img class="emoji emoji-1F34F"> <img class="emoji emoji-1F34A"> <img class="emoji emoji-1F34B">
                                            <img class="emoji emoji-1F352"> <img class="emoji emoji-1F347"> <img class="emoji emoji-1F349"> <img class="emoji emoji-1F353"> <img class="emoji emoji-1F351"> <img class="emoji emoji-1F348"> <img class="emoji emoji-1F34C">
                                            <img class="emoji emoji-1F350"> <img class="emoji emoji-1F34D"> <img class="emoji emoji-1F360"> <img class="emoji emoji-1F346"> <img class="emoji emoji-1F345"> <img class="emoji emoji-1F33D">
                                        </ul>
                                    </li>

                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Places</a>
                                        <ul class="dropdown-menu">
                                            <img class="emoji emoji-1F3E0"> <img class="emoji emoji-1F3E1"> <img class="emoji emoji-1F3EB"> <img class="emoji emoji-1F3E2"> <img class="emoji emoji-1F3E3"> <img class="emoji emoji-1F3E5"> <img class="emoji emoji-1F3E6">
                                            <img class="emoji emoji-1F3EA"> <img class="emoji emoji-1F3E9"> <img class="emoji emoji-1F3E8"> <img class="emoji emoji-1F492"> <img class="emoji emoji-26EA"> <img class="emoji emoji-1F3EC"> <img class="emoji emoji-1F3E4">
                                            <img class="emoji emoji-1F307"> <img class="emoji emoji-1F306"> <img class="emoji emoji-1F3EF"> <img class="emoji emoji-1F3F0"> <img class="emoji emoji-26FA"> <img class="emoji emoji-1F3ED"> <img class="emoji emoji-1F5FC">
                                            <img class="emoji emoji-1F5FE"> <img class="emoji emoji-1F5FB"> <img class="emoji emoji-1F304"> <img class="emoji emoji-1F305"> <img class="emoji emoji-1F303"> <img class="emoji emoji-1F5FD"> <img class="emoji emoji-1F309">
                                            <img class="emoji emoji-1F3A0"> <img class="emoji emoji-1F3A1"> <img class="emoji emoji-26F2"> <img class="emoji emoji-1F3A2"> <img class="emoji emoji-1F6A2"> <img class="emoji emoji-26F5"> <img class="emoji emoji-1F6A4">
                                            <img class="emoji emoji-1F6A3"> <img class="emoji emoji-2693"> <img class="emoji emoji-1F680"> <img class="emoji emoji-2708"> <img class="emoji emoji-1F4BA"> <img class="emoji emoji-1F681"> <img class="emoji emoji-1F682">
                                            <img class="emoji emoji-1F68A"> <img class="emoji emoji-1F689"> <img class="emoji emoji-1F69E"> <img class="emoji emoji-1F686"> <img class="emoji emoji-1F684"> <img class="emoji emoji-1F685"> <img class="emoji emoji-1F688">
                                            <img class="emoji emoji-1F687"> <img class="emoji emoji-1F69D"> <img class="emoji emoji-1F68B"> <img class="emoji emoji-1F683"> <img class="emoji emoji-1F68E"> <img class="emoji emoji-1F68C"> <img class="emoji emoji-1F68D">
                                            <img class="emoji emoji-1F699"> <img class="emoji emoji-1F698"> <img class="emoji emoji-1F697"> <img class="emoji emoji-1F695"> <img class="emoji emoji-1F696"> <img class="emoji emoji-1F69B"> <img class="emoji emoji-1F69A">
                                            <img class="emoji emoji-1F6A8"> <img class="emoji emoji-1F693"> <img class="emoji emoji-1F694"> <img class="emoji emoji-1F692"> <img class="emoji emoji-1F691"> <img class="emoji emoji-1F690"> <img class="emoji emoji-1F6B2">
                                            <img class="emoji emoji-1F6A1"> <img class="emoji emoji-1F69F"> <img class="emoji emoji-1F6A0"> <img class="emoji emoji-1F69C"> <img class="emoji emoji-1F488"> <img class="emoji emoji-1F68F"> <img class="emoji emoji-1F3AB">
                                            <img class="emoji emoji-1F6A6"> <img class="emoji emoji-1F6A5"> <img class="emoji emoji-26A0"> <img class="emoji emoji-1F6A7"> <img class="emoji emoji-1F530"> <img class="emoji emoji-26FD"> <img class="emoji emoji-1F3EE">
                                            <img class="emoji emoji-1F3B0"> <img class="emoji emoji-2668"> <img class="emoji emoji-1F5FF"> <img class="emoji emoji-1F3AA"> <img class="emoji emoji-1F3AD"> <img class="emoji emoji-1F4CD"> <img class="emoji emoji-1F6A9">
                                            <img class="emoji emoji-1F1EF_1F1F5"> <img class="emoji emoji-1F1F0_1F1F7"> <img class="emoji emoji-1F1E9_1F1EA"> <img class="emoji emoji-1F1E8_1F1F3"> <img class="emoji emoji-1F1FA_1F1F8"> <img class="emoji emoji-1F1EB_1F1F7">
                                            <img class="emoji emoji-1F1EA_1F1F8"> <img class="emoji emoji-1F1EE_1F1F9"> <img class="emoji emoji-1F1F7_1F1FA"> <img class="emoji emoji-1F1EC_1F1E7">
                                        </ul>
                                    </li>

                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Symbols</a>
                                        <ul class="dropdown-menu">
                                            <img class="emoji emoji-0031_20E3"> <img class="emoji emoji-0032_20E3"> <img class="emoji emoji-0033_20E3"> <img class="emoji emoji-0034_20E3"> <img class="emoji emoji-0035_20E3"> <img class="emoji emoji-0036_20E3"> <img class="emoji emoji-0037_20E3">
                                            <img class="emoji emoji-0038_20E3"> <img class="emoji emoji-0039_20E3"> <img class="emoji emoji-0030_20E3"> <img class="emoji emoji-1F51F"> <img class="emoji emoji-1F522"> <img class="emoji emoji-0023_20E3"> <img class="emoji emoji-1F523">
                                            <img class="emoji emoji-2B06"> <img class="emoji emoji-2B07"> <img class="emoji emoji-2B05"> <img class="emoji emoji-27A1"> <img class="emoji emoji-1F520"> <img class="emoji emoji-1F521"> <img class="emoji emoji-1F524">
                                            <img class="emoji emoji-2197"> <img class="emoji emoji-2196"> <img class="emoji emoji-2198"> <img class="emoji emoji-2199"> <img class="emoji emoji-2194"> <img class="emoji emoji-2195"> <img class="emoji emoji-1F504">
                                            <img class="emoji emoji-25C0"> <img class="emoji emoji-25B6"> <img class="emoji emoji-1F53C"> <img class="emoji emoji-1F53D"> <img class="emoji emoji-21A9"> <img class="emoji emoji-21AA"> <img class="emoji emoji-2139">
                                            <img class="emoji emoji-23EA"> <img class="emoji emoji-23E9"> <img class="emoji emoji-23EB"> <img class="emoji emoji-23EC"> <img class="emoji emoji-2935"> <img class="emoji emoji-2934"> <img class="emoji emoji-1F197">
                                            <img class="emoji emoji-1F500"> <img class="emoji emoji-1F501"> <img class="emoji emoji-1F502"> <img class="emoji emoji-1F195"> <img class="emoji emoji-1F199"> <img class="emoji emoji-1F192"> <img class="emoji emoji-1F193">
                                            <img class="emoji emoji-1F196"> <img class="emoji emoji-1F4F6"> <img class="emoji emoji-1F3A6"> <img class="emoji emoji-1F201"> <img class="emoji emoji-1F22F"> <img class="emoji emoji-1F233"> <img class="emoji emoji-1F235">
                                            <img class="emoji emoji-1F234"> <img class="emoji emoji-1F232"> <img class="emoji emoji-1F250"> <img class="emoji emoji-1F239"> <img class="emoji emoji-1F23A"> <img class="emoji emoji-1F236"> <img class="emoji emoji-1F21A">
                                            <img class="emoji emoji-1F6BB"> <img class="emoji emoji-1F6B9"> <img class="emoji emoji-1F6BA"> <img class="emoji emoji-1F6BC"> <img class="emoji emoji-1F6BE"> <img class="emoji emoji-1F6B0"> <img class="emoji emoji-1F6AE">
                                            <img class="emoji emoji-1F17F"> <img class="emoji emoji-267F"> <img class="emoji emoji-1F6AD"> <img class="emoji emoji-1F237"> <img class="emoji emoji-1F238"> <img class="emoji emoji-1F202"> <img class="emoji emoji-24C2">
                                            <img class="emoji emoji-1F6C2"> <img class="emoji emoji-1F6C4"> <img class="emoji emoji-1F6C5"> <img class="emoji emoji-1F6C3"> <img class="emoji emoji-1F251"> <img class="emoji emoji-3299"> <img class="emoji emoji-3297">
                                            <img class="emoji emoji-1F191"> <img class="emoji emoji-1F198"> <img class="emoji emoji-1F194"> <img class="emoji emoji-1F6AB"> <img class="emoji emoji-1F51E"> <img class="emoji emoji-1F4F5"> <img class="emoji emoji-1F6AF">
                                            <img class="emoji emoji-1F6B1"> <img class="emoji emoji-1F6B3"> <img class="emoji emoji-1F6B7"> <img class="emoji emoji-1F6B8"> <img class="emoji emoji-26D4"> <img class="emoji emoji-2733"> <img class="emoji emoji-2747">
                                            <img class="emoji emoji-274E"> <img class="emoji emoji-2705"> <img class="emoji emoji-2734"> <img class="emoji emoji-1F49F"> <img class="emoji emoji-1F19A"> <img class="emoji emoji-1F4F3"> <img class="emoji emoji-1F4F4">
                                            <img class="emoji emoji-1F170"> <img class="emoji emoji-1F171"> <img class="emoji emoji-1F18E"> <img class="emoji emoji-1F17E"> <img class="emoji emoji-1F4A0"> <img class="emoji emoji-27BF"> <img class="emoji emoji-267B">
                                            <img class="emoji emoji-2648"> <img class="emoji emoji-2649"> <img class="emoji emoji-264A"> <img class="emoji emoji-264B"> <img class="emoji emoji-264C"> <img class="emoji emoji-264D"> <img class="emoji emoji-264E">
                                            <img class="emoji emoji-264F"> <img class="emoji emoji-2650"> <img class="emoji emoji-2651"> <img class="emoji emoji-2652"> <img class="emoji emoji-2653"> <img class="emoji emoji-26CE"> <img class="emoji emoji-1F52F">
                                            <img class="emoji emoji-1F3E7"> <img class="emoji emoji-1F4B9"> <img class="emoji emoji-1F4B2"> <img class="emoji emoji-1F4B1"> <img class="emoji emoji-00A9"> <img class="emoji emoji-00AE"> <img class="emoji emoji-2122">
                                            <img class="emoji emoji-303D"> <img class="emoji emoji-3030"> <img class="emoji emoji-1F51D"> <img class="emoji emoji-1F51A"> <img class="emoji emoji-1F519"> <img class="emoji emoji-1F51B"> <img class="emoji emoji-1F51C">
                                            <img class="emoji emoji-274C">  <img class="emoji emoji-2B55"> <img class="emoji emoji-2757"> <img class="emoji emoji-2753"> <img class="emoji emoji-2755"> <img class="emoji emoji-2754"> <img class="emoji emoji-1F503">
                                            <img class="emoji emoji-1F55B"> <img class="emoji emoji-1F567"> <img class="emoji emoji-1F550"> <img class="emoji emoji-1F55C"> <img class="emoji emoji-1F551"> <img class="emoji emoji-1F55D"> <img class="emoji emoji-1F552">
                                            <img class="emoji emoji-1F55E"> <img class="emoji emoji-1F553"> <img class="emoji emoji-1F55F"> <img class="emoji emoji-1F554"> <img class="emoji emoji-1F560"> <img class="emoji emoji-1F555"> <img class="emoji emoji-1F556">
                                            <img class="emoji emoji-1F557"> <img class="emoji emoji-1F558"> <img class="emoji emoji-1F559"> <img class="emoji emoji-1F55A"> <img class="emoji emoji-1F561"> <img class="emoji emoji-1F562"> <img class="emoji emoji-1F563">
                                            <img class="emoji emoji-1F564"> <img class="emoji emoji-1F565"> <img class="emoji emoji-1F566"> <img class="emoji emoji-2716"> <img class="emoji emoji-2795"> <img class="emoji emoji-2796"> <img class="emoji emoji-2797">
                                            <img class="emoji emoji-2660"> <img class="emoji emoji-2665"> <img class="emoji emoji-2663"> <img class="emoji emoji-2666"> <img class="emoji emoji-1F4AE"> <img class="emoji emoji-1F4AF"> <img class="emoji emoji-2714">
                                            <img class="emoji emoji-2611"> <img class="emoji emoji-1F518"> <img class="emoji emoji-1F517"> <img class="emoji emoji-27B0"> <img class="emoji emoji-1F531"> <img class="emoji emoji-1F532"> <img class="emoji emoji-1F533">
                                            <img class="emoji emoji-25FC"> <img class="emoji emoji-25FB"> <img class="emoji emoji-25FE"> <img class="emoji emoji-25FD"> <img class="emoji emoji-25AA"> <img class="emoji emoji-25AB"> <img class="emoji emoji-1F53A">
                                            <img class="emoji emoji-2B1C"> <img class="emoji emoji-2B1B"> <img class="emoji emoji-26AB"> <img class="emoji emoji-26AA"> <img class="emoji emoji-1F534"> <img class="emoji emoji-1F535"> <img class="emoji emoji-1F53B">
                                            <img class="emoji emoji-1F536"> <img class="emoji emoji-1F537"> <img class="emoji emoji-1F538"> <img class="emoji emoji-1F539">
                                            <img class="emoji emoji-2049"> <img class="emoji emoji-203C">
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="faketextbox">Msg</label>
                                <div class="controls">
                                    <div contenteditable="true" id="faketextbox" name="faketextbox"></div>
                                </div>
                            </div>
                            <div class="control-group" style="display:none">
                                <label class="control-label" for="status">Status</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-info-sign"></i></span>
                                        <input type="text" id="status" name="status" placeholder="What's your status?" disabled="disabled" >
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="image">Image</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-picture"></i></span>
                                        <input type="text" id="image" name="image" placeholder="Enter a URL...">
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="audio">Audio</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-music"></i></span>
                                        <input type="text" id="audio" name="audio" placeholder="Enter a URL...">
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="video">Video</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-facetime-video"></i></span>
                                        <input type="text" id="video" name="video" placeholder="Enter a URL...">
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="video">Location</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-globe"></i></span>
                                        <input type="text" id="locationname" name="locationname" placeholder="Name of Location...">
                                        <div id='latlongcontainer'>
                                            <span class="add-on"><i class="icon-globe"></i></span>
                                            <input type="text" id="userlat" name="userlat" placeholder="Latitude...">
                                            <input type="text" id="userlong" name="userlong" placeholder="Longitude...">
                                            <button id='pickLocation' type='button' class='btn'><i class='icon-screenshot'></i></button>
                                        </div>
                                        <div id="mapContainer">
                                            <input id="target" type="text" placeholder="Type Address">
                                            <div id="map_canvas"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls" id="formcontrols">
                                    <button id="submit" type="submit" class="btn btn-primary">Send</button>
                                    <button id="logout" type="button" class="btn btn-danger">Log Out</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div> <!-- /container -->
            </body>
        </html>
        <?php

        return ob_get_clean();
    }

}
