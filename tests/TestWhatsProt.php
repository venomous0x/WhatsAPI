<?php
require_once '../src/Whatsprot.class.php';

/**
 * Used to expose protected methods to the testing framework.
 */
class TestWhatsProt extends WhatsProt {
    public function processInboundDataNode(ProtocolNode $node) {
        parent::processInboundDataNode($node);
    }
}
