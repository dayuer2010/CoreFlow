#!/usr/bin/env perl
# Reads from MS_CUSTOM_ANALYSIS_RESULT 
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
if($fileType eq "xls"){ $mimeType= "vnd.ms-excel"}
if($fileType eq "pdf"){ $mimeType= "pdf"}

print "Content-type:application/$mimeType\n";
print "Content-Disposition: attachment; filename=result_".$customAnalysisResultId.".$fileType\n";
print "Pragma: no-cache\n";
print "Expires: 0\n\n";
#print "Content-type:text/html\n\n";
print $myContent ;
exit;

