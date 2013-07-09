<?php
/*
 *  Usage:
 *  ?phone=YourPhonenumber&pass=YourBase64Password&u[]=number1&u[]=number2 etc.
 *
 */
require_once "contacts.php";

$username = @$_GET["phone"];
if ($username == null || empty($username)) {
    die("Missing param: phone");
}

$password = @$_GET["pass"];
if ($password == null || empty($password)) {
    die("Missing param: pass");
}

$debug = true;

$contacts = @$_GET["u"];
if ($contacts == null || empty($contacts)) {
    die("Missing param: u[]");
}

try {
    $sync = new WhatsAppContactSync($username, $password, $contacts, $debug);
    $res = $sync->executeSync();
} catch (Exception $e) {
    die("Error: " . $e->GetMessage());
}

foreach ($res as $contact) {
    var_dump($contact);
}
?>
