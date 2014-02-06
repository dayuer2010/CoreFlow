<?php
  
  function checkViewAccess(){
  	$cfg_xml = new SimpleXMLElement(file_get_contents('.config.xml'));
    $allowedViewIP=$cfg_xml->xpath('//allow_view_IPs');
  	if(!preg_match('/'.$allowedViewIP[0].'/',trim($_SERVER["REMOTE_ADDR"]))){exit;}
  }

	function checkWriteExecuteAccess(){
  	$cfg_xml = new SimpleXMLElement(file_get_contents('.config.xml'));
    $allowedWXIP=$cfg_xml->xpath('//allow_wx_IPs');
  	return(preg_match('/'.$allowedWXIP[0].'/',trim($_SERVER["REMOTE_ADDR"])));
}

    
  function connectToDB(){
 	  $cfg_xml = new SimpleXMLElement(file_get_contents('.config.xml'));
		$dbHostname=$cfg_xml->xpath('//db/host');
		if(checkWriteExecuteAccess()){
			$dbUsername=$cfg_xml->xpath('//db/user');
			$dbPassword=$cfg_xml->xpath('//db/user/@passwd');
		} else {
			$dbUsername=$cfg_xml->xpath('//db/user_public');
			$dbPassword=$cfg_xml->xpath('//db/user_public/@passwd');
		}
		$dbh = mysql_connect($dbHostname[0], $dbUsername[0], $dbPassword[0]) 
			or die("Unable to connect to MySQL: ".mysql_error());
		return($dbh);	
  }	


  function getCfgVal($xpath){
 	  $cfg_xml = new SimpleXMLElement(file_get_contents('.config.xml'));
  	$cfg_val=$cfg_xml->xpath($xpath);
  	return($cfg_val[0]);
  }


	function getDefaultDB(){
		return getCfgVal('//db/database');
  }

	function setDefaultDB($selectedDB ,$dbh){
	  if(! mysql_select_db($selectedDB ,$dbh)) die("Could not set database $selectedDB !!"."<br><pre>".mysql_error()."</pre>");
  }

    
  function getAppName(){
  	return getCfgVal('//app_name');
  }


?>
