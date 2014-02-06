#!/usr/bin/env perl
# Manage requests from Algonquin 
#    (this script must run on Killarney: http://killarney.mshri.on.ca/cgi-bin/NetworKIN/ )
# Date created: 2008 Aug 11
# Last change:  2008 Aug 11
# 		2009 Jan 08 - allow a new cgi param: a (remoteAddr)

use strict;	# comment this line to speed up things
use CGI qw(-debug :standard);

$|=1;
#open(STDERR, ">&STDOUT");	


my @allParams=param();


use common_DB;
if(!common_DB::checkViewAccess()){exit;}
if(!common_DB::checkWriteExecuteAccess()){
	print "Content-type: text/html\n\n";
	print "ERROR! Not allowed to execute scripts!"; 
	exit;
}

my $rFile		=common_DB::trim(param('f'));



# verify params
if ($rFile eq "") {
	print "Content-type: text/html\n\n";
	print "ERROR: no parameters (file name) <!--".join(" ",@allParams)." \n";
	exit;
}


my $localOUTPUTfile=$rFile;
if (open(FILE,"".$localOUTPUTfile)){
	my $rType=$rFile;
	$rType =~ s/.*\.//;
	if ($rType =~ /svg/){
		print "Content-type: application/svg+xml\n";
		print "Content-Disposition: attachment; filename=".$rFile."\n";
		print "Pragma: no-cache\n";
		print "Expires: 0\n";
		print "\n";
	} else {
		if ($rType =~ /htm/){
			print "Content-type: text/html\n\n";
		}else {
			print "Content-Type: application/".$rType."\n";
			print "Content-Disposition: attachment; filename=".$rFile."\n";
			print "Pragma: no-cache\n";
			print "Expires: 0\n";
			print "\n";
		}
	}
	while(my $line=<FILE>){
		print $line;
	}	
	close FILE;
} else {
	print "Content-type: text/html\n\n";
	print "ERROR: R result file missing!\n";
	exit;
}


exit;
