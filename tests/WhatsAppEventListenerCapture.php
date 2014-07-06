<?php
require_once '../src/events/WhatsAppEventListenerProxy.php';

/**
 * Records all calls to the WhatsAppEventListener so they can be analyzed 
 *  and asserted by the testing framework.
 */
class WhatsAppEventListenerCapture extends WhatsAppEventListenerProxy {
    private $capture = array();
    
    protected function handleEvent($eventName, array $arguments) {
        array_push($this->capture, array($eventName,$arguments));
    }
    
    public function getAndResetCapture() {
        $ret = $this->capture;
        $this->capture = array();
        return $ret;
    }
}
