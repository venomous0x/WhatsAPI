<?php
require_once dirname(__FILE__).'/events/WhatsAppEventListener.php';
require_once dirname(__FILE__).'/events/WhatsAppEventListenerLegacyAdapter.php';

/**
 * @file
 * Event class to fire WhatsApp related events.
 */

/**
 *
 */
class WhatsAppEvent
{
   /**
     * Contains all of our event listeners.
     * 
    * Note: This shouldn't be static, and may change in future implementations.
    * 
     * @var WhatsAppEventListener
     */
    static $event_listeners = array();
    
    /**
     * Constructor.
     *
     * @param string $event
     *   To be removed.
     */
    function __construct($event = null)
    {
        // Event argument to be removed.
    }

    /**
     * Adds the given event listener which will be called back
     *  when events are fired.
     * 
     * @param WhatsAppEventListener $event_listener
     */
    function addEventListener(WhatsAppEventListener $event_listener)
    {
        array_push(self::$event_listeners, $event_listener);
    }   
    
    /**
     * Registers an event.
     *
     * @param string $event
     *   Name of the event.
     *
     * @deprecated Use addEventListener instead. 
     */
    protected function register($event)
    {
        // To be removed.
    }
    
    /**
     * Binds a callback to a event.
     *
     * @param string $event
     *   Name of the event.
     * @param string $callback
     *   The method or function to call.
     * 
     * @deprecated Use addEventListener instead. 
     */
    public function bind($event, $listener)
    {
        $this->addEventListener(new WhatsAppEventListenerLegacyAdapter($event,$listener));
    }

    /**
     * Executes all the binded callbacks when the event is fired. Don't  this method,
     *   this is included for backwards compatibility only.
     *
     * @param string $event
     *   Name of the event.
     * @param array $arguments
     *   The arguments to pass to each callback.
     * 
     * @deprecated Fire events specifically by name.
     */
    public function fire($event, $arguments = array())
    {
        // For backwards compatibility only.
        foreach( self::$event_listeners as $event_listener ) {
            call_user_func_array(array($event_listener, $event), $arguments);
        }
    }
  
    /**
     * Fires the callback for each listener.
     * 
     * @param function $callbackEvent
     */
    private function fireCallback($callbackEvent) {
        array_map($callbackEvent, self::$event_listeners);
   }
    
