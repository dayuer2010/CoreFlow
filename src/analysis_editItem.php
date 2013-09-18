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


  $Rserver_cgi=getCfgVal('//R/cgi_dir');
  if($Rserver_cgi[strlen($Rserver_cgi)-1] !="/" ){$Rserver_cgi .= "/";}

	$mainId		=$_REQUEST['mainId'];
	$afterEditRecord=$_REQUEST['afterEditRecord']; ### when we save something in the forms here we should acknowledge with an alert
	if (isset($afterEditRecord)){
		
	} else {
		$alertScript=" window.focus(); // alert('Ready!');";
	}

  $tableName=getCfgVal("//db/custom_analysis_table"); # main table that keeps custom aanalysis
  
	$query="select x.*,a.Picture_Path from $tableName x left join ".$selectedDB.".MB_ANALYSIS_AUTHOR a on a.Analysis_Author=x.Analysis_Author where Custom_Analysis_Id=$mainId limit 1";
	$result = mysql_query($query);
	if ($result){
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$Thread_Step_Number=$row{'Thread_Step_Number'};
		
		$project     =$row{'Project'};
		$projectOwner=$row{'Project_Owner'};
		$threadName  =$row{'Thread_Name'};
		
		$sqlDelimiter	     =$row{'Sql_delimiter'}; if ($sqlDelimiter == "") {$sqlDelimiter=";";}

		$dateCreated=$row{'Created'};
		$dateUpdated=$row{'Last_Updated'};
    
    $dbScript   =$row{'Db_script'};
    $rScript    =$row{'R_script'};
    ### hide app server for public
    if(!checkWriteExecuteAccess()){
    	$rScript=preg_replace('/http:\/\/.*?\/cgi-bin\/CoreFlow\/api_/','http://app_server/cgi-bin/CoreFlow/api_',$rScript);
    }
    
    $analysisName   = $row{'Analysis_Name'};
    $analysisAuthor = $row{'Analysis_Author'};
    $description    = $row{'Description'};
    
    $qualityControlBy  =$row{'Quality_Control_By'};
    $qualityControlDate=$row{'Quality_Control_Date'};
    $ancestorStepsList =$row{'Ancestor_Step_Number_List'};
    $stepImportance    =$row{'Step_Importance'};
    $oneTimeDbScriptRun=$row{'OneTime_DbScript_Run'};
    $iconId            =$row{'Icon_Id'};
     	
    $authorPicturePath =$row{'Picture_Path'}; 	
     	
		if($stepImportance !="") {
			$stepImageHTML="images/analysisStep_small_".$stepImportance.".png";
		}else{
			$stepImageHTML="images/analysisStep_small_null.png";
		}


	} else {
		die("Could not get the data from $tableName ($mainId) !".mysql_error());
	}

  ### get all possible step importances
  $query="select Step_Importance from MB_CUSTOM_ANALYSIS_STEP_IMPORTANCE";
	$result = mysql_query($query);
	$allStepImportance_HTML='';
	if ($result){
    while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    	if($row{'Step_Importance'}==$stepImportance) {$importanceFlag="selected";} else {$importanceFlag="";}
    	$allStepImportance_HTML.='<option value="'.trim($row{'Step_Importance'}).'" '.$importanceFlag.' >'.trim($row{'Step_Importance'}).'</option>';
    }
  }
  $allStepImportance_HTML = '<select style="font-size:90%; padding:1px;" name="Step_Importance" id="col_16" title="Get/Set the importance">'.$allStepImportance_HTML.'</select>';
  
  
  ### get the current iconId and icon
	$query="select  concat(`Web_Path`,`File_Name`) as `Img_Src`,`Comments` from MB_ICON where `Icon_Id`=$iconId limit 1";
	$result = mysql_query($query);
  if ($result){
  	$row = mysql_fetch_array($result,MYSQL_ASSOC);  	
  	$currentIconSrc  =$row{'Img_Src'};
  	$currentIconTitle=$row{'Comments'};
  } else {
  	$currentIconSrc  ="images/palette.png";
  	$currentIconTitle="No icon attached to this analysis step";
  }




  ##### switch view in the DB_table from table to text
	function convertTabsToTable($tabContent){
		# split content into lines
		$contentAsHTML='';

		#$crtLines=preg_split('/$\R?^/m', $tabContent);
		$crtLines=explode("\n", $tabContent);
		foreach($crtLines as $myLine){
			if($myLine == ""){continue;}
		  # split line into fields
		  $lineAsHTML='';
		  $myFields=explode("\t",$myLine);
      foreach($myFields as $crtField){
      	$lineAsHTML .= "<td>".$crtField."</td>";
      }
      $contentAsHTML .= "<tr>".$lineAsHTML."</tr>";
		}
		 
		return $contentAsHTML;
	}			
