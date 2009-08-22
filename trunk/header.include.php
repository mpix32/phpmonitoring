<?php
class_exists('Settings', false) or include('./classes/Settings.class.php');
class_exists('MySQL', false) or include('./classes/MySQL.class.php');
class_exists('Utilities', false) or include('./classes/Utilities.class.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>PHPMonitoring</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/table.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="imgs/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="scripts.js"></script>
<script type="text/javascript" src="sorttable.js"></script>
</head>
<body onload="bodyLoad();" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#eeeeee" class="trim"><img src="imgs/mailTop.gif" width="9" height="3"></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr align="left" valign="top">
    <td class="trim"><p ><br>
      <img src="imgs/logo.png" width="134" height="23"></p></td>
    <td width="100%" valign="top"><br>
      <br>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td bgcolor="#CCCCCC" class="trim">&nbsp;</td>
          <td width="100" align="center" bgcolor="#666666"><a href="logout.php" class="log">log out</a></td>
        </tr>
    </table></td></tr>
  <tr>
    <td width="170" align="right" valign="top">
	  <table width="150" border="0" cellpadding="0" cellspacing="0" background="imgs/navbg.gif" class="mainNav">
      <tr class="trim">
        <td width="10"><img src="imgs/clear.gif" width="8" height="8"></td>
        <td width="160" bgcolor="#eeeeee">
		<ul>
		  <li><a href="dashboard.php" class="main">Dashboard</a></li>
		  <li><a href="monitors.php" class="main">Monitors</a></li>
  		  <li><a href="setupMonitor.php" class="main">Add Monitor</a></li>
   		  <li><a href="settings.php" class="main">Settings</a></li>
          <li><a href="plugins.php" class="main">Plugins</a></li>
		 </ul>
	  </td>
      </tr>
    </table></td>
    <td width="100%" valign="top" class="mainCont">
