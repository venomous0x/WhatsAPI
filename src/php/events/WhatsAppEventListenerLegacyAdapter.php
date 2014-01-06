<?php
require_once 'WhatsAppEventListenerProxy.php';

/**
 * Implements the old legacy events.
 */
class WhatsAppEventListenerLegacyAdapter extends WhatsAppEventListenerProxy {
    /**
     *
     * @var string The event you want to handle.
     */
    protected $eventName;
    /**
     *
     * @var callable The callback when the event is fired.
     */
    protected $callback;


    /**
     * Constructor.
     *
     * @param string $event
     *   To be removed.
     */
    function __construct($eventName, $callback)
    {
        $this->eventName = $eventName;
        $this->callback = $callback;
    }
    
    protected function handleEvent($eventName, array $arguments) 
    {
        if( $this->eventName === $eventName ) {
            call_user_func_array( $this->callback, $arguments );
        }
    }

}

