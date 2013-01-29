<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');

class DiskUsageSSHPlugin extends Plugin {

	//this is default input, prefilled when monitor instance is setup - input is stored in sqllite for the monitor and are only used here
	public static $rawInput =
"host = localhost                ; host name to check
loginUser = root                ; login user name
warningAtPercentFilled = 90     ; maximum disk full percentage, above which we issue notification
";

	public function about() {
		return array(
			'name'=>'DiskUsageSSH',
			'description'=>'Uses SSH to login to remote host via keys and check hard disk usage with df command.',
			'author'=>'mikerlynn',
			'version'=>'1.0'
		);
	}

	/*
	$input is array taken from single input field of values to be used, returns output
	*/
	public function runPlugin($input=array()) {
		$output=Plugin::$output;///set defaults for all output
		$cmd = shell_exec('ssh -o "ConnectTimeout 10" '.$input['loginUser'].'@'.$input['host'] .' df');
		preg_match_all('/([0-9]+)%/', $cmd, $result);
		$output['returnContent'] = "<pre>$cmd</pre>";
		$output['htmlEmail'] = 1;

		foreach($result[1] as $d) {
			$d = (int)$d;
			$output['measuredValue'] = $output['measuredValue'].",".$d;	//last measured value - will show the error when errors - will show last partition if not.
			if($d>=$input['warningAtPercentFilled']) {
					$output['currentStatus'] = 0;
					return $output;
			} else {
					$output['currentStatus'] = 1;
			}			
		}
		return $output;
	}
}
?>
