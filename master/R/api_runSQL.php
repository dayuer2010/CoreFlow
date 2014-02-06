<?php

  include_once('common_connect.php'); checkViewAccess();
	$dbh = connectToDB();
	$selectedDB=getDefaultDB();
	setDefaultDB($selectedDB ,$dbh);


	$qeryError;

	$query=trim($_REQUEST['query']);

	$sqlDelimiter	=trim($_REQUEST['Sql_delimiter']);
	if ($sqlDelimiter == "") {$sqlDelimiter=";";};


	# due to the fact that we have magic_quotes_gpc=On in php.ini ; normally we should use stripslashes
	$query=stripslashes($query);
	#$query		=str_replace('\\', "", $query);	# the stupid php converts quotes in backslash quotes


	# get rid of last ; if any
	if (substr($query,strlen($query)-1) == ";"){
		$query = substr($query,0,strlen($query)-1);
	}


	# simply ignore php warnings etc.
	set_error_handler('error_handler');
	function error_handler() {
	}


	if ($query != ""){
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
		$delimitedResult='';
		{
			$result = mysql_query($querySmall);
			$num	= mysql_numrows($result);
		
			if ($result){
				$j=0; 
		    $delimitedRow='';
				while (mysql_field_name($result, $j)){
					$name=mysql_field_name($result, $j);
					$colNames[$j]=$name;					
					if(	$j>0) {$delimitedRow .= "\t";}
		      $delimitedRow .= $name;
					$j++;
				}
				#$delimitedResult .= $delimitedRow."\n";
	      print $delimitedRow."\n"; ## better force some data
	
				$i=0;		
				while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
					$delimitedRow='';
					$j=0;
					foreach ( $colNames as $cIndex => $cVal ){
						if(	$j>0) {$delimitedRow .= "\t";}
						$delimitedRow .= $row{$cVal};
						$j++;
					}
					#$delimitedResult .= $delimitedRow."\n";
					print $delimitedRow."\n"; ## better force some data
					$i++;
				}
			} else {
				$qeryError .="<pre style='color:red'>".mysql_error()."</pre>\n";
				#die("Could not execute! \n <pre>".mysql_error()."</pre><br/><pre> $query </pre>");
			}
		}
	} else {die ("There is no SQL query!");}


	mysql_close($dbh);

  if($qeryError != '') {print $qeryError; exit;}
  #print $delimitedResult;
  
	
?>

