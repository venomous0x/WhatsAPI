<?php
require "protocol.class.php";
$writer = new BinTreeNodeWriter(getDictionary());
$reader = new BinTreeNodeReader(getDictionary());
$data = $writer->StartStream("s.whatsapp.net", "iPhone-2.8.2-5222");
printhexstr($data, "start");
?>

