#!/usr/bin/env perl
# Manage requests from ProteoChart 
#    (this script must run on ProteoChart_R )
# Date created: 2007 Aug 12
# Last change:  2007 Aug 12
# Last change:  2007 Aug 30 - adding the Expires: 0
#		2007 Nov 21 because of upgrading R to vers 2.6 I had to change the location of the R exe
#               2008 Aug 12 jpg and extending output to tsv (txt)
#               2008 Aug 22 for pdf increased the width from the default 6 to 10 inch
#               2008 Aug 23 keeping the connection alive (fork)
#               2009 Jan 08 changing the write table row.names=TRUE for the fcs format type for FACS
#               2009 Aug 29 adding the option to read R_Script from MS_CUSTOM_ANALUSIS table
#		2009 Nov 06 adding the df.ot.html to generate html files
#		2010 Oct 27 adding the option to save the pdf files in a compressed for in the database MS_CUSTOM_ANALYSIS_RESULT

#   2012 Sept 20 adding the running of perl and python scripts based on the first line that must be like
           ##    #!/usr/bin/env perl
           ##    #!/usr/bin/env python
#   2013 May 03 #using again the user controled __Rformat to set the pdf size

use strict;	# comment this line to speed up things
use CGI qw(-debug :standard);

$|=1;
#open(STDERR, ">&STDOUT");	


print "Content-type: text/html\n\n";




use common_DB;
if(!common_DB::checkViewAccess()){exit;}
my $dbh=common_DB::connectToDB();
if(!common_DB::checkWriteExecuteAccess()){print "ERROR! Not allowed to execute scripts! ".$ENV{'REMOTE_ADDR'}; exit;}

my $rScript					=common_DB::trim(param('rScript'));
my $remoteAddr			=$ENV{'REMOTE_ADDR'};
my $customAnalysisId=common_DB::trim(param('parentPrimaryKey'));
# verify params
my $Rpdf_format=common_DB::trim(param('__Rformat'));
my $Rpdf_command="";
if($Rpdf_format =~ /pdf\((\d+)x(\d+)\)/) {$Rpdf_command="pdf.options(width=$1, height=$2);\n"; }


my @allParams=param();


if (($rScript eq "") ){
	if ($customAnalysisId eq ""){
		$customAnalysisId =common_DB::trim(param('Custom_Analysis_Id'));
	}
	if ($customAnalysisId eq ""){
		print "ERROR: empty R script! <!--".join(" ",@allParams)." \n";
		exit;
	}else{
		connectToDB();
		my $sth;		
		my $sql="select R_script from MS_CUSTOM_ANALYSIS where Custom_Analysis_Id=$customAnalysisId";
		eval { $sth= $dbh->prepare($sql); };
		if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
		eval { $sth->execute() ;};
		if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
		my $row_ref = $sth->fetchrow_hashref() ;
		$rScript=$row_ref->{"R_script"};
		
	}
}

#### 2012-09-20>>>>>
#### obta
my @rScriptLines=split(/\n/,$rScript);
my $scriptProcessor=common_DB::getConfigValue('//R/R_location').' --no-restore --no-save CMD BATCH';
my $scriptType='R';
my $scriptExtension='.R';
if($rScriptLines[0] =~ /\#\!\/bin\/env.+perl/) {
	$scriptProcessor=common_DB::getConfigValue('//R/perl_location').' '; 
	$scriptType='perl'; 
	$scriptExtension='.pl';
}
if($rScriptLines[0] =~ /\#\!\/bin\/env.+python/) {
	$scriptProcessor=common_DB::getConfigValue('//R/python_location').' '; # there are different versions of python we prefer this one (an older that has access to Bio )
	$scriptType='python';
	$scriptExtension='.py';
}
#### 2012-09-20<<<<

my $originalRscript=$rScript;


  my $localWorkingDir=common_DB::getConfigValue('//R/temp_dir').$remoteAddr; 
  # create a new REMOTE_ADDR directory if not existent
  if (! -e $localWorkingDir){
	  my $cmd="mkdir '$localWorkingDir'";
    `$cmd`;
  }


  ### create a new random directory (if not exists)
  my $rndNumber=int(rand(100));
  $localWorkingDir .= "/".$rndNumber;
  if (! -e $localWorkingDir){
	  my $cmd="mkdir '$localWorkingDir'";
    `$cmd`;
  }
  


