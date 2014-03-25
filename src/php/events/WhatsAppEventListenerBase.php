<?php
require_once 'WhatsAppEventListener.php';

/**
 * Empty implementation of WhatsAppEventListener. See that class for documentation.
 * 
 * This class provides no functionality, but allows developers to 
 *  use it as a base class so that their classes don't need to 
 *  define every method in the WhatsAppEventListener interface.
 *  By extending this class, it will also allow new methods to be
 *  created in the interface without the developer needing to update
 *  their base classes.
 * 
 * @file
 * Event class to received WhatsApp related events.
 */


class WhatsAppEventListenerBase implements WhatsAppEventListener {   
    function onClose( 
        $phone, 
        $error  
    ) {}

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
    ) {}
    
    function onCodeRegisterFailed(
        $phone,  
        $status,  
        $reason,  
        $retry_after 
    ) {}
    
    function onCodeRequest(
        $phone, 
        $method,
        $length
    ) {}
    
    function onCodeRequestFailed(
        $phone, 
        $method, 
        $reason, 
        $value
    ) {}
    
   function onCodeRequestFailedTooRecent(
        $phone, 
        $method, 
        $reason, 
        $retry_after 
    ) {}
    
   function onConnect(
        $phone, 
        $socket 
    ) {}

    function onCredentialsBad(
        $phone, 
        $status, 
        $reason 
    ) {}

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
    ) {}

    function onDisconnect(
        $phone, 
        $socket 
    ) {}

    function onDissectPhone(
        $phone, 
        $country, 
        $cc, 
        $mcc, 
        $lc, 
        $lg 
    ) {}

    function onDissectPhoneFailed(
        $phone 
    ) {}

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
    ) {}

    function onGetError(
        $phone,
        $id,
        $error 
    ) {}

    function onGetGroups(
        $phone,
        $groupList
    ) {}

    function onGetGroupsInfo(
        $phone, 
        $groupList
    ) {}

    function onGetGroupsSubject(
        $phone, 
        $gId, 
        $time,
        $author,
        $participant,
        $name,
        $subject
    ) {}

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
    ) {}

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
    ) {}

    function onGetMessage(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $message
    ) {}

    function onGetGroupMessage(
        $phone,
        $from,
        $author,
        $msgid,
        $type,
        $time,
        $name,
        $message
    ) {}

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
    ) {}

    function onGetProfilePicture(
        $phone,
        $from,
        $type,
        $thumbnail
    ) {}
    
    function onGetRequestLastSeen(
        $phone,
        $from,
        $msgid,
        $sec
    ) {}

    function onGetServerProperties(
        $phone,
        $version,
        $properties
    ) {}

    function onGetvCard(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $contact,
        $vcard
    ) {}

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
    ) {}

    function onGroupsChatCreate(
        $phone,
        $gId
    ) {}

    function onGroupsChatEnd(
        $phone,
        $gId
    ) {}

    function onGroupsParticipantsAdd(
        $phone,
        $groupId,
        $participant
    ) {}

    function onGroupsParticipantsRemove(
        $phone,
        $groupId,
        $participant,
        $author
    ) {}

    function onLogin(
        $phone
    ) {}

    function onMessageComposing(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {}

    function onMessagePaused(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {}

    function onMessageReceivedClient(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {}

    function onMessageReceivedServer(
        $phone,
        $from,
        $msgid,
        $type
    ) {}

    function onPing(
        $phone,
        $msgid
    ) {}

    function onPresence(
        $phone,
        $from,
        $type
    ) {}

    function onSendMessageReceived(
        $phone,
        $id,
        $from,
        $type
    ) {}

    function onSendPong(
        $phone,
        $msgid
    ) {}

    function onSendPresence(
        $phone,
        $type,
        $name
    ) {}

    function onSendStatusUpdate(
        $phone,
        $msg
    ) {}
    
    function onUploadFile(
        $phone,
        $name,
        $url
    ) {}

    function onUploadFileFailed(
        $phone,
        $name
    ) {}

    public function onConnectError(
        $phone, 
        $socket
    ) {}

    public function onGetGroupParticipants(
        $phone, 
        $groupId, 
        $groupList
    ) {}

    public function onGetStatus(
        $phone, 
        $from, 
        $type, 
        $id, 
        $t, 
        $status
    ) {}


    public function onLoginFailed(
        $phone, 
        $tag
    ) {}

    public function onMediaMessageSent(
        $phone, 
        $to, 
        $id, 
        $filetype, 
        $url, 
        $filename, 
        $filesize, 
        $icon
    ) {}

    public function onMediaUploadFailed(
        $phone, 
        $id, 
        $node, 
        $messageNode, 
        $reason
    ) {}


    public function onProfilePictureChanged(
        $phone, 
        $from, 
        $id, 
        $t
    ) {}


    public function onProfilePictureDeleted(
        $phone, 
        $from, 
        $id, 
        $t
    ) {}


    public function onSendMessage(
        $phone, 
        $targets, 
        $id,
        $node
    ) {}

    /**
     * @param SyncResult $result
     * @return mixed|void
     */
    public function onGetSyncResult(
        $result
    ) {}

    public function onGetReceipt(
        $from,
        $id,
        $offline,
        $retry
    ) {}
}
