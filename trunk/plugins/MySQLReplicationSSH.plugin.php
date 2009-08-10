<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');

class MySQLReplicationSSHPlugin extends Plugin {

	//this is default input, prefilled when monitor instance is setup - input is stored in sqllite for the monitor and are only used here
	public static $rawInput =
"masterHost = localhost              ; mysql master host name
masterLoginUser = root              ; mysql master login user name
masterMySQLUser = root              ; mysql master user name for connecting to mysql
masterMySQLPasswd =                 ; mysql master password for connecting to mysql (optional)
slaveHost = localhost               ; mysql slave host name
slaveLoginUser = root               ; mysql slave login user name
slaveMySQLUser = root               ; mysql slave user name for connecting to mysql
slaveMySQLPasswd =                  ; mysql slave password for connecting to mysql (optional)
maxSlaveLagTimeBeforeRestart = 60   ; max slave lag time seconds before we issue stop slave/start slave (0=never restart slave)
maxSlaveLagTimeBeforeNotify = 300   ; max slave lag time seconds before we issue notification email
";

	public function about() {
		return array(
			'name'=>'MySQLReplicationSSH',
			'description'=>'Uses SSH to login to remote MySQL master and slave servers, and compares the timestamp in a user-created REPLICATION_TIMESTAMP table in the mysql database. Requires that the user create a table and an update service on the master. For instructions, see plugins/support_files/MySQLReplicationSSH/README.txt.',
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
		$restartThresh = (int)$input['maxSlaveLagTimeBeforeRestart'];
		if ( ($restartThresh > 0) && ($slaveLagTime >= $restartThresh) ) {
			$slaveRestartCmd = 'echo \\"stop slave; start slave;\\" | mysql -s -u \\"'.$input['slaveMySQLUser'].'\\"';
			if ($input['slaveMySQLPasswd'] != '') {
				$slaveRestartCmd .= ' -p \\"'.$input['slaveMySQLPasswd'].'\\"';
			}
			$slaveRestartCmd .= ' mysql 2>&1';
			echo "Restart slave:\n";
			echo shell_exec(
				'ssh '.$input['slaveLoginUser'].'@'.$input['slaveHost'].' "'.$slaveRestartCmd.'"'
			);
			echo "\n\n\n";
		}
		if ($slaveLagTime < $input['maxSlaveLagTimeBeforeNotify']) {
			$output['currentStatus'] = 1;
			$output['returnContent'] = "All normal - slave lag time of {$slaveLagTime} is less than {$input['maxSlaveLagTimeBeforeNotify']}.";
		} else {
			$output['currentStatus'] = 0;
			$output['returnContent'] =
				"<pre>MySQL slave {$input['slaveHost']} is behind {$input['masterHost']}".
				" by {$slaveLagTime} seconds, which exceeds {$input['maxSlaveLagTimeBeforeNotify']} seconds.\n</pre>";
		}
		return $output;
	}
}
?>
