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

## should try another version then 4.01
## <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">


  include_once('common_connect.php'); checkViewAccess();
  
  if(checkWriteExecuteAccess()){
  	$runR_action=getCfgVal('//R/cgi_dir').'runR.pl';
  }else{
  	$runR_action="analysis_openEditor_ConsoleBlocked.html";
  }
  
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
	mysql_select_db($selectedDB,$dbh) or die("Could not select database $selectedDB");
  
	#  2008 Jul 29 - editing a content 
  #  2012 Sep 22 - adding 'parentPrimaryKey'
  

	$parentContent	=$_REQUEST['parentContent'];
	#$parentContent	=stripslashes($parentContent);
  $parentPrimaryKey=$_REQUEST['parentPrimaryKey'];

	$parentId	=$_REQUEST['parentId'];
	$parentName	=$_REQUEST['parentName'];


	$query="select $parentName,Thread_Step_Number from ".$selectedDB.".MB_CUSTOM_ANALYSIS  where Custom_Analysis_Id=$parentPrimaryKey ";
	$result = mysql_query($query);
	if ($result){
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$parentContent=$row{$parentName};
		$Thread_Step_Number=$row{'Thread_Step_Number'};
  }

	if($parentName=="") {
		$parentName="Db_script";
		$saveButtons='';
		$parentContent='
-- this is just an example of sql query	
create temporary table TMP select "alpha" as Struct,1 as Id union select "beta",2 union select "gamma",3;
select * from TMP
limit 100
    ';
	}else {
		if ($parentName=="Description"){$saveButtons='<input type="button" style="color:#000000" value="Edit/View" onclick="switchEditView()"> &nbsp;&nbsp;&nbsp;';}
		$saveButtons .='
		      <input type="button" style="color:#000000" value="Save" onclick="saveContent()">
					<!-- input type="button" style="color:#008C00" value="Save and Quit" onclick="saveContentAndQuit()" -->
					<!-- input type="button" style="color:#D20000" value="Abandon" onclick="if (confirm(\'Discard changes and close window?\')){window.close()}" -->';

	} # for the cases when we call from app_header etc


  $editAreaHeaders='      <script language="javascript" type="text/javascript" src="edit_area/edit_area_full.js"></script>';
  $wikiEditorHeaders='    
      <!-- this is for wiki -->

      <script type="text/javascript" src="js/ajax_googleapis_prototype.js"></script>

      <script type="text/javascript" src="wikiwym-read-only/lib/GoogleCodeWikiParser.js"></script>
      <script type="text/javascript" src="wikiwym-read-only/lib/parser.js"></script>
      

      <link type="text/css" rel="stylesheet" href="wikiwym-read-only/style.css">';
	$runHTML=='';
	$editHeaders='';
	$favIcon='';
	if ($parentName=="Db_script"){
		$favIcon='<link rel="icon" href="images/SQL.ico" />';
		$query="select OneTime_DbScript_Run from MB_CUSTOM_ANALYSIS where Custom_ANalysis_Id=$parentPrimaryKey";
		$result = mysql_query($query);
		$prepareRun  ="prepareRunSql()";
		$prepareExcel="openExcel()";
		if ($result){
			$row = mysql_fetch_array($result,MYSQL_ASSOC);
			$noMoreRun=strtolower(trim($row{'OneTime_DbScript_Run'}));
			if($noMoreRun=="yes"){
				$prepareRun ="alert('The sql script cannot be run anymore! UnCheck attribute `Prevent SQL run` for task : $parentPrimaryKey')";
				$prepareExcel=$prepareRun;
			}
    }
 
 		$bodyStyle="background-color:#E9EFDC";
		$runHTML='&nbsp;&nbsp;<a href="http://dev.mysql.com/doc/refman/5.0/en/select.html" target="_blank"><img alt="" src="images/manual.png" title="online mySQL doc" width="20"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sqlDelim:
			<input style="font-size:80%; font-weight:bold; color:blue" name="sqlDelimiter" size="1" value=";"> ;=char(59) &nbsp;
			<input type="button" style="background-color:#888888;color:#FFFFFF" value="Run" onclick="'.$prepareRun.'">
			&nbsp;&nbsp;<input type="button" style="background-color:#888888;color:#FFFFFF" value="Download result" onclick="'.$prepareExcel.'">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size:80%; color:#BBBBBB">based on <a href="http://www.cdolivet.com/editarea/" target="_blank" style="color:#BBBBBB">editArea javascript</a> </span>';
			$myTitle="s$Thread_Step_Number:$parentPrimaryKey";
	    $editArea_syntax="sql"; # the default syntax for coloring
	    $notes='';
	    $editingAreaHtml='<textarea name="curentContent" rows="80" cols="120" id="curentContent" style="height: 90%; width: 100%;" >'.$parentContent.'</textarea>';
	    $editHeaders=$editAreaHeaders;
	}
	if ($parentName=="R_script"){
		$favIcon='<link rel="icon" href="images/R.ico" />';
		$bodyStyle="background-color:#DBEEF4";
		if(preg_match('/```{r/',$parentContent)){
			$integratedDocAction='&nbsp;<input title="It generates documentation with inline results from the script that uses R markdown language; embedes Wiki, table relationships and data provenance" type="button" style="background-color:#9202CB;color:#FFFFFF" value="generate Full Docs" onclick="prepareIntegratedDoc()">';
		} else {
			$integratedDocAction='<input type="button" style="background-color:#888888;color:#FFFFFF" value="Run" onclick="prepareRunR()" title="Runs the script and provides the results and logs as links in a separate (bottom) window frame">';
		}
		$runHTML='&nbsp;&nbsp;<a href="http://cran.r-project.org/doc/manuals/R-lang.html" target="_blank"><img alt="" src="images/manual.png" title="online R language doc" width="20"></a>
		  &nbsp;&nbsp;&nbsp;<!- format: -->
			<input style="font-size:80%; font-weight:bold; color:blue" name="__Rformat" size="10" title="The default output Rplots.pdf (width x height) inches"  value="pdf(7x7)">
			'.$integratedDocAction.'
			&nbsp;&nbsp;<a style="font-size:80%; color:darkgrey" href="http://www.rstudio.com/ide/docs/r_markdown" target="_blank" title="Examples of using integrated documentation based on MarkDown and R">R &amp; Markdown doc</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size:80%; color:#BBBBBB">based on <a href="http://www.cdolivet.com/editarea/" target="_blank" style="color:#BBBBBB">editArea javascript</a> </span>';
			$myTitle=".$Thread_Step_Number:$parentPrimaryKey";
	    $editArea_syntax="r_script"; # the default syntax for coloring
	    $notes=' <a style="color:grey; font-size:80%"  href="analysis_wiki.php#Application_Programming_Interface__API_" target="_blank">API examples</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	    $editingAreaHtml='<textarea name="curentContent"  cols="120" id="curentContent" style="height: 80%; width: 100%;" >'.$parentContent.'</textarea>
	    <br><iframe name="resultsArea" id="resultsArea" width="100%" height="15%" style="color:grey"  scrolling="yes" src="analysis_openEditor_Console.html">result area</iframe>';
	    $editHeaders=$editAreaHeaders;
	}


	if ($parentName=="Description"){
		$favIcon='<link rel="icon" href="images/wiki.ico" />';		
		$bodyStyle="background-color:#FDEADA";
		$runHTML='&nbsp;&nbsp;<a href="wikiwym-read-only/index-prototype.html" target="_blank"><img alt="" src="images/manual.png" title="online Wiki sintax doc" width="20"></a>';
			$myTitle="w$Thread_Step_Number:$parentPrimaryKey";
	    $editArea_syntax="html"; # the default syntax for coloring
	    $notes='<span style="font-family:Arial;">Edit (with live preview) the wiki code in a textarea by clicking "Edit/View" button
	           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size:80%; color:#888888">based on <a href="http://code.google.com/p/wikiwym/" target="_blank" style="color:#888888">wikiwym javascript</a> </span>
	           <br></span>';
	    $editingAreaHtml='<table class="layout"><tr><td id="edit_td" class="layoutView"><textarea id="textarea" wrap="soft" rows="20" cols="60" style="height:100%" class="editor">'.$parentContent.'</textarea></td><td id="wikicontent" class="layout"></td></tr></table>';
	    $editHeaders=$wikiEditorHeaders;

	}

	#$parentContent  =str_replace('\\"','"',$parentContent);
	#$parentContent  =str_replace("\\'","'",$parentContent);
	
	#print "<script>alert('$parentContent');</script>";
	# must get rid of escaped quotes
	print '	
	<html>
		<head>
		  '.$favIcon.'		
			<title>'.$myTitle.'</title>
      <script type="text/javascript">window.focus()</script>
      '.$editHeaders.'      
   
		</head>
		<body style="margin:3px" onLoad="focus()">
		 <div class="mother" style="'.$bodyStyle.'">

	<form name="formUpdate" style="display:inline" method="post" action="analysis_openEditor_update.pl">
		<input type="hidden" name="__changedValue" value="">
		<input type="hidden" name="__changedColumn" value="">
		<input type="hidden" name="__tableName" value="MB_CUSTOM_ANALYSIS">
		<input type="hidden" name="__autoIncrementColumn" value="Custom_Analysis_Id"> 
		<input type="hidden" name="__autoIncrementValue" value=""> 
		<input type="hidden" name="__timestampColumn" value="Last_Updated">  <!-- this will be set to now() at  update current  in above table -->
  </form>

			<form name="formEditContent" action="">
			 
				<div style="inline; text-align:center;">
					'.$notes.$saveButtons.$runHTML.'
				</div>
				'.$editingAreaHtml.'
			</form>
     </div>
			<script type="text/javascript">
						
				function saveContent(){
					//opener.document.formInsert.'.$parentName.'.value=document.formEditContent.curentContent.value;
					if(document.getElementById("textarea")){ // this is for Wiki
					  if( "'.$parentId.'" !=""){  // if called directly from Flow there is no opener content and no ParentId
					    opener.document.formInsert.'.$parentName.'.value=document.getElementById("textarea").value;
					    // alert("Saved!");
					  }
					  {  // call directly
					    if (confirm("Update record?")){
					      if(opener.document.formInsert){
					        opener.document.formInsert.'.$parentName.'.value=document.getElementById("textarea").value;
					      }
					      document.formUpdate.__changedColumn.value="'.$parentName.'";
					      document.formUpdate.__changedValue.value=document.getElementById("textarea").value;
					      document.formUpdate.__autoIncrementValue.value='.$parentPrimaryKey.';
					      document.formUpdate.submit();
					    }
					  }  
					} else { // this is for Db_Script and R_Script
            my_editArea_content=editAreaLoader.getValue("curentContent");
					  if( "'.$parentId.'" !=""){  // if called directly from Flow there is no opener content and no ParentId
					    opener.document.formInsert.'.$parentName.'.value=my_editArea_content;
					    // alert("Saved!");
					  }
					  {
					    if (confirm("Update record?")){
					      if(opener.document.formInsert){
					        opener.document.formInsert.'.$parentName.'.value=my_editArea_content;
					      }
					      document.formUpdate.__changedColumn.value="'.$parentName.'";
					      document.formUpdate.__changedValue.value=my_editArea_content;
					      document.formUpdate.__autoIncrementValue.value='.$parentPrimaryKey.';
					      document.formUpdate.submit();					    
					    }
					  }  
					}  
					
				}
				function saveContentAndQuit(){
					if (confirm("Save changes and close window?")){
						if(document.getElementById("textarea")){
  					  if( "'.$parentId.'" !=""){  // if called directly from Flow there is no opener content and no ParentId
	  					  opener.document.formInsert.'.$parentName.'.value=document.getElementById("textarea").value;
	  					}  
						} else {					 
					    if( "'.$parentId.'" !=""){  // if called directly from Flow there is no opener content and no ParentId
	              my_editArea_content=editAreaLoader.getValue("curentContent");
						    opener.document.formInsert.'.$parentName.'.value=my_editArea_content;
						  }  
						}  
						window.close();
					}
				}
				function prepareRunSql(){
					with(document.runSqlForm){
						sqlDelimiter.value=document.formEditContent.sqlDelimiter.value;
						//query.value       =document.formEditContent.curentContent.value;
	          my_editArea_content=editAreaLoader.getValue("curentContent");
						query.value       =my_editArea_content;
						submit();
					}
				}
				function prepareRunR(){
				  var r_a=document.getElementById("resultsArea")
				  r_a.src="analysis_openEditor_Console.html";
					with(document.runRForm){
					  original_action=document.runRForm.action;
					  document.runRForm.action="analysis_openEditor_Console.html";
					  submit();
					  
					  document.runRForm.action=original_action;
						__Rformat.value=document.formEditContent.__Rformat.value;
						//rScript.value       =document.formEditContent.curentContent.value;
						my_editArea_content=editAreaLoader.getValue("curentContent");
						rScript.value       =my_editArea_content;
						//alert("Sending to:"+original_action)
						submit();
					}
				}
				
				function prepareIntegratedDoc(){
					with(document.runRForm){
						my_editArea_content=editAreaLoader.getValue("curentContent");
						document.integratedDocForm.rScript.value       =my_editArea_content;
						document.integratedDocForm.submit();
					}	
				}
				
			</script>


				<form name="integratedDocForm" action="analysis_integratedDoc.php" method="post" target="_blank">
				  <input type="hidden" name="__Rformat" value="pdf(7x7)">
				  <input type="hidden" name="parentPrimaryKey" value="'.$parentPrimaryKey.'">
				  <input type="hidden" name=runR_action value="'.$runR_action.'">
				  <textarea name="rScript" rows="1" cols="1" style="display:none;visibility:hidden"></textarea>
			  </form>

			
				<form name="goToExcel" action="dbQuery_downloadResult.php" method="post">
				  <input type="hidden" name="csvContent" value="">
				  <input type="hidden" name="delimiter" value="tab">
				  <input type="hidden" name="database" value="">
				  <input type="hidden" name="sqlDelimiter" value="">
				  <textarea name="csvContent_query" rows="1" cols="1" style="display:none;visibility:hidden"></textarea>
			  </form>

			<script type="text/javascript">
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
				<textarea name="query" rows="1" cols="1" style="display:none; visibility:hidden"></textarea>
			</form>
			<form name="runRForm" style="display:inline" action="'.$runR_action.'" method="post" target="resultsArea">
				<input type="hidden" name="__Rformat" value="pdf(7x7)">
				<input type="hidden" name="parentPrimaryKey" value="'.$parentPrimaryKey.'">
				<textarea name="rScript" rows="1" cols="1"  style="display:none; visibility:hidden"></textarea>
			</form>
