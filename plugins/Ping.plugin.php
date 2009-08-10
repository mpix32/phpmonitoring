<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');

class PingPlugin extends Plugin {
	//this is default input, prefilled when monitor instance is setup - input is stored in database for the monitor and are only used here
	public static $rawInput =
"host = yahoo.com                ; the host to ping
maxResponseTimeMS = 300         ; max response time (if exceeded, we send out a notification)
maxPacketLoss = 0               ; max packet loss (if exceeded, we send out a notification)
";

	public function about() {
		return array (
			'name'=>'Ping',
			'description'=>'icmp pings a host',
			'author'=>'mikerlynn',
			'version'=>'1.0'
		);
	}

	/*
	$input is array taken from single input field of values to be used
	*/
	public function runPlugin($input=array()) {
		$output=Plugin::$output;///set defaults for all output
		$cmd = shell_exec('ping -c 1 '.$input['host']);
		preg_match_all('/([0-9]+)%/', $cmd, $packetLoss);
		preg_match_all('/time=([0-9]+)./', $cmd, $responseTime);

		$responseTime = isset($responseTime[1][0]) ? $responseTime[1][0] : 0;
		$packetLoss = isset($packetLoss[1][0]) ? $packetLoss[1][0] : 100;

		$output['measuredValue'] = "$responseTime,$packetLoss";

		if($responseTime > $input['maxResponseTimeMS']) {
			$output['currentStatus'] = 0;
			$output['returnContent'] = "Response time $responseTime has exceeded max configured value of $input[maxResponseTimeMS] ms.";
			return $output;
		}else{
			$output['currentStatus'] = 1;
		}
		if ($packetLoss > $input['maxPacketLoss']) {
			$output['currentStatus'] = 0;
			$output['returnContent'] = "Packet loss $packetLoss ms has exceeded max configured value of $input[maxPacketLoss] ms.";
			return $output;
		}else{
			$output['currentStatus'] = 1;
		}

		$output['returnContent'] = "All normal [Packet loss:$packetLoss ms | Response Time: $responseTime]";
		return $output;
	}
}
?>
