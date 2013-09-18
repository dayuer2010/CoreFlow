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
  $runRmarkdown_action=preg_replace('/runR.pl/','runRmarkdown.pl',$runR_action);
  
  $Rformat = trim($_REQUEST['__Rformat']);
    

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

  $response = curl_exec( $ch );
  print($response);

  exit;


	
?>

