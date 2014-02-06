<?php header("Content-Type: text/plain");

  include_once('common_connect.php'); checkViewAccess();
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
	setDefaultDB($selectedDB ,$dbh);

	$qeryError;

	$customAnalysisId=trim($_REQUEST['Custom_Analysis_Id']);

  if($customAnalysisId !=''){
		$result = mysql_query("select R_script from MB_CUSTOM_ANALYSIS where Custom_Analysis_Id=$customAnalysisId");
		if (! $result){
				$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
				print $qeryError;
				exit;
		}
  	$row = mysql_fetch_array($result,MYSQL_ASSOC);
  	print $row{'R_script'};
  }else{ die ("<pre style='color:red'>Missing Custom_Analysis_Id</pre>\n");}   



	# simply ignore php warnings etc.
	set_error_handler('error_handler');
	function error_handler() {
	}

	mysql_close($dbh);
  
	
?>

