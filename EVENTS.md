Available events and arguments
==============================
- onConnect:
- onDisconnect:
  - phone: The user phone number including the country code.
  - sokect: The resource socket id.
- onClose:
  - phone: The user phone number including the country code.
  - error: The message error.
- onPing:
- onPong:
  - phone: The user phone number including the country code.
  - msgid: The message id.
- onSendPresence:
  - phone: The user phone number including the country code.
  - type: Presence type.
  - name: User name.
- onSendStatusUpdate:
  - phone: The user phone number including the country code.
  - msg: The text message status.
- onRequestLastSeen:
  - phone: The user phone number including the country code.
  - msgid: The message id.
  - to: The reciepient to get the last seen.
- onDissectPhone
  - phone: The user phone number including the country code.
  - country: The detected country name.
  - cc: The user country code without the country code.
  - phone: The user phone number.
- onFailedDissectPhone:
  - phone: The user phone number including the country code.
- onGoodCredentials:
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
- onBadCredentials:
  - phone: The user phone number including the country code.
  - status: Account status.
  - reason: The reason.
- onRequestCode:
  - phone: The user phone number including the country code.
  - method: Used method.
  - length: Registration code lenght.
- onFailedRequestCode:
  - phone: The user phone number including the country code.
  - method: Used method.
  - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
  - value: The missing_param/bad_param or waiting time before requesting a new code.
- onRegisterCode:
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
- onFailedRegisterCode:
  - phone: The user phone number including the country code.
  - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
  - retry_after: Waiting time before requesting a new code.
- onUploadFile:
  - phone: The user phone number including the country code.
  - name: The name.
  - url: The remote url on WhatsApp servers.
- onFailedUploadFile:
  - phone: The user phone number including the country code.
  - name: The name.
- onSendMessageReceived:
  - phone: The user phone number including the country code.
  - time: The unix time when send message notification.
  - from: The sender phone number.
- onUserComposing:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
- onUserPaused:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
- onCreateGroupChat:
  - phone: The user phone number including the country code.
  - gId: The group id.
- onGetGroupSubject:
  - phone: The user phone number including the country code.
  - gId: The group id.
  - time: The unix time when send subject.
  - author: The author phone number including the country code.
  - participant: The participant phone number including the country code.
  - name: The sender name.
  - subject: The subject.
- onAddParticipantGroup:
  - phone: The user phone number including the country code.
  - groupId: The groupId.
  - participant: The participant phone number including the country code.
_ onRemoveParticipantGroup:
  - phone: The user phone number including the country code.
  - groupId: The groupId.
  - participant: The participant phone number including the country code.
  - author: The author phone number including the country code.
- onGetMessage:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - message: The message.
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
- onGetvCard:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - contact: The vCard contact name.
  - vcard: The vCard.
- onGetLocation:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - msgid: The message id.
  - type: The message type.
  - time: The unix time when send message notification.
  - name: The sender name.
  - longitude: The location longitude.
  - latitude: The location latitude.
  - thumbnail: The base64_encode location image thumbnail.
- onGetPlace:
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
  - thumbnail: The base64_encode place image thumbnail.
- onMessageReceivedServer:
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
- onGetPresence:
  - phone: The user phone number including the country code.
  - from: The sender phone number.
  - type: The presence type.

How to binds a callback to a event
==================================

# Events functions declarations
function MyFunction_onConnect($phone, $socket) {
    print("$socket\n");
}

# Require the class.
require 'whatsprot.class.php';

# Create a instance of WhastPort.
$w = new WhatsProt($userPhone, $userIdentity, $userName, $debug);

# Binds a callback to a event
# $w->eventManager()->bind((string) $event, (string) $callback);
$w->eventManager()->bind('onConnect', 'MyFunction_onConnect');

# Connect to WhatsApp servers.
$w->Connect();
# Now Login function sends Nickname and (Available) Presence.
$w->Login();

[...]
