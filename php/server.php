<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
include("whatsapp.class.php");


$api = new API($_POST['id'], $_POST['action'], $_POST['nickname']);

class API{

	private $_id;
	private $_wa;
	private $_nickname;
	
	function API($id, $action, $nickname){
		$this->_id = $id;
		$this->_id['udid'] = md5(strrev($this->_id['imei']));
		$this->_nickname = (strlen($nickname)>0) ? $nickname : "Unknown";
		$this->_wa = new WhatsApp($this->_id['cc'].$this->_id['pn'], $this->_id['udid'], $this->_nickname);
		$this->_wa->Connect();
		$this->_wa->Login();
		$this->_run($action);
	}
	
	private function _run($action){
		if(method_exists($this->_wa, $action['method'])){
			$pstr = "";
			if(count($action['params'])){
				foreach($action['params'] as $param)$pstr.="\"".$param."\", ";
				$pstr = substr($pstr, 0, -2);
			}
			eval("\$this->_wa->".$action['method']."(".$pstr.");");
		}else $this->_err("INEXISTENT METHOD");
	}
	
	private function _err($msg){
		echo "ERROR: ".$msg;
	}
	
}

