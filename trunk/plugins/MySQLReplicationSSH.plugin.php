<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');

class MySQLReplicationSSHPlugin extends Plugin {

	//this is default input, prefilled when monitor instance is setup - input is stored in sqllite for the monitor and are only used here
	public static $rawInput =
"masterHost = localhost          ; mysql master host name
masterLoginUser = root          ; mysql master login user name
masterMySQLUser = root          ; mysql master user name for connecting to mysql
masterMySQLPasswd =             ; mysql master password for connecting to mysql (optional)
slaveHost = localhost           ; mysql slave host name
slaveLoginUser = root           ; mysql slave login user name
slaveMySQLUser = root           ; mysql slave user name for connecting to mysql
slaveMySQLPasswd =              ; mysql slave password for connecting to mysql (optional)
maxSlaveLagTime = 60            ; max slave lag time seconds before we consider it behind
autoRestartFailedSlave = yes    ; automatically issue stop slave/start slave if behind
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

		$masterCmd = 'echo \\"select LATEST_TIMESTAMP from REPLICATION_TIMESTAMP;\\" | mysql -s -u \\"'.$input['masterMySQLUser'].'\\"';
		if ($input['masterMySQLPasswd'] != '') {
			$masterCmd .= ' -p \\"'.$input['masterMySQLPasswd'].'\\"';
		}
		$masterCmd .= ' mysql 2>&1';
		///echo "masterCmd: $masterCmd\n";
		$masterResult = shell_exec('ssh '.$input['masterLoginUser'].'@'.$input['masterHost'].' "'.$masterCmd.'"');
		///echo "masterResult: $masterResult\n";

		$slaveCmd = 'echo \\"select LATEST_TIMESTAMP from REPLICATION_TIMESTAMP;\\" | mysql -s -u \\"'.$input['slaveMySQLUser'].'\\"';
		if ($input['slaveMySQLPasswd'] != '') {
			$slaveCmd .= ' -p \\"'.$input['slaveMySQLPasswd'].'\\"';
		}
		$slaveCmd .= ' mysql 2>&1';
		///echo "slaveCmd: $slaveCmd\n";
		$slaveResult = shell_exec('ssh '.$input['slaveLoginUser'].'@'.$input['slaveHost'].' "'.$slaveCmd.'"');
		///echo "slaveResult: $slaveResult\n";

		$output['htmlEmail'] = 1;

		$slaveLagTime = max(0, (int)trim($masterResult)-(int)trim($slaveResult));
		$output['measuredValue'] = $slaveLagTime;
		if ($slaveLagTime < $input['maxSlaveLagTime']) {
			$output['currentStatus'] = 1;
			$output['returnContent'] = "All normal - slave lag time of {$slaveLagTime} is less than {$input['maxSlaveLagTime']}.";
		} else {
			$output['currentStatus'] = 0;
			$rc =
				"<pre>MySQL slave {$input['slaveHost']} is behind {$input['masterHost']}".
				" by {$slaveLagTime} seconds.\n";
			if ($input['autoRestartFailedSlave']) {
				$slaveRestartCmd = 'echo \\"stop slave; start slave;\\" | mysql -s -u \\"'.$input['slaveMySQLUser'].'\\"';
				if ($input['slaveMySQLPasswd'] != '') {
					$slaveRestartCmd .= ' -p \\"'.$input['slaveMySQLPasswd'].'\\"';
				}
				$slaveRestartCmd .= ' mysql 2>&1';
				///echo "slaveRestartCmd: $slaveRestartCmd\n";
				$rc .= shell_exec('ssh '.$input['slaveLoginUser'].'@'.$input['slaveHost'].' "'.$slaveRestartCmd.'"').
					"\n\nThe slave has been restarted automatically.\n".
					"If it doesn't recover, manual intervention may be required.\n";
				///echo "rc: $rc\n";
			}
			$rc .= "</pre>";
			$output['returnContent'] = $rc;
		}
		return $output;
	}
}
?>
