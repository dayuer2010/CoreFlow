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

	#  2008 Jan 14 - Projects (look-up table) extracted from OPENFREEZER PRODUCTION (vers 1.4) database 

	include_once('common_connect.php'); checkViewAccess();
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
  mysql_select_db($selectedDB,$dbh) or die("Could not select database $selectedDB");


	$tableName=trim($_REQUEST['tableName']);
	if ($tableName ==""){ $tableName=$selectedDB.'.'.'TEST_TABLE';	}


		$editableTablesHTML;
		$result = mysql_query(' select distinct concat(`Database`,".",Table_Name) as `Table` from MB_USER_EDITABLE_TABLE order by 1');
		if ($result){
			$i=0;
			while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
				if((trim($row{'Table'})==$selectedDB.".MB_ICON") || (trim($row{'Table'})==$selectedDB.".MB_CUSTOM_ANALYSIS") || (trim($row{'Table'})==$selectedDB.".MB_CUSTOM_ANALYSIS_RESULT") || (trim($row{'Table'})==$selectedDB.".MB_ANALYSIS_AUTHOR")) {next;}
				if($tableName == $row{'Table'}) {$selectedFlag = ' selected '; } else {$selectedFlag='';}
				$editableTablesHTML .='<OPTION value="'.$row{'Table'}.'"'.$selectedFlag.'>'.$row{'Table'}.'</OPTION>'."\n";
				$i++;
			}
			if($i==0){
				# the user editable table is empty! try to restore it: it must have at least the MB_USER_EDITABLE_TABLE
				$editableTablesHTML .='<OPTION value="'.$selectedDB.'.MB_USER_EDITABLE_TABLE" selected>'.$selectedDB.'.MB_USER_EDITABLE_TABLE</OPTION>'."\n";
				$resultInsert=mysql_query(' insert into '.$selectedDB.'.MB_USER_EDITABLE_TABLE values("'.$selectedDB.'","MB_USER_EDITABLE_TABLE","contains all tables that can be edited with Copy/Paste from Excel",now())');				
			}
		} else {
			die("Could not determine USER Editable tables!"."<br><pre>".mysql_error()."</pre>");
		}




	### verify if table is in MB_USER_EDITABLE_TABLE
	$query="select concat(`Database`,'.',Table_Name) as Database_Table from MB_USER_EDITABLE_TABLE where concat(`Database`,'.',Table_Name)='$tableName'";
	$result = mysql_query($query);
	$isEditable="NO";
	if ($result){
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$editableTableName=$row{'Database_Table'};
		if ($editableTableName == $tableName){$isEditable="Yes";}
	} else {
		die("Could not get the editable table with name $tableName \n<pre> $query \n".mysql_error()." </pre> !");
	}


	$sqlClause=stripslashes($_REQUEST['sqlClause']);

	$columnName	=trim($_REQUEST['columnName']);
	$sortOrder	=trim($_REQUEST['sortOrder']);
	if (! $columnName){ $columnName =1;}
	if (! $sortOrder )	{ $sortOrder = "desc"; }
	else 			{ $sortOrder = ($sortOrder == "asc")?"desc":"asc" ; }

	# get the number of rows first
	$query="select count(*) as Table_Size from $tableName";
	$result = mysql_query($query);
	if ($result){
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$tableSize=$row{'Table_Size'};
	} else {
		die("Could not get the number of rows of the table $tableName \n<pre> $query \n".mysql_error()." </pre> !");
	}

	$query="show full columns from $tableName";

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
	
	if ($result){ 
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$tableColumnNames[$i]  =$row{'Field'};
			$tableColumnExtra[$i]  =$row{'Extra'};
			$tableColumnKey[$i]    =$row{'Key'};
			$tableColumnType[$i]   =$row{'Type'};
			$tableColumnComment[$i]=$row{'Comment'};

			$tableColumnNamesSelect[$i]=$row{'Field'};
			if ( preg_match("/.*blob.*/i",$row{'Type'}) & (strlen($tableColumnNamesSelect[$i])>2000)){
				$tableColumnNamesSelect[$i]="(select concat(substr(`$tableColumnNamesSelect[$i]`,1,300),' ... binary data! ...')) as `".$tableColumnNamesSelect[$i]."`";
			} else {
				$tableColumnNamesSelect[$i] = "`".$tableColumnNamesSelect[$i]."`";
			}

			$i++;
		}
	} else {
		die("Could not show description of the table $tableName !");
	}



	# get a limited content of the table
	$queryLimit=1000;
	#$query="select * from $tableName ";
	$query="select ".join(",",$tableColumnNamesSelect)." from $tableName where (1=1) $sqlClause";


	
	if ($columnName == 1) { $query .= " order by " .$columnName. " " .$sortOrder ;}
	else {
		if ($columnName != ""){ $query .= " order by `" .$columnName. "` " .$sortOrder ;}	
	}	
	$query .= " limit $queryLimit";

	$result = mysql_query($query);
	if ($result){
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			foreach ( $tableColumnNames as $cIndex => $cVal ){
				$tableContent[$i][$cIndex]=$row{$cVal};
				$item=$row{$cVal};
			}
			$i++;
		}
	} else {
		die("Could not show content of the table $tableName <br> $query !");
	}
	if ($i >= ($queryLimit-1)){ $warning="<br>Showing only $queryLimit records!";}
	mysql_close($dbh);


	# show everything
   $delimTableContent=buildDelimTable(' # ', $tableContent,$tableColumnNames,'	');    

