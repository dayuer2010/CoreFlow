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


	# get Work tables
	$workDatabase=$selectedDB;
	$resultWork = mysql_query("SHOW TABLE STATUS from $workDatabase");
	$htmlWorkTables;
	$htmlWorkTablesFasta;
	if ($resultWork){
		while ($row = mysql_fetch_array($resultWork,MYSQL_ASSOC)) {
			  if (substr($row{'Name'},0,3)=="MB_"){continue;}

			  if ($row{'Name'}=='TEST_TABLE') {$selectedFlag=" selected ";} else {$selectedFlag="";}
				$htmlWorkTables .='<option value="'.$workDatabase.".".$row{'Name'}.'" '.$selectedFlag.'>'.$row{'Name'}.'</option>';

			  if (preg_match("/.*test.*fasta.*/i",$row{'Name'})) {$selectedFlag=" selected ";} else {$selectedFlag="";}
				$htmlWorkTablesFasta .='<option value="'.$workDatabase.".".$row{'Name'}.'" '.$selectedFlag.'>'.$row{'Name'}.'</option>';

		}
	} else {
		$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
	}
	$htmlWorkTables      ='<select name="loadTableName" style="font-size:75%">'.$htmlWorkTables.'</select>';
	$htmlWorkTablesFasta ='<select name="loadTableName" style="font-size:75%">'.$htmlWorkTablesFasta.'</select>';
	mysql_close($dbh);

?>

<?php $menuTitle='file>DB '; include "app_header.php"; ?>
<script type="text/javascript">
	highLiteMenu('loadDatabaseMenu');
</script>	    	

<br><br>
<div align="center">
<table class="loadFile" style="align:center; border:1px solid grey; font-size:80%; color: grey">

  <form name="loadTableTsv" method="post" action="fileToDB_tsv.pl" target="_blank" style="display:inline" enctype="multipart/form-data">
	<tr>
     <td rowspan="4">Load a <b>tab separated values</b>
    	<br> file to a table<br>
    	<input type="button" onclick="if (document.loadTableTsv.tsvFileName.value !='' || document.loadTableTsv.tsvFilePath.value !='') {if (confirm('Load table with file and possibly loose previous content?')){submit();}}" 
				        value="Upload to destination table" title="Appending to or Replacing to selected table ">
    </td>
		<td style="text-align:right; ">select destination table:</td>
		<td><?php print $htmlWorkTables ?> </td>
	</tr>

	<tr>
		<td style="text-align:right;">select source tab separated values (tsv) file:
		  <br>or absolute file path on CoreFlow server:
		</td>
		<td>
			<input type="file" style="font-size:70%;background-color:#CCCCCC;" name="tsvFileName" size="20">
			<a title="click to download an example"  href="dbQuery_downloadResult.php?delimiter=tab&amp;csvContent_query=select%20*%20from%20TEST_TABLE%20limit%20700">
				<img alt="" height="20" src="images/tab_delimited.png" style="vertical-align:bottom"> 
			</a>
			<br><input style="font-size:70%;background-color:#CCCCCC;" name="tsvFilePath" size="40" title="absolute file path if file on CoreFlow web server">	
		</td>
	</tr>

	<tr>
		<td style="text-align:right; ">Treat first row as header?</td>
		<td><select style="font-size:70%;background-color:#CCCCCC;" name="firstRowId">
				<option value="1" selected>ignore first row (Header)</option><option value="0">first row is data (NO header)</option>
				</select>
		</td>
	</tr>

	<tr>
		<td style="text-align:right; "></td>
		<td><select style="font-size:70%;background-color:#CCCCCC;" name="previousContentManagement">
				<option value="insert" selected>Append to current content</option><option value="truncate first">Replace current content</option>
				</select>
		</td>
	</tr>

	<!--tr>
		<td style="text-align:right">If header then do we filter only these column names([cr/lf] separated)?:</td>
		<td><textarea name="columnList" rows="2" cols="30" style="font-size:80%;background-color:#CCCCCC;"></textarea></td>
	</tr-->
 </form>		




 <tr><td colspan="3"><br><hr style="background-color:#EEEEEE; height:5px" /><br></td></tr>




  <form name="loadTableFASTA" method="post" action="fileToDB_fasta.pl" target="_blank" style="display:inline" enctype="multipart/form-data">
	<tr>
  <td rowspan="3">Load a <b>FASTA</b>
    	<br> file to a table having <span style="color:red">two columns only</span> <br>
    	<input type="button" onclick="if (document.loadTableFASTA.fileName.value !='') {if (confirm('Load table with file?')){submit();}}" 
				        value="Upload to destination table" title="Appending to or Replacing selected table ">
    </td>
		<td style="text-align:right;">select destination table:</td>
		<td><?php print $htmlWorkTablesFasta ?> </td>
	</tr>
	<tr>
		<td style="text-align:right;">select source FASTA file</td>
		<td><input type="file" style="font-size:70%;background-color:#CCCCCC;" name="fileName" size="20">
			<a title="click to download an example to be loaded in the TEST_TABLE_FASTA_HUMAN_SH2 table" href="Hsap_SH2_ensembl_mart_export.fasta">
			  <img alt="" src="images/fasta.gif"/>
		  </a>
	  </td>
	</tr>
	<tr>
		<td style="text-align:right; "></td>
		<td><select style="font-size:70%;background-color:#CCCCCC;" name="previousContentManagement">
				<option value="insert" selected>Append to current content</option><option value="truncate first">Replace current content</option>
				</select>
		</td>
	</tr>
 </form>		


 <tr><td colspan="3"><br><hr style="background-color:#EEEEEE; height:5px" /><br></td></tr>

