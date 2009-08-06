<?php include('./requireLogin.include.php');?>
<?php include('./header.include.php');?>
      <table width="100%" border=0 cellpadding=1 cellspacing=2 >
<thead class="top">
<tr bgcolor="#990000">
	<td align=center>Settings</td>
</tr>
</thead>

<tr>
<td>
<?php
$rawSettings = isset($_POST['settings']) ? $_POST['settings'] : '';
if ($rawSettings !='') {
	Settings::setRawSettings($rawSettings);
	header('Location: settings.php');
	exit();
}
$rawSettings = Settings::getRawSettings();
?>

<form action="settings.php" method="post">
<table>
	<tr><td>Settings</td><td><textarea rows="15" cols="100" id="settings" name="settings"><?php echo($rawSettings); ?></textarea></td></tr>
	<tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Save Settings"/></td></tr>
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
