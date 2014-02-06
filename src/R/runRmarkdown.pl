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

my @allParams=param();



#### obta
my @rScriptLines=split(/\n/,$rScript);
my $scriptProcessor=common_DB::getConfigValue('//R/R_location').' --no-restore --no-save CMD BATCH';
my $scriptType='R';

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
  

my $localRMDfile=$localWorkingDir."/".$customAnalysisId.'_.Rmd';
my $localKnitfile=$localWorkingDir."/".$customAnalysisId.'_.md';
my $localHTMLfile=$localWorkingDir."/".$customAnalysisId.'_.html';
if($scriptType eq 'R'){

    `rm $localRMDfile; rm $localHTMLfile; rm $localKnitfile ;`;  
### save content of the original script (Rmd)
        my $originalRscriptNoSlashes = $originalRscript;
        $originalRscriptNoSlashes=~ s/\\([^\\])/$1/g; # strip slashes
        if (open(FILE,">".$localRMDfile)){
                print FILE $originalRscriptNoSlashes;
                close FILE;
        } else {
                print "ERROR: could not prepare local copy of script !\n";
                exit;
        }


	$rScript='
	try(rm(list=ls()),silent=TRUE);
	setwd("'.$localWorkingDir.'");
	library("knitr");
        library("markdown");
        pat_md();
        knit("'.$localRMDfile.'", output="'.$localKnitfile.'", tangle =FALSE);
        markdownToHTML("'.$localKnitfile.'","'.$localHTMLfile.'");

      ls()          #list objects used  
      rm(list=ls()) # remove objects
      q("yes")      # quit
	';
 
} 



#store content of markdown script there
my $localScriptFile;
my $sciptName=($customAnalysisId/1).'.R';
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
	my $rBin="cd $localWorkingDir;  rm $sciptName.out.txt ; rm $sciptName".'out.txt'." ;  $scriptProcessor ";
	my $cmd="$rBin  $localScriptFile ;  mv $sciptName"."out  $sciptName"."out.txt  ";
   #print $cmd;
  
	 my $cmdResponse=`$cmd`; #execute
   #print $cmdResponse;
         if(open(HTML,"< ". $localHTMLfile)){
           while(my $line=<HTML>){
             print $line;
           }
         }			
		

}


