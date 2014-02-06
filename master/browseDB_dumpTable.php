<?php 
/**
*
* Copyright (c) 2012-2013 Mount Sinai Hospital, Toronto, Ontario, 
* Copyright (c) 2012-2013 DTU/CSIG Linding Lab
*
* LICENSE:
*
* This is free software; you can redistribute it
* and/or modify it under the terms of the GNU General
* Public License as published by the Free Software Foundation;
* either version 3 of the License, or (at your option) any
* later version.
*
* This software is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public
* License along with the source code.  If not, see <http://www.gnu.org/licenses/>.
*
*
*/
include_once('common_connect.php'); checkViewAccess();
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=tableDump.sql");
	header("Pragma: no-cache");
	header("Expires: 0");

	$tableName=trim($_REQUEST['tableName']);
	if ($tableName == ""){print "Table name not defined!"; exit;}
	
        $crtDatabase=trim($_REQUEST['database']);
        
        $cfg_xml = new SimpleXMLElement(file_get_contents('.config.xml'));
                $dbHostname=$cfg_xml->xpath('//db/host');
                $dbUsername=$cfg_xml->xpath('//db/user');
                $dbPassword=$cfg_xml->xpath('//db/user/@passwd');
                $dbDatabase=$cfg_xml->xpath('//db/database');
        if($crtDatabase=='') { $crtDatabase=$dbDatabase[0];}


  if($tableName=="*"){$tableName="";}; ##### * is to dump all tables from the default database
  print "    \n";
	print shell_exec("/usr/bin/mysqldump --single-transaction -h$dbHostname[0] -u$dbUsername[0] $crtDatabase  $tableName -p$dbPassword[0]");


	
?>

