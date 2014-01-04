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
$old_function1_called = array();
$old_function2_called = array();

// Function bound to variable:
$oldFunction = function(
    $phone, 
    $from, 
    $msgid,
    $type, 
    $time, 
    $name, 
    $message 
) use (&$old_function1_called) {
    array_push($old_function1_called, array( 
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

// Named funciton:
function oldFunction2(
    $phone, 
    $from, 
    $msgid,
    $type, 
    $time, 
    $name, 
    $message 
) {
    global $old_function2_called;
    
    array_push($old_function2_called, array( 
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

// Callback approach:
$w->eventManager()->bind('onGetMessage', $oldFunction );
// String approach:
$w->eventManager()->bind('onGetMessage', 'oldFunction2' );

print( "Test #1: onGetMessage\n" );
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
        'onSendMessageReceived',
        array(
            0 => '$username',
            // 1 = This is the current time.
            2 => '441234123456'
        )
    ),
    //Second event raised:
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
$old_expected = array($expected[1]);

unset($actual[0][1][1]); // Remove the time.

// Analyze the results:
if( $expected === $actual 
    && $old_expected === $old_function1_called
    && $old_expected === $old_function2_called ) {
    print( "Test 1 Passed.\n");
} else {
    print( "Test 1 Failed!!!!!\n" );
}
$old_function1_called = array();
$old_function2_called = array();

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
$old_expected = $expected;

print( "Test #2: Legacy Fire\n" );
$w->eventManager()->fire('onGetMessage', array(        
    '$username',
    '441234123456',
    '1234567890-123',
    'chat',
    '1234567890',
    'First LastName',
    'TestMessage'
) );

// Analyze the results:
$actual = $listener->getAndResetCapture();
if( $expected === $actual 
    && $old_expected === $old_function1_called
    && $old_expected === $old_function2_called ) {
    print( "Test 2 Passed.\n");
} else {
    print( "Test 2 Failed!!!!!\n" );
}
$old_function1_called = array();
$old_function2_called = array();
    

print( "Test #3: onGetGroupMessage\n" );
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
        'onSendMessageReceived',
        array(
            0 => '$username',
            // 1 = This is the current time.
            2 => '441234123456-1234567890@g.us'
        )
    ),    
    // Second event raised:
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
unset($actual[0][1][1]); // Remove the time.

if( $expected === $actual ) {
    print( "Test 3 Passed.\n");
} else {
    print( "Test 3 Failed!!!!!\n" );
}
     
        
?>