?>
	
<?php $menuTitle='excel>DB ' ;include "app_header.php"; ?>
<script type="text/javascript">
	highLiteMenu('editableTableMenu');
</script>	  

<table  align="center"><tr><td style="text-align:left">
    <h4 style="color:#E46C0A">Simple copy/paste between excel and a database table</h4>

 		<form name="f_userEditableTable">
	 Select one of the tables from the list below:<br>
		<select class="smallFont" name="tableName" onchange="submit()" >
			<?php print $editableTablesHTML ?>
		</select>
			
	</form>
 
		    <script src="css/generic.js" language="javascript" type="text/javascript"></script>
		    <form name="formRunDscript" action="/Admin/runCustomSQL.php" style="display:inline" method="post" target="dbScriptResult">
		    	<input type="hidden" name="Custom_Analysis_Id" value="">
		    	<input type="hidden" name="table_Xsl_Style" value="">
		    </form>
		 
		 <br>
     <form name="bulkReplaceAndInsert" method="post" action="excelToDB_loadTsv.pl" target="_blank" style="display:inline"> 
     <table width="100%"><tr>
      <td>Content to be stored into the destination (paste directly from Excel):
      </td>
      <td align="right">
       <span title="Click and then [Ctrl/Mac C] to get a `tab` in your clipboard">
        <img alt=""  src="images/tab.png" width="20" style="vertical-align:middle" onclick="document.bulkReplaceAndInsert.tab_character.value='	'; document.bulkReplaceAndInsert.tab_character.select()">
        <input  name="tab_character" size="5" style="border:0" value="	" onclick="this.value='	'; this.select()">
       </span>
      </td> 
     </tr></table>
     <textarea name="tableContent" rows="7" cols="140"  style="font-size:70%"><?php print $delimTableContent ?></textarea><br>

     <input type="hidden" name="truncateTable" value="No"> <input type="hidden" name="loadTableName" value="<?php print $tableName ?>"> 
     <select name="firstRowId">
       <option value="1" selected >Exclude first row during import (treat as header)</option>
       <option value="0">Include first row during import (treat as data)</option> <!-- start counting from 0 -->
     </select>
     &nbsp;&nbsp;&nbsp;
     <!-- input type="button" style="color:red" name="__BulkAppend" value="Append to current content" onclick="if(confirmReplaceFromExcel(0)){submit()}"-->
     <input type="button" style="color:red" name="__BulkReplaceAndInsert" value="Replace table content with above data" onclick="if(confirmReplaceFromExcel(1)){submit()}"> 

     <input type="button" style="color:brown" name="__createStatement" value="show a `create table` from Header" onclick="createTableFromHeader(document.bulkReplaceAndInsert.tableContent.value); rollOn('tip_sql');"> 

		 <br>
		 <br>
		 </form>	
		 
		<div style=" display:inline; -webkit-border-radius: 10px; -khtml-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; padding:3px" id="tip_sql" class="tooltip">
		  <form name="sql_form" method="POST" action="dbQuery.php" target="_blank">
		   <table style="border:0px;">
		  	<tr  id="dragMe" title="drag me" style="background-color:#DDDDDD; cursor:grabbing; cursor:-moz-grabbing; cursor:-webkit-grabbing;">
		  		<td><img alt=""  style="cursor:pointer" src="images/close_object.png" width="15" border="0" title="close this pop-up" onclick="rollOut('tip_sql')"></td>
		  	<tr>
		  		<td>
		  			<hr>
				  	new table:
				  	<input name='newTable' style="background-color:#FEC1C1; font-weight:bold" size="20" value='MY_TABLE'>
				  	<button  onclick="createTableFromHeader(document.bulkReplaceAndInsert.tableContent.value); return false">Set table name</button>
				  	<br>
				    <textarea name="parentContent" rows="15" cols="40"></textarea>
				    <br><button style="color:red; font-weight:bold">Prepare to run new table creation</button>		  			
		  	  </td>
		  	</tr>		 
		   </table>	
		  </form>
	  </div>
		 
		 	  
		
		<?php 
		print 	$warning."Currently selected destination table content ($tableName):"."\n";
		print 	buildTable('#  ', $tableContent,$tableColumnNames, $tableName,$columnName,$sortOrder,$sqlClause )."\n";
    ?>

