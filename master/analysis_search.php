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
  setDefaultDB($selectedDB,$dbh);

	$qeryError;
	# get Work tables

//  foreach($_REQUEST as $e => $v){
//  	print $e." : ".$_REQUEST[$e]."<br>";
//  	if(is_array($e)){
//  		print "counts: count($e) <br>";
//  	}
//  }

	$keywords	     =trim($_REQUEST['keywords']);
  $search_scope  =trim($_REQUEST['search_scope']);
  $scope_name    =trim($_REQUEST['scope_name']);
  $scope_main_id =trim($_REQUEST['scope_main_id']);
 
	$expandedElements=$_REQUEST['aSelectControl']; # keeps here all the elements expanded by user
 
 
 
  $expandedHTML="";
  foreach ($expandedElements as $selectedOption){
    $expandedHTML .= '<option value="'.$selectedOption.'" selected="selected">'.$selectedOption.'</option>';
  }
  
	$sqlCondition	= $search_scope.' like "'.stripslashes($scope_name).'"';

  $sqlDelimiter=";"  ; # in case we have a complex query
	$query='
select Custom_Analysis_Id
from MB_CUSTOM_ANALYSIS
where ((Db_Script like "'.$keywords.'") or (R_Script like "'.$keywords.'")) 
and '.$sqlCondition;

  
	# simply ignore php warnings etc.
	#set_error_handler('error_handler');
	#function error_handler() {
	#}


		# execute temporary queries if any
		$queryItems=explode($sqlDelimiter,$query);
		$lastQueryId=sizeof($queryItems)-1;
		for ($k=0; $k<=($lastQueryId-1); $k++){
			$querySmall=$queryItems[$k];
			$result = mysql_query($querySmall);
			if (! $result){
				$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
				break;
			}
		}

		$querySmall=$queryItems[$lastQueryId];
		$result = mysql_query($querySmall);
		$foundIdsHTML="";		
		if ($result){
			while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
				if($foundIds !="") {$foundIds .= ",";}
				$foundIdsHTML .='<option value="'.$row{'Custom_Analysis_Id'}.'" selected="selected">'.$row{'Custom_Analysis_Id'}.'</option>';
			}
		} else {
			$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
		}


	mysql_close($dbh);

?>

<html>
	<head>
		<title>Search in analysis flows</title>
		<style> 
			.invisible {
			              display:none; visibility:hidden **/
			           } 
	  </style>
  </head>
	<body>
	<form name="customAnalysisForm" method="post" action="analysis.php" style="display:inline" >
		<textarea class="invisible" name="keywords"><?php print $keywords ?></textarea>
		<select   class="invisible" multiple="multiple" name="aSelectControl[]" id="aSelectControl">
	      	<?php print $expandedHTML ?>  
		</select>
		<select  class="invisible" multiple="multiple" name="aSearchResults[]" id="aSearchResults">
			    <?php print $foundIdsHTML ?>
		</select>
		<input  class="invisible" type="submit" value="submit"/>
	</form>
	<script>
		document.customAnalysisForm.submit();
	</script>	
	</body>
</html>	