<!--
  <form name="loadTableTsvVert" method="post" action="fileToDB_tsvOneColumn.pl" target="_blank" style="display:inline" enctype="multipart/form-data">
	<tr>
     <td rowspan="5">Load a <b>tab separated values</b>
    	<br> file to a <b>slim</b> table by Converting 
    	<br> column 2..n names to column Ids as key
    	<br> concatenating 2..n to one column 
    	<br> and copying coulmn 1 as first key 
    	<br>
    	<input type="button" onclick="if (document.loadTableTsvVert.tsvFileName.value !='' || document.loadTableTsvVert.tsvFilePath.value !='') {if (confirm('Load table with file and possibly loose previous content?')){submit();}}" 
				        value="Upload to destination table" title="Appending to or Replacing to selected table ">
    </td>
		<td style="text-align:right; ">select destination table:</td>
		<td><?php print $htmlWorkTables ?> </td>
	</tr>
	<tr>
		<td style="text-align:right;">select source tab separated values (tsv) file:
		  <br>or absolute file path on CoreFlow server:
		</td>
		<td>
			<input type="file" style="font-size:70%;background-color:#CCCCCC;" name="tsvFileName" size="20">
			<-
			<a title="click to download an example"  href="dbQuery_downloadResult.php?delimiter=tab&amp;csvContent_query=select%20*%20from%20TEST_TABLE%20limit%20700">
				<img alt="" height="20" src="images/tab_delimited.png" style="vertical-align:bottom"> 
			</a>
			->
			<br><input style="font-size:70%;background-color:#CCCCCC;" name="tsvFilePath" size="40" title="absolute file path if file on CoreFlow web server">	
		</td>
	</tr>
	<tr>
		<td style="text-align:right; ">Treat first row as header?</td>
		<td><select style="font-size:70%;background-color:#CCCCCC;" name="firstRowId">
				<option value="1" selected>ignore first row (Header)</option><option value="0">first row is data (NO header)</option>
				</select>
		</td>
	</tr>
	<tr>
		<td style="text-align:right; "></td>
		<td><select style="font-size:70%;background-color:#CCCCCC;" name="previousContentManagement">
				<option value="insert" selected>Append to current content</option><option value="truncate first">Replace current content</option>
				</select>
		</td>
	</tr>
	<tr>
		<td style="text-align:right; "></td>
		<td><select style="font-size:70%;background-color:#CCCCCC;" name="valuesToSkip">
				<option value="zero_or_empty" selected>Skip Zero or Empty</option>
				<option value="empty_only">Skip empty only</option>
				<option value="none">Skip none</option>
				</select>
		</td>
	</tr>	
-->

	<!--tr>
		<td style="text-align:right">If header then do we filter only these column names([cr/lf] separated)?:</td>
		<td><textarea name="columnList" rows="2" cols="30" style="font-size:80%;background-color:#CCCCCC;"></textarea></td>
	</tr-->
 </form>		



</table>
</div>

<?php include "app_footer.php"; ?>
