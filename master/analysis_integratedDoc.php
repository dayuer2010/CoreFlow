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

	$rScript	=trim($_REQUEST['rScript']); # this is the first Custom_Analysis_Id in the desired Thread
  if($rScript == "") die("missing Markdown script");
  $parentPrimaryKey = trim($_REQUEST['parentPrimaryKey']);
  if($parentPrimaryKey == "") die("missing parentPrimaryKey");
  $runR_action = trim($_REQUEST['runR_action']);
  
  ### will use same app server but a different action
  $runRmarkdown_action=str_replace('runR.pl','runRmarkdown.pl',$runR_action);
  
  $Rformat = trim($_REQUEST['__Rformat']);
  $selfUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."/../";  


  ### execute the following script on the app server
  
  #library(knitr)
  #library(markdown)
  #pat_md()
  #knit('~/temp/test_knitr_v02.Rmd',tangle =FALSE)
  #markdownToHTML('/Users/administrator/test_knitr_v02.md', "~/temp/test_knitr_v02.html")

  $myvars = 'markDown=Yes&parentPrimaryKey=' . $parentPrimaryKey . '&rScript=' . urlencode($rScript);

  $ch = curl_init( $runRmarkdown_action );
   curl_setopt( $ch, CURLOPT_POST, 1);
   curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
   curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
   curl_setopt( $ch, CURLOPT_HEADER, 0);
   curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

  ### should use pcntl_fork to keep the connection alive
  print '     ';
  $response = curl_exec( $ch );

  $rScriptEmbeded=$response;
  #### parse the $rScript (markdown for keywords like [Wiki some_id]
      preg_match_all('/\[Wiki\s+(\d+)\]/',$rScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m < count($my_matches); $m++) {
        $allWikiHtml="";
      	$crtCustAnalysisId=$my_matches[$m][1];

        # obtain task description from database
				$query="select Description,Thread_Step_Number from ".$selectedDB.".MB_CUSTOM_ANALYSIS  where Custom_Analysis_Id=$crtCustAnalysisId ";
				$resultSQL = mysql_query($query);
				if ($resultSQL){
					$row = mysql_fetch_array($resultSQL,MYSQL_ASSOC);
					$parentContent=$row{'Description'};
					$Thread_Step_Number=$row{'Thread_Step_Number'};
			  }
 
        # create a div and the script
        if(trim($parentContent) !=''){
          if($m==0){
          	$allWikiHtml .= '<script src="wikiwym-read-only/lib/GoogleCodeWikiParser.js" type="text/javascript"></script>
<script type="text/javascript">gcwp = new GoogleCodeWikiParser();</script>
';
          }
	        $allWikiHtml .='<div name="wiki_'.$crtCustAnalysisId.'" id="wiki_'.$crtCustAnalysisId.'" style="background-color:#FDEADA; padding: 5px; border: 3px solid gray; border-radius: 15px;" >'.$parentContent.'></div>
<script type="text/javascript">
lyr_wiki=document.getElementById("wiki_'.$crtCustAnalysisId.'");
if(lyr_wiki){
content_wiki=lyr_wiki.innerHTML;
content_nice=gcwp.parse(content_wiki);
lyr_wiki.innerHTML = content_nice;
}
</script>
';
         	 $rScriptEmbeded=str_replace($my_matches[$m][0],$my_matches[$m][0].$allWikiHtml, $rScriptEmbeded);
	      }

      } ;



  #### parse the $rScript (markdown for keywords like [Provenance some_id]
      preg_match_all('/\[Provenance\s+(\d+)\]/',$rScript,$my_matches,PREG_SET_ORDER);
      for($m=0; $m < count($my_matches); $m++) {
        $allProvenanceHtml="";
      	$crtCustAnalysisId=$my_matches[$m][1];

        #### call the Data Provenance script
			  $myvars = 'Custom_Analysis_Id=' . $crtCustAnalysisId ;			
			  $ch = curl_init( $selfUrl."analysis_obtainProvenance.php" );
			   curl_setopt( $ch, CURLOPT_POST, 1);
			   curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
			   curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
			   curl_setopt( $ch, CURLOPT_HEADER, 0);
			   curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
         $response = curl_exec( $ch );
         #parse response only for someting between <!-- Provenance -->  and <!-- --> 
         preg_match_all('@(<div id="world_.+?</script>)@um',str_replace("\n","",$response),$my_provMatches,PREG_SET_ORDER);
         if(count($my_provMatches)>=1){ # we have one Provenance (not more) otherwise we can have an error in the response (eg no Provenance)
         	 if($m==0){ ## add the headers to insert javascripts
         	 	 $allProvenanceHtml .='<script src="'.$selfUrl.'js/raphael.js" type="text/javascript"></script>
<script src="'.$selfUrl.'js/joint.js" type="text/javascript"></script>
<script src="'.$selfUrl.'js/joint.dia.js" type="text/javascript"></script>
<script src="'.$selfUrl.'js/joint.dia.fsa.js" type="text/javascript"></script>
<link rel="stylesheet" href="'.$selfUrl.'js/demo.css" type="text/css" media="screen">
<link rel="stylesheet" href="'.$selfUrl.'js/demo-print.css" type="text/css" media="print">
<style type="text/css">
.worldClass {
background-color: white;
float left;
width: 400px;
height: 340px;
border: 3px solid gray;
border-radius: 15px;
}
</style>
';
         	 }
         	 $allProvenanceHtml .= str_replace('wrld_w=800; wrld_h=680;','wrld_w=400; wrld_h=340;',$my_provMatches[0][1]); # adjust size of diagram
         	 $rScriptEmbeded=str_replace($my_matches[$m][0],$my_matches[$m][0].$allProvenanceHtml, $rScriptEmbeded);
         }
         
      } ;


  print($rScriptEmbeded);

  exit;


	
?>

