<?php
	/*
		Requests a new sms code from the server.
		$phone must be the phone number without country code or leading zeros
	*/
	function request_code($cc, $phone, $method='sms', $id="", $language=null, $countrycode=null)
	{
		// User agent - WARNING! Do not change unless you also change the TokenPrefix!
		$UserAgent = "WhatsApp/2.3.53 S40Version/14.26 Device/Nokia302";
	
		// Build token
		$TokenPrefix = 'PdA2DJyKoUrwLw1Bg6EIhzh502dF9noR9uFCllGk1354754753509';
		$Token = $TokenPrefix . $phone;
		$Token = md5($Token);
		
		// Set language and country code if not given
		if($language == null)
		{
			$language = 'en';
		}
		if($countrycode == null)
		{
			$countrycode = 'US';
		}
		
		// Create md5 hash of id (Note: If no id has been given, a hash needs to be created from an empty string
		$id = md5($id);
		
		// Build url
		$Url = "https://v.whatsapp.com/v2/code?cc=$cc&in=$phone&lc=$countrycode&lg=$language&mcc=000&mnc=000&method=$method&id=$id&token=$Token&c=cookie";
		
		// Initiate Curl
		$ch = curl_init($Url);
		
		// Set useragent and Accept header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, $UserAgent);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // WARNING: Little hack here! This makes CURL accept any peer!
		
		// Get response
		$Response = curl_exec($ch);
		
		// Close response
		curl_close($ch);
		
		// Check for errors
		if(strpos($Response, '"missing_param","param":"c"') || strpos($Response, '"bad_param","param":"c"'))
		{
			throw new Exception('The request that was made was not an HTTPS request.');
		}
		else if(strpos($Response, 'You have received a WhatsApp authorization code'))
		{
			throw new Exception('The request that was made was not an HTTPS request and the parameter \'c\' was given.');
		}
		
		// Return response
		return json_decode($Response);
	}
?>