<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');

class OpenVPNSSHPlugin extends Plugin {

	//this is default input, prefilled when monitor instance is setup - input is stored in sqllite for the monitor and are only used here
	public static $rawInput =
"host = 127.0.0.1                ; host name to check
loginUser = root                ; login user name
pingHost = 127.0.0.1            ; host to ping through VPN
autoRestartFailedOpenVPN = yes	; automatically restart OpenVPN if failed
";

	public function about() {
		return array(
			'name'=>'OpenVPNSSH',
			'description'=>'Uses SSH to login to remote host via keys and attempt to ping through an OpenVPN tunnel to a host on the other end.',
			'author'=>'roncemer',
			'version'=>'1.0'
		);
	}

	/*
	$input is array taken from single input field of values to be used, returns output
	*/
	public function runPlugin($input=array()) {
		$output=Plugin::$output;	// set defaults for all output
		$result = shell_exec('ssh '.$input['loginUser'].'@'.$input['host'] .' "ping -c 1 -q \"'.$input['pingHost'].'\" >/dev/null 2>&1 && echo \"GOOD\""');
		$output['htmlEmail'] = 1;

		$mv = trim($result);
		$output['measuredValue'] = $mv;
		if ($mv == 'GOOD') {
			$output['currentStatus'] = 1;
			$output['returnContent'] = "All normal - pinging through OpenVPN is working.";
		} else {
			$output['currentStatus'] = 0;
			$rc =
				"<pre>Could not ping through OpenVPN from {$input['host']}".
				" to {$input['pingHost']}.\n";
			if ($input['autoRestartFailedOpenVPN']) {
				$rc .= shell_exec(
					'ssh '.$input['loginUser'].'@'.$input['host'].
						' "/etc/init.d/openvpn restart"').
					"\n\nOpenVPN has been restarted automatically.\n".
					"If it doesn't recover, manual intervention may be required.\n";
			}
			$rc .= "</pre>";
			$output['returnContent'] = $rc;
		}
		return $output;
	}
}
?>
