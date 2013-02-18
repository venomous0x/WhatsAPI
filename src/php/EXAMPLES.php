<?php

/**
 * @file
 * Show the use of different types of messaging.
 */

# Require the class.
require_once('whatsprot.class.php');

/**
 * Config data.
 * WhatsApp now changes your password everytime you use this.
 * Do not worry, WhatsAPI saves it for you every time.
 */
$userPhone        = '34666554433';       # Telephone number including the country code without '+' or '00'.
$userIdentity     = '00:00:00:00:00:00'; # This is tipically the IMEI number.
                                         # If you are using an iOS device you should input your WLAN MAC address.
$userName         = 'John Doe';          # This is the username displayed by WhatsApp clients.

$destinationPhone = '34666443322';       # Destination telephone number including the country code without '+' or '00'.
                                         # For groups: [phone number]-[group id].
$debug = TRUE;

# Create a instance of WhastPort.
$w = new WhatsProt($userPhone, $userIdentity, $userName, $debug);

# How to create an account __ONLY__ if you do not have a associated to our phone number.
# You can test your credentials with: $w->checkCredentials();

/**
 * First request a registration code from WhatsApp.
 *
 * @param $method
 *   Accepts only 'sms' or 'voice' as a value.
 * @param $countryCody
 *   ISO Country Code, 2 Digit.
 * @param $langCode
 *   ISO 639-1 Language Code: two-letter codes.
 *
 * @return object
 *   An object with server response.
 *   - status: Status of the request (sent/fail).
 *   - reason: Reason of the status (e.g. too_recent/missing_param/bad_param).
 *   - length: Registration code lenght.
 *   - method: Used method.
 *   - retry_after: Waiting time before requesting a new code.
 */
$w->requestCode('sms', 'ES', 'es');

/**
 * Second register account on WhatsApp using the provided code with $w->requestCode('sms', 'ES', 'es');.
 *
 * @param integer $code
 *   Numeric code value provided on requestCode().
 *
 * @return object
 *   An object with server response.
 *   - status: Account status.
 *   - login: Phone number with country code.
 *   - pw: Account password.
 *   - type: Type of account.
 *   - expiration: Expiration date in UNIX TimeStamp.
 *   - kind: Kind of account.
 *   - price: Formated price of account.
 *   - cost: Decimal amount of account.
 *   - currency: Currency price of account.
 *   - price_expiration: Price expiration in UNIX TimeStamp.
 */
$w->registerCode('123456');

# Connect to WhatsApp servers.
$w->Connect();
# Now Login function sends Nickname and (Available) Presence.
$w->Login();

# Send messages:

/**
 * Send a text message to the user/group.
 *
 * @param $to
 *   The reciepient to send.
 * @param $txt
 *   The text message.
 */
$w->Message($destinationPhone, 'This is an example!');

/**
 * Send a image to the user/group.
 *
 * @param $to
 *   The reciepient to send.
 * @param $file
 *   The url/uri to the image.
 */
$w->MessageImage($destinationPhone, 'http://example.com/photo.jpg');

/**
 * Send a video to the user/group.
 *
 * @param $to
 *   The reciepient to send.
 * @param $file
 *   The url/uri to the MP4 video.
 */
$w->MessageVideo($destinationPhone, 'http://example.com/video.mp4');

/**
 * Send a audio to the user/group.
 *
 * @param $to
 *   The reciepient to send.
 * @param $file
 *   The url/uri to the 3GP audio.
 */
$w->MessageAudio($destinationPhone, 'http://example.com/audio.3gp');

/**
 * Send a vCard to the user/group.
 *
 * @param $to
 *   The reciepient to send.
 * @param $name
 *   The contact name.
 * @param $vCard
 *   The contact vCard to send.
 */
require 'vCard.php';

$v = new vCard();
$image = file_get_contents('http://example.com/photo.jpg');
$v->set('data', array(
    'first_name' => 'John',
    'last_name' => 'Doe',
    'cell_tel' => '34666554433',
    'photo' => base64_encode($image),
));
$w->vCard($destinationPhone, 'John Doe', $v->show());

/**
 * Send a location to the user/group.
 *
 * @param $to
 *   The reciepient to send.
 * @param $long
 *   The logitude to send.
 * @param $lat
 *   The latitude to send.
 */
$w->Location($destinationPhone, '4.948568', '52.352957');

/**
 * Wait for message delivery notification.
 */
$w->WaitforReceipt();

/**
 * Or get all incoming messages and process it.
 */
while (TRUE) {
    $w->PollMessages();
    $msgs = $w->GetMessages();
    foreach ($msgs as $m) {
        # process inbound messages
    }
}
