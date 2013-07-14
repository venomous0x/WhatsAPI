Available events and arguments
==============================
- onClose:
  - phone: The user phone number including the country code.
  - error: The message error.
- onCodeRegister:
  - phone: The user phone number including the country code.
  - login: Phone number with country code.
  - pw: Account password.
  - type: Type of account.
  - expiration: Expiration date in UNIX TimeStamp.
  - kind: Kind of account.
  - price: Formated price of account.
  - cost: Decimal amount of account.
  - currency: Currency price of account.
  - price_expiration: Price expiration in UNIX TimeStamp.
- onCodeRegisterFailed:
  - phone: The user phone number including the country code.
  - status: The server status number
  - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
  - retry_after: Waiting time before requesting a new code.
- onCodeRequest:
  - phone: The user phone number including the country code.
  - method: Used method.
  - length: Registration code length.
- onCodeRequestFailed:
  - phone: The user phone number including the country code.
  - method: Used method.
  - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
  - value: The missing_param/bad_param or waiting time before requesting a new code.
- onCodeRequestFailedTooRecent:
  - phone: The user phone number including the country code.
  - method: Used method.
  - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
  - retry_after: Waiting time before requesting a new code.
- onConnect:
  - phone: The user phone number including the country code.
  - sokect: The resource socket id.
- onCredentialsBad:
  - phone: The user phone number including the country code.
  - status: Account status.
  - reason: The reason.
- onCredentialsGood:
  - phone: The user phone number including the country code.
  - login: Phone number with country code.
  - pw: Account password.
  - type: Type of account.
  - expiration: Expiration date in UNIX TimeStamp.
  - kind: Kind of account.
  - price: Formated price of account.
  - cost: Decimal amount of account.
  - currency: Currency price of account.
  - price_expiration: Price expiration in UNIX TimeStamp.
- onDisconnect:
  - phone: The user phone number including the country code.
  - sokect: The resource socket id.
- onDissectPhone
  - phone: The user phone number including the country code.
  - country: The detected country name.
  - cc: The user country code without the country code.
  - phone: The user phone number.
- onDissectPhoneFailed:
  - phone: The user phone number including the country code.
- onGetAudio:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - size: The image size.
  - url: The url to bigger audio version.
  - file: The audio name.
  - mimetype: The audio mime type.
  - filehash: The audio file hash.
  - duration: The audio duration.
  - acodec: The audio codec.
- onGetError:
  - phone: The user phone number including the country code.
  - error: Array with error data for why request was not successful.
- onGetGroups:
  - phone: The user phone number including the country code.
  - groupList: Array with all the groups and groupsinfo.
- onGetGroupsInfo:
  - phone: The user phone number including the country code.
  - groupList: Array with the the groupinfo.
- onGetGroupsSubject:
  - phone: The user phone number including the country code.
  - gId: The group id.
  - time: The unix time when send subject.
  - author: The author phone number including the country code.
  - participant: The participant phone number including the country code.
  - name: The sender name.
  - subject: The subject.
- onGetImage:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - size: The image size.
  - url: The url to bigger image version.
  - file: The image name.
  - mimetype: The image mime type.
  - filehash: The image file hash.
  - width: The image width.
  - height: The image height.
  - thumbnail: The base64_encode image thumbnail.
- onGetLocation:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - place_name: The place name.
  - longitude: The location longitude.
  - latitude: The location latitude.
  - url: The place url.
  - thumbnail: The base64_encode location image thumbnail.
- onGetMessage:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - message: The message.
- onGetPrivacyBlockedList:
  - phone: The user phone number including the country code.
  - data: Array of data node containing numbers you have blocked.
- onGetProfilePicture:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - type: The type of picture.
  - thumbnail: The base64_encode image.
- onGetRequestLastSeen:
  - phone: The user phone number including the country code.
  - msgid: The message id.
  - sec: The number of seconds seen last seen online.
- onGetServerProperties:
  - phone: The user phone number including the country code.
  - version: The version number on the server.
  - properties: Array of server properties.
- onGetvCard:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - contact: The vCard contact name.
  - vcard: The vCard.
- onGetVideo:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - url: The url to bigger video version.
  - file: The video name.
  - size: The video size.
  - mimetype: The video mime type.
  - filehash: The video file hash.
  - duration: The video duration.
  - vcodec: The video codec.
  - acodec: The audio codec.
  - thumbnail: The base64_encode video thumbnail.
- onGroupsChatCreate:
  - phone: The user phone number including the country code.
  - gId: The group id.
- onGroupsChatEnd:
  - phone: The user phone number including the country code.
  - gId: The group id.
- onGroupsParticipantsAdd:
  - phone: The user phone number including the country code.
  - groupId: The groupId.
  - participant: The participant phone number including the country code.
- onGroupsParticipantsRemove:
  - phone: The user phone number including the country code.
  - groupId: The groupId.
  - participant: The participant phone number including the country code.
  - author: The author phone number including the country code.
- onLogin:
  - phone: The user phone number including the country code.
- onMessageComposing:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
- onMessagePaused:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
- onMessageReceivedClient:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
- onMessageReceivedServer:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
- onPing:
  - phone: The user phone number including the country code.
  - msgid: The message id.
- onPresence:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - type: The presence type.
- onSendMessageReceived:
  - phone: The user phone number including the country code.
  - time: The unix time when send message notification.
  - from: The sender phone number.
- onSendPong:
  - phone: The user phone number including the country code.
  - msgid: The message id.
- onSendPresence:
  - phone: The user phone number including the country code.
  - type: Presence type.
  - name: User name.
- onSendStatusUpdate:
  - phone: The user phone number including the country code.
  - msg: The text message status.
- onUploadFile:
  - phone: The user phone number including the country code.
  - name: The name.
  - url: The remote url on WhatsApp servers.
- onUploadFileFailed:
  - phone: The user phone number including the country code.
  - name: The name.



How to binds a callback to a event
==================================

# Events functions declarations
```php
function MyFunction_onConnect($phone, $socket) {
    print("$socket\n");
}
```
# Require the class.
```php
require 'whatsprot.class.php';
```
# Create a instance of WhastPort.
```php
$w = new WhatsProt($userPhone, $userIdentity, $userName, $debug);
```
# Binds a callback to a event
```php
# $w->eventManager()->bind((string) $event, (string) $callback);
$w->eventManager()->bind('onConnect', 'MyFunction_onConnect');
```
# Connect to WhatsApp servers.
```php
$w->connect();
```
# Now Login function sends Nickname and (Available) Presence.
```php
$w->login();
```
[...]
