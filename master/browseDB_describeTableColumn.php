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

	include_once("common_connect.php"); checkViewAccess();
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
	setDefaultDB($selectedDB ,$dbh);

	if (trim($_REQUEST['database']) != '')	{$selectedDB=trim($_REQUEST['database']);}
	else 					{$selectedDB=$dbDatabase;}
	mysql_select_db($selectedDB,$dbh) or die("Could not select database $selectedDB");

	$tableName=$_REQUEST['table'];
	if (! $tableName){ print "ERROR: Table to be described is not defined!\n"; exit;	}

	$columnName=$_REQUEST['column'];
	if (! $columnName){ print "ERROR: Column to be described is not defined!\n"; exit;	}


	$columnIndex	=$_REQUEST['__columnIndex'];
	$sortOrder	=$_REQUEST['__sortOrder'];
	$sortOrder = ($sortOrder == "asc")?"desc":"asc" ; 


	# get the number of rows first
	$query="select distinct `$columnName` as Column_Values, count(*) as Counts from $tableName group by `$columnName` ";
	if ($columnIndex) { $query .= " order by " .$columnIndex. " " .$sortOrder ; }

	$result = mysql_query($query);
			
	$colNames[0]="Column_Values";
	$colNames[1]="Counts";
	
	if ($result){
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$tableColumnNames[$i]=$row{'Field'};
			foreach ( $colNames as $cIndex => $cVal ){
				$tableDescr[$i][$cIndex]=$row{$cVal};
			}
			$i++;
		}
	} else {
		die("Could not show column values of the table $tableName for column $columnName !");
	}



	mysql_close($dbh);


	# show everything
	print '
		<head>
			<LINK REL="stylesheet" HREF="css/generic.css" type="text/css"/>
			<title>details of '.$tableName.' column: '.$columnName.' </title>
		</head>
		<BODY topmargin="0" leftmargin="2" onload="focus();">
		<DIV id="listing" align="left">
				<FONT size="-1" FACE="miriam">
				'.$tableName.'<br/><b> '.$columnName.'</b> distinct values 
				</FONT>
		';
		print '<table border="0">
		        <tr>'."\n";
		print '   <td>'.buildTable(" ", $tableDescr,$colNames, '',$columnIndex,$sortOrder ).'</td>'."\n";
		print '   <td>
			  </td>
			</tr>
		       </table>'."\n";


	print '
			</DIV>
			<script>
			function forceSort(newCol){
				with (document.mainForm){
					//alert(newCol + "..." +__columnIndex.value);
					if (__columnIndex.value != newCol){
						__columnIndex.value=newCol;
						__sortOrder.value="";
					}
					

					//alert(__columnIndex.value);
					submit();
				}
			}
			
			</script>
			</BODY>
	';


	############################################################################################################
	function buildTable($title, $rows,$cols,$link,$sortColumnIndex,$sortColumnOrder){
		#print the header		
		$html ='
		<form name="mainForm" method="post">
		<input type="hidden" name="__columnIndex" value="'.$sortColumnIndex.'">
		<input type="hidden" name="__sortOrder" value="'.$sortColumnOrder.'">
		<table class="v" border="0" cellpadding="1" cellspacing="1" id="aTable" name="aTable">
			<tr class="v_header_sorter">
			<td class="corner">
			<img src="images/table_corner.gif" width="10" border="0" align="top">
			<span>'.$title.'</span>
			<img src="images/null.gif" width="10" border="0">
			</td>
		';
		foreach ( $cols as $cIndex => $cVal ){
			$html .= '
				<th class="headerList" style="cursor: pointer;" onclick="forceSort('.($cIndex+1).')" nowrap align="center">
				<span id="header_content_1" name="header_content_1" >'
				.$cVal
				.'</span><img src="images/null.gif" width="10" border="0">
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
				<th class="headerList" nowrap align="center">
				  <span>'.($rIndex+1)
				.'</span>
				</th>';
			foreach ( $cols as $cIndex => $cVal ){
				if (! $link){
					$html .= '
					<td class="value_c">
					<span>'.$rVal[$cIndex].''
					.'</span>
					'; 
				} else {
					$html .= '
					<td class="value_c">
					<span><a target="_blank" class="cellValue" href="'.$link.$rVal[0].'">'.$rVal[$cIndex].'</a>'
					.'</span>
					'; 
				}
			}
			$html .= "</tr>\n";
		} 

		$html .=	'
			</table>
			</form>
		';

		return $html;
	}
	

	
?>

