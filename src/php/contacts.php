<?php

//ported from yowsup
//
//usage:
//$wasync = new WhatsAppContactSync($username, $password, contacts);
//$wacontacts = $wasync->executeSync();
//
//$username = phonenumber
//$password = base64 encoded password
//$contact = single phonenumber or array of phonenumbers
//
//this class will only return existing whatsapp accounts
//return value on success example:
//     array (size=4)
//        0 =>
//          array (size=3)
//            'phonenumber' => string '31641xxxxxx' (length=11)
//            'status' => string 'Hey there! I am using WhatsApp.' (length=31)
//            'lastupdate' => int 1365456759
//        1 =>
//          array (size=3)
//            'phonenumber' => string '31629xxxxxx' (length=11)
//            'status' => string 'Beschikbaar' (length=11)
//            'lastupdate' => int 1340793460
//        2 =>
//          array (size=3)
//            'phonenumber' => string '31620xxxxxx' (length=11)
//            'status' => string 'Online' (length=6)
//            'lastupdate' => int 1345740390
//        3 =>
//          array (size=3)
//            'phonenumber' => string '31614xxxxxx' (length=11)
//            'status' => string 'Here comes the kraken!' (length=22)
//            'lastupdate' => int 1362736455
//
//
class WhatsAppContactSync
{
    protected $username;
    protected $password;
    protected $contacts = array();
    protected $debug = false;

    protected function getCnonce()
    {
        //generate random 10char string
        return substr(md5(microtime()), 0, 10);
    }

    protected function getHeaders($nonce = 0, $contentLength = 0)
    {
        //get HTTP headers
        $headers = array(
            "User-Agent: WhatsApp/2.4.7 S40Version/14.26 Device/Nokia302",
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: " . $this->generateAuth($nonce),
            'Accept-Encoding: identity',
            "Content-Length: $contentLength"
        );

        return $headers;
    }

    protected function generateAuth($nonce = 0)
    {
        //generate auth string
        $cnonce = $this->getCnonce();
        $nc = "00000001";
        $digestUri = "WAWA/s.whatsapp.net";
        $credentials = $this->username . ":s.whatsapp.net:";
        $credentials .= $this->password;
        $response = md5(md5(md5($credentials, true) . ":$nonce:" . $cnonce) . ":$nonce:" . $nc . ":" . $cnonce . ":auth:" . md5("AUTHENTICATE:" . $digestUri));

        return "X-WAWA:username=\"" . $this->username . "\",realm=\"s.whatsapp.net\",nonce=\"$nonce\",cnonce=\"$cnonce\",nc=\"$nc\",qop=\"auth\",digest-uri=\"$digestUri\",response=\"$response\",charset=\"utf-8\"";
    }

    protected function curlRequest($url, $headers, $postfields = false)
    {
        //execute curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($postfields) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    protected function processCurlResponse($result)
    {
        //process curl response
        $return = array();
        $return["data"] = $result;

        $lines = explode("\n", $result);
        foreach ($lines as $line) {
            if (stristr($line, "WWW-Authenticate")) {
                //auth response
                $i = strpos($line, "nonce=");
                $nonce = substr($line, $i + 7);
                $nonce = substr($nonce, 0, (strlen($nonce) - 2));
                $return["nonce"] = $nonce;
            }
            if (stristr($line, '{"message":')) {
                //success message
                $message = json_decode($line);
                $message = $message->message;
                $return["message"] = $message;
            }
            if (stristr($line, '{"c":')) {
                //contacts
                $obj = json_decode($line);
                $return["obj"] = $obj;
            }
            if (stristr($line, '{"error":')) {
                //error message
                $message = json_decode($line);
                $message = $message->error;
                $return["error"] = $message;
            }
        }

        return $return;
    }

    public function __construct($username, $password, $contact, $debug = false)
    {
        $this->username = $username;
        $this->password = base64_decode($password);
        if (!is_array($contact)) {
            //single contact
            $contact = array($contact);
        }
        $this->contacts = $contact;
        $this->debug = $debug;
    }

    public function executeSync()
    {
        //main method!
        //get auth
        $url = "https://sro.whatsapp.net/v2/sync/a";
        $headers = $this->getHeaders();
        $result = $this->curlRequest($url, $headers);
        $result = $this->processCurlResponse($result);
        if (isset($result["message"]) && $result["message"] == "next token" && isset($result["nonce"])) {
            //success
            $url = "https://sro.whatsapp.net/v2/sync/q";
            $postfields = "ut=all&t=c";
            foreach ($this->contacts as $contact) {
                if (!stristr($contact, "+")) {
                    //automatically add leading plus sign
                    $contact = "+" . $contact;
                }
                $postfields .= "&u[]=" . urlencode($contact);
            }
            $headers = $this->getHeaders($result["nonce"], strlen($postfields));
            $result = $this->curlRequest($url, $headers, $postfields);
            $result = $this->processCurlResponse($result);
            if (isset($result["obj"])) {
                //succes!
                return($this->processJSONResponse($result["obj"]));
            } elseif (isset($result["message"])) {
                if ($this->debug) {
                    throw new Exception("Received unexpected message: " . $result["message"]);
                }
                return $result["message"];
            } elseif (isset($result["error"])) {
                if ($this->debug) {
                    throw new Exception("Received error: " . $result["error"]);
                }
                return $result["error"];
            } else {
                if ($this->debug) {
                    throw new Exception("Received unknown response: " . print_r($result["data"], true));
                }
                return false;
            }
        } elseif (isset($result["error"])) {
            //error
            if ($this->debug) {
                throw new Exception("Received error: " . $result["error"]);
            }
            return $result["error"];
        } else {
            if ($this->debug) {
                throw new Exception("Received unknown response: " . print_r($result["data"], true));
            }
            return false;
        }
    }

    protected function processJSONResponse($json)
    {
        //process decoded JSON object
        $contacts = $json->c;
        $_contacts = array();
        foreach ($contacts as $contact) {
            if ($contact->w == 1) {
                $_contact = array(
                    "phonenumber" => $contact->n,
                    "status" => $contact->s,
                    "lastupdate" => $contact->t
                );
                $_contacts[] = $_contact;
            }
        }

        return $_contacts;
    }

}

?>
