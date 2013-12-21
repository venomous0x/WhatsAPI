<?php
require_once '../src/php/whatsprot.class.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WhatsProtTest
 *
 * @author daniel
 */
class TestWhatsProt extends WhatsProt {
    public function processInboundDataNode(ProtocolNode $node) {
        parent::processInboundDataNode($node);
    }
    public function setKey($key) {
        $this->reader->setKey($key);
    }
}
