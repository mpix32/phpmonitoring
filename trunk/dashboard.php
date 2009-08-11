<?php include('./requireLogin.include.php');?>
<?php include('./header.include.php');?>

<table width="100%" border="0" cellpadding="1" cellspacing="2">
<thead class="top">
<tr bgcolor="#990000">
        <td align="center">Dashboard</td>
</tr>
</thead>
</table>
	<fieldset class="statField">
		<legend>Previous Issues</legend>
		<table class="dashboard" border="0" cellspacing="2" cellpadding="2">
			<tr>
			<td><strong>When</strong></td>
			<td><strong>Monitor</strong></td>
			<td><strong>Measured Value</strong></td>
			</tr>
		<?php
		$mysql = new MySQL();
		//this query isn't right - the measured value isnt the latest errord measured value
		$rs = $mysql->runQuery("
select q.failureDateTime, min(q.recoveryDateTime) as recoveryDateTime, q.name, q.measuredValue from (
	select f.dateTime as failureDateTime, r.dateTime as recoveryDateTime, m.name, f.measuredValue
	from monitors m
	inner join (select monitorId, max(id) as failureId from logging where status = 0 group by monitorId) fid on fid.monitorId = m.id
	inner join logging f on f.id = fid.failureId
	inner join logging r on r.monitorId = fid.monitorId and r.id > fid.failureId and r.status <> 0
	where m.currentStatus = 1 order by f.id desc, r.id
) q
group by q.failureDateTime, q.name, q.measuredValue
order by q.failureDateTime, q.name
limit 10;
		");
		//--group by m.name
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$whenText = Utilities::timeDiffString($row['failureDateTime']);
			echo("<tr>");
			echo("<td>{$row['failureDateTime']} - {$row['recoveryDateTime']} ($whenText)</td>");
			echo("<td>".htmlentities($row['name'])."</td>");
			echo("<td>".$row['measuredValue']."</td>");
			echo("</tr>");
		}
		mysql_free_result($rs);
		?>
		</table>
	</fieldset>

	<fieldset class="statField">
		<legend>Current Issues</legend>
		<table class="dashboard" border="0" cellspacing="2" cellpadding="2">
			<tr>
			<td><strong>When</strong></td>
			<td><strong>Monitor</strong></td>
			<td><strong>Measured Value</strong></td>
			</tr>
		<?php
		$mysql = new MySQL();
		$rs = $mysql->runQuery("
select ld.dateTime, ld.measuredValue, m.name
from monitors m
inner join (select monitorId, max(id) as id from logging where status = 0 group by monitorId) ldid on ldid.monitorId = m.id
inner join logging ld on ld.id = ldid.id
where m.currentStatus = 0 limit 20;
		");
		$none=true;
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			$none=false;
			echo("<tr>");
			echo("<td>".Utilities::timeDiffString($row['dateTime'])."</td>");
			echo("<td>".$row['name']."</td>");
			echo("<td>".$row['measuredValue']."</td>");
			echo("</tr>");
		}
		if($none) echo("<tr><td colspan=3>none</td></tr>");
		mysql_free_result($rs);
		?>
		</table>
	</fieldset>



<?php include('./footer.include.php');?>
