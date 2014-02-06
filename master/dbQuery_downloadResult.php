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

?>
<?php $csvContent_delimiter=trim($_REQUEST['delimiter']); $browser = $_SERVER['HTTP_USER_AGENT']; if (strpos(strtoupper($browser),"WINDOWS") > 0 ){$fileName='tableContent.xls'; } else { if (strpos(strtoupper($browser),"LINUX") > 0 ){$fileName='tableContent.csv'; } else {if ($csvContent_delimiter == 'tab'){$fileName='tableContent.xls';} else { $fileName='tableContent.xls';}}} header("Content-type: application/octet-stream",TRUE); header("Content-Disposition: attachment; filename=$fileName",TRUE);
	#header("Pragma: no-cache",TRUE);
	#header("Expires: 0",TRUE);

	include_once("common_connect.php");  checkViewAccess();
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
	
	if(trim($_REQUEST['database'])!=""){$selectedDB=trim($_REQUEST['database']);}
	setDefaultDB($selectedDB ,$dbh);

  $sqlDelimiter=trim($_REQUEST['sqlDelimiter']);
  if($sqlDelimiter==""){$sqlDelimiter=";";}

	$csvContent_query=trim($_REQUEST['csvContent_query']);
	if( (strpos($csvContent_query,"%0D")>=0) || (strpos($csvContent_query,"%0A")>=0)){ 
	#	$csvContent_query=urldecode($csvContent_query);
	}
	$csvContent_query=str_replace("\\","",$csvContent_query);
	#$csvContent_query=str_replace("\t"," ",$csvContent_query);
	if (! $csvContent_query){ 
		print "ERROR: database query is not defined!\n"; exit;	
	}

	$query=$csvContent_query;
		# execute temporary queries if any
		$queryItems=explode($sqlDelimiter,$query);
		$lastQueryId=sizeof($queryItems)-1;
		for ($k=0; $k<=($lastQueryId-1); $k++){
			$querySmall=$queryItems[$k];
			$result = mysql_query($querySmall);
			if (! $result){
				$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
				break;
			}
		}


	$querySmall=$queryItems[$lastQueryId];

	#print("\n $querySmall \n");
	$result = mysql_query($querySmall);
	$content="";
	if ($result){
		#print("result=$result\n");
		$j=0;
		while (mysql_field_name($result, $j)){
			if ($csvContent_delimiter == 'tab'){
				if ( $j>0){ $content.="\t";}
				$content .= trim(mysql_field_name($result, $j));
			} else {
				if ( $j>0){ $content.=",";}
				$content .= '"'.str_replace('"','`',mysql_field_name($result, $j)).'"';
			}
			$j++;
		}
		$content .= "\n";
		print $content; 
		$i=0;
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			#print("\n$i --- $row\n");
			$line="";
			$j=0;
			foreach ( $row as $cIndex => $cVal ){
				if ($csvContent_delimiter == 'tab'){
					if ( $j>0){ $line.="\t";}
					$cValNorm=trim($cVal);
					$line .= $cVal;
				} else {
					# assume delimiter is comma
					if ( $j>0){ $line.=",";}
					$cValNorm=str_replace('"','`',$cVal);
					$line .= '"'.$cVal.'"';
				}
				$j++;
			}
			$line .= "\n";
			print $line;
			$i++;
		}
	} else {
		die("Could not execute!: <pre>$csvContent_query \n\n".mysql_error()."  </pre>");
	}


	
?>

