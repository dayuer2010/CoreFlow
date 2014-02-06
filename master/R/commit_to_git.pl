#!/usr/bin/env perl
# update svn with script

# Date created: 2012 dec 09
# Last change:  2012 dec 10
#		

use strict;
use common_DB qw(-debug :standard);

$|=1;
print "Content-type: text/html;\n\n";

open(STDERR,">&STDOUT");
use CGI qw(-debug :standard);
if(!common_DB::checkViewAccess()){exit;}

my $database   =common_DB::getConfigValue('//db/database');


my $mainId     =common_DB::trim(param("Custom_Analysis_Id"));
my $whatScript =common_DB::trim(param('what'));
if ($mainId eq "" | $mainId eq ""){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting non empty Custom_Analysis_Id and indication of what script to be commited!  <br>\n";
	exit;
}

#if(!checkWriteExecuteAccess()){
#	print '<span style="color:red">Not allowed to update data into the database or run scripts!</span>';
#	exit;
#}

my $dbh=common_DB::connectToDB();


my $sth;	
###### obtain script
	my $sql = "select $whatScript from ".$database.".MB_CUSTOM_ANALYSIS where Custom_Analysis_Id= $mainId ";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	eval { $sth->execute() ;};
	if ($@) { 
		print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query!<br>  error : $DBI::errstr\n  <br>\n";
	}
	my $row_ref = $sth->fetchrow_hashref(); 
	if(! $row_ref){
		print "<font style='color:red'><b>ERROR!</b></font> Couldn't find the previously stored $whatScript for Id:$mainId"; exit;
	}
  my $scriptContent =$row_ref->{$whatScript};
	if( common_DB::trim($scriptContent) eq ""){
		print "<font style='color:red'><b>ERROR!</b></font>  $whatScript for Id:$mainId is empty!"; exit;
	}
	
	
####### store in a file created in the repository
  my $remoteAddr		=$ENV{'REMOTE_ADDR'};

  my $fileName;
  if($whatScript eq 'Db_script'){
  	$fileName=$mainId.".sql";
  }else{
    $fileName=$mainId.".".detectScriptType($scriptContent);
  }
  my $localWorkingDir=common_DB::getConfigValue('//svn/svn_repo_location'); 
  my $localHomeDir=common_DB::getConfigValue('//svn/svn_home');

  
  my $localScriptFile=$localWorkingDir."/".$fileName;

####### retrieve from svn

####### save file and commit changes to svn
	if (open(FILE,">".$localScriptFile)){
		print FILE $scriptContent; close FILE;
	} else {
		print "Content-type: text/html\n\n";
		print "ERROR: could not prepare local copy of script !\n";
		exit;
	}

#### just once (if there is no folder called .git)
if (!(-d $localWorkingDir."/.git")){
   my $svnContent=`cd $localWorkingDir ; git init `; # run this just once
   `export HOME=$localHomeDir ; git config --global coreFlow "CoreFlow Apache GitServer" `; # run this just once
   `export HOME=$localHomeDir ; git config --global user.email pasculescu\@lunenfeld.ca`; # run this just once
} 

####### commit and show svn for that particuler script
my $svnContentCmd="export HOME=$localHomeDir ; cd $localWorkingDir ; git add $fileName; git commit $fileName -m '$fileName' ";


#print '<span style="color:red">Finished!</span>';
print '<html><body>
<!--
<pre>'.`$svnContentCmd`.'</pre>
<br><a href="/git/?p=.git&a=search&h=HEAD&s='.$fileName.'">git</a>
-->
<script>
   location.href="/git/?p=.git&a=search&h=HEAD&s='.$fileName.'";
</script>
</body></html>
';

###########################################################################################

sub detectScriptType($){  # based on the first line of the script
	(my $content)=@_;
	my @allLines=split(/\n/,$content);
	my $firstLine=$allLines[0];
	
	if($firstLine =~ /^#!.+perl/) {return 'pl'}
	if($firstLine =~ /^#!.+python/) {return 'py'}
	return 'R';
}


