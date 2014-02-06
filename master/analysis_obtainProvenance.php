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

  ### get all R_scripts that have the same Thread as the one related to $firstCustomAnalysisId and contain an API call !!!
  $targetCustomAnalysisIdList =$firstCustomAnalysisId;
	$query="select distinct Custom_Analysis_Id,Thread_Step_Number,R_script ,Project_Owner,Project,Thread_Name,Analysis_Name
	         from MB_CUSTOM_ANALYSIS where 
	         (Project_Owner,Project,Thread_Name) in (select Project_Owner,Project,Thread_Name from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id in ($targetCustomAnalysisIdList))
	         and (R_script like '%http://%/CoreFlow%api_runDBscript%Custom_Analysis_Id=%'
	             or R_script like  '%http://%/CoreFlow%api_getAttachment.pl?Custom_Analysis_Result_Id=%'
	             or R_script like  '%http://%/CoreFlow%api_getRscript.php?Custom_Analysis_Id=%'
	             )
	         order by Thread_Step_Number ";
   
	$result = mysql_query($query);

  $debug="";
  $all_match_list=array();			
	if ($result){
		$i=0;		
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$allCustomAnalysisId[$i]=$row{'Custom_Analysis_Id'};
			$allStepNmb[$i]         =$row{'Thread_Step_Number'};

			$allOwner[$i]           =$row{'Project_Owner'};
			$allProject[$i]         =$row{'Project'};
			$allThread[$i]          =$row{'Thread_Name'};
			$allAnalysisName[$i]    =$row{'Analysis_Name'};

			$crtScript              =$row{'R_script'};

      #extend $targetCustomAnalysisIdList with new Customer Analysis Id theat are not there based on parsing R_script
      $my_match_list='';
      $my_match_attachment_list='';
      preg_match_all('/http:.+?CoreFlow.+api_runDBscript.php.Custom_Analysis_Id=(\d+)/',$crtScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m<count($my_matches); $m++) {
      	if($my_match_list !=''){ $my_match_list .=",";}
      	$my_match_list .= $my_matches[$m][1]; $all_match_list[$my_matches[$m][1]]++;
      } ;
      #same thing for gerRscript 
      preg_match_all('/http:.+?CoreFlow.+api_getRscript.php.Custom_Analysis_Id=(\d+)/',$crtScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m<count($my_matches); $m++) {
      	if($my_match_list !=''){ $my_match_list .=",";}
      	$my_match_list .= $my_matches[$m][1]; $all_match_list[$my_matches[$m][1]]++;
      } ;

      ### the nex one looks at attachments (so we have to obtain Custom_ANalysis_Id from Custom_Analysis_Result_Id
      preg_match_all('/http:.+?CoreFlow.+api_getAttachment.pl.Custom_Analysis_Result_Id=(\d+)/',$crtScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m<count($my_matches); $m++) {
      	if($my_match_attachment_list !=''){ $my_match_attachment_list .=",";}
      	$my_match_attachment_list .= $my_matches[$m][1];
      } ;
      
			$debug .="<tr>
			<td>$allCustomAnalysisId[$i]</td> 
			<td>$allStepNmb[$i]</td>
			 
			<td>$allOwner[$i]</td>
			<td>$allProject[$i]</td>
			<td>$allThread[$i]</td>
			<td>".$my_match_list."</td>
			<td>".$my_match_attachment_list."</td>
			         </tr>";
			         
			$allProvenance[$i]=$my_match_list;
			$allProvenanceAttachment[$i]=$my_match_attachment_list;
			         
			$i++;
		}
		if($i==0) {
			print('<!--
			'.$query.'
			-->');
			die("No provenance info in the R,perl,python scripts for this Thread !");
		}
	} else {
		die("<pre style='color:red'>".mysql_error()."</pre>\n");
	}
  
  ### we must transform the eventual Custom_Analysis_Result_Id into Custom_Analysis_Id
  for($s=0; $s<count($allProvenanceAttachment); $s++){
  	if($allProvenanceAttachment[$s] !=""){
	    $query="select group_concat(distinct Custom_Analysis_Id) as Custom_Analysis_Id from MB_CUSTOM_ANALYSIS_RESULT where Custom_Analysis_Result_Id in ($allProvenanceAttachment[$s]) ";
	    $result = mysql_query($query);
	    if ($result && mysql_num_rows($result)>0){
	      $row = mysql_fetch_array($result,MYSQL_ASSOC);
	    } else { die (" Missing Custom_Analysis_Id for Custom_Analysis_Result_Id: ".$allProvenanceAttachment[$s]); }
	    if($allProvenance[$s] !='')	{$allProvenance[$s] .=",";}
	    $allProvenance[$s] .= $row{'Custom_Analysis_Id'};	
	    $all_match_list[$row{'Custom_Analysis_Id'}]++;
  	}
  }
  
  $firstOwner    =$allOwner[0];
  $firstProject  =$allProject[0];
  $firstThread   =$allThread[0];
  
    
  
  
