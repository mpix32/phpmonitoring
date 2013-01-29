<?php include('./requireLogin.include.php');?>
<?php include('./header.include.php');?>
<?php
$id = array_key_exists('id', $_GET) ? (int)$_GET['id'] : 0;
?>

      <table width="100%" border=0 cellpadding=1 cellspacing=2 >
<thead class="top">
<tr bgcolor="#990000">
	<td colspan="7" align="center">Monitor #<?php echo($id);?> log</td>
</tr>
</thead>

<tr class="sub">
<td align=center>Event Time</td>
<td align=center>Status</td>
<td align=center>Response Time(ms)</td>
<td align=center>Measured Value</td>
</tr>
<tbody class="grey">
<?php
$mysql = new MySQL();
$rs = $mysql->runQuery("
select *
from logging
where monitorId = $id
order by dateTime desc limit 2000;
");
while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
?>
<tr bgcolor="#dddddd">
<td align="left">&nbsp;&nbsp;<?php echo($row['dateTime']);?></td>
<td align="left">&nbsp;&nbsp;<span class="<?php if($row['status']==1){echo('green');}else{echo('red');}?>"><?php if($row['status']==1){echo('OK');}else{echo('ERROR');}?></span></td>
<td align="left">&nbsp;&nbsp;<?php echo($row['responseTimeMs']);?></td>
<td align="left">&nbsp;&nbsp;<?php echo(htmlspecialchars($row['measuredValue']))?></td>
</tr>
<?
}
mysql_free_result($rs);
?>
</tbody>
<tfoot class="footer">
<tr bgcolor="#990000">
<td colspan="7" align="center"><?php echo(date("F j, Y, g:i a"));?></td>
</tr>
</tfoot>
</table>

<?php include('./footer.include.php');?>
