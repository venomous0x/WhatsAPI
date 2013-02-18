Available events and arguments
==============================
- onConnect:
- onDisconnect:
  - sokect: The resource socket id.
- onClose:
  - error: The message error.
- onPing:
- onPong:
  - msgid: The message id.
- onSendNickname:
  - name: User name.
- onSendPresence:
   - type: Presence type.
   - name: User name.
- onSendStatusUpdate:
   - msg: The text message status.
- onDissectPhone
   - cc: The user country code.
   - phone: The user phone number.
- onFailedDissectPhone:
   - phone: The user phone number including the country code.
- onGoodCredentials:
   - status: Account status.
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
   - status: Account status.
   - reason: The reason.
- onUploadFile:
   - name: The name.
   - url: The remote url on WhatsApp servers.
- onFailedUploadFile:
   - name: The name.

How to binds a callback to a event
==================================

# Events functions declarations
function MyFunction_onConnect($socket) {
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
