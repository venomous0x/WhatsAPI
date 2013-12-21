#!/usr/bin/php
<?php
require_once 'TestWhatsProt.php';
require_once 'WhatsAppEventListenerCapture.php';

// To be made into a formal test framework, using phpUnit, perhaps?:

// Setup:
$w = new TestWhatsProt('$username', '$identity', '$nickname', true);
$listener = new WhatsAppEventListenerCapture();
$w->eventManager()->addEventListener($listener);

// Old event bindings:
$old_function_called = array();
$oldFunction = function(
    $phone, 
    $from, 
    $msgid,
    $type, 
    $time, 
    $name, 
    $message 
) use (&$old_function_called) {
    array_push($old_function_called, array( 
        'onGetMessage',
        array(
            $phone, 
            $from, 
            $msgid,
            $type, 
            $time, 
            $name, 
            $message             
        )
    ) );
};
$w->eventManager()->bind('onGetMessage', $oldFunction );

print( 'Test #1: onGetMessage\n' );
/*
rx  <message from="441234123456@s.whatsapp.net" id="1234567890-123" type="chat" t="1234567890">
rx    <notify xmlns="urn:xmpp:whatsapp" name="First LastName"></notify>
rx    <request xmlns="urn:xmpp:receipts"></request>
rx    <body>TestMessage</body>
rx  </message>
*/

// To be moved to config:
$node = new ProtocolNode("message",array(
    'from' => '441234123456',
    'id' => '1234567890-123',
    'type' => 'chat',
    't' => '1234567890'
), array(
    new ProtocolNode('notify', array('xmlns'=>'urn:xmpp:whatsapp','name'=>'First LastName'), NULL, ''),
    new ProtocolNode('request', array('xmlns'=>'urn:xmpp:receipts'), NULL, ''),
    new ProtocolNode('body', NULL, NULL, 'TestMessage')
), '' );

// Send the data into the framework:
$w->processInboundDataNode($node);

// Analyze the results:
$actual = $listener->getAndResetCapture();
// Assert expected result:
$expected = array(
    //First event raised:
    array( 
        // Event name:
        'onGetMessage',
        // Event Arguments:
        array(
            '$username',
            '441234123456',
            '1234567890-123',
            'chat',
            '1234567890',
            'First LastName',
            'TestMessage'
        )
    )
);

if( $expected === $actual 
    && $expected === $old_function_called ) {
    print( "Test Passed.\n");
} else {
    print( "Test Failed!!!!!\n" );
}

print( 'Test #2: onGetGroupMessage\n' );
/*
rx  <message from="441234123456-1234567890@g.us" id="1234567890-123" type="chat" t="1234567890" author="11231231234@s.whatsapp.net">
rx    <notify xmlns="urn:xmpp:whatsapp" name="Fun Guy"></notify>
rx    <request xmlns="urn:xmpp:receipts"></request>
rx    <body>Are you real, or are you a bot?</body>
rx  </message>
*/

$node = new ProtocolNode("message",array(
    'from' => '441234123456-1234567890@g.us',
    'id' => '1234567890-123',
    'type' => 'chat',
    't' => '1234567890',
    'author' => '11231231234@s.whatsapp.net'
), array(
    new ProtocolNode('notify', array('xmlns'=>'urn:xmpp:whatsapp','name'=>'Fun Guy'), NULL, ''),
    new ProtocolNode('request', array('xmlns'=>'urn:xmpp:receipts'), NULL, ''),
    new ProtocolNode('body', NULL, NULL, 'Are you real, or are you a bot?')
), '' );
// Send the data into the framework:
$w->processInboundDataNode($node);
// Analyze the results:
$actual = $listener->getAndResetCapture();
// Assert expected result:
$expected = array(
    //First event raised:
    array( 
        // Event name:
        'onGetGroupMessage',
        // Event Arguments:
        array(
            '$username',
            '441234123456-1234567890@g.us',
            '11231231234@s.whatsapp.net',
            '1234567890-123',
            'chat',
            '1234567890',
            'Fun Guy',
            'Are you real, or are you a bot?'
        )
    )
);

if( $expected === $actual ) {
    print( "Test Passed.\n");
} else {
    print( "Test Failed!!!!!\n" );
}

?>