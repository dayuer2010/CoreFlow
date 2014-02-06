<?php include_once("common_connect.php"); checkViewAccess();
	$dbh = connectToDB();
        if(trim($_REQUEST['database'])=='') {
	  $selectedDB=getDefaultDB();
        } else {
          $selectedDB=trim($_REQUEST['database']);
        }
	setDefaultDB($selectedDB ,$dbh);

	#get all databases
	$allDatabasesHtml="";
	$query="show databases";
	$result = mysql_query($query);
	if ($result){
		while ($row = mysql_fetch_row($result)) {
			$crtDatabase=$row[0];
			if (($crtDatabase != 'information_schema') && ($crtDatabase != 'test')){
				$selectedHtml="";
				if ( $crtDatabase == $selectedDB ){ $selectedHtml= "selected"; };
				$allDatabasesHtml .= '<option value="'.$crtDatabase.'" '.$selectedHtml.' >'.$crtDatabase.'</option>'."\n";
			}
		}
	}

        if (trim($_REQUEST['database_2']) != '')        {$selectedDB_2=trim($_REQUEST['database_2']);}
        else                                    {$selectedDB_2=$selectedDB;}
        mysql_select_db($selectedDB_2,$dbh) or die("Could not select database $selectedDB_2");



	$allDatabasesHtml = '<form style="display:inline" action=""><input type="hidden" name="database_2" value="'.$selectedDB_2.'"><select name="database" onchange="submit()">'.$allDatabasesHtml.'</select></form>'."\n"; 


	#get all databases
	$allDatabasesHtml_2="";
	$query="show databases";
	$result = mysql_query($query);
	$nmbDatabases=0;
	if ($result){
		while ($row = mysql_fetch_row($result)) {
			$crtDatabase=$row[0];
			if (($crtDatabase != 'information_schema') && ($crtDatabase != 'test')){
				$selectedHtml="";
				if ( $crtDatabase == $selectedDB_2 ){ $selectedHtml= "selected"; };
				$allDatabasesHtml_2 .= '<option value="'.$crtDatabase.'" '.$selectedHtml.' >'.$crtDatabase.'</option>'."\n";
				$nmbDatabases=$nmbDatabases+1;
			}
		}
	}

	$allDatabasesHtml_2 = '<form style="display:inline" action=""><input type="hidden" name="database" value="'.$selectedDB.'"><select name="database_2" onchange="submit()">'.$allDatabasesHtml_2.'</select></form>'."\n"; 

	$query="SHOW TABLE STATUS from $selectedDB";
	$result = mysql_query($query);			
	$colNames[0]="Table Name";
	$colNames[1]="Rows";
	if ($result){
		$i=0;
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$i++;
			$tables[$i][0]=$row{'Name'};
			$tables[$i][1]=$row{'Rows'};
		}
	} else {
		die("Could not show all tables!");
	}

	$query="SHOW TABLE STATUS from $selectedDB_2";
	$result = mysql_query($query);			
	$colNames_2[0]="Table Name";
	$colNames_2[1]="Rows";
	if ($result){
		$i=0;
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$i++;
			$tables_2[$i][0]=$row{'Name'};
			$tables_2[$i][1]=$row{'Rows'};
		}
	} else {
		die("Could not show all tables!");
	}

	mysql_close($dbh);

?><?php $menuTitle='browseDB '; include "app_header.php"; ?>
<?php
		print '
<script  type="text/javascript">
	highLiteMenu("viewTablesMenu");
</script>	    	
		
<br><br><br>
<table border="0" align="center">
 <tr>
  <td valign="top">
    <b>Database:</b>'.$allDatabasesHtml.'<br><b>Tables:</b>'.buildTable(" ", $tables,$colNames, 'browseDB_describeTable.php?database='.$selectedDB.'&amp;table=' ).'
  </td>';
  if($nmbDatabases>1){print'	
  <td valign="top">
   <b>Database:</b>'.$allDatabasesHtml_2.'<br><b>Tables:</b>'.buildTable(" ", $tables_2,$colNames_2, 'browseDB_describeTable.php?database='.$selectedDB_2.'&amp;table=' ).'
  </td>';}
 print '
 </tr>
 <tr>
  <td>
   <a href="browseDB_dumpTable.pl?database='.$selectedDB.'&amp;tableName=*" onclick="return confirm(\'Mysql dump all tables. Are you sure?\')">
       <img  alt="" src="images/dump_truck.png" width="33" title="Download a mysql dump file of all tables!"></a>
  </td>';
  if($nmbDatabases>1){print '
  <td>
   <a href="browseDB_dumpTable.pl?database='.$selectedDB_2.'&amp;tableName=*" onclick="return confirm(\'Mysql dump all tables. Are you sure?\')">
       <img  alt="" src="images/dump_truck.png" width="33" title="Download a mysql dump file of all tables!"></a>

  </td>';}
  print '
  </tr>
 </table>'."\n";
?>
<?php include "app_footer.php"; ?>


<?php
	############################################################################################################
	function buildTable($title, $rows,$cols,$link){
		#print the header		
		$html ='
		<table class="v" border="0" cellpadding="1" cellspacing="1" id="aTable">
			<tr class="v_header">
			<td class="corner">
			<img  alt="" src="images/table_corner.gif" width="10" border="0" align="top">
			<span>'.$title.'</span>
			<img  alt="" src="images/null.gif" width="10" border="0">
			</td>
		';
		foreach ( $cols as $cIndex => $cVal ){
			$html .= '
				<th class="headerList" nowrap align="center">
				<span>'
				.$cVal
				.'</span><img  alt=""  src="images/null.gif" width="10" border="0">
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
				.'</span><img alt="" src="images/null.gif" width="10" border="0">
				</th>';
			foreach ( $cols as $cIndex => $cVal ){
				if ($cIndex==0)	{$tdClass="value_c";}
				else 		{$tdClass="value_n";}
				$html .= '
				<td class="'.$tdClass.'">
				  <span>
                                   <a target="'.$rVal[0].'" class="cellValue" href="'.$link.$rVal[0].'">'.$rVal[$cIndex].'</a>'
				.'</span>
				</td>'; 

			}
			$html .= "</tr>\n";
		} 

		$html .=	'
			</table>
		';

		return $html;
	}
	
?>