    // The supported events:
    function fireClose( 
        $phone, 
        $error  
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $error) {
            $listener->onClose($phone, $error);
        };
        $this->fireCallback($callbackEvent);
   }

    function fireCodeRegister(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $login, $pw, $type, $expiration, $kind, $price, $cost, $currency, $price_expiration) {
            $listener->onCodeRegister($phone, $login, $pw, $type, $expiration, $kind, $price, $cost, $currency, $price_expiration);
        };
        $this->fireCallback($callbackEvent);        
    }
    
    function fireCodeRegisterFailed(
        $phone,  
        $status,  
        $reason,  
        $retry_after 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $status, $reason, $retry_after) {
            $listener->onCodeRegisterFailed($phone, $status, $reason, $retry_after);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireCodeRequest(
        $phone, 
        $method,
        $length
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $method, $length) { 
            $listener->onCodeRequest($phone, $method, $length);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireCodeRequestFailed(
        $phone, 
        $method, 
        $reason, 
        $value
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $method, $reason, $value) {
            $listener->onCodeRequestFailed($phone, $method, $reason, $value);
        };
        $this->fireCallback($callbackEvent);          
    }
    
   function fireCodeRequestFailedTooRecent(
        $phone, 
        $method, 
        $reason, 
        $retry_after 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $method, $reason, $retry_after){  
            $listener->onCodeRequestFailedTooRecent($phone, $method, $reason, $retry_after);
        };
        $this->fireCallback($callbackEvent);          
    }
    
   function fireConnect(
        $phone, 
        $socket 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $socket) { 
            $listener->onConnect($phone, $socket);
        };
        $this->fireCallback($callbackEvent);          
    }
    
   function fireConnectError(
        $phone, 
        $socket 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $socket){ 
            $listener->onConnectError($phone, $socket);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireCredentialsBad(
        $phone, 
        $status, 
        $reason 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $status, $reason) { 
            $listener->onCredentialsBad($phone, $status, $reason);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireCredentialsGood(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $login, $pw, $type, $expiration, $kind, $price, $cost, $currency, $price_expiration) { 
            $listener->onCredentialsGood($phone, $login, $pw, $type, $expiration, $kind, $price, $cost, $currency, $price_expiration);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireDisconnect(
        $phone, 
        $socket 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $socket) { 
            $listener->onDisconnect($phone, $socket);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireDissectPhone(
        $phone, 
        $country, 
        $cc, 
        $mcc, 
        $lc, 
        $lg 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $country, $cc, $mcc, $lc, $lg) {
            $listener->onDissectPhone($phone, $country, $cc, $mcc, $lc, $lg);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireDissectPhoneFailed(
        $phone 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone) { 
            $listener->onDissectPhoneFailed($phone);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetAudio(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time, $name, $size, $url, $file, $mimetype, $filehash, $duration, $acodec) { 
            $listener->onGetAudio($phone, $from, $msgid, $type, $time, $name, $size, $url, $file, $mimetype, $filehash, $duration, $acodec);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetError(
        $phone,
        $id,
        $error 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $id, $error) {
            $listener->onGetError($phone, $id, $error);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetGroups(
        $phone,
        $groupList
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $groupList) {  
            $listener->onGetGroups($phone, $groupList);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetGroupsInfo(
        $phone, 
        $groupList
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $groupList) { 
            $listener->onGetGroupsInfo($phone, $groupList);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetGroupsSubject(
        $phone, 
        $gId, 
        $time,
        $author,
        $participant,
        $name,
        $subject
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $gId, $time, $author, $participant, $name, $subject) {  
            $listener->onGetGroupsSubject($phone, $gId, $time, $author, $participant, $name, $subject);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetImage(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time, $name, $size, $url, $file, $mimetype, $filehash, $width, $height, $thumbnail) {  
            $listener->onGetImage($phone, $from, $msgid, $type, $time, $name, $size, $url, $file, $mimetype, $filehash, $width, $height, $thumbnail);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetLocation(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time, $name, $place_name, $longitude, $latitude, $url, $thumbnail) { 
            $listener->onGetLocation($phone, $from, $msgid, $type, $time, $name, $place_name, $longitude, $latitude, $url, $thumbnail);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetMessage(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $message
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time, $name, $message) {
            $listener->onGetMessage($phone, $from, $msgid, $type, $time, $name, $message);
        };
        $this->fireCallback($callbackEvent);                
    }

    function fireGetGroupMessage(
        $phone,
        $from,
        $author,
        $msgid,
        $type,
        $time,
        $name,
        $message
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $author, $msgid, $type, $time, $name, $message) { 
            $listener->onGetGroupMessage($phone, $from, $author, $msgid, $type, $time, $name, $message);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetGroupParticipants(
        $phone,
        $groupId,
        $groupList            
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $groupId, $groupList) { 
            $listener->onGetGroupParticipants($phone, $groupId, $groupList);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireGetPrivacyBlockedList(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $children) { 
            $listener->onGetPrivacyBlockedList($phone, $children);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetProfilePicture(
        $phone,
        $from,
        $type,
        $thumbnail
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $type, $thumbnail) { 
            $listener->onGetProfilePicture($phone, $from, $type, $thumbnail);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireGetRequestLastSeen(
        $phone,
        $from,
        $msgid,
        $sec
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $sec) {
            $listener->onGetRequestLastSeen($phone, $from, $msgid, $sec);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetServerProperties(
        $phone,
        $version,
        $properties
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $version, $properties) { 
            $listener->onGetServerProperties($phone, $version, $properties);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireGetStatus(
        $phone,
        $from,
        $type,
        $id,
        $t,
        $status
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $type, $id, $t, $status) {  
            $listener->onGetStatus($phone, $from, $type, $id, $t, $status);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireGetvCard(
        $phone,
        $from,
        $msgid,
        $type,
        $time,
        $name,
        $contact,
        $vcard
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time, $name, $contact, $vcard){ 
            $listener->onGetvCard($phone, $from, $msgid, $type, $time, $name, $contact, $vcard);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGetVideo(
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
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time, $name, $url, $file, $size, $mimetype, $filehash, $duration, $vcodec, $acodec, $thumbnail){  
            $listener->onGetVideo($phone, $from, $msgid, $type, $time, $name, $url, $file, $size, $mimetype, $filehash, $duration, $vcodec, $acodec, $thumbnail);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGroupsChatCreate(
        $phone,
        $gId
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $gId) {  
            $listener->onGroupsChatCreate($phone, $gId);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGroupsChatEnd(
        $phone,
        $gId
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $gId) {  
            $listener->onGroupsChatEnd($phone, $gId);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGroupsParticipantsAdd(
        $phone,
        $groupId,
        $participant
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $groupId, $participant) { 
            $listener->onGroupsParticipantsAdd($phone, $groupId, $participant);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireGroupsParticipantsRemove(
        $phone,
        $groupId,
        $participant,
        $author
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $groupId, $participant, $author) { 
            $listener->onGroupsParticipantsRemove($phone, $groupId, $participant, $author);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireLogin(
        $phone
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone) { 
            $listener->onLogin($phone);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireLoginFailed(
        $phone,
        $tag
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $tag) { 
            $listener->onLoginFailed($phone, $tag);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireMessageComposing(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time) { 
            $listener->onMessageComposing($phone, $from, $msgid, $type, $time);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireMediaMessageSent(
        $phone, 
        $to,
        $id,
        $filetype,
        $url,
        $filename,
        $filesize,
        $icon        
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $to, $id, $filetype, $url, $filename, $filesize, $icon) { 
            $listener->onMediaMessageSent($phone, $to, $id, $filetype, $url, $filename, $filesize, $icon);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireMediaUploadFailed(
        $phone,
        $id,
        $node,  
        $messageNode,
        $reason
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $id, $node, $messageNode, $reason) { 
            $listener->onMediaUploadFailed($phone, $id, $node, $messageNode, $reason);
        };
        $this->fireCallback($callbackEvent);          
    }
       
    function fireMessagePaused(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time) { 
            $listener->onMessagePaused($phone, $from, $msgid, $type, $time);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireMessageReceivedClient(
        $phone,
        $from,
        $msgid,
        $type,
        $time
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type, $time) {  
            $listener->onMessageReceivedClient($phone, $from, $msgid, $type, $time);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireMessageReceivedServer(
        $phone,
        $from,
        $msgid,
        $type
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $msgid, $type) {
            $listener->onMessageReceivedServer($phone, $from, $msgid, $type);
        };
        $this->fireCallback($callbackEvent);          
    }

    function firePing(
        $phone,
        $msgid
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $msgid) { 
            $listener->onPing($phone, $msgid);
        };
        $this->fireCallback($callbackEvent);          
    }

    function firePresence(
        $phone,
        $from,
        $type
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $type) {  
            $listener->onPresence($phone, $from, $type);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireProfilePictureChanged(
        $phone, 
        $from,
        $id,
        $t            
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $id, $t) {  
            $listener->onProfilePictureChanged($phone, $from, $id, $t);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireProfilePictureDeleted(
        $phone, 
        $from,
        $id,
        $t            
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $from, $id, $t) {  
            $listener->onProfilePictureDeleted($phone, $from, $id, $t);
        };
        $this->fireCallback($callbackEvent);          
    }
        
    function fireSendMessageReceived(
        $phone,
        $id,
        $from,
        $type
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $id, $from, $type) {
            $listener->onSendMessageReceived($phone, $id, $from, $type);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireSendPong(
        $phone,
        $msgid
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $msgid) { 
            $listener->onSendPong($phone, $msgid);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireSendMessage(
        $phone, 
        $targets,
        $id,
        $node 
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $targets, $id, $node) { 
            $listener->onSendMessage($phone, $targets, $id, $node);
        };
        $this->fireCallback($callbackEvent);          
    }
    
    function fireSendPresence(
        $phone,
        $type,
        $name
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $type, $name) {
            $listener->onSendPresence($phone, $type, $name);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireSendStatusUpdate(
        $phone,
        $msg
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $msg) { 
            $listener->onSendStatusUpdate($phone, $msg);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireUploadFile(
        $phone,
        $name,
        $url
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $name, $url) { 
            $listener->onUploadFile($phone, $name, $url);
        };
        $this->fireCallback($callbackEvent);          
    }

    function fireUploadFileFailed(
        $phone,
        $name
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($phone, $name) { 
            $listener->onUploadFileFailed($phone, $name);
        };
        $this->fireCallback($callbackEvent);          
    }

    /**
     * @param $result SyncResult
     */
    function fireGetSyncResult(
        $result
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($result) {
            $listener->onGetSyncResult($result);
        };
        $this->fireCallback($callbackEvent);
    }

    function fireGetReceipt(
        $from,
        $id,
        $offline,
        $retry
    ) {
        $callbackEvent = function(WhatsAppEventListener $listener) use ($from, $id, $offline, $retry) {
            $listener->onGetReceipt($from, $id, $offline, $retry);
        };
        $this->fireCallback($callbackEvent);
    }

}
