#!/usr/bin/env perl

use strict;	# comment this line to speed up things
use CGI qw(-debug :standard);

$|=1;
#open(STDERR, ">&STDOUT");	

use common_DB;
if(!common_DB::checkViewAccess()){exit;}
my $dbh=common_DB::connectToDB();
if(!common_DB::checkWriteExecuteAccess()){
  print "Content-type: text/html\n\n";
  print "ERROR! Not allowed to execute scripts! ".$ENV{'REMOTE_ADDR'}; 
  exit;
 }


  print "Content-type: octet-stream\n";
  print("Content-Disposition: attachment; filename=CoreFlow_this_code.tgz\n");
  print("Pragma: no-cache\n");
  print("Expires: 0\n\n");

  my $cmd='tar -zcvf -  --exclude "../CoreFlow/.config.xml" --exclude "../CoreFlow/R/.config.xml" --exclude "../CoreFlow/*~"  ../CoreFlow/* ';
  print `$cmd`; 
  
