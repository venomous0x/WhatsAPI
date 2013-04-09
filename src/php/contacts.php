<?php
//ported from yowsup
//
//usage:
//$wasync = new WhatsAppContactSync($username, $password, contacts);
//$wacontacts = $wasync->executeSync();
//
//$username = phonenumber (*see NOTE*)
//$password = base64 encoded password
//$contact = single phonenumber or array of phonenumbers
//
//NOTE:
//contact phonenumber must be either without cc or with cc and leading +
//e.g.
//  "650568134" (will use same country code as you)
//  or
//  "+31650568134" (uses specified country code [NL])
//
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
    protected $_username;
    protected $_password;
    protected $_contacts = array();
    
    protected function _getCnonce()
    {
        //generate random 10char string
        return substr(md5(microtime()), 0, 10);
    }
    
    protected function _getHeaders($nonce = 0, $contentLength = 0)
    {
        //get HTTP headers
        $headers = array(
            "User-Agent: WhatsApp/2.4.7 S40Version/14.26 Device/Nokia302",
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: " . $this->_generateAuth($nonce),
            'Accept-Encoding: identity',
            "Content-Length: $contentLength"
        );
        return $headers;
    }
    
    protected function _generateAuth($nonce = 0)
    {
        //generate auth string
        $cnonce = $this->_getCnonce();
        $nc = "00000001";
        $realm = "s.whatsapp.net";
        $qop = "auth";
        $digestUri = "WAWA/s.whatsapp.net";
        $charSet = "utf-8";
        $authMethod = "X-WAWA";
        $credentials = $this->_username . ":s.whatsapp.net:";
        $credentials .= $this->_password;
        $response = md5(md5(md5($credentials, true) . ":$nonce:" . $cnonce) . ":$nonce:" . $nc . ":" . $cnonce . ":auth:" . md5("AUTHENTICATE:" . $digestUri));
        return "$authMethod:username=\"" . $this->_username . "\",realm=\"$realm\",nonce=\"$nonce\",cnonce=\"$cnonce\",nc=\"$nc\",qop=\"auth\",digest-uri=\"$digestUri\",response=\"$response\",charset=\"utf-8\"";
    }
    
    protected function _curlRequest($url, $headers, $postfields = false)
    {
        //execute curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if($postfields)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    protected function _processCurlResponse($result)
    {
        //process curl response
        $return = array();
        
        $lines = explode("\n", $result);
        foreach($lines as $line)
        {
            if(stristr($line, "WWW-Authenticate"))
            {
                //auth response
                $i = strpos($line, "nonce=");
                $nonce = substr($line, $i + 7);
                $nonce = substr($nonce, 0, (strlen($nonce) - 2));
                $return["nonce"] = $nonce;
            }
            if(stristr($line, '{"message":'))
            {
                //success message
                $message = json_decode($line);
                $message = $message->message;
                $return["message"] = $message;
            }
            if(stristr($line, '{"c":'))
            {
                //contacts
                $obj = json_decode($line);
                $return["obj"] = $obj;
            }
            if(stristr($line, '{"error":'))
            {
                //error message
                $message = json_decode($line);
                $message = $message->error;
                $return["error"] = $message;
            }
        }
        
        return $return;
    }
    
    public function __construct($username, $password, $contact)
    {
        $this->_username = $username;
        $this->_password = base64_decode($password);
        if(!is_array($contact))
        {
            //single contact
            $contact = array($contact);
        }
        $this->_contacts = $contact;
    }
    
    public function executeSync()
    {
        //main method!
        //get auth
        $url = "https://sro.whatsapp.net/v2/sync/a";
        $headers = $this->_getHeaders();
        $result = $this->_curlRequest($url, $headers);
        $result = $this->_processCurlResponse($result);
        if(isset($result["message"]) && $result["message"] == "next token" && isset($result["nonce"]))
        {
            //success
            $url = "https://sro.whatsapp.net/v2/sync/q";
            $postfields = "ut=all&t=c";
            foreach($this->_contacts as $contact)
            {
                $postfields .= "&u[]=" . urlencode($contact);
            }
            $headers = $this->_getHeaders($result["nonce"], strlen($postfields));
            $result = $this->_curlRequest($url, $headers, $postfields);
            $result = $this->_processCurlResponse($result);
            if(isset($result["obj"]))
            {
                return($this->_processJSONResponse($result["obj"]));
            }
            elseif(isset($result["message"]))
            {
                return $result["message"];
            }
            elseif(isset($result["error"]))
            {
                return $result["error"];
            }
            else
            {
                return false;
            }
        }
        elseif(isset($result["error"]))
        {
            //error
            return $result["error"];
        }
        else
        {
            return false;
        }
    }
    
    protected function _processJSONResponse($json)
    {
        //process decoded JSON object
        $contacts = $json->c;
        $_contacts = array();
        foreach($contacts as $contact)
        {
            if($contact->w == 1)
            {
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
