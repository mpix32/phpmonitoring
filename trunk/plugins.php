<?php include('./requireLogin.include.php');?>
<?php
$listType = isset($_GET['t']) ? $_GET['t'] : 'html';
if($listType=='html'){
?>
	<?php include('./header.include.php');?>
	<table width="100%" border=0 cellpadding=1 cellspacing=2 >
	<thead class="top">
	<tr bgcolor="#990000">
		<td colspan="4" align=center>Plugins</td>
	</tr>
	</thead>
	<tr class="sub">
	<td align=center>Plugin</td>
	<td align=center>Version</td>
	<td align=center>Description</td>
	<td align=center>Author</td>
	</tr>
<?php
}
foreach(scandir('plugins/') as $item) {
	if( (is_file('plugins/'.$item)) && (strpos($item,'.plugin.php')!==false) ){
		$p=explode('.',$item);
		$pluginClassName = $p[0].'Plugin';		
		class_exists($pluginClassName, false) or include('./plugins/'.$p[0].'.plugin.php');
		$__inst = new $pluginClassName;
		$classInfo = $__inst->about();
		unset ($__inst);
		if($listType=='html'){
?>
		<tbody class="grey">
			<tr bgcolor="#dddddd">
				<td align=left>&nbsp;&nbsp;<?php echo($classInfo['name']); ?></td>
				<td align=left>&nbsp;&nbsp;<?php echo($classInfo['version']); ?></td>
				<td align=left>&nbsp;&nbsp;<?php echo($classInfo['description']); ?></td>
				<td align=left>&nbsp;&nbsp;<?php echo($classInfo['author']); ?></td>
			</tr>
<?php
		}else{
			echo(" * $classInfo[name] - $classInfo[version] - $classInfo[description] - $classInfo[author]");
			echo("\n\n");

		}
	}
}
?>
<?php
if($listType=='html'){
?>
	</tbody>
	<tfoot class="footer">
	<tr bgcolor="#990000">
	<td colspan="14" align="center">&nbsp;</td>
	</tr>
	</tfoot>
	</table>
	<?php include('./footer.include.php');?>
<?php 
}
?>
