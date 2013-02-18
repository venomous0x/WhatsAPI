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