<script type='text/javascript' src='http://code.jquery.com/jquery-1.7.1.js'></script>
   <script type="text/javascript">
   
			function confirmReplaceFromExcel(flagTruncate){
				if(flagTruncate){
					document.bulkReplaceAndInsert.truncateTable.value="Yes";
					return confirm("Replace ALL records with the ones you paste from Excel ?");
				} 
				else {	document.bulkReplaceAndInsert.truncateTable.value="No";
					return confirm("Append records you paste from Excel ?");
				}
			}   
   
			function createTableFromHeader(myString){
			    my_content=myString.split("\n");
			    my_header =my_content[0];
			    my_values =my_content[1].split("\t");			    
			    my_columns=my_header.split("\t");

			    my_columns_sql=my_columns.join("` varchar(255)\n  ,`");
			    my_columns_sql="`"+my_columns_sql+"` varchar(255)\n";
			    my_columns_sql='';
			    for (i = 0; i < my_columns.length; ++i) {
			      if(isNaN(my_values[i])){
			      	my_length=Math.round(my_values[i].length * 3); 
			      	if(my_length<=255) { my_type='varchar('+my_length+')'} else { my_type='mediumtext'}; 
			      }else my_type='float';
			    	my_columns_sql += "`"+my_columns[i]+"` "+ my_type+" \n";
			    	if(i<(my_columns.length-1)) my_columns_sql +=' ,'; 
			    }
			  	with(document.sql_form){
			  		my_table=newTable.value;
			  	  parentContent.value='   \n\
set @my_table="'+my_table+'";\n\
use <?php print $selectedDB ?>;\n\
drop table if exists `'+my_table+'`;\n\
create table `'+my_table+'`(\n\
  '+my_columns_sql+'\n\
);\n\n\
delete from MB_USER_EDITABLE_TABLE \n\
where `Database`="<?php print $selectedDB; ?>" \n\
      and Table_Name=@my_table;\n\n\
insert into MB_USER_EDITABLE_TABLE \n\
values("<?php print $selectedDB; ?>",@my_table,"new playground table",now())\n';
			  	}
			  	
			  	//document.sql_form.parentContent.select();
			}

	
	$(document).ready(function() {
    var startAt = null;

    $(document.body).live("mousemove", function(e) {
        if (startAt) {
            $("#tip_sql").offset({
                top: e.pageY,
                left: $("#tip_sql").position().left-startAt+e.pageX
            });
            startAt = e.pageX; 
        }
    });

    $("#dragMe").live("mousedown", function (e) {startAt = e.pageX;});
    $(document.body).live("mouseup", function (e) {startAt = null;});
});

		</script>