if($scriptType eq 'R'){
	$rScript='
		try(rm(list=ls()),silent=TRUE);'.$Rpdf_command.'
		setwd("'.$localWorkingDir.'");
		'.$rScript.'
      ls()          #list objects used  
      rm(list=ls()) # remove objects
			q("yes")      # quit
	';
 
} else {
	if($scriptType eq 'python'){
		# set ythe working directory
	  $rScript='
import os
os.chdir("'.$localWorkingDir.'")
'.$rScript.'
';		
	} else {
		if($scriptType eq 'perl'){
		  $rScript='chdir "$localWorkingDir";
'.$rScript.'
	    ';		
		}
  }
}	


#read the attachment.png image
use MIME::Base64;
my $encodedPng;
my $decodedPng;
if (open(FILE,"".'attachment.png')){
	binmode FILE ;
  read (FILE, $decodedPng, 120000);
	$encodedPng=encode_base64($decodedPng);
#	print($encodedPng);
	close FILE;
} else {
		print "ERROR: find the attachment icon !\n";
		exit;
	}


#store content of script there
my $localScriptFile;
my $sciptName=($customAnalysisId/1).$scriptExtension;
if ($rScript ne ""){
	$localScriptFile=$localWorkingDir."/".$sciptName;
	if (open(FILE,">".$localScriptFile)){
		print FILE $rScript; 
		close FILE;
	} else {
		print "ERROR: could not prepare local copy of script !\n";
		exit;
	}
}
my $forcedRead= `sleep 1; ls -l $localScriptFile`; # just force a read on the same file (it will flush bufferes etc)

my $pid = fork();
if (not defined $pid) {
	print "Please try again later!\n";
	exit;
}
if ($pid == 0) { # this is the child
	perform();
	exit;
}

my $kid=-1;
use POSIX ":sys_wait_h";
while ($kid <=0){
	$kid=waitpid($pid,WNOHANG);
	print " ";
	sleep(10);
}

exit;
##################################################################


sub perform(){
	#run R (or other) in batch mode
	my $rBin="cd $localWorkingDir; rm $sciptName.out.txt ; rm $sciptName".'out.txt'." ;  $scriptProcessor ";
	my $cmd="$rBin  $localScriptFile ;  mv $sciptName"."out  $sciptName"."out.txt  ";
  if($scriptType eq 'perl'  ) {$cmd="$rBin  $localScriptFile > $sciptName.out.txt 2>&1 "}
  if($scriptType eq 'python') {$cmd="$rBin  $localScriptFile > $sciptName.out.txt 2>&1 "}
  # print $cmd;
  
	 my $cmdResponse=`$cmd`; #execute
  # print $cmdResponse;
			
    # show all files generated recently (after the script)
    my $Files =`cd $localWorkingDir; find . -type f -newer $sciptName`;		
    #my $Files =`cd $localWorkingDir; find . -type f`;		
		my @allFile=split(/\n/,$Files);
		my @htmlList;
		my $i=0;
		foreach my $f (@allFile){
			#remove the ./
			$f=substr($f,2);
			# ignore invisible files
		  if(substr($f,0,1) eq ".") {next;}
			my $storeBinaryAction="";
			$storeBinaryAction='<img style="cursor:pointer" onclick="javascript:{if(confirm(\'Attach file to current analysis step?\')){if(popup) popup.close(); popup=window.open(\'storeFile2DB.pl?Custom_Analysis_Id='.$customAnalysisId.'&f='.$localWorkingDir."/".$f.'\',\'attachment confirmation\',\'height=20,width=600, left=150, top=150, directories=0, location=0, menubar=0, status=0, titlebar=0\')}}"  title="attach current FlowChart step:'.$customAnalysisId.' " src="data:image/png;base64,'.$encodedPng.'" width="16">';

			push @htmlList,'<td style="border:1px solid grey"><a target="_blank" href="getFile.pl?f='.$localWorkingDir.'/'.$f.'">'.$f.'</a> &nbsp;
			'.$storeBinaryAction.'</td>';
			$i++;
			if($i % 10 ==0) {push @htmlList,'</tr><tr>'};
		}
		print '<html>
		  <head><title>Attach to FlowChart item</title>
		  <script>var popup;</script>
		  </head>
			<body style="font-size:80%"><b>request:'.$rndNumber.' customAnalysisId:'.$customAnalysisId.'</b> nmb files:'.($#allFile+1).'<br>
			<table><tr>
			'.join("\n",@htmlList)
			.'</tr></table>'
			.'<br><span style="color:red">Finished</span>
          </body>
			</html>';
		

}


