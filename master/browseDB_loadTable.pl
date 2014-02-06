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


# Load a table with data from a  dump file

# Date created: 2013 jan 13
# Last change:  2013 jan 13

$|=1;
print "Content-type: text/html;\n\n";

use strict;	# comment this line to speed up things
open(STDERR,">&STDOUT");

use common_DB;
my $dbh=common_DB::connectToDB();


use CGI qw(-debug :standard);
if(!common_DB::checkViewAccess()){exit;}

if(!common_DB::checkWriteExecuteAccess()){
	print '<span style="color:red">You do not have authorization to execute scripts or modify tables! Please contact authors!</span>';
	exit;
}

my $appName = common_DB::getConfigValue('//app_name');


my $tableName =common_DB::trim(param('tableName'));
if($tableName eq ""){
	print '<span style="color:red">Target table missing!</span>';
	exit;
}

my $database = common_DB::trim(param('database'));
if($database eq ""){
	print '<span style="color:red">Target database missing!</span>';
	exit;
}

my $fileToSlurp=common_DB::trim(param("fileToSlurp"));
if($fileToSlurp ne ""){
	my $contentDump;
	my $size;
	my $buff;
	while (my $bytes_read=read($fileToSlurp,$buff,2096)){
		$size += $bytes_read;
		$contentDump .=$buff;
	}
	
	my $dbHostname=common_DB::getConfigValue('//db/host');
	my $dbUsername=common_DB::getConfigValue('//db/user');
	my $dbPassword=common_DB::getConfigValue('//db/user/@passwd');
	
	my $cmd="/usr/bin/mysql -h$dbHostname -u$dbUsername -D$database  -p$dbPassword";
  if(open(PIPE,"| $cmd > temp/_fasta_slurp_result.txt 2>&1")){
    print PIPE $contentDump;
    #print $contentDump;
    close PIPE; 
  }else{
  	print '<span style="color:red">Could not re-create table '.$tableName.'!</span>'. $!."<br>\n";
  	exit;
  }

  if(open(RESULT," temp/_fasta_slurp_result.txt")){
  	my $resultContent=<RESULT>;
  	if(common_DB::trim($resultContent) ne "") {
  		print '<pre style="color:red">'."\n".$resultContent."\n</pre>\n";
  	  exit;
  	}
  	close(RESULT);
  }else{
  	print '<span style="color:red">Could not finish table replacement!</span>'. $!."<br>\n";
  	exit;
  }

	print'
	 <html>
	   <head>
	     <title>'.$appName.'</title>
	   </head>
	   <body>
	   <script>
	     location.href="browseDB_describeTable.php?database='.$database.'&table='.$tableName.'";
	   </script>
	   </body>
	 </html>  
  ';

} else {
	print'
	 <html>
	   <head>
	     <title>'.$appName.'</title>
	   </head>
	   <body>
	   <form name="slurpFileForm" method="post" style="display:inline" enctype="multipart/form-data">
	   Locate the file that will completely replace table <b>'.$tableName.'</b> (in mysql `dump` format)
	   <br><input type="file" style="font-size:70%;background-color:#CCCCCC;" name="fileToSlurp" size="20">
	   <input type="hidden" name="tableName" value="'.$tableName.'">
	   <input type="hidden" name="database" value="'.$database.'">
	   <input type="submit" value="Replace table with file content" onclick="if (confirm(\'Are you sure?\')) return(true)">
	   </form>
	   </body>
	 </html>  
	   ';
	
}

	
###########################################################################################