</td></tr></table>

<?php include "app_footer.php"; ?>


<?php

	############################################################################################################
	function buildTable($title, $rows,$cols,$tableName,$columnName,$sortOrder,$sqlClause){
		#print the header		
		$html ='
		<form name="editForm" method="post" action="editTable.php">
		<input type="hidden" name="tableName" value="'.$tableName.'">
		<input type="hidden" name="columnName" value="'.$columnName.'">
		<input type="hidden" name="sqlClause" value="'.$sqlClause.'">
		<input type="hidden" name="sortOrder" value="'.$sortOrder.'">
		  <table class="v" border="0" cellpadding="1" cellspacing="1" id="aTable" >
			<tr class="v_header_sorter">
			<td class="corner">
			<img alt=""  src="images/table_corner.gif" width="10" border="0" align="top">
			<span>'.$title.'</span>
			<img alt=""  src="images/null.gif" width="10" border="0">
			</td>
		';
		foreach ( $cols as $cIndex => $cVal ){
			$html .= '
				<th class="headerList" nowrap align="center"  >
				<span>'
				.$cVal
				.'</span>
				</th>
			';
		}
		$html .= '
			</tr>
		';


		# now print the rest of the lines
		foreach ( $rows as $rIndex => $rVal){
			$htmlCells = '
				<th class="headerList" nowrap align="center">
				  <span>'.($rIndex+1)
				.'</span> <!--img src="delete_icon.jpg" width="12" style="cursor: pointer;"-->
				</th>';
			$setInsertValues="";
			$nmbCols=0;	
			foreach ( $cols as $cIndex => $cVal ){
				$toolTipEvents=' title="'.$cVal.'" ';
				$item=$rVal[$cIndex];
					$htmlCells .= '
					<td class="value_c">
					<span '.$toolTipEvents.'>'.$item.''
					.'</span>
					'; 
				$htmlCells .= '
					<textarea style="visibility:hidden; display:none" id="oldValue_col_'.$cIndex.'_row_'.$rIndex.'" rows="1" cols="80">'.$rVal[$cIndex].'</textarea>
					';
				if ($setInsertValues){ $setInsertValues .=",";}
				$setInsertValues .= "'".addslashes($rVal[$cIndex])."'";
				$nmbCols++;
			}
			$html .= '<tr class="v_'.($rIndex%2).'" onclick="markOne(this,\'v_marked\');">'.$htmlCells."</tr>\n";
		} 

		$html .=	'
			  </table>
			</form>'."\n";

		return $html;
	}
	
	function buildDelimTable($title, $rows,$cols,$delim){
		#print the header		
		$csv ='';
    $line = '';
		foreach ( $cols as $cIndex => $cVal ){
			  $v=$cVal;
				$v=str_replace('"',"`",$v);
				$v=str_replace("\n"," ",$v);  
				$v=str_replace("\r"," ",$v);  
				if($line !=""){$line .= $delim;}
				$line .=$v; 
		}
			$csv .= $line.'
';
		# now print the rest of the lines
		foreach ( $rows as $rIndex => $rVal){
			$line = '';
			foreach ( $cols as $cIndex => $cVal ){
				# replace " with `
				$v=$rVal[$cIndex];
				$v=str_replace('"',"`",$v);
				$v=str_replace("\n"," ",$v);  
				$v=str_replace("\r"," ",$v);  
				if($line !=""){$line .= $delim;}
				$line .=$v; 
			}
			$csv .= $line.'
';
		} 

		return $csv;
	}

	
?>


