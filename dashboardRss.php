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




header('Content-Type: text/xml');

echo('<?xml version="1.0" ?>');
//echo('<link rel="apple-touch-icon" href="imgs/logo.png"/>');
echo("\n");
echo('<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">');
echo("\n");
echo('<channel>');
echo("\n");
echo('<title>phpMonitor Issues</title>');
echo("\n");
echo('<description>Monitored items with recent issues.</description>');
echo("\n");
echo('<language>en-us</language>');
echo("\n");
echo('<link>'.Utilities::getRequestURLWithQueryString().'</link>');
echo("\n");
	echo('<ttl>1</ttl>');
echo("\n");	

		$mysql = new MySQL();
		$rs = $mysql->runQuery("
select min(l.dateTime) as failureDateTime, l.measuredValue, m.name
from monitors m 
        inner join (
                select max(id) as id, monitorId
                from logging
                where status = 1
                group by monitorId
        ) le on le.monitorId = m.id
        inner join logging l on m.id = l.monitorId and le.id < l.id

where m.currentStatus = 0 and l.status = 0
group by m.name
order by min(l.dateTime) desc limit 50;
		");
		$none=true;
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$none=false;
$whenText = Utilities::timeDiffString($row['failureDateTime']);
			echo('<item>');
			echo("\n");
			echo('<title>'.htmlentities($row['name'].' ('.$whenText.') </title>');
			echo("\n");
			echo('<description>'.htmlentities(strip_tags($row['measuredValue'])).'</description>');
			echo("\n");
			echo('<guid isPermaLink="false">phpmonitoring:current:'.htmlentities($row['name'].'-'.$row['failureDateTime']).'- ('.$whenText.')</guid>');
			echo("\n");
			//echo('<link></link>');
			echo('</item>');
			echo("\n");
		}
		if($none) {
			echo('<item>');
			echo("\n");
			echo('<title>Status Good</title>');
			echo("\n");
			echo('<description>No monitored items are having problems.</description>');
			echo("\n");
			echo('<guid isPermaLink="false">phpmonitoring:statusGood'.time().'</guid>');
			echo("\n");
			//echo('<link></link>');
			echo('</item>');
			echo("\n");
		}

		mysql_free_result($rs);
		$mysql = new MySQL();
		$rs = $mysql->runQuery("
		select min(l.dateTime) as failureDateTime, l.measuredValue, m.name
from monitors m 
        inner join (
                select max(id) as id, monitorId
                from logging
                where status = 0
                group by monitorId
        ) le on le.monitorId = m.id
        inner join logging l on m.id = l.monitorId and le.id = l.id

where m.currentStatus = 1
group by m.name
order by min(l.dateTime) desc limit 10;
		");
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$whenText = Utilities::timeDiffString($row['failureDateTime']);
			echo('<item>');
			echo("\n");
			echo('<title>'.htmlentities($row['name'].' - ('.$whenText.') '.$row['failureDateTime']).'</title>');
			echo("\n");
			echo('<description>'.htmlentities(strip_tags($row['measuredValue'])).'</description>');
			echo("\n");
			echo('<guid isPermaLink="false">phpmonitoring:Previous:'.htmlentities($row['name'].'-'.$row['failureDateTime']).'- ('.$whenText.')</guid>');
			echo("\n");
			//echo('<link></link>');
			echo('</item>');
			echo("\n");
		}
		mysql_free_result($rs);



echo('</channel>');
echo("\n");
echo('</rss>');
echo("\n");
?>
