#!/usr/bin/php
<?php
set_time_limit(0);
ini_set('memory_limit', '25M');		//TODO: need to change this?  let individual plugings control it too is good
ini_set('display_errors', 'true');
ini_set('error_reporting', E_ALL);
ini_Set('display_startup_errors', 'On');


//classes
class_exists('Settings', false) or include('./classes/Settings.class.php');
class_exists('MySQL', false) or include('./classes/MySQL.class.php');
class_exists('PHPMailer', false) or include('./classes/Phpmailer.class.php');
class_exists('Timer', false) or include('./classes/Timer.class.php');
class_exists('SMS', false) or include('./classes/SMS.class.php');

//load settings
$settings = Settings::getSettings();

//don't do this everytime so rand  - which probably isnt that bad
if(rand(1,50)===1){
	echo("clearing logging table....\n");
	$mysql = new MySQL();
	$mysql->runQuery("delete from logging where DATE_ADD(dateTime, INTERVAL ".$settings['flushLogsDays']." DAY) < now();");
	$mysql->close();
}

$cronIterations=0;
$mysql = new MySQL();
$rs = $mysql->runQuery("select cronIterations from settings;");
if($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
	$cronIterations=$row['cronIterations'];
}
$mysql->close();

//this script run's till there is no more work to do
for ($i = 1; $i <= $cronIterations; $i++) {

	$mysql = new MySQL();
	//get one at a time only, put a lock on so we only get one
	$mysql->runQuery("LOCK TABLES monitors WRITE;");
	//echo("LOCKED\n");
	$rs = $mysql->runQuery("select id, name, frequency, lastRun, pluginType, pluginInput, notifyAdmin, notifyAdminSMS, active from monitors where lastRun = '' or lastRun is null or (now() > DATE_ADD(lastRun, INTERVAL frequency SECOND) and active=1) limit 1;");
	if($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
		$id=$row['id'];
		$name=$row['name'];
		$frequency=$row['frequency'];
		$lastRun=$row['lastRun'];
		$pluginType=$row['pluginType'];
		$pluginInput=$row['pluginInput'];
		$notifyAdmin=$row['notifyAdmin'];
		$notifyAdminSMS=$row['notifyAdminSMS'];
		$mysql->runQuery("update monitors set lastRun=now() where id = $id;");
		$mysql->runQuery("UNLOCK TABLES;");
		$mysql->close();
		//echo("UNLOCKED\n");

		$pluginClass = $pluginType.'Plugin';

		//run pluggin
		class_exists($pluginClass, false) or include('./plugins/'.$pluginType.'.plugin.php');
		$input = Settings::parseIniString($pluginInput);
		eval('$output = '.$pluginClass.'::runPlugin($input);');
		echo(date('Y-m-d H:i:s')."\t$pluginType\t$id\t$name\tStarted\n");
		
		$mysql = new MySQL();

		$t = new Timer();
		$t->start();

		$sql = sprintf(
			'update monitors set currentStatus = %d where id = %d;',
			$output['currentStatus'],
			$id
		);
		$mysql->runQuery($sql);
		if($output['currentStatus']==0) $mysql->runQuery("update monitors set lastError=now() where id = $id;");

		//notify anyone?
		$rs = $mysql->runQuery("select * from logging where monitorId = $id order by dateTime desc limit 1;");
		if($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$previousStatus=$row['status'];
		}else{
			$previousStatus=0;
		}
		//echo("prev: $previousStatus  - current: $output[currentStatus]\n");
		if( ($notifyAdmin==1) && ($previousStatus!=$output['currentStatus']) ){
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Host = $settings['smtpServer'];
			$mail->SetFrom($settings['noticeFromEmail']);
			foreach($settings['noticeEmails'] as $email){
				$mail->AddAddress($email);
				echo("email notice sent to $email\n");
			}			
			if($output['currentStatus']==1){
				$mail->Subject = "$pluginType @ $name [Status: Ok | Response Time: $output[responseTimeMs]ms]";
			}else{
				$mail->Subject = "$pluginType @ $name [Status: Error | Response Time: $output[responseTimeMs]ms]";
			}
			//if a pluggin doesnt return content the email will error
			if( (isset($output['returnContent'])) && 
				($output['returnContent']!='') ){
				$body = $output['returnContent'];
			}else{
				$body = 'plugin returned no data';
			}
			if( (boolean)$output['htmlEmail'] ){
					$mail->AltBody = $body;
					$mail->MsgHTML($body);
					$mail->IsHTML=(boolean)$output['htmlEmail'];
			}else{
				$mail->Body = $body;
			}
			if(!$mail->Send()) {
				echo("Mailer Error: " . $mail->ErrorInfo);
			}
			if($notifyAdminSMS==1){
				if(!SMS::send($mail->Subject)){
					echo("SMS error\n");
				}else{
					echo("SMS Sent\n");
				}
			}

		}
		
		//log output
		$sql="insert into logging (monitorId,dateTime,responseTimeMs,measuredValue,returnContent,status) values($id,now(),$output[responseTimeMs],'".mysql_real_escape_string($output['measuredValue'],$mysql->mysqlCon)."','".mysql_real_escape_string($output['returnContent'],$mysql->mysqlCon)."',$output[currentStatus]);";
		$mysql->runQuery($sql);
		
		echo(date('Y-m-d H:i:s')."\t$pluginType\t$id\t$name\tEnded\t" . round($t->stop(),0) ." ms \n");	

	}else{
		$mysql->runQuery("UNLOCK TABLES;");
		//echo("UNLOCKED\n");
		//echo "no more work to do\n";
		sleep(1);
		//usleep(500*1000);	//500 milliseconds
	}

	$mysql->close();

}
