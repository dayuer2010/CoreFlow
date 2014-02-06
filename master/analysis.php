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

header('Content-Type: text/xml');
  	echo '<!-- ?xml version="1.0" encoding="UTF-8" standalone="no"?  -->'."\n";
    echo '<?xml-stylesheet type="text/xsl" href="analysis.xsl"?>'."\n"; 

	#  2008 May 20 - generate XML for MB_CUSTOM_ANALYSIS

	include_once("common_connect.php"); checkViewAccess();
	$organization=str_replace('CoreFlow','',getAppName());
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
  mysql_select_db($selectedDB,$dbh) or die("Could not select database $selectedDB");

	$tableName='MB_CUSTOM_ANALYSIS';

	$expandedElements        =$_POST['aSelectControl']; # keeps here all the elements expanded by user
	$expandedOriginalElements=$_POST['aSelectOriginalControl']; # keeps here all the elements expanded by user (a copy)
	$expandAllFlag           =$_REQUEST['expand_all_flag'];
	
	$foundElements   =$_POST['aSearchResults']; # keeps here all the elements searched by user and found
	$keywords	       =trim($_POST['keywords']);


	# get a limited content of the table
	$queryLimit=3000;
	#  
	$query="select distinct
   Project_Owner
  ,Project
  ,Thread_Name
  ,Thread_Step_Number
    ,ma.Custom_Analysis_Id
    ,replace(replace(Analysis_Name,'<',' less '),'>',' greater ') as Analysis_Name
    ,Description	
    ,ifnull(Step_Importance,'null') as Step_Importance

    ,concat(mi.Web_Path,mi.File_Name) as Icon_File
    ,mi.Comments as Icon_Comments

    ,mar.Result_Type
    ,mar.Custom_Analysis_Result_Id
    ,mar.Result_Description

    ,if(Db_script is null or trim(Db_script)='','Empty','Not Empty') as Empty_DB_Script	
    ,if(R_script is null or trim(R_script)='','Empty','Not Empty') as Empty_R_Script	


from MB_CUSTOM_ANALYSIS ma
 left join MB_ICON mi on mi.Icon_Id=ma.Icon_Id
 left join MB_CUSTOM_ANALYSIS_RESULT mar on mar.Custom_Analysis_Id=ma.Custom_Analysis_Id 
