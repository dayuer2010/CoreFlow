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

	$firstCustomAnalysisId	=trim($_REQUEST['Custom_Analysis_Id']); # this is the first Custom_Analysis_Id in the desired Thread
  if($firstCustomAnalysisId == "") die("Missing Custom_Analysis_Id !");

  ##get all tables form all visible databases
	$query="show databases";   
	$result = mysql_query($query);
	$allDatabases=array();			
	if ($result){
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			array_push($allDatabases,$row{'Database'});
    }
  }
  ## get all tables
	$allTables=array(); $allDbTables=array();			
  foreach ($allDatabases as $db) {
  	if($db == 'information_schema') {continue;}
  	setDefaultDB($db ,$dbh);
	  $query="show tables";   
	  $result = mysql_query($query);
	  if ($result){
		  while ($row = mysql_fetch_array($result)) {
			  array_push($allTables  ,        $row[0]);
			  array_push($allDbTables,$db.'.'.$row[0]);
      }
    }
  }

  ### get all non empty DB_scripts that have the same Thread as the one related to $firstCustomAnalysisId  !!!
	setDefaultDB($selectedDB ,$dbh);
  $targetCustomAnalysisIdList =$firstCustomAnalysisId;
	$query="select distinct Custom_Analysis_Id,Thread_Step_Number,DB_script ,Project_Owner,Project,Thread_Name,Analysis_Name,Sql_delimiter
	         from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id in ($targetCustomAnalysisIdList)
	         and DB_Script is not null and replace(DB_Script,' ','')!=''
	         order by Thread_Step_Number ";
   
	$result = mysql_query($query);

  $debug="";
  $all_match_list=array();			
	if ($result){
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$allQueryTables[$i]=$row{'Custom_Analysis_Id'};
			$allStepNmb[$i]         =$row{'Thread_Step_Number'};

			$allOwner[$i]           =$row{'Project_Owner'};
			$allProject[$i]         =$row{'Project'};
			$allThread[$i]          =$row{'Thread_Name'};
			$allAnalysisName[$i]    =$row{'Analysis_Name'};

			$crtScript              =$row{'DB_script'};
			$sqlDelimiter           =$row{'Sql_delimiter'};


      #split the DB_script by sqlDelimiter and extract table names
      $allQueryTables=extractTables($crtScript,$sqlDelimiter,$allTables,$allDbTables);
      
			         
			$allProvenance[$i]=$my_match_list;
			$allProvenanceAttachment[$i]=$my_match_attachment_list;
			         
			$i++;
		}
		if($i==0) {die("No Database Table relationships info in the DB scripts for this Thread !");}
	} else {
		die("<pre style='color:red'>".mysql_error()."</pre>\n");
	}
    
  $firstOwner    =$allOwner[0];
  $firstProject  =$allProject[0];
  $firstThread   =$allThread[0];
  
    
     
	mysql_close($dbh);

  $debug = '<table>'.$debug.'</table>';

	############################################################################################################

  #print $debug;

 function extractTables($query,$sqlDelimiter,$allTables,$allDbTables){
	# due to the fact that we have magic_quotes_gpc=On in php.ini ; normally we should use stripslashes
	$query=stripslashes($query);

	# get rid of last ; if any
	if (substr($query,strlen($query)-1) == $sqlDelimiter){
		$query = substr($query,0,strlen($query)-1);
	}

   
	if ($query ==""){return '';}
  $allQueryTables=array();
  
		$queryItems=explode($sqlDelimiter,$query);
		$lastQueryId=sizeof($queryItems)-1;
		for ($k=0; $k<=$lastQueryId; $k++){
			$querySmall=$queryItems[$k];
			# get rid of comments 
			#$querySmall_nc=preg_replace('%\/\*.*?\*\/%s','',$querySmall);
			$querySmall_nc=preg_replace("%/\*(?:(?!\*/).)*\*/%s","",$querySmall); # using negative lookahead
			$querySmall_nc=preg_replace("%--.*?\n%s","",$querySmall_nc);
			## add a space at tthe end to make parsing easier
			$querySmall_nc=$querySmall_nc . ' ';
			# now obtain table names for each query by  extracting create temporary table ...
			if(preg_match_all('/create\s+temporary\s+table(\s+([^\s]+)\s+)/i',$querySmall_nc,$matches)>0){
	    	$noTemporary=FALSE;
				foreach($matches[2] as $tempTable){ # matches[1] actually may have multiple entries (each one is the name of a temporary table)
	        array_push($allQueryTables, 'temp,'.$k.','.$tempTable);
	        #also save the temporary table name in the $allTables becaus eotheer queries might use them
	        array_push($allTables,$tempTable);
	      } 
				foreach($matches[1] as $tempTableSpaces){ # matches[2] actually may have multiple entries (each one is the name of a temporary table with spaces around)
	        ### we must also replace the $tempTable that contains the adtabase with '' because in the next block we look for same tables
	        $querySmall_nc=preg_replace('/'.$tempTableSpaces.'/s','',$querySmall_nc);
	      }
	    }else{
	    	$noTemporary=TRUE;
	    }
	    
			# now  by comparing to $allTables (or with the table prefixed with database)
			
			foreach ($allDbTables as $crtTable){
			  if(preg_match_all('/(\s+('.$crtTable.')\s+)/',$querySmall_nc,$matches)>0){
			  	if($noTemporary){ $noTemporary=FALSE; array_push($allQueryTables, 'temp,'.$k.','.'final');}
				  foreach($matches[2] as $tempTable){ # matches[1] actually may have multiple entries (each one is the name of a  table)
	          array_push($allQueryTables, 'perm,'.$k.','.$tempTable);
	        } 
				  foreach($matches[1] as $tempTableSpaces){ # matches[2] actually may have multiple entries (each one is the name of a temporary table with spaces around)
	          ### we must also replace the $tempTable that contains the adtabase with '' because in the next block we look for same tables
	          $querySmall_nc=preg_replace('/'.$tempTableSpaces.'/s','',$querySmall_nc);
	        }
	      }				
			}

			foreach ($allTables as $crtTable){
			  if(preg_match_all('/(\s+('.$crtTable.')\s+)/',$querySmall_nc,$matches)>0){
			  	if($noTemporary){ $noTemporary=FALSE; array_push($allQueryTables, 'temp,'.$k.','.'final');}
				  foreach($matches[2] as $tempTable){ # matches[1] actually may have multiple entries (each one is the name of a table)
	          array_push($allQueryTables, 'perm,'.$k.','.$tempTable);
	        } 
				  foreach($matches[1] as $tempTableSpaces){ # matches[2] actually may have multiple entries (each one is the name of a temporary table with spaces around)
	          ### we must also replace the $tempTable that contains the adtabase with '' because in the next block we look for same tables
	          $querySmall_nc=preg_replace('/'.$tempTableSpaces.'/s','',$querySmall_nc);
	        }
	      }				
			}


		}

    return $allQueryTables;
 }

	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
    <head>
        <meta http-equiv='cache-control' content='no-cache'>
        <meta http-equiv='expires' content='0'>
        <meta http-equiv='pragma' content='no-cache'>

        <script src="js/raphael.js" type="text/javascript"></script>
	      <script src="js/joint.js" type="text/javascript"></script>
        <script src="js/joint.dia.js" type="text/javascript"></script>
        <script src="js/joint.dia.fsa.js" type="text/javascript"></script>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Data provenance</title>
        <link rel="stylesheet" href="js/demo.css" type="text/css" media="screen">
        <link rel="stylesheet" href="js/demo-print.css" type="text/css" media="print">
				<style type="text/css">
					#world {
            background-color: white;
            margin: 0 auto 0 auto;
            width: 800px;
            height: 680px;
            border: 3px solid gray;
            border-radius: 30px;
          }

				</style>
    </head>
    <body style="text-align: center">
       <span style="text-align: center">
       	DB tables used for data extraction (provenance) for:<br>
       	               <img height="24" style="vertical-align:bottom" src="images/owner_plus_small.png"><?php print $firstOwner ?> 
       	  &nbsp;&nbsp; <img height="24" style="vertical-align:bottom" src="images/project_minus_small.png"><?php print $firstProject ?> 
       	  &nbsp;&nbsp; <img height="24" style="vertical-align:bottom" src="images/thread_minus_small.png"><?php print $firstThread ?>
       	  <!--
       	  <?php print join(' ; ',$allQueryTables) ?> 
       	  -->
       	</span>
        <div id="world"></div>
        <p id="copy">Based on <a href="http://raphaeljs.com/">Raphael</a> JavaScript Vector Library
        	<br> And <a href="http://www.jointjs.com">JointJS</a> diagramming Library
        </p>


       <script type="text/javascript" charset="utf-8">
         var fsa = Joint.dia.fsa;
         Joint.paper("world", 800, 680);

        shapes = [];
    <?php
      $nmbNodes=count($allQueryTables); # this contains all table in all sub queries
      
      $distinctSubQuery=array(); # contains for each distinct subquery numbers the list of table ides (0,1... associated)
      for($s=0; $s < $nmbNodes; $s++){
      	# obtain the  the type of table (temp.orary) or perm.anent),subquery number and the table name 
      	$tblAttributes=explode(',',$allQueryTables[$s]);
      	$tblType=$tblAttributes[0]; $subQueryNmb=$tblAttributes[1]; $crtTableName=$tblAttributes[2];
      	
      	$x_center=round(400+280*cos(deg2rad(-90+$s*360/$nmbNodes)));
      	$y_center=round(340+280*sin(deg2rad(-90+$s*360/$nmbNodes)));
      	print 'shapes.push(fsa.State.create({ position:{x:'.$x_center.', y:'.$y_center.'},label: "'.$crtTableName.'"}));'." \n";

        if(! array_key_exists($subQueryNmb,$distinctSubQuery) ) { 
        	 $distinctSubQuery[$subQueryNmb]=$s;
        } else {
        	 $distinctSubQuery[$subQueryNmb]=$distinctSubQuery[$subQueryNmb].','.$s;
        }
      	if( $tblType =='perm') {
      		# blue for the permanent tables
      	  print 'shapes['.$s.'].attr({stroke:"#00f", title:"'.$crtTableName.'"});'."\n";
      	} else {
      		# red for a diferent thread, project or owner
      	  print 'shapes['.$s.'].attr({stroke:"#f00", title:"'.$crtTableName.'"});'."\n";
      	}     

      }

      foreach($distinctSubQuery as $s => $idList){
      	  $linkedTableIds=explode(",",$idList);
      	  foreach($linkedTableIds as $id){
            print 'shapes['.$id.'].joint(shapes['.$linkedTableIds[0].'], (fsa.arrow)).register(shapes);'."\n";
          }
      }

    ?>

    </script>

    </body>
</html>