';

if ($parentName !="Description"){
print '
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
		
		
		// callback functions
		function my_save(id, content){
			alert("Here is the content of the EditArea as received by the save callback function:\n"+content);
		}
		
		function my_load(id){
			editAreaLoader.setValue(id, "The content is loaded from the load_callback function into EditArea");
			window.focus();
		}
		
		function test_setSelectionRange(id){
			editAreaLoader.setSelectionRange(id, 100, 150);
		}
		
		function test_getSelectionRange(id){
			var sel =editAreaLoader.getSelectionRange(id);
			alert("start: "+sel["start"]+"\nend: "+sel["end"]); 
		}
		
		function test_setSelectedText(id){
			text= "[REPLACED SELECTION]"; 
			editAreaLoader.setSelectedText(id, text);
		}
		
		function test_getSelectedText(id){
			alert(editAreaLoader.getSelectedText(id)); 
		}
		
		function editAreaLoaded(id){
		  window.focus();
		}
		
		function open_file1()
		{
			
		}
		
		function open_file2()
		{
			
		}
		
		function close_file1()
		{
			
		}
		
		function toogle_editable(id)
		{
			
		}
	}

 </script>
';
} else {
	print '
      <script type="text/javascript">
        window.onload = function(){
          if(document.getElementById("textarea")){
            var parser = new Wikiwym("textarea", "wikicontent");
          }  
        }

				 function switchEditView(){
				 	 obj=document.getElementById("edit_td");
				 	 if(obj.className=="layoutView"){obj.className="layout"} else {obj.className="layoutView"}
				 } 

      </script>	
   ';   
}	


print '
<script type="text/javascript">
	window.focus();
</script>
		</body>

	</html>	
	';
