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
		select l.dateTime, m.name, l.measuredValue
		from monitors m 
			inner join logging l on m.id = l.monitorId
			inner join (
				select max(id) as id
				from logging
				where status = 0
			) le on le.id = l.id
		where m.currentStatus = 1		
		order by l.dateTime desc limit 10;
		");
		//--group by m.name
		while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
			echo("<tr>");
			echo("<td>".Utilities::timeDiffString($row['failureDateTime'])."</td>");
			echo("<td>".$row['name']."</td>");
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
		select max(l.dateTime) as dateTime, l.measuredValue, l.status, m.name, m.currentStatus
		from monitors m inner join logging l on l.monitorId = m.id
		where l.status = 0 and m.currentStatus = 0
		group by m.name
		order by max(l.dateTime) desc limit 20;
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
