<?php
//always include plugin class in the top of all plugins
class_exists('Plugin', false) or include(dirname(dirname(__FILE__)).'/classes/Plugin.class.php');
class_exists('Timer', false) or include(dirname(dirname(__FILE__)).'/classes/Timer.class.php');

class CustomClientDomainsPlugin extends Plugin {
	//this is default input, prefilled when monitor instance is setup - input is stored in sqllite for the monitor and are only used here
	public static $rawInput =
"[mysqlConnection]
host = yourmysqlhostname            ; hostname for MySQL server to be tested
user = yourmysqlusername            ; username for logging into MySQL server
passwd = yourmysqlpassword          ; password for logging into MySQL server
database = yourmysqldatabasename    ; database name in which to run the test query
";

	public function about() {
		return array (
			'name'=>'CustomClientDomains',
			'description'=>'Monitors client redirect domains in admarket client table',
			'author'=>'mike',
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

		$output['currentStatus'] = 0;
		$output['returnContent'] = '';	
		$downCount = 0;
		$output['measuredValue'] = $downCount;
		$mysqlc['host'] = $input['mysqlConnection']['host'];
		$mysqlc['user'] = $input['mysqlConnection']['user'];
		$mysqlc['passwd'] = $input['mysqlConnection']['passwd'];
		$mysqlc['database'] = $input['mysqlConnection']['database'];

		try{
			$mysql = new MySQL($mysqlc);
			$rs = $mysql->runQuery("select distinct domainName from client where statusId = 1 and domainName <> ''");
			while($row = mysql_fetch_array($rs, MYSQL_NUM)) {
				$test = CustomClientDomainsPlugin::doHTTPGet("http://".$row[0].'/m/api/health.php');
				if(trim($test)!='healthy'){
					$downCount++;
					$output['returnContent'] .= $row[0]."	down\n";
					$output['measuredValue'] = $output['returnContent'];
				}
			}
			if($downCount!=0){
				$output['currentStatus'] = 0;
			}else{
				$output['currentStatus'] = 1;
			}

		} catch (Exception $e) {
			$output['currentStatus'] = 0;
			$output['measuredValue'] = "CustomClientDomainsPlugin error:\n".$e->getMessage();	
		}
		$output['responseTimeMs'] = (int)$t->stop();		
		return $output;
	}

	private static function doHTTPGet($requestURL, $connectTimeout = 60, $requestTimeout = 60) {
		$con = curl_init();
		curl_setopt($con, CURLOPT_URL, $requestURL);
		curl_setopt($con, CURLOPT_HEADER, false);
		curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($con, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($con, CURLOPT_BUFFERSIZE, 16384);
		curl_setopt($con, CURLOPT_CONNECTTIMEOUT, (int)$connectTimeout);
		curl_setopt($con, CURLOPT_TIMEOUT, (int)$requestTimeout);
		curl_setopt($con, CURLOPT_FAILONERROR, true);
		$headers = array(
				'Accept-Language: en-us,en;q=0.5',
				'Cache-Control: max-age=0',
				'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
				'Accept-Encoding:',
				'User-Agent: Mozilla/5.0',
				'Keep-Alive: 300'
			);
		curl_setopt($con, CURLOPT_HTTPHEADER, $headers);

		try {
			$data = curl_exec($con);
		} catch (Exception $e) {
			return 'http error - '.$e->getMessage();//this makes sure it always returns content if there's an error
		}
		curl_close($con);
		return $data;
	}


}
