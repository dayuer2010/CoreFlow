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


# Reads from MB_CUSTOM_ANALYSIS_RESULT 
# Date created: 2010 Oct 29
# Last change:  2010 Oct 29 - 
use strict;	# comment this line to speed up things
use common_DB;

use CGI qw(-debug :standard);
if(!common_DB::checkViewAccess()){print "Content-type:text/html\n\n"; exit;}

my $dbh=common_DB::connectToDB();
my $sql;
my @allParams=param();
my $customAnalysisResultId=common_DB::trim(param("Custom_Analysis_Result_Id"));
if ($customAnalysisResultId eq ""){ 
	print "Content-type:text/html\n\n";
	print "\nERROR: Custom_Analysis_Result_Id is not defined!\n"; exit;	
}



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
	$sql="select Result_Type,uncompress(Result_Content) as Result_Content from MB_CUSTOM_ANALYSIS_RESULT where Custom_Analysis_Result_Id=$customAnalysisResultId ";
	my $sth;
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Content-type:text/html\n\nCouldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute() ;};
	if ($@) { print "Content-type:text/html\n\nCouldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	my $row_ref = $sth->fetchrow_hashref(); 
	if(! $row_ref){
		print "Content-type:text/html\n\nCouldn't find the previously stored result Id:$customAnalysisResultId"; exit;
	}
	my $myContent=$row_ref->{"Result_Content"};
	my $fileType        =$row_ref->{"Result_Type"};
	$fileType =~ s/.*\.//;
	my $mimeType=$fileType;
	if($fileType =~ /xls/i){ $mimeType= "vnd.ms-excel"}
	if($fileType =~ /pdf/i){ $mimeType= "pdf"}
	
	if($fileType =~ /htm(l)*/i){
	  print "Content-type:text/html\n\n";
	}else{
	  print "Content-type:application/$mimeType\n";
	  print "Content-Disposition: attachment; filename=result_".$customAnalysisResultId.".$fileType\n";
	  print "Pragma: no-cache\n";
	  print "Expires: 0\n\n";
	}  
	print $myContent ;
  exit;
}