#  print join(';',array_keys($all_match_list))."<br>";
#  print join('-',array_diff(array_keys($all_match_list),$allCustomAnalysisId))."<br>";
  
  $iter=1;
  $targetCustomResultIdList="";  # initally all Custom_Analysis_Resu;t_Id have been resolved (see $query above)
  while(count(array_diff(array_keys($all_match_list),$allCustomAnalysisId))>0 || $targetCustomResultIdList !=""){ 
  	### keep obtaining matched APIs as long as they are not in the previous list

    $targetCustomAnalysisIdList =join(',',array_diff(array_keys($all_match_list),$allCustomAnalysisId));
    if($targetCustomResultIdList !=''){
    	$queryAttach="or (Custom_Analysis_Id in (select Custom_Analysis_Id from MB_CUSTOM_ANALYSIS_RESULT where Custom_Analysis_Result_Id in ($targetCustomResultIdList) ) )";
    } else {
    	$queryAttach="";
    }
	  $query="select distinct Custom_Analysis_Id,Thread_Step_Number,R_script ,Project_Owner,Project,Thread_Name,Analysis_Name
	         from MB_CUSTOM_ANALYSIS where 
	             (Custom_Analysis_Id in ($targetCustomAnalysisIdList)) $queryAttach
	          order by Thread_Step_Number";
	  $result = mysql_query($query);
	  $targetCustomResultIdList="";
	  if ($result && mysql_num_rows($result)>0){
	   # we keep the $i from previous
		 while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$allCustomAnalysisId[$i]=$row{'Custom_Analysis_Id'};
			$allStepNmb[$i]         =$row{'Thread_Step_Number'};

			$allOwner[$i]           =$row{'Project_Owner'};
			$allProject[$i]         =$row{'Project'};
			$allThread[$i]          =$row{'Thread_Name'};
			$allAnalysisName[$i]    =$row{'Analysis_Name'};

			$crtScript              =$row{'R_script'};
			
      #extend $targetCustomAnalysisIdList with new Customer Analysis Id theat are not there based on parsing R_script
      $my_match_list='';
      $my_match_attachment_list='';
      preg_match_all('/http:.+?CoreFlow.api_runDBscript.php.Custom_Analysis_Id=(\d+)/',$crtScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m<count($my_matches); $m++) {
      	if($my_match_list !=''){ $my_match_list .=",";}
      	$my_match_list .= $my_matches[$m][1]; $all_match_list[$my_matches[$m][1]]++;
      } ;
      preg_match_all('/http:.+?CoreFlow.api_getAttachment.pl.Custom_Analysis_Result_Id=(\d+)/',$crtScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m<count($my_matches); $m++) {
      	if($my_match_attachment_list !=''){ $my_match_attachment_list .=",";}
      	$my_match_attachment_list .= $my_matches[$m][1];
      	if($targetCustomResultIdList !="") { $targetCustomResultIdList .=",";}
      	$targetCustomResultIdList .= $my_matches[$m][1]; 
      } ;

			$debug .="<tr>
			<td>$allCustomAnalysisId[$i]</td> 
			<td>$allStepNmb[$i]</td>
			 
			<td>$allOwner[$i]</td>
			<td>$allProject[$i]</td>
			<td>$allThread[$i]</td>
			<td>".$my_match_list."</td>
			<td>".$my_match_attachment_list."</td>
			         </tr>";

			$allProvenance[$i]=$my_match_list;
			$allProvenanceAttachment[$i]=$my_match_attachment_list;
       
			$i++;
		 }	  	
    } else { break;} # there is no additional info to gather