order by 
   Project_Owner
  ,Project
  ,Thread_Name
  ,Thread_Step_Number 
    ,mar.Result_Description
	";

	$result = mysql_query($query);
	$xml;
	if ($result){

		$xml="";
		$prevOwner="";
		$prevProject="";
		$prevTrhread="";
		$prevMainId="";
		
		$expandedAllElements=array();
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$projectOwner	=trim(normalize($row{'Project_Owner'}));
			$project	=trim(normalize($row{'Project'}));
			$thread		=trim(normalize($row{'Thread_Name'}));
			$threadStep	=trim(normalize($row{'Thread_Step_Number'}));			
			$mainId		=trim(normalize($row{'Custom_Analysis_Id'}));
			$analysisName	=trim(normalize($row{'Analysis_Name'}));
			$description	=trim(normalize($row{'Description'}));
			$stepImportance	=trim(normalize($row{'Step_Importance'}));
			$iconFile	=trim(normalize($row{'Icon_File'}));
			$iconComments	=trim(normalize($row{'Icon_Comments'}));

			$resultType	=trim(normalize($row{'Result_Type'}));
			$customAnalysisResultId=trim(normalize($row{'Custom_Analysis_Result_Id'}));
			$resultDescription=trim(normalize($row{'Result_Description'}));

      $emptyDBscript =trim(normalize($row{'Empty_DB_Script'}));
      $emptyRscript  =trim(normalize($row{'Empty_R_Script'}));


			$iconFileAttribute='';
			if ($iconFile !=""){
				$iconFileAttribute='iconFile="'.$iconFile.'" iconComments="'.$iconComments.'"';
			}

			
			if($projectOwner == ""){$projectOwner="undefined";}
			if($project == "")     {$project     ="undefined";}
			if($thread == "")      {$thread      ="undefined";}
			if($mainId == "")      {$mainId      ="undefined";}
			
			if ($projectOwner != $prevOwner){
				if($prevOwner != "") $xml .="</step></thread></project></owner>";
				$xml .= '<owner name="'.$projectOwner.'" mainId="'.$mainId.'">'."\n";
				$xml .= '<project name="'.$project.'" mainId="'.$mainId.'">'."\n";
				$xml .= '  <thread name="'.$thread.'" mainId="'.$mainId.'">'."\n";
				$xml .= '    <step name="'.$analysisName.'" threadId="'.$threadStep.'" mainId="'.$mainId.'" stepImportance="'.$stepImportance.'" '.$iconFileAttribute.'  emptyDBscript="'.$emptyDBscript.'" emptyRscript="'.$emptyRscript.'" ><step_description><![CDATA['.$description.']]></step_description>';
				array_push($expandedAllElements,'owner_'.$projectOwner.'_'.$mainId);
				array_push($expandedAllElements,'project_'.$project.'_'.$mainId);
				array_push($expandedAllElements,'thread_'.$thread.'_'.$mainId);
			}else{
				if ($project != $prevProject){
					if($prevProject != "") $xml .="</step></thread></project>";
					$xml .= '<project name="'.$project.'" mainId="'.$mainId.'">'."\n";
				  $xml .= '  <thread name="'.$thread.'" mainId="'.$mainId.'">'."\n";
				  $xml .= '    <step name="'.$analysisName.'" threadId="'.$threadStep.'" mainId="'.$mainId.'" stepImportance="'.$stepImportance.'" '.$iconFileAttribute.'  emptyDBscript="'.$emptyDBscript.'" emptyRscript="'.$emptyRscript.'" ><step_description><![CDATA['.$description.']]></step_description>';
					array_push($expandedAllElements,'project_'.$project.'_'.$mainId);
					array_push($expandedAllElements,'thread_'.$thread.'_'.$mainId);
				}else{
					if ($thread != $prevTrhread){
						if($prevTrhread != "") $xml .="</step></thread>";
						$xml .= '  <thread name="'.$thread.'" mainId="'.$mainId.'">'."\n";
				    $xml .= '    <step name="'.$analysisName.'" threadId="'.$threadStep.'" mainId="'.$mainId.'" stepImportance="'.$stepImportance.'" '.$iconFileAttribute.'  emptyDBscript="'.$emptyDBscript.'" emptyRscript="'.$emptyRscript.'" ><step_description><![CDATA['.$description.']]></step_description>';
						array_push($expandedAllElements,'thread_'.$thread.'_'.$mainId);
					}else{
						if($mainId != $prevMainId) $xml .="</step>";
				    if($mainId != $prevMainId) $xml .= '    <step name="'.$analysisName.'" threadId="'.$threadStep.'" mainId="'.$mainId.'" stepImportance="'.$stepImportance.'" '.$iconFileAttribute.'  emptyDBscript="'.$emptyDBscript.'" emptyRscript="'.$emptyRscript.'" ><step_description><![CDATA['.$description.']]></step_description>';
					}
				}
			}
			
				if ($resultType !=""){
					$xml .= '<attachment mainId="'.$mainId.'" mainResultId="'.$customAnalysisResultId.'" description="'.$resultDescription.'" resultFileType="'.$resultType.'" />'."\n";
				}

				$prevOwner     =$projectOwner;
				$prevProject   =$project;
				$prevTrhread   =$thread;
				$prevMainId    =$mainId;
				
		}
		
		if($xml !=""){
			$xml .='     </step>'."\n";
			$xml .= '  </thread>'."\n";
			$xml .= '</project>'."\n";
			$xml .= '</owner>'."\n";
    }
						
		print '<analysis source="'.$tableName.'" organization="'.$organization.'">'."\n";
		print $xml."\n";

    if($expandAllFlag==""){
			print '  <expandedSet img_src="images/expand_2.png" >'."\n";
			if ($expandedElements){
				foreach ($expandedElements as $eName){
					print '<expand name="'.$eName.'"/>'."\n";
				}
			}
			print "  </expandedSet>\n";
		}else{
			print '  <expandedSet img_src="images/collapse_2.png">'."\n";
			if ($expandedAllElements){
				foreach ($expandedAllElements as $eName){
					print '<expand name="'.$eName.'"/>'."\n";
				}
			}
			print "  </expandedSet>\n";
	  }
		
		
		
    # a copy of the expanded set when we have to expand all
    {
			print "  <expandedOriginalSet>\n";
			if ($expandedOriginalElements){
				foreach ($expandedOriginalElements as $eName){
					print '<expand name="'.$eName.'"/>'."\n";
				}
			}
			print "  </expandedOriginalSet>\n";
    }
		
		print "  <foundSet>\n";
		if ($foundElements){
			foreach ($foundElements as $fMainId){
				print '<found mainId="'.$fMainId.'"/>'."\n";
			}
		}
		print "  </foundSet>\n";
		
		if($keywords != ""){
			print '<keywords><![CDATA['.$keywords.']]></keywords>';
		}
		
		print '</analysis>'."\n";


	} else {
		die("<!-- Could not show content of the table $tableName \n $query -->");
	}
	if ($i >= ($queryLimit-1)){ $warning="<text>Showing only $queryLimit records!</text>";}
	mysql_close($dbh);


	# show everything
	#print '';

function normalize($s){
	str_replace('"','`',$s);
	str_replace("'","`",$s);
	str_replace("&","&amp;",$s);
	str_replace("<","&lt;",$s);
	str_replace(">","&gt;",$s);
	return($s);
}
		
?>

