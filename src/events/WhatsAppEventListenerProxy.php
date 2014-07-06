<?php

/*
 * This class may be helpful if you want to handle all events the same way.
 */

/**
 * Funnels all events through the abstract handleEvent method.
*/
abstract class WhatsAppEventListenerProxy implements WhatsAppEventListener {
    /**
     *  This method will get all event calls.
     * 
     */
    abstract protected function handleEvent($eventName, array $arguments );

    function onClose( 
        $phone, 
        $error  
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onCodeRegister(
        $phone,  
        $login,  
        $pw,     
        $type,   
        $expiration,  
        $kind,   
        $price,  
        $cost,   
        $currency,  
        $price_expiration  
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());      
    }
    
    function onCodeRegisterFailed(
        $phone,  
        $status,  
        $reason,  
        $retry_after 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }
    
    function onCodeRequest(
        $phone, 
        $method,
        $length
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }
    
    function onCodeRequestFailed(
        $phone, 
        $method, 
        $reason, 
        $value
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }
    
   function onCodeRequestFailedTooRecent(
        $phone, 
        $method, 
        $reason, 
        $retry_after 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }
    
   function onConnect(
        $phone, 
        $socket 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onCredentialsBad(
        $phone, 
        $status, 
        $reason 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onCredentialsGood(
        $phone, 
        $login, 
        $pw, 
        $type, 
        $expiration, 
        $kind, 
        $price, 
        $cost, 
        $currency, 
        $price_expiration 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onDisconnect(
        $phone, 
        $socket 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onDissectPhone(
        $phone, 
        $country, 
        $cc, 
        $mcc, 
        $lc, 
        $lg 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onDissectPhoneFailed(
        $phone 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetAudio(
        $phone, 
        $from, 
        $msgid, 
        $type, 
        $time, 
        $name, 
        $size, 
        $url, 
        $file, 
        $mimetype,
        $filehash,
        $duration,
        $acodec 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetError(
        $phone,
        $id,
        $error 
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetGroups(
        $phone,
        $groupList
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetGroupsInfo(
        $phone, 
        $groupList
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetGroupsSubject(
        $phone, 
        $gId, 
        $time,
        $author,
        $participant,
        $name,
        $subject
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetImage(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $size,
        $url,
        $file,
        $mimetype,
        $filehash,
        $width,
        $height,
        $thumbnail
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetLocation(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $place_name,
        $longitude,
        $latitude,
        $url,
        $thumbnail
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetMessage(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $message
    ) {
        $func = __FUNCTION__;
        $args = func_get_args();
        $this->handleEvent($func, $args);
    }

    function onGetGroupMessage(
        $phone,
        $from,
        $author,
        $msgid,
        $type,
        $time,
        $name,
        $message
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetPrivacyBlockedList(
        $phone,
        $children
            /*
        $data,
        $onGetProfilePicture, 
        $phone,
        $from,
        $type,
        $thumbnail
            */
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetProfilePicture(
        $phone,
        $from,
        $type,
        $thumbnail
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }
    
    function onGetRequestLastSeen(
        $phone,
        $from,
        $msgid,
        $sec
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetServerProperties(
        $phone,
        $version,
        $properties
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetvCard(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $contact,
        $vcard
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGetVideo(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $url,
        $file,
        $size,
        $mimetype,
        $filehash,
        $duration,
        $vcodec,
        $acodec,
        $thumbnail
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGroupsChatCreate(
        $phone,
        $gId
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGroupsChatEnd(
        $phone,
        $gId
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGroupsParticipantsAdd(
        $phone,
        $groupId,
        $participant
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onGroupsParticipantsRemove(
        $phone,
        $groupId,
        $participant,
        $author
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onLogin(
        $phone
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onMessageComposing(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onMessagePaused(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onMessageReceivedClient(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onMessageReceivedServer(
        $phone,
        $from,
        $msgid,
        $type
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onPing(
        $phone,
        $msgid
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onPresence(
        $phone,
        $from,
        $type
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onSendMessageReceived(
        $phone,
        $id,
        $from,
        $type
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onSendPong(
        $phone,
        $msgid
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onSendPresence(
        $phone,
        $type,
        $name
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onSendStatusUpdate(
        $phone,
        $msg
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }
    
    function onUploadFile(
        $phone,
        $name,
        $url
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    function onUploadFileFailed(
        $phone,
        $name
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onConnectError(
        $phone, 
        $socket
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args()); 
    }

    public function onGetGroupParticipants(
        $phone, 
        $groupId, 
        $groupList
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args()); 
    }

    public function onGetStatus(
        $phone, 
        $from, 
        $type, 
        $id, 
        $t, 
        $status
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onLoginFailed(
        $phone, 
        $tag
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onMediaMessageSent(
        $phone, 
        $to, 
        $id, 
        $filetype, 
        $url, 
        $filename, 
        $filesize,
        $icon
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onMediaUploadFailed(
        $phone, 
        $id, 
        $node, 
        $messageNode, 
        $reason
    ) {      
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onProfilePictureChanged(
        $phone, 
        $from, 
        $id, 
        $t
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onProfilePictureDeleted(
        $phone, 
        $from, 
        $id, 
        $t
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onSendMessage(
        $phone, 
        $targets, 
        $id, 
        $node
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    /**
     * @param SyncResult $result
     * @return mixed|void
     */
    public function onGetSyncResult(
        $result
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

    public function onGetReceipt(
        $from,
        $id,
        $offline,
        $retry
    ) {
        $this->handleEvent(__FUNCTION__, func_get_args());
    }

}
