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

  $search_scope  =trim($_REQUEST['search_scope']); # this is the Owner or Project or Thread
  $scope_name    =trim($_REQUEST['scope_name']);  # this is the new name to be changed to
  $scope_main_id =trim($_REQUEST['scope_main_id']); # this keeps the old name
 
	$expandedElements=$_REQUEST['aSelectControl']; # keeps here all the elements expanded by user
 
 
 
  $expandedHTML="";
  foreach ($expandedElements as $selectedOption){
    $expandedHTML .= '<option value="'.$selectedOption.'" selected="selected">'.$selectedOption.'</option>';
  }
  
	$newName	= str_replace('"','',stripslashes($scope_name)); # remove " 

  $sqlDelimiter=";"  ; # in case we have a complex query
  if($search_scope=='Project_Owner'){
	  $query='set @o=(select `Project_Owner` from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id='.$scope_main_id.');
update MB_CUSTOM_ANALYSIS set `Project_Owner`="'.$newName.'"
where  `Project_Owner`=@o';
  } else {
  	if($search_scope=='Project'){
  	  $query='set @o=(select `Project_Owner` from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id='.$scope_main_id.');
  	          set @p=(select `Project`       from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id='.$scope_main_id.');
update MB_CUSTOM_ANALYSIS set `Project`="'.$newName.'"
where  `Project_Owner`=@o and `Project`=@p ';
  	} else {
    	if($search_scope=='Thread_Name'){
    	  $query='set @o=(select `Project_Owner` from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id='.$scope_main_id.');
  	            set @p=(select `Project`       from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id='.$scope_main_id.');
  	            set @t=(select `Thread_Name`   from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id='.$scope_main_id.');
update MB_CUSTOM_ANALYSIS set `Thread_Name`="'.$newName.'"
where  `Project_Owner`=@o and `Project`=@p and `Thread_Name`=@t';
  	  } else {
  	  	print("ERROR! Unexpected element: $search_scope");
  	  	exit;
  	  }

  	}
  }
  
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
		if (!$result){
			$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
		}


	mysql_close($dbh);

?>

<html>
	<head>
		<title>Rename in analysis flows</title>
		<style> 
			.invisible {
			              display:none; visibility:hidden **/
			           } 
	  </style>
  </head>
	<body>
	<form name="customAnalysisForm" method="post" action="analysis.php" style="display:inline" >
		<select   class="invisible" multiple="multiple" name="aSelectControl[]" id="aSelectControl">
	      	<?php print $expandedHTML ?>  
		</select>
		<input  class="invisible" type="submit" value="submit"/>
	</form>
	<script>
		document.customAnalysisForm.submit();
	</script>	
	</body>
</html>	
