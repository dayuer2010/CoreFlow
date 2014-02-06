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

	if (trim($_REQUEST['database']) != '')	{$selectedDB=trim($_REQUEST['database']);}
	else 					{$selectedDB=getDefaultDB();}
	mysql_select_db($selectedDB,$dbh) or die("Could not select database $selectedDB");


	$tableName=$_REQUEST['table'];
	if (! $tableName){ print "ERROR: Table to be described is not defined!\n"; exit;	}

	# additional colum spec for sorting
	$columnName	=$_REQUEST['columnName'];
	$sortOrder	=$_REQUEST['sortOrder'];
	if (! $sortOrder )	{ $sortOrder = "asc"; }
	else 			{ $sortOrder = ($sortOrder == "asc")?"desc":"asc" ; }

	# additional flag for No table structure description
	$noStructure	=$_REQUEST['noStructure'];

	$tempTableName="";
	$createTempTable="";
	#determine if we have a view (we have a script instead of a table
	if (strpos($tableName,"select") === false) {
	}else{
		# get rid of back slashes
		$tableName =str_replace('\"','"',$tableName);
		$tempTableName =" TMP ";
		$createTempTable = "create temporary table TMP ($tableName) ";	
		$result = mysql_query($createTempTable);
		if ($result){
		} else {
			die("Could not create view table ($tableName) !");
		}
	}

	# get the number of rows first
	$query="select count(*) as Table_Size from ($tableName) $tempTableName ";
	$result = mysql_query($query);
	if ($result){
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$tableSize=$row{'Table_Size'};
	} else {
		die("Could not get the number of rows of the table ($tableName) ($tempTableName)!");
	}


	if (strpos($tableName,"select") === false) {
		$query="show full columns from $tableName";
	} else {
		$query="show full columns from TMP ";
	}

	$result = mysql_query($query);
			
	$colNames[0]="Field";
	$colNames[1]="Type";
	$colNames[2]="Collation";
	$colNames[3]="Null";
	$colNames[4]="Key";
	$colNames[5]="Default";
	$colNames[5]="Extra";
	$colNames[6]="Privileges";
	$colNames[7]="Comment";

	
	$tableColumnNamesList="";
	$blobFields=array();
	if ($result){
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$tableColumnNames[$i]=$row{'Field'};
      $tableColumnNamesList.=',`'.$row{'Field'}.'`
';
			if(preg_match("/.*blob.*/i",$row{'Type'})) {array_push($blobFields,$row{'Field'});}
			foreach ( $colNames as $cIndex => $cVal ){
				$tableDescr[$i][$cIndex]=$row{$cVal};
			}
			$i++;
		}
	} else {
		die("Could not show description of the table $tableName !");
	}


	#get also the create statement
	if($tempTableName ==''){
		$query="show create table $tableName";
		$result = mysql_query($query);
		if ($result){
			$row = mysql_fetch_array($result,MYSQL_ASSOC);
			$createTableStatement=$row{'Create Table'}."

/******* list of columns and a simple select 
select 
".$tableColumnNamesList."
from $tableName
*******/";
		} else {
			die("Could not get structure of $tableName !");
		}
  }


	# get a limited content of the table
	$queryLimit=700;
	if (strpos($tableName,"select") === false) {
		$query="select * from $tableName";
	} else {
		$query="select * from ($tableName) TMP";
	}
	if ($columnName){ $query .= " order by `" .$columnName. "` " .$sortOrder ;}
	$queryNoLimit=$query;	
	$query .= " limit $queryLimit";
	#print "$query <br>\n";
	
	$result = mysql_query($query);
	if ($result){
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			foreach ( $tableColumnNames as $cIndex => $cVal ){
				if( in_array($cVal,$blobFields) & strlen($row{$cVal})>500){
					$tableContent[$i][$cIndex]=substr($row{$cVal},1,100)." ... ";
				} else {
					$tableContent[$i][$cIndex]=$row{$cVal};
				}
			}
			$i++;
		}
	} else {
		die("Could not show content of the table $tableName <br> $query !");
	}


	mysql_close($dbh);

  if (strpos($tableName,"select") === false) {
	} else {
		$tableName="";
	}


	# show everything
	print '
	 <html>
		<head>
			<LINK REL="stylesheet" HREF="css/generic.css" type="text/css"/>
			<title>'.$tableName.'</title>
			<script>
				crtFileLocation="";
				var popup;
				function expandFile(fLocation){

						if (popup)popup.close();
						popup=window.open(fLocation
						 ,"Column Values"
						 ,"height=800,width=400, left=200, top=200, directories=0, location=0, menubar=0, status=0, titlebar=0, scrollbars=1");
				}	

				function forceSort_1(newCol){
					with (document.mainForm_1){
						//alert(newCol + "..." +columnName.value);
						if (columnName.value != newCol){
							columnName.value=newCol;
							sortOrder.value="";
						}
						//alert(tableName.value);
						//alert(columnName.value);
						submit();
					}
				}

				function forceSort_0(newCol){
				}

				function selectMainId(idVal,colName){
					with (document.mainIdForm){
						if (mainTableId){
							mainTableId.value=idVal;
							mainTableId.select();
							  //alert(opener.document.formInsert);
							  if (opener.document.formInsert){
							  	cmd="opener.document.formInsert."+colName+".value=\'"+idVal+"\';";
							  	eval (cmd);
							  }
							 
						}
					}	
				}

				function selectMainIdImage(idVal,colName,imgSrc){
					with (document.mainIdForm){
						if (mainTableId){
							mainTableId.value=idVal;
							mainTableId.select();
							  //alert(opener.document.formInsert);
							  if (opener.document.formInsert){
							  	cmd="opener.document.formInsert."+colName+".value=\'"+idVal+"\';";
							  	eval (cmd);
							  	if(opener.document.getElementById(\'crtIconSrc\')){
							  	  cmd="opener.document.getElementById(\'crtIconSrc\').src=\'"+imgSrc+"\';";
							  	  eval (cmd); 
							  	}
							  }
							 
						}
					}	
				}


				function selectAuthor(idVal,colName,imgSrc){
					with (document.mainIdForm){
						if (mainTableId){
							mainTableId.value=idVal;
							mainTableId.select();
							  //alert(opener.document.formInsert);
							  if (opener.document.formInsert){
							  	cmd="opener.document.formInsert."+colName+".value=\'"+idVal+"\';";
							  	eval (cmd);
							  	if(opener.document.getElementById(\'crtAuthorPictureSrc\')){
							  	  cmd="opener.document.getElementById(\'crtAuthorPictureSrc\').src=\'images/Icons/"+imgSrc+"\';";
							  	  eval (cmd); 
							  	}
							  }
							 
						}
					}	
				}


			</script>
		</head>
		';

    $dump_html="";
    $struct_html="";
		if ($noStructure != "Y"){
       $dump_html='&nbsp;&nbsp;
       <img style="cursor:pointer; vertical-align:bottom" src="images/text_file.png" width="33" title="Show the mysql create table statement" onclick="rollOn(\'tip_H_controlPanel\')">
       &nbsp;
       <a href="browseDB_dumpTable.pl?database='.$selectedDB.'&tableName='.$tableName.'" onclick="return confirm(\'Download a possibly large table content (in mysql format) to file. Are you sure?\')">
       <img src="images/juice_squeezer.png" style="vertical-align:bottom" width="33" title="Download a possible large file with table content in mysql `dump` format (squeeze table content to file). Table remains unchanged.!"></a>

       &nbsp;
       <a href="browseDB_loadTable.pl?database='.$selectedDB.'&tableName='.$tableName.'" onclick="return confirm(\'Replace table content with a local file  content (in mysql internal format). Are you sure?\')">
       <img src="images/orange_straw.png" style="vertical-align:bottom" width="33" title="Replace table content with a local file content in mysql `dump` format (load file to table). Table might change!"></a>

       <!--
       &nbsp;&nbsp;&nbsp;
       <a href="analysis_obtainTableUsage.php?database='.$selectedDB.'&tableName='.$tableName.'" target="_blank">
       <img src="images/table_usage.png" style="vertical-align:bottom" width="33" title="Obtain table usage: what CoreFlow tasks are referencing this table directly!"></a>
       -->
       
       &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;

       <!--
       <img style="vertical-align:bottom; cursor:pointer"  title="switch to parent window tab" width="23" src="images/back.png" onclick="backToParent(\'backFocus\',\''.$tableName.'\',\'browseDB.php\')"></a>
       <img style="vertical-align:bottom; cursor:pointer"  title="close"                       width="23" src="images/stop.png" onclick="backToParent(\'closeChild\',\''.$tableName.'\',\'browseDB.php\')"></a>
       -->
       
      <div style="display:inline; -webkit-border-radius: 10px; -khtml-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; padding:3px" name="tip_H_controlPanel" id="tip_H_controlPanel" class="tooltip">
				  <img style="cursor:pointer" src="images/close_object.png" width="20" border="0" alt="close Control Panel" title="close Control Panel" onclick="rollOut(\'tip_H_controlPanel\')">&nbsp; 
				  <pre>
'.$createTableStatement.'				  
				  </pre>
				</div>
				';
		}			

		print '
		<BODY topmargin="10" leftmargin="10" onload="focus();">
		    <script src="css/generic.js" language="javascript" type="text/javascript"></script>
                <DIV id="listing" align="left">
				<form name="mainIdForm" style="inline">
				<b style="font-family:Arial">'.$tableName.'</b>';
		if($tableSize>$queryLimit){		
		  print ' (<b>'.$tableSize.'</b> rows and max '.$queryLimit.' visible)';
	  }
		print '
				<input type="text" style="font-size:8; visibility:hidden" name="mainTableId" size="0" value="">
				 '.$dump_html.'
				</form>
		';
		print '<table border="0">'."\n";
		if ($noStructure != "Y"){
			print '<tr>'."\n";
			print '   <td><span>Table structure (click on the name of a Field to obtain distribution of the values):</span><br>'.buildTable(" ", $tableDescr,$colNames, 'javascript:expandFile(\'browseDB_describeTableColumn.php?database='.$selectedDB.'&amp;table='.$tableName.'&amp;column=','','','',0  ).'</td>'."\n";
			print	'</tr>';
		}			
			
		print ' <tr><td>'."\n";
		if($tempTableName ==''){
			print '<span>Table content:</span>';
		  print 		buildTable('# <img width="24px" style="cursor: pointer; border-left:2px inset #FF0000; border-bottom:2px inset #FF0000; border-right:2px outset #FFFFFF; border-top:2px outset #FFFFFF;" src="images/download.png" title="Download table in tab delimited format" onclick="openExcel()"> ', $tableContent,$tableColumnNames, '',$tableName,$columnName,$sortOrder,1 )."\n";
		} else {
		  print 		buildTable('#', $tableContent,$tableColumnNames, '',$tableName,$columnName,$sortOrder,1 )."\n";
		}
		print '</td></tr>'."\n";
		print '</table>'."\n";


	print '
			</DIV>
			<form name="goToExcel" action="dbQuery_downloadResult.php" method="post">
				<input type="hidden" name="csvContent" value="">
				<input type="hidden" name="delimiter" value="tab">
				<input type="hidden" name="database" value="'.$selectedDB.'">
				<input type="hidden" name="sqlDelimiter" value=";">
				<textarea name="csvContent_query" style="display:none;visibility:hidden">
				'.$query.'
				</textarea>
			</form>

			<form name="formBackToParent" action="#" target="CoreFlow" method="post" >
			  <input type="hidden" name="command" value="">
			  <input type="hidden" name="targetName" value="">
			</form>



			<script  type="text/javascript">
				function openExcel(){
					document.goToExcel.submit();
				}
				

				function backToParent(my_command,my_targetName,my_action){
				  with(document.formBackToParent){
				     command.value=my_command; // command backFocus or closeChild
				     action=my_action; // going back to this page
				     targetName.value=window.name; // after going back try to close this child target
				     submit();
				  }
				}


			</script>
			</BODY>
			</html>
	';


	############################################################################################################
	function buildTable($title, $rows,$cols,$link,$tableName,$columnName,$sortOrder,$tableIndex){
		#print the header
		$html ='
		<form name="mainForm_'.$tableIndex.'" method="post">
		<input type="hidden" name="table" value="'.$tableName.'">
		<input type="hidden" name="columnName" value="'.$columnName.'">
		<input type="hidden" name="sortOrder" value="'.$sortOrder.'">
		<table class="v" border="0" cellpadding="1" cellspacing="1" id="aTable" name="aTable">
			<tr class="v_header_sorter">
			<td class="corner">
			<img src="images/table_corner.gif" width="10" border="0" align="top">
			<span>'.$title.'</span>
			<img src="images/null.gif" width="10" border="0">
			</td>
		';
		foreach ( $cols as $cIndex => $cVal ){
		 if($tableIndex==0){ # there will be no sorting
			$html .= '
				<th class="headerList" nowrap align="center">
				<span>'
				.$cVal
				.'</span><img src="images/null.gif" width="10" border="0">
				</th>
			';
		 } else {
			$html .= '
				<th class="headerList" title="Click on column heading to sort" style="cursor: pointer;" onclick="forceSort_'.$tableIndex.'(\''.$cVal.'\')" nowrap align="center">
				<span>'
				.$cVal
				.'</span><img src="images/null.gif" width="10" border="0">
				</th>
			';
     }
		}
		$html .= '
			</tr>
		';

		# now print the rest of the lines
		foreach ( $rows as $rIndex => $rVal){
			if(preg_match("/Icon_Id/",$cols[0]) ){
			  $html .= '
				  <tr class="v_'.($rIndex%2).'" onclick="selectMainIdImage(\''.$rVal[0].'\',\''.$cols[0].'\',\''.$rVal[3].'\');markOne(this,\'v_marked\')">
			  ';
			}else{
				if(preg_match("/Analysis_Author/",$cols[0]) ){
				  $html .= '
					  <tr class="v_'.($rIndex%2).'" onclick="selectAuthor(\''.$rVal[0].'\',\''.$cols[0].'\',\''.$rVal[1].'\');markOne(this,\'v_marked\')">
				  ';
				 } else {
					$html .= '
						<tr class="v_'.($rIndex%2).'" onclick="selectMainId(\''.$rVal[0].'\',\''.$cols[0].'\');markOne(this,\'v_marked\')">
					';
				 }
			}
			$html .= '
				<th class="headerList" nowrap align="center">
				  <span>'.($rIndex+1)
				.'</span>
				</th>';
			foreach ( $cols as $cIndex => $cVal ){
				$toolTipEvents=' title="'.$cVal.'"';
				if (! $link){
					$html .= '
					<td class="value_c">
					<span '.$toolTipEvents.' >'.$rVal[$cIndex].''
					.'</span>
					'; 
				} else {
					$html .= '
					<td class="value_c">
					<span '.$toolTipEvents.'><a  class="cellValue" href="'.$link.$rVal[0].'\')">'.$rVal[$cIndex].'</a>'
					.'</span>
					'; 
				}
			}
			$html .= "</tr>\n";
		} 

		$html .=	'
			</table>
			</form>'."\n";
		return $html;
	}
	

	
?>

