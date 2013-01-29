<?php include('./requireLogin.include.php');?>
<?php include('./header.include.php');?>

      <table width="100%" border=0 cellpadding=1 cellspacing=2 >
<thead class="top">
<tr bgcolor="#990000">
	<td align=center>Manage Monitors</td>
</tr>
</thead>

<tr>
<td>
<?php
$id = array_key_exists('id', $_GET) ? (int)$_GET['id'] : 0;
$del = array_key_exists('del', $_GET) ? (int)$_GET['del'] : 0;
$copy = array_key_exists('cpy', $_GET) ? (int)$_GET['cpy'] : 0;

$name = array_key_exists('name', $_POST) ? $_POST['name'] : '';
$frequency = array_key_exists('frequency', $_POST) ? (int)$_POST['frequency'] : 60;		//60 seconds
$notifyAdmin = array_key_exists('notifyAdmin', $_POST) ? (int)$_POST['notifyAdmin'] : 1;
$pluginType = array_key_exists('pluginType', $_POST) ? $_POST['pluginType'] : '';
$pluginInput = array_key_exists('pluginInput', $_POST) ? $_POST['pluginInput'] : '';
$active = array_key_exists('active', $_POST) ? (int)$_POST['active'] : 1;

$mysql = new MySQL();
if($copy !=0 && $id !=0) {
	$mysql->runQuery("insert into monitors(name,frequency,notifyAdmin,currentStatus,pluginType,pluginInput,active) (select name,frequency,notifyAdmin,currentStatus,pluginType,pluginInput,active from monitors where id=$id);");
	$id = $mysql->identity;
	Settings::recalWorkers();
	header("Location: setupMonitor.php?id=$id");
	exit();
}elseif($name !='' && $id !=0) {
	//update
	$sql="update monitors 
		set name ='$name', 
		frequency=$frequency, 
		notifyAdmin=$notifyAdmin,
		pluginType='$pluginType',
		pluginInput='".mysql_real_escape_string($pluginInput,$mysql->mysqlCon)."', 
	 active=$active where id = $id;";
	//echo($sql);
	$mysql->runQuery($sql);
	Settings::recalWorkers();
	header('Location: monitors.php');
	exit();

}elseif($name !=''){
	//insert
	$sql="insert into monitors (name,frequency,notifyAdmin,currentStatus,pluginType,pluginInput,active) values(
	'$name',
	$frequency,
	$notifyAdmin,
	1,
	'$pluginType',
	'".mysql_real_escape_string($pluginInput,$mysql->mysqlCon)."',	
	$active
	);";
	$mysql->runQuery($sql);
	Settings::recalWorkers();
	header('Location: monitors.php');
	exit();
}elseif($id!=0 && $del==1){
	//delete
	$mysql->runQuery("delete from monitors where id = $id;");
	$mysql->runQuery("delete from logging where monitorId = $id;");
	Settings::recalWorkers();
	header('Location: monitors.php');
	exit();
}

$rs = $mysql->runQuery("select * from monitors where id = $id");
if($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
	$name = $row['name'];
	$frequency = $row['frequency'];
	$notifyAdmin = $row['notifyAdmin'];
	$pluginType = $row['pluginType'];
	$pluginInput = $row['pluginInput'];
	$active = $row['active'];
}
mysql_free_result($rs);
?>

<form onload="populateDefaultInput();" name="monitorform" action="setupMonitor.php?id=<?php echo($id);?>" method="post">
<table>
	<tr><td>monitor id</td><td><?php if($id!==0){ echo($id);}?></td></tr>
	<tr><td>Installed Plugins</td><td><select onchange="javascript: populateDefaultInput();" name="pluginType">
	 <?php
	 if($pluginType=='') echo("<option>Pick One</option>\n");
foreach (scandir('plugins/') as $item) {
	$fn = './plugins/'.$item;
	if (   (is_file($fn))
		&& (($pos = strrpos($item,'.plugin.php')) !== false)
		&& (($pos+11) == strlen($item))
		&& ($item[0] != '.')   ) {
		$pluginName = substr($item, 0, strlen($item)-11);
		$pluginClassName = $pluginName.'Plugin';
		class_exists($pluginClassName, false) or include($fn);
		echo('<option value="'.htmlentities($pluginName).'"');
		if ($pluginName == $pluginType) echo(' selected');
		echo('>'.htmlentities($pluginName)."</option>\n");
	}
}
?></select></td></tr>
	<tr><td>Monitor Name</td><td><input type="text" size="75" maxlength="100" name="name" value="<?php echo($name); ?>"/></td></tr>
	<tr><td>Monitoring Frequency(seconds)</td><td><input size="5" type="text" name="frequency" value="<?php echo($frequency); ?>"/></td></tr>
	<tr><td>Alerts On(1=yes,0=no)</td><td><input size="2" type="text" name="notifyAdmin" value="<?php echo($notifyAdmin); ?>"/></td></tr>
	<tr><td>Active(1=yes,0=no)</td><td><input size="2" type="text" name="active" value="<?php echo($active); ?>"/></td></tr>
	<tr><td>Plugin Data</td><td><textarea rows="15" cols="130" id="pluginInput" name="pluginInput"><?php echo($pluginInput); ?></textarea></td></tr>
	<tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Save Settings"/></td></tr>
	<tr><td>&nbsp;</td><td><?php if($id!=0){?><a href="setupMonitor.php?id=<?php echo($id);?>&cpy=1">copy monitor</a><?php }?></td></tr>
	<tr><td>&nbsp;</td><td><?php if($id!=0){?><a onclick="return confirm('are you sure you want to delete?')" href="setupMonitor.php?id=<?php echo($id);?>&del=1">delete monitor</a><?php }?></td></tr>
</table>
</form>
</td>
</tr>
</tbody>
<tfoot class="footer">
<tr bgcolor="#990000">
<td colspan=14 align=center ><?php echo(date("F j, Y, g:i a"));?></td>
</tr>
</tfoot>
</table>

<?php include('./footer.include.php');?>
