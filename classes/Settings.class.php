<?php
ini_set('display_errors', 'true');
ini_set('error_reporting', E_ALL|E_NOTICE);

class_exists('MySQL', false) or include('MySQL.class.php');

class Settings {
	// Singleton instance for database connection parameters.
	private static $__dbConnectionParamsCache = null;

	//don't change these - you change these inside web admin - these are just the "defaults"
	private static $DEFAULT_SETTINGS_RAW =
"username = admin
passwd = admin
noticeEmails = you@mail.com
noticeFromEmail = you@mail.com
smtpServer = localhost
smtpServerPort = 25
flushLogsDays = 365
webIpACL = 10.0.0.0/8,192.168.0.0/16,127.0.0.0/24; cidr form
rssIpACL = 10.0.0.0/8,192.168.0.0/16,127.0.0.0/24; cidr form
";

	public static function getDBConnectionParams() {
		if (!is_array(Settings::$__dbConnectionParamsCache)) {
			$settingsInc = dirname(dirname(__FILE__)).'/dbSettings.include.php';
			if (file_exists($settingsInc)) include($settingsInc);
			if (!isset($dbHost)) $dbHost = 'localhost';
			if (!isset($dbUser)) $dbUser = 'phpmonitor';
			if (!isset($dbPasswd)) $dbPasswd = 'yoursecretpassword';
			if (!isset($dbDatabase)) $dbDatabase = 'phpmonitor';
			Settings::$__dbConnectionParamsCache = array(
				'host'=>$dbHost,
				'user'=>$dbUser,
				'passwd'=>$dbPasswd,
				'database'=>$dbDatabase,
			);
		}
		return Settings::$__dbConnectionParamsCache;
	}

	public static function getRawSettings() {
		$mysql = new MySQL();
		$rs = $mysql->runQuery("select settings from settings;");
		$row = mysql_fetch_array($rs);
		mysql_free_result($rs);
		$mysql->close();
		if (trim($row['settings']) != '') return $row['settings'];
		return Settings::$DEFAULT_SETTINGS_RAW;
	}

	public static function getSettings() {
		$settings = Settings::parseIniString(Settings::getRawSettings());
		if (!is_array($settings)) $settings = array();
		// Some fields are comma-separated, and need to be converted to arrays.
		Settings::convertCommaSeparatedToArray($settings, 'noticeEmails');
		return $settings;
	}

	public static function setRawSettings($settings) {
		$mysql = new MySQL();
		$sql="update settings set settings = '".mysql_real_escape_string($settings, $mysql->mysqlCon)."'";
		$mysql->runQuery($sql);
		$mysql->close();
	}

	public static function recalWorkers() {
		$mysql = new MySQL();
		$rs = $mysql->runQuery("select count(id) as cnt, frequency from monitors where active=1 group by frequency;");
		$workers = 0.0;
		while ($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$workers += (double)$row['cnt']/(double)$row['frequency'];
		}
		$workers = (int)ceil($workers)*60;
		$mysql->runQuery("update settings set cronIterations=$workers;");
		$mysql->close();
	}

	// Replacement for parse_ini_string(), which is only available in PHP 5.3 or later.
	public static function parseIniString($s) {
		$tmpfile = tempnam('/tmp', 'tmpIni');
		file_put_contents($tmpfile, $s);
		$iniData = parse_ini_file($tmpfile, true);
		unlink($tmpfile);
		return $iniData;
	}

	public static function convertCommaSeparatedToArray(&$settings, $key) {
		if ( (isset($settings[$key])) && (!is_array($settings[$key])) ) {
			$arr = explode(',', $settings[$key]);
			for ($i = 0, $n = count($arr); $i < $n; $i++) {
				$arr[$i] = trim($arr[$i]);
			}
			$settings[$key] = $arr;
		} else {
			$settings[$key] = array();
		}
	}
}

