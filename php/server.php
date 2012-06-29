<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
include("whatsapp.class.php");


$api = new API($_POST['id'], $_POST['action']);

class API{

	private $_id;
	private $_wa;
	
	function API($id, $action){
		$this->_id = $id;
		$this->_id['udid'] = md5(strrev($this->_id['imei']));
		$this->_wa = new WhatsApp($this->_id['cc'].$this->_id['pn'], $this->_id['udid']);
		$this->_wa->Connect();
		$this->_wa->Login();
		$this->_run($action);
	}
	
	private function _run($action){
		if(method_exists($this->_wa, $action['method'])){
			$pstr = "";
			foreach($action['params'] as $param)$pstr.="\"".$param."\", ";
			$pstr = substr($pstr, 0, -2);
			eval("\$this->_wa->".$action['method']."(".$pstr.");");
		}else $this->_err("INEXISTENT METHOD");
	}
	
	private function _err($msg){
		echo "ERROR: ".$msg;
	}
	
}

