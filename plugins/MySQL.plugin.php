<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');
class_exists('Timer', false) or include(dirname(dirname(__FILE__)).'/classes/Timer.class.php');

class MySQLPlugin extends Plugin {
	public static $rawInput =
"testQuery = select now();               ; test query which returns some data

[mysqlConnection]
hostname = yourmysqlhostname            ; hostname for MySQL server to be tested
username = yourmysqlusername            ; username for logging into MySQL server
password = yourmysqlpassword            ; password for logging into MySQL server
databasename = yourmysqldatabasename    ; database name in which to run the test query
";

	public function about() {
		return array (
			'name'=>'MySQL',
			'description'=>'Preforms a simple mysql query and returns success if it gets at least 1 result back, otherwise it errors',
			'author'=>'mikerlynn',
			'version'=>'1.0'
		);
	}

	/*
	$input is array taken from single input field of values to be used
	*/
	public function runPlugin($input=array()) {
		$output=Plugin::$output;///set defaults for all output

		$t = new Timer();
		$t->start();

		try{
			$mysql = new MySQL($input['mysqlConnection']);
			$rs = $mysql->runQuery($input['testQuery']);
			if($row = mysql_fetch_array($rs, MYSQL_NUM)) {
				$output['currentStatus'] = 1;
				$output['returnContent'] = "All normal\n" . $row[0];
				$output['measuredValue'] = $row[0];
			}else{
				$output['currentStatus'] = 0;
				$output['returnContent'] = "no results were returned for the query" . $input['testQuery'];	
			}
		} catch (Exception $e) {
			$output['currentStatus'] = 0;
			$output['returnContent'] = "Query $input[testQuery] errored:\n".$e->getMessage();	
		}
		$output['responseTimeMs'] = (int)$t->stop();		
		return $output;
	}

}
?>