?>

	  <html>
		<head>
			<LINK REL="stylesheet" HREF="css/generic.css" type="text/css">
			<title><?php print($Thread_Step_Number.':'.$mainId) ?></title>

			  <!-- calendar stylesheet -->
			  <link rel="stylesheet" type="text/css" media="all" href="css/calendar-green.css" title="green" >
			
			  <!-- main calendar program -->
			  <script type="text/javascript" src="css/calendar.js"></script>
			
			  <!-- language for the calendar -->
			  <script type="text/javascript" src="css/lang/calendar-en.js"></script>
			
			  <!-- the following script defines the Calendar.setup helper function, which makes
			       adding a calendar a matter of 1 or 2 lines of code. -->
			  <script type="text/javascript" src="css/calendar-setup.js"></script>
        <style type="text/css">
        	td.main_data_spacer {width:100px;}
        	td.main_data_info {text-align:right;}

        	table.info {color:#BBBBBB; font-size:80%}


		      td.commands   {text-align:center;
		  		            -webkit-border-radius: 10px;
	                    -khtml-border-radius: 10px;	
	                    -moz-border-radius: 10px;
	                     border-radius: 10px;
	                     border: 1px solid #BBBBBB;
	                     padding: 3px;
	                     background: #EEEEEE
		  	               }

          img { vertical-align:middle; }

	        .change_bkg:hover    {background-color: #FFFFFF}
          
        </style>
		</head>
		<BODY style="font-family:Arial; margin-top:2px; margin-left:px" onLoad="window.focus()" >

		<script src="css/generic.js" language="javascript" type="text/javascript"></script>


<!-- main items BEGIN -->
  
	<form name="formInsert" style="display:inline" method="post" action="analysis_editItem_save.pl">
		<input type="hidden" name="__command" value="">
		<input type="hidden" name="__changedColumns" value="">
		<input type="hidden" name="__tableName" value="MB_CUSTOM_ANALYSIS">
		<input type="hidden" name="__autoIncrementColumn" value="Custom_Analysis_Id"> <!-- this will be neglected at insert new record in above table -->
		<input type="hidden" name="__timestampColumn" value="Last_Updated">  <!-- this will be set to now() at insert new and update current  in above table -->
		<input type="hidden" name="__timeCreatedColumn" value="Created">  <!-- this will be set to now() at insert new  in above table -->


	<table>
		<tr>
			<td class="commands">
				<table>					
					<!-- Placement of the Analysis (Owner, Project, Thread...) -->
					<tr>
						<td>
              <table class="info">
								<tr>
									<td style="text-align:left" colspan="4">
										<img alt="" src="images/owner_minus_small.png" height="24" title="Project owner"><input name="Project_Owner" id="col_1" value="<?php print $projectOwner; ?>">
										  <textarea style="visibility:hidden; display:none" id="oldValue_col_1" rows="1" cols="1"><?php print $projectOwner; ?></textarea>
										  <img alt="" style="cursor:pointer" src="images/ordered_list.gif" title="List other similar values" onclick="document.goToExcel.foreignTable.value='select distinct Project_Owner from MB_CUSTOM_ANALYSIS order by 1';showForeignkeyTable()">
									</td>
								</tr>
                <tr>
                	<td>&nbsp;</td>
                	<td style="text-align:left" colspan="3">
									  <img alt="" src="images/project_minus_small.png" height="24" title="Project Name"><input name="Project" id="col_2" value="<?php print $project; ?>">
									    <textarea style="visibility:hidden; display:none" id="oldValue_col_2" rows="1" cols="1"><?php print $project; ?></textarea>
									    <img alt="" src="images/ordered_list.gif" title="List other similar values"  onclick="document.goToExcel.foreignTable.value='select distinct Project,Project_Owner from MB_CUSTOM_ANALYSIS order by 2,1';showForeignkeyTable()">
                	</td>
                </tr>
                <tr>
                	<td>&nbsp;</td><td>&nbsp;</td>
                	<td style="text-align:left" colspan="2">
						 			  <img alt="" src="images/thread_minus_small.png" height="24" title="Thread Name"><input name="Thread_Name" id="col_3" value="<?php print $threadName; ?>">
						 			    <textarea style="visibility:hidden; display:none" id="oldValue_col_3" rows="1" cols="1"><?php print $threadName; ?></textarea>
									    <img alt="" src="images/ordered_list.gif" title="List other similar values"  onclick="document.goToExcel.foreignTable.value='select distinct Thread_Name,Project,Project_Owner from MB_CUSTOM_ANALYSIS order by 3,2,1';showForeignkeyTable()">               		
                	</td>
                </tr>
                <tr>
                	<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                	<td>
									  &nbsp;&nbsp;&nbsp;&nbsp;<img alt="" src="<?php print $stepImageHTML; ?>" height="16" title="Step Number"><input name="Thread_Step_Number" id="col_4" size="4" value="<?php print $Thread_Step_Number; ?>">
									  <textarea style="visibility:hidden; display:none" id="oldValue_col_4" rows="1" cols="1"><?php print $Thread_Step_Number; ?></textarea>
									  &nbsp;&nbsp;&nbsp;&nbsp;Created:<?php print $dateCreated ?> &nbsp;&nbsp;&nbsp;&nbsp;Last Updated:<?php print $dateUpdated ?>
									  
					          <img alt="" style="cursor:pointer; vertical-align:top" src="images/attachment.png" width="22" title="Manage Attached files" onclick="rollOn('tip_H_controlPanel')">
                	</td>
                </tr>
              </table>
						</td>
					</tr>

					<!-- Main data, Author, Name, DB script, RScript, Summary -->
					<tr>
						<td>
							<table class="info">
								<tr>
									<td class="main_data_spacer"></td>
									<td class="main_data_info"><span>Analysis Author:</span></td>
									<td style="text-align:left">
										<input name="Analysis_Author" id="col_5" value="<?php print $analysisAuthor ?>"><img alt="" id="crtAuthorPictureSrc"  src="images/Icons/<?php print $authorPicturePath ?>" width="20" title="<?php print $analysisAuthor ?>">
									  <img alt=""  src="images/ordered_list.gif" title="List other similar values"  onclick="document.goToExcel.foreignTable.value='MB_ANALYSIS_AUTHOR';showForeignkeyTable()">
										<textarea style="visibility:hidden; display:none" id="oldValue_col_5" rows="1" cols="1"><?php print $analysisAuthor; ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="main_data_spacer"></td>
									<td class="main_data_info"><span style="cursor:help" title="a few words describig the analysis">Analysis Short Name:</span></td>
									<td style="text-align:left">
										<input name="Analysis_Name" id="col_6" value="<?php print $analysisName ?>" size="80">
										<textarea style="visibility:hidden; display:none" id="oldValue_col_6" rows="1" cols="1"><?php print $analysisName; ?></textarea>
									</td>
								</tr>


								<tr>
									<td class="main_data_spacer"></td>						    		
									<td class="main_data_info">
										<span style="cursor:help" title="Description, in wiki format, of the esential assumptions and calculations performed">Analysis Summary</span>
										<br>(wiki or markdown for knitr)<input type="button" title="open wiki or markdown for knitr editor in a new tab" onclick="openEditor('col_15','Description',<?php print $mainId ?>,'<?php print "w$mainId" ?>')" style="background-color:transparent; color:blue; width:15px; padding:1px; cursor:pointer" value="...">:
									</td>
									<td>
										<textarea name="Description" style="background-color:#FDEADA" id="col_15" rows="3" cols="80"><?php print $description; ?></textarea>
										<textarea style="visibility:hidden; display:none" id="oldValue_col_15" rows="1" cols="1"><?php print $description; ?></textarea>
									</td>  
								</tr>

								<tr>
									<td class="main_data_spacer"></td>
						    	<td class="main_data_info">
						    		<span style="cursor:help" title="SQL script to be run on the DB server; result can be attached or re-generated with a call from the BioInfo/Stats script">data pre processing
						    			<br> (SQL)</span>
						    			<img alt=""  style="cursor:pointer" 
						    			  onclick="javascript:{if(popup) popup.close();popup=window.open('<?php print getCfgVal('//svn_server') ?>commit_to_git.pl?what=Db_script&amp;Custom_Analysis_Id=<?php print $mainId; ?>','View/commit to versioning','height=420,width=600, left=150, top=150, directories=0, location=0, menubar=0, status=0, titlebar=0, scrollbars=1');}" 
						    			  title="view/update svn/git versioning with this SQL script that is stored in DB" src="images/github-fork.png" height="16">
						    		&nbsp;
						    		<input type="button" title="open/run script in a separate colored syntax editor" onclick="openEditor('col_10','Db_script',<?php print $mainId ?>,'<?php print "s$mainId" ?>')" style="background-color:transparent; color:blue; width:15px; padding:1px; cursor:pointer" value="...">
						    		<br><!--input type="button" value="get data provenance"-->
						    	</td> 
						    	<td>
						    		<textarea name="Db_script" style="background-color:#E9EFDC" id="col_10" rows="6" cols="80"><?php print $dbScript ?></textarea>
						    		<textarea style="visibility:hidden; display:none" id="oldValue_col_10" rows="1" cols="1"><?php print $dbScript; ?></textarea>
						    		
						    		<input type="hidden" id="Sql_delimiter" name="" value="<?php print $sqlDelimiter; ?>">
						    		<textarea style="visibility:hidden; display:none" id="oldValue_col_28" rows="1" cols="1"><?php print $sqlDelimiter; ?></textarea>
						    	</td>
								</tr>
								<tr>
									<td class="main_data_spacer"></td>
						    	<td class="main_data_info">
						    		<span style="cursor:help" title="script will be run on the Linux/Unix stats server by R, #!/usr/bin/env perl, #!/usr/bin/env python">Bioinformatics and/or 
						    			<br>Statistical analysis</span>
						    		<br>(R, perl, python...)
						    		<!-- popup=window.open('about:blank','Manage attachments','height=20,width=600, left=150, top=150, directories=0, location=0, menubar=0, status=0, titlebar=0'); document.attachFile_form.submit(); return true} -->  
						    			<img alt=""  style="cursor:pointer" 
						    			  onclick="javascript:{if(popup) popup.close();popup=window.open('<?php print getCfgVal('//svn_server') ?>commit_to_git.pl?what=R_Script&amp;Custom_Analysis_Id=<?php print $mainId; ?>','View/commit to versioning','height=420,width=600, left=150, top=150, directories=0, location=0, menubar=0, status=0, titlebar=0,scrollbars=1');}" 
						    			  title="view/update svn/git versioning with this script that is stored in DB" src="images/github-fork.png" height="16">
						    		&nbsp;<input type="button" title="open/run script in a separate colored syntax editor" onclick="openEditor('col_11','R_script',<?php print $mainId ?>,'<?php print ".$mainId" ?>')" style="background-color:transparent; color:blue; width:15px; padding:1px; cursor:pointer" value="...">
						    		<br><!--input type="button" value="get data provenance"-->
						    	</td>
						    	<td>
						    		<textarea name="R_script" style="background-color:#DBEEF4" id="col_11" rows="6" cols="80"><?php print $rScript ?></textarea>
						    		<textarea style="visibility:hidden; display:none" id="oldValue_col_11" rows="1" cols="1"><?php print $rScript; ?></textarea>
						    	</td> 
								</tr>

								<tr>
									<td class="main_data_spacer"></td>
									<td class="main_data_info"></td>
									<td><hr></td>
								</tr>

								<tr>
									<td class="main_data_spacer"></td>						    		
									<td class="main_data_info">
										Optional:
									</td>
									<td>
						       <span style="cursor:help; font-size:90%" title="Shows this analysis with different color in the general analysis flow">Importance:</span><?php print $allStepImportance_HTML ?>&nbsp;&nbsp;
						       <textarea style="visibility:hidden; display:none" id="oldValue_col_16" rows="1" cols="1"><?php print $stepImportance; ?></textarea>
						       <span style="cursor:help; background-color:#E9EFDC; font-size:90%" title="Protect accidental re-run of the SQL script (Can be reset only from the `DB query` menu!)">Prevent SQL run</span><input type="checkbox" name="OneTime_DbScript_Run" id="col_17" value="Yes" <?php if($oneTimeDbScriptRun=="Yes"){ print "checked";} ?> >
						                      <textarea style="visibility:hidden; display:none" id="oldValue_col_17"  rows="1" cols="1"><?php print $oneTimeDbScriptRun; ?></textarea>&nbsp;&nbsp;
						       <span style="cursor:help; font-size:90%" title="If set will prevent any changes to the SQL, BioInfo/Stats and Summary. Make it empty to alow updates to DB.">QC By:</span><input style="font-size:90%" name="Quality_Control_By" id="col_18" value="<?php print $qualityControlBy ?>" size="10">
						             <textarea style="visibility:hidden; display:none" id="oldValue_col_18" rows="1" cols="1"><?php print $qualityControlBy; ?></textarea>&nbsp;&nbsp;
						       <span style="cursor:help; font-size:90%" title="If set will prevent any changes to the SQL, BioInfo/Stats and Summary. Make it empty to alow updates to DB.">QC Date:</span><input type="button" id="date_col_20" title="use a calendar" style="width:28px;height:29px;background: url(images/calendar_24.png) no-repeat; font-size:90%" value="   ">
						               <input name="Quality_Control_Date" id="col_20" value="<?php print $qualityControlDate ?>" size="10">
						               <textarea style="visibility:hidden; display:none" id="oldValue_col_20" rows="1" cols="1"><?php print $qualityControlDate; ?></textarea>
						       
						       <!--
						         <br><span style="cursor:help" title="Set manually the `upstream` dependencies for this analysis">Ancestors Ids:</span><input name="Ancestor_Step_Number_List" id="col_23" value="<?php print $ancestorStepsList ?>" size="70">
						       	     <textarea style="visibility:hidden; display:none" id="oldValue_col_23" value="" rows="1" cols="1"><?php print $ancestorStepsList; ?></textarea>
						       -->
						       	   
									</td>  
								</tr>

							</table>	
						</td>
					</tr>


					
				</table>
		  </td>
		  <!-- global commands -->
			<td class="commands">
				  <a href="index.php" target="CoreFlow" title="open main CoreFlow page in a separate tab if not already present (Google Chrome only)"><img alt="" style="vertical-align:bottom"  src="images/home2.png" height="23px"/></a> 
          <br>
          <br>
          <br>
          <br>
          <br>
				
				  <span title="The unique identifier of this analysis step in CUSTOM_ANALYSIS table">Id:<?php print $mainId?><input name="Custom_Analysis_Id" type="hidden" value="<?php print $mainId?>" ></span>
					<br><img alt=""  style="cursor:pointer" id="crtIconSrc" src="<?php print $currentIconSrc; ?>" width="32" title="Click to change icon!" onclick="document.goToExcel.foreignTable.value='MB_ICON';showForeignkeyTable()">
					<br><input name="Icon_Id" id="col_27" type="hidden" value="<?php print $iconId; ?>" >
						  <textarea style="visibility:hidden; display:none" id="oldValue_col_27" rows="1" cols="1"><?php print $iconId; ?></textarea>
					<br>
					<br>	 
					<br><input type="button" name="__Delete" style="cursor:pointer; color:#FF0000" value="Delete" onclick="confirmDelete()" title="Delete this analysis from the FlowChart DB"><br>
				  <br><input type="button" name="__Insert" style="cursor:pointer; color:#804000" value="Insert" onclick="confirmInsert()" title="Insert this analysis as new step in the FlowChart DB"><br>
				  <br><input type="button" name="__Update"                                       value="Update" onclick="confirmUpdate()" title="Update this analysis in the FlowChart DB"><br>
				   
		  </td>
	  </tr>
  </table>
		
</form>


<!-- main items END -->

<?php
  ### get tha attached files
	$query="select Custom_Analysis_Result_Id,Result_Type,Result_Description from MB_CUSTOM_ANALYSIS_RESULT where Custom_Analysis_Id=$mainId";
	$result = mysql_query($query);
	$attachedResultsHTML="";
	if ($result){
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$Custom_Analysis_Result_Id=trim($row{'Custom_Analysis_Result_Id'});
			$Result_Type              =trim($row{'Result_Type'});
			$Result_Description       =trim($row{'Result_Description'});
			$assocIcon                           ="images/binary.png";

			$Result_Description_Norm  =str_replace("'",'`',str_replace('"','`',$Result_Description)); # replace quotes with `

			if(preg_match("/\.png$/i",$Result_Type)|| preg_match("/jpg$/i",$Result_Type) || preg_match("/gif$/i",$Result_Type)) $assocIcon ="images/img_icon.png";
			if(preg_match("/\.ppt.*$/i",$Result_Type)) $assocIcon ="images/pptx_icon.png";
			if(preg_match("/\.doc.*$/i",$Result_Type)) $assocIcon ="images/docx_icon.png";
			if(preg_match("/\.xls.*$/i",$Result_Type)) $assocIcon ="images/xls_icon.png";
			if(preg_match("/\.pdf$/i",$Result_Type)) $assocIcon ="images/pdf_icon.png";
			if(preg_match("/\.htm.*$/i",$Result_Type)) $assocIcon="images/html_icon.png";
			if(preg_match("/\.svg$/i",$Result_Type)) $assocIcon ="images/svg_logo.jpg";
			$attachedResultsHTML .='<tr class="change_bkg">
																	<td style="font-size:80%; color:#BBBBBB" title="attachment Result Id (in MB_CUSTOM_ANALYSIS_RESULT)">'.$Custom_Analysis_Result_Id.'</td>
			                            <td><a target="_blank" href="analysis_getAttachement.pl?Custom_Analysis_Result_Id='.$Custom_Analysis_Result_Id.'">
			                                <img alt=""  title="get attached  ('.$Result_Type.') " src="'.$assocIcon.'" height="20" border="0">
			                                </a>
			                            </td>
			                            <td style="font-size:80%; color:#BBBBBB">'.$Result_Description.'</td>
			                            <td><a href="#" style="cursor:pointer" onclick="if (confirm(\'Delete this atachement?\')){ if (popup)popup.close(); popup=window.open(\'analysis_editItem_manageAttachments.pl?action=delete&amp;Custom_Analysis_Result_Id='.$Custom_Analysis_Result_Id.'\',\'Manage attachments\',\'height=20,width=600, left=150, top=150, directories=0, location=0, menubar=0, status=0, titlebar=0\');} return false;"><img alt=""  title="delete this attachment" src="images/trash.png" border="0"></a></td>
			                        </tr>';
		}
	}
  if($attachedResultsHTML !=""){
  	$attachedResultsHTML = '<br><table>'.$attachedResultsHTML.'</table>';
  }
