/*
function bodyLoad(){
	var sPath = window.location.pathname;
	var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
	if(sPage=='setupMonitor.php') populateDefaultInput();
}
*/
function populateDefaultInput(){
	loadXMLDoc('getPluginInfo.php?p='+document.monitorform.pluginType.value);
}
var xmlhttp;
function loadXMLDoc(url) {
	xmlhttp=null;
if (window.XMLHttpRequest){
	// code for IE7, Firefox, Opera, etc.
	xmlhttp=new XMLHttpRequest();
}else if (window.ActiveXObject){
	// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}
if (xmlhttp!=null){
	xmlhttp.open("GET",url,false);
	xmlhttp.send(null);
	document.getElementById('pluginInput').value=xmlhttp.responseText;
} else {
	alert("Your browser does not support XMLHTTP.");
  }
}