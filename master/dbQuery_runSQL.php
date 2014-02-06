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
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
	setDefaultDB($selectedDB ,$dbh);


	$qeryError;

	$format = trim($_REQUEST['format']);
	if ($format == ""){$format="html";}  # otherwise can be format delimited
	$customAnalysisId=trim($_REQUEST['Custom_Analysis_Id']);
  if($customAnalysisId !=''){$format='delimited';} # Custom_Analysis_Id will also force to obtain the Query from DB_script  


	$sqlDelimiter	=trim($_REQUEST['sqlDelimiter']);
	if ($sqlDelimiter == "") {$sqlDelimiter=";";};
	$query		=trim($_REQUEST['query']);
	$queryLimit	=trim($_REQUEST['limit']);
	$showResultOnly =trim($_REQUEST['showResultOnly']);


	$queryExample='create temporary table TMP select "alpha" as Struct,1 as Id union select "beta",2 union select "gamma",3;
select * from TMP
limit 100
';


  if($customAnalysisId !=''){
  	$queryExample='';
		$result = mysql_query("select Db_script,Sql_delimiter from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id=$customAnalysisId");
		if (! $result){
				$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
				print $qeryError;
				exit;
		}
  	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	  $sqlDelimiter	=$row{'Sql_delimiter'};
	  if ($sqlDelimiter == "") {$sqlDelimiter=";";};
  	$query=$row{'Db_script'};
  }   


	# due to the fact that we have magic_quotes_gpc=On in php.ini ; normally we should use stripslashes
	$query=stripslashes($query);
	#$query		=str_replace('\\', "", $query);	# the stupid php converts quotes in backslash quotes


	# get rid of last ; if any
	if (substr($query,strlen($query)-1) == $sqlDelimiter){
		$query = substr($query,0,strlen($query)-1);
	}


	if ($query ==""){
		$query=$queryExample;
		$queryExample="";
	}

	# simply ignore php warnings etc.
	set_error_handler('error_handler');
	function error_handler() {
	}

  $finalNmbColumns=0;
	if ($query != ""){
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
		$delimitedResult='';
		{
			$result = mysql_query($querySmall);
			$num	= mysql_numrows($result);
		
			if ($result){
				$j=0; 
		    $delimitedRow='';
				while (mysql_field_name($result, $j)){
					$name=mysql_field_name($result, $j);
					$colNames[$j]=$name;
					if(	$j>0) {$delimitedRow .= "\t";}
		      $delimitedRow .= $name;
					$j++;
				}
				$delimitedResult .= $delimitedRow."\n";
				$finalNmbColumns=$j;
	
				$i=0;		
				while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
					$delimitedRow='';
					$j=0;
					foreach ( $colNames as $cIndex => $cVal ){
						$tableDescr[$i][$cIndex]=$row{$cVal};
						if(	$j>0) {$delimitedRow .= "\t";}
						$delimitedRow .= $row{$cVal};
						$j++;
					}
					$delimitedResult .= $delimitedRow."\n";
					$i++;
				}
			} else {
				$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
				#die("Could not execute! \n <pre>".mysql_error()."</pre><br/><pre> $query </pre>");
			}
		}
	}


	mysql_close($dbh);

  if($format == 'delimited'){
  	if($qeryError != '') {print $qeryError; exit;}
  	print $delimitedResult;
  	exit;
  }
  
  ### the rest is for 'html' format

	# show space for query if we have a query
	print '<html>
		<head>
			<LINK REL="stylesheet" HREF="css/generic.css" type="text/css"/>
			<title>SQL query result</title>
      <script src="css/generic.js" language="javascript" type="text/javascript"></script>
		</head>
		<BODY topmargin="10" leftmargin="10" onload="focus();">

	

		';


	#show results if we have a query
	# if queryError is due to database access error
  if(!checkWriteExecuteAccess()){	### we do not have full rights
  	if($qeryError !=""){print  "Note that you do not have privileges to <b>change data</b> on the database! Please contact authors to obtain additional privileges if needed!<br<br>\n"; }
  }
	print $qeryError;
	
	print '
