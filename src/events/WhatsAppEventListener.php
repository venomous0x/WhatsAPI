<?php
/**
 * @file
 * Event class to received WhatsApp related events.
 */


interface WhatsAppEventListener {   
    function onClose( 
        $phone, // The user phone number including the country code.
        $error  // The error message. 
    );

    function onCodeRegister(
        $phone, // The user phone number including the country code.
        $login, // Phone number with country code.
        $pw,    // Account password.
        $type,  //, // Type of account.
        $expiration, //Expiration date in UNIX TimeStamp.
        $kind,  // Kind of account.
        $price, // Formated price of account.
        $cost,  // Decimal amount of account.
        $currency, // Currency price of account.
        $price_expiration // Price expiration in UNIX TimeStamp.
    );
    
    function onCodeRegisterFailed(
        $phone, // The user phone number including the country code.
        $status, // The server status number
        $reason, // Reason of the status (e.g. too_recent/missing_param/bad_param).
        $retry_after// Waiting time before requesting a new code in seconds.
    );
    
    function onCodeRequest(
        $phone, // The user phone number including the country code.
        $method, // Used method (SMS/voice).
        $length// Registration code length.
    );
    
    function onCodeRequestFailed(
        $phone, // The user phone number including the country code.
        $method, // Used method (SMS/voice).
        $reason, // Reason of the status (e.g. too_recent/missing_param/bad_param).
        $value// The missing_param/bad_param or waiting time before requesting a new code.
    );
    
   function onCodeRequestFailedTooRecent(
        $phone, // The user phone number including the country code.
        $method, // Used method (SMS/voice).
        $reason, // Reason of the status (too_recent).
        $retry_after // Waiting time before requesting a new code in seconds.
    );
    
   function onConnect(
        $phone, // The user phone number including the country code.
        $socket // The resource socket id.
    );

   function onConnectError(
        $phone, // The user phone number including the country code.
        $socket // The resource socket id.
    );

   function onCredentialsBad(
        $phone, // The user phone number including the country code.
        $status, // Account status.
        $reason // The reason.
    );

    function onCredentialsGood(
        $phone, // The user phone number including the country code.
        $login, // Phone number with country code.
        $pw, // Account password.
        $type, // Type of account.
        $expiration, // Expiration date in UNIX TimeStamp.
        $kind, // Kind of account.
        $price, // Formated price of account.
        $cost, // Decimal amount of account.
        $currency, // Currency price of account.
        $price_expiration // Price expiration in UNIX TimeStamp.
    );

    function onDisconnect(
        $phone, // The user phone number including the country code.
        $socket // The resource socket id.
    );

    function onDissectPhone(
        $phone, // The user phone number including the country code.
        $country, // The detected country name.
        $cc, // The number's country code.
        $mcc, // International cell network code for the detected country.
        $lc, // Location code for the detected country
        $lg // Language code for the detected country
    );

    function onDissectPhoneFailed(
        $phone // The user phone number including the country code.
    );

    function onGetAudio(
        $phone, // The user phone number including the country code.
        $from, // The sender phone number.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $size, // The image size.
        $url, // The url to bigger audio version.
        $file, // The audio name.
        $mimetype, // The audio mime type.
        $filehash, // The audio file hash.
        $duration, // The audio duration.
        $acodec // The audio codec.
    );

    function onGetError(
        $phone, // The user phone number including the country code.
        $id, // The id of the request that caused the error
        $error // Array with error data for why request failed.
    );

    function onGetGroups(
        $phone, // The user phone number including the country code.
        $groupList // Array with all the groups and groupsinfo.
    );

    function onGetGroupsInfo(
        $phone, // The user phone number including the country code.
        $groupList // Array with the the groupinfo.
    );

    function onGetGroupsSubject(
        $phone, // The user phone number including the country code.
        $gId, // The group JID.
        $time, // The unix time when send subject.
        $author, // The author phone number including the country code.
        $participant, // The participant phone number including the country code.
        $name, // The sender name.
        $subject // The subject (e.g. group name).
    );

    function onGetImage(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $size, // The image size.
        $url, // The url to bigger image version.
        $file, // The image name.
        $mimetype, // The image mime type.
        $filehash, // The image file hash.
        $width, // The image width.
        $height, // The image height.
        $thumbnail // The base64_encode image thumbnail.
    );

    function onGetLocation(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $place_name, // The place name.
        $longitude, // The location longitude.
        $latitude, // The location latitude.
        $url, // The place url.
        $thumbnail // The base64_encode location image thumbnail.
    );

