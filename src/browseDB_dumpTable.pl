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

my $tableName		=common_DB::trim(param('tableName'));
my $crtDatabase		=common_DB::trim(param('database'));
my $remoteAddr		=$ENV{'REMOTE_ADDR'};

if ($tableName eq ""){
  print "Content-type: text/html\n\n";
  print "Table name not defined!";
  exit;
}
        
my $dbHostname=common_DB::getConfigValue('//db/host');
my $dbUsername=common_DB::getConfigValue('//db/user');
my $dbPassword=common_DB::getConfigValue('//db/user/@passwd');
my $dbDatabase=common_DB::getConfigValue('//db/database');
if($crtDatabase eq '') { $crtDatabase=$dbDatabase;}


my $pid = fork();
if (not defined $pid) {
  print "Content-type: text/html\n\n";
  print "Please try again later!\n";
  exit;
}

if ($pid == 0) { # this is the child
  $|=1;
  use POSIX ":sys_wait_h";
  sleep(1); # in case child is faster
  perform();
  exit;
}

  print "Content-type: text/mysqldump\n";
  print("Content-Disposition: attachment; filename=tableDump.sql\n");
  print("Pragma: no-cache\n");
  print("Expires: 0\n\n");

  my $kid=-1; 
  my $i=0;
  while (($kid <=0) ){
	  $kid=waitpid($pid,WNOHANG);
	  print " ";
	  sleep(10);
	  $i++; if($i>=5){last;}
  }
  
exit;
##################################################################


        
sub perform(){

  if($tableName eq "*"){$tableName="";}; ##### * is to dump all tables from the default database
  my $cmd= "/usr/bin/mysqldump --single-transaction -h$dbHostname -u$dbUsername $crtDatabase  $tableName -p$dbPassword";
  print `$cmd`;
}

