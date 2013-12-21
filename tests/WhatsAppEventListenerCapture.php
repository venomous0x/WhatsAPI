<?php
require_once '../src/php/events/WhatsAppEventListenerProxy.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WhatsAppEventListenerCapture
 *
 * @author daniel
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
