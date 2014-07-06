<?php
session_start();

class JSONResponse
{
    public $messages = array();
    public $profilepic = "";

}

$method = $_POST["method"];
switch ($method) {
    case "sendMessage":
        $target = $_POST["target"];
        $message = $_POST["message"];
        $outbound = $_SESSION["outbound"];
        $outbound[] = array("target" => $target, "body" => $message);
        $_SESSION["outbound"] = $outbound;
        break;
    case "pollMessages":
        $inbound = @$_SESSION["inbound"];
        $_SESSION["inbound"] = array();
        $profilepic = @$_SESSION["profilepic"];
        $ret = new JSONResponse();
        if ($profilepic != null && $profilepic != "") {
            $ret->profilepic = $profilepic;
        }
        $_SESSION["profilepic"] = "";
        if (count($inbound) > 0) {
            foreach ($inbound as $message) {
                $ret->messages[] = $message;
            }
        }
        echo json_encode($ret);
        break;
}