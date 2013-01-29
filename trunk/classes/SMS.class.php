<?php
class_exists('TextUtilities', false) or include('TextUtilities.class.php');

class SMS {

	// Send the message.
	public static function send($message = "test message", $group = "support") {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER , TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

		$param = "APIKEY=" . urlencode("6f82f2853e7f5323a4a87e29145a55cf");
		$param .= "&EmailMode=" . urlencode("FALSE");
		$param .= "&IMMode=" . urlencode("FALSE");
		$param .= "&SMSMode=" . urlencode("TRUE");
		$param .= "&SBMode=" . urlencode("FALSE");
		$param .= "&Description=" . urlencode($message);
	//	$param .= "&EmailSubject=" . urlencode("This is a test message.");
	//	$param .= "&EmailMessage=" . urlencode("This is a test message#1<br><br>This is a test message#2<br><br>This is a test message#3");
	//	$param .= "&IMMessage=" . urlencode("This is a test message. - IM");
		$param .= "&SMSMessage=" . urlencode($message);
		$param .= "&ListNames=" . urlencode($group);
		$param .= "&SendLater=" . urlencode("FALSE");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		curl_setopt($ch, CURLOPT_URL, "http://trumpia.com/api/sendtolist.php");

		//execute curl and parse to an object of class SimpleXMLElement
		$data = curl_exec($ch);
		$result = TextUtilities::parseBetweenText(
			$data, 
			'<STATUSCODE>', 
			'</STATUSCODE>', 
			false, 
			false,
			true);
		if($result == "1") {
			return true;
		}else {
			return false;
		} 

	}

}