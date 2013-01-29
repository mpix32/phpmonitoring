<?php 
class_exists('Settings', false) or include('./classes/Settings.class.php');
class_exists('MySQL', false) or include('./classes/MySQL.class.php');
class_exists('Utilities', false) or include('./classes/Utilities.class.php');

$settings = Settings::getSettings();

//ip access list check
if(isset($settings['rssIpACL'])){
        $ips = explode(',', $settings['rssIpACL']);
        $acl = false;
        foreach($ips as $ip){
                if(Utilities::checkIpToNetwork($_SERVER['REMOTE_ADDR'], $ip)){
                        $acl=true;
                        break;
                };
        }
        if($acl===false) {
		echo('no acl match');
		exit();
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>phpMonitor</title>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<style type="text/css" media="screen">
body {
    margin: 0;
    font-family: Helvetica;
    background: #FFFFFF;
    color: #000000;
    overflow: hidden;
    -webkit-text-size-adjust: none;
}
body > h1 {
    box-sizing: border-box;
    margin: 0;
    padding: 10px;
    line-height: 20px;
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    text-shadow: rgba(0, 0, 0, 0.6) 0px -1px 0;
    text-overflow: ellipsis;
    color: #FFFFFF;
    background: #6d84a2 repeat-x;
    border-bottom: 1px solid #2d3642;
}
body > form,
body > ul {
    left: 0;
    width: 99%;
    margin: 0;
    padding: 0;
}
body > *[selected="true"] {
    display: block;
}
body > ul > li {
    margin: 0;
    border-bottom: 1px solid #E0E0E0;
    padding: 0;
    font-size: 18px;
    list-style: none;
}
body > ul > li > a {
    display: block;
    padding: 8px 32px 8px 8px;
    text-decoration: none;
    color: inherit;
}
body > ul > li > span {
    display: block;
    padding: 8px 32px 8px 8px;
    text-decoration: none;
    color: inherit;
}
div {
    margin:5px 0px 0px 3px;
}

div > h1 {
    font-size: 20px;
}
</style>
<link rel="apple-touch-icon" href="imgs/iphone.png" />
</head>
<body onload="setTimeout(function() { window.scrollTo(0, 1) }, 100);">
<h1>Current Issues</h1>
<ul>
<?php
$mysql = new MySQL();
$rs = $mysql->runQuery("
select name, lastError
from monitors 
where active = 1 and currentStatus = 0
order by lastError desc;
		");
		$none=true;
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$none=false;
			$whenText = Utilities::timeDiffString($row['lastError']);
			echo('<li><span>'.$row['name'].' ('.$whenText.')</span></li>');
		}
		if($none) {
			echo('<li><span>no current issues</span></li>');
		}
echo('</ul>');
echo('<h1>Previous Issues</h1>');
echo('<ul>');
		mysql_free_result($rs);
		$mysql = new MySQL();
		$rs = $mysql->runQuery("
select name, lastError
from monitors 
where active = 1 and currentStatus = 1
order by lastError desc limit 10;
		");
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$whenText = Utilities::timeDiffString($row['lastError']);
			echo('<li><span>'.$row['name'].' ('.$whenText.')</span></li>');
		}
		mysql_free_result($rs);


?>
</ul>
</body>
</html>