<div style="display:inline" name="tip_H_controlPanel" id="tip_H_controlPanel" class="tooltip">
				<form name="mainForm" action="" method="post" style="display:inline">
				<input type="hidden" name="showResultOnly" value="'.$showResultOnly.'">
				<img style="cursor:pointer" src="images/close_object.png" width="20" border="0" alt="close Control Panel" title="close Control Panel" onclick="rollOut(\'tip_H_controlPanel\')">&nbsp; 
				 <b>Queries:</b>
				(using delimiter:<input style="font-size:80%; font-weight:bold; color:blue" name="sqlDelimiter" size="1" value="'.$sqlDelimiter.'">)
	
				
	<br>
				</form>
		<form name="goToExcel" action="open_excel_DB.php" method="post">
				<input type="hidden" name="csvContent" value="">
				<input type="hidden" name="delimiter" value="tab">
				<input type="hidden" name="database" value="">
				<input type="hidden" name="sqlDelimiter" value="'.$sqlDelimiter.'">
				<textarea name="csvContent_query" style="display:none;visibility:hidden">
				'.$query.'
				</textarea>
			</form>
			<script>
				function openExcel(){
				  document.goToExcel.sqlDelimiter.value=document.mainForm.sqlDelimiter.value;
					document.goToExcel.submit();
				}

			</script>
</div>';


		#$cornerIcon='# <img width="24px" style="border-left:2px inset #FF0000; border-bottom:2px inset #FF0000; border-right:2px outset #FFFFFF; border-top:2px outset #FFFFFF;" src="images/download.png" title="Download table in tab delimited format" onclick="openExcel()"> ';
		$cornerIcon='# ';
		if($finalNmbColumns>0){
		  print buildTable($cornerIcon, $tableDescr,$colNames, '' ).'</body></html>'."\n";
    } else {
    	if($qeryError =="") {print "Execution finished OK!";}
    }

	#$browser = $_SERVER['HTTP_USER_AGENT']; #get_browser(null, true);
	#print "<br><pre>...\n$browser\n...</pre>";

	############################################################################################################
	function buildTable($title, $rows,$cols,$link){
		#print the header		
		$html ='
		<table class="v" border="0" cellpadding="1" cellspacing="1" id="aTable" name="aTable" align="center">
			<tr class="v_header_sorter">
			<td class="corner">
			<img src="images/table_corner.gif" width="10" border="0" align="top">
			<span>'.$title.'</span>
			<img src="images/null.gif" width="10" border="0">
			</td>
		';
		foreach ( $cols as $cIndex => $cVal ){
			$html .= '
				<th class="headerList"  align="center">
				<span id="header_content_1" name="header_content_1" >'
				.$cVal
				.'</span><img src="images/null.gif" width="10" border="0">
                                  <div style="display:inline" name="tip_H_'.$cIndex.'" id="tip_H_'.$cIndex.'" class="tooltip">'.$cVal.'</div>
				</th>
			';
		}
		$html .= '
			</tr>
		';

		# now print the rest of the lines
		foreach ( $rows as $rIndex => $rVal){
			$html .= '
				<tr class="v_'.($rIndex%2).'">
			';
			$html .= '
				<th class="headerList"  align="center">
				  <span>'.($rIndex+1)
				.'</span>
				</th>';
			foreach ( $cols as $cIndex => $cVal ){
				if (! $link){
					$html .= '
					<td class="value_c">
					<span title="'.$cVal.'">'.$rVal[$cIndex].''
					.'</span>
					'; 
				} else {
					$html .= '
					<td class="value_c">
					<span title="'.$cVal.'"><a target="_blank" class="cellValue" href="'.$link.$rVal[0].'">'.$rVal[$cIndex].'</a>'
					.'</span>
					'; 
				}
			}
			$html .= "</tr>\n";
		} 

		$html .=	'
			</table>
		';

		return $html;
	}
	

	
?>

