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
== CoreFlow DOWNLOAD and INSTALLATION instructions ==

The latest stable release of CoreFlow is available for download at [http://pawsonlab.mshri.on.ca/CoreFlow/download.php http://pawsonlab.mshri.on.ca/CoreFlow/download.php].

===INSTALLATION REQUIREMENTS===

 * PHP: [http://php.net/  http://php.net/] version 5 or later  with MySQL support (modify php.ini)
 * Perl: [http://perl.org http://perl.org] 
 * Apache HTTP server: [http://httpd.apache.org/ http://httpd.apache.org/] 
 * Python version 2.4 or later: [http://python.org/ http://python.org/] ; tutorial available at: [http://docs.python.org/using/index.html http://docs.python.org/using/index.html] 
 * BioPython: [http://biopython.org/wiki/Main_Page http://biopython.org/wiki/Main_Page]  
 * MySQL DBMS version 5 or later: [http://www.mysql.com/ http://www.mysql.com/] 



===INSTALLATION PROCEDURE===

 CoreFlow can be installed on any Unix/Linux/Mac platform.

 Database and server code can be installed either on the same hardware node (server), or on separate nodes (web server, database server, application server), according to your preference.

 It is *strongly recommended* to have all your server nodes <span style="color:red">*behind a FireWall*</span>. 

 # Download and extract `coreFlow_web_2013_01.tgz` into your web server directory. The unpacked directory contains:
  * A folder named `CoreFlow`  main website directory
  * A database dump file, named `coreflow.sql`, and
  * A copy of the Installation Instructions in PDF format
 # Edit `httpd.conf` Apache configuration file as follows:
  * Define ServerName, DocumentRoot and ServerAlias for your CoreFlow setup directory.
  * Set LD_LIBRARY_PATH to point to your MySQL directory.
  * It is helpful to designate an ErrorLog file, e.g. /my_install_dir/my_coreflow-error.log.
  * Example: (replace the highlighted directives according to your servers specifications)

<pre>
  <Directory /var/www/html/CoreFlow> 
   IndexIgnore .config.xml 
   DirectoryIndex index.php
   Options Indexes FollowSymLinks 
   AddType application/x-httpd-php .php
   Options ExecCGI 
   AddHandler cgi-script .cgi .pl .py .php
   AllowOverride AuthConfig 
   Order deny,allow 
   Allow from all # or the ip addresses range you want to allow accessing CoreFlow 
 </Directory> 
</pre>

 # In your MySQL workspace, create an empty schema that will be populated in the next step:
  * `mysql> create database my_coreflow_db;`
  * (replace `my_coreflow_db` with a schema name of your choice)
  * Use the database dump file to add tables to the newly created schema. At the command prompt, type: 
   * `xterm> mysql u my_user p < coreflow.sql`
 # Create a new MySQL user, e.g. `coreflow_web`, and grant them  SELECT, INSERT, UPDATE, DELETE, ALTER, CREATE TEMPORARY TABLES ON  privileges on `my_coreflow_db`.
 # In the CoreFlow folder, edit .config.xml:
 - details here... 
   ';
  
  
  $action=trim($_REQUEST['editAction']);
  if($action=="update"){
		$wikiNote=trim($_REQUEST['Wiki_Note']);
		$query="update $tableName set Wiki_Note_Install ='".$wikiNote."'";
		print($query);
		$result = mysql_query($query);
		if ($result){
			print('<script>location.href="install_wiki_edit.php"</script>');
			exit;
    }else{
    	print $query."<br>";
    	die('<span style="color:red">You do not have permissions to update your own Wiki_Note for CoreFlow. Please contact authors.</span>'); ### add here .mysql_error() if you need to debug
    }  	
  }
  
  
  
  
  ## read the content  of the table MB_CORE_FLOW_WIKI_NOTE if exists if note then create one and populate
	$query="select Wiki_Note_Install from $tableName limit 1";
	$result = mysql_query($query);
	if ($result){
    if(mysql_num_rows($result)<=0){
					$query="insert into $tableName (Wiki_Note_Install) values('".$initialWikiNote."')";
					$result = mysql_query($query);
					$wikiNote=$initialWikiNote;
					### continue with the initial notes
    } else {
		  $row = mysql_fetch_array($result,MYSQL_ASSOC);
		  $wikiNote=trim($row{'Wiki_Note_Install'});
		  if($wikiNote==""){$wikiNote=$initialWikiNote;}
    }
	} else {
		# probably the table does not exist. Create one
		 if(preg_match("/doesn't.*exist/i",mysql_error())){
				$query="create table $tableName (Wiki_Note mediumtext,Wiki_Note_Install mediumtext)";
				$result = mysql_query($query);
				if ($result){
					$query="insert into $tableName (Wiki_Note_Install) values('".$initialWikiNote."')";
					$result = mysql_query($query);
					$wikiNote=$initialWikiNote;
	      }else{
	      	die('<span style="color:red">You do not have permissions to create your own Wiki_Note for the public CoreFlow version! Please contact authors.</span>!'); ### add here .mysql_error() if you need to debug
	      }
		 };
	}
  
  
  


?>

	<html>
		<head>		
			<title>Install CoreFlow wiki-notes</title>
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
							<textarea id="textarea" wrap="soft" rows="140" name="Wiki_Note" class="editor"><?php print $wikiNote ?></textarea>
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
