<?php include('./requireLogin.include.php');?>
<?php include('./header.include.php');?>

<table width="100%" border="0" cellpadding="1" cellspacing="2">
<thead class="top">
<tr bgcolor="#990000">
        <td align="center">Monitors</td>
</tr>
</thead>
</table>

<table class="sortable" width="100%" border="0" cellpadding="1" cellspacing="2">
<tr class="sub">
<th align=center>Monitor</td>
<th align=center>Plugin</td>
<th align=center>Frequency</td>
<th align=center>Status</td>
<th align=center>Last Run</td>
<th align=center>Last Error</td>
<th align=center>Active</td>
<th align=center>Notices</td>
<th align=center>Reports</td>
</tr>
<!--<tbody class="grey">-->
<?php
$mysql = new MySQL();
$rs = $mysql->runQuery("
select *
from monitors m
order by active, name;
");
while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
?>
<tr class="grey" bgcolor="#dddddd">
<td align="left">&nbsp;&nbsp;<a href="setupMonitor.php?id=<?php echo urlencode($row['id']);?>"><?php echo htmlspecialchars($row['name']);?></a></td>
<td align="left">&nbsp;&nbsp;<?php echo htmlspecialchars($row['pluginType']);?></td>
<td align="left">&nbsp;&nbsp;<?php echo htmlspecialchars($row['frequency']);?></td>
<td align="left">&nbsp;&nbsp;<span class="<?php if($row['currentStatus']==1){echo('green');}else{echo('red');}?>"><?php if($row['currentStatus']==1){echo('OK');}else{echo('ERROR');}?></span></td>
<td align="left">&nbsp;&nbsp;<?php echo htmlspecialchars($row['lastRun'])?></td>
<td align="left">&nbsp;&nbsp;<?php echo htmlspecialchars($row['lastError'])?></td>
<td align="left">&nbsp;&nbsp;<span class="<?php if($row['active']==1){echo('green');}else{echo('red');}?>"><?php if($row['active']==1){echo('Yes');}else{echo('No');}?></span></td>
<td align="left">&nbsp;&nbsp;<span class="<?php if($row['notifyAdmin']==1){echo('green');}else{echo('red');}?>"><?php if($row['notifyAdmin']==1){echo('Yes');}else{echo('No');}?></span></td>
<td align="center"><a href="monitorLog.php?id=<?php echo htmlspecialchars($row['id']);?>">Log</a></td>
</tr>
<?php
}
mysql_free_result($rs);
?>
<!--</tbody>-->
<tfoot class="footer">
<tr bgcolor="#990000">
<td colspan="9" align="center"><?php echo htmlspecialchars(date("F j, Y, g:i a"));?></td>
</tr>
</tfoot>
</table>

<?php include('./footer.include.php');?>
