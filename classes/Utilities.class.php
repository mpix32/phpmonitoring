<?php

class Utilities {

	public static function timeDiffString($timestamp, $now = null, $detailed=false, $max_detail_levels=8, $precision_level='second', $textDesc=true){

		//timestamp - $timestamp - says timestamp but really takes it in yyyy-mm-dd time.

		preg_match('/^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$/', $timestamp, $matches);
		$timestamp = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);

		if($now==null) $now = time();

		#If the difference is positive "ago" - negative "away"
		($timestamp >= $now) ? $action = 'away' : $action = 'ago';
   
		# Set the periods of time
		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

		$diff = ($action == 'away' ? $timestamp - $now : $now - $timestamp);
   
		$prec_key = array_search($precision_level,$periods);
   
		# round diff to the precision_level
		$diff = round(($diff/$lengths[$prec_key]))*$lengths[$prec_key];
   
		# if the diff is very small, display for ex "just seconds ago"
		if ($diff <= 10) {
			$periodago = max(0,$prec_key-1);
			$agotxt = $periods[$periodago].'s';
			if($textDesc) return "just $agotxt $action";
			return "$agotxt";
		}
   
		# Go from decades backwards to seconds
		$time = "";
		for ($i = (sizeof($lengths) - 1); $i>0; $i--) {
			if($diff > $lengths[$i-1] && ($max_detail_levels > 0)) {        # if the difference is greater than the length we are checking... continue
				$val = floor($diff / $lengths[$i-1]);    # 65 / 60 = 1.  That means one minute.  130 / 60 = 2. Two minutes.. etc
				$time .= $val ." ". $periods[$i-1].($val > 1 ? 's ' : ' ');  # The value, then the name associated, then add 's' if plural
				$diff -= ($val * $lengths[$i-1]);    # subtract the values we just used from the overall diff so we can find the rest of the information
				if(!$detailed) { $i = 0; }    # if detailed is turn off (default) only show the first set found, else show all information
				$max_detail_levels--;
			}
		}
 
		# Basic error checking.
		if($time == "") {
			return "Error-- Unable to calculate time.";
		} else {
			if($textDesc) return $time.$action;
			return $time;
		}

	}

	/**
	* @return string The protocol ('http' or 'https') for the current request.
	*/
	public static function getProtocol() {
		$serverPort = $_SERVER['SERVER_PORT'];
		if (   ($serverPort == '443')
			|| (   (array_key_exists('HTTPS', $_SERVER))
				&& (strtolower($_SERVER['HTTPS']) == 'on')   )   ) {
			$protocol = 'https';
		} else {
			$protocol = 'http';
		}
		return $protocol;
	}

	/**
	* @return string The server host for the current request.
	*/
	public static function getServerHost() {
		if (array_key_exists('HTTP_HOST', $_SERVER)) {
			return $_SERVER['HTTP_HOST'];
		}
		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			return $_SERVER['SERVER_NAME'];
		}
		return null;
	}

	/**
	* @return string The server port for the current request.
	*/
	public static function getServerPort() {
		return $_SERVER['SERVER_PORT'];
	}

	/**
	* @return string The URL to the document root for the current request.
	*/
	public static function getRootURL() {
		$serverHost = Utilities::getServerHost();
		$protocol = Utilities::getProtocol();
		$rootURL = $protocol . '://' . $serverHost;
		return $rootURL;
	}

	/**
	* @return string The request URL, without the query string for the current request.
	*/
	public static function getRequestURLWithoutQueryString() {
		$requestURL = Utilities::getRequestURLWithQueryString();
		$qidx = strpos($requestURL, '?');
		if ($qidx !== false) $requestURL = substr($requestURL, 0, $qidx);
		return $requestURL;
	}

	/**
	* @return string The request URL, with the query string if present, for the current request.
	*/
	public static function getRequestURLWithQueryString() {
		$requestURL = Utilities::getRootURL();
		if (array_key_exists('REQUEST_URI', $_SERVER)) {
			$requestURL .= $_SERVER['REQUEST_URI'];
		}
		return $requestURL;
	}

	public static function checkIpToNetwork($ip, $network) {
                // The $ip parameter must be a valid, parseable IP address.
                if (($ipLong = ip2long($ip)) === false) {
                        return false;
                }
                // Break the network address into two pieces, separated by the slash.
                // there must be exactly two pieces, or it is invalid.
                $networkPieces = explode('/', $network);
                if (count($networkPieces) != 2) {
                        return false;
                }
                // Parse the IP address portion of the network address.
                // If unparseable, it is invalide.
                if (($networkLong = ip2long($networkPieces[0])) === false) {
                        return false;
                }
                // Count the dots in the netmask portion.  It must be either three (if it's a netmask)
                // or zero (if it's a most-significant-bits count).  Parse or calculate the netmask
                // accordingly.
                $counts = count_chars($networkPieces[1], 1);
                $idx = ord('.');
                $dotCount = isset($counts[$idx]) ? $counts[$idx] : 0;
                if ($dotCount == 3) {
                        if (($mask = ip2long($networkPieces[1])) === false) {
                                return false;
                        }
                } else if ($dotCount == 0) {
                        $bitCount = (int)$networkPieces[1];
                        if ( ($bitCount < 0) || ($bitCount > 32) ) {
                                // Invalid bit count.
                                return false;
                        } else if ($bitCount == 0) {
                                // << 32 in PHP doesn't seem to do anything; so we work around it here.
                                $mask = 0;
                        } else if ($bitCount == 32) {
                                // Prevent << 0, which effects no change.
                                $mask = 0xffffffff;
                        } else {
                                $mask = (0xffffffff << (32 - $bitCount)) & 0xffffffff;
                        }
                } else {
                        // Invalid dot count in mask.
                        return false;
                }
                // In order for the IP address to be in the network, the biwise-and of the IP address
                // with the netmask, must equal the bitwise-and of the network address with the netmask.

		return ($ipLong & $mask) == ($networkLong & $mask);
	}

}
