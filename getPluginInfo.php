<?php
$allowLoginForm = false;
include('./requireLogin.include.php');

$plugName = isset($_GET['p']) ? $_GET['p'] : '';
if ($plugName != '') {
	foreach (scandir('plugins/') as $item) {
		if ( (is_file('plugins/'.$item)) && (strpos($item,'.plugin.php') !== false) ) {
			$p = explode('.',$item);
			if ($p[0] == $plugName) {
				$pluginClassName = $p[0].'Plugin';
				class_exists($pluginClassName, false) or include('./plugins/'.$p[0].'.plugin.php');
				eval('echo '.$pluginClassName.'::$rawInput;');
			}
		}
	}
}
?>