?>

    <form name="attachFile_form" method="post" action="analysis_editItem_manageAttachments.pl" target="Manage attachments" style="display:inline" enctype="multipart/form-data">
			<div style="text-align:left; display:inline; -webkit-border-radius: 10px; -khtml-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; padding:3px" id="tip_H_controlPanel" class="tooltip">
		    <img alt=""  style="cursor:pointer" src="images/close_object.png" width="16" border="0" title="close Attachment Manager" onclick="rollOut('tip_H_controlPanel')">&nbsp;
		    <img alt=""  style="cursor:pointer" src="images/view-refresh.png" width="16" border="0" title="refresh" onclick="window.location.reload();return true;">
		    <span style=" color:#BBBBBB">available attachments:</span>
		    <hr> 
        <?php print $attachedResultsHTML ?>
        <br>
        <input type="hidden" name="Custom_Analysis_Id" value="<?php print $mainId?>">
        <input type="hidden" name="action" value="attach">
        Enter a short description:<input name="short_description" size="20" title="short description of the content">
				<br><input type="file" style="background-color:#DDDDDD;" name="local_file" size="20">
				 
        <input style="cursor:pointer" type="button" onclick="if(confirm('[Insert?] or [Update when existing same `short description`?]')){popup=window.open('about:blank','Manage attachments','height=20,width=600, left=150, top=150, directories=0, location=0, menubar=0, status=0, titlebar=0'); document.attachFile_form.submit(); return true}" name="__Attach" value="Attach file" title="Attach a new file to this analysis after Browsing and selecting it.">				  
		  </div>
    </form>									  



		<form name="goToExcel" action="dbQuery_downloadResult.php" method="post">
			<input type="hidden" name="csvContent" value="">
			<input type="hidden" name="foreignTable" value="">
			<input type="hidden" name="sqlDelimiter" value=";">
		</form>
		<form name="formOpenEditor" action="analysis_openEditor.php" method="post" target="">
			<input type="hidden" name="parentPrimaryKey"/>
			<input type="hidden" name="parentId"/>
			<input type="hidden" name="parentName"/>
			<input type="hidden" name="parentContent"/>
		</form>
		<script type="text/javascript">


				    Calendar.setup({
				        inputField     :    "col_20",      // id of the input field
				        ifFormat       :    "%Y-%m-%d",       // format of the input field
				        showsTime      :    false,            // will display a time selector
				        button         :    "date_col_20",   // trigger for the calendar (button ID)
				        singleClick    :    false,           // double-click mode
				        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
				    });
			
			function openExcel(content){
				document.goToExcel.csvContent.value=content;
				document.goToExcel.submit();
			}

			function confirmDelete(){
				if (confirm("Delete current record?")){
					if (document.formInsert.Quality_Control_By){
						if (document.formInsert.Quality_Control_By.value!=""){
							if (! confirm("This is a Quality_Control_By entry! - delete anyway?")){
								return;
							}
						}
					}
					with (document.formInsert){
						__command.value="delete";
						submit();
					}	
				}
			}
			


			function confirmUpdate(){
					with (document.formInsert){
						__command.value="update";
						;
						__changedColumns.value="";
						for (i=1; i<=99; i++){
							o=document.getElementById("col_"+i);
							if (! o){continue}
							oOld=document.getElementById("oldValue_col_"+i+"");
							if (! oOld){continue}
      							if (o.value != oOld.value){
      								if (__changedColumns.value != ""){__changedColumns.value += ","; }
      								__changedColumns.value += o.name;
      							}
							if (o.name=="Quality_Control_By"){
								if (oOld.value !=""){
									// allow only removing the QC
									alert("Cannot update if Quality_Control_By is enforced!");
									return;
								}
							}	
						}
						if (__changedColumns.value == ""){
							alert("There is no change to update!");
							return;
						}
						if (confirm("Update record?")){
							submit();
						}	
					}	
			}


			
			function confirmInsert(){
				if (confirm("Insert new record?  This might result in duplicates !")){
					with (document.formInsert){
						// reset the Quality_Control_By
						if (document.formInsert.Quality_Control_By){
							cmd="document.formInsert.Quality_Control_By.value=\'\'";
							eval(cmd);
						}
						__command.value="insert";
						submit();
					}	
				}
			}

			var popup;
			function showForeignkeyTable(){
				obj=document.getElementById("iFrameSpan");
				if (document.goToExcel.foreignTable.value == "MB_ICON"){
					fLocation="browseDB_describeTable.php?noStructure=Y&table=select Icon_Id, concat(\"<img alt  src=\",Web_Path,File_Name,\" width=20>\") as Icon,Comments,concat(Web_Path,File_Name) as Path from MB_ICON";
				} else {
					if(document.goToExcel.foreignTable.value == "MB_ANALYSIS_AUTHOR"){
						fLocation="browseDB_describeTable.php?noStructure=Y&table=select distinct Analysis_Author,Picture_Path,concat(\"<img alt  width=20 src=images/Icons/\",Picture_Path,\" >\") as Picture from MB_ANALYSIS_AUTHOR order by 1";
					}else{
					  fLocation="browseDB_describeTable.php?noStructure=Y&table="+document.goToExcel.foreignTable.value;
					} 
				}

				if (popup)popup.close();
				if(document.documentElement.scrollTop){
					offset_Left=document.documentElement.scrollLeft;
					offset_Top =document.documentElement.scrollTop;
				}else{
					offset_Left=document.body.scrollLeft;
					offset_Top =document.body.scrollTop;
				}
				var winX = (document.all)?window.screenLeft:window.screenX;
				var winY = (document.all)?window.screenTop:window.screenY;

				xCoord=150;
				yCoord=150;
				
				popup_left = winX+(xCoord + 20 + offset_Left)+230;
				popup_top  = winY+(yCoord - 15 + offset_Top) +230;
				popup=window.open(fLocation
						 ,document.goToExcel.foreignTable.value
						 ,"height=200,width=400, left="+popup_left+", top="+popup_top+", directories=0, location=0, menubar=0, status=0, titlebar=0, scrollbars=1");


			}




			function runRscript(parentPrimaryKey){
					//alert(document.formRun_R_Script.rScript);
					document.formRun_R_Script.rScript.innerHTML  =document.formInsert.R_script.value;
					//alert(document.formInsert.R_script.value);
					document.formRun_R_Script.__Rformat.value=document.formInsert.__Rformat.value;
					document.formRun_R_Script.parentPrimaryKey.value=parentPrimaryKey;
					document.formRun_R_Script.submit();
			}

			function openEditor(objId,objName,primaryKey,myTarget){
				obj=document.getElementById(objId);
				document.formOpenEditor.parentId.value=objId;
				document.formOpenEditor.parentName.value=objName;
				document.formOpenEditor.parentContent.value=obj.value;
				document.formOpenEditor.parentPrimaryKey.value=primaryKey;
				document.formOpenEditor.target=myTarget;
				document.formOpenEditor.submit();
			}
			
			
			<?php print $alertScript ?>
			// document.onload=window.focus();
		</script>
		</BODY>
		</html>



<?php   mysql_close($dbh); ?>	
