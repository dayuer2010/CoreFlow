#!/usr/bin/env perl

#**
#
# Copyright (c) 2012-2013 Mount Sinai Hospital, Toronto, Ontario, 
# Copyright (c) 2012-2013 DTU/CSIG Linding Lab
#
# LICENSE:
#
# This is free software; you can redistribute it
# and/or modify it under the terms of the GNU General
# Public License as published by the Free Software Foundation;
# either version 3 of the License, or (at your option) any
# later version.
#
# This software is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with the source code.  If not, see <http://www.gnu.org/licenses/>.
#
#
#**


use strict;	# comment this line to speed up things
use CGI qw(-debug :standard);
use common_DB;
if(!common_DB::checkViewAccess()){exit;}

$|=1;
#open(STDERR, ">&STDOUT");	


my @allParams=param();
print "Content-type: text/html\n\n";


my $dbh=common_DB::connectToDB();
my $sth;

my $database   =common_DB::getConfigValue('//db/database');


my $action=common_DB::trim(param('action'));
if($action eq "delete"){
  my $customAnalysisResultId=common_DB::trim(param('Custom_Analysis_Result_Id'));
	my $sql="delete from  ".$database.".MB_CUSTOM_ANALYSIS_RESULT where Custom_Analysis_Result_Id='$customAnalysisResultId'";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute() ;};
	if ($@) { 
		print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; 
		exit; 
	};

  print "<head><title>Analysis Attachment management in the Database</title></head><body>Done! ($sql) <script>window.focus()</script></body>";
  exit;
} 

my $customAnalysisId	=common_DB::trim(param('Custom_Analysis_Id'));
my $fileDescription		=common_DB::trim(param('short_description'));
my $localFile         =common_DB::trim(param('local_file'));

my $sql="select Custom_Analysis_Result_Id,Result_Description from ".$database.".MB_CUSTOM_ANALYSIS_RESULT where Custom_Analysis_Id=$customAnalysisId and Result_Description='$fileDescription'";
eval { $sth= $dbh->prepare($sql); };
if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
eval { $sth->execute() ;};
if ($@) { 
	print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; 
	exit; 
};

my $row_ref =$sth->fetchrow_hashref() ;
my $resultId=$row_ref->{"Custom_Analysis_Result_Id"};
		
my $size;
my $buff;
my $resultContent;
while (my $bytes_read=read($localFile,$buff,2096)){
	$size += $bytes_read;
	$resultContent .=$buff;
}

my $resultType = $localFile;
$resultType =~ s/^.*\\.//;


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

sub perform(){
 if($resultId ne ""){ # we have to update
	print "Custom_Analysis_Result_Id:$resultId <br> updating with: ".$localFile;
	$sql="SET max_allowed_packet=100*1024*1024";
	#eval { $sth= $dbh->prepare($sql); };
  #      if ($@) { print "<br>Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
  #      eval { $sth->execute() ;};
  #      if ($@) { print "<br>Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>";} 


	$sql="update ".$database.".MB_CUSTOM_ANALYSIS_RESULT set Result_Content=COMPRESS(?), Result_Type='$resultType' where Custom_Analysis_Result_Id=$resultId";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute($resultContent) ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; 
    my (undef, $max_allowed_packet) =  $dbh->selectrow_array( qq{show variables LIKE ? }, undef, "max_allowed_packet" ) ;
    printf "max_allowed_packet => %.2f MB\n or try: SET GLOBAL max_allowed_packet=16*1024*1024; using mysql", $max_allowed_packet  / (1024*1024);
		exit; 
	};
	print " Done!";
	
 }else{ # we have to insert
	print "Custom_Analysis_Result_Id:$resultId <br> inserting with: ".$localFile;
	$sql="insert into ".$database.".MB_CUSTOM_ANALYSIS_RESULT(Custom_Analysis_Id,Result_Type,Result_Content,Result_Description) values(?,?,COMPRESS(?),?)";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute($customAnalysisId,$resultType,$resultContent,$fileDescription) ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; 
    my (undef, $max_allowed_packet) =  $dbh->selectrow_array( qq{show variables LIKE ? }, undef, "max_allowed_packet" ) ;
    printf "max_allowed_packet => %.2f MB\n or try: SET GLOBAL max_allowed_packet=16*1024*1024; using mysql", $max_allowed_packet  / (1024*1024);
	  exit;
	};
	print " Done!";
 }
}




