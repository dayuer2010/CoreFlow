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
	mysql_select_db($selectedDB,$dbh) or die("Could not select database $selectedDB");

  $tableName = $selectedDB.'.MB_CORE_FLOW_WIKI_NOTE';
  $initialWikiNote='
= Purpose =
  * Management and integration of analysis tasks and of data; helps modelling complex and heterogeneous sets of data.

= Overview =

  * Curent examples are from molecular biology field 
  * Curent target: audience computational biologists, biologists, bioinformaticians

    The focus of the application is managing Pipelines of data Analysis tasks that are described in structured textual format (such as scripts).
    One task (called also "step") can use data stored in one or more  database table or from files atached to some analysis steps.


    Database (DB) management is flexible and powerful:

 * users has almost all privileges on the database tables
 *  easy import of tab delimited files
 * easy data exchange between a table and a spreadsheet
 * a software developer can easily extend imports of other types of data sources (parsing Mascot, MaxQuant, Flow Cytometry files etc...)


    *Main elements of a task:*
 
  * Description area (Wiki format)
  * Database processing area (SQL language - colored syntax editor)
  * High level statistical analysis or informatics area (R perl/Bioperl, Biopython/python, etc... - colored syntax editor)

 
   *WorkFlows and Pipeline organization:* 

 * Owner (can be generic like Mass Spectrometry or name: Rachel),
 * Project (eg study of ShcA scaffold protein)
 * Thread (eg an aspect or collection of related experiments: eg k-down of ShcA in Mouse myocard). Each thread is composed of steps (or tasks). They are the building blocks of the analysis.
 *  The Data "provenance" actions provides a graph showing how data is passed from one task to another. 

 * Tasks are sorted by their task number (a floating point number - that helps changing the order by changing just one number)
 * Related tasks can exchange or pass data. For example one step can extract and filter experiment data from tables. The next one can use the predecessor\'s data by applying some statistical models (look for significan differences between conditions, etc.). 

 * Tasks have also other attributes: Owner, Project, Thread, Date created/updated, Quality Control date and person, list of attachments, an associated icon, importance, etc.


   *CoreFlow architecture:*

  * a webserver (that satisfies requests from browsers)
  * a database server (where one or more databases are maintained, backed-up, balanced, etc) 
  * an application server (where the R,perl,python scripts are run, etc.) 
  * a versioning server (where is stored a history of requested snapshots of the R,perl,python, etc scripts)
 
Notes:
  
  # All servers can physically reside on the same computer.
  # In a normal implementation servers are situated *behind a Fire Wall*
  # There is *no user authentication* since it targets a small or medium size lab that communicate very well
  # The configuration allows for *restriction of updates/inserts and execution* to a subset of selected IP addresses
  

== Application Programming Interface (API) ==

 * To run a sql query stored in the SQL area of any *task* and retrieve it\'s result in a tab delimited format: 
 *.../api_runDBscript.php?Custom_Analysis_Id=*_mainId_


 * To obtain the content of any attachment 
 *.../api_getAttachment.pl?Custom_Analysis_Result_Id=*_attachId_

 *...* is the url of the CoreFlow application server
 
  _mainId_ is the unique Database Id associated to a task

 _attachId_ is the unique attachment Id
 
   ';
  
  
  $action=trim($_REQUEST['editAction']);
  if($action=="update"){
		$wikiNote=trim($_REQUEST['Wiki_Note']);
		$query="update $tableName set Wiki_Note ='".$wikiNote."'";
		$result = mysql_query($query);
		if ($result){
			print('<script>location.href="analysis_wiki.php"</script>');
			exit;
    }else{
    	print ('<span style="color:red">You do not have permissions to update your own Wiki_Note for CoreFlow. Please contact authors.</span>'); ### add here .mysql_error() if you need to debug
    	# print '<pre>'.$query."</pre><br>";
    	print "<pre style='color:red'>".mysql_error()."</pre>\n";
    	exit;
    }  	
  }
  
  
  
  
  ## read the content  of the table MB_CORE_FLOW_WIKI_NOTE if exists if note then create one and populate
	$query="select Wiki_Note from $tableName limit 1";
	$result = mysql_query($query);
	if ($result){
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$wikiNote=trim($row{'Wiki_Note'});
    if($wikiNote == ""){
					$query="insert into $tableName values('".$initialWikiNote."')";
					$result = mysql_query($query);
					$wikiNote=$initialWikiNote;
    }
	} else {
		# probably the table does not exist. Create one
		 if(preg_match("/doesn't.*exist/i",mysql_error())){
				$query="create table $tableName (Wiki_Note mediumtext, Wiki_Note_Install mediumtext)";
				$result = mysql_query($query);
				if ($result){
					$query="insert into $tableName(Wiki_Note) values('".$initialWikiNote."')";
					$result = mysql_query($query);
	      }else{
	      	die('<span style="color:red">You do not have permissions to create your own Wiki_Note for the public CoreFlow version! Please contact authors.</span>!'); ### add here .mysql_error() if you need to debug
	      }
		 };
	}
  
  
  


?>

	<html>
		<head>
			<link rel="icon" href="images/wiki.ico" />		
			<title>CoreFlow wiki-notes</title>
      <!--script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script-->
      <script type="text/javascript" src="js/ajax_googleapis_prototype.js"></script>

      <script type="text/javascript" src="wikiwym-read-only/lib/GoogleCodeWikiParser.js"></script>
      <script type="text/javascript" src="wikiwym-read-only/lib/parser.js"></script>
      <script type="text/javascript">
        window.onload = function(){
          if(document.getElementById("textarea")){
            var parser = new Wikiwym("textarea", "wikicontent");
          }  
        }
      </script>
      <link type="text/css" rel="stylesheet" href="wikiwym-read-only/style.css" />   
		</head>
		<body style="background-color:#FDEADA">
		  <style>
		    img {vertical-align:middle}
		  </style>
		
			<form name="formEditContent" method="post">
				<div style="inline; text-align:center;">
          <span style="font-family:Arial;">Edit (with live preview) the wiki code in a textarea by clicking "Edit/View" button.
	           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size:80%; color:#888888">based on <a href="http://code.google.com/p/wikiwym/" target="_blank" style="color:#888888">wikiwym javascript</a> </span>
	           <br>
	        </span>
	        <input type="hidden" name="editAction" value="update"> 
					<input type="button" style="color:#000000" value="Edit/View" onclick="switchEditView()">
					<input type="button" style="color:#000000" value="Save" onclick="if(confirm('Save changes?')) submit()">

					&nbsp;&nbsp;<a href="wikiwym-read-only/index-prototype.html" target="_blank"><img src="images/manual.png" title="online Wiki sintax doc" width="20"></a>
				</div>
				<table class="layout">
					<tr>
						<td id="edit_td" class="layoutView">
							<textarea id="textarea" wrap="soft" rows="240" name="Wiki_Note" class="editor"><?php print $wikiNote ?></textarea>
						</td>
						<td id="wikicontent" class="layout"></td>
					</tr>
				</table>
			</form>

			<script type="text/javascript">
				 window.focus();
				 function switchEditView(){
				 	 obj=document.getElementById("edit_td");
				 	 if(obj.className=="layoutView"){obj.className="layout"} else {obj.className="layoutView"}
				 } 
			</script>
			
		</body>

	</html>	
