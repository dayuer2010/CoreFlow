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
  
	#  2008 Jul 29 - editing a content 
  #  2012 Sep 22 - adding 'parentPrimaryKey'
  

$parentContent	=trim(stripslashes($_REQUEST['parentContent']));


$parentName="Db_script";
$saveButtons='';
if($parentContent == "") {$parentContent='
-- this is just an example of sql query	
create temporary table TMP select "alpha" as Struct,1 as Id union select "beta",2 union select "gamma",3;
select * from TMP
limit 100
    ';
}

$editAreaHeaders='      <script language="javascript" type="text/javascript" src="edit_area/edit_area_full.js"></script>';
$runHTML='';
$editHeaders='';

$bodyStyle="background-color:#E9EFDC";
$runHTML='&nbsp;&nbsp;<a href="http://dev.mysql.com/doc/refman/5.0/en/select.html" target="_blank"><img alt="" src="images/manual.png" title="online mySQL doc" width="20"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sqlDelim:
	<input style="font-size:80%; font-weight:bold; color:blue" name="sqlDelimiter" size="1" value=";"> ;=char(59) &nbsp;
	<input type="button" style="background-color:#888888;color:#FFFFFF" value="Run" onclick="prepareRunSql()">
	&nbsp;&nbsp;<input type="button" style="background-color:#888888;color:#FFFFFF" value="Download result" onclick="openExcel()">';
$myTitle="s$parentPrimaryKey";
$editArea_syntax="sql"; # the default syntax for coloring
$notes='';
$editingAreaHtml='<textarea name="curentContent" id="curentContent" cols="120" style="height: 500px; width: 100%;" >'.$parentContent.'</textarea>';
$editHeaders=$editAreaHeaders;

$menuTitle='queryDB ' ;
	# must get rid of escaped quotes
?>
<?php $menuTitle='queryDB '; include "app_header.php"; ?>
<?php print '
'.$editAreaHeaders.'	
<script type="text/javascript">
	highLiteMenu("sqlEditorMenu");
</script>	    

<div style="'.$bodyStyle.'" height="90%">
		
			<form name="formEditContent" action="">
				<div style="inline; text-align:center;">
					'.$notes.$saveButtons.$runHTML.'
				</div>
				'.$editingAreaHtml.'
			</form>

			<script type="text/javascript">

				function prepareRunSql(){
					with(document.runSqlForm){
						sqlDelimiter.value=document.formEditContent.sqlDelimiter.value;
						//query.value       =document.formEditContent.curentContent.value;
	          my_editArea_content=editAreaLoader.getValue("curentContent");
						query.value       =my_editArea_content;
						submit();
					}
				}

				// alert("Ready!");
			</script>
			
				<form name="goToExcel" action="dbQuery_downloadResult.php" method="post">
				  <input type="hidden" name="csvContent" value="">
				  <input type="hidden" name="delimiter" value="tab">
				  <input type="hidden" name="database" value="">
				  <input type="hidden" name="sqlDelimiter" value="">
				  <textarea rows="1" cols="1" name="csvContent_query" style="display:none;visibility:hidden"></textarea>
			  </form>

			<script  type="text/javascript">
				function openExcel(){
	          my_editArea_content=editAreaLoader.getValue("curentContent");
						document.goToExcel.csvContent_query.value       =my_editArea_content;
						document.goToExcel.sqlDelimiter.value=document.formEditContent.sqlDelimiter.value;
						document.goToExcel.submit();
				}
			</script>	
			<form name="runSqlForm" style="display:inline" action="dbQuery_runSQL.php" method="post" target="_blank">
				<input type="hidden" name="showResultOnly" value="yes">
				<input type="hidden" name="sqlDelimiter" value="">
				<textarea rows="1" cols="1" name="query" style="display:none; visibility:hidden"></textarea>
			</form>


<script language="javascript" type="text/javascript">
		// initialisation
  if(editAreaLoader){
		editAreaLoader.init({
			id: "curentContent"	// id of the textarea to transform	
			,start_highlight: true
			,allow_toggle: false
			,language: "en"
			,syntax: "'.$editArea_syntax.'"	
			//,toolbar: "search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight, |, help"
			,syntax_selection_allow: "css,html,js,php,python,vb,xml,c,cpp,sql,r_script,basic,pas"
			,toolbar: "go_to_line,  select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight, |, help"
			,is_multi_files: false
			,EA_load_callback: "editAreaLoaded"
			,show_line_colors: true
		});
		
		
		

	}

</script>

</div>

	'.`php app_footer.php`;
	
?>	
