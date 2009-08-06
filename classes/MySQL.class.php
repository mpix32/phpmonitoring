<?php
/*
*<blockquote>
*	How to use this class for updating queries:
*            $mysql = new MySQL();
*            $rs = $mysql->runQuery($sqlquery);
*            (use normal mysql_*() functions on $rs)
*            $mysql->close();
*</blockquote>
*/
class_exists('Settings', false) or include('Settings.class.php');

class MySQL {
	public $affectedRows = 0;
	public $identity = 0;
	public $mysqlCon = null;

	public function MySQL($connectionParams=null) {
		if (!is_array($connectionParams)) $connectionParams = Settings::getDBConnectionParams();
		$this->connect($connectionParams);
	}

	/**
	 * Requires an array containing the keys host,user,passwd,database.
	 * This is called automatically by the constructor, so normally wouldn't
	 * need to be called unless you've called close() on an instance and want
	 * to re-connect it.
	 */
	public function connect($connectionParams) {
		$this->close();
		$this->mysqlCon = mysql_connect(
			$connectionParams['host'],
			$connectionParams['user'],
			$connectionParams['passwd'],
			true,
			65536);
		if ($this->mysqlCon !== false) {
			mysql_select_db($connectionParams['database'], $this->mysqlCon);
		}
		
		// If no servers are responding, throw an exception.
		if ($this->mysqlCon === false) {
			throw new Exception(
				'Unable to connect to any db servers - last error: ' .
				mysql_error());
		}

		return $this->mysqlCon;
	}

	/**
	* Run a query on the currently connected database server.
	* @param $query The SQL query to run.
	* @return resource The results of <code>mysql_unbuffered_query()</code>, which represents
	* a resource that can be used to fetch the returned rows from the query, or <code>false</code>
	* if error.
	*/
	public function runQuery($query) {
		$result = @mysql_unbuffered_query($query, $this->mysqlCon);
		if ($result === false) {
			throw new Exception('Database query failed: ' . mysql_error($this->mysqlCon));
		}
		$this->affectedRows = mysql_affected_rows($this->mysqlCon);
		$this->identity = mysql_insert_id($this->mysqlCon);
		return $result;
	}

	public function close() {
		if ($this->mysqlCon !== null) {
			mysql_close($this->mysqlCon);
			$this->mysqlCon = null;
		}
	}
}
?>