#    print "<br>\n";
#    print join(';',array_keys($all_match_list))."<br>\n";
#    print join('-',array_diff(array_keys($all_match_list),$allCustomAnalysisId))."<br>\n";
    $iter++;
    if($iter>15){break;}; # I think max 15 iterations (on different Threads and possiby projects is enough)
  }
     
	mysql_close($dbh);

  $debug = '<table>'.$debug.'</table>';

	############################################################################################################

  #print $debug;
	
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
					.worldClass {
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
       	Data exchange (provenance) between tasks belonging to:<br>
       	               <img height="24" style="vertical-align:bottom" src="images/owner_plus_small.png"><?php print $firstOwner ?> 
       	  &nbsp;&nbsp; <img height="24" style="vertical-align:bottom" src="images/project_minus_small.png"><?php print $firstProject ?> 
       	  &nbsp;&nbsp; <img height="24" style="vertical-align:bottom" src="images/thread_minus_small.png"><?php print $firstThread ?>
       	</span>
<!-- next comments are used as delimiters by analysis_integratedDoc.php-->       	
<!-- Provenance -->      	
<div id="world_<?php print $firstCustomAnalysisId; ?>" class="worldClass"></div>
<script type="text/javascript" charset="utf-8">
var fsa = Joint.dia.fsa;
<?php
print '
wrld_w=800; wrld_h=680; wrld_R=Math.round(Math.min(wrld_w,wrld_h)/2*0.8);
Joint.paper("world_'.$firstCustomAnalysisId.'", wrld_w, wrld_h);
shapes = [];
';

      $nmbNodes=count($allCustomAnalysisId);
      for($s=0; $s < $nmbNodes; $s++){
      	$x_center=round(wrld_w/2+wrld_R*cos(deg2rad(-90+$s*360/$nmbNodes))); $crtCos=round(cos(deg2rad(-90+$s*360/$nmbNodes)),4);
      	$y_center=round(wrld_h/2+wrld_R*sin(deg2rad(-90+$s*360/$nmbNodes))); $crtSin=round(sin(deg2rad(-90+$s*360/$nmbNodes)),4);
      	print "x_c=Math.round(wrld_w/2+wrld_R*$crtCos); \n";
      	print "y_c=Math.round(wrld_h/2+wrld_R*$crtSin); \n";
      	print 'shapes.push(fsa.State.create({ position:{x: x_c, y: y_c },label: "'.$allStepNmb[$s].'"}));'." \n";


      	if(($allOwner[$s]==$firstOwner) && ($allProject[$s]==$firstProject) && ($allThread[$s]==$firstThread) ) {
      		# blue for the same thread
      	  print 'shapes['.$s.'].attr({stroke:"#00f", title:"'.$allCustomAnalysisId[$s].'-'.str_replace('"','`',str_replace("'","`",substr($allAnalysisName[$s],0,30))).'..."});'."\n";
      	} else {
      		# red for a diferent thread, project or owner
      		$extraInfo="";
      		if(($allOwner[$s]  !=$firstOwner))  { $extraInfo.= $allOwner[$s]." ";}
      		if(($allProject[$s]!=$firstProject)){ $extraInfo.= $allProject[$s]." ";}
      		if(($allThread[$s] !=$firstThread)) { $extraInfo.= $allThread[$s]." ";}
      	  print 'shapes['.$s.'].attr({stroke:"#f00", title:"'.$allCustomAnalysisId[$s].' '.$extraInfo.'"});'."\n";
      	}
      


      }

      for($s=0; $s<count($allCustomAnalysisId); $s++){
      	if($allProvenance[$s]!=''){
      	  $linkedCustAnalysisIds=explode(",",$allProvenance[$s]);
      	  foreach($linkedCustAnalysisIds as $j=> $id){
      	 	  for($sLink=0; $sLink< $nmbNodes; $sLink++){ if($allCustomAnalysisId[$sLink]==$id) {break;}}
            print 'shapes['.$sLink.'].joint(shapes['.$s.'], (fsa.arrow)).register(shapes);'."\n";
          }
        }
      }

    ?>
</script>
<!-- -->
        <p id="copy">Based on <a href="http://raphaeljs.com/" style="color:blue">Raphael</a> JavaScript Vector Library
        	<br> And <a href="http://www.jointjs.com"  style="color:blue">JointJS</a> diagramming Library
        </p>



    </body>
</html>