    function onGetMessage(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $message // The message.
    );

    function onGetGroupMessage(
        $phone, // The user phone number including the country code.
        $from, // The group JID.
        $author, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $message // The message.
    );
    
    function onGetGroupParticipants(
        $phone,
        $groupId,
        $groupList            
    );
    
    function onGetPrivacyBlockedList(
        $phone, // The user phone number including the country code.
        $children
        /*
        $data, // Array of data nodes containing numbers you have blocked.
        $onGetProfilePicture, //
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $type, // The type of picture (image/preview).
        $thumbnail // The base64_encoded image.
        */
    );

    function onGetProfilePicture(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $type, // The type of picture (image/preview).
        $thumbnail// The base64_encoded image.
    );
    
    function onGetRequestLastSeen(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $sec // The number of seconds since the user went offline.
    );

    function onGetServerProperties(
        $phone, // The user phone number including the country code.
        $version, // The version number on the server.
        $properties // Array of server properties.
    );
    
    function onGetStatus(
        $phone,
        $from,
        $type,
        $id,
        $t,
        $status
    );
    
    function onGetvCard(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $contact, // The vCard contact name.
        $vcard // The vCard.
    );

    function onGetVideo(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time, // The unix time when send message notification.
        $name, // The sender name.
        $url, // The url to bigger video version.
        $file, // The video name.
        $size, // The video size.
        $mimetype, // The video mime type.
        $filehash, // The video file hash.
        $duration, // The video duration.
        $vcodec, // The video codec.
        $acodec, // The audio codec.
        $thumbnail // The base64_encode video thumbnail.
    );

    function onGroupsChatCreate(
        $phone, // The user phone number including the country code.
        $gId // The group JID.
    );

    function onGroupsChatEnd(
        $phone, // The user phone number including the country code.
        $gId // The group JID.
    );

    function onGroupsParticipantsAdd(
        $phone, // The user phone number including the country code.
        $groupId, // The group JID.
        $participant // The participant JID.
    );

    function onGroupsParticipantsRemove(
        $phone, // The user phone number including the country code.
        $groupId, // The group JID.
        $participant, // The participant JID.
        $author // The author JID.
    );

    function onLogin(
        $phone // The user phone number including the country code.
    );
    
    function onLoginFailed(
        $phone, // The user phone number including the country code.
        $tag
    );

    function onMediaMessageSent(
        $phone, // The user phone number including the country code.
        $to,
        $id,
        $filetype,
        $url,
        $filename,
        $filesize,
        $icon        
    );
    
    function onMediaUploadFailed(
        $phone, // The user phone number including the country code.
        $id,
        $node,  
        $messageNode,
        $reason
    );
   
    function onMessageComposing(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time // The unix time when send message notification.
    );

    function onMessagePaused(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time // The unix time when send message notification.
    );

    function onMessageReceivedClient(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type, // The message type.
        $time // The unix time when send message notification.
    );

    function onMessageReceivedServer(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $msgid, // The message id.
        $type // The message type.
    );

    function onPing(
        $phone, // The user phone number including the country code.
        $msgid // The message id.
    );

    function onPresence(
        $phone, // The user phone number including the country code.
        $from, // The sender JID.
        $type // The presence type.
    );

    function onProfilePictureChanged(
        $phone, 
        $from,
        $id,
        $t            
    );

    function onProfilePictureDeleted(
        $phone, 
        $from,
        $id,
        $t            
    );
    
    function onSendMessage(
        $phone, // The user phone number including the country code.
        $targets,
        $id,
        $node 
    );

    function onSendMessageReceived(
        $phone, // The user phone number including the country code.
        $id, // Message ID
        $from, // The sender JID.
        $type // Message type
    );

    function onSendPong(
        $phone, // The user phone number including the country code.
        $msgid // The message id.
    );

    function onSendPresence(
        $phone, // The user phone number including the country code.
        $type, // Presence type.
        $name  // User nickname.
    );

    function onSendStatusUpdate(
        $phone, // The user phone number including the country code.
        $msg  // The status message.
    );
    
    function onUploadFile(
        $phone, // The user phone number including the country code.
        $name, // The filename.       
        $url  // The remote url on WhatsApp servers (note, // this is NOT the URL to download the file, only used for sending message).
    );

    function onUploadFileFailed(
        $phone, // The user phone number including the country code.
        $name // The filename.     
    );

    /**
     * @param $result SyncResult
     * @return mixed
     */
    function onGetSyncResult(
        $result
    );

    function onGetReceipt(
        $from,
        $id,
        $offline,
        $retry
    );
}
